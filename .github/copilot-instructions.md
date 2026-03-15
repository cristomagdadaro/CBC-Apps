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
