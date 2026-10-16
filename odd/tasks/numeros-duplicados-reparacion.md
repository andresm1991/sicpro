# Reparacion de los numeros de adquisicion duplicados historicos

Llevar `adquisiciones.numero` al valor que la aplicacion garantiza hoy (fecha de registro
+ id) en las dos bases: `sicpro` y `jp_limpieza`. Los scripts se entregan para correr
tambien en produccion.

Estado: en curso.

## Specs

- **S1** — "ataquemos los historios duplicados pero me das los script para aplicarlos en la
  base de produccion tambien". Se reparan los duplicados historicos y se entregan scripts
  SQL aplicables a produccion, con respaldo y rollback.
- **S2** — El numero definitivo de una adquisicion es `fecha de registro (created_at)` +
  `-` + `id` con 3 digitos minimos. Es el valor que asigna el codigo desde el arreglo del
  alta, y el unico estado estable: los derivados son distintos entre si porque el `id` es
  unico y autoincremental.
- **S3** — La reparacion tiene que ser **completa**, no solo de las filas de los grupos
  duplicados. Medido: en `sicpro`, renumerar solo los extras de los 36 grupos duplicados
  (36 filas) crearia **7 numeros repetidos nuevos** (`20250717-845`, `20260310-1724`,
  `20260414-1865`, `20260415-1886`, `20260521-2108`, `20260715-2371`, `20260922-2689`),
  porque hay filas unicas pero desviadas que ocupan el valor derivado de otra fila. Con la
  normalizacion completa quedan 0 duplicados y 0 desviadas.
- **S4** — Nada se aplica sin respaldo previo y sin verificacion posterior que lo pueda
  desmentir.

## Tasks

Sin commit: el usuario no lo pidio.

- [x] **T1** (S1, S3, S4) Script SQL para `sicpro`:
  `database/scripts/2026_10_16_renumerar_adquisiciones_duplicadas_sicpro.sql`. Tabla
  temporal con el valor derivado y la condicion de cambio calculados una sola vez,
  respaldo, UPDATE de 81 filas en transaccion, verificacion y rollback.
- [x] **T2** (S1, S3, S4) El mismo script para `jp_limpieza` (228 filas, 28 grupos).
- [x] **T3** (S4) Ensayo de los dos scripts sobre una copia fiel, cada uno en su propia
  base de ensayo desechable: `sicpro` 81 renumeradas / 81 respaldadas / rollback con 0
  filas distintas del origen y 36 duplicados restaurados; `jp_limpieza` 228 / 228 / rollback
  con 0 filas distintas y 28 duplicados restaurados. Las bases de ensayo se eliminaron.
- [x] **T4** (S1) Aplicado en `sicpro` y `jp_limpieza` con el mismo script ya ensayado, y
  verificado despues de forma independiente (recorriendo las tablas por PHP contra
  `numeroAdquisicion()`, no con el chequeo del propio script): 0 desviadas y 0 duplicados
  en las dos bases, con los totales intactos (2702 y 635 filas y una tabla de respaldo
  cada una).
- **L5** — El ensayo atrapo tres defectos antes de tocar produccion:
  1. **Bloqueante**: el script abortaba con `ERROR 1267 Illegal mix of collations
     (latin1_swedish_ci) and (cp850_general_ci) for operation '<>'`. El cliente `mysql` en
     Windows negocia charset `cp850`, y comparar la columna `numero` (collation de la
     tabla) contra el valor calculado en la tabla temporal (collation de la conexion) es un
     mix ilegal. Corregido con `CAST(... AS BINARY)` en todas las comparaciones y
     `SET NAMES utf8mb4`. Sin esto el script habria abortado en la primera consulta en
     produccion.
  2. **Bloqueante**: el respaldo se hacia con `DROP TABLE IF EXISTS` + `CREATE TABLE AS`,
     asi que una segunda corrida (idempotente, sin cambios) lo recreaba **vacio** y
     destruia el punto de rollback. Corregido con `CREATE TABLE IF NOT EXISTS`.
  3. El nombre del respaldo no identificaba la base. En el primer ensayo las dos corridas
     compartieron base, la segunda reuso el respaldo de la primera y el rollback restauro
     datos equivocados (232 filas distintas, 0 duplicados restaurados). En produccion cada
     script corre en su propia base y no puede pasar, pero el nombre ahora lleva sufijo
     (`_sicpro`, `_jp_limpieza`) y el ensayo se repitio con una base aislada por fuente.
- **L6** — Se verifico ademas que la formula del script es **equivalente a la de PHP** en
  las 3337 filas de las dos bases (0 diferencias contra `numeroAdquisicion()`). Sin ese
  chequeo, el script podria haber escrito numeros que la aplicacion nunca generaria.
- **L7** — Aplicado en las bases locales y verificado de forma independiente: `sicpro`
  2702 filas / 0 desviadas / 0 duplicados; `jp_limpieza` 635 / 0 / 0. Ejemplo del cambio:
  el grupo `20251030-1249` (ids 1249 y 1250) quedo en `20251030-1249` y `20251030-1250`.
- **L8** — En cada base quedo una tabla de respaldo: `adquisiciones_numero_backup_20261016_sicpro`
  y `adquisiciones_numero_backup_20261016_jp_limpieza`. Son el rollback; se pueden borrar
  cuando el usuario este conforme con los numeros nuevos. El rollback esta comentado al
  final de cada script.
- **L9** — Para produccion: correr los **mismos** scripts que se ensayaron (las rutas estan
  en el encabezado de cada uno). No se toco ningun dato de produccion desde aca.
- **L10** — Indice unico: el guard de las migraciones consulta los duplicados antes del
  `ALTER` y lanza un `RuntimeException` con la instruccion exacta. Se probo el orden real:
  el test que exige que la base rechace el duplicado daba RED antes de la migracion
  (`Failed asserting that exception of type QueryException is thrown`) y GREEN despues.
- **L11** — Indice `adquisiciones_numero_unique` confirmado por consulta a
  `information_schema.STATISTICS` en `sicpro`, `jp_limpieza` y `jp_limpieza_testing`. En
  `sicpro_testing` lo crea el `migrate:fresh` de la proxima corrida de tests. El indice
  entra sin problema sobre el `varchar` en MySQL 5.7 con el formato de fila por defecto.
- **L12** — Las migraciones de `database/migrations/jp_limpieza` no las corre
  `php artisan migrate` (no hay `loadMigrationsFrom`): hay que ejecutarlas aparte con
  `--path=database/migrations/jp_limpieza --database=mysql_jp_limpieza`. Queda dicho en el
  docblock de la migracion y en el script de produccion.
- **L13** — Hallazgo de entorno: `php artisan test` crashea al **reportar** un fallo de este
  test, con `An error occurred inside PHPUnit: Cannot find TestCase object on call stack`,
  porque Collision emite una deprecacion de PHP 8.5 mientras carga su clase de escritura.
  El resultado del test no es el que muestra: hay que correr
  `php vendor/phpunit/phpunit/phpunit --filter=...` para ver el mensaje real. Afecta a
  cualquier fallo que Collision tenga que renderizar en este entorno.
- [x] **T5** (S2) Decidido por el usuario: se agrega el indice unico.
  `database/migrations/2026_10_16_000001_add_unique_numero_to_adquisiciones.php` y
  `database/migrations/jp_limpieza/2026_10_16_000001_add_unique_numero_to_adquisiciones.php`,
  con un guard que falla con un mensaje que dice que reparacion correr primero en vez del
  `Duplicate entry` crudo. Aplicadas a `sicpro`, `jp_limpieza` y `jp_limpieza_testing`.
  Entregado ademas `database/scripts/2026_10_16_indice_unico_numero_produccion.sql` con el
  orden obligatorio para produccion.

## Log

- **L1** — "ataquemos los historios duplicados pero me das los script para aplicarlos en la
  base de produccion tambien".
- **L2** — Medido antes de reparar: `sicpro` 2702 filas / 36 grupos duplicados / 81 filas
  desviadas; `jp_limpieza` 635 filas / 28 grupos / 228 filas desviadas (215 dentro de
  grupos duplicados). Los derivados distintos igualan a las filas totales en las dos bases,
  asi que la normalizacion completa no puede dejar duplicados.
- **L3** — El alcance minimo (solo grupos duplicados) queda descartado por S3: en `sicpro`
  introduce 7 duplicados nuevos. No hay subconjunto seguro mas chico que la normalizacion
  completa, porque el unico estado estable es "numero == derivado".
- **L4** — Advertencia que el usuario ya tiene: los numeros cambiados pueden estar impresos
  en PDFs ya entregados a proveedores. El PDF se genera al vuelo, asi que una reimpresion
  mostraria el numero nuevo. El campo `adquisiciones.archivo` guarda una ruta que contiene
  el numero viejo: el enlace sigue funcionando (la ruta esta guardada, no se deriva), pero
  queda desalineada.
