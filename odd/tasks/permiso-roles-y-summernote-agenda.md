# Permiso de roles y Summernote en agenda

Estado: en curso · Rama: `sicpro-laravel` · Autor: el Gentleman
Requisito 1: el módulo de roles pasa de `role:Administrador` a permiso asignable.
Requisito 2: Summernote + sanitización en los tres textarea de `/agenda`.

## Specs

### S1 — Gate del módulo de roles por permiso, no por rol hardcodeado

El módulo `/sistema/roles` debe quedar gobernado por permisos del sistema spatie, y no por
el `role:Administrador` hardcodeado en `routes/modules/sistema.php:78` y
`app/Http/Controllers/Sistema/RoleController.php:16`.

Motivo textual del usuario (L2): «solo el usuario administrador puede entrar al modulo de
roles y permisos pero como no veo ese permiso no se lo puede asignar a otro rol».

Verificación:
- `GET /sistema/roles` (index) y `GET /sistema/roles/{role}/editar` exigen `sistema.roles.ver`.
- `PUT /sistema/roles/{role}/update` exige `sistema.roles.gestionar`.
- Un usuario autenticado sin esos permisos recibe 403.
- Un usuario con `sistema.roles.ver` entra.
- El rol `Administrador` conserva el acceso (vía `Gate::before`, `AuthServiceProvider:30-32`).

### S2 — El permiso debe aparecer en la UI para asignarlo

Motivo textual del usuario (L2): «porque no aparece el permisos en roles para asignar
permisos a un rol creo que esta oculto».

Causa raíz medida: `database/seeders/PermissionTableSeeder.php` nunca crea `sistema.roles*`.
`RoleController@edit` (`:56`) solo dibuja un padre con hijos, y
`resources/views/sistema/roles/edit.blade.php:24-48` no filtra nada. No está oculto: no existe.

Verificación:
- Existe el permiso `sistema.roles.ver` y `sistema.roles.gestionar` con `parent_id` = id de `sistema`.
- Deben colgar **directamente** de `sistema`: `RoleController@edit:44` consulta
  `Permission::whereNull('parent_id')` y luego solo los hijos directos, con lo cual un grupo
  intermedio `sistema.roles` nunca se renderizaría (hallazgo de implementación).
- `GET /sistema/roles/{role}/editar` renderiza los checkboxes de ambos permisos.
- Para entrar hace falta `sistema.ver` (módulo) **y** `sistema.roles.ver` (submódulo), igual
  que `sistema.usuarios.*`: el grupo de `routes/modules/sistema.php:16` exige `sistema.ver`.
  `Gerencial` ya tiene `sistema.ver`, pero no `sistema.roles.ver` (S3).

### S3 — Ningún rol obtiene el permiso de forma implícita

`database/seeders/RoleTableSeeder.php:62` concede a `Gerencial` `sistema%` completo. Un
`like 'sistema%'` absorbería `sistema.roles.*` y daría a Gerencial acceso a la gestión de
roles sin que nadie lo pida.

Verificación:
- Tras `db:seed`, `Gerencial` NO tiene `sistema.roles.ver` ni `sistema.roles.gestionar`.
- `Administrador`, `Operativo`, `Administrativo`, `Marketing` y `Sistema` tampoco los tienen
  por seed; solo `Administrador` entra por el bypass de `Gate::before`.

### S4 — Summernote en los tres textarea de /agenda

Motivo textual del usuario (L1): «hay que incluir summernote en los textarea de /agenda».
Alcance elegido por el usuario: «Todos, con sanitización».

Superficies: `resources/views/modals/tareas.blade.php:29` (`descripcion`),
`resources/views/modals/comentarios_tarea.blade.php:47` y `resources/views/tareas/comentarios.blade.php:39`.

Verificación:
- Los tres textarea se inicializan como editor Summernote al abrir su pantalla.
- El editor de agenda no hereda el `placeholder` «Escribe el contenido del contrato aquí...»
  ni la altura de 900 px de `resources/js/app.js:261`.

### S5 — Sanitización con allowlist antes de persistir

Motivo: el usuario eligió «limpieza de HTML permitido al guardar (allowlist de etiquetas)
antes de persistir».

Verificación:
- `describir`/`descripcion` de tarea y de comentario se sanitizan antes del `save`.
- `<script>`, atributos `on*` y `javascript:` se eliminan.
- Etiquetas de la allowlist (`p`, `br`, `strong`, `b`, `em`, `i`, `u`, `s`, `ul`, `ol`, `li`,
  `a[href]`, `h1`-`h6`, `blockquote`, `table`/`thead`/`tbody`/`tr`/`td`/`th`) se conservan.
- El contenido se muestra formateado (`{!! !!}`), nunca como etiquetas crudas, en
  `resources/views/tareas/partials/list.blade.php` y en la vista de detalle.

### S6 — Sin regresión en el editor global existente
`resources/js/app.js:261` inicializa `.summernote` para contratos y actas de entrega
(`marketing/seguimiento_ventas/partials/form_reserva.blade.php:224`,
`form_acta_entrega.blade.php:29`). Ese comportamiento no debe cambiar.

Verificación: para ambos formularios, el init conserva `height: 900` y el
`placeholder` de contrato.

## Tasks
| ID | Specs | Ruta | Commit | Evidencia |
| -- | ----- | ---- | ------ | --------- |
| T1 | S1, S2, S3 | inline (parent) | `permiso sistema.roles.*` + gate por permiso | `RolesPermissionGateTest` 9/9 verde |
| T2 | S4, S5 | `gentle-ai-worker` + correcciones del parent | summernote + sanitizador en agenda | `AgendaHtmlSanitizationTest` + `AgendaSanitizationRegressionTest` 10/10 verde |
| T3 | S6 | inline (parent) | sin cambio extra: verificación diferencial | 8/8 checks OK contra `HEAD` |

## Log

**L1 (usuario, textual):** «hola quiero que me ayudes unas cositas: 1. hay que quitar el mensaje 403 Acceso Restringido que esta harcodeado ya que solo el usuario con rol administrador puede entrar pero como ya esta los roles y permisos ese control esta demas, 2. hay que incluir summernote en los textarea de /agenda.»

**L2 (usuario, cambio de requisito, textual):** «lo que hay que ver es porque no aparece el permisos en roles para asignar permisos a un rol creo que esta oculto y mi requisito cambia porque solo el usuario administrador puede entrar al modulo de roles y permisos pero como no veo ese permiso no se lo puede asignar a otro rol»

**L3 (usuario, alcance Summernote):** «Todos, con sanitización».

**L4 (evidencia):** `sistema.roles.*` no existe en ningún seeder; `RoleController@edit:56`
solo pinta padres con hijos; `edit.blade.php:24-48` no filtra. No está oculto, no existe.
`Gate::before` (`AuthServiceProvider:30-32`) da todo a `Administrador`, que tiene
`syncPermissions([])` (`RoleTableSeeder:39`). `RoleTableSeeder:62` da `sistema%` a Gerencial.

**L5 (hallazgo de implementación):** la pantalla de edición de roles renderiza solo dos
niveles, por lo que los permisos de tercer nivel (`sistema.usuarios.ver`,
`sistema.configuracion.ver`, etc.) tampoco son asignables desde la UI. Se resuelve para
roles colgando `sistema.roles.*` de `sistema`; el resto queda como follow-up.

**L6 (hallazgo de implementación):** `/sistema/*` completo exige `sistema.ver`
(`routes/modules/sistema.php:16`), así que otorgar el módulo de roles a otro rol requiere
marcar también «Ver módulo de Sistema».

**L7 (hallazgo de implementación):** `resources/js` se copia tal cual a `public/js`
(`webpack.mix.js:27`); cualquier cambio en `resources/js/app.js` debe replicarse en
`public/js/app.js` o no llega al navegador.

**L8 (verificación independiente, blockers corregidos):**
- B1: el `comentario` es texto plano y no pasa por el editor; sanitizarlo con el
  serializador HTML guardaba `A &amp; B` y la UI volvía a escapar. Se guarda con
  `strip_tags` (sin serializar) y se escapa al mostrar.
- B2: `resources/views/pdf/tareas.blade.php:181` mostraba `&lt;p&gt;…` porque la columna
  ahora guarda HTML. Se convierte a texto con `agendaPlainText()`.
- A1: se cambió el `<p>` contenedor de la descripción por un `<div>`; un `<p>` dentro de
  otro `<p>` rompía el truncado a 3 líneas.
- A3: `resources/views/tareas/comentarios.blade.php` no está referenciada por ninguna
  ruta ni include; se actualizó igual y queda como follow-up.

**L9 (deuda conocida, fuera de alcance):** la pantalla de edición de roles renderiza
solo dos niveles, así que los permisos de tercer nivel del módulo `sistema`
(`sistema.usuarios.ver`, `sistema.configuracion.ver`, `sistema.productos.*`, etc.)
siguen sin ser asignables desde la UI. Los roles no administradores los obtienen solo por
el comodín de `RoleTableSeeder`.

**L10 (corrección de revisión):** R3-002 (sin cambio de código, disposición
documentada): `/public` está íntegramente gitignoreado (`.gitignore:2`), por lo que
`public/js/app.js` y `public/js/tareas_scripts.js` nunca pueden aparecer en el candidato
de revisión; son salida de build generada por `npm run prod` (`webpack.mix.js:27`
`mix.copyDirectory('resources/js', 'public/js')`). La copia de trabajo YA está
sincronizada y `s6_el_espejo_publico_del_js_esta_actualizado` lo fija.
Regla href (R2-1/R3-001): normalizar el candidato quitando `[\x00-\x20\x7f]` en toda
la cadena, rechazar el vacío, admitir solo `http://`, `https://`, `mailto:`, `tel:` o URL
relativa, y guardar el valor NORMALIZADO, nunca el original ofuscado.
Follow-ups aceptados (no se corrigen ahora): R1-001 `strip_tags` en comentarios de texto
plano puede borrar texto que parece etiqueta (`use <file> now`); R3-003 un `</div>`
parásito cierra el wrapper del sanitizador y deja caer texto posterior en silencio;
R3-004 la allowlist permite `<a>` dentro del propio `<a class="agregar-comentario">` de
la tarjeta del listado, así que los anchors anidados pueden romper el excerpt.

**L11 (revisión nativa, lineage `review-a4511034dbf335d7`):** revisión ordinaria de tres lentes(risk, reliability, readability) sobre el rango `24b14747..7fdd6829`. Encontró el bypass de
`href` ofuscado (R2-1/R3-001), que fue corregido en `7fdd6829` y validado por el validador
dirigido del proveedor. Resultado: `native-approved-acknowledgement-completed`, autoridad
quemada, entrega por política ordinaria del repositorio. Hallazgos informativos no bloqueantes
que quedan como follow-up: R1-001 (`strip_tags` en comentarios puede perder texto con forma de
etiqueta), R2-2, R3-003 (`</div>` suelto cierra el wrapper del sanitizador y descarta texto
posterior), R3-004 (`<a>` permitido dentro del `<a>` de la tarjeta del listado), R3-005.

**L12 (usuario, cambio de requisito, textual):** «vemos el summernote de agenda no esta aplicado a los demas textarea falto el de COMENTARIOs»

### S7 — Summernote tambien en el campo `comentario` de /agenda

Motivo textual del usuario (L12): «falto el de COMENTARIOs».

Anula la decision de texto plano de L8-B1: el comentario pasa a ser contenido de editor.

Superficies: `resources/views/modals/comentarios_tarea.blade.php:67` (id `comentario`, el que usa
`#guardar-comentario`) y `resources/views/tareas/comentarios.blade.php:59` (pagina huerfana).

Verificacion:
- Los dos `comentario` se inicializan como editor Summernote con las opciones de agenda.
- Al escribir se sanitiza con la misma allowlist de `App\Support\HtmlSanitizer` (se revierte el
  `strip_tags` de L8-B1); `<script>` y `on*` no sobreviven, `<p>`/`<strong>`/`<ul>` si.
- Al leer (`getComentariosTarea`) se sanitiza tambien, para cubrir filas anteriores al editor.
- El listado renderiza el comentario formateado dentro de un `div.comentario`, sin doble
  escapado: `A & B` se ve `A & B`, no `A &amp; B`.
- La edicion inline de un comentario tambien es editor y conserva el formato al aceptar y al
  cancelar.
