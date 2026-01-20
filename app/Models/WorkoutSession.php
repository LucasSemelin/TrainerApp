<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkoutSession extends Model
{
    /** @use HasFactory<\Database\Factories\WorkoutSessionFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'workout_id',
        'session_order',
        'name',
        'notes',
    ];

    public function workout(): BelongsTo
    {
        return $this->belongsTo(Workout::class);
    }

    public function exercises(): HasMany
    {
        return $this->hasMany(WorkoutSessionExercise::class)->orderBy('position');
    }

    /**
     * Get the next position for a new exercise in this session
     */
    public function getNextPosition(): int
    {
        return ($this->exercises()->max('position') ?? 0) + 1;
    }
}
