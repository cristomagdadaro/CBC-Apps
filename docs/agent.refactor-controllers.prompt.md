---
applyTo: "app/Http/Controllers/**/*.php"
description: "Refactor Direct Controller Descendants to use BaseController standards"
---

# Controller Refactoring Agent

You are an expert Laravel architect specializing in repository pattern standardization. Your task is to refactor controllers that currently extend `Controller` directly to properly use `BaseController` conventions.

## Current Architecture Context

This Laravel 10 application uses a three-tier controller hierarchy:
- `Controller` (Laravel base - framework root)
- `BaseController` (AbstractRepoService injection + CRUD template methods)
- Concrete Controllers (resource-specific implementations)

**BaseController provides:**
- `protected AbstractRepoService $service` - repository injection
- `protected _index(request)`, `_store(request)`, `_update(id, request)`, `_destroy(id)` - template CRUD
- `public _multiDestroy(request)` - bulk operations
- `protected loadAuditLogs(model)` - audit trail support

## Target Controllers for Refactoring

The following 9 controllers extend `Controller` directly and need standardization:

1. **DashboardController** - Analytics aggregation (multi-repo)
2. **EventCertificateController** - Complex certificate generation (no repo shown)
3. **EventWorkflowController** - State machines (no repo shown)
4. **FormBuilderController** - Form schema management (FormBuilderRepo)
5. **FormScanController** - QR/Barcode scanning (no repo shown)
6. **ICTEquipmentController** - IT asset tracking (LaboratoryLogService)
7. **LaboratoryEquipmentController** - Lab equipment (LaboratoryLogService + LabEquipmentLogRepo)
8. **LocationController** - Geo-data endpoints (no repo)
9. **PDFGeneratorController** - Document generation (RequestFormPivotRepo)
10. **ProfileController** - User self-service (no repo)

## Refactoring Rules

### Rule 1: Repository Standardization
IF controller uses data access:
- Inject via constructor into `protected $service` (type-hint AbstractRepoService or concrete)
- Move custom repository references to follow naming: `protected {Name}Repo $service`
- Example: `FormBuilderController` should assign `FormBuilderRepo` to `$this->service`

### Rule 2: CRUD Method Alignment
IF controller performs standard CRUD:
- Replace custom `index()` with `_index()` call or override using parent logic
- Use `request` type-hinting: `Request $request` or custom FormRequest
- Delegate to `$this->service->search()`, `$this->service->create()`, etc.

### Rule 3: Service Layer Preservation
IF controller uses specialized services (LaboratoryLogService, etc.):
- Keep additional service as separate property: `protected LaboratoryLogService $logService`
- Do NOT merge into `$service` if service isn't a repository
- BaseController's `$service` MUST be AbstractRepoService subtype

### Rule 4: Non-CRUD Controller Handling
IF controller is purely specialized (no CRUD):
- Still extend BaseController for consistency
- Leave `$service` as nullable or inject a lightweight NullRepo
- Override methods that don't apply with clear docblocks: `@throws \BadMethodCallException`

### Rule 5: Multi-Repository Controllers
IF controller uses multiple repositories (e.g., DashboardController):
- Primary repo → `$this->service`
- Secondary repos → `protected {Type}Repo ${name}Repo`
- Example: DashboardController keeps `DashboardRepo` as `$service`, `TransactionRepo` as `$transactionRepo`

## Implementation Steps for Each Controller

1. **Analyze current dependencies** - List all repos/services used
2. **Determine primary repository** - The main resource this controller manages
3. **Refactor constructor** - Match BaseController injection pattern
4. **Standardize methods** - Use protected `_` prefix for internal CRUD, public for HTTP endpoints
5. **Update method bodies** - Delegate to `$this->service` where applicable
6. **Add audit logging** - Call `loadAuditLogs()` where model instances are loaded

## Before/After Example

### Before (FormBuilderController):
```php
class FormBuilderController extends Controller
{
    protected FormBuilderRepo $repository;
    
    public function __construct(FormBuilderRepo $repository) {
        $this->repository = $repository;
    }
    
    public function indexTemplates(Request $request) {
        return $this->repository->getTemplates();
    }
}