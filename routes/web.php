<?php

use App\Http\Controllers\ClientController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


/**
 * Client Management
 */
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('clients', [ClientController::class, 'index'])->name('clients.index');
    Route::post('clients', [ClientController::class, 'store'])->name('clients.store');
    Route::delete('clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
});

use App\Http\Controllers\AiController;

Route::post('ai/prompt', [AiController::class, 'handlePrompt'])
    ->middleware(['auth', 'verified'])->name('ai.prompt');

// Route::post('ai/prompt/parse', [AiController::class, 'parse'])
//     ->middleware(['auth', 'verified'])->name('ai.prompt.parse');

// Route::post('ai/prompt/execute', [AiController::class, 'execute'])
//     ->middleware(['auth', 'verified'])->name('ai.prompt.execute');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
