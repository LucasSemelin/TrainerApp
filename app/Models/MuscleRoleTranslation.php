<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MuscleRoleTranslation extends Model
{
    /** @use HasFactory<\Database\Factories\MuscleRoleTranslationFactory> */
    use HasFactory;

    protected $fillable = ['muscle_role_id', 'locale', 'label'];

    public function muscleRole()
    {
        return $this->belongsTo(MuscleRole::class);
    }
}
