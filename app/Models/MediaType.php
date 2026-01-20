<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MediaType extends Model
{
    /** @use HasFactory<\Database\Factories\MediaTypeFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'code',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(MediaTypeTranslation::class);
    }

    public function exerciseMedia(): HasMany
    {
        return $this->hasMany(ExerciseMedia::class);
    }

    public function label(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();

        $translation = $this->translations
            ->where('locale', $locale)
            ->first();

        if ($translation) {
            return $translation->label;
        }

        // Fallback to any available translation
        return $this->translations->first()?->label;
    }
}
