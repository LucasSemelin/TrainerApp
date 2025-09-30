<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryExercise extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryExerciseFactory> */
    use HasFactory;

    protected $fillable = [
        'exercise_id',
        'category_id',
    ];
}
