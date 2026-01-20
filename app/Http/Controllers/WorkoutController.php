<?php

namespace App\Http\Controllers;

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

        // For Inertia, return a simple JSON response
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'workout' => $workout->load('sessions'),
                'message' => 'Rutina creada exitosamente',
            ]);
        }

        // If not an AJAX request, redirect
        return redirect()->route('clients.workouts.index', $validated['client_id'])
            ->with('success', 'Rutina creada exitosamente');
    }

    public function destroy(Workout $workout)
    {
        $clientId = $workout->client_id;
        $workout->delete();

        return redirect()->route('clients.workouts.index', $clientId)
            ->with('success', 'Rutina eliminada exitosamente');
    }
}
