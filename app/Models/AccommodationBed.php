<?php

namespace App\Models;

use App\Enums\Accommodation\BedType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccommodationBed extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'accommodation_id',
        'bed_type',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'bed_type' => BedType::class,
        ];
    }

    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class);
    }
}
