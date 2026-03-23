<?php

namespace App\Models;

use App\Enums\LegalDocument\SectionType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class LegalDocumentSection extends Model
{
    use HasTranslations;

    protected $fillable = ['legal_document_id', 'section_type', 'content', 'sort_order'];

    public $translatable = ['content'];

    protected function casts(): array
    {
        return [
            'section_type' => SectionType::class,
            'sort_order' => 'integer',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(LegalDocument::class, 'legal_document_id');
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
