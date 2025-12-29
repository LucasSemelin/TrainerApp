<?php

namespace App\Actions;

use App\Mail\WelcomeClientMail;
use App\Models\Profile;
use App\Models\User;
use App\Notifications\TrainerInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class CreateClient
{
    public function handle(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'first_name' => 'required|string|max:255',
            'confirm_existing' => 'nullable|boolean',
        ]);

        $email = $validated['email'];
        $firstName = $validated['first_name'];
        $confirmExisting = $validated['confirm_existing'] ?? false;
        $lastName = '';
        $gender = null;

        // Implementation for creating a client
        $trainer = Auth::user();

        // Check if user already exists
        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            // If user exists but no confirmation, return error asking for confirmation
            if (!$confirmExisting) {
                return [
                    'user_exists' => true,
                    'user' => [
                        'email' => $existingUser->email,
                        'name' => $existingUser->profile ? $existingUser->profile->first_name . ' ' . $existingUser->profile->last_name : $existingUser->email,
                    ],
                ];
            }

            // User exists and confirmed, just create the relationship
            $user = $existingUser;

            // Check if relationship already exists
            if ($trainer->clients()->where('client_id', $user->id)->exists()) {
                throw new \Exception('Este usuario ya es tu alumno.');
            }
        } else {
            // create new user (password is nullable; we send a reset link so client sets it)
            $user = User::create([
                'email' => $email,
                'password' => null,
            ]);

            // create profile
            Profile::create([
                'user_id' => $user->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'gender' => $gender,
            ]);

            // assign roles/permissions
            $user->assignRole('client');
        }

        // Generate invitation token
        $invitationToken = Str::random(60);

        // assign client to trainer with 'pending' status
        $trainer->clients()->attach($user->id, [
            'status' => 'pending',
            'invitation_token' => $invitationToken,
            'invited_at' => now(),
        ]);

        // Send trainer invitation notification
        $user->notify(new TrainerInvitation($trainer, $invitationToken));

        // send password reset link using Laravel's Password facade
        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status === Password::RESET_LINK_SENT) {
            Log::info("CreateClient: Password reset link sent to {$user->email}");
        } else {
            Log::warning("CreateClient: Failed to send password reset link to {$user->email}: {$status}");
        }

        return $user;
    }
}
