# Reporte global de adquisiciones: los totales salen del servidor

Segundo tramo del refactor de totales. El reporte global suma en tres lugares
independientes: el navegador (`reporteGlobal()` en `reportes_scripts.js`), la vista PDF
(`reporte_adquisiciones_global.blade.php`, acumuladores `@php`) y el modelo
(`Adquisicion::totalesReporteGlobal`). El objetivo es el mismo que el del tramo plano: una
funcion pura es la unica fuente, la vista la renderiza y el preview la recibe en su JSON.

Estado: cerrada.

## Specs

- **S1** — "opcion 2": el reporte global de adquisiciones deja de tener tres
  implementaciones de los mismos totales. `Adquisicion::totalesReporteGlobal()` pasa a
  devolver tambien el desglose por etapa y por categoria (contratistas contratado, pagos y
  saldos; mano de obra; materiales; servicios; total de la etapa) y el total de productos.
- **S2** — La vista PDF deja de acumular con `@php`: lee el desglose del servidor. El pie
  (subtotal, costos indirectos, total general) sigue saliendo de la misma funcion.
- **S3** — El preview recibe el desglose en la clave `totales` del JSON de la rama
  `global`, y el JS lo renderiza en vez de sumar filas.
- **S4** — Los numeros no cambian. Base de cada categoria, identica a la de hoy y a la del
  Reporte Balance de Gerencia: contratistas aportan lo **pagado**, mano de obra su campo
  `pagado`, materiales y servicios su `total`. El subtotal es la suma de esas cuatro.
- **S5** — Tests que fijen el desglose (unitario) y el contrato del JSON (integracion).

Fuera de alcance, documentado: el **Total General con costos indirectos** del preview se
calcula al vuelo mientras el usuario tipea el porcentaje (`phpRound(...,4)` en el
navegador). Al exportar, el PDF lo recalcula en PHP con `calcularProcentaje()`. Son dos
implementaciones de la misma formula; se dejan como estan para no agregar un viaje al
servidor por tecla, pero queda registrado que el documento de registro es el PDF.

## Tasks

Sin commit: los commits los decide el usuario.

- [x] **T1** (S1, S4) Desglose por etapa y categoria en `totalesReporteGlobal`, con test
  unitario. RED: `Undefined array key "por_etapa"` / `"total_productos"` (4 errores);
  GREEN: 10 tests, incluido el cruce global contra el Reporte Balance.
- [x] **T2** (S2) La vista PDF renderiza `$totales['por_etapa'][$etapaNombre]` y perdio los
  cinco acumuladores `@php`. 28 tests de reporte global verdes, incluido el watermark que
  pasa un array `totales` sin `por_etapa` (lo cubre el `?? []`).
- [x] **T3** (S3) La rama `global` del preview devuelve `totales`, con el porcentaje que
  llega por `costos_indirectos`.
- [x] **T4** (S3) `reporteGlobal()` recibe `response.totales` y renderiza el desglose;
  `totalGeneral` pasa a ser el `subtotal` del servidor. Ningun acumulador de filas queda en
  la funcion que arma el global.
- [x] **T5** (S5) Test del contrato del JSON (rama global, con `por_etapa` vacio y
  `porcentaje` 10), build de Mix y suite completa.

## Log

- **L1** — "comitea y luego encaramos opcion 2". Opcion 2 = el segundo punto de lo que
  quedo pendiente: el reporte global de adquisiciones.
- **L2** — Mapa de las tres implementaciones: modelo `totalesReporteGlobal:354` (pie, unico
  consumidor el PDF, `GenerarPdfController:501`); vista
  `reporte_adquisiciones_global.blade.php:159-316` (acumuladores por etapa y por categoria);
  JS `reporteGlobal()` `reportes_scripts.js:1400-1630` (los mismos acumuladores en el
  navegador, mas `totalProductos`).
- **L3** — Hallazgo: la formula del pie tambien esta duplicada en lenguajes distintos.
  PHP: `calcularProcentaje()` = `round($valor / (1 - $porcentaje/100), $precision)`
  (`app/helpers.php:955`). JS: `phpRound()` (`reportes_scripts.js:1041`) imita el `round`
  de PHP con notacion exponencial. Coinciden en la practica, no por construccion.
- **L4** — La base de cada categoria ya es coherente entre las tres implementaciones:
  contratistas `pagos`, mano de obra `pagado`, materiales y servicios `total`. No hay
  deriva numerica hoy: lo que hay es triplicacion de la formula, con el costo de
  mantenimiento y el riesgo de que la proxima correccion se haga en un solo lado.
- **L5** — El subtotal no cambio de valor: la suma por categoria del codigo anterior es la
  misma que la suma de los `total` por etapa (`pagos + mano_obra + materiales +
  servicios`). Lo verifica `ReporteGlobalCuadraConBalanceTest`, que cruza el subtotal con
  los gastos del Reporte Balance de Gerencia.
- **L6** — `total_productos` se suma de `cantidad_total` de materiales y servicios, que es
  exactamente lo que hacia el JS; los contratistas no aportan productos.
- **L7** — La vista tolera un array `totales` sin `por_etapa` (`?? []` por etapa), porque
  `WatermarkReporteGlobalTest` renderiza el blade con un `totales` minimo. Sin ese guard el
  blade rompia.
- **L8** — Verificacion de la exploracion: el subagente `gentle-ai-explore` fallo con
  `provider_unavailable`; el mapa se hizo inline con lecturas acotadas y no se reintento el
  subagente.
- **L10** — Validacion en navegador real (ego lite, sicpro.test, usuario QA con rol
  Administrador), proyecto 17 "Adecentamientos":
  - Etapa Acabados: pagos 18.322,4285 + mano de obra 11.888,5000 + materiales 13.913,2157 +
    servicios 1.159,2000 = 45.283,3442, el mismo numero que muestra la pantalla.
  - Etapa Diseno: 560 + 32 = 592,0000; subtotal 45.875,3442. El input oculto del navegador
    (45875,34423033999) y el subtotal que devuelve el JSON del servidor coinciden con
    diferencia 0.
  - Con 10% de costos indirectos la pantalla muestra 50.972,6047 = round(45875,34423033999 /
    0,9, 4), la misma cuenta que hace calcularProcentaje() en PHP para el PDF.
  - El PDF real responde 200, application/pdf, 119.880 bytes: el blade renderiza con la
    estructura nueva sobre datos vivos, no solo con fixtures.
  - Trampa: el formulario rechaza rangos de fechas con formato inventado ("El rango de
    fechas enviado no es valido"); el reporte se genero sin fechas.
  - Falso positivo: el detector de NaN/undefined marcaba FERNANDO y NARVAEZ. Filtrar por
    tokens, no por subcadenas.
- **L9** — **Cerrada.** Build de Mix: exit 0 (`Compiled successfully`). Suite completa:
  `Tests: 264, Assertions: 995`, exit 0 (eran 259/981: +4 unitarios del desglose y +1 del
  contrato del JSON global). Sin failures ni errors. Queda sin commitear, como el tramo
  anterior hasta que el usuario decida.
