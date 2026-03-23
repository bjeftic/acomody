<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\LegalDocument\DocumentType;
use App\Enums\LegalDocument\SectionType;
use App\Models\LegalDocument;
use App\Models\LegalDocumentSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LegalDocumentController
{
    public function index(): View
    {
        $documents = LegalDocument::withoutAuthorization(function () {
            return LegalDocument::with('author')
                ->withCount('sections')
                ->orderBy('type')
                ->orderByDesc('version')
                ->get()
                ->groupBy(fn (LegalDocument $doc) => $doc->type->value);
        });

        $documentTypes = collect(DocumentType::cases())->mapWithKeys(
            fn (DocumentType $type) => [$type->value => $type->label()]
        )->toArray();

        return view('super-admin.legal-documents.index', compact('documents', 'documentTypes'));
    }

    public function create(Request $request): View
    {
        $type = $request->query('type')
            ? DocumentType::from($request->query('type'))
            : DocumentType::Terms;

        $documentTypes = collect(DocumentType::cases())->mapWithKeys(
            fn (DocumentType $type) => [$type->value => $type->label()]
        )->toArray();

        $sectionTypes = collect(SectionType::cases())->mapWithKeys(
            fn (SectionType $type) => [$type->value => $type->label()]
        )->toArray();

        return view('super-admin.legal-documents.create', compact('type', 'documentTypes', 'sectionTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $locales = config('app.supported_locales', ['en', 'sr', 'de']);

        $titleRules = ['title' => 'required|array'];
        foreach ($locales as $locale) {
            $titleRules["title.{$locale}"] = $locale === 'en' ? 'required|string|max:255' : 'nullable|string|max:255';
        }

        $validated = $request->validate(array_merge($titleRules, [
            'type' => 'required|in:'.implode(',', array_column(DocumentType::cases(), 'value')),
            'sections' => 'nullable|array',
            'sections.*.section_type' => 'required|in:'.implode(',', array_column(SectionType::cases(), 'value')),
            'sections.*.content' => 'required|array',
            'sections.*.content.en' => 'required|string',
        ]));

        $docType = DocumentType::from($validated['type']);
        $version = LegalDocument::nextVersion($docType);

        LegalDocument::withoutAuthorization(function () use ($validated, $docType, $version, $request, $locales) {
            $document = LegalDocument::create([
                'type' => $docType,
                'version' => $version,
                'title' => $this->buildTranslations($validated['title']),
                'is_published' => false,
                'created_by' => $request->user()->id,
            ]);

            foreach ($validated['sections'] ?? [] as $index => $sectionData) {
                $content = [];
                foreach ($locales as $locale) {
                    if (! empty($sectionData['content'][$locale])) {
                        $content[$locale] = $sectionData['content'][$locale];
                    }
                }

                LegalDocumentSection::create([
                    'legal_document_id' => $document->id,
                    'section_type' => SectionType::from($sectionData['section_type']),
                    'content' => $content,
                    'sort_order' => $index,
                ]);
            }
        });

        return redirect()->route('admin.legal-documents.index')
            ->with('success', "New version of {$docType->label()} (v{$version}) created successfully.");
    }

    public function show(LegalDocument $legalDocument): View
    {
        $legalDocument->load('sections', 'author');

        $sectionTypes = collect(SectionType::cases())->mapWithKeys(
            fn (SectionType $type) => [$type->value => $type->label()]
        )->toArray();

        return view('super-admin.legal-documents.show', compact('legalDocument', 'sectionTypes'));
    }

    public function publish(LegalDocument $legalDocument): RedirectResponse
    {
        if ($legalDocument->is_published) {
            return redirect()->route('admin.legal-documents.index')
                ->with('alert-warning', 'This version is already published.');
        }

        LegalDocument::withoutAuthorization(function () use ($legalDocument) {
            $legalDocument->update([
                'is_published' => true,
                'published_at' => now(),
            ]);
        });

        return redirect()->route('admin.legal-documents.index')
            ->with('success', "{$legalDocument->type->label()} v{$legalDocument->version} is now published.");
    }

    public function destroy(LegalDocument $legalDocument): RedirectResponse
    {
        if ($legalDocument->is_published) {
            return redirect()->route('admin.legal-documents.index')
                ->with('alert-danger', 'Cannot delete a published version. Publish a newer version first.');
        }

        LegalDocument::withoutAuthorization(fn () => $legalDocument->delete());

        return redirect()->route('admin.legal-documents.index')
            ->with('success', 'Draft deleted successfully.');
    }

    /**
     * @param  array<string, string|null>  $translations
     * @return array<string, string>
     */
    private function buildTranslations(array $translations): array
    {
        return array_filter($translations, fn ($value) => ! empty($value));
    }
}
