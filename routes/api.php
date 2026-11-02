<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Report\ReportController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public Auth endpoints with rate limiting
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register'])->middleware('throttle:api-auth');
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:api-auth');

        // Authenticated Auth endpoints
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
        });
    });

    // Mobile User Reports API (Protected)
    Route::middleware(['auth:sanctum', 'user'])->group(function () {
        Route::get('reports', [ReportController::class, 'index']);
        Route::post('reports', [ReportController::class, 'store']);
        Route::get('reports/{report}', [ReportController::class, 'show']);
    });
});
