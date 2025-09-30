<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ExerciseCategory extends Model
{
    /** @use HasFactory<\Database\Factories\ExerciseCategoryFactory> */
    use HasFactory;

    protected $fillable = ['type_slug', 'name_slug'];

    // Eager loading de traducciones para evitar N+1 al pedir labels
    protected $with = ['translations'];

    // Relaciones
    public function translations()
    {
        return $this->hasMany(ExerciseCategoryTranslation::class, 'category_id');
    }

    public function exercises()
    {
        return $this->belongsToMany(Exercise::class,  'category_exercise', 'category_id', 'exercise_id');
    }

    // ------- SCOPES -------

    public function scopeType(Builder $q, string $type): Builder
    {
        return $q->where('type_slug', $type);
    }

    public function scopeNamed(Builder $q, string|array $name): Builder
    {
        $names = is_array($name) ? $name : [$name];
        return $q->whereIn('name_slug', $names);
    }

    // ------- i18n Helpers -------

    /**
     * Etiqueta traducida del nombre (según locale actual, con fallback).
     */
    public function label(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        $t = $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->firstWhere('locale', 'es');

        return $t?->name_label ?? Str::title(str_replace('_', ' ', $this->name_slug));
    }

    /**
     * Etiqueta traducida del tipo (según locale actual, con fallback).
     */
    public function typeLabel(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        $t = $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->firstWhere('locale', 'es');

        return $t?->type_label ?? Str::title(str_replace('_', ' ', $this->type_slug));
    }

    /**
     * Serializa la categoría con labels y slugs (útil para API/Views).
     */
    public function toLabeledArray(?string $locale = null): array
    {
        return [
            'type'       => $this->type_slug,
            'name'       => $this->name_slug,
            'type_label' => $this->typeLabel($locale),
            'name_label' => $this->label($locale),
        ];
    }
}
