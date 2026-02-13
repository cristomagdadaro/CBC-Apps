# CBC-Apps RBAC and Layered Architecture Implementation

## Overview

This document describes the implemented industry-standard architecture and role-based access control (RBAC) model for CBC-Apps. The implementation standardizes business flow orchestration with Laravel Pipelines, Services, Observers, and Request Rules while introducing centralized authorization for backend and frontend modules.

## Architectural Standard Applied

### Layered Flow

The backend follows this sequence:

1. HTTP transport in controllers (`app/Http/Controllers`).
2. Input validation and request authorization in Form Requests (`app/Http/Requests`).
3. Business orchestration in Services/Repositories (`app/Services`, `app/Repositories`).
4. Multi-step workflow handling in Pipelines (`app/Pipelines`).
5. Persistence through Eloquent models (`app/Models`).
6. Post-persistence side effects in Observers (`app/Observers`).

### Why This Matters

This structure keeps each layer focused, lowers coupling, improves testability, and allows role checks to be enforced consistently in a single policy/gate system.

## RBAC Design

### Roles

Roles are defined as enum values in `app/Enums/Role.php`:

- `admin`
- `laboratory_manager`
- `ict_manager`
- `administrative_assistant`

### Permission Registry

Permission strings and role-permission mapping are centralized in `config/rbac.php`.

### Role Storage

RBAC persistence is implemented with:

- `roles` table
- `role_user` pivot table

Migration: `database/migrations/2026_02_13_120000_create_roles_and_role_user_tables.php`

### User Integration

`app/Models/User.php` now includes:

- `roles()` relation
- `hasRole()`
- `hasAnyRole()`

`app/Observers/UserObserver.php` keeps admin compatibility by attaching admin role automatically when `is_admin` is true.

## Authorization Enforcement

### Gate Layer

`app/Providers/AuthServiceProvider.php`:

- Registers model policies.
- Registers permission gates from `config/rbac.php`.

### Route Layer

API modules are enforced with `can:<permission>` middleware in route files:

- `routes/forms.php`
- `routes/fes.php`
- `routes/inventory.php`
- `routes/laboratory.php`
- `routes/rental.php`

### Request Layer

Critical approval request authorization is enforced in:

- `app/Http/Requests/UpdateRequestFormPivot.php`

### Pipeline Layer

Approval authorization is enforced in pipeline stage:

- `app/Pipelines/RequestApproval/AuthorizeApprovalAction.php`

This prevents bypass through direct repository invocation and protects state transitions at workflow level.

## Policy Coverage

Policies were added for module-level resources:

- `FormPolicy`
- `RequestFormPivotPolicy`
- `TransactionPolicy`
- `SuppEquipReportPolicy`
- `LaboratoryEquipmentLogPolicy`
- `RentalVehiclePolicy`
- `RentalVenuePolicy`

Shared policy helper trait:

- `app/Policies/Concerns/AuthorizesByPermission.php`

## Frontend RBAC

### Shared Auth Payload

Inertia shared auth props are served from:

- `app/Http/Middleware/HandleInertiaRequests.php`

Shared keys:

- `auth.user`
- `auth.roles`
- `auth.permissions`

### Navigation Guarding

`resources/js/Layouts/AppLayout.vue` filters service navigation based on role/permission metadata.

Admin-only user management navigation is exposed through `users.manage` permission.

### Reusable Authorization Composable

`resources/js/Modules/composables/useAuthorization.js` provides:

- `hasRole()`
- `hasAnyRole()`
- `hasPermission()`

## Role Matrix Implemented

### Admin

- Full access (`*`) to all modules.

### Laboratory Manager

- FES request approvals
- Laboratory equipment logger
- Inventory management
- Supply/equipment report management

### ICT Manager

- Event forms management
- Inventory management
- Supply/equipment report management

### Administrative Assistant

- Vehicle rental management
- Venue rental management
- Hostel rental permission reserved in config (`rental.hostel.manage`)

## Admin User Management Module

### Backend

Admin-only user CRUD API is implemented with:

- `app/Http/Controllers/UserController.php`
- `app/Repositories/UserRepo.php`
- `app/Http/Requests/CreateUserRequest.php`
- `app/Http/Requests/UpdateUserRequest.php`
- `app/Http/Requests/DeleteUserRequest.php`
- `app/Http/Requests/GetUserRequest.php`
- `routes/users.php`

The API is protected by `can:users.manage` middleware.

### Frontend

Admin-only user management pages are implemented with:

- `resources/js/Pages/System/Users/UsersIndex.vue`
- `resources/js/Pages/System/Users/CreateUser.vue`
- `resources/js/Pages/System/Users/EditUser.vue`

Web routes are mounted under `/apps/system/users` with `auth` and `can:users.manage` middleware.

## Users Primary Key UUID Migration

### Objective

The `users.id` column was moved from incremental integer to UUID to align with cross-module UUID usage.

### Migration Coverage

Migration file:

- `database/migrations/2026_02_14_090000_convert_users_primary_key_to_uuid.php`

The migration updates and remaps user references in:

- `transactions.user_id`
- `supp_equip_reports.user_id`
- `audit_logs.user_id`
- `registrations.checked_in_by`
- `event_scan_logs.scanned_by`
- `laboratory_equipment_logs.checked_in_by`
- `laboratory_equipment_logs.checked_out_by`
- `role_user.user_id`
- `sessions.user_id`
- `personal_access_tokens.tokenable_id` for user tokens

### Fresh Install Alignment

Core historical migrations were updated so fresh installations create UUID-compatible user references by default.

## Seeder and Bootstrap Updates

### Seeders

- `database/seeders/RolesSeeder.php` creates role records and assigns admin role to users with `is_admin = true`.
- `database/seeders/DatabaseSeeder.php` calls `RolesSeeder`.

### Observer Registration

`app/Providers/AppServiceProvider.php` registers `UserObserver`.

## Operational Notes

### Required Deployment Commands

1. `php artisan migrate`
2. `php artisan db:seed --class=RolesSeeder`
3. `php artisan optimize:clear`

### Post-Deployment Assignment

The operations team should assign non-admin users to one of the managed roles by attaching records in `role_user`.

## Extension Guidance

### Adding New Protected Module

1. Add permission string in `config/rbac.php`.
2. Assign permission to appropriate roles.
3. Protect route group with `can:<permission>`.
4. Add policy method if resource-based authorization is needed.
5. Use `useAuthorization()` for frontend visibility gating.

### Adding New Workflow Guard

1. Add dedicated pipeline stage under `app/Pipelines/<Flow>`.
2. Place authorization/constraint stage before persistence stage.
3. Keep persistence stage idempotent and side-effect free.

## Compliance Summary

The implementation now enforces centralized RBAC in backend and frontend, aligns workflows with pipeline-oriented orchestration, uses observer-driven side effects, and keeps validation/rules in standardized request-rule locations.
