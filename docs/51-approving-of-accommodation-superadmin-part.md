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
