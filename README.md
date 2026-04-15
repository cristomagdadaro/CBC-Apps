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

### Linux server / production-like setup

1. Install the runtime dependencies:
   - PHP 8.1+, Composer, Node.js, Python 3, and LibreOffice
   - `sudo apt install libreoffice libreoffice-impress` on Debian/Ubuntu-based servers
   - `soffice` should be available on `PATH` or referenced by absolute path

2. Configure the environment:
   - Copy `.env.example` to `.env`
   - Set your database, app URL, and queue/realtime settings
   - For certificate generation on Linux, use:
     - `PYTHON_PATH=python3`
     - `LIBREOFFICE_PATH=soffice` or `/usr/bin/soffice`
   - If the certificate generator uses its own virtual environment, install `app/python/Certificate-Generator/requirements.txt` into that same interpreter and point `PYTHON_PATH` to that venv's `bin/python3` so modules like `pandas` resolve correctly.

3. Install application dependencies and prepare the app:
   - `composer install`
   - `npm install`
   - `php artisan key:generate`
   - `php artisan migrate`
   - `php artisan db:seed`

4. Run the long-lived background services on the main Linux server:
   - Use Supervisor to keep queue workers running after boot
   - Use Supervisor to keep Reverb running after boot
   - Create dedicated files in `/etc/supervisor/conf.d/` such as `onecbc-workers.conf` and `onecbc-reverb.conf`

   Example worker program:

   ```ini
   [program:onecbc-worker]
   command=php /var/webapps-php81/onecbc/CBC-Apps/artisan queue:work --queue=certificates,notifications,default --sleep=3 --tries=3 --timeout=90
   autostart=true
   autorestart=true
   user=www-data
   numprocs=8
   redirect_stderr=true
   stdout_logfile=/var/webapps-php81/onecbc/CBC-Apps/storage/logs/worker.log
   stopwaitsecs=3600
   ```

   Example Reverb program:

   ```ini
   [program:onecbc-reverb]
   command=php /var/webapps-php81/onecbc/CBC-Apps/artisan reverb:start
   autostart=true
   autorestart=true
   user=www-data
   redirect_stderr=true
   stdout_logfile=/var/webapps-php81/onecbc/CBC-Apps/storage/logs/reverb.log
   ```

   If you want one command to restart both queue workers and Reverb together, add a Supervisor group that references the program names exactly:

   ```ini
   [group:onecbc]
   programs=onecbc-worker,onecbc-reverb
   ```

   With that group in place, you can restart everything at once with:

   ```bash
   sudo supervisorctl restart onecbc:*
   ```

   If you do not define a group, restart each program directly instead:

   ```bash
   sudo supervisorctl restart onecbc-worker:*
   sudo supervisorctl restart onecbc-reverb
   ```

   After saving the config, run:

   ```bash
   sudo supervisorctl reread
   sudo supervisorctl update
   sudo supervisorctl status
   ```

   When code changes need a worker refresh:

   ```bash
   sudo supervisorctl restart onecbc-worker:*
   sudo supervisorctl restart onecbc-reverb:*
   ```

5. Run the post-deploy Artisan maintenance commands after updates are integrated:

   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   php artisan cache:clear
   php artisan optimize:clear
   php artisan ziggy:generate
   php artisan storage:link
   php artisan queue:restart
   ```

   If your workers and Reverb are Supervisor-managed, confirm they are still running after the restart:

   ```bash
   sudo supervisorctl status
   ```

### Windows development setup

1. Install the runtime dependencies:
   - PHP 8.1+, Composer, Node.js, and LibreOffice
   - If certificate generation runs on Windows, point `LIBREOFFICE_PATH` to the LibreOffice binary, for example:
     - `LIBREOFFICE_PATH=C:/Program Files/LibreOffice/program/soffice.exe`

2. Configure the environment:
   - Copy `.env.example` to `.env`
   - Set your database, app URL, and local development values

3. Install dependencies and run the app:
   - `composer install`
   - `npm install`
   - `php artisan key:generate`
   - `php artisan migrate`
   - `php artisan db:seed`
   - `php artisan serve`
   - `npm run dev`

If you are developing in WSL on Windows, use the Linux setup above and keep `soffice` available in that Linux environment.

## Documentation Index

- RBAC and layered implementation: `docs/RBAC_ARCHITECTURE_IMPLEMENTATION.md`
- AI coding instructions: `.github/copilot-instructions.md`

## Development Notes

- Option lookups should be resolved through `App\Repositories\OptionRepo`.
- New multi-step use cases should be implemented as pipeline stages.
- New module authorization should be added in `config/rbac.php`, then enforced in routes and frontend guards.
