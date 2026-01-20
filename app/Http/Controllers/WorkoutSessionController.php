<?php

namespace App\Http\Controllers;

use App\Models\Workout;
use App\Models\WorkoutSession;
use Illuminate\Http\Request;

class WorkoutSessionController extends Controller
{
    public function store(Request $request, Workout $workout)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
        ]);

        $nextOrder = $workout->sessions()->max('session_order') + 1 ?? 1;

        $session = $workout->sessions()->create([
            'session_order' => $nextOrder,
            'name' => $validated['name'] ?? "Día {$nextOrder}",
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sesión creada exitosamente',
            'session' => $session,
        ]);
    }

    public function update(Request $request, WorkoutSession $session)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
        ]);

        $session->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Sesión actualizada exitosamente',
            'session' => $session->fresh(),
        ]);
    }

    public function destroy(WorkoutSession $session)
    {
        // Don't allow deleting the last session
        $sessionCount = $session->workout->sessions()->count();
        if ($sessionCount <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar la última sesión de la rutina',
            ], 422);
        }

        $session->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesión eliminada exitosamente',
        ]);
    }

    public function reorder(Request $request, Workout $workout)
    {
        $validated = $request->validate([
            'sessions' => 'required|array',
            'sessions.*.id' => 'required|uuid|exists:workout_sessions,id',
            'sessions.*.session_order' => 'required|integer|min:1',
        ]);

        foreach ($validated['sessions'] as $sessionData) {
            WorkoutSession::where('id', $sessionData['id'])
                ->where('workout_id', $workout->id)
                ->update(['session_order' => $sessionData['session_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sesiones reordenadas exitosamente',
        ]);
    }
}
