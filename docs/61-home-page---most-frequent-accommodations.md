# Home Page — Dynamic Sections System

Branch: `61-home-page---most-frequent-accommodations`

---

## Overview

Implemented a fully dynamic home page sections system managed from the SuperAdmin panel. Admins can create, reorder and configure sections that appear on the welcome page. Each section has a type (`locations` or `accommodations`), a multilingual title, optional country targeting, and a list of associated locations.

The old approach (hardcoded featured locations, `is_featured` flag, `NearbyLocations`) has been **removed** and replaced entirely.

---

## Database

### `home_sections`

| Column | Type | Notes |
|---|---|---|
| `id` | ULID | Primary key |
| `title` | JSON | Translatable (EN/SR/DE) |
| `type` | string | Enum: `locations`, `accommodations` |
| `sort_order` | integer | Lower = first on page |
| `is_active` | boolean | Whether shown on home page |
| `country_codes` | JSON (nullable) | ISO-2 codes; `null` = all countries |
| `created_at` / `updated_at` | timestamps | |

### `home_section_locations`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint | Primary key |
| `home_section_id` | char(26) | FK → `home_sections` |
| `location_id` | char(26) | FK → `locations` |
| `sort_order` | integer | Order within section |
| `created_at` / `updated_at` | timestamps | |

---

## Backend

### Models

**`app/Models/HomeSection.php`**

- Traits: `HasFactory`, `HasTranslations`, `HasUlids`
- Translatable: `title`
- Casts: `type` → `SectionType` enum, `country_codes` → array
- Relationship: `sectionLocations()` → `HasMany(HomeSectionLocation)` ordered by `sort_order`
- `booted()` hook: calls `Cache::forget('home_sections')` on `saved` and `deleted`

**`app/Models/HomeSectionLocation.php`**

- Relationships: `homeSection()`, `location()`
- `booted()` hook: calls `Cache::forget('home_sections')` on `saved` and `deleted`

### Enum

**`app/Enums/HomeSection/SectionType.php`**

```php
enum SectionType: string {
    case Locations = 'locations';        // horizontal strip of location cards
    case Accommodations = 'accommodations'; // grid of accommodations for one location
}
```

### Public API

**`GET /api/public/home-sections`**

- **Controller:** `app/Http/Controllers/Public/HomeSectionController`
- **Route name:** `api.publichome-sections.index`
- No authentication required
- Detects user's country from IP (cached 24h per IP via `stevebauman/location`)
- All active sections are cached forever under key `home_sections`
- Sections are filtered per-request by `country_codes` (null = visible to all)
- Cache is invalidated automatically via model hooks on any save/delete

**Response:**
```json
[
  {
    "id": "01JTXXX...",
    "title": { "en": "Top Destinations", "sr": "Najpopularnije destinacije" },
    "type": "locations",
    "sort_order": 0,
    "country_codes": null,
    "locations": [
      {
        "id": "01JTYYY...",
        "name": { "en": "Belgrade", "sr": "Beograd" },
        "photo_url": "https://...",
        "country_code": "RS"
      }
    ]
  }
]
```

### SuperAdmin CRUD

**`app/Http/Controllers/SuperAdmin/HomeSectionController`**

| Method | Route | Action |
|---|---|---|
| `GET` | `/admin/home-sections` | List all sections |
| `GET` | `/admin/home-sections/create` | Create form |
| `POST` | `/admin/home-sections` | Store new section |
| `GET` | `/admin/home-sections/{id}/edit` | Edit form |
| `PUT` | `/admin/home-sections/{id}` | Update section |
| `DELETE` | `/admin/home-sections/{id}` | Delete section |
| `GET` | `/admin/home-sections/search-locations` | Select2 AJAX search |
| `POST` | `/admin/home-sections/{id}/locations` | Add location to section |
| `DELETE` | `/admin/home-sections/{id}/locations/{sectionLocation}` | Remove location |

### Removed from `LocationController`

- `nearby()` method (IP geolocation + Typesense geo search) — removed
- Corresponding route `GET /api/public/nearby-locations` — removed

---

## SuperAdmin UI (Blade)

Views in `resources/views/super-admin/home-sections/`:

- **`index.blade.php`** — table listing all sections with sort order, type, countries, location count, active status
- **`create.blade.php`** / **`edit.blade.php`** — standard form wrappers
- **`partials/forms/home-section.blade.php`** — shared form partial:
  - Tabbed multilingual title input (EN required, SR/DE optional)
  - Section type dropdown
  - Sort order input
  - Country targeting multi-select (Select2, empty = all countries)
  - Active checkbox

The edit page additionally has a **Locations panel**:
- Select2 AJAX search to find and add a location
- Table of current locations with Remove button

Nav link added to `layouts/superadmin.blade.php`.

---

## Country Targeting

Admins can restrict a section to specific countries via ISO-2 codes (e.g. `RS`, `BA`, `DE`).

**How it works:**
1. All active sections are cached once in `home_sections` (includes `country_codes`)
2. On each request to `/api/public/home-sections`, the user's IP is resolved to a country code (cached 24h)
3. Sections are filtered: `country_codes = null` → shown to all; otherwise only matching countries
4. If IP detection fails (local/VPN), country-targeted sections are hidden; global sections still show
5. In non-production environments, falls back to `config('location.testing.ip')` if IP resolves to nothing

---

## Frontend

### Vuex Module — `store/modules/home/`

| File | Purpose |
|---|---|
| `state.js` | `sections: []`, `loading: false` |
| `mutation-types.js` | `SET_SECTIONS`, `SET_LOADING` |
| `mutations.js` | Sets state |
| `actions.js` | `fetchHomeSections` → `GET /api/public/home-sections` |
| `getters.js` | `sections`, `loading` |
| `index.js` | Registered as `home` module (namespaced) |

### Components

**`Welcome.vue`** (updated)

- Dispatches `home/fetchHomeSections` on mount
- Shows skeleton while loading
- Renders `<home-section-component>` for each section

**`HomeSectionComponent.vue`** (new)

- Accepts `section` prop
- `type === 'locations'` → renders `<locations-row>`
- `type === 'accommodations'` → renders `<location-section>` with the first location in the section
- Resolves multilingual `title` and `name` objects: prefers `en`, falls back to first available key

**`LocationsRow.vue`** (new)

- Props: `title` (string), `locations` (array)
- Horizontal scrollable strip of circular location cards with photo + country flag emoji
- Click navigates to search filtered by location

**`LocationSection.vue`** (existing, updated)

- Added optional `title` prop — if provided, overrides `location.name` in the section header
- Used for `accommodations` type sections

### Deleted Components

- `NearbyLocations.vue` — removed (nearby/geo feature removed)
- `FeaturedLocations.vue` — removed (replaced by dynamic system)
- `RecomendedAccommodations.vue` — removed (replaced by `HomeSectionComponent`)
- `DestinationsGrid.vue` — removed (was unused)

---

## Seeder

**`database/seeders/HomeSectionSeeder.php`**

Creates 3 example sections:

| Section | Type | Countries | Locations |
|---|---|---|---|
| Top Destinations | `locations` | All | First 9 from DB |
| Stay in Belgrade | `accommodations` | All | First location from DB |
| Discover Serbia | `locations` | `RS` only | First 9 from DB |

Called automatically from `DatabaseSeeder`.

---

## Cache Strategy

| Cache Key | Content | TTL | Invalidated by |
|---|---|---|---|
| `home_sections` | All active sections with resolved locations | Forever | `HomeSection` / `HomeSectionLocation` save or delete |
| `geo_ip:{ip}` | IP → country position object | 24 hours | Natural expiry |

---

## Files Changed / Created

### New
- `database/migrations/..._create_home_sections_table.php`
- `database/migrations/..._create_home_section_locations_table.php`
- `database/migrations/..._add_country_codes_to_home_sections_table.php`
- `app/Enums/HomeSection/SectionType.php`
- `app/Models/HomeSection.php`
- `app/Models/HomeSectionLocation.php`
- `app/Http/Controllers/Public/HomeSectionController.php`
- `app/Http/Controllers/SuperAdmin/HomeSectionController.php`
- `resources/views/super-admin/home-sections/index.blade.php`
- `resources/views/super-admin/home-sections/create.blade.php`
- `resources/views/super-admin/home-sections/edit.blade.php`
- `resources/views/super-admin/partials/forms/home-section.blade.php`
- `resources/js/store/modules/home/` (index, state, mutations, mutation-types, actions, getters)
- `resources/js/src/views/welcome/components/LocationsRow.vue`
- `resources/js/src/views/welcome/components/HomeSectionComponent.vue`
- `database/seeders/HomeSectionSeeder.php`

### Updated
- `routes/api.php` — added `home-sections`, removed `nearby-locations`
- `routes/admin.php` — added home-sections resource + location sub-routes
- `resources/views/layouts/superadmin.blade.php` — added nav link
- `resources/js/store/index.js` — registered `home` module
- `resources/js/src/views/welcome/Welcome.vue` — rewritten
- `resources/js/src/views/welcome/components/LocationSection.vue` — added `title` prop
- `database/seeders/DatabaseSeeder.php` — calls `HomeSectionSeeder`
- `app/Http/Controllers/Public/LocationController.php` — removed `nearby()` method

### Deleted
- `resources/js/src/views/welcome/components/NearbyLocations.vue`
- `resources/js/src/views/welcome/components/FeaturedLocations.vue`
- `resources/js/src/views/welcome/components/RecomendedAccommodations.vue`
- `resources/js/src/views/welcome/components/DestinationsGrid.vue`
