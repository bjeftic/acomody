# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Acomody is a full-stack accommodation search and management platform. The backend is Laravel 12 (PHP 8.4) with PostgreSQL and Typesense for search. The frontend is a Vue 3 SPA served via Laravel's Blade entry point with Vite.

## Project Overview details

When someone creates accommodation for example in Serbia, base currency of that accommodation is RSD.

## Internationalisation (i18n)

The app supports 5 locales: `en`, `sr`, `hr`, `mk`, `sl`.

### Serbian (`sr`) — Latin script, Ekavian dialect

Serbian translations **must always** use Latin script and the **Ekavian dialect**. Never use Cyrillic or Ijekavian forms.

Common Ijekavian→Ekavian corrections:
| Ijekavian (wrong) | Ekavian (correct) |
|---|---|
| cijena | cena |
| cijeli / cijelo | celi / celo |
| smještaj | smeštaj |
| mjesto / mjesta | mesto / mesta |
| prijevoz | prevoz |
| promijeniti | promeniti |
| uvijek | uvek |
| prije | pre |
| Podijelite | Podelite |
| Savjeti | Saveti |
| bit će | biće |
| Primit ćete | Primićete |
| obavijest | obaveštenje |
| primjenjiv | primenjiv |
| Uvjeti | Uslovi |

### Vue component translations

- Use component-level `<i18n lang="yaml">` blocks — one block per `.vue` file.
- Access with `$t('key')` for strings, `$tm('key')` + `.map(d => $rt(d))` for arrays.
- All 5 locales (`en`, `sr`, `hr`, `mk`, `sl`) must be present in every `<i18n>` block.

### Translatable data in JS config files

- If config data contains English text that users see, translate it via one of:
  1. **Backend RuntimeConstants** with `__()` helpers (e.g. `BookingType` enum labels) — injected as meta tags by Laravel Blade, consumed via `config.ui.*`.
  2. **Component computed property** using `$t()` / `$tm()` / `$rt()` — map config IDs to translated strings in the component, not in the config file itself.
- Never leave user-visible English strings in `.js` config files.

### RuntimeConstants key naming

The string value of each `RuntimeConstants` constant is used **directly as the HTML meta tag name**, which `config.js` reads via `document.querySelector('meta[name="..."]')`. The value **must be camelCase** to match how `config.js` accesses it (e.g., `config.ui.daysOfWeek`).

```php
// Correct — camelCase value matches config.js access pattern
const DAYS_OF_WEEK = 'daysOfWeek';

// Wrong — UPPERCASE value won't be found by config.js
const DAYS_OF_WEEK = 'DAYS_OF_WEEK';
```

### Backend locale for Blade rendering

`DetectLanguage` middleware runs on **both** `api` and `web` middleware groups (see `bootstrap/app.php`). This ensures RuntimeConstants injected into the Blade template (on the initial page load) are rendered with the correct locale.

### Backend translations

- Category labels and other server-side strings use `lang/{locale}/*.php` files.
- `AmenityResource` returns `category_label` via `trans('amenity_category.'.$this->category)`.
- `BookingType::toArray()` uses `__()` so labels are locale-aware when called via RuntimeConstants.

## Testing Policy

**Every feature addition or change must be covered by tests.** This applies to the entire application, not just specific domains:

- When adding new functionality → write new tests
- When modifying existing functionality → update affected tests and verify they pass
- When fixing a bug → add a regression test that would have caught it
- Tests must pass before a task is considered done — run affected tests after every change

## Cross-Cutting Concerns — Prices, Bookings, Accommodations

When working on anything related to **prices**, **bookings**, or **accommodations**, always check and update **all** of the following layers — do not treat any as optional:

1. **Frontend (Vue) components** — guest-facing views, search results, accommodation detail, booking flow
2. **Frontend (Vue) host dashboard** — host listings, host booking management, earnings/pricing panels
3. **Backend controllers & services** — API endpoints, service classes (`PricingService`, `BookingService`, `AccommodationService`, etc.)
4. **Superadmin area** — admin views/controllers for managing accommodations, bookings, and pricing
5. **Emails** (`app/Mail/`) — booking confirmation, cancellation, pricing summaries, host notifications
6. **In-app notifications** — `NotificationType` enum + notification classes affected by the change
7. **Tests** — update existing tests and add new ones for all changed/added functionality across all layers above

If a change is intentionally scoped to only some layers, explicitly note which layers were skipped and why.

## Frontend — Mobile Responsiveness

All frontend Vue components must support both desktop and mobile. When creating or modifying any component:
- Use Tailwind responsive prefixes (`md:`, `lg:`, `sm:`) for all layouts
- Mobile-first: default classes apply to mobile, prefixed classes override for larger screens
- Never use fixed widths that would overflow on small screens — use `w-full`, `max-w-*`, `flex-wrap`, `min-w-0` as needed
- Use `gap-*` for spacing between siblings instead of margins
- Test layouts at both mobile (< 768px) and desktop (≥ 768px) breakpoints
- Horizontal `flex` rows with multiple items should have `flex-wrap` or collapse to stacked layout on mobile (`flex-col md:flex-row`)
- Typography: use responsive sizes where appropriate (`text-2xl md:text-3xl`)
- The app uses `md:` as the main mobile/desktop breakpoint (768px)

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

### Typesense searchability rules

An `Accommodation` is indexed in Typesense only when **both** conditions are met:
1. `is_active = true` (accommodation is approved)
2. The owner's `HostProfile` exists and `is_complete = true` (display_name + contact_email + phone all filled)

This is enforced in `Accommodation::isSearchable()`. Two triggers re-index accommodations:
- **Draft approved** (`CreateAccommodation` job) — calls `$accommodation->searchable()` after creation. Scout calls `isSearchable()` so it only indexes if host profile is already complete. The approval email includes a warning if the profile is not yet complete.
- **Host profile completed** (`HostProfile::booted()` saved hook) — when `is_complete` changes to `true`, re-indexes all active accommodations and sends a "listings are now live" email.

Emails involved:
- `AccommodationApprovedMail` — sent on approval; `$hostProfileComplete` bool controls whether it shows a warning or "you're live" message
- `AccommodationsNowSearchableMail` (`app/Mail/Host/`) — sent when host profile completion triggers indexing of existing accommodations

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

**All `apiClient` calls must live in Vuex `actions.js` files** — never call `apiClient` directly from Vue components. Components dispatch actions; actions call the API.

**Router auth guard:** `resources/js/router/index.js` — routes with `meta: { requiresAuth: true }` trigger a login modal and redirect back after auth. The hosting section (`/hosting/*`) requires auth.

**Global components registered in `app.js`:** All `Fwb*` Flowbite Vue components, `BaseWrapper`, `FormWrapper`, `SearchWrapper`, `ValidationAlertBox`, `ActionCard`, `SelectActionCard`, skeleton components, and `IconLoader`.

### Subscription Plans

Every host has a `HostSubscription` (one per user). Plans are seeded via `PlanSeeder` and managed only by superadmins.

**Plans** (`App\Models\Plan`):
| Code | Name | Price | Commission | Notes |
|------|------|-------|------------|-------|
| `free` | Free | €0/mo | 10% | Default plan for all hosts |
| `club` | Club | €30/mo | 5% | Premium plan |

Both plans allow **unlimited accommodations** (`max_accommodations = null`).

**Key enums** (`App\Enums\Subscription\`):
- `PlanCode` — `Free`, `Club`
- `SubscriptionStatus` — `Active`, `Trial`, `Expired`, `Cancelled` (`isActive()` returns true for Active + Trial)
- `BillingPeriod` — `Monthly`, `Annual`

**Early host benefit** (`HostSubscription::is_early_host`):
- Hosts who register during cold start phase get `is_early_host = true` and **0% commission** regardless of plan
- `early_host_expires_at = null` means cold start hasn't ended yet — benefit is still pending activation
- When the `cold_start` feature flag is disabled, `early_host_expires_at` is set to `now() + 6 months` automatically (see `FeatureFlag::booted()`)
- `HostSubscription::isCommissionFree()` → true only if subscription is active AND early host benefit is active

**`SubscriptionService`** (inject or use `app(SubscriptionService::class)`):
- `getActivePlan(User)` — returns current plan or Free plan as fallback
- `getCommissionRate(User)` — effective commission rate (0 if early host active)
- `isCommissionFree(User)`, `isEarlyHost(User)`
- `canAddAccommodation(User)` — checks against `plan->max_accommodations`
- `assignFreePlan(User)` — creates/updates `HostSubscription` to Free plan (called during registration)
- `markAsEarlyHost(User)` — sets `is_early_host = true`, `early_host_expires_at = null`

**API routes**:
- `GET /api/plans` — public, lists all active plans
- `GET /api/host/subscription` — protected, returns current user's subscription + plan details

**User model relationships**: `$user->hostSubscription` (hasOne), free plan is assigned automatically on registration.

### Infrastructure

- **Local dev:** SQLite database at `database/database.sqlite`; uses `acomody.local` hostname for HMR
- **Production/staging:** PostgreSQL 17, Redis (cache + queues), MinIO (file storage), Typesense (search), Laravel Horizon (queue dashboard)
- **Docker Compose:** Full stack defined in `docker-compose.yml`
- **CI/CD:** GitHub Actions (`.github/workflows/staging.yml`) with Deployer (`deploy.php`)

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.18
- laravel/framework (LARAVEL) - v12
- laravel/horizon (HORIZON) - v5
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- laravel/scout (SCOUT) - v10
- larastan/larastan (LARASTAN) - v3
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v3
- phpunit/phpunit (PHPUNIT) - v11
- vue (VUE) - v3
- tailwindcss (TAILWINDCSS) - v3

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `pest-testing` — Tests applications using the Pest 3 PHP framework. Activates when writing tests, creating unit or feature tests, adding assertions, testing Livewire components, architecture testing, debugging test failures, working with datasets or mocking; or when the user mentions test, spec, TDD, expects, assertion, coverage, or needs to verify functionality works.
- `tailwindcss-development` — Styles applications using Tailwind CSS v3 utilities. Activates when adding styles, restyling components, working with gradients, spacing, layout, flex, grid, responsive design, dark mode, colors, typography, or borders; or when the user mentions CSS, styling, classes, Tailwind, restyle, hero section, cards, buttons, or any visual/UI changes.
- `eloquent-best-practices` — Best practices for Laravel Eloquent ORM including query optimization, relationship management, and avoiding common pitfalls like N+1 queries.
- `laravel-specialist` — Use when building Laravel 10+ applications requiring Eloquent ORM, API resources, or queue systems. Invoke for Laravel models, Livewire components, Sanctum authentication, Horizon queues.
- `php-best-practices` — PHP 8.5+ modern patterns, PSR standards, and SOLID principles. Use when reviewing PHP code, checking type safety, auditing code quality, or ensuring PHP best practices. Triggers on &quot;review PHP&quot;, &quot;check PHP code&quot;, &quot;audit PHP&quot;, or &quot;PHP best practices&quot;.
- `php-pro` — Use when building PHP applications with modern PHP 8.3+ features, Laravel, or Symfony frameworks. Invoke for strict typing, PHPStan level 9, async patterns with Swoole, PSR standards.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `vendor/bin/sail npm run build`, `vendor/bin/sail npm run dev`, or `vendor/bin/sail composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan

- Use the `list-artisan-commands` tool when you need to call an Artisan command to double-check the available parameters.

## URLs

- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Tinker / Debugging

- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.
- Use the `database-schema` tool to inspect table structure before writing migrations or models.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before trying other approaches when working with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries at once. For example: `['rate limiting', 'routing rate limiting', 'routing']`. The most relevant results will be returned first.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.

## Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
    - `public function __construct(public GitHub $github) { }`
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

## Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<!-- Explicit Return Types and Method Params -->
```php
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
```

## Enums

- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

## Comments

- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless the logic is exceptionally complex.

## PHPDoc Blocks

- Add useful array shape type definitions when appropriate.

=== sail rules ===

# Laravel Sail

- This project runs inside Laravel Sail's Docker containers. You MUST execute all commands through Sail.
- Start services using `vendor/bin/sail up -d` and stop them with `vendor/bin/sail stop`.
- Open the application in the browser by running `vendor/bin/sail open`.
- Always prefix PHP, Artisan, Composer, and Node commands with `vendor/bin/sail`. Examples:
    - Run Artisan Commands: `vendor/bin/sail artisan migrate`
    - Install Composer packages: `vendor/bin/sail composer install`
    - Execute Node commands: `vendor/bin/sail npm run dev`
    - Execute PHP scripts: `vendor/bin/sail php [script]`
- View all available Sail commands by running `vendor/bin/sail` without arguments.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `vendor/bin/sail artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `vendor/bin/sail artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `vendor/bin/sail artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

## Database

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `vendor/bin/sail artisan make:model`.

### APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## Controllers & Validation

- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

## Authentication & Authorization

- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).
- Never use withoutAuthorization method (only in seeders and migrations).

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Queues

- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

## Configuration

- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `vendor/bin/sail artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `vendor/bin/sail npm run build` or ask the user to run `vendor/bin/sail npm run dev` or `vendor/bin/sail composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app\Console\Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/sail bin pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/sail bin pint --test --format agent`, simply run `vendor/bin/sail bin pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `vendor/bin/sail artisan make:test --pest {name}`.
- Run tests: `vendor/bin/sail artisan test --compact` or filter: `vendor/bin/sail artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.
- CRITICAL: ALWAYS use `search-docs` tool for version-specific Pest documentation and updated code examples.
- IMPORTANT: Activate `pest-testing` every time you're working with a Pest or testing-related task.

=== tailwindcss/core rules ===

# Tailwind CSS

- Always use existing Tailwind conventions; check project patterns before adding new ones.
- IMPORTANT: Always use `search-docs` tool for version-specific Tailwind CSS documentation and updated code examples. Never rely on training data.
- IMPORTANT: Activate `tailwindcss-development` every time you're working with a Tailwind CSS or styling-related task.

</laravel-boost-guidelines>
