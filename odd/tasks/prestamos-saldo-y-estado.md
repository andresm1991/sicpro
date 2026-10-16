# Prestamos: saldo y estado del pago

Defecto de dinero confirmado en el flujo de prestamos, encontrado mientras se
instrumentaba la auditoria (T5). El humano decidio arreglarlo y ademas revisar
el resto de los calculos de saldo por si hay mas aritmetica rota del mismo tipo.

## Specs

S1 — Pedido, verbatim de la opcion elegida: "Arreglarlo y revisar todos los
saldos" y "Ademas de ese, reviso los otros calculos de saldo de prestamos
(recalcularPagos y el saldo semanal) por si hay mas aritmetica rota del mismo
tipo".

S2 — Evidencia verificada por el parent en `app/Http/Controllers/PrestamoController.php`:

```php
$prestamo->saldo = $prestamo->saldo - $monto_pagado;              // saldo ya reducido
$prestamo->estado_id = $prestamo->saldo - $monto_pagado <= 0 ? pagado : pendiente;  // resta otra vez
```

Con saldo 1000 y un pago de 600 el saldo queda en 400 pero el prestamo se marca
pagado. La condicion correcta es el saldo ya actualizado: `$prestamo->saldo <= 0`.

S3 — Cuidado con lo compartido: `app/helpers.php` (`calcularCuotaSemanalPrestamo`,
`calcularFechasPago`) lo usan otros modulos. Si el defecto esta ahi, no se cambia
su comportamiento en esta unidad: se reporta con evidencia y se arregla aparte.

## Tasks

| # | Spec | Que | Commit |
|---|------|-----|--------|
| T1 | S1, S2 | Revisar la aritmetica de saldo y estado del flujo de prestamos (`registrarPago`, `recalcularPagos`, `agregarTodosPagos`, `calcularCuotaSemanalPrestamo`) y entregar los hallazgos con path:line | evidencia |
| T2 | S1, S2 | Arreglar la doble resta de `registrarPago` con dos tests: un pago parcial no marca pagado y el ultimo pago si marca | |
| T3 | S1 | Auditar `recalcularPagos`, que reescribe el plan de pagos y el saldo y hoy no deja rastro (cierre del follow-up de T5) | |

## Log

L1 (pedido original de la sesion, verbatim): "ok ahora quiero revises este
proyecto y agregues logs en donde sea necesario porque veo que existe la logica
pero no hay auditoria".

L2 (hallazgo del worker de T5, verificado por el parent): "registrarPago tiene
una doble resta preexistente en el calculo de estado_id (saldo ya reducido menos
monto otra vez); no la toque por ser comportamiento existente, pero con pagos
parciales pequenos podria marcar 'pagado' antes de tiempo".

L3 (decision del humano, verbatim de la opcion elegida): "Arreglarlo y revisar
todos los saldos".

L4 (revision completa del flujo, hallazgos con path:line que NO se tocan por ser
de otra clase que S2):
- MEDIA, deriva de centavos: `recalcularPagos` (`:413,416-423`) reparte con
  `round($saldo/$n, 2)` sin asignar el residuo a la ultima cuota (1000/3 ->
  3 x 333,33 = 999,99), asi que el plan puede no sumar el saldo al centavo.
  Mismo patron en `updatePrestamo` (`:180,194,198,202`) y en `agregarTodosPagos`
  (`:285-299`).
- BAJA, contabilidad muerta: en el closure de `recalcularPagos` el decremento de
  `$saldoRestante` se pierde (captura `use` por valor, sin referencia).
- BAJA, relacion posiblemente stale: `updatePrestamo` (`:158,213`) usa
  `$prestamo->pago_prestamo->sum(...)` (coleccion en memoria) en vez de
  `->pago_prestamo()->sum(...)`.
- Los helpers compartidos (`app/helpers.php:454-487`, `calcularCuotaSemanalPrestamo`
  y `calcularFechasPago`) no tienen defecto y no se tocaron (S3).

## Evidencia de commits

| Task | Commit |
|------|--------|
| T1 | evidencia en L4 |
| T2, T3 | `e38ba7e2` |

Los hallazgos de L4 quedan como follow-ups (deriva de centavos y `sum()` en
memoria), fuera del alcance que autorizo el humano.
