# Copilot Instructions for AI Agents

Overview
This repository is a Laravel 10 monolith for business process management (adquisiciones, ventas, proyectos). The codebase is organized by domain under `app/` with clear separations:

- `app/Models/` — Eloquent models (example: `App\\Models\\Adquisicion`)
- `app/Http/Controllers/` — HTTP handlers and orchestration
- `app/Services/` — Reusable business logic and integrations
- `app/Exports/` — Excel/CSV exporters (uses `maatwebsite/excel`)
- `resources/views/` — Blade templates grouped by feature

Key patterns & structure
- Routing: `routes/web.php` is the entry; additional feature route files are loaded from `routes/modules/` (e.g. `routes/modules/proyectos.php`).
- Helpers: `app/helpers.php` is autoloaded via Composer `files` in `composer.json`.
- Constants/Enums: shared values live in `app/Constants/` and `app/Enums/`.
- Exports: put report classes in `app/Exports/` and call them from controllers or queued jobs.

Essential commands (PowerShell on Windows)
- Install PHP deps: `composer install` 
- Prepare environment: `copy .env.example .env; php artisan key:generate`
- DB setup (local): `php artisan migrate --seed`
- Run tests: `php artisan test` or `vendor/bin/phpunit`
- Assets: `npm install` then `npm run dev` (or `npm run production`)
- Serve (local dev): use Laragon or `php artisan serve` (note: Laragon recommended for matching local Windows setup)

Repository-specific workflow hints
- After adding helpers, exports, or new classes, run `composer dump-autoload` to refresh autoload.
- When adding front-end assets, update `resources/js/*` and `resources/css/*` and run `npm run dev`; `webpack.mix.js` compiles many entry points (see file for list).
- Exports: follow existing patterns such as `app/Exports/ReportAdquisicionesExport.php` and register routes in `routes/modules/reporteria.php` or `routes/web.php`.
- Routes: prefer modular route files under `routes/modules/` for large features — controllers should be kept in feature folders when possible.

Key integration points & packages to be aware of
- `maatwebsite/excel` — exports in `app/Exports/`
- `spatie/laravel-permission` — roles/permissions, check `config/permission.php`
- `yajra/laravel-datatables` — server-side DataTables usage in controllers and views
- `barryvdh/laravel-dompdf` — PDF generation

Repo conventions and examples
- To add a new export: create `app/Exports/MyExport.php`, reference it in `app/Http/Controllers/ReportController.php`, add a route in `routes/modules/reporteria.php` and a button in the Blade view under `resources/views/reportes/`.
- Models naming: singular, Eloquent style (`Adquisicion.php`, `Articulo.php`).
- Services: put business logic in `app/Services/` and call them from controllers to keep controllers thin.

Useful file references
- `composer.json` — PHP dependencies and autoloading
- `package.json` & `webpack.mix.js` — frontend build scripts and entry points
- `routes/modules/` — modular route files (e.g. `proyectos.php`, `reporteria.php`)
- `backup_db/` — SQL backups present in repo; use for restoring test data

Editing & PR guidance for AI agents
- Make minimal, focused changes. Keep public APIs and DB migrations backward-compatible where possible.
- Run `php artisan test` and `npm run dev` locally before opening PRs that change backend or frontend behavior.
- If you add migrations, also add a seeder or example data in `backup_db/` for reviewers.

If something is not discoverable in code
- Ask the repo owner for environment-specific details (e.g., third-party service credentials, S3 buckets). Many integration points are configured in `config/` and `.env` (which is not in repo).

References
- Entry point: `public/index.php`
- Main controller namespace: `App\\Http\\Controllers`
- Example export: `app/Exports/ReportAdquisicionesExport.php`

---
If any section is unclear or you want examples in Spanish, tell me which parts to expand.
