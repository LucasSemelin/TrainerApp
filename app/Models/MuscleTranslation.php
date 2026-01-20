<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MuscleTranslation extends Model
{
    /** @use HasFactory<\Database\Factories\MuscleTranslationFactory> */
    use HasFactory;

    protected $fillable = ['muscle_id', 'locale', 'label'];

    public function muscle()
    {
        return $this->belongsTo(Muscle::class);
    }
}
