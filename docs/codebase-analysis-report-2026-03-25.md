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

**Tracker ID Convention:** `ID-YYYY-MM-DD-XXX` where `XXX` is a zero-padded daily incremental number.

## Tracker Updates (2026-03-30)
- [ID-2026-03-30-001] Resolved. Guest equipment mutation routes now require `auth:sanctum`, activity filters use the authenticated user's linked `employee_id`, and the log service no longer authorizes equipment actions with caller-supplied IDs.
- [ID-2026-03-30-002] Resolved earlier and re-verified. `config/cors.php` now uses explicit origins instead of wildcard credentialed CORS.
- [ID-2026-03-30-003] Resolved. Guest rental list/show endpoints now return sanitized DTO-style payloads instead of raw rental models.
- [ID-2026-03-30-004] Resolved. Rental availability endpoints now return generic availability plus normalized conflict windows only.
- [ID-2026-03-30-005] Resolved. Guest participant lookup now returns a generic verification-required response and no longer creates or reuses registrations.
- [ID-2026-03-30-006] Resolved earlier and re-verified. Generated request PDFs are cached under `storage/app/private/generated-pdfs`.
- [ID-2026-03-30-007] Resolved. The public internal reachability probe is no longer present in the regenerated frontend route map or in the sensitive guest route throttling list.
- [ID-2026-03-30-008] Resolved earlier and re-verified. Legacy unsigned QR payloads are rejected.
- [ID-2026-03-30-009] Resolved on 2026-03-30. `composer update laravel/framework league/commonmark spatie/laravel-google-calendar google/apiclient phpseclib/phpseclib --with-all-dependencies` upgraded `league/commonmark` to `2.8.2`, `phpseclib/phpseclib` to `3.0.50`, `google/apiclient` to `2.19.1`, and `spatie/laravel-google-calendar` to `3.8.5`; `composer audit --format=json` is now clean.
- [ID-2026-03-30-010] Resolved earlier and re-verified. Targeted PHPUnit runs execute without requiring a live MySQL bootstrap when the configured test connection is not MySQL.
- [ID-2026-03-30-011] Resolved in repo. `tests/setup.ts` now exists; local Vitest verification on this workstation is still blocked by a mixed Windows-node/WSL Rollup optional dependency issue rather than the missing setup file.
- [ID-2026-03-30-012] Resolved. `FormRepo::getParticipantsByEventId()` now queries by `event_subform_id` and preserves legacy direct event-id compatibility.
- [ID-2026-03-30-013] Resolved and covered by new same-day non-overlapping rental tests.
- [ID-2026-03-30-014] Open.
- [ID-2026-03-30-015] Open.
- [ID-2026-03-30-016] Resolved. `resources/js/ziggy.js` had drifted from the current route definitions and still exposed removed guest-route metadata until regeneration.

## Tracker Updates (2026-03-31)
- [ID-2026-03-31-001] Reopened by product decision. The Equipment Logger guest mutations were switched back to the local-only employee-ID trust model, so this remains an accepted risk unless the module is exposed outside the trusted local deployment.
- [ID-2026-03-31-002] Resolved. Module Access Controls now grant authenticated administrator accounts a full backend and frontend bypass, so `deployment.access` middleware, shared Inertia props, app navigation, and welcome-page service cards stay aligned for admins regardless of module access or mode settings.
- [ID-2026-03-31-003] Resolved. Module Access Control grouping was not logically flawless because guest-facing Incident Reports and Experiment Monitoring were being governed by internal Inventory/Research module keys; they now use dedicated guest/shared module keys so the Options grouping matches the actual route surface.
- [ID-2026-03-31-004] Resolved. The guest Equipment Logger page no longer closes the location update modal prematurely on failed submissions, and its tour metadata now matches the intended local-trust guest workflow.
- [ID-2026-03-31-005] Resolved. Authenticated non-admin users still need their linked `employee_id` enforced on active-equipment lookups, while administrator navigation and approval cards must honor the centralized global admin/permission context. The active-equipment filter, app navigation, rental approvals, and research permission gates are now aligned to the shared auth globals.
- [ID-2026-03-31-006] Resolved. Personnel onboarding and equipment logger data quality are now aligned: fresh personnel records start with `updated_at = null`, Equipment Logger check-in requires a one-time profile completion for those fresh records, and outsider/OJT/thesis IDs now auto-increment from `new_barcodes` using the `CBC-YY-0000` format instead of manual guessing.
- [ID-2026-03-31-007] Resolved. Ordinary form updates such as suspend/reopen were unintentionally clearing attached requirements because the suspend action sent an empty requirements array and the backend always re-synced requirements on update. Form state updates now preserve existing attached pre-registration and other requirement records unless requirements were explicitly edited.
- [ID-2026-03-31-008] Resolved. Form builder edit changes were not reflected until a hard page reload because the editor kept stale local form state after successful saves. The editor now re-fetches the authoritative form payload after update so workflow, requirement, and rendered-form changes reflect immediately.
- [ID-2026-03-31-009] Resolved. Legacy attached subforms were inferring `region_address`, `province_address`, `city_address`, agreement consent, and updates consent fields incorrectly, causing dropdown-backed fields to degrade to plain inputs and consent text to render with truncated or wrong labels. Legacy schema inference now maps those keys to the intended field types and full labels.
- [ID-2026-03-31-010] Resolved. Guest equipment active-session endpoints were shadowed by the generic `{identifier}` route and the laboratory guest API prefix drifted from the frontend's `/api/guest/lab/*` calls, producing `404` responses for active session lookups. Route ordering and guest path prefixes are now aligned for both lab and ICT equipment APIs.
- [ID-2026-03-31-011] Resolved. Inventory dashboard filters were being serialized as nested `params[...]` query values, so period/date/month selections never reached the backend in the expected format. The dashboard now sends flat query parameters and the selected scope values are reflected server-side.
- [ID-2026-03-31-012] Resolved. OJT/outsider CBC ID auto-generation could reuse already-assigned IDs because it only trusted the `new_barcodes` counter row. The generator now also scans the `personnels` table for the highest current-year CBC suffix and issues the next available ID.
- [ID-2026-03-31-013] Resolved. `UseRequestCard` and `ListOfUseRequests` were missing declared emits/props for forwarded update events, which produced Vue fragment-root listener warnings at runtime. The component contracts now explicitly declare the custom events they emit.
- [ID-2026-03-31-014] Resolved. The Options API was still guarded by `can:event.forms.manage`, which blocked legitimate administrator updates on the internet deployment even though deployment-access admin bypass was already implemented. Options management now uses a dedicated middleware that accepts administrators and the intended management permissions.
- [ID-2026-03-31-015] Resolved. Sidebar module groups were hidden unless the parent container permission passed, even when the current user had access to one or more child pages. Group visibility now derives from visible child routes, and active groups open automatically so logged-in users can actually navigate the app.
- [ID-2026-03-31-016] Resolved. Equipment Logger check-in did not enforce a usable email address, so overdue sessions had no reliable notification target. Personnel lookup now exposes email presence, guest logger flows capture missing emails before check-in, and overdue equipment sessions now email the affected user.
- [ID-2026-03-31-017] Resolved. The Equipment Logger detail view returned a trimmed `active_log` payload without eager-loaded personnel data, so the Current Status card could show an empty user while the Active Sessions list still showed the correct name. The equipment detail payload now includes the active personnel snapshot, and the frontend status card falls back to the current equipment-user context when needed.
- [ID-2026-03-31-018] Resolved. The research project form exposed backend support for `project_leader_id` but never rendered a project leader field in create/edit, and research project navigation still used opaque IDs even when funding codes existed. The project form now supports leader assignment, research project routes resolve by `funding_code` then `code` then `id`, and the project/study/experiment headers now use the shared breadcrumb-capable header layout.
- [ID-2026-03-31-019] Partially resolved. Equipment Logger QR resolution depended only on non-deleted `transactions` rows, so some existing equipment barcodes returned not found after the source transaction was soft-deleted. Barcode resolution and equipment eligibility now include soft-deleted transaction history. A remaining data-quality risk still exists for equipment-category items that have no transaction history at all, because the current schema has no alternate barcode-to-item source for those records.
- [ID-2026-03-31-020] Resolved. The Equipment Logger page could render in some browsers without a usable `equipment_id` prop during client hydration, so the detail `show` API was never called and the page fell into a false "not found" state even for valid QR URLs. The page now falls back to parsing the identifier directly from `window.location.pathname` when the Inertia prop is missing or stale.
- [ID-2026-04-07-021] Resolved. Some browsers could still show false "Equipment not found" states on QR loads because the guest logger page depended fully on client-side Ziggy route generation before it ever issued the detail request. The page now falls back to direct guest API paths for list, active-session, and detail loads when route-helper resolution fails, preventing stale browser route state from masking valid equipment records as missing.
- [ID-2026-04-07-022] Resolved. The guest Equipment Logger flow required users to click the personnel lookup button before check-in, and the saved personnel cache dropped email/profile metadata, so repeat check-ins could still reopen the email-required modal even after an email had already been saved. The check-in action now triggers lookup when needed, reuses the saved employee context for new equipment sessions, and persists email/profile flags in the local personnel cache.
- [ID-2026-04-07-023] Resolved. `personnels.employee_id` is not unique in the current schema, so guest lookup and equipment check-in could resolve different duplicate personnel rows for the same ID and incorrectly prompt for email even when another matching record already had one. Personnel resolution now uses a shared preferred ordering that prioritizes rows with email and initialized profiles, and the Equipment Logger explicitly closes the email modal when a fresh lookup confirms email already exists.
- [ID-2026-04-07-024] Resolved. `EquipmentShow.vue` was issuing duplicate active-session requests on first load because `mounted()` called `loadActiveEquipments()` directly while `loadEquipment()` also refreshed the same endpoint in its `finally` block. Active-session loading is now centralized and deduplicated with an in-flight request guard so the page reuses the same request instead of firing overlapping AJAX calls.
- [ID-2026-04-07-025] Resolved. The repeated Equipment Logger email-required modal was also caused by a frontend contract gap: `resources/js/Modules/dto/DtoPersonnel.ts` and `resources/js/Modules/interface/IPersonnel.ts` were not carrying `has_email` and `profile_requires_update`, so normalized personnel payloads could silently drop the lookup flags coming from the backend and the page would fall back to the email-required state even when the database record already had an email. The DTO/interface contract is now aligned with the guest personnel lookup payload.
- [ID-2026-04-07-026] Resolved. `ActionHeaderLayout` interpreted `route-link` as a raw URL while several pages passed route names, which made shared page headers inconsistent and prone to broken links. The shared header now resolves route names safely before rendering links.
- [ID-2026-04-07-027] Resolved. Shared form inputs were still rendering invalid or empty `autocomplete` attributes, and the personnel lookup field used the non-standard token `employee_id`, which triggered browser warnings about incorrect autocomplete usage. `TextInput.vue` now omits blank `id`, `name`, and `autocomplete` attributes, `SelectSearchField.vue` uses a valid explicit autocomplete mode, and `PersonnelLookup.vue` now uses the standards-compliant `username` token.
- [ID-2026-04-07-028] Resolved. The main authenticated dashboard was not honoring Module Access Controls or per-user RBAC, so users could still see counts, quick actions, and activity feeds for modules that were unavailable on the current deployment or outside their assigned permissions. The dashboard now receives server-side access flags, zeroes inaccessible module metrics, filters module cards and quick actions in the UI, and adds a recent equipment logs panel that only appears when the laboratory dashboard is actually available to the current user.

## Tracker Updates (2026-04-10)
- [ID-2026-04-10-001] Resolved. The recent equipment logs card on the authenticated dashboard was hard-coded to `ict.equipments.show` even though the feed mixes both ICT and laboratory equipment records. Dashboard links now choose `ict.equipments.show` or `laboratory.equipments.show` per log based on the loaded equipment category.
- [ID-2026-04-10-002] Resolved. `phpunit.xml` inherited the application's default database connection and schema name, which made local test runs prone to sharing the primary app database. PHPUnit now pins a dedicated testing schema (`cbc_one_db_test`) so database-backed tests target an isolated database by default.
- [ID-2026-04-10-003] Resolved. Shared Inertia requests were repeatedly querying the same deployment-access option keys because `DeploymentAccessService::sharedPayload()` rebuilt the module configuration map for every module visibility/availability check, and separate option consumers inside the same request did not reuse already-fetched option values. Deployment access state is now memoized per request/service instance, welcome/service payloads reuse the same module state snapshot, and `OptionRepo` now caches resolved option values within the current request lifecycle.
- [ID-2026-04-10-004] Resolved. The guest booking and rentals views already expected `requested_by`, but the public vehicle and venue rental payloads had stripped it out entirely alongside the contact fields. Public rental list/detail payloads now expose the requester name while continuing to hide `contact_number` and other sensitive request details.

### Verification Snapshot
- `php artisan test tests/Feature/Laboratory/EquipmentControllersTest.php tests/Feature/Rental/RentalControllersTest.php tests/Feature/Inventory/GuestPersonnelLookupTest.php tests/Feature/Events/Registration/GuestLookupTest.php tests/Feature/Events/FormRepoParticipantsTest.php tests/Feature/Events/Workflow/TimelineIntegrationTest.php tests/Feature/Events/Workflow/ToggleSettingsTest.php`: 56 passed, 222 assertions, 0 failures.
- `composer audit --format=json`: clean after dependency updates.
- `php artisan ziggy:generate resources/js/ziggy.js`: regenerated the frontend route map; stale `api.test-local-network` entry is gone.
- `npm test -- --run resources/js/smoke.spec.js`: the missing `tests/setup.ts` failure is resolved, but this workstation still routes through Windows `node/npm` from WSL and is missing Rollup's optional native package (`@rollup/rollup-win32-x64-msvc`).

## Module Access Control Assessment (2026-04-07)
The current Module Access Control approach is operationally strong for CBC-Apps because the same web app is deployed on both the trusted local server (`192.168.36.10`) and the public internet server (`onecbc.philrice.gov.ph`) while intentionally exposing different services on each surface. The core policy is centralized in [`app/Services/DeploymentAccessService.php`](../app/Services/DeploymentAccessService.php), enforced by [`app/Http/Middleware/EnsureDeploymentAccess.php`](../app/Http/Middleware/EnsureDeploymentAccess.php), surfaced in the options UI through [`resources/js/Pages/System/Options/OptionsIndex.vue`](../resources/js/Pages/System/Options/OptionsIndex.vue), and mirrored into frontend visibility through shared Inertia props consumed by [`resources/js/Pages/Welcome.vue`](../resources/js/Pages/Welcome.vue) and [`resources/js/Layouts/AppLayout.vue`](../resources/js/Layouts/AppLayout.vue).

### Strengths
- Centralized policy model. Deployment access, mode, defaults, grouping, and administrator bypass are defined in one backend service instead of being scattered across pages and routes.
- Good fit for the real deployment topology. The `local` / `internet` / `both` channel model maps directly to how CBC-Apps is actually operated.
- Backend and frontend can stay aligned. The same state can govern route middleware, welcome-page visibility, sidebar visibility, and options management.
- Operational flexibility. Module `active`, `deactivated`, and `maintenance` modes are useful for phased rollouts and temporary read-only behavior without redeploying code.
- Explicit administrator bypass. This reduces accidental lockouts and allows system management even when a module is restricted to one deployment channel.

### Weaknesses
- Policy drift is the biggest ongoing risk. New routes, APIs, pages, buttons, websocket subscriptions, generated links, or queued side effects can bypass the intended module key if they are added outside the centralized flow.
- Host-based channel detection can be brittle. Reverse proxies, unexpected hostnames, tunnels, misconfigured `APP_URL` / `APP_LOCAL_URL`, or proxy-header mistakes can classify requests into the wrong channel.
- Some module boundaries are still broad. A single module key can end up governing guest and internal surfaces with different threat models, which increases the chance of frontend/backend mismatches.
- This is not a full security boundary by itself. `deployment.access` is an operational gate and visibility system; it must complement authentication, permissions, network controls, and data minimization rather than replace them.
- Shared configuration means shared blast radius. A mistaken option change immediately affects both deployments because the policy is data-driven and centralized.
- Maintenance mode depends on HTTP semantics. If a supposedly read-only `GET` endpoint performs side effects, the maintenance contract becomes misleading.

### What To Watch For
- Route coverage: every web route and API route related to a module must carry the same `deployment.access:<module>` policy.
- UI/API symmetry: if a page or action is hidden on one deployment, the related backend endpoint must also reject it, and vice versa.
- Module-key boundaries: split guest/shared and internal-only surfaces when their exposure model differs.
- Administrator bypass consistency: middleware, shared props, navigation, welcome-page services, and page-level action guards must all treat admins the same way.
- Channel detection correctness: validate behavior on the real local host, the real public host, localhost, and direct private-IP access.
- Background features: Reverb channels, mail links, queued jobs, notifications, exports, and generated URLs should not assume only one deployment surface.
- Read-only expectations: maintenance mode should block all writes, including indirect writes triggered by GET endpoints or background callbacks.

### Recommended Verification Matrix
- Local deployment + guest user
- Local deployment + authenticated non-admin user
- Local deployment + administrator
- Internet deployment + guest user
- Internet deployment + authenticated non-admin user
- Internet deployment + administrator
- Repeat the matrix for each module mode: `active`, `maintenance`, and `deactivated`

This is a modular Laravel monolith with a clear controller-repository-service split, but the public guest surface is unusually large for an app that also handles staff workflows, approvals, rentals, and document generation. The biggest risks are authorization-by-knowledge (`employee_id` as a credential), permissive cross-origin settings on a stateful Sanctum stack, public PII leakage in guest rental APIs, and cached PDFs being written into the webroot.

At initial scan there were two strong delivery risks: the PHPUnit path was effectively pinned to a live MySQL instance while CI only provisioned SQLite extensions, and the Vitest setup referenced a missing `tests/setup.ts`. The tracker section above reflects the current status after remediation work.


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
- `composer audit --format=json`: clean after the 2026-03-30 dependency update.
- Targeted PHPUnit verification now passes without requiring a live MySQL bootstrap when the configured test connection is not MySQL.
- The missing `tests/setup.ts` issue is fixed in-repo; local Vitest execution on this workstation is currently blocked by a mixed Windows-node/WSL Rollup optional dependency issue.

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

[ID-016] [LOW] [Maintainability] - Generated Ziggy Route Map Drifted From Current Route Definitions  
Location: `resources/js/ziggy.js`  
Issue: The generated Ziggy file kept removed guest-route metadata until it was manually regenerated after the route hardening pass.  
Suggestion: Regenerate Ziggy whenever route names, URIs, or guest-route exposure changes.  
Impact: XS effort, low-to-medium correctness benefit.

[ID-017] [MEDIUM] [Access Control] - Module Access Controls Did Not Honor Administrator Bypass End To End  
Location: `app/Services/DeploymentAccessService.php`, `resources/js/Layouts/AppLayout.vue`, `resources/js/Pages/Welcome.vue`  
Issue: Deployment-access evaluation and frontend visibility both hid or blocked modules for administrator accounts instead of reserving those restrictions for non-admin users.  
Suggestion: Treat authenticated administrators as a deliberate deployment-access bypass on both the middleware and shared-prop/UI layers.  
Impact: XS effort, medium operational benefit.

[ID-018] [LOW] [Maintainability] - Guest Module Grouping Drifted From The Actual Route Surface  
Location: `app/Services/DeploymentAccessService.php`, `routes/web/file-reports.php`, `routes/api/file-reports.php`, `routes/web/research.php`, `resources/js/Pages/Welcome.vue`  
Issue: Guest-facing Incident Reports and Experiment Monitoring features were controlled by internal module keys, so the Options page grouping did not accurately represent what each control governed.  
Suggestion: Give guest/shared routes their own module keys when they do not share the same exposure model as the internal module.  
Impact: S effort, medium configuration clarity benefit.

[ID-019] [LOW] [UX] - Guest Equipment Logger Location Modal Closed On Failed Submit  
Location: `resources/js/Pages/Laboratory/EquipmentShow.vue`  
Issue: The location update dialog closed immediately even when the API returned validation errors, which made guest corrections harder during equipment logging.  
Suggestion: Keep the dialog open until the request succeeds and preserve the entered values so the user can fix validation errors in place.  
Impact: XS effort, medium user-trust benefit.

---

## Metrics & Trends
- Test inventory: 65 tracked test files
  - Feature: 54
  - Unit: 5
  - e2e: 2
- CI quality gate: 40% PHP line coverage threshold in [`/.github/workflows/tests.yml`](../.github/workflows/tests.yml)
- Current executable test health:
  - PHPUnit: targeted verification slice passing locally (56 tests / 222 assertions)
  - Vitest: setup file restored; this workstation is still blocked by a Windows-node/WSL Rollup optional dependency mismatch
- Dependency freshness:
  - npm audit: clean
  - composer audit: clean
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
- [x] Remove public write access from equipment guest routes or replace it with signed/OTP-backed authorization.
- [x] Restrict CORS origins for Sanctum-backed credentialed requests.
- [x] Move generated request PDFs out of `public/`.
- [x] Sanitize all guest rental responses and stop returning requester identity in availability checks.
- [x] Update Composer dependencies until `composer audit` is clean.

### Month 1 (High Impact)
- [x] Replace public email participant lookup with a mailbox-verification flow.
- [x] Fix rental conflict detection to use full timestamp overlap.
- [x] Align PHPUnit config, test bootstrap, and CI services around one database strategy.
- [x] Restore frontend unit testing by adding or removing the missing Vitest setup file.
- [x] Remove internal network probe route from production.

### Quarter 1 (Strategic)
- [ ] Introduce API resources/DTOs for every guest endpoint.
- [ ] Add health checks, security headers, and a lightweight operational readiness dashboard.
- [ ] Reduce guest write surface and split public/stateless APIs from staff/stateful APIs.
- [ ] Add abuse monitoring for high-risk guest routes.
