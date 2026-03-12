# Branch: 75-edit-accommodation-and-draft---with-review-comment-and-after-approval

## Purpose

Allow hosts to edit their approved/live accommodations directly (no re-approval required). Also polish the draft edit flow, fix the rejected-drafts display, and tighten the superadmin panel.

---

## Features Implemented

### 1. Edit Approved Accommodation — Backend

**Endpoint:** `PUT /api/accommodations/{accommodation}`

**Controller:** `app/Http/Controllers/AccommodationController.php` — `update()`

**Form Request:** `app/Http/Requests/Accommodation/UpdateRequest.php`
- Authorises via ownership check (`accommodation->user_id === user->id`)
- Validates all sections: accommodation type/occupation, address, coordinates, floor plan, amenities, title, description, pricing, house rules

**Service:** `app/Services/AccommodationService.php`
- `getAccommodationForEdit(string $id)` — eager-loads `amenities`, `photos`, `pricing`, `beds`, `location.country`
- `updateAccommodation(Accommodation $accommodation, array $data)` — updates all fields, syncs amenities, recreates beds (only when `bed_types` is non-empty), updates pricing, returns a fresh resource

**Resource:** `app/Http/Resources/AccommodationResource.php`
- Extended to include: `bedrooms`, `bathrooms`, `check_in_from`, `check_in_until`, `check_out_until`, `quiet_hours_from`, `quiet_hours_until`
- Conditionally includes `beds` (when relation loaded) and `location` (name + country_code)

**Key validation notes:**
- `floor_plan.bed_types` uses `present` (not `required`) — allows an empty array for accommodations that have no beds defined
- Individual bed entries still require `quantity >= 1`
- Beds are only deleted/recreated when the submitted array is non-empty — editing other sections never wipes existing beds

---

### 2. Show/Edit Accommodation Page — Frontend

**Route:** `/hosting/listings/:accommodationId` (single page, no separate `/edit` route)

**Component:** `resources/js/src/views/hosting/listings/ShowListing.vue`

Merged the old `ShowListing.vue` (static display) and `EditAccommodation.vue` (separate edit page) into one page. `EditAccommodation.vue` was removed.

**Behaviour:**
- Loads via `loadInitialEditAccommodationData` (fetches accommodation + accommodation types + amenities + bed types in parallel)
- Preview card at the top shows cover photo, title, city/country, guest/bed/bath counts, base price
- Photo area is clickable — hovering shows "Edit photos" overlay; clicking opens `Step6Photos` inline within the card
- Below the card: edit sections (Property info, Location, Floor plan, Amenities, Title, Description, Pricing, House rules) — each collapsed by default with an Edit button
- Clicking Edit on a section expands the form inline; Save calls `PUT /api/accommodations/{id}` with the full payload; Cancel restores the collapsed view
- Green "Changes saved successfully" banner appears for 3 seconds after a successful save

**Data mapping fixes:**
- `toHHMM(timeStr)` helper strips seconds from DB time values (`HH:MM:SS` → `HH:MM`) so the backend `date_format:H:i` validation passes
- `accommodationTypeName` / `occupationTypeName` fall back to the raw value if not found in the types list

**Vuex — `store/modules/hosting/listings`:**
- `loadInitialEditAccommodationData(accommodationId)` — parallel dispatches for accommodation data + types/amenities/bed types from `hosting/createAccommodation` module (using `{ root: true }`)
- `updateAccommodation({ accommodationId, data })` — calls `PUT`, commits `SET_ACCOMMODATION`
- `fetchAccommodation(id)` — calls `GET /api/accommodations/{id}`, commits `SET_ACCOMMODATION`

---

### 3. Draft Edit Page — Photos Inline

**Component:** `resources/js/src/views/hosting/listings/EditAccommodationDraft.vue`

The separate "Photos" edit section in the sections list was removed. The preview card photo area now works the same as the accommodation show page — click the thumbnail to open `Step6Photos` inline within the card.

---

### 4. Rejected Drafts on My Listings

**Component:** `resources/js/src/views/hosting/listings/MyListings.vue`

A "Rejected" section was added above "Waiting for approval". Clicking a rejected draft navigates to the draft edit page (`page-draft-edit`) so the host can review feedback and resubmit.

**Vuex additions:**
- `state.myRejectedDrafts: []`
- `fetchRejectedDrafts()` — queries `?status=rejected`
- `loadInitialMyListingsData` now dispatches `fetchRejectedDrafts` in parallel with the other fetches

---

### 5. Superadmin — Accommodations & Drafts

**Routes (`routes/admin.php`):**
- `accommodations` resource restricted to `only(['index', 'show'])` — no edit/update/create/delete routes
- `accommodation-drafts` resource restricted to `only(['index', 'show'])` — same

**Drafts index view (`resources/views/super-admin/accommodation-drafts/index.blade.php`):**

Updated table columns:

| Column | Source |
|---|---|
| Title | `data->>'title'` (JSON) |
| Host | `user.userProfile.first_name + last_name` |
| Location | `data.address.city` + `data.address.country` (JSON) |
| Type | `AccommodationType::from(data.accommodation_type)->label()` |
| Status | `status` column |
| Created at | `created_at` formatted `Y-m-d H:i` |

- "Edit" button removed; only "View" remains
- Search fixed to use `whereRaw("data->>'title' ilike ?", ...)` (PostgreSQL JSON extract) — the `title` column does not exist directly on the table
- Eager-loads `user.userProfile` to avoid N+1

---

## Bug Fixes

| Bug | Fix |
|---|---|
| `check_in_from` / `check_in_until` / `check_out_until` stored as `HH:MM:SS` but validated as `H:i` | Added `toHHMM()` helper in `ShowListing.vue` to strip seconds before sending |
| Editing any section failed with `floor_plan.bed_types required` for accommodations with no beds | Changed validation rule from `required` to `present`; service only recreates beds when array is non-empty |
| `AccommodationType::IGLOO` is a valid enum value — test used it as an invalid type | Test updated to use `spaceship` as the invalid value |
| `Amenity::factory()` undefined | Added `HasFactory` trait to `App\Models\Amenity` and created `database/factories/AmenityFactory.php` |
| `ReviewComment::factory()` undefined | Added `HasFactory` trait to `App\Models\ReviewComment` |
| PUT update tests failing with 500 "Exchange rates table is empty" | Added `seedCurrencyRates()` in `beforeEach` of the PUT describe block |
| Superadmin drafts search used `where('title', ...)` on a non-existent column | Fixed to `whereRaw("data->>'title' ilike ?", ...)` |
| `User` relation `profile` not found | Correct relation name is `userProfile` |

---

## Tests

**File:** `tests/Feature/Api/AccommodationTest.php`

Added `describe('PUT /api/accommodations/{id} (update)', ...)` with 9 tests:

| Test | Asserts |
|---|---|
| returns 401 for unauthenticated requests | 401 |
| returns 403 when a different user tries to update | 403 |
| returns 200 on success | 200 + success message |
| persists updated title and description | DB has updated title |
| returns 422 when title is missing | 422 + field error |
| returns 422 when accommodation_type is invalid | 422 + field error |
| returns 422 when cancellation_policy is invalid | 422 + field error |
| syncs amenities on update | `accommodation_amenity` pivot row exists |
| recreates beds on update | `accommodation_beds` row exists |
