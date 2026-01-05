<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkoutSession extends Model
{
    /** @use HasFactory<\Database\Factories\WorkoutSessionFactory> */
    use HasFactory;

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
}
