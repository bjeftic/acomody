# Branch: 51-approving-of-accommodation-superadmin-part

## Purpose

Expand the superadmin accommodation draft review system. When a host submits an accommodation draft, superadmins can now fully review all data, approve (creating the live accommodation), reject (with optional reason), and communicate with the host via review comments and automated emails.

---

## Features Implemented

### 1. Full Draft Review View (`/admin/accommodation-drafts/{id}`)

The draft detail view was rewritten to show all data entered during the accommodation creation process:
- Accommodation type, occupation, title, description
- Address fields (street, city, state, country, postal code)
- Max guests, bedrooms, bathrooms
- Amenities — resolved from IDs to **names** (not raw IDs)
- Pricing (base price, min/max nights)
- GPS coordinates
- House rules (check-in/out times, quiet hours, cancellation policy)
- Photos (grid with primary badge)
- Review comments thread (with timestamps and author names)

### 2. Approve with Location Assignment

Hosts enter free-text address during submission. Superadmins assign the draft to an existing curated `Location` record at approval time. This prevents false location data from hosts.

- Select2 dropdown fetches locations from `/admin/locations/search`
- `location_id` is required and validated against `locations` table

### 3. Reject with Optional Reason

- POST `/admin/accommodation-drafts/{id}/reject`
- Sets draft status to `rejected`
- Optionally creates a `ReviewComment` with the reason
- Queues `AccommodationRejectedMail` to the host

### 4. Review Comments

- POST `/admin/accommodation-drafts/{id}/comments`
- Creates a `ReviewComment` (polymorphic, can attach to any model)
- Queues `ReviewCommentAddedMail` to the host
- Comments are displayed chronologically in the draft view

### 5. Email Notifications

Three new queued mailables in `app/Mail/Accommodation/`:
- `AccommodationApprovedMail` — sent after accommodation goes live
- `AccommodationRejectedMail` — sent on rejection, includes optional reason
- `ReviewCommentAddedMail` — sent when superadmin leaves a comment

All mails use only `Queueable` (no `SerializesModels`) — see Bug Fix #2 below.

### 6. `CreateAccommodation` Job

Accommodation creation was moved fully into `App\Jobs\CreateAccommodation`:
- Creates the `Accommodation` record from draft JSON data
- Sets up pricing via `PricingService`
- **Transfers photos** from `accommodation_draft_photos` disk to `accommodation_photos` disk:
  - Copies all size variants (original, thumbnail, medium, large)
  - Path transform: `draft-{draftId}/` → `property-{accommodationId}/`
  - Creates new `Photo` records for the accommodation
  - Deletes draft `Photo` records (booted hook cleans up files from disk)
- Updates draft status to `published`
- Queues `AccommodationApprovedMail`

Commented sections preserved for future development: seasonal pricing, special date pricing, weekend pricing, bulk discounts, fees, taxes.

### 7. SuperAdmin Accommodations List & Detail

- `GET /admin/accommodations` — paginated list with title search
- `GET /admin/accommodations/{id}` — full detail view (basic info, location, house rules, pricing, fees table, amenities, photo grid)

### 8. `ReviewComment` Model

Polymorphic model, can attach to any model class:

```php
// app/Models/ReviewComment.php
public function commentable(): MorphTo  // polymorphic parent
public function user(): BelongsTo       // who wrote it
```

Migration uses `string` columns (not `morphs()`) to support ULID-keyed parent models.

---

## Database Changes

### `accommodation_drafts` — status enum extended
Added `rejected` to the existing enum in the original migration:
```
['draft', 'waiting_for_approval', 'processing', 'published', 'rejected']
```

### `review_comments` — new table
```php
$table->id();
$table->string('commentable_id');    // string, not bigint — supports ULIDs
$table->string('commentable_type');
$table->index(['commentable_type', 'commentable_id']);
$table->foreignId('user_id')->constrained()->cascadeOnDelete();
$table->text('body');
$table->timestamps();
```

> **Why `string` instead of `morphs()`?** `morphs()` generates a `bigint` ID column, but `AccommodationDraft` uses ULIDs (strings). Using `$table->string('commentable_id')` keeps compatibility.

---

## Routes Added

```php
// routes/admin.php
Route::post('accommodation-drafts/{id}/reject', [AccommodationDraftController::class, 'reject'])
    ->name('accommodation-drafts.reject');

Route::post('accommodation-drafts/{id}/comments', [AccommodationDraftController::class, 'addComment'])
    ->name('accommodation-drafts.comments.store');
```

---

## Bug Fixes

### Bug Fix #1 — Queue Jobs Failing: `AccessDeniedHttpException` on `AccommodationDraft`

**Problem:** `CreateAccommodation` job used `SerializesModels` with `AccommodationDraft $accommodationDraft` as a constructor property. When the queue worker deserialized the job, `SerializesModels` re-fetched the model from the database. The `retrieved` Eloquent event fired, calling `Authorizable::canBeReadBy(null)` (no authenticated user in a queue worker) → 403 exception.

Same issue affected `AccommodationRejectedMail` and `ReviewCommentAddedMail` — both were queued mailables with `SerializesModels` holding an `AccommodationDraft` instance.

**Fix:**
- **Mails:** Removed `SerializesModels` trait. Models are PHP-serialized/unserialized natively, which does not trigger Eloquent events.
- **Job:** Changed constructor from `AccommodationDraft $accommodationDraft` to `protected string $accommodationDraftId`. At the start of `handle()`, call `Auth::loginUsingId($this->userId)` to authenticate as the approving superadmin. The `Authorizable` trait bypasses all checks for superadmins, so subsequent model operations succeed. The draft is then fetched normally: `AccommodationDraft::with('user')->findOrFail($this->accommodationDraftId)`.
- **Controller dispatch:** Updated to pass `$accommodationDraft->id` (string) instead of the model instance.

**Why not `withoutAuthorization`?** Project convention disallows `withoutAuthorization` outside of seeders/migrations.

**Why not `runningInConsole()`?** The check would incorrectly bypass auth for `php artisan tinker` and other unrelated console commands.

### Bug Fix #2 — `Photo::withoutAuthorization()` Does Not Exist

**Problem:** The original job called `Photo::withoutAuthorization()` but `Photo` extends the base Laravel `Illuminate\Database\Eloquent\Model`, not the project's `App\Models\Model`. It does not have the `Authorizable` trait, so the method does not exist — this would have caused a fatal error at runtime.

**Fix:** Removed all `withoutAuthorization` calls. Since `Auth::loginUsingId($this->userId)` authenticates as a superadmin, and `Photo` has no authorization checks at all, photo operations work without any bypass.

### Bug Fix #3 — `location.id` Validation Rejected ULIDs

**Problem:** `SearchAccommodationRequest` had `'location.id' => 'required_without:bounds|integer'`. The `Location` model uses ULIDs (strings like `01kka809y9g4w1xfqb29gqrqj3`), causing a 422 validation error on every search request.

**Fix:** Changed rule to `'location.id' => 'required_without:bounds|string'`.

**File:** `app/Http/Requests/Search/SearchAccommodationRequest.php`

### Bug Fix #4 — `Accommodation` Missing `location()` Relationship

**Problem:** `App\Models\Accommodation` was missing the `location()` BelongsTo relationship, causing `RelationNotFoundException` on the accommodations list/detail pages.

**Fix:** Added the relationship to the model.

### Bug Fix #5 — `review_comments` Migration Used `morphs()` (bigint)

**Problem:** Initial migration used `$table->morphs('commentable')` which generates a `bigint` `commentable_id` column. `AccommodationDraft` uses ULIDs (strings), causing a type mismatch.

**Fix:** Replaced with explicit `$table->string('commentable_id')` + `$table->string('commentable_type')` + manual index. Required `migrate:fresh`.

---

## Test Patterns & Gotchas

### Global Pest Helpers (in `tests/Pest.php`)
- `superadmin()` — creates and logs in a superadmin user
- `makeLocation()` — creates a `Location` record (internally calls `superadmin()`)

### Session Ordering in Tests
`makeDraft()` calls `authenticatedUser()` internally, which logs in a regular user and **overwrites the session**. Always call `superadmin()` **after** `makeDraft()` in tests that need admin access.

```php
// Correct
$draft = makeDraft();
$admin = superadmin(); // called last — sets admin session

// Wrong
$admin = superadmin();
$draft = makeDraft(); // overwrites session → admin is no longer logged in
```

### Mocking SearchService in Tests
The `searchAccommodations` endpoint calls Typesense via `SearchService`, which is not available in the test environment. Mock it:

```php
$this->mock(SearchService::class, function ($mock) {
    $mock->shouldReceive('getSortByFilter')->andReturn('base_price_eur:asc');
    $mock->shouldReceive('searchCollection')->andReturn([
        'found' => 0, 'hits' => [], 'page' => 1, 'facet_counts' => [],
    ]);
});
```

---

## Files Changed

| File | Change |
|------|--------|
| `database/migrations/2025_10_28_085257_create_accommodation_drafts_table.php` | Added `rejected` to status enum |
| `database/migrations/2026_03_09_*_create_review_comments_table.php` | New migration (string morphs) |
| `app/Models/ReviewComment.php` | New model |
| `app/Models/AccommodationDraft.php` | Added `reviewComments()` and `user()` relations |
| `app/Models/Accommodation.php` | Added missing `location()` BelongsTo |
| `app/Http/Controllers/SuperAdmin/AccommodationDraftController.php` | Added `reject()`, `addComment()`; updated `approve()` and `show()` |
| `app/Http/Controllers/SuperAdmin/AccommodationController.php` | Implemented `index()` and `show()` |
| `app/Jobs/CreateAccommodation.php` | Full rewrite — string ID, Auth::loginUsingId, photo transfer, no withoutAuthorization |
| `app/Mail/Accommodation/AccommodationApprovedMail.php` | New mailable (no SerializesModels) |
| `app/Mail/Accommodation/AccommodationRejectedMail.php` | New mailable (no SerializesModels) |
| `app/Mail/Accommodation/ReviewCommentAddedMail.php` | New mailable (no SerializesModels) |
| `app/Http/Requests/Search/SearchAccommodationRequest.php` | `location.id` rule: `integer` → `string` |
| `resources/views/super-admin/accommodation-drafts/view.blade.php` | Full rewrite |
| `resources/views/super-admin/accommodations/index.blade.php` | Rewritten (was copy of users view) |
| `resources/views/super-admin/accommodations/show.blade.php` | New view |
| `resources/views/mail/accommodation/approved.blade.php` | New email template |
| `resources/views/mail/accommodation/rejected.blade.php` | New email template |
| `resources/views/mail/accommodation/review-comment-added.blade.php` | New email template |
| `routes/admin.php` | Added reject and comment routes |
| `tests/Pest.php` | Added global `superadmin()` and `makeLocation()` helpers |
| `tests/Feature/SuperAdmin/AccommodationDraftControllerTest.php` | New — 25 tests |
| `tests/Feature/SuperAdmin/AccommodationControllerTest.php` | New — 10 tests |
| `tests/Feature/Api/SearchAccommodationTest.php` | New — 4 tests |

---

## Session 2 — Data Validation, Bug Fixes & Frontend Polish

### Bug Fix #6 — `postal_code` vs `zip_code` Key Mismatch

**Problem:** `AccommodationDraftController::show()` read `$accommodationDraft->data['address']['postal_code']` but both the frontend and `AccommodationDraftFactory` store the field as `address.zip_code`. The postal code was always `null` in the superadmin review view.

**Fix:** Changed line 69 of the controller `show()` method:
```php
// Before
$draftData['postal_code'] = $accommodationDraft->data['address']['postal_code'] ?? null;

// After
$draftData['postal_code'] = $accommodationDraft->data['address']['zip_code'] ?? null;
```

**File:** `app/Http/Controllers/SuperAdmin/AccommodationDraftController.php`

---

### Bug Fix #7 — `withoutAuthorization` Used Outside Seeders/Migrations

**Problem:** `AccommodationDraftController::reject()` and `addComment()` wrapped `ReviewComment::create()` in `ReviewComment::withoutAuthorization()`. Project convention forbids `withoutAuthorization` outside seeders/migrations. Additionally, `ReviewComment::canBeCreatedBy($user)` already returns `true` for superadmins — the wrapper was entirely redundant.

Same issue existed in `AccommodationDraftControllerTest.php` where `ReviewComment::create()` in the test setup used the wrapper.

**Fix:** Removed all three `withoutAuthorization` usages. No functional change — superadmins could always create comments.

**Files:**
- `app/Http/Controllers/SuperAdmin/AccommodationDraftController.php`
- `tests/Feature/SuperAdmin/AccommodationDraftControllerTest.php`

---

### Bug Fix #8 — Frontend Sends `bed_id` Instead of `bed_type`

**Problem:** In `CreateAccommodation.vue`, `prepareDraftData()` mapped bed types as:
```js
bed_types: this.formData.floorPlan.bedTypes
    .filter((bt) => bt.quantity > 0)
    .map((bt) => ({
        bed_id: bt.bed_id,   // bt.bed_id doesn't exist on the object → undefined → omitted from JSON
        quantity: bt.quantity,
    })),
```
The `bedTypes` objects have a `bed_type` field, not `bed_id`. The backend and factory both use `bed_type`. As a result, all submitted bed type entries were missing their type identifier.

**Fix:**
```js
.map((bt) => ({
    bed_type: bt.bed_type,   // correct field name
    quantity: bt.quantity,
})),
```

**File:** `resources/js/src/views/hosting/createAccommodation/CreateAccommodation.vue`

---

### Feature — Comprehensive `data` Field Validation in `UpdateRequest`

**Before:** The `UpdateRequest` only validated the top-level `data` field as `required|array`. All nested fields (accommodation type, address, coordinates, floor plan, pricing, house rules) were accepted without any type, format, or enum validation.

**After:** Full nested validation with two modes controlled by the `status` field:

| Mode | Trigger | Behaviour |
|------|---------|-----------|
| Draft | `status: draft` | All `data.*` fields are `nullable` — partial payloads accepted at any wizard step |
| Submit | `status: waiting_for_approval` | All required fields become `required` — full payload enforced |

**Validated fields:**

| Field | Rule |
|-------|------|
| `data.accommodation_type` | `Rule::enum(AccommodationType::class)` |
| `data.accommodation_occupation` | `Rule::enum(AccommodationOccupation::class)` |
| `data.address.country` | `exists:countries,iso_code_2` |
| `data.address.street`, `.city` | `string\|max:255` |
| `data.address.state`, `.zip_code` | `nullable` always |
| `data.coordinates.latitude` | `numeric\|between:-90,90` |
| `data.coordinates.longitude` | `numeric\|between:-180,180` |
| `data.floor_plan.guests` | `integer\|min:1\|max:16` |
| `data.floor_plan.bedrooms` | `integer\|min:0\|max:50` |
| `data.floor_plan.bathrooms` | `integer\|min:0\|max:20` |
| `data.floor_plan.bed_types` | `array\|min:1` on submit |
| `data.floor_plan.bed_types.*.bed_type` | `Rule::enum(BedType::class)` |
| `data.floor_plan.bed_types.*.quantity` | `integer\|min:1\|max:20` |
| `data.amenities.*` | `exists:amenities,id` |
| `data.title` | `min:10\|max:255` on submit, `max:255` in draft |
| `data.description` | `min:50\|max:5000` on submit, `max:5000` in draft |
| `data.pricing.basePrice` | `numeric\|min:10\|max:10000` |
| `data.pricing.bookingType` | `Rule::enum(BookingType::class)` |
| `data.pricing.minStay` | `integer\|min:1\|max:365` |
| `data.house_rules.checkInFrom/Until` | `date_format:H:i` |
| `data.house_rules.checkOutUntil` | `date_format:H:i` |
| `data.house_rules.quietHoursFrom/Until` | `nullable\|date_format:H:i` |
| `data.house_rules.cancellationPolicy` | `in:flexible,moderate,firm,strict,non-refundable` |

**File:** `app/Http/Requests/AccommodationDraft/UpdateRequest.php`

---

### Feature — Bed Types Visual Distinction (Step 4 Floor Plan)

The bed types list was visually identical to the main counters (guests, bedrooms, bathrooms). Wrapped the bed types section in a bordered card to make it clearly a sub-section:

```html
<div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4">
    <!-- header + counter-item list -->
</div>
```

**File:** `resources/js/src/views/hosting/createAccommodation/steps/Step4FloorPlan.vue`

---

### Tests Added (Session 2)

| File | Tests Added |
|------|-------------|
| `tests/Feature/Jobs/CreateAccommodationJobTest.php` | 11 new — accommodation record creation, bed types (filter zero qty, no bed_types key), amenities sync, pricing |
| `tests/Feature/SuperAdmin/AccommodationDraftControllerTest.php` | 2 new — renders bed type labels, renders postal code from `zip_code` |
| `tests/Feature/Api/AccommodationDraftTest.php` | 12 new — partial draft acceptance, invalid enum values, out-of-range coordinates, invalid country code, invalid bed type, price below minimum, invalid time format, invalid cancellation policy, required fields on submission, complete valid submission |

**Total tests after session 2: 473 passing**
