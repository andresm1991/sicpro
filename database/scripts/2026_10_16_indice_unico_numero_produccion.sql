-- ============================================================================
-- Indice unico de adquisiciones.numero — PARA PRODUCCION
-- ============================================================================
--
-- ORDEN OBLIGATORIO
--
--   1. Primero reparar los duplicados (si no, el ALTER de abajo falla):
--        mysql -u <usuario> -p sicpro      < 2026_10_16_renumerar_adquisiciones_duplicadas_sicpro.sql
--        mysql -u <usuario> -p jp_limpieza < 2026_10_16_renumerar_adquisiciones_duplicadas_jp_limpieza.sql
--
--   2. Recien despues este archivo, en CADA base por separado:
--        mysql -u <usuario> -p sicpro      < 2026_10_16_indice_unico_numero_produccion.sql
--        mysql -u <usuario> -p jp_limpieza < 2026_10_16_indice_unico_numero_produccion.sql
--
--   Si en cambio se despliega el codigo con las migraciones ANTES de reparar, las
--   migraciones fallan a proposito con este mensaje:
--     "No se puede crear el indice unico de adquisiciones.numero: quedan N numeros
--      duplicados. Corre primero ... y despues volve a migrar."
--   Eso es preferible al "Duplicate entry" crudo de MySQL, pero igual hay que
--   reparar antes: el deploy queda a mitad de camino si la migracion falla.
--
-- POR QUE
--   Sin indice unico, un duplicado futuro vuelve a entrar en silencio, que es
--   exactamente lo que paso dos veces: 36 grupos en sicpro y 28 en jp_limpieza,
--   el peor con 41 filas con el mismo numero. El codigo ya deriva el numero del
--   propio registro, y este indice es la barrera que avisa si algun dia se rompe.
--
-- NOTA DEL DEPLOY
--   Las migraciones del repositorio hacen exactamente esto:
--     database/migrations/2026_10_16_000001_add_unique_numero_to_adquisiciones.php
--     database/migrations/jp_limpieza/2026_10_16_000001_add_unique_numero_to_adquisiciones.php
--   La de JP no la corre `php artisan migrate`: hay que ejecutarla aparte con
--     php artisan migrate --path=database/migrations/jp_limpieza --database=mysql_jp_limpieza
--   Si se usan las migraciones, este archivo NO hace falta (hacen lo mismo).
--
-- NOTA TECNICA
--   numero es un varchar; el indice entra sin problema en MySQL 5.7 con el formato
--   de fila por defecto (DYNAMIC y innodb_large_prefix). Se probo sobre las tres
--   bases: sicpro, jp_limpieza y jp_limpieza_testing.
-- ============================================================================

-- ---------------------------------------------------------------------------
-- Paso 1. Control previo. Tiene que dar 0. Si no da 0, NO correr el ALTER:
-- todavia hay duplicados y el ALTER va a fallar.
-- ---------------------------------------------------------------------------
SELECT
    'duplicados' AS control,
    IF(COUNT(*) = 0, 'OK: se puede crear el indice', 'ERROR: quedan duplicados, reparar primero') AS resultado
FROM (SELECT numero FROM adquisiciones GROUP BY numero HAVING COUNT(*) > 1) d;

-- ---------------------------------------------------------------------------
-- Paso 2. El indice.
--   En MySQL no existe CREATE UNIQUE INDEX IF NOT EXISTS. Si el indice ya estaba,
--   esto devuelve el error 1061 "Duplicate key name" y no cambia nada: se puede
--   ignorar con seguridad y seguir.
-- ---------------------------------------------------------------------------
ALTER TABLE adquisiciones ADD UNIQUE KEY adquisiciones_numero_unique (numero);

-- ---------------------------------------------------------------------------
-- Paso 3. Verificacion. Tiene que decir OK.
-- ---------------------------------------------------------------------------
SELECT
    'indice' AS control,
    IF(COUNT(*) = 1, 'OK: indice unico presente', 'ERROR: el indice no se creo') AS resultado
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'adquisiciones'
  AND INDEX_NAME = 'adquisiciones_numero_unique';

-- ---------------------------------------------------------------------------
-- ROLLBACK. Si hace falta sacarlo:
-- ---------------------------------------------------------------------------
-- ALTER TABLE adquisiciones DROP INDEX adquisiciones_numero_unique;
