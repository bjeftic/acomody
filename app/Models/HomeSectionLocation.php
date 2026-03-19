<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class HomeSectionLocation extends Model
{
    use HasFactory;

    protected $fillable = ['home_section_id', 'location_id', 'sort_order'];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function homeSection(): BelongsTo
    {
        return $this->belongsTo(HomeSection::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('home_sections'));
        static::deleted(fn () => Cache::forget('home_sections'));
    }

    public function canBeReadBy($user): bool
    {
        return true;
    }

    public function canBeCreatedBy($user): bool
    {
        return $user !== null && $user->is_superadmin;
    }

    public function canBeUpdatedBy($user): bool
    {
        return $user !== null && $user->is_superadmin;
    }

    public function canBeDeletedBy($user): bool
    {
        return $user !== null && $user->is_superadmin;
    }
}
