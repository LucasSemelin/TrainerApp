<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentTranslation extends Model
{
    /** @use HasFactory<\Database\Factories\EquipmentTranslationFactory> */
    use HasFactory;

    protected $fillable = ['equipment_id', 'locale', 'label'];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
}
