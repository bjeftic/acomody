<?php

namespace App\Http\Controllers\SuperAdmin;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\AccommodationDraft;
use App\Models\AccommodationType;
use App\Enums\AccommodationOccupation;
use App\Jobs\CreateAccommodation;
use Illuminate\Http\RedirectResponse;

class AccommodationDraftController
{
    /**
     * Show the accommodation draft applications.
     *
     * @return View
     */
    public function index(Request $request): View
    {
        /** @var string $search */
        $search = $request->search ?? '';

        $accommodationDraftsPaginated = AccommodationDraft::where('status', 'waiting_for_approval')
            ->latest('id')
            ->when(!empty($search), function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%");
            })
            ->paginate(12)
            ->appends($request->only(['search', 'page']));

        return view('super-admin.accommodation-drafts.index', [
            'accommodationDrafts' => $accommodationDraftsPaginated,
            'search' => $search,
            'page' => $request->page ?? 1,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return View
     */
    public function show($id): View
    {
        $accommodationDraft = AccommodationDraft::whereId($id)->firstOrFail();

        $accommodationDraft->data = json_decode($accommodationDraft->data, true);

        $draftData = [];
        $draftData['accommodation_type'] = AccommodationType::find($accommodationDraft->data['accommodation_type'])->name ?? null;
        $draftData['accommodation_occupation'] = AccommodationOccupation::fromId($accommodationDraft->data['accommodation_occupation'])?->label() ?? null;
        $draftData['title'] = $accommodationDraft->data['title'] ?? null;
        $draftData['description'] = $accommodationDraft->data['description'] ?? null;
        $draftData['website'] = $accommodationDraft->data['website'] ?? null;
        $draftData['email'] = $accommodationDraft->data['email'] ?? null;
        $draftData['street'] = $accommodationDraft->data['address']['street'] ?? null;
        $draftData['city'] = $accommodationDraft->data['address']['city'] ?? null;
        $draftData['state'] = $accommodationDraft->data['address']['state'] ?? null;
        $draftData['country'] = $accommodationDraft->data['address']['country'] ?? null;
        $draftData['postal_code'] = $accommodationDraft->data['address']['postal_code'] ?? null;

        // House rules
        $houseRules = [];
        foreach ($accommodationDraft->data['house_rules'] ?? [] as $rule => $value) {
            $houseRules[Str::snake($rule)] = $value;
        }
        $draftData['house_rules'] = $houseRules;
        // Return data
        $accommodationDraft->draftData = $draftData;
        // dd($accommodationDraft);
        return view('super-admin.accommodation-drafts.view')
            ->with('accommodationDraft', $accommodationDraft);
    }

    /**
     * Approve the accommodation draft and dispatch job to create accommodation.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function approve($id): RedirectResponse
    {
        $accommodationDraft = AccommodationDraft::whereId($id)->firstOrFail();

        CreateAccommodation::dispatch($accommodationDraft, userOrFail()->id)->onQueue('accommodation-queue');

        return redirect()
            ->route('super-admin.accommodation-drafts.index')
            ->with('success', 'Accommodation draft approved and accommodation creation job dispatched.');
    }
}
