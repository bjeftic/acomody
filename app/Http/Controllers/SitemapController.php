<?php

namespace App\Http\Controllers;

use App\Models\Accommodation;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $sitemap = Sitemap::create();

        $this->addStaticPages($sitemap);
        $this->addAccommodations($sitemap);

        return response($sitemap->render(), 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    private function addStaticPages(Sitemap $sitemap): void
    {
        $sitemap->add(
            Url::create('/')
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                ->setPriority(1.0)
        );

        $sitemap->add(
            Url::create('/s')
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                ->setPriority(0.9)
        );

        $sitemap->add(
            Url::create('/become-a-host')
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.7)
        );

        $sitemap->add(
            Url::create('/terms')
                ->setLastModificationDate(Carbon::parse('2025-01-01'))
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                ->setPriority(0.3)
        );

        $sitemap->add(
            Url::create('/privacy-policy')
                ->setLastModificationDate(Carbon::parse('2025-01-01'))
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                ->setPriority(0.3)
        );
    }

    private function addAccommodations(Sitemap $sitemap): void
    {
        Accommodation::query()
            ->whereNotNull('approved_by')
            ->where('is_active', true)
            ->select(['id', 'updated_at', 'approved_by', 'is_active', 'user_id'])
            ->each(fn (Accommodation $accommodation) => $sitemap->add($accommodation));
    }
}
