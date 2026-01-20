<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientWorkoutController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\WorkoutController;
use App\Http\Controllers\WorkoutSessionController;
use App\Http\Controllers\WorkoutSessionExerciseController;
use App\Http\Controllers\WorkoutSessionExerciseSetController;
use Illuminate\Support\Facades\Route;
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

    // Activar una rutina
    Route::patch('clients/{client}/workouts/{workout}/activate', [ClientWorkoutController::class, 'activate'])->name('clients.workouts.activate');

    // Archivar una rutina
    Route::patch('clients/{client}/workouts/{workout}/archive', [ClientWorkoutController::class, 'archive'])->name('clients.workouts.archive');

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
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('exercises', [ExerciseController::class, 'index'])->name('exercises.index');
    Route::get('exercises/list', [ExerciseController::class, 'list'])->name('exercises.list');
    Route::get('exercises/search', [ExerciseController::class, 'search'])->name('exercises.search');
    Route::post('exercises', [ExerciseController::class, 'store'])->name('exercises.store');
    Route::get('exercises/{exercise}', [ExerciseController::class, 'show'])->name('exercises.show');
    Route::put('exercises/{exercise}', [ExerciseController::class, 'update'])->name('exercises.update');
    Route::patch('exercises/{exercise}/categories', [ExerciseController::class, 'updateCategories'])->name('exercises.categories.update');
    Route::patch('exercises/{exercise}/equipment', [ExerciseController::class, 'updateEquipment'])->name('exercises.equipment.update');
    Route::patch('exercises/{exercise}/tags', [ExerciseController::class, 'updateTags'])->name('exercises.tags.update');
    Route::post('exercises/{exercise}/media', [ExerciseController::class, 'storeMedia'])->name('exercises.media.store');
    Route::delete('exercises/{exercise}/media/{media}', [ExerciseController::class, 'destroyMedia'])->name('exercises.media.destroy');
    Route::put('exercises/{exercise}/instructions', [ExerciseController::class, 'storeOrUpdateInstructions'])->name('exercises.instructions.update');
    Route::delete('exercises/{exercise}', [ExerciseController::class, 'destroy'])->name('exercises.destroy');
});

/**
 * Workout Management
 */
Route::middleware(['auth', 'verified', 'ensure.role'])->group(function () {
    Route::get('workouts', [WorkoutController::class, 'index'])->name('workouts.index');
    Route::post('workouts', [WorkoutController::class, 'store'])->name('workouts.store');
    Route::delete('workouts/{workout}', [WorkoutController::class, 'destroy'])->name('workouts.destroy');
});

/**
 * Workout Session Management
 */
Route::middleware(['auth', 'verified', 'ensure.role'])->group(function () {
    // Sessions
    Route::post('workouts/{workout}/sessions', [WorkoutSessionController::class, 'store'])->name('workouts.sessions.store');
    Route::patch('workout-sessions/{session}', [WorkoutSessionController::class, 'update'])->name('workout-sessions.update');
    Route::delete('workout-sessions/{session}', [WorkoutSessionController::class, 'destroy'])->name('workout-sessions.destroy');
    Route::patch('workouts/{workout}/sessions/reorder', [WorkoutSessionController::class, 'reorder'])->name('workouts.sessions.reorder');

    // Session Exercises
    Route::post('workout-sessions/{session}/exercises', [WorkoutSessionExerciseController::class, 'store'])->name('workout-sessions.exercises.store');
    Route::patch('workout-session-exercises/{sessionExercise}', [WorkoutSessionExerciseController::class, 'update'])->name('workout-session-exercises.update');
    Route::delete('workout-session-exercises/{sessionExercise}', [WorkoutSessionExerciseController::class, 'destroy'])->name('workout-session-exercises.destroy');
    Route::patch('workout-sessions/{session}/exercises/reorder', [WorkoutSessionExerciseController::class, 'reorder'])->name('workout-sessions.exercises.reorder');

    // Session Exercise Sets
    Route::post('workout-session-exercises/{sessionExercise}/sets', [WorkoutSessionExerciseSetController::class, 'store'])->name('workout-session-exercises.sets.store');
    Route::patch('workout-session-exercise-sets/{set}', [WorkoutSessionExerciseSetController::class, 'update'])->name('workout-session-exercise-sets.update');
    Route::delete('workout-session-exercise-sets/{set}', [WorkoutSessionExerciseSetController::class, 'destroy'])->name('workout-session-exercise-sets.destroy');
});

/**
 * Invitation Management (no auth required - token-based)
 */
Route::get('invitations/accept/{token}', [InvitationController::class, 'accept'])->name('invitations.accept');
Route::get('invitations/reject/{token}', [InvitationController::class, 'reject'])->name('invitations.reject');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
