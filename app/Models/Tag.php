<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tag extends Model
{
    /** @use HasFactory<\Database\Factories\TagFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(TagTranslation::class);
    }

    public function exercises(): BelongsToMany
    {
        return $this->belongsToMany(Exercise::class, 'exercise_tags')
            ->using(ExerciseTag::class);
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
