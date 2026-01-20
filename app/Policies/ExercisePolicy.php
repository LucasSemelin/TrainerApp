<?php

namespace App\Policies;

use App\Models\Exercise;
use App\Models\User;

class ExercisePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Allow all authenticated users to view exercise list
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Exercise $exercise): bool
    {
        return true; // Allow all authenticated users to view exercises
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('trainer');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Exercise $exercise): bool
    {
        \Log::info('ExercisePolicy::update called', [
            'user_id' => $user?->id,
            'exercise_id' => $exercise->id,
            'returning' => true,
        ]);

        return true; // Allow all authenticated users to update exercises
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Exercise $exercise): bool
    {
        return $user->hasRole('trainer');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Exercise $exercise): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Exercise $exercise): bool
    {
        return false;
    }
}
