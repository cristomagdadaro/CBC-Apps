# CBC-Apps

CBC-Apps is a Laravel 10 + Inertia + Vue 3 platform for event forms, FES approvals, laboratory equipment logging, inventory management, and rental workflows.

## Architecture Summary

The backend applies a layered standard:

- Controllers in `app/Http/Controllers` handle HTTP transport.
- Validation and request-level authorization are implemented in `app/Http/Requests`.
- Business orchestration is implemented in Services and Repositories.
- Multi-step flows are implemented with Laravel Pipelines in `app/Pipelines`.
- Side effects are implemented with model Observers in `app/Observers`.

## RBAC Summary

RBAC is implemented with role and permission mapping:

- Roles: `admin`, `laboratory_manager`, `ict_manager`, `administrative_assistant`
- Permission map: `config/rbac.php`
- User-role pivot: `role_user`
- Inertia auth payload: `auth.user`, `auth.roles`, `auth.permissions`

API module routes are protected with permission middleware using `can:<permission>`.

## Setup

1. Install dependencies:
   - `composer install`
   - `npm install`

2. Configure environment:
   - Copy `.env.example` to `.env`
   - Set database and app settings

3. Generate app key:
   - `php artisan key:generate`

4. Run schema and seeders:
   - `php artisan migrate`
   - `php artisan db:seed`

5. Start development servers:
   - `php artisan serve`
   - `npm run dev`

## Documentation Index

- RBAC and layered implementation: `docs/RBAC_ARCHITECTURE_IMPLEMENTATION.md`
- AI coding instructions: `.github/copilot-instructions.md`

## Development Notes

- Option lookups should be resolved through `App\Repositories\OptionRepo`.
- New multi-step use cases should be implemented as pipeline stages.
- New module authorization should be added in `config/rbac.php`, then enforced in routes and frontend guards.
