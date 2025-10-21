<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    /** @use HasFactory<\Database\Factories\ProfileFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'gender',
    ];

    protected function firstName(): Attribute
    {
        return new Attribute(
            get: fn ($value) => ucfirst($value),
        );
    }

    protected function lastName(): Attribute
    {
        return new Attribute(
            get: fn ($value) => ucfirst($value),
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
