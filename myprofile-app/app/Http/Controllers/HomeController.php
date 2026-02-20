<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Throwable;
use App\Services\Steam\SteamClient;
use App\Services\Weather\WeatherClient;

final class HomeController extends Controller
{
    public function __invoke(Request $request)
    {
        // ---------- STEAM ----------
        $steam          = SteamClient::fromConfig();
        $recent         = [];
        $summary        = [];
        $achv           = [];
        $currentGame    = null; // <- usado pelo Blade para “Jogando agora”
        $featuredGameId = null;

        try {
            // Se seu client tiver outro nome, troque currentGame() por nowPlaying() ou getCurrentGame()
            $currentGame = $steam->currentGame(); // deve retornar ['appid'=>int,'name'=>string,'image'=>url,'capsule'=>url] ou null
        } catch (Throwable $e) {
            // evita quebrar a home se a Steam rate-limitou
            $currentGame = null;
        }

        try {
            $recent  = $steam->recentGames(6);       // array de jogos recentes (até 6)
        } catch (Throwable $e) {
            $recent = [];
        }

        try {
            $summary = $steam->librarySummary(6);    // ['game_count'=>int,'total_minutes'=>int,'top'=>[...]]
        } catch (Throwable $e) {
            $summary = ['game_count' => 0, 'total_minutes' => 0, 'top' => []];
        }

        // Escolhe um app para destacar conquistas:
        // 1) o que está jogando agora; 2) primeiro dos recentes; 3) primeiro do top.
        $featuredGameId =
            Arr::get($currentGame, 'appid')
            ?? Arr::get($recent, '0.appid')
            ?? Arr::get($summary, 'top.0.appid');

        try {
            $achv = $featuredGameId ? $steam->achievements((int) $featuredGameId, 8) : [];
        } catch (Throwable $e) {
            $achv = [];
        }

        // ---------- CLIMA ----------
        $wx = new WeatherClient();

        try {
            // Natal/RN (coords aproximadas)
            $weatherNatal = $wx->byCoords(-5.795, -35.209, 'Natal, RN');
        } catch (Throwable $e) {
            $weatherNatal = null;
        }

        try {
            // Visitante (por IP)
            $weatherVisitor = $wx->byRequest($request);
        } catch (Throwable $e) {
            $weatherVisitor = null;
        }

        // ---------- VIEW ----------
        return view('home', [
            // Steam
            'steamProfile'         => 'https://steamcommunity.com/profiles/' . (config('services.steam.id') ?? config('services.steam.steamid')),
            'currentGame'          => $currentGame,        // <- necessário para o bloco “Jogando agora” no Blade
            'recentGames'          => $recent,
            'steamSummary'         => $summary,
            'featuredGameId'       => $featuredGameId,
            'featuredAchievements' => $achv,

            // Clima
            'weatherNatal'         => $weatherNatal,
            'weatherVisitor'       => $weatherVisitor,
        ]);
    }
}
