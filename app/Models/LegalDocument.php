<?php

namespace App\Models;

use App\Enums\LegalDocument\DocumentType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class LegalDocument extends Model
{
    use HasTranslations;

    protected $fillable = ['type', 'version', 'title', 'is_published', 'published_at', 'created_by'];

    public $translatable = ['title'];

    protected function casts(): array
    {
        return [
            'type' => DocumentType::class,
            'version' => 'integer',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function sections(): HasMany
    {
        return $this->hasMany(LegalDocumentSection::class)->orderBy('sort_order');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    public function scopeOfType(Builder $query, DocumentType $type): void
    {
        $query->where('type', $type);
    }

    /**
     * Get the latest published document for a given type.
     */
    public static function latestPublished(DocumentType $type): ?self
    {
        return static::withoutAuthorization(function () use ($type) {
            return static::query()
                ->ofType($type)
                ->published()
                ->orderByDesc('version')
                ->first();
        });
    }

    /**
     * Get the next version number for a given type.
     */
    public static function nextVersion(DocumentType $type): int
    {
        $max = static::withoutAuthorization(function () use ($type) {
            return static::query()->ofType($type)->max('version');
        });

        return ($max ?? 0) + 1;
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
