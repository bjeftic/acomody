<?php

namespace App\Models;

use App\Enums\HomeSection\SectionType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Spatie\Translatable\HasTranslations;

class HomeSection extends Model
{
    use HasFactory, HasTranslations, HasUlids;

    protected $fillable = ['title', 'type', 'sort_order', 'is_active', 'country_codes'];

    public $translatable = ['title'];

    protected function casts(): array
    {
        return [
            'type' => SectionType::class,
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'country_codes' => 'array',
        ];
    }

    public function sectionLocations(): HasMany
    {
        return $this->hasMany(HomeSectionLocation::class)->orderBy('sort_order');
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
