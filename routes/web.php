<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/tools', [DashboardController::class, 'storeTool'])->name('tools.store');
    Route::patch('/tools/{tool}/condition', [DashboardController::class, 'updateToolCondition'])->name('tools.condition');
    Route::patch('/tools/{tool}/archive', [DashboardController::class, 'archiveTool'])->name('tools.archive');
    Route::post('/technicians', [DashboardController::class, 'storeTechnician'])->name('technicians.store');
    Route::patch('/technicians/{technician}/archive', [DashboardController::class, 'archiveTechnician'])->name('technicians.archive');
    Route::post('/loans', [DashboardController::class, 'borrow'])->name('loans.store');
    Route::patch('/loans/{loan}/return', [DashboardController::class, 'returnTool'])->name('loans.return');
});
