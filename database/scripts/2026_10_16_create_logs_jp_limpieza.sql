-- ============================================================================
-- Tabla logs propia de JP Limpieza (S4: "Una tabla por base")
-- Base: jp_limpieza  (modulo JP Limpieza)
-- ============================================================================
--
-- QUE HACE
--   Crea la tabla `logs` en la base jp_limpieza con las mismas columnas e
--   indices que la tabla principal `logs` (ver
--   database/migrations/2024_09_02_173328_create_logs_table.php y
--   database/migrations/2026_10_16_000001_add_column_auditoria_to_logs_table.php):
--   id, level, message, context (json), entidad, entidad_id, accion,
--   valores_antes (json), valores_despues (json), user_id, ip, timestamps,
--   indice (entidad, entidad_id, created_at) e indice (user_id, created_at).
--   Es la contraparte manual de
--   database/migrations/jp_limpieza/2026_10_16_000002_create_logs_table.php:
--   jp_limpieza NO se puede migrar con `migrate --database` (tiene 102
--   migraciones pendientes, su base se creo por dumps).
--
-- IMPACTO MEDIDO ANTES DE CORRER (sobre esta base)
--   filas afectadas ................ 0 (solo crea la tabla, no toca datos)
--   tablas creadas ................. 1 (`logs`, vacia)
--   tablas modificadas ............. 0
--   tablas borradas ................ 0
--
-- COMO CORRERLO
--   mysql -u <usuario> -p jp_limpieza < 2026_10_16_create_logs_jp_limpieza.sql
--   o dentro de una sesion:  source <ruta>/este_archivo.sql
--
--   Es idempotente: CREATE TABLE IF NOT EXISTS no falla si la tabla ya existe,
--   y no borra ni modifica filas si se corre dos veces.
--
-- NOTA TECNICA
--   Los json se declaran como JSON (MySQL 5.7+). Si la base corriera en una
--   version sin JSON nativo, cambiar a LONGTEXT: Eloquent los seguiria leyendo
--   como arreglos por los casts del modelo App\Models\JPLimpieza\Log.
-- ============================================================================

SET NAMES utf8mb4;

-- ---------------------------------------------------------------------------
-- Paso 1. La tabla. No modifica nada existente.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `level` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `context` JSON NULL,
    `entidad` VARCHAR(60) NULL,
    `entidad_id` BIGINT UNSIGNED NULL,
    `accion` VARCHAR(30) NULL,
    `valores_antes` JSON NULL,
    `valores_despues` JSON NULL,
    `user_id` BIGINT UNSIGNED NULL,
    `ip` VARCHAR(45) NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `logs_entidad_id_fecha_index` (`entidad`, `entidad_id`, `created_at`),
    KEY `logs_user_fecha_index` (`user_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Paso 2. Verificacion. Tiene que mostrar la tabla con 0 filas.
-- ---------------------------------------------------------------------------
SELECT COUNT(*) AS filas_logs_jp FROM `logs`;

-- ---------------------------------------------------------------------------
-- ROLLBACK. Si hay que revertir (la tabla esta vacia recien creada):
-- ---------------------------------------------------------------------------
-- DROP TABLE IF EXISTS `logs`;
