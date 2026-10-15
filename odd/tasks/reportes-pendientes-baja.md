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
| T1 | S1 | inline | cerrada — commit `022a96b2` |
| T2 | S2 | inline | cerrada — commit pendiente |
| T3 | S3 | inline | cerrada — commit pendiente |
| T4 | S4 | inline | cerrada — commit pendiente |
| T5 | S5 | inline | cerrada — commit pendiente |

## Log

L1 (pedido del usuario, verbatim): "segui con los pendientes de reportes"

L2 (origen de los pendientes, verbatim de la nota `sicpro/reportes-baja-pendientes`):
"**PENDIENTES (BAJA) EN ORDEN DE PRIORIDAD**: 1. `CatalogoDato::getChildrenCatalogo` sin null-check — el mas valioso ...
2. Fechas sin validar ... 3. Codigo muerto y filtro que no llega ... 4. `CatalogoDato::find($tipo)->slug` sin null-check ...
5. Duplicacion preview/PDF de la logica de JP"

L4 (T1, S1 cerrada): commit `022a96b2` — `getChildrenCatalogo` devuelve coleccion vacia si falta el padre.
Tests nuevos `tests/Feature/CatalogoDatoChildrenTest.php` (RED observado: `Attempt to read property "id" on null` en `CatalogoDato.php:39`;
GREEN 3/3). Suite: 99 tests, 229 assertions, los mismos 4 fallos preexistentes.
Residual anotado (no introducido por este cambio): `pdf/proforma.blade.php:59,60,271,273,275` indexa `$empresa[0..4]`
sobre `getInfoEmpresa()`; con la coleccion vacia el fallo pasa del helper al blade. Antes tambien era un 500
(la coleccion vacia ya era alcanzable cuando `informacion.general` existia sin hijos), asi que no es regresion.

L5 (T2, S2 cerrada):
- Nuevos: `app/Support/RangoFechas.php` (parser unico `m/d/Y - m/d/Y`) y `app/Exceptions/RangoFechasInvalidoException.php`;
  `app/Exceptions/Handler.php` traduce la excepcion a 302+flash (formularios) o JSON 422 (AJAX).
- Los 21 `explode(' - ')` de reportes pasaron a `RangoFechas` (solo quedan los 3 de las dos funciones muertas, que borra T3).
- RED observado: PDF de caja con `basura - basura` daba **500** con `Carbon\Exceptions\InvalidFormatException` en
  `MovimientoCaja.php:131`; `guardarRevisionCaja` con `02/30/2025 - 03/30/2025` guardaba `success: true` (2025-03-02 por rollover).
- `checkdate()` ademas de `Carbon::hasFormat()`: hasFormat acepta 02/30/2025 y Carbon la corre en silencio.
- Tests: `tests/Unit/RangoFechasTest.php`, `tests/Feature/GuardarRevisionCajaTest.php`, `tests/Feature/ReporteFechasInvalidasTest.php` (26 tests).
- Suite: 125 tests, 292 assertions, los mismos 4 fallos preexistentes.
- Observacion para el usuario (no tocada): hay una **colision preexistente** del nombre de ruta `reporte.caja`
  (routes/modules/reporteria.php -> GET /reportes/flujo-de-efectivo y routes/modules/export_pdf.php -> POST
  /generar-pdf/reporte-caja/{tipo}). `route('reporte.caja', ...)` resuelve a la GET, asi que un POST por nombre da 405.

  **CORRECCION (15/10/2026)**: esto era **falso**, fue un error de diagnostico propio. Los nombres son distintos
  (`reporte.caja` para la GET de la pantalla y `pdf.reporte.caja` para el POST del export), cada uso apunta al
  correcto y **no hay colision**. El 405 salio de usar `route('reporte.caja', 'pdf')` en un test, que genera la URL
  de la GET con un query param. Ver el Log de `odd/tasks/reportes-followups.md` (L4).

L6 (T3, S3 cerrada):
- `ReposicionTiempo::getTotalReposiciones` ahora aplica el `estado` tambien al eager load de `reposiciones`
  (antes solo al `whereHas` del agrupado), asi que las filas de `reposiciones_detallado` respetan el filtro.
- Borradas las dos funciones sin llamadores: `dataReporteReposiciones` (la que nombra S3) y
  `dataReporteSolicitudesYReposiciones` (misma regla, misma clase, 0 llamadores en app/resources/routes/tests).
  El archivo pasa de 291 a 141 lineas. Con eso desaparecen los 3 `explode(' - ')` que quedaban.
- Alcance del filtro: el JS deshabilita el select de estado para `reposiciones_global`, asi que el unico caso alcanzable
  es el detallado; el unico campo que podria cambiar de valor (`tiempo_recuperado_formateado`) solo se dibuja en la
  rama global, donde el filtro esta deshabilitado.
- RED observado: con `estado=<pendiente>` la tabla devolvia las dos reposiciones ([aprobada, pendiente]).
- Tests: `tests/Feature/ReporteReposicionesEstadoTest.php` (filtro + no-regresion sin filtro).
- Suite: 127 tests, 299 assertions, los mismos 4 fallos preexistentes.

L7 (T4, S4 cerrada):
- `reportAdquisiciones`: guarda si el `tipo` no existe (antes 500 en la linea 511) y el `$tipo = CatalogoDato::find($tipo)`
  duplicado pasa a reusar el modelo ya resuelto (sitio 520 de S4).
- `reportSolicitudes`: resuelve y valida el tipo de solicitud **antes** de consultar (antes 500 en la linea 580).
- `ReporteriaController::visualizarReporteAdquisiciones` (AJAX): mensaje claro en lugar del error crudo de PHP.
- RED observado: 500 `Attempt to read property "slug" on null` (PDF adquisiciones) y `"descripcion" on null`
  (PDF solicitudes); y `Error al generar el reporte: Attempt to read property "slug" on null` en el preview.
- Tests: `tests/Feature/ReporteTipoInvalidoTest.php` (3 RED + 1 no-regresion con tipo valido).
- Suite: 131 tests, 310 assertions, los mismos 4 fallos preexistentes.
- NO tocado (se reporta al usuario, fuera de los 4 sitios de S4):
  `ReporteriaController::filtroSubProyectosAdquisiciones` lee `$request->input('tipo')` pero la ruta lo manda en el path
  (`{tipo}`) y el JS no lo agrega a la query, asi que esa rama esta muerta en el flujo real; y `$proyecto->adquisiciones`
  sin null-check si el proyecto no existe (contrato distinto al de CatalogoDato::find).

L8 (T5, S5 cerrada):
- Gran total: `JPLimpieza\Adquisicion::granTotalReportePorArticulo()` y `JPLimpieza\Proyecto::totalesBalance()`
  (funciones puras) pasan a ser el unico camino: las usan el preview (`JPLimpieza\ReporteController`)
  y el PDF (`GenerarPdfController::reporteAdquisicionesGlobal` y `reporteAdquisicionesFinanciero`).
  El operativo ya compartia `granTotalReporte` desde el fix A.
- `guardarRevisionCaja` duplicado identico en los dos controladores -> trait
  `App\Http\Controllers\Concerns\GuardaRevisionCaja` con `modeloRevisionCaja()` abstracto (la unica
  diferencia era el modelo, uno por base).
- RED observado: los 4 tests unitarios de las funciones nuevas fallaban con
  `BadMethodCallException: Call to undefined method`.
- Tests: `tests/Unit/TotalesReporteJPTest.php` (puras, sin base), `tests/Feature/GuardarRevisionCajaTest.php`
  (camino feliz del trait, conexion de testing), `tests/Feature/GuardarRevisionCajaJPTest.php`
  (solo rechazo: el modelo JP escribe en la base real) y `tests/Feature/ReporteJPPreviewTest.php`
  (red de seguridad del cableado del preview de JP, escrito despues del refactor: es caracterizacion, no RED-GREEN).
- Suite: 139 tests, 330 assertions, los mismos 4 fallos preexistentes.

L9 (alcance dejado fuera, para decision del usuario):
- `Gerencia\ReporteGerencialController:25` y `GenerarPdfController::exportarReporteBalancePrime` (~921)
  siguen calculando sus propios totales de balance (no son JP; S5 hablaba solo de JP).
- Duplicacion preview/PDF equivalente en los reportes de SICPRO (adquisiciones/caja): fuera de S5.

L3 (estado del repo al arrancar): rama `sicpro-laravel` en sync con `origin/sicpro-laravel`, working tree limpio,
HEAD `b7a24d0e`. Runner: `php vendor/phpunit/phpunit/phpunit` (PHP 8.5.1 en Git Bash).
Suite base: 4 fallos preexistentes ajenos (`ModuleVisibilityTest.php:163`, `RoleAccessTest.php:32,71,84`).
