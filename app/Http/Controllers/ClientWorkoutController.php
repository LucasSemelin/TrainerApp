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
            ->with('exercise')
            ->get();

        return inertia('PageClientWorkoutShow', [
            'client' => $client,
            'workout' => $workout,
            'exercises' => $exercises,
        ]);
    }
}
