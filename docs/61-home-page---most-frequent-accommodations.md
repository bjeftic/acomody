# Home Page — Most Frequent Accommodations

Branch: `61-home-page---most-frequent-accommodations`

---

## Overview

Implemented a home page section that displays accommodations grouped by location, similar to Airbnb. Each location is shown as a separate segment with a 2-row grid of top-rated accommodations and a "See all" button linking to the search page filtered by that location.

---

## Backend Changes

### New endpoint: `GET /api/public/accommodations`

Returns top-rated accommodations from Typesense, optionally filtered by location.

- **Controller:** `app/Http/Controllers/Public/AccommodationController::index()`
- **Request:** `app/Http/Requests/Public/FeaturedAccommodationsRequest`
- **Route name:** `api.publicaccommodations.index`

**Query parameters:**

| Param | Type | Default | Description |
|---|---|---|---|
| `sortBy` | string | `rating` | One of: `rating`, `reviews`, `price_asc`, `price_desc`, `newest` |
| `page` | integer | `1` | Page number |
| `perPage` | integer | `12` | Max 100 |
| `location_id` | integer | — | Filter by location (must exist in `locations` table) |

**Response:**
```json
{
  "hits": [ { "id": "...", "title": "...", "rating": 4.9, "photos": [], ... } ],
  "found": 24,
  "page": 1,
  "per_page": 12
}
```

---

### New endpoint: `GET /api/public/locations`

Returns up to 10 active locations with translated names.

- **Controller:** `app/Http/Controllers/Public/LocationController::index()`
- **Route name:** `api.publiclocations.index`

**Response:**
```json
[
  { "id": 1, "name": "Belgrade" },
  { "id": 2, "name": "Novi Sad" }
]
```

> **Note:** `name` is a translatable field (Spatie). The controller resolves it via `getTranslation()` using the current app locale with fallback — avoids returning raw `{"en": "..."}` JSON objects.

---

## Frontend Changes

### Component tree

```
Welcome.vue
└── RecomendedAccommodations.vue   (orchestrator — fetches/receives locations)
    └── LocationSection.vue        (one per location — fetches & displays accommodations)
        └── AccommodationCard.vue  (existing search card, reused)
```

---

### `Welcome.vue`

Hardcodes 5 featured locations and passes them to `RecomendedAccommodations` via the `locations` prop.

```js
// TODO: Replace with dynamic logic (e.g. most accommodations, admin-curated, etc.)
const FEATURED_LOCATIONS = [
  { id: 1, name: 'Belgrade' },
  { id: 2, name: 'Novi Sad' },
  { id: 3, name: 'Zlatibor' },
  { id: 4, name: 'Kopaonik' },
  { id: 5, name: 'Niš' },
];
```

Update the IDs to match your actual `locations` table rows.

---

### `RecomendedAccommodations.vue`

Orchestrator component. Accepts an optional `locations` prop.

- If `locations` is passed and non-empty → renders those directly
- If `locations` is empty `[]`, `null`, or not provided → fetches from `GET /api/public/locations` and uses the first 5

Renders a `LocationSection` for each location. Shows full skeleton loading state while locations are being fetched.

---

### `LocationSection.vue`

Self-contained section for a single location.

- Fetches 12 accommodations on mount (`sortBy=rating`, `location_id=X`)
- Displays in a `grid-cols-2 sm:grid-cols-3 lg:grid-cols-6` grid (2 rows of 6 at desktop)
- **"See all"** button → navigates to `page-search` with `locationId` + `locationName` query params
- Card click → navigates to `accommodation-detail`
- Graceful skeleton loading, empty, and error states per section

---

### `Paginator.vue` (new reusable component)

Extracted from `RecomendedAccommodations` and made reusable.

- **Path:** `resources/js/src/components/common/Paginator.vue`
- **Props:** `modelValue` (current page), `totalItems`, `perPage` (default 12)
- **Emits:** `update:modelValue` — compatible with `v-model`
- Only renders when `totalPages > 1`
- Used in both `RecomendedAccommodations.vue` (home) and `SearchResults.vue` (search), replacing `fwb-pagination` in the latter

---

## Z-index Stack

Fixed cards bleeding over the sticky search bar and navbar dropdowns hiding behind it.

| Element | z-index |
|---|---|
| Navbar (`fwb-navbar`) | `z-30` |
| Sticky search bar (`SearchWrapper`) | `z-20` |
| Card badges / Swiper controls | `z-10` |

---

## Tests

**File:** `tests/Feature/Api/FeaturedAccommodationsTest.php` — 12 tests

- Returns 200 without authentication
- Correct JSON structure (`hits`, `found`, `page`, `per_page`)
- Each hit has required keys
- Accepts all valid `sortBy` values
- Rejects invalid `sortBy` with 422
- Rejects `page < 1` and `perPage > 100` with 422
- Returns empty hits when no results found
- Passes `location_id` filter through to `SearchService`

`SearchService` is mocked in all tests — Typesense is not required to be running.

---

## Future Work

- Replace hardcoded `FEATURED_LOCATIONS` in `Welcome.vue` with dynamic logic:
  - Most accommodations per location
  - Admin-curated `is_featured` flag on `locations` table
  - Most bookings / most searched
- `GET /api/public/locations` currently returns first 10 by insertion order — add ordering by accommodation count or a featured flag
