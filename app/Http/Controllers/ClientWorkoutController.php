<?php

namespace App\Http\Controllers;

use App\Models\ExerciseWorkout;
use App\Models\User;
use App\Models\Workout;
use Illuminate\Http\Request;

class ClientWorkoutController extends Controller
{

    public function index(User $client)
    {

        return inertia('PageClientWorkoutIndex', [
            'client' => $client,
            'workouts' => $client->myWorkouts()->get(),
        ]);
    }

    public function show(User $client, Workout $workout)
    {
        $exercises = ExerciseWorkout::where('workout_id', $workout->id)
            ->with([
                'exercise.categories.translations' => function ($query) {
                    $query->where('locale', 'es');
                },
                'sets' => function ($query) {
                    $query->orderBy('set_number');
                }
            ])
            ->get();

        // Transform exercises to include category information
        $exercises = $exercises->map(function ($exerciseWorkout) {
            // Get main categories (muscle_group primarily)
            $mainCategories = $exerciseWorkout->exercise->categories
                ->where('type_slug', 'muscle_group')
                ->map(function ($category) {
                    return $category->label('es');
                })
                ->filter()
                ->values()
                ->toArray();

            // If no muscle_group, use movement_pattern as alternative
            if (empty($mainCategories)) {
                $mainCategories = $exerciseWorkout->exercise->categories
                    ->where('type_slug', 'movement_pattern')
                    ->map(function ($category) {
                        return $category->label('es');
                    })
                    ->filter()
                    ->values()
                    ->toArray();
            }

            // Convert to array and add categories
            $exerciseArray = $exerciseWorkout->toArray();
            $exerciseArray['exercise']['categories'] = $mainCategories;

            return $exerciseArray;
        });

        return inertia('PageClientWorkoutShow', [
            'client' => $client,
            'workout' => $workout,
            'exercises' => $exercises,
        ]);
    }
}
