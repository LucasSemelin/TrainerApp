<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExerciseWorkout extends Model
{
    /** @use HasFactory<\Database\Factories\ExerciseWorkoutFactory> */
    use HasFactory;

    protected $fillable = [
        'workout_id',
        'exercise_id',
        'sets',
        'min_reps',
        'max_reps',
        'weight',
    ];

    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }

    public function workout()
    {
        return $this->belongsTo(Workout::class);
    }
}
