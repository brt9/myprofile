<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;

class TelemetryController extends Controller
{
    private const CACHE_KEY = 'telemetry:latest';
    private const TTL_SEC   = 120;   // mantém último pacote por até 2 min
    private const STALE_SEC = 30;    // após 30s sem push, marcar "stale"

    public function store(Request $req): JsonResponse
    {
        // Token simples (opcional)
        $auth  = $req->header('Authorization', '');
        $token = config('services.telemetry.token', env('TELEMETRY_TOKEN'));
        if (!$token || $auth !== 'Bearer ' . $token) {
            return response()->json(['message' => 'unauthorized'], 401);
        }

        // Campos aceitos (inclui gpu_load)
        $fields = ['cpu_temp', 'gpu_temp', 'cpu_load', 'gpu_load', 'pump_rpm', 'coolant_temp'];
        $in     = $req->only($fields);

        // Normalização / limites simples
        $num = static fn($v) => is_null($v) ? null : (is_numeric($v) ? (float)$v : null);
        $clamp = static function ($v, $lo, $hi) {
            if ($v === null) return null;
            if ($v < $lo) return $lo;
            if ($v > $hi) return $hi;
            return $v;
        };

        $cpu_temp     = $clamp($num($in['cpu_temp'] ?? null), 0, 120);
        $gpu_temp     = $clamp($num($in['gpu_temp'] ?? null), 0, 120);
        $cpu_load     = $clamp($num($in['cpu_load'] ?? null), 0, 100);
        $gpu_load     = $clamp($num($in['gpu_load'] ?? null), 0, 100);
        $pump_rpm     = $num($in['pump_rpm'] ?? null);       // se um dia existir
        $coolant_temp = $clamp($num($in['coolant_temp'] ?? null), 0, 120);

        // Arredonda onde faz sentido (1 casa p/ temp e carga)
        $round1 = static fn($v) => is_null($v) ? null : round($v, 1);

        $payload = [
            'cpu_temp'      => $round1($cpu_temp),
            'gpu_temp'      => $round1($gpu_temp),
            'cpu_load'      => $round1($cpu_load),
            'gpu_load'      => $round1($gpu_load),
            'pump_rpm'      => $pump_rpm,            // inteiro opcional (se vier)
            'coolant_temp'  => $round1($coolant_temp),
            'updated_at'    => Carbon::now()->toIso8601String(),
        ];

        Cache::put(self::CACHE_KEY, $payload, self::TTL_SEC);

        return response()->json(['ok' => true]);
    }

    public function show(): JsonResponse
    {
        $d = Cache::get(self::CACHE_KEY);
        if (!$d) {
            return response()->json(['stale' => true]);
        }

        $stale = Carbon::parse($d['updated_at'])->diffInSeconds(now()) > self::STALE_SEC;

        // retorna exatamente o que temos + flag 'stale'
        return response()->json($d + ['stale' => $stale]);
    }
}
