<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\Accommodation\AccommodationOccupation;
use App\Enums\Accommodation\AccommodationType;
use App\Enums\Accommodation\BedType;
use App\Jobs\CreateAccommodation;
use App\Mail\Accommodation\AccommodationRejectedMail;
use App\Mail\Accommodation\ReviewCommentAddedMail;
use App\Models\AccommodationDraft;
use App\Models\Amenity;
use App\Models\Location;
use App\Models\ReviewComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AccommodationDraftController
{
    /**
     * Show the accommodation draft applications.
     */
    public function index(Request $request): View
    {
        /** @var string $search */
        $search = $request->search ?? '';

        $accommodationDraftsPaginated = AccommodationDraft::with('user.userProfile')
            ->where('status', 'waiting_for_approval')
            ->latest('id')
            ->when(! empty($search), function ($q) use ($search) {
                $q->whereRaw("data->>'title' ilike ?", ["%{$search}%"]);
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
     */
    public function show($id): View
    {
        $accommodationDraft = AccommodationDraft::with(['photos', 'reviewComments.user', 'user'])
            ->whereId($id)
            ->firstOrFail();

        $accommodationDraft->data = json_decode($accommodationDraft->data, true);

        $draftData = [];
        $draftData['accommodation_type'] = AccommodationType::from($accommodationDraft->data['accommodation_type'] ?? null)->label() ?? null;
        $draftData['accommodation_occupation'] = AccommodationOccupation::from($accommodationDraft->data['accommodation_occupation'] ?? null)->label() ?? null;
        $draftData['title'] = $accommodationDraft->data['title'] ?? null;
        $draftData['description'] = $accommodationDraft->data['description'] ?? null;
        $draftData['website'] = $accommodationDraft->data['website'] ?? null;
        $draftData['email'] = $accommodationDraft->data['email'] ?? null;
        $draftData['street'] = $accommodationDraft->data['address']['street'] ?? null;
        $draftData['city'] = $accommodationDraft->data['address']['city'] ?? null;
        $draftData['state'] = $accommodationDraft->data['address']['state'] ?? null;
        $draftData['country'] = $accommodationDraft->data['address']['country'] ?? null;
        $draftData['postal_code'] = $accommodationDraft->data['address']['zip_code'] ?? null;
        $floorPlan = $accommodationDraft->data['floor_plan'] ?? [];
        $draftData['max_guests'] = $floorPlan['guests'] ?? null;
        $draftData['bedrooms'] = $floorPlan['bedrooms'] ?? null;
        $draftData['bathrooms'] = $floorPlan['bathrooms'] ?? null;
        $draftData['bed_types'] = collect($floorPlan['bed_types'] ?? [])
            ->filter(fn (array $bt) => ($bt['quantity'] ?? 0) > 0)
            ->map(fn (array $bt) => [
                'label' => BedType::from($bt['bed_type'])->label(),
                'quantity' => $bt['quantity'],
            ])
            ->values()
            ->all();
        $amenityIds = $accommodationDraft->data['amenities'] ?? [];
        $draftData['amenities'] = ! empty($amenityIds)
            ? Amenity::whereIn('id', $amenityIds)->pluck('name')->all()
            : [];
        $draftData['pricing'] = $accommodationDraft->data['pricing'] ?? [];
        $draftData['coordinates'] = $accommodationDraft->data['coordinates'] ?? [];

        // House rules
        $houseRules = [];
        foreach ($accommodationDraft->data['house_rules'] ?? [] as $rule => $value) {
            $houseRules[Str::snake($rule)] = $value;
        }
        $draftData['house_rules'] = $houseRules;

        $accommodationDraft->draftData = $draftData;

        return view('super-admin.accommodation-drafts.view')
            ->with('locationOptions', Location::pluck('name', 'id'))
            ->with('accommodationDraft', $accommodationDraft);
    }

    /**
     * Approve the accommodation draft and dispatch job to create accommodation.
     */
    public function approve(Request $request, $id): RedirectResponse
    {
        $validated = $request->validate([
            'location_id' => 'required|exists:locations,id',
        ]);

        $locationId = $validated['location_id'];
        $accommodationDraft = AccommodationDraft::with('user')->whereId($id)->firstOrFail();

        $accommodationDraft->update(['status' => 'processing']);

        \Log::channel('queue')->info('Approving accommodation draft', [
            'draft_id' => $accommodationDraft->id,
        ]);

        CreateAccommodation::dispatch($accommodationDraft->id, $locationId, userOrFail()->id)->onQueue('accommodation-queue');

        return redirect()
            ->route('admin.accommodation-drafts.index')
            ->with('success', 'Accommodation draft approved and accommodation creation job dispatched.');
    }

    /**
     * Reject the accommodation draft, optionally adding a reason comment.
     */
    public function reject(Request $request, $id): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:2000',
        ]);

        $accommodationDraft = AccommodationDraft::with('user')->whereId($id)->firstOrFail();

        $accommodationDraft->update(['status' => 'rejected']);

        $reason = $validated['reason'] ?? null;

        if (! empty($reason)) {
            ReviewComment::create([
                'commentable_id' => $accommodationDraft->id,
                'commentable_type' => AccommodationDraft::class,
                'user_id' => userOrFail()->id,
                'body' => $reason,
            ]);
        }

        Mail::to($accommodationDraft->user->email)
            ->queue(new AccommodationRejectedMail($accommodationDraft, $reason));

        return redirect()
            ->route('admin.accommodation-drafts.index')
            ->with('success', 'Accommodation draft has been rejected and the host has been notified.');
    }

    /**
     * Add a review comment to the accommodation draft.
     */
    public function addComment(Request $request, $id): RedirectResponse
    {
        $validated = $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        $accommodationDraft = AccommodationDraft::with('user')->whereId($id)->firstOrFail();

        $comment = ReviewComment::create([
            'commentable_id' => $accommodationDraft->id,
            'commentable_type' => AccommodationDraft::class,
            'user_id' => userOrFail()->id,
            'body' => $validated['body'],
        ]);

        Mail::to($accommodationDraft->user->email)
            ->queue(new ReviewCommentAddedMail($accommodationDraft, $comment));

        return redirect()
            ->route('admin.accommodation-drafts.show', $accommodationDraft->id)
            ->with('success', 'Comment added and the host has been notified.');
    }
}
