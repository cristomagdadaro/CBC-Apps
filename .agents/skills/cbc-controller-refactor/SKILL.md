---
name: cbc-controller-refactor
description: Guidelines and step-by-step workflow for refactoring CBC-Apps legacy controllers to BaseController and repository patterns.
---

# CBC Controller Refactoring Workflow

Use this skill when converting legacy controllers that extend `Controller` directly to `BaseController` standards in `CBC-Apps`.

## Implementation Workflow

1. **Identify Collaborators**:
   - Primary Repository -> Assign to `$this->service`.
   - Secondary Repositories/Services -> Assign to private/protected properties (e.g. `$logService`).

2. **Select Binding Pattern**:
   - **Pattern 1 (Standard Repo)**: `$this->service = $repository;` -> Delegate CRUD to `_index()`, `_store()`, `_update()`, `_destroy()`.
   - **Pattern 2 (Aggregator)**: `$this->service = $primaryRepo; $this->secondaryRepo = $secondaryRepo;`.
   - **Pattern 3 (Service/Pipeline)**: `$this->service = null;` Keep specialized service calls explicit.
   - **Pattern 4 (Static/View)**: Return JSON/Inertia directly.

3. **Refactor Code**:
   - Change inheritance to `extends BaseController`.
   - Refactor constructor injection.
   - Replace direct model calls with repository methods or `buildSearchQuery()`.
   - Preserve existing route names, request contracts, and response shapes.

4. **Verify & Update Artifacts**:
   - Run tests: `php artisan test tests/Feature/...`
   - Regenerate Ziggy routes: `php artisan ziggy:generate resources/js/ziggy.js`
   - Update issue tracker in `.github/codebase-analysis-report-2026-03-25.md`.
