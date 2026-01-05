# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Environment and setup
- PHP dependencies: `composer install`
- Environment file (Windows / PowerShell): `copy .env.example .env; php artisan key:generate`
- Database (local): `php artisan migrate --seed`
- Frontend dependencies: `npm install`
- Frontend build (dev): `npm run dev`
- Frontend build (prod): `npm run production`
- Local serve: use Laragon (recommended on Windows) or `php artisan serve`

## Core commands

### Backend / PHP
- Install/update Composer dependencies: `composer install` / `composer update`
- Regenerate autoloads after adding helpers/classes: `composer dump-autoload`
- Run all tests (Laravel 10 + PHPUnit):
  - `php artisan test`
  - or `vendor/bin/phpunit`
- Run a single test:
  - By class/method name: `php artisan test --filter=ClassName` or `php artisan test --filter=ClassName::method_name`
  - By file path: `vendor/bin/phpunit tests/Feature/SomeFeatureTest.php`
- Run only a testsuite (as configured in `phpunit.xml`):
  - Unit tests: `vendor/bin/phpunit --testsuite=Unit`
  - Feature tests: `vendor/bin/phpunit --testsuite=Feature`

**Note on linting/static analysis**
No dedicated PHP linter or static analysis Composer scripts are defined in this repo. Use the test suite plus your editor/IDE tooling to catch issues, or coordinate before introducing new tooling.

### Frontend / assets
`package.json` defines Laravel Mix scripts:
- Development build: `npm run dev` (alias for `npm run development` / `mix`)
- Watch for changes: `npm run watch`
- Hot reloading: `npm run hot`
- Production build: `npm run prod` (alias for `npm run production` / `mix --production`)

## High-level architecture

This is a Laravel 10 monolith for business process management (adquisiciones, ventas, proyectos). The application is organized by domain with a conventional Laravel layering.

### Domain model (`app/Models`)
- Core business entities (e.g. `Adquisicion`, `Proyecto`, `Solicitud`, `PresupuestoProyecto`, `ResumenPagoSemanal`) live directly under `app/Models`.
- Sub-namespaces group specialized domains:
  - `App\Models\JPLimpieza\*` — JP limpieza domain (adquisiciones, caja, mano de obra, presupuesto, etc.).
  - `App\Models\Marketing\*` — marketing and sales flow (clientes de venta, contratos, documentación/escrituración, procesos de venta, etc.).
- Supporting models such as `MovimientoCaja`, `RevisionCaja`, `Pago*` classes, and `UsuarioTarea` capture financial flows, scheduling, and task assignments.

These models form the backbone of the domain; controllers, services, exports, and views are layered on top of them.

### HTTP layer (`app/Http`)
- Controllers in `app/Http/Controllers` are organized by business area and mostly map 1:1 to routes/modules, for example:
  - `AdquisicionController`, `SolicitudController`, `ContratistaController`, `ManoObraController`, `InventarioController`, `CajaController`, `ProyectoController`, `PresupuestoController`, `Reporte-riaController`, `Marketing`-related controllers, etc.
  - `GenerarPdfController` and `ImageProxyController` handle PDF generation and image proxying.
  - `PushNotificationController` / `PushNotificationMsgController` handle web push workflows.
- Request validation and input shaping is done via `app/Http/Requests/*` (e.g. `AdquisicionStoreRequest`, `ProyectoStoreRequest`, `UserStoreRequest`, various `*UpdateRequest` classes). These encapsulate per-endpoint validation and should be updated alongside controller changes.
- Middleware in `app/Http/Middleware` contains standard Laravel middleware plus custom pieces like `EncryptDecryptParameters` and `DecryptParameter`, which are important when changing URL/ID handling.

### Services and business logic (`app/Services`)
- `CajaService`, `LogService`, `PushNotificationService` encapsulate reusable business logic and cross-cutting concerns (e.g. cashbox operations, logging, push notifications).
- When adding non-trivial logic, prefer putting it into a service and calling it from controllers to keep controllers thin and easier to test.

### Exports and reporting (`app/Exports`)
- Reporting and data exports live in `app/Exports`, using `maatwebsite/excel`:
  - Examples include `ReportAdquisicionesExport`, `ReportGasolinaCamionetaExport`, `ReportSolicitudesExport`, `ReporteAdquisicionesOperativoJPExport`.
- These classes are typically invoked from controllers in response to user actions (e.g. report screens) and wired via dedicated routes.
- To add a new export, follow the pattern of the existing classes: create an export in `app/Exports`, call it from an appropriate controller method, and expose it via a route and Blade button.

### Shared utilities and configuration
- `app/helpers.php` contains global helper functions and is autoloaded via the `files` section in `composer.json`. After modifying this file, run `composer dump-autoload`.
- Shared constants and enums live under:
  - `app/Constants` (e.g. `MessagesConstant`)
  - `app/Enums` (e.g. `PushNotificationsEnum`)

### Routing and modularization (`routes`)
- `routes/web.php` is the main web entrypoint and pulls in additional modular route files.
- Feature-specific route groups live under `routes/modules/`:
  - Examples: `administrativo.php`, `agenda.php`, `caja.php`, `cliente.php`, `export_pdf.php`, `gerencia.php`, `jp_limpieza.php`, `marketing.php`, `proforma.php`, `proyectos.php`, `reporteria.php`, `sistema.php`, `solicitudes.php`.
- `routes/api.php` defines API routes; `routes/channels.php` and `routes/console.php` handle broadcasting and console routes.

When introducing a new feature, prefer creating a dedicated `routes/modules/<feature>.php` file and grouping its controllers and views by feature for consistency with the existing structure.

### Persistence, testing, and data
- Database migrations and seeders follow standard Laravel conventions (see `database/migrations` and `database/seeders`). Use `php artisan migrate --seed` for a fresh local setup.
- `phpunit.xml` defines two test suites:
  - `Unit` — tests in `tests/Unit`.
  - `Feature` — tests in `tests/Feature`.
  It also configures coverage to include the `app` directory and sets testing environment variables (e.g. cache, session, mail, queue drivers).
- `backup_db/` contains SQL dumps (e.g. `sicpro-20241004.sql`) used as snapshots of real data for local/test environments. Coordinate before updating or adding large dumps.

### Frontend build
- Frontend assets are built with Laravel Mix (`webpack.mix.js`) and the scripts defined in `package.json`.
- JS/CSS sources live under `resources/js` and `resources/css` (plus feature-specific subdirectories). After changing assets, run `npm run dev` (or `npm run prod` for optimized builds).

## Key packages and integrations

Important Composer dependencies and where they are typically used:
- `spatie/laravel-permission` — roles and permissions; see `config/permission.php` and usage in models/controllers for authorization checks.
- `yajra/laravel-datatables-oracle` — server-side DataTables integration for large tables, wired from controllers to Blade views.
- `maatwebsite/excel` — Excel/CSV exports via classes in `app/Exports`.
- `barryvdh/laravel-dompdf` — PDF generation, often triggered from `GenerarPdfController` and related flows.
- `league/flysystem-aws-s3-v3` — S3-compatible storage used through Laravel's `Storage` facade (check `config/filesystems.php` and usage in controllers/services).
- `minishlink/web-push` — web push notifications, connected with `PushNotification*` models and `PushNotificationService`.

## Repository-specific workflow notes
- Keep domain logic close to its models and services. The existing structure favors domain-centric organization (e.g., JP limpieza, marketing, caja, adquisiciones).
- When adding new flows that cross multiple domains (e.g., a new report that touches adquisiciones + proyectos), look at how existing reports are modeled under `app/Exports`, `routes/modules/reporteria.php`, and their associated controllers and views.
- Consult `CHANGELOG.md` for business-level changes (e.g., the introduction of `subProyecto` fields across adquisiciones-related tables) when modifying domain-sensitive areas.
