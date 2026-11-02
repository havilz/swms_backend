<?php

use App\Http\Controllers\Web\Admin\AuthController;
use App\Http\Controllers\Web\Admin\DashboardController;
use App\Http\Controllers\Web\Admin\ReportController;
use App\Http\Controllers\Web\Admin\UserController;
use Illuminate\Support\Facades\Route;

// Redirect root to admin dashboard or login
Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

// Admin Authentication Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->name('login.submit');
    });

    // Protected Admin Dashboard Routes
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Reports Management
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/{report}', [ReportController::class, 'show'])->name('reports.show');
        Route::patch('reports/{report}/status', [ReportController::class, 'updateStatus'])->name('reports.status');
        Route::delete('reports/{report}', [ReportController::class, 'destroy'])->name('reports.destroy');

        // User Management
        Route::resource('users', UserController::class)->except(['show']);
    });
});
