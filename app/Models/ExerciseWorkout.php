<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function workout(): BelongsTo
    {
        return $this->belongsTo(Workout::class);
    }

    public function sets(): HasMany
    {
        return $this->hasMany(ExerciseSet::class);
    }
}
