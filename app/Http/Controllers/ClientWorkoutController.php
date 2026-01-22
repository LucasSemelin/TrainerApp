<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Workout;

class ClientWorkoutController extends Controller
{
    public function index(User $client)
    {
        return inertia('Clients/PageWorkoutIndex', [
            'client' => $client,
            'workouts' => $client->myWorkouts()
                ->orderByRaw("CASE WHEN status = 'active' THEN 0 WHEN status = 'draft' THEN 1 ELSE 2 END")
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }

    public function show(User $client, Workout $workout)
    {
        // Load sessions with their exercises and sets
        $sessions = $workout->sessions()
            ->with([
                'exercises.exercise.categories.translations' => function ($query) {
                    $query->where('locale', 'es');
                },
                'exercises.sets' => function ($query) {
                    $query->orderBy('set_order');
                },
            ])
            ->get();

        // Transform sessions to include category information for each exercise
        $sessions = $sessions->map(function ($session) {
            $exercises = $session->exercises->map(function ($sessionExercise) {
                // Get main categories (muscle_group primarily)
                $mainCategories = $sessionExercise->exercise->categories
                    ->where('type_slug', 'muscle_group')
                    ->map(fn($category) => $category->label('es'))
                    ->filter()
                    ->values()
                    ->toArray();

                // If no muscle_group, use movement_pattern as alternative
                if (empty($mainCategories)) {
                    $mainCategories = $sessionExercise->exercise->categories
                        ->where('type_slug', 'movement_pattern')
                        ->map(fn($category) => $category->label('es'))
                        ->filter()
                        ->values()
                        ->toArray();
                }

                // Convert to array and add categories
                $exerciseArray = $sessionExercise->toArray();
                $exerciseArray['exercise']['categories'] = $mainCategories;

                return $exerciseArray;
            });

            $sessionArray = $session->toArray();
            $sessionArray['exercises'] = $exercises;

            return $sessionArray;
        });

        return inertia('Clients/PageWorkoutShow', [
            'client' => $client,
            'workout' => $workout,
            'sessions' => $sessions,
        ]);
    }

    public function activate(User $client, Workout $workout)
    {
        // Verify the workout belongs to the client
        if ($workout->client_id !== $client->id) {
            abort(403, 'Esta rutina no pertenece al cliente especificado.');
        }

        $workout->activate();

        return redirect()->back()->with('success', 'Rutina activada exitosamente.');
    }

    public function archive(User $client, Workout $workout)
    {
        // Verify the workout belongs to the client
        if ($workout->client_id !== $client->id) {
            abort(403, 'Esta rutina no pertenece al cliente especificado.');
        }

        $workout->archive();

        return redirect()->back()->with('success', 'Rutina archivada exitosamente.');
    }

    public function unarchive(User $client, Workout $workout)
    {
        // Verify the workout belongs to the client
        if ($workout->client_id !== $client->id) {
            abort(403, 'Esta rutina no pertenece al cliente especificado.');
        }

        $workout->update(['status' => Workout::STATUS_DRAFT]);

        return redirect()->back()->with('success', 'Rutina desarchivada exitosamente.');
    }
}
