# JP Limpieza: numeros de adquisicion y contratista unicos

Alinear el modulo JP Limpieza con el arreglo de numeros duplicados ya aplicado al
modulo principal. El numero se calculaba al renderizar el formulario y se guardaba
tal cual, asi que un formulario viejo repetia el mismo numero.

Estado: en curso.

## Specs

- **S1** — Pedido original del modulo principal: "al momento de crear adquisiciones en
  algunas ocasiones se crean con numeros duplicados". El numero guardado se deriva del
  propio registro (fecha de registro + su id) y nunca del valor que manda el cliente.
  Cumplido en el modulo principal.
- **S2** — "aliniemos JPLimpieza": el mismo criterio en JP Limpieza. La convencion del
  modulo es la misma (sufijo == id propio del registro; verificado en los datos:
  `max_sufijo == max_id` en todos los dias recientes). En `jp_limpieza.adquisiciones`
  habia 28 numeros repetidos entre el 18/07/2025 y el 11/09/2025, el peor con 41 filas.
  El 15/09/2025 se agrego la validacion `unique`, que atajo el sangrado con un error al
  usuario; esa validacion se retira porque valida un valor que pasa a descartarse.
- **S3** — "Aislar JP con su propia base de test (recomendado)": crear
  `jp_limpieza_testing`, agregarla a `phpunit.xml` y migrar el esquema JP ahi, para que
  el alta de JP se pueda ejecutar en un test sin escribir en la base real.
- **S4** — "Si, alinealos tambien": contratistas y pagos de JP. Contratistas tiene
  columna `numero` y el mismo defecto (mas `Contratista::first()` en vez del ultimo, o
  sea sufijo 002 siempre). Pagos **no tiene** columna `numero`: el numero se calcula al
  leer desde el id (`getNumeroFormattedAttribute`), asi que su almacenamiento ya es
  inmune; solo le sobra una vista previa muerta.

## Tasks

Sin commit: el usuario no lo pidio. La referencia de commit de cada tarea es el estado
sin commitear del arbol de trabajo.

- [x] **T1** (S1, S2) Alinear `JPLimpieza\AdquisicionController::store`: ignorar el numero
  enviado, insertar con provisorio unico y derivar del registro, dentro de una
  transaccion de la conexion `mysql_jp_limpieza`.
- [x] **T2** (S2) Vista previa de JP en `create()` y `createAdministrativo()`.
- [x] **T3** (S3) Aislar la conexion JP: crear `jp_limpieza_testing`, migrar el esquema JP
  (18 migraciones), agregar `DB_JPLIMPIEZA_DATABASE` a `phpunit.xml`.
- [x] **T4** (S2, S3) Test del alta de JP: RED contra el codigo previo (stash acotado) y
  GREEN, a nivel controller. `EXIT_RED=1` / `EXIT_GREEN=0`, 12 aserciones.
- [x] **T5** (S4) Alinear `JPLimpieza\ContratistaController::store` y su vista previa, con
  test. `EXIT_RED=1` / `EXIT_GREEN=0`, 10 aserciones.
- [x] **T6** (S4) Pagos de JP: retirar la vista previa muerta de `crear()`. No se toco el
  accessor `numero_formatted`: cambiaria numeros ya emitidos. Se retiro ademas
  `numeroPedido()` de `app/helpers.php`, que quedo sin llamadores.
- [~] **T7** Suite completa y verificacion independiente. Verificacion final sin blockers;
  suite pendiente de la ultima re-corrida tras agregar los guards de aislamiento.

## Log

- **L1** — "hola! quiero que me ayudes con algo y es que al momento de crear
  adquisiciones en algunas ocasiones se crean con numeros duplicados."
- **L2** — Diagnostico y arreglo del modulo principal aceptados con alcance
  "Prevenir solamente": sin tocar los 36 numeros duplicados historicos de `sicpro`.
- **L3** — "aliniemos JPLimpieza"
- **L4** — Decision de test para JP: "Aislar JP con su propia base de test
  (recomendado)".
- **L5** — Alcance de contratistas y pagos de JP: "Si, alinealos tambien".
- **L6** — Hallazgo de la verificacion independiente (A1): en el modulo JP el
  `DB::beginTransaction()` de `store()` abre la transaccion de la conexion default, no
  la de `mysql_jp_limpieza`, asi que el insert con el numero provisorio quedaba
  commiteado y visible. Se corrigio envolviendo el insert y la asignacion en una
  transaccion de la conexion JP; la re-verificacion no encontro blockers.
- **L7** — Hallazgos preexistentes no atendidos: el alta de JP se commitea antes del
  detalle (un fallo posterior deja una adquisicion huerfana); el import muerto de
  `JPLimpieza\AdquisicionAdministrativo` (la clase no existe); el `catch (Exception)`
  del store de JP no atrapa `Error`.
- **L12** — Verificacion independiente final (sin blockers). Advisories, atendidos o
  reportados segun su severidad:
  - **A1 (atendido)** — `config/database.php` toma `DATABASE_JPLIMPIEZA_URL`, que si
    existiera en el `.env` anularia `DB_JPLIMPIEZA_DATABASE` y los tests escribirian en
    una base real en silencio. Hoy esa variable no esta definida en ningun `.env`, pero
    el modo de falla es grave y mudo: los dos tests de JP ahora verifican en `setUp` que
    la conexion apunte a una base terminada en `_testing`, y si no, se saltean con un
    mensaje explicito.
  - **A2 (reportado)** — `RefreshDatabase` solo hace `migrate:fresh` de la conexion
    default, asi que `jp_limpieza_testing` no se trunca: el `assertCount(2)` de los tests
    de JP asume que esas tablas no tienen filas de corridas manuales. El mensaje del
    assert ahora lo dice.
  - **A3 (atendido)** — el docblock de `GuardarRevisionCajaJPTest` afirmaba que
    `phpunit.xml` no aislaba JP; ya no era cierto.
  - **A4 (reportado, preexistente)** — la migracion JP
    `2025_09_29_180529_add_column_estado_to_proyectos` usa `Schema::table('proyectos')`
    sin `connection`: solo es segura porque `migrate --database=mysql_jp_limpieza` fija la
    conexion default (`Migrator::setConnection`). Sin ese flag alteraria la base default.
    No se toco por ser una migracion ya aplicada.
  - **A5, A6, B1** — ya registrados en L11, L7 y L7.
- **L13** — Nota de alcance: el arbol de trabajo tambien trae los cambios del modulo
  principal (adquisiciones + sus vistas + su test) y cambios ajenos preexistentes
  (`config/database.php` con la conexion `pgsql_v2`, `odd/tasks/postgres-v2*.md`). No
  commiteado nada; los commits y la separacion en PR los decide el usuario.
- **L8** — Pendiente de decision del usuario: los 28 numeros duplicados historicos de
  JP (72+ filas, el peor con 41) y el indice unico que hoy no se puede crear.
- **L14** — **Resuelto**, y el trabajo sigue en `odd/tasks/numeros-duplicados-reparacion.md`:
  los 28 grupos de JP se repararon (228 filas renumeradas, verificado con 0 duplicados) y el
  indice unico `adquisiciones_numero_unique` ya existe en `jp_limpieza`. La migracion JP
  esta en `database/migrations/jp_limpieza/2026_10_16_000001_add_unique_numero_to_adquisiciones.php`.
- **L10** — Al alinear los tres usos de `numeroPedido()` (adquisiciones x2, contratistas,
  pagos) ese helper quedo sin llamadores: se retiro de `app/helpers.php`. Era ademas una
  trampa, porque los llamadores de contratistas y pagos le pasaban `::first()` en vez del
  ultimo registro.
- **L11** — Pagos de JP: nada que arreglar en el almacenamiento (`pagos_contratistas` no
  tiene columna `numero`; el que se muestra se deriva del id al leer, o sea que ya es unico
  por construccion). Queda reportada una inconsistencia de presentacion que NO se toco:
  el label del formulario de pago usa `numeroOrden($pago)` (sufijo `id + 1`, y `001` fijo
  cuando el pago es nuevo), mientras que las listas y el PDF muestran
  `getNumeroFormattedAttribute()` (sufijo = id, con el prefijo de la fecha del pago).
  Cambiar esa formula alteraria numeros ya emitidos.
- **L9** — Puesta en marcha del aislamiento de JP, una vez por maquina (los comentarios
  de XML no admiten doble guion, por eso los comandos viven aca y no en `phpunit.xml`):

  ```sh
  mysql -e "CREATE DATABASE IF NOT EXISTS jp_limpieza_testing"
  php artisan tinker --execute="DB::statement('CREATE DATABASE IF NOT EXISTS jp_limpieza_testing');"
  DB_JPLIMPIEZA_DATABASE=jp_limpieza_testing php artisan migrate --path=database/migrations/jp_limpieza --database=mysql_jp_limpieza --force
  ```

  Ya ejecutado en esta maquina: 18 migraciones aplicadas a `jp_limpieza_testing`.
  Verificado despues que `sicpro` quedo intacta: en `sicpro.proyectos`, `estado_id`
  conserva su FK a `catalogo_datos` (esquema principal), mientras que en la base de
  test quedo el `bigint unsigned default 151` sin FK que define la migracion JP.
