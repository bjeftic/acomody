<?php

namespace App\Http\Controllers\Public;

use App\Enums\LegalDocument\DocumentType;
use App\Models\LegalDocument;
use Illuminate\Http\JsonResponse;

class LegalDocumentController
{
    public function show(string $type): JsonResponse
    {
        $documentType = DocumentType::tryFrom($type);

        if (! $documentType) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $document = LegalDocument::latestPublished($documentType);

        if (! $document) {
            return response()->json(['message' => 'No published document found.'], 404);
        }

        $document->load('sections');

        return response()->json($this->serialize($document));
    }

    /**
     * @return array<string, mixed>
     */
    private function serialize(LegalDocument $document): array
    {
        return [
            'type' => $document->type->value,
            'type_label' => $document->type->label(),
            'version' => $document->version,
            'title' => $document->getTranslations('title'),
            'published_at' => $document->published_at?->toIso8601String(),
            'sections' => $document->sections->map(fn ($section) => [
                'section_type' => $section->section_type->value,
                'content' => $section->getTranslations('content'),
                'sort_order' => $section->sort_order,
            ])->values()->toArray(),
        ];
    }
}
