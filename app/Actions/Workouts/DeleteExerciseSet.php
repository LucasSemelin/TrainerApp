<?php

namespace App\Actions\Workouts;

use App\Models\WorkoutSessionExerciseSet;

class DeleteExerciseSet
{
    public function execute(WorkoutSessionExerciseSet $set): void
    {
        $exerciseId = $set->workout_session_exercise_id;
        $deletedSetOrder = $set->set_order;

        // Eliminar la serie
        $set->delete();

        // Reordenar las series restantes del mismo ejercicio
        WorkoutSessionExerciseSet::where('workout_session_exercise_id', $exerciseId)
            ->where('set_order', '>', $deletedSetOrder)
            ->decrement('set_order');
    }
}
