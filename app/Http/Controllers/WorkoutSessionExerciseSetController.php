<?php

namespace App\Http\Controllers;

use App\Actions\Workouts\DeleteExerciseSet;
use App\Models\WorkoutSessionExercise;
use App\Models\WorkoutSessionExerciseSet;
use Illuminate\Http\Request;

class WorkoutSessionExerciseSetController extends Controller
{
    public function store(Request $request, WorkoutSessionExercise $sessionExercise)
    {
        $validated = $request->validate([
            'target_reps' => 'nullable|integer|min:1',
            'target_weight' => 'nullable|numeric|min:0',
            'target_rpe' => 'nullable|numeric|min:1|max:10',
            'rest_seconds' => 'nullable|integer|min:0',
            'tempo' => 'nullable|string|max:20',
        ]);

        $nextSetOrder = $sessionExercise->getNextSetOrder();

        $set = $sessionExercise->sets()->create([
            'set_order' => $nextSetOrder,
            'target_reps' => $validated['target_reps'] ?? null,
            'target_weight' => $validated['target_weight'] ?? null,
            'target_rpe' => $validated['target_rpe'] ?? null,
            'rest_seconds' => $validated['rest_seconds'] ?? null,
            'tempo' => $validated['tempo'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Serie creada exitosamente',
            'set' => $set->load('sessionExercise.exercise'),
        ]);
    }

    public function update(Request $request, WorkoutSessionExerciseSet $set)
    {
        $validated = $request->validate([
            'target_reps' => 'nullable|integer|min:1',
            'target_weight' => 'nullable|numeric|min:0',
            'target_rpe' => 'nullable|numeric|min:1|max:10',
            'rest_seconds' => 'nullable|integer|min:0',
            'tempo' => 'nullable|string|max:20',
        ]);

        $set->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Serie actualizada exitosamente',
            'set' => $set->fresh(),
        ]);
    }

    public function destroy(WorkoutSessionExerciseSet $set, DeleteExerciseSet $deleteExerciseSet)
    {
        $deleteExerciseSet->execute($set);

        return response()->json([
            'success' => true,
            'message' => 'Serie eliminada exitosamente',
        ]);
    }
}
