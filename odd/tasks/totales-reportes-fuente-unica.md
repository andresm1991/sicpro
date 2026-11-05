# Totales de reportes: una sola fuente de verdad

Unificar los totales de dinero de los reportes de adquisiciones de SICPRO para que el
PDF, el Excel y el preview calculen con una sola funcion pura, igual que ya hace JP
Limpieza con `granTotalReporte()` / `totalesBalance()`.

Estado: cerrada.

## Specs

- **S1** — "Fuente única real: PHP calcula y el preview lo muestra": los totales de un
  reporte salen de una funcion pura, y la usan todos los caminos de salida (PDF, Excel,
  preview). Ninguna vista ni ningun JS vuelve a sumar lo que ya sumo esa funcion.
- **S2** — El preview recibe los totales del servidor: el JSON de
  `ReporteriaController::visualizarReporteAdquisiciones` incluye una clave `totales`, y
  el JS la renderiza en vez de acumular sus propios contadores.
- **S3** — Comportamiento numerico identico al actual. Los importes llegan ya formateados
  como `'$ 1,234.0000'`; una clave ausente cuenta como `0`; `total_cantidades` no se
  formatea ni se castea distinto que hoy. El refactor no puede cambiar un solo numero.
- **S4** — Tests que fijen los numeros del pie, que hoy no existen: unitario de la funcion
  pura (importes formateados, claves ausentes, coleccion vacia) y de integracion del pie
  del PDF y de la clave `totales` del preview.

Fuera de alcance de esta feature (se registra, no se toca): el reporte **global** de
adquisiciones tiene su propia forma anidada por etapa y hoy suma en tres lugares
(`resources/js/reportes_scripts.js:1425+`, `resources/views/pdf/reporte_adquisiciones_global.blade.php:164+`
y `Adquisicion::totalesReporteGlobal`). Es la misma enfermedad con otra estructura y sus
propios tests numericos: merece su propio tramo.

## Tasks

Sin commit: la convencion del proyecto es que los commits los decide el usuario.

- [x] **T1** (S1, S3) Funcion pura `Adquisicion::totalesReporteAdquisiciones()` con test
  unitario RED/GREEN. RED: `BadMethodCallException` en los 4 tests; GREEN: 4 tests, 10
  aserciones.
- [x] **T2** (S1) `GenerarPdfController::reportAdquisiciones` toma los cuatro totales de la
  funcion en vez de recalcularlos. Los nombres de variable de la vista y del Excel no
  cambiaron.
- [x] **T3** (S2) `ReporteriaController::visualizarReporteAdquisiciones` devuelve `totales`
  en el JSON de las tres ramas planas. Las ramas `global` y `comparativo` quedan sin la
  clave a proposito: tienen otra forma de datos (ver "Fuera de alcance").
- [x] **T4** (S2) `resources/js/reportes_scripts.js`: las tres ramas del preview leen
  `response.totales` y dejaron de sumar. Se retiro el helper `totales($valor)`, que quedo
  sin llamadores.
- [x] **T5** (S4) Test de integracion de la clave `totales` y suite completa. Dos tests
  de contrato del JSON (rama materiales y rama contratistas); el camino del PDF lo cubre
  `ReporteTipoInvalidoTest::test_el_pdf_de_adquisiciones_con_tipo_valido_sigue_generando_el_pdf`
  y los numeros son los mismos por construccion (misma funcion).

## Log

- **L1** — "que es esto "permiso fino del visor, duplicación preview/PDF en reportes""
- **L2** — "dale vamos con el refacto de totales"
- **L3** — Alcance elegido: "Fuente única real: PHP calcula y el preview lo muestra" —
  "Extraigo una función pura, la usan PDF y Excel, el JSON del preview pasa a incluir
  `totales` y el JS deja de sumar. Elimina la deriva de raíz, pero toca el contrato JSON
  y el JS compartido con otras pantallas."
- **L4** — Correccion del diagnostico previo: caja ya tiene fuente unica
  (`MovimientoCaja::dataReporteCaja:128-186`) y balance tambien
  (`Proyecto::totalesBalanceReporte:428`). La duplicacion real esta en adquisiciones, y no
  es PHP contra PHP: el preview suma en el navegador. Este documento reemplaza a
  `reportes-pendientes-baja.md` L9 en su parte de adquisiciones.
- **L5** — Forma real de los datos (evidencia que define la funcion):
  `Adquisicion::dataReporteAdquisiciones` devuelve `'total' => '$ '.number_format($total, 4)`
  y `cantidad` (suma de `cantidad_solicitada`); `Contratista::filtroContratista` agrega
  `total_pagado` y `saldo` con el mismo formato; `ManoObra::filtroManoObra` solo `total`. Por
  eso la funcion limpia `'$'`, `','` y espacios, y una clave ausente cuenta como cero.
- **L6** — Chequeo diferencial (script descartable fuera del repo, `mt_srand(20261105)`):
  la formula vieja inline del PDF contra la funcion nueva, sobre 205 casos (5 de borde y 200
  aleatorios con el formato real de los modelos). **0 diferencias.** La equivalencia
  numerica no se asume: se midio.
- **L7** — El front no tiene bundling: `webpack.mix.js` solo copia `resources/js` a
  `public/js`, y `public/js` no esta trackeado en git. Correr `npm run prod` es parte de
  aplicar el cambio en cada entorno; la corrida local compilo sin errores
  (`reportes_scripts.js` 80.5 KiB).
- **L8** — Contrato del JSON: en HTTP los `0.0` del array PHP se serializan como `0`, asi
  que el test de integracion compara con `assertEquals`, no con `assertSame` (un
  `assertSame` fallaba por tipo, no por numero).
- **L9** — **Cerrada.** Suite completa: `Tests: 259, Assertions: 981`, exit 0, 1m43s, sin
  failures ni errors (253 -> 259 tests: 4 unitarios y 2 de integracion nuevos). Los 72 tests
  de reportes existentes siguen verdes, o sea que el refactor no movio ningun numero
  (`--filter 'Reporte|ReporteCaja|ExportarBalance|FiltroCosto'`, exit 0). Ademas el chequeo
  diferencial de L6 (205 casos, 0 diferencias) y el build de Mix (L7).
  Queda sin commitear: la convencion del proyecto es que los commits los decide el usuario.
