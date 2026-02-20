<?php

declare(strict_types=1);

namespace App\Services\Steam;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

final class SteamClient
{
    private const BASE = 'https://api.steampowered.com';

    public function __construct(
        private readonly string $key,
        private readonly string $steamId,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            (string) config('services.steam.key'),
            (string) config('services.steam.id'),
        );
    }

    /** Header padrão grande da loja */
    private function headerImage(int $appId): string
    {
        return "https://cdn.cloudflare.steamstatic.com/steam/apps/{$appId}/header.jpg";
    }

    /** Capsule grande (bom para fallback) */
    private function capsuleImage(int $appId): string
    {
        return "https://cdn.cloudflare.steamstatic.com/steam/apps/{$appId}/capsule_616x353.jpg";
    }

    /**
     * Jogos jogados recentemente
     * @return array<int, array{appid:int,name:string,playtime:int,image:string,capsule:string}>
     */
    public function recentGames(int $limit = 6): array
    {
        return Cache::remember("steam:recent:{$this->steamId}", 900, function () use ($limit) {
            $res = Http::get(self::BASE . '/IPlayerService/GetRecentlyPlayedGames/v1', [
                'key'     => $this->key,
                'steamid' => $this->steamId,
                'format'  => 'json',
            ])->json('response.games') ?? [];

            $mapped = array_map(function (array $g): array {
                $appid = (int) $g['appid'];
                return [
                    'appid'    => $appid,
                    'name'     => (string) $g['name'],
                    'playtime' => (int) ($g['playtime_2weeks'] ?? $g['playtime_forever'] ?? 0),
                    'image'    => $this->headerImage($appid),
                    'capsule'  => $this->capsuleImage($appid),
                ];
            }, $res);

            return array_slice($mapped, 0, $limit);
        });
    }

    /**
     * Resumo da biblioteca (top jogados)
     * @return array{game_count:int,total_minutes:int,top:array<int, array{appid:int,name:string,playtime:int,image:string,capsule:string}>}
     */
    public function librarySummary(int $top = 6): array
    {
        return Cache::remember("steam:owned:{$this->steamId}", 900, function () use ($top) {
            $res = Http::get(self::BASE . '/IPlayerService/GetOwnedGames/v1', [
                'key'                       => $this->key,
                'steamid'                   => $this->steamId,
                'include_appinfo'           => 1,
                'include_played_free_games' => 1,
            ])->json('response') ?? [];

            $games = $res['games'] ?? [];
            $total = 0;

            $mapped = array_map(function (array $g) use (&$total): array {
                $appid   = (int) $g['appid'];
                $minutes = (int) ($g['playtime_forever'] ?? 0);
                $total  += $minutes;

                return [
                    'appid'    => $appid,
                    'name'     => (string) ($g['name'] ?? 'Jogo'),
                    'playtime' => $minutes,
                    'image'    => $this->headerImage($appid),
                    'capsule'  => $this->capsuleImage($appid),
                ];
            }, $games);

            usort($mapped, fn($a, $b) => $b['playtime'] <=> $a['playtime']);

            return [
                'game_count'    => (int) ($res['game_count'] ?? 0),
                'total_minutes' => $total,
                'top'           => array_slice($mapped, 0, $top),
            ];
        });
    }

    /**
     * Jogo atual (se estiver in-game e o perfil permitir)
     * @return array{appid:int,name:string,image:string}|null
     */
    public function currentGame(): ?array
    {
        // cache curtinho pra não bater sempre
        return Cache::remember("steam:now:{$this->steamId}", 30, function () {
            $player = Http::get(self::BASE . '/ISteamUser/GetPlayerSummaries/v2', [
                'key'      => $this->key,
                'steamids' => $this->steamId,
            ])->json('response.players.0');

            if (!$player || empty($player['gameid'])) {
                return null;
            }

            $appid = (int) $player['gameid'];

            return [
                'appid' => $appid,
                'name'  => (string) ($player['gameextrainfo'] ?? 'Jogando agora'),
                'image' => $this->headerImage($appid),
            ];
        });
    }

    /**
     * Conquistas (recentes) do app destacado
     * @return array<int, array{name:string,achieved:bool,icon:string,unlock_time:int}>
     */
    public function achievements(int $appId, int $limit = 8): array
    {
        $key = "steam:achievements:{$this->steamId}:{$appId}";

        return Cache::remember($key, 900, function () use ($appId, $limit) {
            // PT-BR primeiro, com fallback
            $lang = 'brazilian';

            $player = Http::get(self::BASE . '/ISteamUserStats/GetPlayerAchievements/v1', [
                'key'     => $this->key,
                'steamid' => $this->steamId,
                'appid'   => $appId,
                'l'       => $lang,          // <<< PT-BR
            ])->json('playerstats.achievements') ?? [];

            $schema = Http::get(self::BASE . '/ISteamUserStats/GetSchemaForGame/v2', [
                'key'   => $this->key,
                'appid' => $appId,
                'l'     => $lang,            // <<< PT-BR
            ])->json('game.availableGameStats.achievements') ?? [];

            // Fallback automático se o jogo não tiver PT-BR
            if (!$schema && $lang === 'brazilian') {
                $player = Http::get(self::BASE . '/ISteamUserStats/GetPlayerAchievements/v1', [
                    'key'     => $this->key,
                    'steamid' => $this->steamId,
                    'appid'   => $appId,
                    'l'       => 'portuguese',
                ])->json('playerstats.achievements') ?? [];

                $schema = Http::get(self::BASE . '/ISteamUserStats/GetSchemaForGame/v2', [
                    'key'   => $this->key,
                    'appid' => $appId,
                    'l'     => 'portuguese',
                ])->json('game.availableGameStats.achievements') ?? [];
            }

            // Se ainda não houver, cai para inglês
            if (!$schema) {
                $schema = Http::get(self::BASE . '/ISteamUserStats/GetSchemaForGame/v2', [
                    'key'   => $this->key,
                    'appid' => $appId,
                    'l'     => 'english',
                ])->json('game.availableGameStats.achievements') ?? [];
            }

            $info = [];
            foreach ($schema as $s) {
                $name = (string) $s['name'];
                $info[$name] = [
                    'display'  => (string) ($s['displayName'] ?? $name),
                    'icon'     => (string) ($s['icon'] ?? ''),
                    'icongray' => (string) ($s['icongray'] ?? ''),
                ];
            }

            $merged = [];
            foreach ($player as $a) {
                $api      = (string) $a['apiname'];
                $achieved = (bool)   ($a['achieved'] ?? false);
                $unlock   = (int)    ($a['unlocktime'] ?? 0);
                $meta     = $info[$api] ?? ['display' => $api, 'icon' => '', 'icongray' => ''];

                $merged[] = [
                    'name'        => $meta['display'],                    // <<< Nome traduzido
                    'achieved'    => $achieved,
                    'icon'        => $achieved ? $meta['icon'] : ($meta['icongray'] ?: $meta['icon']),
                    'unlock_time' => $unlock,
                ];
            }

            $merged = array_values(array_filter($merged, fn($x) => $x['achieved']));
            usort($merged, fn($a, $b) => $b['unlock_time'] <=> $a['unlock_time']);

            return array_slice($merged, 0, $limit);
        });
    }
}
