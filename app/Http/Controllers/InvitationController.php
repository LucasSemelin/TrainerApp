<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvitationController extends Controller
{
    public function accept(Request $request, string $token)
    {
        $invitation = DB::table('client_trainer')
            ->where('invitation_token', $token)
            ->where('status', 'pending')
            ->first();

        if (!$invitation) {
            return redirect('/')->with('error', 'Invitación no válida o ya procesada.');
        }

        // Update status to active
        DB::table('client_trainer')
            ->where('invitation_token', $token)
            ->update([
                'status' => 'active',
                'accepted_at' => now(),
                'invitation_token' => null, // Clear token after use
            ]);

        return redirect('/dashboard')->with('success', '¡Has aceptado la invitación! Ya puedes comenzar a entrenar.');
    }

    public function reject(Request $request, string $token)
    {
        $invitation = DB::table('client_trainer')
            ->where('invitation_token', $token)
            ->where('status', 'pending')
            ->first();

        if (!$invitation) {
            return redirect('/')->with('error', 'Invitación no válida o ya procesada.');
        }

        // Delete the relationship
        DB::table('client_trainer')
            ->where('invitation_token', $token)
            ->delete();

        return redirect('/')->with('success', 'Has rechazado la invitación.');
    }
}
