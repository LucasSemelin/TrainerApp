<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExerciseInstructionSet extends Model
{
    /** @use HasFactory<\Database\Factories\ExerciseInstructionSetFactory> */
    use HasFactory;

    protected $fillable = [
        'exercise_id',
        'locale',
        'setup',
        'execution_steps',
        'common_mistakes',
        'cues',
        'breathing',
    ];

    protected $casts = [
        'execution_steps' => 'array',
    ];

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }
}
