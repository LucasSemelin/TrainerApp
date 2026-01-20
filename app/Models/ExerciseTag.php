<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ExerciseTag extends Pivot
{
    /** @use HasFactory<\Database\Factories\ExerciseTagFactory> */
    use HasFactory;

    protected $table = 'exercise_tags';

    public $timestamps = false;

    public $incrementing = false;

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }
}
