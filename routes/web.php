<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DisplayController;
use App\Http\Controllers\KioskController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/turnos');

Route::get('/turnos', [KioskController::class, 'index'])->name('kiosk.index');
Route::post('/turnos', [KioskController::class, 'store'])
    ->middleware('throttle:30,1')
    ->name('turns.store');
Route::get('/turnos/{turn}/comprobante', [KioskController::class, 'ticket'])->name('turns.ticket');
Route::get('/turnos/{turn}/estado', [KioskController::class, 'status'])->name('turns.status');

Route::get('/pantalla', [DisplayController::class, 'index'])->name('display');
Route::get('/pantalla/estado', [DisplayController::class, 'state'])->name('display.state');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1')
        ->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::prefix('agente')->name('agent.')->group(function (): void {
        Route::get('/', [AgentController::class, 'index'])->name('index');
        Route::post('/llamar', [AgentController::class, 'callNext'])->name('call');
        Route::post('/turnos/{turn}/iniciar', [AgentController::class, 'start'])->name('start');
        Route::post('/turnos/{turn}/finalizar', [AgentController::class, 'finish'])->name('finish');
        Route::post('/turnos/{turn}/ausente', [AgentController::class, 'markAbsent'])->name('absent');
    });
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::post('/tramites', [AdminController::class, 'storeService'])->name('services.store');
    Route::patch('/tramites/{service}', [AdminController::class, 'updateService'])->name('services.update');
    Route::post('/ventanillas', [AdminController::class, 'storeCounter'])->name('counters.store');
    Route::patch('/ventanillas/{counter}', [AdminController::class, 'updateCounter'])->name('counters.update');
});
