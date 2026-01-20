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

    public const STATUS_DRAFT = 'draft';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'name',
        'trainer_id',
        'client_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }

    protected static function booted(): void
    {
        // When a workout is activated, archive other active workouts for the same client-trainer pair
        static::saving(function (Workout $workout) {
            if ($workout->status === self::STATUS_ACTIVE && $workout->isDirty('status')) {
                static::where('client_id', $workout->client_id)
                    ->where('trainer_id', $workout->trainer_id)
                    ->where('id', '!=', $workout->id)
                    ->where('status', self::STATUS_ACTIVE)
                    ->update(['status' => self::STATUS_ARCHIVED]);
            }
        });

        // Create a default session when a workout is created
        static::created(function (Workout $workout) {
            $workout->sessions()->create([
                'session_order' => 1,
                'name' => 'Día 1',
            ]);
        });
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(WorkoutSession::class)->orderBy('session_order');
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
     * Activate this workout (marks it as current for the client-trainer pair)
     */
    public function activate(): bool
    {
        return $this->update(['status' => self::STATUS_ACTIVE]);
    }

    /**
     * Archive this workout
     */
    public function archive(): bool
    {
        return $this->update(['status' => self::STATUS_ARCHIVED]);
    }

    /**
     * Check if this workout is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if this workout is a draft
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Check if this workout is archived
     */
    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVED;
    }

    /**
     * Scope for active workouts
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for draft workouts
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Scope for archived workouts
     */
    public function scopeArchived($query)
    {
        return $query->where('status', self::STATUS_ARCHIVED);
    }

    /**
     * Scope for workouts by client
     */
    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Scope for workouts by trainer
     */
    public function scopeForTrainer($query, $trainerId)
    {
        return $query->where('trainer_id', $trainerId);
    }

    /**
     * Get the active workout for a specific client-trainer pair
     */
    public static function activeFor($clientId, $trainerId = null): ?self
    {
        return static::forClient($clientId)
            ->when($trainerId, fn ($q) => $q->forTrainer($trainerId))
            ->active()
            ->first();
    }
}
