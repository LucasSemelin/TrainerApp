<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function updateGender(Request $request, Profile $profile)
    {
        $validated = $request->validate([
            'gender' => ['required', Rule::in(['male', 'female', 'other'])],
        ]);

        $profile->update([
            'gender' => $validated['gender'],
        ]);

        return back();
    }

    public function updateBirthdate(Request $request, Profile $profile)
    {
        $validated = $request->validate([
            'date_of_birth' => ['required', 'date', 'before:today'],
        ]);

        $profile->update([
            'date_of_birth' => $validated['date_of_birth'],
        ]);

        return back();
    }
}
