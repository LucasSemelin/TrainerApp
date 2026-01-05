<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExerciseMedia extends Model
{
    /** @use HasFactory<\Database\Factories\ExerciseMediaFactory> */
    use HasFactory;

    protected $fillable = [
        'exercise_id',
        'media_type_id',
        'url',
        'provider',
        'is_primary',
        'locale',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function mediaType(): BelongsTo
    {
        return $this->belongsTo(MediaType::class);
    }
}
