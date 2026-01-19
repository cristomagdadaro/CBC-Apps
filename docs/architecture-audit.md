# Architecture Audit Report

Date: 2026-01-19
Branch: Use-Request-Form
Scope: Backend (Laravel) + Frontend (Vue/Inertia)

## Expected Design Principles
- Backend: Repository-based modeling + pipeline pattern for request/command processing.
- Frontend: DDD-style bounded contexts and clear domain/application/infrastructure layering.
- Generic: Simple, optimized, standardized, maintainable.

## Summary
- Repository pattern is present but inconsistently enforced across controllers.
- Pipeline pattern is not implemented anywhere in the backend.
- Frontend DDD layering is mixed between Modules and Components, causing cross-domain coupling.

---

## Backend Findings

### BE-01: Direct Eloquent usage in controller (bypassing repositories) - ok na
**Location:** app/Http/Controllers/FormController.php
**Observation:** Uses `Form`, `Registration`, `Participant`, `EventRequirement` directly for queries, deletes, and create operations.
**Why this violates:** Repository-based modeling expects controllers to delegate data access to repositories/services.
**Needed action:** Move data access to dedicated repository/service methods and call them from the controller.

### BE-02: Direct model factories and DB transaction in controller - ok na
**Location:** app/Http/Controllers/EventSubformController.php
**Observation:** Creates `Participant` and `Registration` via factories inside controller with `DB::transaction()`.
**Why this violates:** Business workflow in controller bypasses repository/service orchestration.
**Needed action:** Extract workflow to a service or pipeline stage(s) invoked by repository or application service.

### BE-03: Mixed repository and direct model usage in controller - ok na
**Location:** app/Http/Controllers/ParticipantController.php
**Observation:** Uses repository for participant creation, but manually creates `Registration` with direct `Registration::factory()` and in-controller transaction handling.
**Why this violates:** Repository pattern should encapsulate related persistence in a single service layer.
**Needed action:** Move registration creation and transaction handling to a service or repository method.

### BE-04: Direct model create/find in controller - ok na
**Location:** app/Http/Controllers/RequestFormPivotController.php
**Observation:** Uses `Requester`, `UseRequestForm`, `RequestFormPivot` directly instead of repository/service.
**Why this violates:** Repository pattern expects creation and orchestration to be within repositories/services.
**Needed action:** Introduce a request-form service to encapsulate the create flow and reuse in controller.

### BE-05: Direct model queries for view data - ok na
**Location:** app/Http/Controllers/InventoryFormController.php
**Observation:** Uses `Category::select(...)` and `Personnel::all()` directly for Inertia view.
**Why this violates:** Data access should be in repository/services for standardization.
**Needed action:** Move query logic to repository/service methods and inject into controller.

### BE-06: Direct model query in view controller - ok na
**Location:** app/Http/Controllers/LabRequestFormController.php
**Observation:** Uses `RequestFormPivot` directly to fetch guest view data.
**Why this violates:** Repository pattern expects data access to pass through repositories.
**Needed action:** Add repository method to fetch pivot with relations and use it here.

### BE-07: Direct query for transaction enrichment - ok na
**Location:** app/Http/Controllers/SuppEquipReportController.php
**Observation:** Fetches `Transaction::with('item')` directly in controller when building payload.
**Why this violates:** Repository/service should handle data enrichment and domain rules.
**Needed action:** Move enrichment to report service or repository method.

### BE-08: Complex query and filtering built in controller - ok na
**Location:** app/Http/Controllers/TransactionController.php
**Observation:** Builds a multi-join, aggregate query with filter logic inside controller (`remainingStocks`).
**Why this violates:** Query building should live in repository/service/pipeline for reuse and maintainability.
**Needed action:** Move query construction to repository or query object; use pipeline stages for filters.

### BE-09: Direct model usage in PDF generator - ok na
**Location:** app/Http/Controllers/PDFGeneratorController.php
**Observation:** `RequestFormPivot` is accessed directly in controller.
**Why this violates:** Repository pattern expects all data access in services/repositories.
**Needed action:** Use a repository/service method to load the pivot and related models.

### BE-10: Pipeline pattern missing
**Location:** app/ (global)
**Observation:** No Pipeline classes or usage found (no *Pipeline* files in app/).
**Why this violates:** Backend requirement includes pipeline pattern for request/command processing.
**Needed action:** Introduce pipelines for multi-step workflows (e.g., form submission, inventory transaction, request approvals).

---

## Frontend Findings

### FE-01: DDD layering split between Modules and Components - ok na
**Location:** resources/js/Modules/* and resources/js/Components/*
**Observation:** Domain and infrastructure layers exist in Modules, but DataTable domain/infrastructure lives under Components.
**Why this violates:** DDD should centralize bounded contexts consistently (e.g., Modules/*) to avoid fragmented layers.
**Needed action:** Consolidate domain/infrastructure under Modules for consistent bounded-context architecture.

### FE-02: Cross-domain coupling in infrastructure - ok na
**Location:** resources/js/Components/DataTable/infrastracture/CoreApi.js
**Observation:** DataTable infrastructure imports Notification domain service from Components.
**Why this violates:** Infrastructure should depend on application/domain boundaries via contracts, not direct cross-domain services.
**Needed action:** Provide a shared notification interface or application-level service in a common module.

---

## Standardization & Maintainability Gaps

### GM-01: Inconsistent orchestration responsibilities
**Observation:** Multiple controllers implement orchestration, validations, and persistence directly.
**Impact:** Harder to reuse, test, and optimize workflows.
**Needed action:** Move orchestration to services/pipelines with clear responsibilities.

### GM-02: Missing architectural documentation for DDD boundaries
**Observation:** No explicit boundary map for domain modules.
**Impact:** Higher risk of accidental cross-domain dependencies.
**Needed action:** Define domain map and enforce via folder structure and linting rules.

---

## Recommended Next Actions (No Code Changes Implemented)
1. Create application services for form, registration, request-form workflows.
2. Introduce pipelines for multi-step operations (validation, enrichment, persistence, events).
3. Refactor controllers to delegate to repositories/services only.
4. Consolidate frontend domain layers under Modules with clear bounded contexts.
5. Add architectural lint rules or review checklist to prevent regression.
