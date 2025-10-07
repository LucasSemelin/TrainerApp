<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workout extends Model
{
    /** @use HasFactory<\Database\Factories\WorkoutFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'trainer_id',
        'client_id',
    ];

    public function exerciseWorkouts(): HasMany
    {
        return $this->hasMany(ExerciseWorkout::class);
    }
}
