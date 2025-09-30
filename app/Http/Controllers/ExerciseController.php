<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use Illuminate\Http\Request;

class ExerciseController extends Controller
{
    public function index()
    {
        //
    }

    public function list()
    {
        $exercises = Exercise::all();

        return response()->json($exercises);
    }
}
