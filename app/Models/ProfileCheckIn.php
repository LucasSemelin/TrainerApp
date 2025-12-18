<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileCheckIn extends Model
{
    /** @use HasFactory<\Database\Factories\ProfileCheckInFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'context_changes' => 'array',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
