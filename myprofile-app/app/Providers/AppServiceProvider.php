<?php

namespace App\Providers;

use Carbon\Carbon;
use Dflydev\DotAccessData\Data;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        Carbon::setLocale('pt_BR');
        // Dflydev\DotAccessData\Data does not provide a setLocale method; use PHP's setlocale instead
        setlocale(LC_ALL, 'pt_BR.UTF-8');
    }
}
