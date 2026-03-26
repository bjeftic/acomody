<?php

use Illuminate\Support\Facades\Auth;

function sitemapUrl(): string
{
    return '/sitemap-'.config('app.sitemap_token').'.xml';
}

describe('Sitemap', function () {
    it('does not serve sitemap at plain /sitemap.xml', function () {
        $response = $this->get('/sitemap.xml');

        expect($response->headers->get('Content-Type'))
            ->not->toContain('application/xml');
    });

    it('returns 200 with XML content type for token URL', function () {
        $response = $this->get(sitemapUrl());

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');
    });

    it('includes all static pages', function () {
        $content = $this->get(sitemapUrl())->getContent();

        expect($content)
            ->toContain('<loc>')
            ->toContain('/s<')
            ->toContain('/become-a-host<')
            ->toContain('/terms<')
            ->toContain('/privacy-policy<');
    });

    it('includes active approved accommodations', function () {
        $host = authenticatedUser();
        $accommodation = createAccommodation($host);
        Auth::logout();
        $this->app['auth']->forgetGuards();

        $response = $this->get(sitemapUrl());

        expect($response->getContent())
            ->toContain("/accommodations/{$accommodation->id}");
    });

    it('excludes inactive accommodations', function () {
        $host = authenticatedUser();
        $accommodation = createAccommodation($host, ['is_active' => false]);
        Auth::logout();

        $response = $this->get(sitemapUrl());

        expect($response->getContent())
            ->not->toContain("/accommodations/{$accommodation->id}");
    });

    it('excludes unapproved accommodations', function () {
        $host = authenticatedUser();
        $accommodation = createAccommodation($host, ['approved_by' => null]);
        Auth::logout();

        $response = $this->get(sitemapUrl());

        expect($response->getContent())
            ->not->toContain("/accommodations/{$accommodation->id}");
    });

    it('generates sitemap file with token in filename via artisan command', function () {
        $filename = 'sitemap-'.config('app.sitemap_token').'.xml';
        $path = public_path($filename);

        if (file_exists($path)) {
            unlink($path);
        }

        $this->artisan('sitemap:generate')->assertSuccessful();

        expect(file_exists($path))->toBeTrue();

        unlink($path);
    });
});
