<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileIntake extends Model
{
    /** @use HasFactory<\Database\Factories\ProfileIntakeFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'answers' => 'array',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
