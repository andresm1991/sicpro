# Copilot Instructions for AI Agents

## Overview
This is a Laravel-based monolithic application for business process management, with a focus on modules for acquisitions, sales tracking, and project management. The codebase is organized by domain-driven folders under `app/` and `resources/views/`, with clear separation between controllers, models, services, and exports.

## Key Architectural Patterns
- **Domain Structure:**
  - `app/Models/`: Eloquent models for DB tables (e.g., `Adquisicion.php`, `Articulo.php`).
  - `app/Http/Controllers/`: Handles HTTP requests, business logic, and view rendering.
  - `app/Services/`: Encapsulates reusable business logic and integrations.
  - `app/Exports/`: Excel/CSV export logic using Laravel Excel.
  - `resources/views/`: Blade templates, organized by feature/module.
- **Routing:**
  - `routes/web.php` for web routes, `routes/api.php` for API endpoints.
  - Modular route files under `routes/api/` and `routes/web/` for large features.
- **Configuration:**
  - All environment-specific and service configs in `config/`.

## Developer Workflows
- **Build:**
  - Use `npm run dev` or `npm run prod` for asset compilation (see `webpack.mix.js`).
- **Testing:**
  - Run `php artisan test` or `vendor/bin/phpunit` for backend tests (see `tests/`).
- **Database:**
  - Migrations in `database/migrations/`, seeders in `database/seeders/`.
  - Use `php artisan migrate` and `php artisan db:seed`.
- **Docker:**
  - Local development can use `docker-compose up` (see `docker-compose.yml`).

## Project-Specific Conventions
- **Constants & Enums:**
  - Use `app/Constants/` and `app/Enums/` for shared values and enums.
- **Helpers:**
  - Common helpers in `app/helpers.php`.
- **Exports:**
  - All Excel/CSV exports in `app/Exports/`, using `Maatwebsite\Excel`.
- **Notifications:**
  - Notification logic in `app/Notifications/` (if present), config in `config/notifications.php`.
- **Blade Views:**
  - Use `@extends`, `@section`, and `@include` for layout composition.
  - Organize views by feature in `resources/views/feature/`.

## Integration Points
- **External Packages:**
  - Laravel Excel, Spatie Permission, DomPDF, Yajra DataTables, and others (see `composer.json`).
- **APIs:**
  - API endpoints defined in `routes/api.php` and consumed via JS in `resources/js/`.
- **Assets:**
  - Static assets in `public/`, source in `resources/`.

## Examples
- To add a new acquisition report export: create a class in `app/Exports/`, register route in `routes/web.php`, and add a controller method in `app/Http/Controllers/`.
- To add a new Blade view for sales tracking: create a folder in `resources/views/marketing/seguimiento_ventas/` and register the route/controller.

## References
- Main entry: `public/index.php`
- App config: `config/app.php`
- Main controller namespace: `App\Http\Controllers`

---
For more details, see the README.md or ask a maintainer for module-specific guidance.
