<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExerciseSet extends Model
{
    /** @use HasFactory<\Database\Factories\ExerciseSetFactory> */
    use HasFactory;

    protected $fillable = [
        'exercise_workout_id',
        'set_number',
        'weight',
        'min_reps',
        'max_reps',
        'rest_time_seconds',
        'notes',
    ];

    protected $casts = [
        'weight' => 'float',
        'set_number' => 'integer',
        'min_reps' => 'integer',
        'max_reps' => 'integer',
        'rest_time_seconds' => 'integer',
    ];

    public function exerciseWorkout(): BelongsTo
    {
        return $this->belongsTo(ExerciseWorkout::class);
    }
}
