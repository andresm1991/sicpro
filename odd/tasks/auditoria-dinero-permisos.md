# Auditoria de dinero y permisos (tramo 1)

Hoy existe `LogService::log()` (~86 usos en 16 archivos) escribiendo en la tabla
`logs`, pero no es auditoria: nadie la lee, no guarda entidad, id ni valores
viejos/nuevos, y hay flujos criticos con cero registro (caja, usuarios y roles,
prestamos, nomina, bajas de inventario). Este tramo convierte ese log en una
auditoria consultable para el dinero y los permisos.

## Specs

S1 — Pedido original, verbatim: "ok ahora quiero revises este proyecto y agregues
logs en donde sea necesario porque veo que existe la logica pero no hay
auditoria".

S2 — Forma elegida, verbatim de la opcion: "Auditoría + arreglar lo operativo".
Contenido de esa opcion, verbatim: "Todo lo de la opcion 1, mas: - indice en
logs(entidad, entidad_id, created_at) - purga/retencion de la tabla logs - canal
daily con retencion para laravel.log (hoy single, >512 MB)". Y de la opcion 1:
"Migración que agrega entidad, id, antes/después, actor e IP + un servicio único +
una pantalla para consultarlo (recomendado)".

S3 — Alcance del primer tramo, verbatim: "Dinero y permisos primero" y "Modulos:
CajaController, ManoObraController, AdministrativoController (pagos),
PrestamoController, ContratistaController, AdquisicionController, UserController,
Sistema/RoleController."

S4 — Base del log, verbatim: "Una tabla por base" y "Requiere una migracion y un
modelo Log en la conexion jp_limpieza, y ajustar los 2 call sites de JPLimpieza."
Beneficio elegido, verbatim: "cada base es autocontenida".

S5 — El visor no puede romper nada existente: los ~86 call sites de
`LogService::log()` tienen que seguir funcionando sin cambios (el servicio
mantiene su firma) y el visor es de solo lectura.

## Tasks

| # | Spec | Que | Commit |
|---|------|-----|--------|
| T1 | S2, S5 | Infraestructura: columnas de auditoria en `logs` (entidad, entidad_id, accion, valores_antes, valores_despues, user_id, ip) + indice, `LogService::auditar()`, visor `/sistema/auditoria` con filtros y paginacion, comando de purga con retencion, stack de logging a `daily` con retencion | |
| T2 | S4 | `logs` propia en `jp_limpieza`: migracion, `App\Models\JPLimpieza\Log`, servicio JP, los 2 call sites de JPLimpieza y selector de base en el visor | |
| T3 | S3 | Caja: alta y baja de movimientos de caja con antes/despues | |
| T4 | S3 | Mano de obra y nomina: planificacion (alta/edicion/baja), registro de trabajadores y `registrarPagoManoObra` | |
| T5 | S3 | Prestamos: alta, edicion y `registrarPago` con el saldo y el estado antes/despues | |
| T6 | S3 | Contratistas: orden de trabajo (alta/edicion/baja) y sus pagos (alta/edicion/baja) | |
| T7 | S3 | Adquisiciones: alta, edicion, baja de pedido y orden de recepcion con id de entidad | |
| T8 | S3 | Usuarios y roles: alta/edicion/baja, cambio de clave, `roles()->sync()` y `syncPermissions()` con antes/despues | |
| T9 | S2, S3 | Cierre del tramo: documentar la convencion en WARP.md, verificar el visor end-to-end y correr la suite completa | |
| T10 | S3 | Auditar `AdministrativoController::pagoOrdenTrabajo` (`:676`): pone `pagado = true`, el flag que decide si ese dinero entra al balance, y cambia el estado de la orden; hoy solo tiene un `log()` generico | |
| T11 | S3 | Auditar `AdministrativoController::actualizarContratista` (`:615`): muta cantidades, valores y costo indirecto de la orden y hasta `Articulo::valor_unitario`, hoy sin antes/despues | |
| T12 | S3 | Auditar el registro publico (`Auth/RegisterController.php:70`): crea una credencial sin ningun rastro | |
| T13 | S3 | Auditar las mutaciones administrativas de adquisiciones (`AdministrativoController::actualizarAdquisicion`, `createRecepcionAdministrativo`, `destroyPedido`, `agergarProductoAdquisicion`), reportadas por el worker de T7 | |

## Log

L1 (pedido original, verbatim): "ok ahora quiero revises este proyecto y agregues
logs en donde sea necesario porque veo que existe la logica pero no hay
auditoria".

L2 (decision del humano, verbatim de las opciones elegidas): forma = "Auditoría +
arreglar lo operativo"; alcance = "Dinero y permisos primero"; base = "Una tabla
por base".

L3 (evidencia del relevamiento, path:line): `app/Services/LogService.php:11`
escribe `App\Models\Log` (tabla `logs`: level, message, context json, timestamps)
y **nadie la lee**: sin ruta, sin vista, sin indice, sin columnas de entidad. Sin
observers, traits ni paquete de auditoria. Antes/despues solo en
`ProyectoController.php:271` y `ProveedorController.php:200`. Niveles
inconsistentes ('error'/'ERROR'/'success') y claves divergentes
('message'/'mensaje'/'error_message'/'execption'). `revision_caja` es lo unico con
actor + fecha + intencion y cubre solo la revision de un rango de caja.
`storage/logs/laravel.log` supera los 512 MB con el canal `single` (sin
rotacion). `database/migrations/jp_limpieza/` existe y los modelos JP usan
`protected $connection = 'mysql_jp_limpieza'`.

L4 (decision de implementacion para T1): el visor reusa el permiso existente
`sistema.ver` (el modulo ya es solo-admin, ver `routes/modules/sistema.php:11`)
en vez de crear un permiso nuevo, para no tocar el catalogo de permisos ni sus
tests. Se documenta aca por si el humano quiere un permiso mas fino despues.

L5 (hallazgos del worker de T6, NO editados por estar en un archivo que otro
agente estaba tocando en paralelo): `AdministrativoController::pagoOrdenTrabajo`
(`:676`) escribe `pagado = true`, el flag que decide si el dinero entra al
balance, y cambia el estado de la orden, con un `log()` generico y sin
entidad/valores; `AdministrativoController::actualizarContratista` (`:615`) muta
cantidades, valores y costo indirecto de la orden y hasta
`Articulo::valor_unitario`, con el mismo `log()` generico. Quedan como T10 y T11.

L6 (hallazgo del worker de T8, NO editado por estar fuera del alcance):
`Auth/RegisterController.php:70` usa `Hash::make` al registrar un usuario en el
flujo publico y no deja auditoria.

L7 (hallazgo del worker de T7, NO editado por estar en un archivo que otro agente
estaba tocando): en `AdministrativoController` quedan sin auditar
`actualizarAdquisicion`, `createRecepcionAdministrativo`, `destroyPedido` y
`agergarProductoAdquisicion`, que mutan adquisiciones y recepciones. Queda como
T13.

## Evidencia de commits

| Task | Commit |
|------|--------|
| T1 | `70b9909c` |
| T3 | `b82698fa` |
| T4 | `f7fb029a` |
| T5 | `74c8d463` |
| T6 | `5655da9e` |
| T7 | `eff22068` |
| T8 | `6949e550` |
| T9 | el commit que agrega esta tabla |
| T10, T11 | `f0e07ba7` |
| T12 | `df70cbaa` |
| T2, T13 | pendientes |

Follow-ups de prestamos (documento `odd/tasks/prestamos-saldo-y-estado.md`):
`e38ba7e2` (fix del estado y auditoria del recalculo) y `bb9bd2f8` (residuo de
redondeo y `sum()` a la base).

Verificacion del cierre: suite completa `227 tests, 801 assertions, 4 fallos` (los
4 preexistentes de roles/modulo sistema, 0 errores) y el visor probado end-to-end
contra la base real: el registro se ve, el antes/despues se despliega y la fila
guarda actor e IP.
