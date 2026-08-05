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

## Senior UX/UI & Visual Balance Standards

- **Mobile-First Density & Rhythm**: Remove excessive whitespace on mobile viewports (`px-2.5 sm:px-6`, `py-3 sm:py-6`, `gap-2.5 sm:gap-4`). Never hardcode fixed margins (e.g. `mx-5`) or min-widths (`min-w-[300px]`) that break mobile grids or cause horizontal overflow.
- **Typography & Scale Hierarchy**: Maintain consistent typographic rhythm (`text-[0.65rem] sm:text-xs` for micro-badges, `text-xs sm:text-sm` for secondary text/actions, `text-sm sm:text-base` for body, `text-lg sm:text-2xl` for titles/metrics).
- **Visual Weight & Alignment**: Use symmetrical padding (`p-3 sm:p-5`), matching icon container proportions (`h-8 w-8 sm:h-11 sm:w-11`), consistent border-radii (`rounded-xl sm:rounded-2xl`), and balanced elevation shadows.
- **Dark Mode Elevation & Contrast**: Provide full, rich dark mode support (`dark:bg-slate-900`, `dark:border-slate-800`, `dark:text-slate-100`, `dark:text-slate-300`). Maintain high WCAG-compliant contrast ratios without harsh stark whites on OLED screens.
- **Micro-Interactions & Depth**: Integrate subtle hover elevations (`hover:-translate-y-0.5`), smooth transitions (`transition-all duration-300`), glassmorphic overlays (`backdrop-blur-md bg-white/95 dark:bg-slate-900/90`), and clear interactive states.

## Modern Laravel & Repository Practices

- **Language Features**: Favor typed properties, constructor property promotion, dedicated DTOs (over array inputs), `match`, `enum` filters, and value objects. Avoid sprawling `if/else` chains.
- **Constants**: Do not hardcode statuses (e.g. "Active", "Suspended"); use constants from `config/system.php`.
- **Repository Design**: Enforce single responsibility (one repository per aggregate root). Cross-model operations should compose repositories.
- **Nullable Service Safety**: When a controller uses `$this->service = null`, never call base CRUD methods (`_index()`, `_store()`, etc.). Use `requireService()` inside controllers to assert repository existence.

## RBAC & Frontend Conventions

- **RBAC**: Roles are defined in `app/Enums/Role.php`. Permissions live in `config/rbac.php`. Gate definitions surface in `AuthServiceProvider`.
- **Frontend Context**: Navigation comes through `AppLayout` and `useAuthorization()`. Centralize frontend auth state, admin detection, roles, and permissions through `resources/js/Modules/composables/useAppContext.js` and global app properties (`$isAdminUser`, `$currentRoles`, etc.).
- **Vue Thinness**: Keep Vue components small and declarative. Controllers and repositories should prepare the data.
- **Onboarding**: When adding a new public-facing feature or guest page, include a Driver.js guide entry, stable `data-guide` anchors, and update the manual.

## Module Access Control & Verification

- **Testing Matrix**: A Module Access Control change is not complete until validated against a 6-way matrix: (local vs internet deployment) × (guest vs non-admin vs admin user), repeated for `active`, `maintenance`, and `deactivated` modes.
- **Maintenance Mode**: Ensure it blocks all writes, including indirect writes triggered by `GET` endpoints or background callbacks.
- **Drift Prevention**: Module keys must be strictly aligned across web routes, API routes, shared Inertia props, and frontend visibility checks.
