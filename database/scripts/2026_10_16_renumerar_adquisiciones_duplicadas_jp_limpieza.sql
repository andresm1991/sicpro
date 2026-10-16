-- ============================================================================
-- Reparacion de numeros de adquisicion duplicados
-- Base: jp_limpieza  (modulo JP Limpieza)
-- ============================================================================
--
-- QUE HACE
--   Lleva adquisiciones.numero al valor que la aplicacion garantiza desde el
--   arreglo del alta: fecha de registro (created_at) + '-' + id, con 3 digitos
--   minimos. Es el sufijo del propio registro, nunca el que mando el cliente.
--
--   Es una normalizacion COMPLETA, no solo de los grupos duplicados, para dejar
--   el invariante "numero == derivado" en toda la tabla. Ese es el unico estado
--   estable: los derivados son distintos entre si (el id es unico y
--   autoincremental), asi que no puede quedar ningun duplicado. Dejar filas
--   desviadas afuera volveria a chocar en la proxima reparacion, porque una fila
--   desviada ocupa el valor derivado de otra.
--
-- IMPACTO MEDIDO ANTES DE CORRER (sobre esta base)
--   filas ........................ 635
--   numeros duplicados ........... 28 grupos (el peor, 20250828-002, con 41 filas)
--   filas dentro de esos grupos .. 215
--   filas con numero != derivado . 228   <-- las unicas que cambian
--   derivados distintos .......... 635 (igual a filas totales)
--   Todos los duplicados son de julio a septiembre de 2025.
--
-- COMO CORRERLO
--   mysql -u <usuario> -p jp_limpieza < 2026_10_16_renumerar_adquisiciones_duplicadas_jp_limpieza.sql
--   o dentro de una sesion:  source <ruta>/este_archivo.sql
--
--   Es idempotente: si no hay nada que corregir, el UPDATE afecta 0 filas.
--   No borra ni crea filas de adquisiciones: solo cambia numero, y deja un
--   respaldo en una tabla permanente antes de tocar nada.
--
-- NOTA TECNICA
--   Las comparaciones de numero usan CAST(... AS BINARY). Sin eso, el cliente
--   mysql puede negociar otro charset (cp850 en Windows) y la comparacion entre
--   la collation de la tabla y la de la conexion falla con
--   "Illegal mix of collations". Con BINARY la comparacion es byte a byte y no
--   depende del cliente. Se probo en ensayo: sin BINARY el script aborta.
--
-- ADVERTENCIA DE NEGOCIO
--   Los numeros que cambian pueden estar impresos en PDFs ya entregados. El PDF
--   se genera al vuelo: una reimpresion mostraria el numero nuevo. Revisar eso
--   antes de correr esto en produccion.
-- ============================================================================

SET NAMES utf8mb4;

-- ---------------------------------------------------------------------------
-- Paso 0. Valor derivado y condicion de cambio, calculados UNA sola vez para
-- que el respaldo, el UPDATE y la verificacion usen la misma formula.
-- ---------------------------------------------------------------------------
DROP TEMPORARY TABLE IF EXISTS tmp_numero_derivado;
CREATE TEMPORARY TABLE tmp_numero_derivado AS
SELECT
    base.id,
    base.numero_actual,
    base.numero_nuevo,
    (CAST(base.numero_actual AS BINARY) <> CAST(base.numero_nuevo AS BINARY)) AS cambia
FROM (
    SELECT
        a.id,
        a.numero AS numero_actual,
        CONCAT(
            DATE_FORMAT(a.created_at, '%Y%m%d'),
            '-',
            LPAD(CAST(a.id AS CHAR), GREATEST(3, CHAR_LENGTH(CAST(a.id AS CHAR))), '0')
        ) AS numero_nuevo
    FROM adquisiciones a
) base;
ALTER TABLE tmp_numero_derivado ADD PRIMARY KEY (id);

-- ---------------------------------------------------------------------------
-- Paso 1. Informe previo. No modifica nada.
-- ---------------------------------------------------------------------------
SELECT 'filas' AS que, COUNT(*) AS valor FROM adquisiciones
UNION ALL
SELECT 'numeros duplicados', COUNT(*) FROM (
    SELECT numero FROM adquisiciones GROUP BY numero HAVING COUNT(*) > 1
) d
UNION ALL
SELECT 'filas a renumerar', COUNT(*) FROM tmp_numero_derivado WHERE cambia = 1;

-- ---------------------------------------------------------------------------
-- Paso 2. Respaldo de las filas que cambian. Tabla permanente: es el rollback.
-- IF NOT EXISTS a proposito: si el script se corre dos veces, la segunda no
-- tiene nada que respaldar y NO debe pisar el respaldo de la primera, porque
-- eso destruiria el punto de rollback.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS adquisiciones_numero_backup_20261016_jp_limpieza AS
SELECT a.id, a.numero, a.created_at
FROM adquisiciones a
JOIN tmp_numero_derivado t ON t.id = a.id
WHERE t.cambia = 1;

SELECT 'filas respaldadas' AS que, COUNT(*) AS valor FROM adquisiciones_numero_backup_20261016_jp_limpieza;

-- ---------------------------------------------------------------------------
-- Paso 3. La reparacion.
-- ---------------------------------------------------------------------------
START TRANSACTION;

UPDATE adquisiciones a
JOIN tmp_numero_derivado t ON t.id = a.id
SET a.numero = t.numero_nuevo
WHERE t.cambia = 1;

COMMIT;

-- ---------------------------------------------------------------------------
-- Paso 4. Verificacion. Las dos filas tienen que decir OK.
-- ---------------------------------------------------------------------------
SELECT
    'duplicados' AS control,
    IF(COUNT(*) = 0, 'OK: sin numeros duplicados', 'ERROR: quedan duplicados, correr el rollback') AS resultado
FROM (SELECT numero FROM adquisiciones GROUP BY numero HAVING COUNT(*) > 1) d;

SELECT
    'desviadas' AS control,
    IF(COUNT(*) = 0, 'OK: toda fila tiene su numero derivado', 'ERROR: quedan filas desviadas') AS resultado
FROM adquisiciones a
JOIN tmp_numero_derivado t ON t.id = a.id
WHERE CAST(a.numero AS BINARY) <> CAST(t.numero_nuevo AS BINARY);

-- ---------------------------------------------------------------------------
-- Paso 5 (opcional, recomendado). El indice unico: convierte cualquier
-- duplicado futuro en un error ruidoso en vez de un duplicado silencioso.
-- Recien ahora se puede crear, porque ya no hay duplicados.
-- ---------------------------------------------------------------------------
-- ALTER TABLE adquisiciones ADD UNIQUE KEY adquisiciones_numero_unique (numero);

-- ---------------------------------------------------------------------------
-- ROLLBACK. Si la verificacion del paso 4 da ERROR:
-- ---------------------------------------------------------------------------
-- UPDATE adquisiciones a
-- JOIN adquisiciones_numero_backup_20261016_jp_limpieza b ON b.id = a.id
-- SET a.numero = b.numero;
--
-- Verificado el rollback, se puede borrar el respaldo:
-- DROP TABLE adquisiciones_numero_backup_20261016_jp_limpieza;
