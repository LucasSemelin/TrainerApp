<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Exercise extends Model
{
    /** @use HasFactory<\Database\Factories\ExerciseFactory> */
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'image_path',
        'metadata',
    ];

    protected $casts = ['metadata' => 'array'];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ExerciseCategory::class, 'category_exercise', 'exercise_id', 'category_id');
    }

    public function names(): HasMany
    {
        return $this->hasMany(ExerciseName::class);
    }

    public function muscles(): BelongsToMany
    {
        return $this->belongsToMany(Muscle::class, 'exercise_muscles')
            ->withPivot('muscle_role_id')
            ->using(ExerciseMuscle::class);
    }

    public function equipment(): BelongsToMany
    {
        return $this->belongsToMany(Equipment::class, 'exercise_equipment')
            ->using(ExerciseEquipment::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'exercise_tags')
            ->using(ExerciseTag::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(ExerciseMedia::class);
    }

    public function instructionSets(): HasMany
    {
        return $this->hasMany(ExerciseInstructionSet::class);
    }

    public function primaryName(string $locale = 'es'): ?ExerciseName
    {
        return $this->names()
            ->where('locale', $locale)
            ->where('is_primary', true)
            ->first();
    }

    /**
     * Obtiene todos los nombres del ejercicio para un locale
     */
    public function getAllNames(string $locale = 'es'): array
    {
        return $this->names()
            ->where('locale', $locale)
            ->pluck('name')
            ->toArray();
    }

    /**
     * Scope para buscar ejercicios por nombre
     */
    public function scopeSearchByName(Builder $query, string $search, string $locale = 'es'): Builder
    {
        $normalizedSearch = ExerciseName::normalizeForSearch($search);

        return $query->whereHas('names', function ($q) use ($normalizedSearch, $locale) {
            $q->where('locale', $locale)
                ->where('name_normalized', 'LIKE', "%{$normalizedSearch}%");
        });
    }

    /**
     * Scope para búsqueda más flexible (palabras separadas)
     */
    public function scopeSearchByNameFlexible(Builder $query, string $search, string $locale = 'es'): Builder
    {
        $words = explode(' ', ExerciseName::normalizeForSearch($search));

        return $query->whereHas('names', function ($q) use ($words, $locale) {
            $q->where('locale', $locale);

            foreach ($words as $word) {
                if (strlen($word) >= 2) { // Solo palabras de 2+ caracteres
                    $q->where('name_normalized', 'LIKE', "%{$word}%");
                }
            }
        });
    }

    protected static function booted()
    {
        static::creating(function (Exercise $m) {
            if (empty($m->slug) && ! empty($m->name)) {
                $m->slug = Str::slug($m->name);
            }
        });

        // Crear nombre primario automáticamente
        static::created(function (Exercise $model) {
            if (! empty($model->name)) {
                $model->names()->create([
                    'name' => $model->name,
                    'locale' => 'es',
                    'is_primary' => true,
                ]);
            }
        });
    }

    /**
     * Filtra por categoría: type_slug = $type y name_slug ∈ $names
     * Ej: Exercise::withCategory('equipment', ['barbell','dumbbell'])
     */
    public function scopeWithCategory(Builder $q, string $type, string|array $names): Builder
    {
        $names = is_array($names) ? $names : [$names];

        return $q->whereHas('categories', function ($c) use ($type, $names) {
            $c->where('type_slug', $type)
                ->whereIn('name_slug', $names);
        });
    }

    /**
     * Filtra por varias condiciones de categorías (todas deben cumplirse).
     * Ej:
     *  Exercise::withAllCategories([
     *      ['muscle_group','chest'],
     *      ['movement_pattern','push'],
     *      ['equipment',['barbell','dumbbell']],
     *  ])
     */
    public function scopeWithAllCategories(Builder $q, array $pairs): Builder
    {
        foreach ($pairs as $pair) {
            [$type, $names] = $pair;
            $q->withCategory($type, $names);
        }

        return $q;
    }

    /**
     * Devuelve categorías agrupadas por type_slug: ['equipment'=>['barbell','...'], ...]
     * Útil para serializar o mostrar en API.
     */
    public function categorySlugsGrouped(): array
    {
        return $this->categories
            ->groupBy('type_slug')
            ->map(fn ($grp) => $grp->pluck('name_slug')->values()->all())
            ->toArray();
    }
}
