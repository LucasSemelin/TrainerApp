<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ExerciseName extends Model
{
    use HasFactory;

    protected $fillable = [
        'exercise_id',
        'name',
        'name_normalized',
        'locale',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    protected static function booted()
    {
        static::creating(function (ExerciseName $model) {
            $model->name_normalized = static::normalizeForSearch($model->name);
        });

        static::updating(function (ExerciseName $model) {
            if ($model->isDirty('name')) {
                $model->name_normalized = static::normalizeForSearch($model->name);
            }
        });
    }

    /**
     * Normaliza el nombre para búsquedas más eficientes
     */
    public static function normalizeForSearch(string $name): string
    {
        // Convertir a minúsculas, remover acentos y caracteres especiales
        $normalized = Str::lower($name);
        $normalized = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü'],
            ['a', 'e', 'i', 'o', 'u', 'n', 'u'],
            $normalized
        );

        // Remover caracteres especiales excepto espacios y guiones
        $normalized = preg_replace('/[^a-z0-9\s\-]/', '', $normalized);

        // Normalizar espacios múltiples
        $normalized = preg_replace('/\s+/', ' ', trim($normalized));

        return $normalized;
    }
}
