<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ExerciseEquipment extends Pivot
{
    /** @use HasFactory<\Database\Factories\ExerciseEquipmentFactory> */
    use HasFactory;

    protected $table = 'exercise_equipment';

    protected $fillable = ['exercise_id', 'equipment_id'];

    public $timestamps = false;

    public $incrementing = false;

    // Relaciones
    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
}
