-- ============================================================================
-- Reparacion de numeros de adquisicion duplicados
-- Base: sicpro  (modulo principal de adquisiciones)
-- ============================================================================
--
-- QUE HACE
--   Lleva adquisiciones.numero al valor que la aplicacion garantiza desde el
--   arreglo del alta: fecha de registro (created_at) + '-' + id, con 3 digitos
--   minimos. Es el sufijo del propio registro, nunca el que mando el cliente.
--
--   Es una normalizacion COMPLETA, no solo de los grupos duplicados. Se midio
--   que renumerar unicamente los extras de los 36 grupos duplicados (36 filas)
--   crearia 7 numeros repetidos NUEVOS, porque hay filas unicas pero desviadas
--   que ocupan el valor derivado de otra fila:
--     20250717-845, 20260310-1724, 20260414-1865, 20260415-1886,
--     20260521-2108, 20260715-2371, 20260922-2689
--   Tras la normalizacion completa quedan 0 duplicados y 0 desviadas, porque
--   los derivados son distintos entre si (el id es unico y autoincremental).
--
-- IMPACTO MEDIDO ANTES DE CORRER (sobre esta base)
--   filas ........................ 2702
--   numeros duplicados ........... 36 grupos
--   filas con numero != derivado . 81    <-- las unicas que cambian
--   derivados distintos .......... 2702 (igual a filas totales)
--
-- COMO CORRERLO
--   mysql -u <usuario> -p sicpro < 2026_10_16_renumerar_adquisiciones_duplicadas_sicpro.sql
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
CREATE TABLE IF NOT EXISTS adquisiciones_numero_backup_20261016_sicpro AS
SELECT a.id, a.numero, a.created_at
FROM adquisiciones a
JOIN tmp_numero_derivado t ON t.id = a.id
WHERE t.cambia = 1;

SELECT 'filas respaldadas' AS que, COUNT(*) AS valor FROM adquisiciones_numero_backup_20261016_sicpro;

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
-- JOIN adquisiciones_numero_backup_20261016_sicpro b ON b.id = a.id
-- SET a.numero = b.numero;
--
-- Verificado el rollback, se puede borrar el respaldo:
-- DROP TABLE adquisiciones_numero_backup_20261016_sicpro;
