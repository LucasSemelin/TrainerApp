<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExerciseCategoryTranslation extends Model
{
    /** @use HasFactory<\Database\Factories\ExerciseCategoryTranslationFactory> */
    use HasFactory;

    protected $fillable = ['category_id', 'locale', 'type_label', 'name_label'];

    public function category()
    {
        return $this->belongsTo(ExerciseCategory::class);
    }
}
