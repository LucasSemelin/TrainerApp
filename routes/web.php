<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientWorkoutController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\ExerciseSetController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\WorkoutController;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Socialite;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');


/**
 * PANTALLAS DE ONBOARDING
 */

Route::middleware(['auth'])->group(function () {
    Route::get('register/role', [App\Http\Controllers\Auth\RegisteredUserController::class, 'createRole'])->name('user.createRole');
    Route::post('register/role', [App\Http\Controllers\Auth\RegisteredUserController::class, 'storeRole'])->name('user.storeRole');
});

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified', 'ensure.role'])->name('dashboard');

/**
 * Administración de alumnos
 * perfil: entrenador
 */
Route::middleware(['auth', 'verified', 'ensure.role'])->group(function () {
    // Listado de alumnos
    Route::get('clients', [ClientController::class, 'index'])->name('clients.index');

    // Guardar alumno
    Route::post('clients', [ClientController::class, 'store'])->name('clients.store');

    // Mostrar perfil del alumno
    Route::get('clients/{client}', [ClientController::class, 'show'])->name('clients.show');

    // ELiminar relación entrenador-alumno
    Route::delete('clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');

    // Rutinas del alumno
    Route::get('clients/{client}/workouts', [ClientWorkoutController::class, 'index'])->name('clients.workouts.index');

    // Rutina específica del alumno
    Route::get('clients/{client}/workouts/{workout}', [ClientWorkoutController::class, 'show'])->name('clients.workouts.show');

    // Crear nueva rutina para el alumno
    Route::post('clients/{client}/workouts', [ClientWorkoutController::class, 'store'])->name('clients.workouts.store');

    // Marcar una rutina como actual
    Route::patch('clients/{client}/workouts/{workout}/make-current', [ClientWorkoutController::class, 'makeCurrent'])->name('clients.workouts.make-current');

    // Eliminar rutina del alumno
    Route::delete('clients/{client}/workouts/{workout}', [ClientWorkoutController::class, 'destroy'])->name('clients.workouts.destroy');
});


/**
 * Profile Management
 */
Route::middleware(['auth', 'verified', 'ensure.role'])->group(function () {
    Route::patch('profiles/{profile}/gender', [App\Http\Controllers\ProfileController::class, 'updateGender'])->name('profiles.update-gender');
    Route::patch('profiles/{profile}/birthdate', [App\Http\Controllers\ProfileController::class, 'updateBirthdate'])->name('profiles.update-birthdate');
});

/**
 * Exercise Management
 */
Route::middleware(['auth', 'verified', 'ensure.role'])->group(function () {
    Route::get('exercises', [ExerciseController::class, 'index'])->name('exercises.index');
    Route::get('exercises/list', [ExerciseController::class, 'list'])->name('exercises.list');
    Route::get('exercises/search', [ExerciseController::class, 'search'])->name('exercises.search');
    Route::post('exercises', [ExerciseController::class, 'store'])->name('exercises.store');
    Route::get('exercises/{exercise}', [ExerciseController::class, 'show'])->name('exercises.show');
    Route::delete('exercises/{exercise}', [ExerciseController::class, 'destroy'])->name('exercises.destroy');
});

/**
 * Exercise Sets Management
 */
Route::middleware(['auth', 'verified', 'ensure.role'])->group(function () {
    Route::post('exercise-workouts/{exerciseWorkout}/sets', [ExerciseSetController::class, 'store'])->name('exercise-workouts.sets.store');
    Route::patch('exercise-sets/{set}', [ExerciseSetController::class, 'update'])->name('exercise-sets.update');
    Route::delete('exercise-workouts/{exerciseWorkout}/sets/{set}', [ExerciseSetController::class, 'destroy'])->name('exercise-workouts.sets.destroy');
    Route::delete('exercise-sets/{set}', [ExerciseSetController::class, 'destroy'])->name('exercise-sets.destroy');
});

/**
 * Workout Management
 */
Route::middleware(['auth', 'verified', 'ensure.role'])->group(function () {
    Route::get('workouts', [WorkoutController::class, 'index'])->name('workouts.index');
    Route::post('workouts', [WorkoutController::class, 'store'])->name('workouts.store');
    Route::delete('workouts/{workout}', [WorkoutController::class, 'destroy'])->name('workouts.destroy');

    Route::post('workouts/{workout}/exercises', [WorkoutController::class, 'addExercise'])->name('workouts.exercises.store');
});

/**
 * Client Workout Management
 */


use App\Http\Controllers\AiController;

Route::post('ai/prompt', [AiController::class, 'handlePrompt'])
    ->middleware(['auth', 'verified', 'ensure.role'])->name('ai.prompt');

/**
 * Invitation Management (no auth required - token-based)
 */
Route::get('invitations/accept/{token}', [InvitationController::class, 'accept'])->name('invitations.accept');
Route::get('invitations/reject/{token}', [InvitationController::class, 'reject'])->name('invitations.reject');

// Route::post('ai/prompt/parse', [AiController::class, 'parse'])
//     ->middleware(['auth', 'verified'])->name('ai.prompt.parse');

// Route::post('ai/prompt/execute', [AiController::class, 'execute'])
//     ->middleware(['auth', 'verified'])->name('ai.prompt.execute');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
