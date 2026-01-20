<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Muscle extends Model
{
    /** @use HasFactory<\Database\Factories\MuscleFactory> */
    use HasFactory;

    protected $fillable = ['code'];

    protected $with = ['translations'];

    // Relaciones
    public function translations()
    {
        return $this->hasMany(MuscleTranslation::class);
    }

    public function exercises()
    {
        return $this->belongsToMany(Exercise::class, 'exercise_muscles')
            ->withPivot('muscle_role_id');
    }

    // i18n Helper
    public function label(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        $t = $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->firstWhere('locale', 'es');

        return $t?->label ?? Str::title(str_replace('_', ' ', $this->code));
    }
}
