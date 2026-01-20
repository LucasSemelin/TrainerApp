<?php

namespace App\Http\Controllers;

use App\Models\WorkoutSession;
use App\Models\WorkoutSessionExercise;
use Illuminate\Http\Request;

class WorkoutSessionExerciseController extends Controller
{
    public function store(Request $request, WorkoutSession $session)
    {
        $validated = $request->validate([
            'exercise_id' => 'required|exists:exercises,id',
            'notes' => 'nullable|string|max:2000',
        ]);

        $nextPosition = $session->getNextPosition();

        $sessionExercise = $session->exercises()->create([
            'exercise_id' => $validated['exercise_id'],
            'position' => $nextPosition,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Load the related exercise data with categories and sets
        $sessionExercise->load([
            'exercise.categories.translations' => function ($query) {
                $query->where('locale', 'es');
            },
            'sets' => function ($query) {
                $query->orderBy('set_order');
            },
        ]);

        // Transform to include category information
        $mainCategories = $sessionExercise->exercise->categories
            ->where('type_slug', 'muscle_group')
            ->map(fn ($category) => $category->label('es'))
            ->filter()
            ->values()
            ->toArray();

        // If no muscle_group, use movement_pattern as alternative
        if (empty($mainCategories)) {
            $mainCategories = $sessionExercise->exercise->categories
                ->where('type_slug', 'movement_pattern')
                ->map(fn ($category) => $category->label('es'))
                ->filter()
                ->values()
                ->toArray();
        }

        // Convert to array and add categories
        $exerciseArray = $sessionExercise->toArray();
        $exerciseArray['exercise']['categories'] = $mainCategories;

        return response()->json([
            'success' => true,
            'message' => 'Ejercicio agregado exitosamente',
            'session_exercise' => $exerciseArray,
        ]);
    }

    public function update(Request $request, WorkoutSessionExercise $sessionExercise)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:2000',
        ]);

        $sessionExercise->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Ejercicio actualizado exitosamente',
            'session_exercise' => $sessionExercise->fresh(),
        ]);
    }

    public function destroy(WorkoutSessionExercise $sessionExercise)
    {
        $sessionExercise->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ejercicio eliminado exitosamente',
        ]);
    }

    public function reorder(Request $request, WorkoutSession $session)
    {
        $validated = $request->validate([
            'exercises' => 'required|array',
            'exercises.*.id' => 'required|uuid|exists:workout_session_exercises,id',
            'exercises.*.position' => 'required|integer|min:1',
        ]);

        foreach ($validated['exercises'] as $exerciseData) {
            WorkoutSessionExercise::where('id', $exerciseData['id'])
                ->where('workout_session_id', $session->id)
                ->update(['position' => $exerciseData['position']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ejercicios reordenados exitosamente',
        ]);
    }
}
