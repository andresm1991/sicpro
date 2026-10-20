-- ============================================================================
-- Gerencial: acceso al modulo Sistema, administracion de usuarios y configuracion
-- Base: sicpro (principal).  NO aplica a jp_limpieza (esa base no usa RBAC).
-- ============================================================================
--
-- QUE HACE
--   Le otorga al rol `Gerencial` los permisos de `sistema.*` que la decision del
--   2026-10-19 aprobo:
--
--     sistema.ver                      ver el modulo /sistema
--     sistema.usuarios.ver             listar usuarios
--     sistema.usuarios.crear           crear usuarios
--     sistema.usuarios.editar          editar usuarios
--     sistema.usuarios.eliminar        eliminar usuarios
--     sistema.usuarios.gestionar       administrar usuarios
--     sistema.configuracion.ver        ver configuracion
--     sistema.configuracion.gestionar  administrar configuracion
--     sistema.rubros.ver               entrar a Rubros (grupo propio dentro de /sistema)
--
--   Los nombres no son decorativos: son exactamente los que el codigo exige.
--   Busqueda exhaustiva sobre routes/ (`grep -rh 'permission:sistema' routes/`)
--   da cinco permisos efectivos —`sistema.ver` (grupo /sistema),
--   `sistema.usuarios.{ver,crear,editar,eliminar}`, `sistema.rubros.ver` y
--   `sistema.configuracion.ver`— y los dos `.gestionar`, que existen en el
--   catalogo como el nombre canonico de "administrar". Todo lo demas del
--   catalogo (`sistema.proveedores.*`, `sistema.productos.*`,
--   `sistema.inventario.*`, `sistema.clientes.*`) NO se exige en ninguna ruta:
--   esas pantallas cuelgan del grupo /sistema y basta `sistema.ver`.
--
--   Rutas que quedan alcanzables (routes/modules/sistema.php): `sistema.index`
--   (permission:sistema.ver), `sistema.users.*` (permission:sistema.usuarios.*),
--   `sistema.rubros.*` (permission:sistema.rubros.ver) y `sistema.config.*`
--   (permission:sistema.configuracion.ver). `sistema.roles.*` NO se toca: lo
--   gobierna `role:Administrador`, no un permiso.
--
-- POR QUE UN SCRIPT Y NO `db:seed`
--   `RoleTableSeeder` NO describe la base viva: le faltan 67 permisos a
--   Gerencial, le sobran 23 a Operativo y le da a Gerencial
--   `reporteria.financiero.ver`, que el seeder excluye explicitamente. Correr
--   `db:seed --class=RoleTableSeeder` en produccion cambiaria el acceso de
--   cuatro roles de una vez. Este script toca UN rol y OCHO permisos.
--
-- IMPACTO MEDIDO ANTES DE CORRER (sobre `sicpro` de desarrollo)
--   filas insertadas ............... 8 (los ocho que le faltan; `sistema.ver` y
--                                     los siete padres de menu ya los tiene)
--   filas borradas ................. 0
--   filas modificadas .............. 0
--   tablas afectadas ............... 1 (`role_has_permissions`, solo INSERT)
--   roles afectados ................ 1 (`Gerencial`)
--   permisos `sistema.*` de Gerencial ............... 8 -> 16
--   permisos totales de Gerencial ................... 53 -> 61
--   Medido tambien en base aislada (`sicpro_testing`): pasada 1 y pasada 2
--   dan las mismas 16 filas —`INSERT IGNORE` no duplica— y ningun otro rol
--   cambia (Administrativo 37, Marketing 5, Operativo 4, Administrador 0,
--   Sistema 0).
--
-- COMO CORRERLO
--   mysql -u <usuario> -p sicpro < 2026_10_19_gerencial_permisos_sistema.sql
--   o dentro de una sesion:  source <ruta>/este_archivo.sql
--
--   Es idempotente: `INSERT IGNORE` sobre la PK (permission_id, role_id) de
--   Spatie, asi que correrlo dos veces no duplica ni falla.
--
-- DESPUES DE CORRERLO
--   Spatie cachea el mapa de permisos 24 h (`config/permission.php`), y el store
--   por defecto de este proyecto es `file`. Limpiar la cache del servidor:
--     php artisan permission:cache-reset
--   Si ese comando imprime "Unable to flush cache", no es un error del grant:
--   el driver `file` de ese proceso no pudo borrar la clave (visto en Windows).
--   Usar el martillo y listo:
--     php artisan cache:clear
--   Sin limpiar la cache, los usuarios ya logueados siguen viendo el menu viejo.
--
-- VERIFICACION
--   Las dos consultas del final listan lo otorgado y el total por rol.
--   Esperado en `Gerencial`: 16 permisos con prefijo `sistema.` (8 previos +
--   los 8 de este script), y el total de permisos +8.
-- ============================================================================

SET NAMES utf8mb4;

-- ---------------------------------------------------------------------------
-- 1) Otorgar los permisos (idempotente)
-- ---------------------------------------------------------------------------
INSERT IGNORE INTO role_has_permissions (permission_id, role_id)
SELECT p.id, r.id
FROM permissions p
JOIN roles r
  ON r.name = 'Gerencial'
 AND r.guard_name = 'web'
WHERE p.guard_name = 'web'
  AND p.name IN (
      'sistema.ver',
      'sistema.usuarios.ver',
      'sistema.usuarios.crear',
      'sistema.usuarios.editar',
      'sistema.usuarios.eliminar',
      'sistema.usuarios.gestionar',
      'sistema.configuracion.ver',
      'sistema.configuracion.gestionar',
      'sistema.rubros.ver'
  );

-- ---------------------------------------------------------------------------
-- 2) Verificacion: que quedo otorgado
-- ---------------------------------------------------------------------------
SELECT p.name AS permiso
FROM role_has_permissions rp
JOIN roles r       ON r.id = rp.role_id
JOIN permissions p ON p.id = rp.permission_id
WHERE r.name = 'Gerencial'
  AND p.name LIKE 'sistema%'
ORDER BY p.name;

-- ---------------------------------------------------------------------------
-- 3) Verificacion: cuantos permisos tiene cada rol (control de no-regresion)
--    Antes de correr esto, anotar los numeros. Despues, el unico que cambia es
--    `Gerencial` (+8).
-- ---------------------------------------------------------------------------
SELECT r.name AS rol, COUNT(rp.permission_id) AS permisos
FROM roles r
LEFT JOIN role_has_permissions rp ON rp.role_id = r.id
GROUP BY r.name
ORDER BY r.name;
