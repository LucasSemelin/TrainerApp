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
            'sets' => 'required|integer|min:1|max:20',
            'min_reps' => 'required|integer|min:1|max:200',
            'max_reps' => 'nullable|integer|min:1|max:200|gte:min_reps',
            'weight' => 'required|numeric|min:0|max:1000',
        ]);

        // Create the exercise workout relationship
        ExerciseWorkout::create([
            'workout_id' => $workout->id,
            'exercise_id' => $validated['exercise_id'],
            'sets' => $validated['sets'],
            'min_reps' => $validated['min_reps'],
            'max_reps' => $validated['max_reps'],
            'weight' => $validated['weight'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ejercicio agregado exitosamente'
        ]);
    }
}
