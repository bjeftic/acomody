<?php

use App\Enums\LegalDocument\DocumentType;
use App\Enums\LegalDocument\SectionType;
use App\Models\LegalDocument;
use App\Models\LegalDocumentSection;

// ============================================================
// Public API: GET /api/public/legal/{type}
// ============================================================

it('returns 404 when no published document exists', function () {
    $this->getJson('/api/public/legal/terms')
        ->assertStatus(404);
});

it('returns 404 for an invalid document type', function () {
    $this->getJson('/api/public/legal/invalid_type')
        ->assertStatus(404);
});

it('returns the latest published terms document', function () {
    $user = authenticatedUser();

    $doc = LegalDocument::withoutAuthorization(fn () => LegalDocument::create([
        'type' => DocumentType::Terms,
        'version' => 1,
        'title' => ['en' => 'Terms & Conditions', 'sr' => 'Uslovi korišćenja'],
        'is_published' => true,
        'published_at' => now(),
        'created_by' => $user->id,
    ]));

    LegalDocumentSection::withoutAuthorization(fn () => LegalDocumentSection::create([
        'legal_document_id' => $doc->id,
        'section_type' => SectionType::Heading,
        'content' => ['en' => 'Introduction', 'sr' => 'Uvod'],
        'sort_order' => 0,
    ]));

    LegalDocumentSection::withoutAuthorization(fn () => LegalDocumentSection::create([
        'legal_document_id' => $doc->id,
        'section_type' => SectionType::Paragraph,
        'content' => ['en' => 'Welcome to Acomody.'],
        'sort_order' => 1,
    ]));

    $this->getJson('/api/public/legal/terms')
        ->assertOk()
        ->assertJsonFragment(['type' => 'terms'])
        ->assertJsonFragment(['version' => 1])
        ->assertJsonPath('title.en', 'Terms & Conditions')
        ->assertJsonPath('title.sr', 'Uslovi korišćenja')
        ->assertJsonCount(2, 'sections')
        ->assertJsonPath('sections.0.section_type', 'heading')
        ->assertJsonPath('sections.0.content.en', 'Introduction')
        ->assertJsonPath('sections.1.section_type', 'paragraph');
});

it('returns only the latest published version', function () {
    $user = authenticatedUser();

    LegalDocument::withoutAuthorization(fn () => LegalDocument::create([
        'type' => DocumentType::Terms,
        'version' => 1,
        'title' => ['en' => 'Terms v1'],
        'is_published' => true,
        'published_at' => now()->subDay(),
        'created_by' => $user->id,
    ]));

    LegalDocument::withoutAuthorization(fn () => LegalDocument::create([
        'type' => DocumentType::Terms,
        'version' => 2,
        'title' => ['en' => 'Terms v2'],
        'is_published' => true,
        'published_at' => now(),
        'created_by' => $user->id,
    ]));

    $this->getJson('/api/public/legal/terms')
        ->assertOk()
        ->assertJsonFragment(['version' => 2])
        ->assertJsonPath('title.en', 'Terms v2');
});

it('does not return unpublished drafts', function () {
    $user = authenticatedUser();

    LegalDocument::withoutAuthorization(fn () => LegalDocument::create([
        'type' => DocumentType::Terms,
        'version' => 1,
        'title' => ['en' => 'Terms Draft'],
        'is_published' => false,
        'created_by' => $user->id,
    ]));

    $this->getJson('/api/public/legal/terms')
        ->assertStatus(404);
});

it('returns the latest published privacy policy document', function () {
    $user = authenticatedUser();

    LegalDocument::withoutAuthorization(fn () => LegalDocument::create([
        'type' => DocumentType::PrivacyPolicy,
        'version' => 1,
        'title' => ['en' => 'Privacy Policy'],
        'is_published' => true,
        'published_at' => now(),
        'created_by' => $user->id,
    ]));

    $this->getJson('/api/public/legal/privacy_policy')
        ->assertOk()
        ->assertJsonFragment(['type' => 'privacy_policy'])
        ->assertJsonPath('title.en', 'Privacy Policy');
});

// ============================================================
// SuperAdmin: publish and delete
// ============================================================

it('superadmin can publish a draft document', function () {
    $admin = authenticatedUser(['is_superadmin' => true]);

    $doc = LegalDocument::withoutAuthorization(fn () => LegalDocument::create([
        'type' => DocumentType::Terms,
        'version' => 1,
        'title' => ['en' => 'Terms Draft'],
        'is_published' => false,
        'created_by' => $admin->id,
    ]));

    $this->post("/admin/legal-documents/{$doc->id}/publish")
        ->assertRedirect();

    expect($doc->fresh()->is_published)->toBeTrue()
        ->and($doc->fresh()->published_at)->not->toBeNull();
});

it('superadmin can delete an unpublished draft', function () {
    $admin = authenticatedUser(['is_superadmin' => true]);

    $doc = LegalDocument::withoutAuthorization(fn () => LegalDocument::create([
        'type' => DocumentType::Terms,
        'version' => 1,
        'title' => ['en' => 'Terms Draft'],
        'is_published' => false,
        'created_by' => $admin->id,
    ]));

    $this->delete("/admin/legal-documents/{$doc->id}")
        ->assertRedirect();

    expect(LegalDocument::find($doc->id))->toBeNull();
});

it('superadmin cannot delete a published document', function () {
    $admin = authenticatedUser(['is_superadmin' => true]);

    $doc = LegalDocument::withoutAuthorization(fn () => LegalDocument::create([
        'type' => DocumentType::Terms,
        'version' => 1,
        'title' => ['en' => 'Terms'],
        'is_published' => true,
        'published_at' => now(),
        'created_by' => $admin->id,
    ]));

    $this->delete("/admin/legal-documents/{$doc->id}")
        ->assertRedirect();

    expect(LegalDocument::find($doc->id))->not->toBeNull();
});

it('non-superadmin cannot access admin legal document routes', function () {
    authenticatedUser(['is_superadmin' => false]);

    $this->get('/admin/legal-documents')
        ->assertRedirect();
});
