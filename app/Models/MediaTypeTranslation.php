<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaTypeTranslation extends Model
{
    /** @use HasFactory<\Database\Factories\MediaTypeTranslationFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'media_type_id',
        'locale',
        'label',
    ];

    public function mediaType(): BelongsTo
    {
        return $this->belongsTo(MediaType::class);
    }
}
