# Copilot Instructions for CBC-Apps

## Mission & Stack
- Laravel 10 (moving toward 11) + Jetstream/Fortify backed by Inertia/Vue 3 and Vite. Treat PHP as the orchestration layer for business rules and keep the Vue SPA thin.
- Follow the `Controller -> Service/Repository -> Pipeline -> Model` flow outlined in existing docs and keep pipelines in [`app/Pipelines`](app/Pipelines) even when a request touches multiple aggregates.
- Side effects belong to observers or services (mail, cache, audit, PDF generation). Keep controllers focused on HTTP semantics.

## Modern Laravel Practices
- Favor typed properties, constructor property promotion, and dedicated DTOs over `array` inputs when reshaping requests.
- Prefer `match`, `enum` filters, and `value objects` for expressive domain logic; avoid sprawling `if/else` chains in controllers.
- Leverage route caching (`php artisan route:cache`) and config caching whenever configuration changes land.
- Keep HTTP responses consistent by reusing shared response macros or `response()->json([...])` structures instead of ad-hoc arrays.
- Use `Resource` classes or `Inertia::share()` to standardize payloads when multiple controllers expose similar data.

## System Architecture
- Requests flow through controllers that either rely on a repository (`AbstractRepoService`) or orchestrate services/pipelines for complex workflows.
- Multi-step actions anchor in pipelines inside [`app/Pipelines`](app/Pipelines); controllers trigger them through services or repositories, never inline loops.
- Observers in [`app/Observers`](app/Observers) handle side effects (cache busting, mail, file cleanups) so we keep controllers declarative.
- Validation lives in [`app/Http/Requests`](app/Http/Requests) and domain-specific rules live in [`app/Rules`](app/Rules).

## Data Access Pattern
- All repository logic lives in [`app/Repositories`](app/Repositories) and extends [`AbstractRepoService`](app/Repositories/AbstractRepoService.php) unless the repository is read-only and clearly documented.
- Repositories expose helper methods named by intent: `get{Entity}`, `find{Criteria}`, `getOptions` and `search()` for pageable lists.
- Override `buildSearchQuery()` for custom filters instead of rewriting `search()`; pagination, sorting, and filtering stay in the base implementation.
- Use the repository whenever a controller needs data—never call Eloquent models or query builders directly in controllers when a repository exists.

## Controller Standards
- Controllers default to extending `BaseController`. Inject the primary repository through the constructor, assign it to `$this->service`, and expose a typed getter for IDE support.
- Keep non-repository collaborators (services, jobs, log helpers) in their own properties; they should not overwrite `$this->service`.
- Standard CRUD actions delegate to `_index()`, `_store()`, `_update()`, `_destroy()`, `_multiDestroy()`, or repository helpers so controllers stay lightweight.
- Preserve route names, response contracts, and boundaries. Any new endpoints should document why they cannot reuse base helpers.

## Delivery Expectations
- Minimize scope: a refactor should touch only the controller, its requests, and closely related repos/services.
- Add unit or feature tests when a refactor changes authorization, validation, or crucial payloads.
- Keep PR descriptions tight: describe the new primary repository, any new service dependencies, and why specialized endpoints remain.

## RBAC Standard
- Roles come from [`app/Enums/Role.php`](app/Enums/Role.php) and link to users via the `role_user` pivot.
- Permissions live in [`config/rbac.php`](config/rbac.php) and gate middleware uses `can:<permission>` to secure routes.
- Gate definitions surface in `AuthServiceProvider` so any new permission must be registered there before use.
- Frontend modules leverage `Inertia::share(['auth.roles', 'auth.permissions'])` and `resources/js/Modules/composables/useAuthorization.js` to mirror backend checks.

## Module Permission Targets
- `event.forms.manage` – event form creation/updates
- `fes.request.approve` – FES request approvals
- `laboratory.logger.manage` – lab logger module
- `inventory.manage` – transactions, items, suppliers, personnels
- `equipment.report.manage` – supply/equipment reports
- `users.manage` – system user administration
- `rental.vehicle.manage`, `rental.venue.manage`, `rental.hostel.manage` – rental modules

## Frontend Conventions
- Navigation comes through `AppLayout`, filtered by permissions, and relies on `useAuthorization()` helpers.
- Guest routes stay public; guard only authenticated modules and surface the shared `auth.*` props on every Inertia visit.
- Keep Vue components small: let controllers/repositories prepare data and let Vue focus on presentation and interaction.
- Avoid duplicating business logic in Vue; mirror backend validation through shared status codes/messages.
- Centralize frontend auth state, admin detection, current-user roles, current-user permissions, and public-service metadata through `resources/js/Modules/composables/useAppContext.js` and the global app properties (`$isAdminUser`, `$currentRoles`, `$currentPermissions`, `$publicServices`). Do not re-implement ad-hoc `is_admin`, role, or permission checks in page components when the shared context already covers them.
- Whenever a new public-facing feature or guest page is added, include a Driver.js guide entry, stable `data-guide` anchors, and a manuals update so onboarding ships with the feature.

## Module Access Control Standard
- Deployment access is controlled centrally through [`app/Services/DeploymentAccessService.php`](../app/Services/DeploymentAccessService.php) and enforced by the `deployment.access:<module>` middleware. Treat that backend evaluation as the source of truth.
- Keep module keys aligned across web routes, API routes, shared Inertia props, and frontend visibility checks. If a page and its related API surface are meant to be governed together, they must use the same module key.
- Separate guest/shared module keys from internal-only module keys when the exposure model differs. Do not hide a guest-facing feature behind an internal module key just because they share a broader business domain.
- Authenticated administrator accounts bypass Module Access Control restrictions in both backend middleware and frontend visibility. Non-admin users must continue to follow the configured access and mode rules.
- Frontend hiding is only a UX mirror of backend policy. If you change module access behavior, update both the backend shared payload and the Vue consumers so navigation, cards, forms, and API authorization stay synchronized.
- When a guest page relies on authenticated mutations, the page must reflect that requirement in the UI instead of presenting write actions that the backend will reject.
- Public service cards on `Welcome.vue`, internal navigation in `AppLayout.vue`, and any page-level action guards must all derive from the same deployment-access payload plus the shared auth globals. Do not maintain separate hard-coded visibility lists per page.
- If a local-trust guest workflow intentionally accepts employee ID or other typed staff identifiers, keep that exception explicitly limited to the module key that is set to local-only. If the module later becomes internet-accessible, revisit the workflow before expanding exposure.

## Guest API Guardrails
- Treat every `api/guest/*` route as a public internet surface, even when authenticated staff can also hit it.
- Default guest routes to read-only. If a guest-facing mutation is unavoidable, require authenticated staff context or a signed/OTP-backed workflow rather than knowledge-based identifiers.
- The Laboratory and ICT Equipment Logger guest flows are the current exception when they are intentionally constrained through `DeploymentAccessService` to a trusted local deployment. In that local-only trust model, employee ID verification is acceptable by product decision and the frontend should reflect the same expectation.
- In that same local-trust logger flow, treat `personnels.updated_at === null` as a fresh-profile flag. Equipment check-in should prompt a one-time personnel/contact update before proceeding, and guest-safe profile initialization should stay narrowly scoped to that logger onboarding step.
- Never authorize actions with caller-supplied identifiers such as `employee_id`, `participant_hash`, or email addresses; derive identity from the authenticated user or a signed callback/token.
- Public list/show endpoints should return explicit DTO/resource-style payloads instead of raw `Model::toArray()` output.
- Availability and conflict-check endpoints may expose boolean availability and normalized windows only; do not leak requester names, contact numbers, event names, notes, or other free text.
- Authenticated callers hitting guest endpoints must still receive the guest-safe payload variant.
- Public personnel lookup must stay exact-match-only and return display-safe identity fields only.
- Generated PDFs belong under `storage/app/private/generated-pdfs` and should only be streamed through authorized controllers.

## Tracker & Generated Assets
- Update [docs/codebase-analysis-report-2026-03-25.md](../docs/codebase-analysis-report-2026-03-25.md) whenever you discover, resolve, or defer a codebase issue.
- Regenerate `resources/js/ziggy.js` after route additions, removals, or guest-surface changes.
- If `vite.config.js` references `tests/setup.ts`, keep the file present and minimal.
- If local frontend verification is blocked by workstation toolchain issues, record the exact blocker separately from code fixes so the tracker stays accurate.

## Personnel ID Standard
- New PhilRice personnel records should still use their official employee ID supplied by the operator.
- New outsider, OJT, thesis, or similar temporary personnel records should not rely on manually typed CBC IDs. Generate the next `CBC-YY-0000` identifier through the shared personnel ID service backed by `new_barcodes`.
- Keep the create-form preview and the actual persisted ID generation aligned, but treat the backend generator as the source of truth so concurrent creates cannot duplicate IDs.

## Realtime / Websocket Standard
- Use Laravel Reverb as the default websocket stack for CBC-Apps whenever realtime server push is required. Do not introduce third-party hosted websocket dependencies unless there is an explicit architectural decision to do so.
- Treat realtime as an extension of the existing HTTP API surface, not a replacement for it. REST endpoints remain the source of truth; websocket messages should usually carry invalidation hints, compact summaries, or bounded DTO-style payloads.
- Broadcast domain events from services, repositories, observers, jobs, or scheduled command flows after persistence succeeds. Do not shape websocket payloads directly inside controllers.
- Prefer private or presence channels for staff operations. Public channels must be rare, explicitly justified, and safe for unauthenticated internet clients.
- Reuse existing RBAC and ownership rules in `routes/channels.php`; channel authorization must match the sensitivity of the underlying module.
- Never broadcast raw model arrays. Use sanitized resource/DTO payloads and keep guest-safe vs staff-safe payload variants separate.
- Default datatable integrations to event-driven invalidation plus refetch instead of broadcasting full table payloads.
- Priority realtime surfaces in this system are: datatables, rental calendars, inventory stock movement, supplies checkout, laboratory/ICT equipment loggers, dashboard counters, form-response monitoring, and certificate batch progress.
- Certificate generation should move toward Reverb-backed progress events while preserving an HTTP fallback path until rollout is proven stable.
- Any realtime feature that touches guest/public flows must preserve the same privacy boundaries already enforced on guest APIs.
- Feature-flag realtime rollout through [`config/realtime.php`](../config/realtime.php) and the `REALTIME_*` env keys. New subscriptions should honor the shared feature flags instead of assuming websocket availability everywhere.
- Google Calendar sync status is a staff-only realtime surface. Keep sync-result broadcasts on private rental/calendar channels and never mirror OAuth or sync-status details onto guest channels.

## Email / Notification Standard
- Treat email delivery as a shared notification domain, not ad-hoc Mail::to(...)->send(...) logic spread across observers and controllers.
- Prefer domain events plus queued listeners or notification dispatch services over direct mail sends.
- Centralize recipient resolution, feature toggles, and module-specific routing rules so certificate emails, form-response alerts, equipment-log lifecycle notices, and supply checkout notices follow one policy layer.
- Use queue-first delivery for operational emails by default. Immediate sending should be exceptional and documented.
- Keep mailables or notifications focused on rendering and channel formatting. Business rules for who gets notified and when belong in dedicated notification services or listeners.
- Log or otherwise audit notification attempts and failures for operational visibility.
- Reuse notification domain events for websocket and in-app notifications when possible so what happened stays separate from how we notify.
- Notification recipient options must resolve through the `users` table. Store user-backed recipient selections in `Option` values and resolve current email addresses from `User` records instead of treating arbitrary raw emails as the source of truth.
- Prefer module-scoped option keys under the notification config map, and keep certificate, form-response, equipment-log, and inventory recipient policy in [`config/notifications.php`](../config/notifications.php).

## Anti-Patterns
- Don’t instantiate `Model` queries directly in controllers; always prefer the relevant repository.
- Don’t overload controllers with pipeline logic—delegate to dedicated pipeline stages in [`app/Pipelines`](app/Pipelines).
- Don’t mutate request objects before validation or spread request data across dozens of services; build DTOs or reusable `FormRequest` helpers instead.
- Don’t bypass middleware (e.g., `can:<permission>`) just to make quick changes; keep authorization centralized.

## Quick Reference
| Resource | Location | Purpose |
| --- | --- | --- |
| `BaseController` | [app/Http/Controllers/BaseController.php](app/Http/Controllers/BaseController.php) | Shared CRUD helpers and nullable `$service` guard.
| `AbstractRepoService` | [app/Repositories/AbstractRepoService.php](app/Repositories/AbstractRepoService.php) | Search, pagination, and helper conventions for repositories.
| `AppServiceProvider` | [app/Providers/AppServiceProvider.php](app/Providers/AppServiceProvider.php) | Observer registration, shared Inertia props, and service bindings.
| RBAC config | [config/rbac.php](config/rbac.php) | Permission map referenced by controllers and middleware.
| Prompts | [.github/prompts/agent.refactor-controllers.prompt.md](.github/prompts/agent.refactor-controllers.prompt.md) | Agent instructions for standardizing controllers that still extend `Controller`.

Helper methods to reuse:
- `_index()`, `_store()`, `_update()`, `_destroy()`, `_multiDestroy()` from `BaseController` for CRUD endpoints.
- `buildSearchQuery()` and `search()` from `AbstractRepoService` for filtering/pagination.
- `requireService()` when a controller temporarily needs to assert that `$this->service` is available.

## Agentic Refactoring Workflow
- Follow the reusable prompt in [.github/prompts/agent.refactor-controllers.prompt.md](.github/prompts/agent.refactor-controllers.prompt.md) when touching legacy controllers that still extend `Controller` directly.
- Kickoff order: start with `FormBuilderController` and work methodically so each controller consistently assigns `$this->service` and keeps other services separate.
- Document new dependencies in controllers and mention why any custom endpoints cannot reuse the shared CRUD helpers.

## Controller-Service Binding Patterns
### Pattern 1: Standard Repository Controller
Use when a controller manages a single resource with CRUD operations.
```php
class ItemController extends BaseController
{
    public function __construct(ItemRepo $repository)
    {
        $this->service = $repository;
    }

    public function index(Request $request)
    {
        return $this->_index($request); // Uses BaseController CRUD
    }
}
```
### Pattern 2: Multi-Repository Controller (Aggregator)
Use when a controller coordinates several domains (e.g., dashboards).
```php
class DashboardController extends BaseController
{
    private TransactionRepo $transactionRepo;

    public function __construct(
        DashboardRepo $dashboardRepo,
        TransactionRepo $transactionRepo
    ) {
        $this->service = $dashboardRepo;
        $this->transactionRepo = $transactionRepo;
    }

    protected function dashboardRepo(): DashboardRepo
    {
        return $this->service;
    }
}
```
### Pattern 3: Service-Based Controller (No Repository)
Use for integrations, file handling, or complex pipelines where `$this->service` remains `null`.
```php
class FormScanController extends BaseController
{
    private QrValidationService $validator;

    public function __construct(QrValidationService $validator)
    {
        $this->validator = $validator;
    }

    public function scan(Request $request, $event_id)
    {
        // $this->requireService(); // Guard if we accidentally assume a repository exists.
        $result = $this->validator->process($request->input('payload'));
        // ...
    }
}
```
### Pattern 4: Form/View Controller (Static Data)
Use when a controller simply serves static or lookup data.
```php
class LocationController extends BaseController
{
    public function regions()
    {
        return response()->json(Region::all());
    }
}
```

## Repository Design Rules
- Single responsibility: One repository handles one aggregate root (e.g., `Item`, `Transaction`, `Form`).
- Cross-model operations should compose repositories (e.g., dashboard repo injecting `TransactionRepo`).
- Naming conventions: `get{Entity}` returns a single entity, `find{Criteria}` returns a collection or `null`, and `getOptions()` provides dropdown pairs.
- Use `buildSearchQuery()` for filtering logic instead of overriding `search()` entirely.

## Nullable Service Safety
- When `$this->service` is `null`, never call `_index()`, `_store()`, `_update()`, `_destroy()`, or `_multiDestroy()`—they depend on a repository.
- Use `requireService()` inside base controllers to fail fast when a repository is expected.
- Load audit logs manually when `$this->service` is `null`; controllers must instantiate models directly and pass them to `loadAuditLogs()`.
- Keep validation in `FormRequest` classes, and let repositories/repo services handle persistence logic.


