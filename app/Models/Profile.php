<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Profile extends Model
{
    /** @use HasFactory<\Database\Factories\ProfileFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'gender',
        'date_of_birth',
    ];

    protected function firstName(): Attribute
    {
        return new Attribute(
            get: fn($value) => ucfirst($value),
        );
    }

    protected function lastName(): Attribute
    {
        return new Attribute(
            get: fn($value) => ucfirst($value),
        );
    }

    protected function gender(): Attribute
    {
        return new Attribute(
            get: fn($value) => match ($value) {
                'male' => 'Masculino',
                'female' => 'Femenino',
                'other' => 'Otro',
                default => $value,
            },
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function intake(): HasMany
    {
        return $this->hasMany(ProfileIntake::class);
    }

    public function checkIns(): HasMany
    {
        return $this->hasMany(ProfileCheckIn::class);
    }

    public function lastCheckIn(): HasOne
    {
        return $this->hasOne(ProfileCheckIn::class)->latestOfMany();
    }
}
