<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkoutSessionExercise extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'workout_session_id',
        'exercise_id',
        'position',
        'notes',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(WorkoutSession::class, 'workout_session_id');
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function sets(): HasMany
    {
        return $this->hasMany(WorkoutSessionExerciseSet::class)->orderBy('set_order');
    }

    /**
     * Get the next set order for a new set in this exercise
     */
    public function getNextSetOrder(): int
    {
        return ($this->sets()->max('set_order') ?? 0) + 1;
    }
}
