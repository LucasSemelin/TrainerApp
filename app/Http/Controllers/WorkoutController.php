<?php

namespace App\Http\Controllers;

use App\Models\ExerciseWorkout;
use App\Models\Workout;
use Illuminate\Http\Request;

class WorkoutController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'trainer_id' => 'required|uuid|exists:users,id',
            'client_id' => 'required|uuid|exists:users,id',
        ]);

        $workout = Workout::create($validated);

        // Para Inertia, devolvemos una respuesta JSON simple
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'workout' => $workout,
                'message' => 'Rutina creada exitosamente'
            ]);
        }

        // Si no es una petición AJAX, redirigir
        return redirect()->route('clients.workouts.index', $validated['client_id'])
            ->with('success', 'Rutina creada exitosamente');
    }

    public function addExercise(Request $request, Workout $workout)
    {
        $validated = $request->validate([
            'exercise_id' => 'required|exists:exercises,id',
        ]);

        // Get the next order number for this workout
        $nextOrder = $workout->exerciseWorkouts()->max('order') + 1 ?? 1;

        // Create the exercise workout relationship
        $exerciseWorkout = ExerciseWorkout::create([
            'workout_id' => $workout->id,
            'exercise_id' => $validated['exercise_id'],
            'order' => $nextOrder,
        ]);

        // Load the related exercise data and empty sets
        $exerciseWorkout->load(['exercise', 'sets']);

        return response()->json([
            'success' => true,
            'message' => 'Ejercicio agregado exitosamente',
            'exercise_workout' => $exerciseWorkout,
        ]);
    }
}
