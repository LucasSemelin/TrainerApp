<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'gender' => 'nullable|in:male,female,other',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->profile()->create([
            'first_name' => $request->first_name,
            'last_name' => '',
            'gender' => $request->gender,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return to_route('user.createRole');
    }

    /**
     * Show the role selection page.
     */
    public function createRole(): Response
    {
        return Inertia::render('auth/RegisterRole');
    }

    /**
     * Handle role selection for the authenticated user.
     */
    public function storeRole(Request $request): RedirectResponse
    {
        $request->validate([
            'role' => 'required|string|in:trainer,client',
        ]);

        $user = Auth::user();
        $user->assignRole($request->role);

        return to_route('dashboard');
    }
}
