<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workout extends Model
{
    /** @use HasFactory<\Database\Factories\WorkoutFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'trainer_id',
        'client_id',
        'is_current',
    ];

    protected function casts(): array
    {
        return [
            'is_current' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Cuando se marca una rutina como actual, desmarcar las demás del mismo cliente
        static::saving(function (Workout $workout) {
            if ($workout->is_current && $workout->isDirty('is_current')) {
                static::where('client_id', $workout->client_id)
                    ->where('id', '!=', $workout->id)
                    ->update(['is_current' => false]);
            }
        });
    }

    public function exerciseWorkouts(): HasMany
    {
        return $this->hasMany(ExerciseWorkout::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    /**
     * Marcar esta rutina como actual para el cliente
     */
    public function makeCurrentForClient(): bool
    {
        return $this->update(['is_current' => true]);
    }

    /**
     * Scope para obtener la rutina actual de un cliente
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    /**
     * Scope para obtener rutinas por cliente
     */
    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Obtener la rutina actual de un cliente específico
     */
    public static function currentForClient($clientId): ?self
    {
        return static::forClient($clientId)->current()->first();
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(WorkoutSession::class)->orderBy('session_order');
    }
}
