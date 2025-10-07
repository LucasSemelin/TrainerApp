<?php

namespace App\Http\Controllers;

use App\Models\ExerciseSet;
use App\Models\ExerciseWorkout;
use Illuminate\Http\Request;

class ExerciseSetController extends Controller
{
    public function store(Request $request, ExerciseWorkout $exerciseWorkout)
    {
        $validated = $request->validate([
            'weight' => 'nullable|numeric|min:0',
            'min_reps' => 'required|integer|min:1',
            'max_reps' => 'nullable|integer|min:1|gte:min_reps',
            'rest_time_seconds' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:1000'
        ]);

        // Obtener el siguiente número de serie automáticamente
        $nextSetNumber = ExerciseSet::where('exercise_workout_id', $exerciseWorkout->id)
            ->max('set_number') + 1;

        // Si no hay sets previos, empezar desde 1
        if (is_null($nextSetNumber) || $nextSetNumber === 1) {
            $nextSetNumber = 1;
        }

        $exerciseSet = ExerciseSet::create([
            'exercise_workout_id' => $exerciseWorkout->id,
            'set_number' => $nextSetNumber,
            'weight' => $validated['weight'],
            'min_reps' => $validated['min_reps'],
            'max_reps' => $validated['max_reps'] ?? $validated['min_reps'],
            'rest_time_seconds' => $validated['rest_time_seconds'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Serie creada exitosamente',
            'set' => $exerciseSet->load('exerciseWorkout.exercise')
        ]);
    }

    public function update(Request $request, ExerciseSet $set)
    {
        $validated = $request->validate([
            'weight' => 'nullable|numeric|min:0',
            'min_reps' => 'nullable|integer|min:1',
            'max_reps' => 'nullable|integer|min:1|gte:min_reps',
            'rest_time_seconds' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:1000'
        ]);

        // Solo actualizar los campos que vienen en el request
        $updateData = array_filter($validated, function ($value) {
            return $value !== null;
        });

        $set->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Serie actualizada exitosamente',
            'set' => $set->fresh()
        ]);
    }

    public function destroy(ExerciseSet $set)
    {
        $set->delete();

        return response()->json([
            'success' => true,
            'message' => 'Serie eliminada exitosamente'
        ]);
    }
}
