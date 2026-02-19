# Copilot Instructions for CBC-Apps

## System Architecture
- This repository uses Laravel 10 + Inertia + Vue 3.
- Backend requests should follow `Controller -> Service/Repository -> Pipeline -> Model`.
- Multi-step workflows should be implemented as Laravel Pipeline stages in `app/Pipelines/*`.
- Side effects (mail, cache invalidation, audit hooks) should be implemented in `app/Observers/*` and registered in `AppServiceProvider`.
- Validation must stay in `app/Http/Requests/*` and domain-specific constraints in `app/Rules/*`.

## Data Access Pattern
- Repositories extend `AbstractRepoService` for searchable lists and CRUD patterns.
- Prefer repository methods over direct model static calls from controllers.
- Option retrieval should use `App\Repositories\OptionRepo` (avoid static `Option::*` methods).

## RBAC Standard
- Roles are enum-based in `app/Enums/Role.php`.
- User-role relation: `users` <-> `roles` via `role_user` pivot.
- Permission map is centralized in `config/rbac.php`.
- Gate checks are defined in `AuthServiceProvider` from configured permissions.
- API route protection should use `can:<permission>` middleware.
- Frontend authorization uses Inertia shared props: `auth.roles` and `auth.permissions`.

## Module Permission Targets
- `event.forms.manage` for event forms module.
- `fes.request.approve` for FES request approvals.
- `laboratory.logger.manage` for lab logger module.
- `inventory.manage` for inventory transactions/items/suppliers/personnels.
- `equipment.report.manage` for supply/equipment reports.
- `users.manage` for system user administration.
- `rental.vehicle.manage`, `rental.venue.manage`, `rental.hostel.manage` for rentals.

## Frontend Conventions
- Use `AppLayout` service navigation filtered by permissions.
- Add shared authorization logic through `resources/js/Modules/composables/useAuthorization.js`.
- Keep guest pages public and guard only authenticated modules.

## Delivery Expectations
- Preserve existing APIs and route names unless migration requires explicit change.
- Add tests for authorization gates/middleware when introducing new RBAC checks.
- Keep changes minimal, explicit, and module-scoped.
