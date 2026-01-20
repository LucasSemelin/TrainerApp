<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkoutSessionExerciseSet extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'workout_session_exercise_id',
        'set_order',
        'target_reps',
        'target_weight',
        'target_rpe',
        'rest_seconds',
        'tempo',
    ];

    protected function casts(): array
    {
        return [
            'target_reps' => 'integer',
            'target_weight' => 'decimal:2',
            'target_rpe' => 'decimal:1',
            'rest_seconds' => 'integer',
        ];
    }

    public function sessionExercise(): BelongsTo
    {
        return $this->belongsTo(WorkoutSessionExercise::class, 'workout_session_exercise_id');
    }
}
