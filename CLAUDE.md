# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Acomody is a full-stack accommodation search and management platform. The backend is Laravel 12 (PHP 8.4) with PostgreSQL and Typesense for search. The frontend is a Vue 3 SPA served via Laravel's Blade entry point with Vite.

## Commands

### Development

```bash
composer dev        # Start all services concurrently (Laravel, queue, logs, Vite)
```

This runs `php artisan serve`, `php artisan queue:listen --tries=1`, `php artisan pail --timeout=0`, and `npm run dev` in parallel.

### Build

```bash
npm run build       # Build frontend assets
composer install    # Install PHP dependencies
npm ci              # Install Node dependencies (CI)
php artisan migrate # Run database migrations
```

### Testing

```bash
composer test                   # Clear config and run all tests
php artisan test                # Run all tests
php artisan test --parallel     # Run tests in parallel with ParaTest
php artisan test tests/Feature/SomeTest.php  # Run a single test file
php artisan test --filter=test_method_name   # Run a single test by name
```

### Linting / Formatting

```bash
./vendor/bin/pint               # Run Laravel Pint (PHP formatter)
./vendor/bin/pint --test        # Check without modifying
```

## Architecture

### Backend (Laravel)

The app follows a **Controller → Service → Model** pattern. Controllers handle HTTP concerns; Services contain business logic.

Key service classes in `app/Services/`:
- `SearchService` — Typesense-backed search for accommodations and locations
- `PricingService` / `FeeService` / `TaxService` — Complex pricing calculation pipeline
- `PhotoService` / `ImageUploadService` — Image handling with MinIO (S3)
- `AccommodationService` — Core accommodation CRUD and lifecycle
- `TypesenseCollectionService` — Manages Typesense index schemas
- `AvailabilityService` — Booking availability and period management

Enums in `app/Enums/` are namespace-grouped by domain (e.g., `App\Enums\Accommodation\BookingType`, `App\Enums\Fee\ChargeType`). Use enums for any typed status or type values.

API routes in `routes/api.php` are split into:
- **Public** — auth endpoints, `/public/*`, `/search/*`
- **Protected** — `auth:sanctum` middleware group for user-specific resources

### Frontend (Vue 3 SPA)

The frontend lives in `resources/js/`. The `@` alias resolves to `resources/js/`.

**Entry point:** `resources/js/app.js` — initializes auth state before mounting the app.

**Structure:**
```
resources/js/
├── app.js              # App entry, global component registration
├── config.js           # App-wide config (modal names, UI constants)
├── runtime-constants.js # Static data (currencies, countries, sort options)
├── router/             # Vue Router (index.js + per-feature router files)
├── store/              # Vuex store
│   └── modules/        # auth, user, hosting, search, accommodation, ui
├── services/           # API client (apiClient.js, enhancedFluentApi.js)
├── src/
│   ├── components/     # Reusable components + common/ subdirectory
│   ├── layouts/        # BaseWrapper, FormWrapper, SearchWrapper
│   ├── modals/         # Auth modals (login, signup, forgot password), filters modal
│   └── views/          # Page components grouped by feature
└── utils/
```

**Vuex store modules** (all namespaced):
- `auth` — token storage, login/logout, `isLoggedIn` getter
- `user` — current user profile
- `hosting` — host dashboard; has nested `createAccommodation` and `listings` submodules
- `search` — search params, filters, results, pagination
- `accommodation` — accommodation detail view state
- `ui` — modal open/close state, global loading

Each module follows the pattern: `index.js`, `state.js`, `mutations.js`, `mutation-types.js`, `actions.js`, `getters.js`.

**API client:** `resources/js/services/apiClient.js` wraps `EnhancedFluentApiClient`. It auto-adds CSRF and auth headers. Public endpoints (no auth token) are declared by pattern in `apiClient.js`. Use `createApiCall()` wrapper for consistent error handling in store actions.

**Router auth guard:** `resources/js/router/index.js` — routes with `meta: { requiresAuth: true }` trigger a login modal and redirect back after auth. The hosting section (`/hosting/*`) requires auth.

**Global components registered in `app.js`:** All `Fwb*` Flowbite Vue components, `BaseWrapper`, `FormWrapper`, `SearchWrapper`, `ValidationAlertBox`, `ActionCard`, `SelectActionCard`, skeleton components, and `IconLoader`.

### Infrastructure

- **Local dev:** SQLite database at `database/database.sqlite`; uses `acomody.local` hostname for HMR
- **Production/staging:** PostgreSQL 17, Redis (cache + queues), MinIO (file storage), Typesense (search), Laravel Horizon (queue dashboard)
- **Docker Compose:** Full stack defined in `docker-compose.yml`
- **CI/CD:** GitHub Actions (`.github/workflows/staging.yml`) with Deployer (`deploy.php`)
