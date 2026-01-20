<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ExerciseMuscle extends Pivot
{
    /** @use HasFactory<\Database\Factories\ExerciseMuscleFactory> */
    use HasFactory;

    protected $table = 'exercise_muscles';

    protected $fillable = ['exercise_id', 'muscle_id', 'muscle_role_id'];

    public $timestamps = false;

    public $incrementing = false;

    // Relaciones
    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }

    public function muscle()
    {
        return $this->belongsTo(Muscle::class);
    }

    public function muscleRole()
    {
        return $this->belongsTo(MuscleRole::class);
    }
}
