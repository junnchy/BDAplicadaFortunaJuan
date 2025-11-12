-- ============================================================================
-- backup_and_clear.sql
-- Crea un backup completo de la base SQLite y limpia tablas de datos (DW)
-- Uso (desde raíz del proyecto):
--   sqlite3 writable/etl_dw_system.db ".read writable/sql_imports/backup_and_clear.sql"
-- Requisitos: SQLite >= 3.27 (para VACUUM INTO)
-- El backup se guarda en writable/ con timestamp en el nombre
-- ============================================================================

-- 1) Generar backup con timestamp (YYYYMMDDHHMMSS)
-- Nota: La ruta es relativa al directorio actual. Ejecuta el comando desde la raíz del repo.
VACUUM INTO ('writable/etl_dw_system_backup_' || strftime('%Y%m%d%H%M%S','now') || '.db');

-- 2) Desactivar claves foráneas para limpieza segura
PRAGMA foreign_keys = OFF;

BEGIN TRANSACTION;

-- 3) Borrar primero los hechos
DELETE FROM fact_ventas;

-- 4) Borrar dimensiones (orden sugerido)
DELETE FROM dim_cliente;
DELETE FROM dim_producto;
DELETE FROM dim_tiempo;

COMMIT;

-- 5) Reactivar claves foráneas
PRAGMA foreign_keys = ON;

-- Opcional: mostrar conteos post-limpieza (útil si se ejecuta desde CLI)
SELECT 'Post-limpieza' AS etapa,
       (SELECT COUNT(*) FROM dim_tiempo)   AS dim_tiempo,
       (SELECT COUNT(*) FROM dim_producto) AS dim_producto,
       (SELECT COUNT(*) FROM dim_cliente)  AS dim_cliente,
       (SELECT COUNT(*) FROM fact_ventas)  AS fact_ventas;
