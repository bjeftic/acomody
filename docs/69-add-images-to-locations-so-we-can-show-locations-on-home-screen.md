# Branch: 69-add-images-to-locations-so-we-can-show-locations-on-home-screen

## Request

Add image upload support to the Location model in the superadmin panel, using the existing polymorphic `Photo` model (same infrastructure as accommodations and user profiles). The image upload should be optional — a single cover photo per location.

Additional improvements requested:
- `is_active` toggle on create/edit form
- Edit and delete actions (previously missing — Edit button returned 404)
- Multilingual name input (English / Serbian / German) using existing Spatie `HasTranslations`
- "Remove current image" checkbox on edit form
- Location Type column in the index table
- MinIO `location_photos` bucket
- Locations should only appear in Typesense search when `is_active = true`

---

## What Was Developed

### Schema Changes

**`database/migrations/2025_07_03_122245_create_locations_table.php`**
- Changed primary key from `id()` (bigint) to `ulid('id')->primary()` — consistent with Accommodation and AccommodationDraft
- `parent_id` changed to `ulid('parent_id')->nullable()` (self-referential FK added in a separate `Schema::table()` call after `Schema::create()` — required by PostgreSQL for self-referential constraints)
- `latitude` and `longitude` made nullable
- Added `is_active` boolean column (default `true`)

**`database/migrations/2026_02_06_215858_create_photos_table.php`**
- Changed `ulidMorphs('photoable')` to explicit `string('photoable_type')` + `string('photoable_id')` columns
- This resolves a PostgreSQL type mismatch: `ulidMorphs` creates `char(26)` which is incompatible with integer-keyed models (User, Location before ULID change)

**`database/migrations/2025_11_22_133909_create_accommodations_table.php`**
- Changed `foreignId('location_id')` → `foreignUlid('location_id')` to match Location's new ULID primary key

### Model Changes

**`app/Models/Location.php`**
- Added `HasUlids` trait
- Added `is_active` to `$fillable` and `$casts`
- Added `photos()` (morphMany) and `primaryPhoto()` (morphOne) relationships
- Added `shouldBeSearchable(): bool` — returns `$this->is_active`, gating Typesense indexing to active-only locations

### Service Changes

**`app/Services/PhotoService.php`**
- Added `Location` cases to `getDiskForModel()` and `getFolderName()` so the existing polymorphic photo upload pipeline supports locations

### Config Changes

**`config/filesystems.php`**
- Added `location_photos` disk entry (public visibility, MinIO bucket)

**`config/images.php`**
- Added `location` preset with thumbnail / medium / large size definitions

### Controller Changes

**`app/Http/Controllers/SuperAdmin/LocationController.php`**
- Full rewrite — added `edit()`, `update()`, `destroy()`, and `search()` methods
- `PhotoService` injected via constructor
- `store()` handles optional image upload
- `update()` handles image replacement and "remove image" checkbox
- `destroy()` deletes all associated photos before deleting the location
- `search()` provides JSON endpoint for Select2 AJAX parent-location picker
- Private helpers: `nameValidationRules()` (per-locale validation), `buildTranslations()` (filters empty locales)
- Removed all `withoutSyncingToSearch` wrappers — locations now sync to Typesense naturally

### View Changes

**`resources/views/super-admin/partials/forms/location.blade.php`**
- Replaced single name input with Bootstrap 3 tab UI for multilingual name (en / sr / de)
- Active tab persisted in `localStorage` across validation errors
- Parent location picker using Select2 AJAX (calls `admin.locations.search`)
- `is_active` checkbox
- Cover image upload field with current image preview (thumbnail)
- "Remove current image" checkbox (shown only when a completed photo exists)

**`resources/views/super-admin/locations/create.blade.php`**
- Added `enctype="multipart/form-data"`

**`resources/views/super-admin/locations/edit.blade.php`**
- Created (previously missing — caused 404 on Edit button click)

**`resources/views/super-admin/locations/index.blade.php`**
- Added Image thumbnail column
- Added Type column (using `$location->location_type?->label()`)
- Added Active badge column (green Yes / grey No)
- Added Delete button with confirmation dialog
- Added success flash message display
- Eager loads: `country`, `parent`, `primaryPhoto`

### Test Changes

**`tests/Feature/SuperAdmin/LocationControllerTest.php`**
- New test file, 22 tests covering:
  - Index access (superadmin, guest, regular user, search results)
  - Store: with image, without image, is_active false, no coordinates, invalid image type, image over 5MB, missing English name, invalid country, invalid location type
  - Edit: shows form, redirects regular users
  - Update: fields, photo replacement, remove image, no image submitted
  - Destroy: deletes location, deletes associated photos, redirects regular users

**`phpunit.xml`**
- Added `SCOUT_DRIVER=null` — decouples all tests from Typesense

### Typesense / Search

- `shouldBeSearchable()` on the Location model gates indexing: only `is_active = true` locations are indexed
- `scout:import "App\Models\Location"` run to index all existing active locations

---

## Key Technical Notes

- PostgreSQL requires self-referential FK constraints to be added in a separate `Schema::table()` call after `Schema::create()`
- Polymorphic morph columns must be `string` (not `ulidMorphs`) when the same polymorphic relation is shared between integer-keyed and ULID-keyed models
- `$request->boolean('is_active')` (no default) correctly returns `false` when the checkbox is absent from the form submission
- `SCOUT_DRIVER=null` in `phpunit.xml` prevents tests from requiring a live Typesense instance
