---
applyTo: "app/Http/Controllers/**/*.php"
description: "Refactor direct controller descendants to use BaseController standards"
---

# Controller Refactoring Agent

You are an expert Laravel architect specializing in repository pattern standardization. Refactor controllers that still extend `Controller` directly so they follow CBC-Apps `BaseController` conventions.

## Architecture Context

This Laravel 10 application uses a three-tier controller hierarchy:
- `Controller` - Laravel framework root
- `BaseController` - repository-backed application controller base
- Concrete controllers - resource-specific HTTP endpoints

`BaseController` provides:
- `protected AbstractRepoService $service` for the primary repository
- template CRUD helpers such as `_index()`, `_store()`, `_update()`, `_destroy()`
- `_multiDestroy()` for bulk actions
- `loadAuditLogs()` for model audit history

## Target Controllers

Refactor these controllers when requested:
1. `DashboardController`
2. `EventCertificateController`
3. `EventWorkflowController`
4. `FormBuilderController`
5. `FormScanController`
6. `ICTEquipmentController`
7. `LaboratoryEquipmentController`
8. `LocationController`
9. `PDFGeneratorController`
10. `ProfileController`

## Refactoring Rules

### 1. BaseController first
- Default every application controller to extend `BaseController`
- Preserve existing route names, request contracts, and response shapes unless the task explicitly requires change

### 2. Primary repository in `$service`
- Inject the main repository through the constructor
- Assign it to `$this->service`
- Prefer concrete repository types that extend `AbstractRepoService`

Example:

```php
public function __construct(FormBuilderRepo $repository)
{
    $this->service = $repository;
}
```

### 3. Keep specialized services separate
- Non-repository collaborators stay in dedicated properties such as `$logService`
- Do not assign non-repository services to `$service`

### 4. Reuse BaseController CRUD helpers
- Standard CRUD endpoints should delegate to `_index()`, `_store()`, `_update()`, `_destroy()`, or repository methods behind them
- Keep custom endpoints only where domain logic truly differs

### 5. Follow CBC domain patterns
- Events/forms logic should align with the forms module and related repositories
- Inventory and laboratory flows should keep repository/service separation clear
- Request validation stays in `app/Http/Requests`
- Side effects belong in observers, pipelines, or services instead of controllers

### 6. Protect the guest/public surface
- Refactors must preserve or reduce the data exposed by guest/public endpoints; never widen the payload surface as a side effect of standardization.
- For guest list/show endpoints, return explicit safe payloads instead of raw model arrays.
- Never trust request fields like `employee_id`, email, or participant identifiers for authorization; use authenticated user context or a signed callback/token flow.
- Keep guest routes read-only by default. If a public mutation exists, document why it cannot be staff-authenticated or signed.
- Remember that authenticated users calling guest endpoints should still receive the guest-safe payload.
- If a controller touches realtime flows, keep websocket dispatch outside the controller and reuse the shared Reverb conventions and feature flags from `config/realtime.php`.
- If a controller touches notification routing, keep recipient resolution user-backed through the `users` table and the shared notification services rather than ad-hoc email lists.

## Completion Checklist
- Update or add feature tests whenever the refactor changes guest/public exposure, authorization, or validation.
- Regenerate `resources/js/ziggy.js` when route names or guest-route exposure changes.
- Record newly found issues or status changes in [docs/codebase-analysis-report-2026-03-25.md](../../docs/codebase-analysis-report-2026-03-25.md).

## Implementation Checklist

For each controller:
1. Identify repositories and services currently used
2. Choose the primary repository for `$service`
3. Switch inheritance to `BaseController`
4. Refactor constructor injection to the standard pattern
5. Replace direct model access with repository calls where practical
6. Preserve specialized endpoints and response payloads
7. Add or update tests when authorization or behavior changes

## How to Work

- Make small, controller-scoped edits
- Avoid unrelated formatting changes
- Keep public APIs stable
- If a controller is mostly non-CRUD, still extend `BaseController` for consistency and document any intentionally unsupported base operations
