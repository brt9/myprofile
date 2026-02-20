<?php

declare(strict_types=1);

namespace App\Services\Weather;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

final class WeatherClient
{
    private const OPEN_METEO = 'https://api.open-meteo.com/v1/forecast';

    /**
     * Busca clima atual por latitude/longitude.
     * Retorna dados prontos pra mostrar na view.
     */
    public function byCoords(float $lat, float $lon, ?string $label = null): array
    {
        $cacheKey = sprintf('wx:%s:%s', round($lat, 3), round($lon, 3));

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($lat, $lon, $label) {
            $params = [
                'latitude'  => $lat,
                'longitude' => $lon,
                'current'   => implode(',', [
                    'temperature_2m',
                    'apparent_temperature',
                    'weather_code',
                    'wind_speed_10m',
                    'wind_direction_10m',
                    'relative_humidity_2m',
                    'pressure_msl',
                    'uv_index',
                    'cloud_cover',
                ]),
                'timezone'  => 'auto',
            ];

            $json = Http::timeout(8)->get(self::OPEN_METEO, $params)->json();
            $c = $json['current'] ?? [];

            $code = (int) ($c['weather_code'] ?? 0);

            return [
                'label'      => $label ?: '—',
                'temp'       => $c['temperature_2m'] ?? null,
                'feels_like' => $c['apparent_temperature'] ?? null,
                'wind_kmh'   => isset($c['wind_speed_10m']) ? round((float) $c['wind_speed_10m']) : null,
                'wind_dir'   => $c['wind_direction_10m'] ?? null,
                'humidity'   => $c['relative_humidity_2m'] ?? null,
                'pressure'   => $c['pressure_msl'] ?? null,
                'uv'         => $c['uv_index'] ?? null,
                'clouds'     => $c['cloud_cover'] ?? null,
                'updated_at' => $c['time'] ?? null,
                'code'       => $code,
                'condition'  => self::describe($code),
                'emoji'      => self::emoji($code),
            ];
        });
    }

    /**
     * Deduz cidade/coords pelo IP do visitante e retorna o clima.
     */
    public function byRequest(Request $request): array
    {
        // tenta pegar o IP “real” atrás de proxy/reverso
        $ip = $request->server('HTTP_CF_CONNECTING_IP')
            ?: ($request->server('HTTP_X_FORWARDED_FOR')
                ? trim(explode(',', (string) $request->server('HTTP_X_FORWARDED_FOR'))[0])
                : null)
            ?: $request->server('HTTP_X_REAL_IP')
            ?: $request->ip();

        // se só temos IP privado (ex.: 172.20.0.1), evita geolocalizar
        if (self::isPrivateIp($ip)) {
            return $this->byCoords(-23.5505, -46.6333, 'Sua localização');
        }

        // 1) tenta ipapi.co
        $geo = null;
        try {
            $geo = Http::timeout(5)->retry(1, 200)
                ->get("https://ipapi.co/{$ip}/json/")
                ->json();
        } catch (\Throwable $e) {
            Log::warning('ipapi.co indisponível', ['err' => $e->getMessage()]);
        }

        // 2) fallback: ipwho.is (sem key, Cloudflare)
        if (empty($geo) || empty($geo['latitude']) || empty($geo['longitude'])) {
            try {
                $alt = Http::timeout(5)->retry(1, 200)
                    ->get("https://ipwho.is/{$ip}")
                    ->json();

                if (!empty($alt) && !($alt['bogon'] ?? false) && ($alt['success'] ?? true)) {
                    $geo = [
                        'latitude'     => $alt['latitude']  ?? null,
                        'longitude'    => $alt['longitude'] ?? null,
                        'city'         => $alt['city']      ?? null,
                        'region'       => $alt['region']    ?? null,
                        'region_code'  => $alt['region_code'] ?? null,
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning('ipwho.is indisponível', ['err' => $e->getMessage()]);
            }
        }

        // monta resposta mesmo se não vier nada
        $lat  = isset($geo['latitude'])  ? (float) $geo['latitude']  : -23.5505;
        $lon  = isset($geo['longitude']) ? (float) $geo['longitude'] : -46.6333;
        $city = trim(($geo['city'] ?? 'Sua localização') . (
            !empty($geo['region_code']) ? ", {$geo['region_code']}" : (!empty($geo['region']) ? ", {$geo['region']}" : '')
        ));

        return $this->byCoords($lat, $lon, $city);
    }

    /**
     * Verifica se o IP é privado ou reservado.
     *
     * Trata null/empty ou IPs inválidos como privados para evitar geolocalização.
     */
    private static function isPrivateIp(?string $ip): bool
    {
        if (!$ip) return true;

        if ($ip === '::1') return true;

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            if (
                str_starts_with($ip, '10.')
                || str_starts_with($ip, '127.')
                || str_starts_with($ip, '192.168.')
                || preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $ip)
            ) {
                return true;
            }
        }

        return false;
    }

    /** Descrição PT-BR do weather_code do Open-Meteo */
    public static function describe(int $code): string
    {
        $map = [
            0 => 'Céu limpo',
            1 => 'Principalmente limpo',
            2 => 'Parcialmente nublado',
            3 => 'Nublado',
            45 => 'Nevoeiro',
            48 => 'Nevoeiro com gelo',
            51 => 'Garoa fraca',
            53 => 'Garoa',
            55 => 'Garoa forte',
            56 => 'Garoa congelante fraca',
            57 => 'Garoa congelante',
            61 => 'Chuva fraca',
            63 => 'Chuva',
            65 => 'Chuva forte',
            66 => 'Chuva congelante fraca',
            67 => 'Chuva congelante',
            71 => 'Neve fraca',
            73 => 'Neve',
            75 => 'Neve forte',
            77 => 'Grãos de neve',
            80 => 'Aguaceiros fracos',
            81 => 'Aguaceiros',
            82 => 'Aguaceiros fortes',
            85 => 'Aguaceiros de neve fracos',
            86 => 'Aguaceiros de neve fortes',
            95 => 'Trovoadas',
            96 => 'Trovoadas com granizo leve',
            99 => 'Trovoadas com granizo forte',
        ];

        return $map[$code] ?? 'Tempo variável';
    }

    /** Emojizinho simpático 😄 */
    public static function emoji(int $code): string
    {
        return match (true) {
            $code === 0 => '☀️',
            in_array($code, [1, 2]) => '🌤️',
            $code === 3 => '☁️',
            in_array($code, [45, 48]) => '🌫️',
            in_array($code, [51, 53, 55, 61, 63, 65, 80, 81, 82]) => '🌧️',
            in_array($code, [71, 73, 75, 77, 85, 86]) => '❄️',
            in_array($code, [95, 96, 99]) => '⛈️',
            default => '⛅',
        };
    }
}
