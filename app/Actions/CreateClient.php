<?php

namespace App\Actions;

use App\Mail\WelcomeClientMail;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class CreateClient
{
    public function handle(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'first_name' => 'required|string|max:255',
            'last_name' => 'string|max:255|nullable',
            'gender' => 'in:male,female,other|nullable',
        ]);

        $email = $validated['email'];
        $firstName = $validated['first_name'];
        $lastName = $validated['last_name'];
        $gender = $validated['gender'] ?? null;

        // Implementation for creating a client
        $trainer = Auth::user();

        // create user (password is nullable; we send a reset link so client sets it)
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
        // assign client to trainer
        $trainer->clients()->attach($user->id);

        // send password reset link using Laravel's Password facade
        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status === Password::RESET_LINK_SENT) {
            Log::info("CreateClient: Password reset link sent to {$user->email}");
        } else {
            Log::warning("CreateClient: Failed to send password reset link to {$user->email}: {$status}");
        }

        // Send welcome email summarizing next steps (reset link sent or failed)
        // try {
        //     Mail::to($user->email)->send(new WelcomeClientMail($user, $status === Password::RESET_LINK_SENT));
        // } catch (\Throwable $e) {
        //     Log::error("CreateClient: Failed to send WelcomeClientMail to {$user->email}: {$e->getMessage()}");
        // }
        return $user;
    }
}
