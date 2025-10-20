<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientWorkoutController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\ExerciseSetController;
use App\Http\Controllers\WorkoutController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
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

/**
 * Exercise Management
 */
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('exercises', [ExerciseController::class, 'index'])->name('exercises.index');
    Route::get('exercises/list', [ExerciseController::class, 'list'])->name('exercises.list');
    Route::get('exercises/search', [ExerciseController::class, 'search'])->name('exercises.search');
    Route::post('exercises', [ExerciseController::class, 'store'])->name('exercises.store');
    Route::delete('exercises/{exercise}', [ExerciseController::class, 'destroy'])->name('exercises.destroy');
});

/**
 * Exercise Sets Management
 */
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('exercise-workouts/{exerciseWorkout}/sets', [ExerciseSetController::class, 'store'])->name('exercise-workouts.sets.store');
    Route::patch('exercise-sets/{set}', [ExerciseSetController::class, 'update'])->name('exercise-sets.update');
    Route::delete('exercise-workouts/{exerciseWorkout}/sets/{set}', [ExerciseSetController::class, 'destroy'])->name('exercise-workouts.sets.destroy');
    Route::delete('exercise-sets/{set}', [ExerciseSetController::class, 'destroy'])->name('exercise-sets.destroy');
});

/**
 * Workout Management
 */
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('workouts', [WorkoutController::class, 'index'])->name('workouts.index');
    Route::post('workouts', [WorkoutController::class, 'store'])->name('workouts.store');
    Route::delete('workouts/{workout}', [WorkoutController::class, 'destroy'])->name('workouts.destroy');

    Route::post('workouts/{workout}/exercises', [WorkoutController::class, 'addExercise'])->name('workouts.exercises.store');
});

/**
 * Client Workout Management
 */
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('clients/{client}/workouts', [ClientWorkoutController::class, 'index'])->name('clients.workouts.index');
    Route::get('clients/{client}/workouts/{workout}', [ClientWorkoutController::class, 'show'])->name('clients.workouts.show');
    Route::post('clients/{client}/workouts', [ClientWorkoutController::class, 'store'])->name('clients.workouts.store');
    Route::delete('clients/{client}/workouts/{workout}', [ClientWorkoutController::class, 'destroy'])->name('clients.workouts.destroy');
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
