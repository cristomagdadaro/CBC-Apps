# Codebase Analysis Report
**Generated:** 2026-03-25 12:10 Asia/Shanghai  
**Project:** CBC-Apps  
**Tech Stack:** Laravel 10, PHP 8.1+, Sanctum, Jetstream, Inertia.js, Vue 3, Vite, Tailwind CSS, MySQL, Playwright, Vitest, PHPUnit  
**Lines of Code:** ~98,601  
**Files Analyzed:** 1,137 tracked files  
**Route Surface:** 229 routes, 40 `api/guest/*` routes, 14 unauthenticated guest write routes

---

## Executive Summary
- Critical Issues: 1
- High Priority: 5
- Medium Priority: 6
- Low Priority: 2
- Quick Wins: 4
- Security Issues: 8

This is a modular Laravel monolith with a clear controller-repository-service split, but the public guest surface is unusually large for an app that also handles staff workflows, approvals, rentals, and document generation. The biggest risks are authorization-by-knowledge (`employee_id` as a credential), permissive cross-origin settings on a stateful Sanctum stack, public PII leakage in guest rental APIs, and cached PDFs being written into the webroot.

There are also two strong delivery risks: the PHPUnit path is effectively pinned to a live MySQL instance while CI only provisions SQLite extensions, and the Vitest setup references a missing `tests/setup.ts`, so the backend and frontend test runners are both currently non-runnable in this workspace.

---

## Discovery & Mapping
### Architecture
- Monolith with modular routing through [`routes/api.php`](../routes/api.php) and [`routes/web.php`](../routes/web.php).
- Public guest APIs live in [`routes/guest.php`](../routes/guest.php).
- Authenticated modules are split into `forms`, `laboratory`, `inventory`, `rental`, `users`, `calendar`, `locations`, and `fes`.
- Backend layering follows README guidance: controllers in `app/Http/Controllers`, validation in `app/Http/Requests`, orchestration in `app/Services` and `app/Repositories`, side effects in `app/Observers`, and multi-step flows in `app/Pipelines`.
- Certificate generation is an asynchronous critical path: [`EventCertificateController`](../app/Http/Controllers/EventCertificateController.php) -> [`ProcessCertificateBatchJob`](../app/Jobs/ProcessCertificateBatchJob.php) -> Python + LibreOffice.

### Entry Points & Critical Paths
- Public web entry points:
  - `/forms/event/{event}`
  - `/forms/request-to-use/{request}`
  - `/inventory/outgoing`
  - `/rental/*`
  - `/laboratory/equipments/{equipment_id}`
- Public API entry points:
  - Event registration and response submission
  - Laboratory and ICT equipment guest logging
  - Public inventory stock lookups
  - Public rental booking and availability
  - Participant email lookup in event workflow
- Authenticated API entry points:
  - Form builder and event workflow management
  - Inventory CRUD
  - Rental approval/status workflows
  - Certificate generation

### Verification Notes
- `npm audit --json`: no JS advisories reported.
- `composer audit --format=json`: advisories reported for `league/commonmark` and `phpseclib/phpseclib`.
- `vendor\bin\phpunit --testsuite Unit`: failed before assertions because [`tests/TestCase.php`](../tests/TestCase.php) requires a live MySQL server.
- `npm test`: failed because [`vite.config.js`](../vite.config.js) points Vitest to missing `tests/setup.ts`.

---

## Security Vulnerabilities
### VULN-001 Public Equipment Log Mutation Uses `employee_id` As Authentication
```yaml
Vulnerability: Public equipment check-in/check-out authorization by user-supplied employee ID
Severity: CRITICAL
CWE ID: CWE-306 / CWE-639
OWASP Category: A01:2021 – Broken Access Control
Location:
  - File: routes/guest.php
    Lines: 47, 151-165
  - File: app/Services/Laboratory/LaboratoryLogService.php
    Lines: 141-166, 198-205, 235-245, 263-279
  - File: app/Http/Controllers/PersonnelController.php
    Lines: 24-47
  - File: app/Models/Personnel.php
    Lines: 15-37

Description: |
  Guest users can mutate laboratory and ICT equipment state without authentication.
  The service authorizes check-out and end-use updates by comparing the caller's
  submitted `employee_id` to the log owner. Because the guest routes are public and
  the guest personnel endpoint returns personnel records directly, a caller only needs
  to know or enumerate an employee ID to impersonate staff and alter equipment state.

Evidence: |
  $personnel = Personnel::where('employee_id', $payload['employee_id'])->first();
  if ($personnel->id !== $activeLog->personnel_id && !$isAdminOverride) {
      abort(403, 'Only the original check-in personnel can check out this equipment.');
  }

Exploitation Scenario: |
  1. Call GET /api/guest/personnel/public and search for a target employee.
  2. Read active equipment logs via GET /api/guest/equipments/active/{employee_id}.
  3. POST to /api/guest/equipments/{identifier}/check-out with the stolen employee_id.
  4. The service accepts the request and closes the log as if the real employee acted.

Immediate Fix: |
  Emergency mitigation: remove public write access and require authenticated staff
  sessions for all guest mutation routes, then reintroduce kiosk/self-service with a
  signed per-log token or OTP.
```

```php
// routes/guest.php
Route::prefix('guest')->group(function () {
    Route::get('/equipments', [LaboratoryEquipmentController::class, 'index']);
    Route::get('/equipments/{identifier}', [LaboratoryEquipmentController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/equipments/{identifier}/check-in', [LaboratoryEquipmentController::class, 'checkIn']);
        Route::post('/equipments/{identifier}/check-out', [LaboratoryEquipmentController::class, 'checkOut']);
        Route::post('/equipments/{identifier}/update-end-use', [LaboratoryEquipmentController::class, 'updateEndUse']);
        Route::post('/equipments/{identifier}/report-location', [LaboratoryEquipmentController::class, 'reportLocation']);
    });
});
```

Verification:
- Unauthenticated POSTs to guest equipment mutation routes must return `401`.
- Public personnel search must no longer expose `employee_id`, `email`, `phone`, or `address`.

Additional Hardening:
- Issue signed, short-lived log tokens for kiosk flows.
- Add audit alerts for guest-facing equipment mutations.
- Remove or heavily reduce the public personnel directory.

### VULN-002 Credentialed CORS Is Open To Any Origin
```yaml
Vulnerability: Wildcard CORS with credentials on stateful Sanctum API
Severity: HIGH
CWE ID: CWE-942
OWASP Category: A05:2021 – Security Misconfiguration
Location:
  - File: config/cors.php
    Lines: 18-32
  - File: config/sanctum.php
    Lines: 18-22, 36-80

Description: |
  The API allows all origins while also enabling credentialed requests and uses
  Sanctum's stateful frontend middleware. In this configuration, browsers can be
  permitted to send cookies from arbitrary origins, which broadens the blast radius
  of XSS on any allowed frontend and makes cross-origin data access much harder to reason about.

Immediate Fix: |
  Restrict allowed origins to explicit frontend hosts and keep credentials enabled only
  for those origins. If public APIs need wildcard access, split them onto a separate
  stateless path or subdomain with credentials disabled.
```

```php
// config/cors.php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => array_filter(explode(',', env('CORS_ALLOWED_ORIGINS', 'https://onecbc.philrice.gov.ph'))),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['Content-Type', 'X-Requested-With', 'X-CSRF-TOKEN', 'Accept', 'Authorization'],
    'exposed_headers' => [],
    'max_age' => 3600,
    'supports_credentials' => true,
];
```

Verification:
- Requests from approved SPA origins return `Access-Control-Allow-Origin` for that origin.
- Requests from unapproved origins do not receive credentialed CORS headers.

Additional Hardening:
- Separate public read-only APIs from first-party stateful APIs.
- Add CSP on all SPA entry points.

### VULN-003 Public Rental Endpoints Leak PII And Booking Details
```yaml
Vulnerability: Unauthenticated rental APIs expose requester names, contact numbers, and schedules
Severity: HIGH
CWE ID: CWE-200
OWASP Category: A01:2021 – Broken Access Control
Location:
  - File: routes/guest.php
    Lines: 121-137
  - File: app/Http/Controllers/RentalVehicleController.php
    Lines: 32-50, 210-243, 269-277
  - File: app/Http/Controllers/RentalVenueController.php
    Lines: 31-49, 173-207, 233-241
  - File: app/Models/RentalVehicle.php
    Lines: 35-55
  - File: app/Models/RentalVenue.php
    Lines: 34-50

Description: |
  `publicIndex()` returns full rental models, which include `contact_number`.
  `checkAvailability()` additionally returns `requested_by`, event names, and schedule
  information to unauthenticated callers. This creates an avoidable privacy leak and
  exposes operational booking data.
```

Immediate Fix:
```php
// RentalVehicleController::publicIndex()
$rentals = $this->repo()->all([
    'vehicle_type' => $request->query('vehicle_type'),
    'date_from' => $request->query('date_from'),
    'date_to' => $request->query('date_to'),
    'status' => $request->query('statuses'),
]);

return response()->json([
    'data' => collect($rentals)
        ->map(fn (RentalVehicle $rental) => Arr::only($rental->toArray(), [
            'id', 'vehicle_type', 'trip_type', 'date_from', 'date_to', 'time_from', 'time_to', 'status'
        ]))
        ->values(),
]);
```

Verification:
- Public list/show responses exclude `contact_number`, `requested_by`, and free-text `notes`.
- Availability endpoints return only boolean availability plus normalized time windows.

Additional Hardening:
- Add rate limiting per IP and optional CAPTCHA on public booking creation.
- Introduce API resources for all guest payloads to avoid raw model serialization.

### VULN-004 Public Participant Lookup Enables Enumeration And Auto-Registration
```yaml
Vulnerability: Email-based participant enumeration and registration creation on public endpoint
Severity: HIGH
CWE ID: CWE-203 / CWE-204
OWASP Category: A01:2021 – Broken Access Control
Location:
  - File: routes/guest.php
    Lines: 145-149
  - File: app/Http/Controllers/EventWorkflowController.php
    Lines: 28-107

Description: |
  The guest participant lookup endpoint accepts an email address, reveals whether a
  participant profile exists, returns the participant object, and creates a new
  registration automatically if none exists for the event. That enables email
  enumeration and unauthorized registration creation without proving mailbox control.
```

Immediate Fix:
```php
public function resolveParticipantByEmail(Request $request, string $event_id): JsonResponse
{
    $validated = $request->validate([
        'email' => ['required', 'email'],
    ]);

    // Return a generic response and defer registration creation to a signed email flow.
    return response()->json([
        'status' => 'success',
        'data' => [
            'verification_required' => true,
            'message' => 'If this email is eligible, a verification link will be sent.',
        ],
    ]);
}
```

Verification:
- Same response body is returned for known and unknown emails.
- No registration rows are created during lookup.

Additional Hardening:
- Use signed verification links or one-time email codes.
- Avoid returning full participant models from public endpoints.

### VULN-005 Cached PDFs Are Stored Under The Public Webroot
```yaml
Vulnerability: Authorization bypass through publicly served PDF cache
Severity: HIGH
CWE ID: CWE-552
OWASP Category: A01:2021 – Broken Access Control
Location:
  - File: app/Http/Controllers/PDFGeneratorController.php
    Lines: 27-28, 48-55, 97-115, 142-152

Description: |
  The controller correctly authorizes access before generating a request-form PDF,
  but the generated file is cached in `public/generated-pdfs/{templateSlug}/{id}.pdf`.
  Once cached, the file can be fetched directly from the web server by URL, bypassing
  the route's `authorize('view', $form)` check.
```

Immediate Fix:
```php
$cacheDir = storage_path("app/private/generated-pdfs/{$templateSlug}");
if (!File::exists($cacheDir)) {
    File::makeDirectory($cacheDir, 0775, true);
}
$cacheFile = $cacheDir . DIRECTORY_SEPARATOR . $id . '.pdf';
```

Verification:
- Direct requests to `/generated-pdfs/...` return `404`.
- Authorized controller route still streams/downloads PDFs correctly.

Additional Hardening:
- Add cache expiry cleanup.
- Avoid returning raw exception details in PDF generation errors.

### VULN-006 Public Internal Network Probe Route
```yaml
Vulnerability: Public internal reachability probe
Severity: MEDIUM
CWE ID: CWE-918
OWASP Category: A05:2021 – Security Misconfiguration
Location:
  - File: routes/guest.php
    Lines: 167-190

Description: |
  `/api/guest/test-local-network` lets unauthenticated users probe whether an internal
  host (`192.168.36.10`) is reachable from the server. The target is fixed, so this is
  not full SSRF, but it still leaks internal topology and gives attackers a low-cost probe.
```

Immediate Fix:
- Remove the route from production builds, or restrict it to authenticated admin users and non-production environments.

### VULN-007 Legacy Unsigned QR Payloads Still Accepted
```yaml
Vulnerability: HMAC-based QR validation bypassed by legacy raw UUID payloads
Severity: MEDIUM
CWE ID: CWE-345
OWASP Category: A07:2021 – Identification and Authentication Failures
Location:
  - File: app/Http/Controllers/FormScanController.php
    Lines: 191-226

Description: |
  The scanner verifies signed payloads, but if the payload is just a UUID it is still
  accepted as `version=legacy`. That makes the signed format optional and weakens the
  point of adding HMAC validation.
```

Immediate Fix:
```php
if (Str::isUuid($raw)) {
    return [
        'version' => 'invalid',
        'signature_valid' => false,
    ];
}
```

Verification:
- Unsigned legacy UUID QR payloads now return `invalid`.
- Signed payloads continue to work.

### VULN-008 Composer Advisories In Framework Dependency Graph
```yaml
Vulnerability: Known Composer advisories affecting framework dependency graph
Severity: MEDIUM
OWASP Category: A06:2021 – Vulnerable and Outdated Components
Location:
  - File: composer.json
    Lines: 12, 17

Description: |
  `composer audit --format=json` reported advisories for:
  - league/commonmark via laravel/framework (CVE-2026-33347)
  - phpseclib/phpseclib via google/apiclient / spatie/laravel-google-calendar (CVE-2026-32935)
```

Immediate Fix:
```bash
composer update laravel/framework google/apiclient phpseclib/phpseclib league/commonmark
composer audit --format=json
```

Verification:
- `composer audit --format=json` returns zero advisories.

---

## Recommended Optimizations
### Optimization: Filter And Sanitize Public Rental Listings In SQL
Priority: P0  
Effort: XS  
Current State: `publicIndex()` loads all rentals, filters statuses in PHP, and returns full models.  
Optimized Solution:
```php
// app/Repositories/RentalVehicleRepository.php
public function all(array $filters = [])
{
    $query = $this->model->newQuery();

    if (!empty($filters['vehicle_type'])) {
        $query->where('vehicle_type', $filters['vehicle_type']);
    }
    if (!empty($filters['statuses'])) {
        $query->whereIn('status', (array) $filters['statuses']);
    }
    if (!empty($filters['date_from'])) {
        $query->where('date_from', '>=', $filters['date_from']);
    }
    if (!empty($filters['date_to'])) {
        $query->where('date_to', '<=', $filters['date_to']);
    }

    return $query
        ->orderBy('date_from', 'asc')
        ->get(['id', 'vehicle_type', 'trip_type', 'date_from', 'date_to', 'time_from', 'time_to', 'status']);
}
```
Expected Gain: Lower memory use on public endpoints, smaller JSON payloads, and removal of accidental PII exposure.  
Risk Level: Low  
Test Strategy: Call guest vehicle/venue index endpoints with multiple `statuses` combinations and compare payload shape before/after.

### Optimization: Make Rental Conflict Detection Time-Aware
Priority: P1  
Effort: S  
Current State: `checkConflict()` and `getConflicts()` accept `timeFrom/timeTo` arguments but ignore them; only date overlap is checked.  
Optimized Solution:
```php
public function checkConflict(string $vehicleType, Carbon $dateFrom, Carbon $dateTo, string $timeFrom, string $timeTo, ?string $excludeId = null): bool
{
    $requestedStart = Carbon::parse($dateFrom->toDateString() . ' ' . $timeFrom);
    $requestedEnd = Carbon::parse($dateTo->toDateString() . ' ' . $timeTo);

    $query = $this->model->newQuery()
        ->where('vehicle_type', $vehicleType)
        ->whereIn('status', $this->blockingStatuses())
        ->where(function ($q) use ($requestedStart, $requestedEnd) {
            $q->whereRaw("TIMESTAMP(date_from, time_from) < ?", [$requestedEnd])
              ->whereRaw("TIMESTAMP(date_to, time_to) > ?", [$requestedStart]);
        });

    if ($excludeId) {
        $query->where('id', '!=', $excludeId);
    }

    return $query->exists();
}
```
Expected Gain: Fewer false conflicts, better booking utilization, and more accurate approval decisions.  
Risk Level: Low  
Test Strategy: Add bookings on the same day with non-overlapping times and confirm they no longer conflict.

### Optimization: Keep Generated Request PDFs Off The Webroot
Priority: P0  
Effort: S  
Current State: Request PDFs are cached in `public/generated-pdfs`.  
Optimized Solution: Store in `storage/app/private/generated-pdfs` and only stream via the controller.  
Expected Gain: Eliminates policy bypass and simplifies cache lifecycle management.  
Risk Level: Low  
Test Strategy: Generate a PDF, confirm controller download works, and direct static URL access fails.

### Optimization: Fix The Test Harness Before Adding More Features
Priority: P1  
Effort: S  
Current State: PHPUnit requires MySQL in [`tests/TestCase.php`](../tests/TestCase.php), while CI only provisions SQLite extensions; Vitest references missing `tests/setup.ts`.  
Optimized Solution:
```php
// tests/TestCase.php
if (env('DB_CONNECTION') !== 'mysql') {
    return;
}
```

```yaml
# .github/workflows/tests.yml
extensions: mbstring, dom, fileinfo, pdo_mysql
services:
  mysql:
    image: mysql:8
```

Expected Gain: Restores fast feedback loops and makes the 40% coverage gate meaningful again.  
Risk Level: Low  
Test Strategy: `vendor\bin\phpunit --testsuite Unit` and `npm test` both execute in CI and locally.

---

## Areas For Improvement
[ID-001] [CRITICAL] [Security] - Public Equipment Writes Trust Caller-Supplied `employee_id`  
📍 Location: `routes/guest.php:47`, `routes/guest.php:151-165`, `app/Services/Laboratory/LaboratoryLogService.php:141-166`  
🔍 Issue: Public mutation endpoints authorize by matching user-supplied `employee_id` to the active log owner.  
💡 Suggestion: Require authenticated staff sessions immediately; replace with signed log tokens for kiosk flows.  
📊 Impact: S effort, very high security benefit.

[ID-002] [HIGH] [Security] - Stateful Sanctum API Has Wildcard Credentialed CORS  
📍 Location: `config/cors.php:18-32`, `config/sanctum.php:18-22`  
🔍 Issue: `allowed_origins=['*']` with `supports_credentials=true` broadens cross-origin credential use.  
💡 Suggestion: Restrict origins from env and isolate public/stateless APIs.  
📊 Impact: XS effort, very high risk reduction.

[ID-003] [HIGH] [Security] - Public Rental APIs Leak Personal Data  
📍 Location: `app/Http/Controllers/RentalVehicleController.php:32-50`, `app/Http/Controllers/RentalVenueController.php:31-49`  
🔍 Issue: Guest list endpoints serialize full rental models, including `contact_number`.  
💡 Suggestion: Return DTO/API-resource payloads with only public fields.  
📊 Impact: XS effort, high privacy benefit.

[ID-004] [HIGH] [Security] - Availability Endpoints Reveal Booking Identities  
📍 Location: `app/Http/Controllers/RentalVehicleController.php:210-243`, `app/Http/Controllers/RentalVenueController.php:173-207`  
🔍 Issue: Unauthenticated callers can see `requested_by`, schedules, and event names for conflicts.  
💡 Suggestion: Return boolean availability and coarse time slots only.  
📊 Impact: XS effort, high privacy benefit.

[ID-005] [HIGH] [Security] - Public Participant Lookup Enumerates Users And Creates Registrations  
📍 Location: `app/Http/Controllers/EventWorkflowController.php:28-107`  
🔍 Issue: The endpoint reveals whether an email exists and can auto-create a registration record without mailbox proof.  
💡 Suggestion: Replace with generic email verification flow and signed callback.  
📊 Impact: M effort, high security benefit.

[ID-006] [HIGH] [Security] - Request PDFs Cached In Public Webroot  
📍 Location: `app/Http/Controllers/PDFGeneratorController.php:48-55`  
🔍 Issue: Cached PDFs become directly reachable via static URLs, bypassing policy checks.  
💡 Suggestion: Move cache to private storage and stream through controller only.  
📊 Impact: S effort, high confidentiality benefit.

[ID-007] [MEDIUM] [Security] - Public Internal Reachability Probe  
📍 Location: `routes/guest.php:167-190`  
🔍 Issue: Public route probes fixed internal host `192.168.36.10`.  
💡 Suggestion: Remove in production or guard behind admin auth and environment checks.  
📊 Impact: XS effort, medium benefit.

[ID-008] [MEDIUM] [Security] - Legacy Unsigned QR Payloads Still Work  
📍 Location: `app/Http/Controllers/FormScanController.php:191-226`  
🔍 Issue: Raw UUID payloads are accepted even when signed payload support exists.  
💡 Suggestion: Reject legacy mode after a migration window.  
📊 Impact: XS effort, medium benefit.

[ID-009] [MEDIUM] [Security] - Composer Audit Reports Known Advisories  
📍 Location: `composer.json:12`, `composer.json:17`  
🔍 Issue: `composer audit` reports CVEs in transitive dependencies pulled via `laravel/framework` and Google Calendar integration.  
💡 Suggestion: Update framework/dependency set and re-run audit in CI.  
📊 Impact: S effort, medium-to-high benefit.

[ID-010] [HIGH] [DevOps] - PHPUnit Path Is Misaligned With CI Provisioning  
📍 Location: `tests/TestCase.php:23-66`, `phpunit.xml:23-31`, `.github/workflows/tests.yml:18-35`  
🔍 Issue: Tests require MySQL at runtime, but CI only provisions SQLite extensions and no MySQL service.  
💡 Suggestion: Standardize on SQLite for fast tests or provision MySQL consistently in CI and local docs.  
📊 Impact: S effort, very high delivery benefit.

[ID-011] [MEDIUM] [Maintainability] - Vitest Config References Missing Setup File  
📍 Location: `vite.config.js:34-39`  
🔍 Issue: `setupFiles: 'tests/setup.ts'` points to a file that does not exist, so `npm test` fails immediately.  
💡 Suggestion: Add the setup file or remove the config entry until it exists.  
📊 Impact: XS effort, medium benefit.

[ID-012] [MEDIUM] [Code Quality] - Participant Lookup Query Uses Nonexistent Registration Column  
📍 Location: `app/Repositories/FormRepo.php:76-84`, `app/Models/Registration.php:28-36`  
🔍 Issue: `getParticipantsByEventId()` queries `Registration::where('event_id', ...)`, but the model/table only defines `event_subform_id`.  
💡 Suggestion: Join through `event_subforms` or query by `event_subform_id` correctly.  
📊 Impact: XS effort, medium functional benefit.

[ID-013] [MEDIUM] [Maintainability] - Rental Conflict Logic Ignores Time Parameters  
📍 Location: `app/Repositories/RentalVehicleRepository.php:138-187`, `app/Repositories/RentalVenueRepository.php:133-174`  
🔍 Issue: Conflict checks accept `timeFrom/timeTo` but only compare dates, creating false positives and blocking legitimate bookings.  
💡 Suggestion: Compare full start/end timestamps.  
📊 Impact: S effort, medium-to-high product benefit.

[ID-014] [LOW] [Observability] - No Health Endpoint Or Security Header Middleware  
📍 Location: `app/Http/Kernel.php:16-24`, `app/Http/Kernel.php:31-49`  
🔍 Issue: No tracked app-level health endpoint, CSP, HSTS, frame, or content-type header middleware was found.  
💡 Suggestion: Add `/healthz` plus a dedicated security headers middleware.  
📊 Impact: S effort, medium operational benefit.

[ID-015] [LOW] [DevOps] - Generated Test Artifacts Are Tracked In Git  
📍 Location: `playwright-report/index.html`, `test-results/.last-run.json`  
🔍 Issue: Generated artifacts are committed despite `.gitignore`, which bloats diffs and risks leaking screenshots or logs.  
💡 Suggestion: Remove tracked artifacts and keep them ignored.  
📊 Impact: XS effort, low-to-medium benefit.

---

## Metrics & Trends
- Test inventory: 65 tracked test files
  - Feature: 54
  - Unit: 5
  - e2e: 2
- CI quality gate: 40% PHP line coverage threshold in [`/.github/workflows/tests.yml`](../.github/workflows/tests.yml)
- Current executable test health:
  - PHPUnit: blocked by DB bootstrap mismatch
  - Vitest: blocked by missing setup file
- Dependency freshness:
  - npm audit: clean
  - composer audit: 2 advisories
- Observability:
  - Audit logging exists
  - No explicit metrics/tracing pipeline found
  - No health endpoint found
- Technical debt estimate:
  - 1 week to close the highest-risk security gaps
  - 1 week to repair test infrastructure and guest API DTO boundaries
  - 1 additional sprint for workflow hardening and privacy-safe public API redesign

---

## Action Plan
### Week 1 (Critical Path)
- [ ] Remove public write access from equipment guest routes or replace it with signed/OTP-backed authorization.
- [ ] Restrict CORS origins for Sanctum-backed credentialed requests.
- [ ] Move generated request PDFs out of `public/`.
- [ ] Sanitize all guest rental responses and stop returning requester identity in availability checks.
- [ ] Update Composer dependencies until `composer audit` is clean.

### Month 1 (High Impact)
- [ ] Replace public email participant lookup with a mailbox-verification flow.
- [ ] Fix rental conflict detection to use full timestamp overlap.
- [ ] Align PHPUnit config, test bootstrap, and CI services around one database strategy.
- [ ] Restore frontend unit testing by adding or removing the missing Vitest setup file.
- [ ] Remove internal network probe route from production.

### Quarter 1 (Strategic)
- [ ] Introduce API resources/DTOs for every guest endpoint.
- [ ] Add health checks, security headers, and a lightweight operational readiness dashboard.
- [ ] Reduce guest write surface and split public/stateless APIs from staff/stateful APIs.
- [ ] Add abuse monitoring for high-risk guest routes.
