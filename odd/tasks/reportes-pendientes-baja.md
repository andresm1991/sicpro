# Reportes — pendientes BAJA

Feature: cierre de los hallazgos BAJA que quedaron abiertos en la auditoría de los 8 tipos de reporte
(SICPRO + JP Limpieza). Fuente de la lista: nota de continuidad `sicpro/reportes-baja-pendientes`.

## Specs

S1 (prioridad 1) — `getChildrenCatalogo` no debe romper si falta el nodo padre.
Texto fuente (verbatim): "`CatalogoDato::getChildrenCatalogo` sin null-check — el mas valioso.
`app/Models/CatalogoDato.php:38-39`: `$catalogo = CatalogoDato::where('slug', $value)->first();` y enseguida
`->where('padre_id', $catalogo->id)`. Si el nodo padre no existe, **`ErrorException: Attempt to read property \"id\" on null` → 500**
en cualquier pantalla que lo llame. Es un helper **compartido** (lo usa `getInfoEmpresa()` y varios controladores)."
Contrato nuevo: padre ausente → colección vacía, sin excepción.
No-regresión: padre presente → exactamente los mismos hijos que hoy, respetando `$slugsADescartar`.

S2 (prioridad 2) — fechas sin validar.
Texto fuente (verbatim): "**2. Fechas sin validar.** `explode(' - ', $request->input('fechas'))` en todos los reportes
(reventaria con `Carbon::createFromFormat` si llega basura) y `guardarRevisionCaja` valida `fechas.*` sobre un string."
Contrato nuevo: un `fechas` basura produce un error claro (no un 500 ni un `ErrorException` de PHP);
un `fechas` válido `m/d/Y - m/d/Y` filtra exactamente como hoy.

S3 (prioridad 3) — código muerto y filtro que no llega.
Texto fuente (verbatim): "**3. Codigo muerto y filtro que no llega.** `ReposicionTiempo::dataReporteReposiciones`
(`app/Models/ReposicionTiempo.php:137`) no lo usa nadie (global y detallado usan `getTotalReposiciones`)
y el filtro `estado` no llega a las filas del detalle."

S4 (prioridad 4) — `CatalogoDato::find($tipo)->slug` sin null-check.
Texto fuente (verbatim): "**4. `CatalogoDato::find($tipo)->slug` sin null-check.** `GenerarPdfController.php:515,520,578`
y `ReporteriaController.php:175`."

S5 (prioridad 5) — duplicación preview/PDF de la lógica de JP.
Texto fuente (verbatim): "**5. Duplicacion preview/PDF de la logica de JP** (gran total y `guardarRevisionCaja`):
el preview y el PDF calculan por caminos distintos."

## Tasks

| ID | Specs | Ruta | Estado |
|----|-------|------|--------|
| T1 | S1 | inline | pendiente |
| T2 | S2 | inline | pendiente |
| T3 | S3 | inline | pendiente |
| T4 | S4 | inline | pendiente |
| T5 | S5 | inline | pendiente |

## Log

L1 (pedido del usuario, verbatim): "segui con los pendientes de reportes"

L2 (origen de los pendientes, verbatim de la nota `sicpro/reportes-baja-pendientes`):
"**PENDIENTES (BAJA) EN ORDEN DE PRIORIDAD**: 1. `CatalogoDato::getChildrenCatalogo` sin null-check — el mas valioso ...
2. Fechas sin validar ... 3. Codigo muerto y filtro que no llega ... 4. `CatalogoDato::find($tipo)->slug` sin null-check ...
5. Duplicacion preview/PDF de la logica de JP"

L3 (estado del repo al arrancar): rama `sicpro-laravel` en sync con `origin/sicpro-laravel`, working tree limpio,
HEAD `b7a24d0e`. Runner: `php vendor/phpunit/phpunit/phpunit` (PHP 8.5.1 en Git Bash).
Suite base: 4 fallos preexistentes ajenos (`ModuleVisibilityTest.php:163`, `RoleAccessTest.php:32,71,84`).
