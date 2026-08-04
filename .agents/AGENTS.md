# CBC-Apps Project Rules & Architecture Guardrails

## Core Stack & Architecture Flow
- **Flow**: `Controller -> Service/Repository -> Pipeline -> Model`.
- **Controllers**:
  - Extend `BaseController` by default.
  - Inject primary repository into `$this->service` (Pattern 1 / 2).
  - Delegate standard CRUD endpoints to `_index()`, `_store()`, `_update()`, `_destroy()`, `_multiDestroy()`.
  - For non-repository controllers (services/pipelines only), set `$this->service = null` and use `requireService()` if repo assertion is needed.
  - Never call Eloquent models directly in controllers when a repository exists.
- **Repositories**:
  - Extend `AbstractRepoService`.
  - Override `buildSearchQuery()` for filtering; keep pagination/sorting in base implementation.
  - Expose helper methods named by intent: `get{Entity}`, `find{Criteria}`, `getOptions()`, `search()`.
- **Validation & Business Logic**:
  - Validation lives in `app/Http/Requests`. Domain rules live in `app/Rules`.
  - Multi-step actions anchor in `app/Pipelines`. Side effects (mail, cache, audit) belong in `app/Observers` or dedicated Services.

## Deployment Topology & Module Access Control
- **Dual Surface**: Trust local server (`192.168.36.10`) vs Public internet server (`onecbc.philrice.gov.ph`).
- **Module Access Control**: Enforced centrally via `DeploymentAccessService` and `deployment.access:<module>` middleware.
- **Admin Bypass**: Authenticated admins bypass deployment-access restrictions in both backend and frontend. Non-admin users follow deployment/mode policy.
- **Symmetry**: Ensure route middleware, shared Inertia props, welcome cards, app navigation, and APIs use matching module keys and policies.

## Security & Guest API Guardrails
- **Public Surface (`api/guest/*`)**: Treat as public internet. Default to read-only.
- **Authorization**: NEVER authorize actions using caller-supplied parameters like `employee_id`, email, or participant IDs. Derive identity from authenticated user context or signed/OTP tokens.
- **Payload Sanitization**: Public endpoints MUST return sanitized DTO/Resource objects; never expose raw `Model::toArray()` or sensitive PII (contact numbers, notes).
- **PDF Assets**: Store generated PDFs in `storage/app/private/generated-pdfs` and stream only via authorized controllers.

## Data & Notification Standards
- **Personnel ID**: Generate non-PhilRice/outsider IDs via shared personnel ID service (`CBC-YY-0000` format) backed by `new_barcodes` and `personnels` collision scans.
- **Realtime (Reverb)**: Use Laravel Reverb for web-sockets. Broadcast DTO/invalidation hints from services/jobs, feature-flagged via `config/realtime.php`.
- **Notifications**: Queue-first mailables. Recipient resolution must resolve through `users` table and `config/notifications.php`.

## Verification & Tracking Requirements
- **Route Maps**: Regenerate `resources/js/ziggy.js` (`php artisan ziggy:generate resources/js/ziggy.js`) after any route additions, removals, or guest exposure changes.
- **Testing Database**: PHPUnit MUST target dedicated testing database (`cbc_one_db_test`) as configured in `phpunit.xml`.
- **Tracker Update**: Update `.github/codebase-analysis-report-2026-03-25.md` whenever codebase issues are discovered, resolved, or deferred.
