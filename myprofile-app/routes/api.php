<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelemetryController;

Route::prefix('telemetry')->as('telemetry.')->group(function (): void {
    Route::post('push', [TelemetryController::class, 'store'])
        ->middleware('throttle:60,1')
        ->name('push'); // POST /api/telemetry/push

    Route::get('', [TelemetryController::class, 'show'])
        ->name('show'); // GET /api/telemetry
});
