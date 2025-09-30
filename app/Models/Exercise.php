<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Exercise extends Model
{
    /** @use HasFactory<\Database\Factories\ExerciseFactory> */
    use HasFactory;

    protected $fillable = ['slug', 'name', 'description', 'metadata'];
    protected $casts = ['metadata' => 'array'];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ExerciseCategory::class, 'category_exercise', 'exercise_id', 'category_id');
    }

    protected static function booted()
    {
        static::creating(function (Exercise $m) {
            if (empty($m->slug) && !empty($m->name)) {
                $m->slug = Str::slug($m->name);
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
            ->map(fn($grp) => $grp->pluck('name_slug')->values()->all())
            ->toArray();
    }
}
