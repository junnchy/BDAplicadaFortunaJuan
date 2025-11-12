-- ============================================================================
-- import_all.sql
-- Ejecuta secuencialmente los scripts de carga en el orden requerido.
-- Uso (desde la raíz del proyecto):
--   sqlite3 writable/etl_dw_system.db ".read writable/sql_imports/import_all.sql"
-- ============================================================================

.echo ON
.bail ON
.timer ON

-- Desactivar FKs durante la carga
PRAGMA foreign_keys = OFF;

BEGIN TRANSACTION;

-- Orden solicitado
.read writable/sql_imports/dim_tiempo_2025.sql
.read writable/sql_imports/dim_cliente.sql
.read writable/sql_imports/dim_producto.sql
.read writable/sql_imports/fact_ventas.sql

COMMIT;

-- Reactivar FKs
PRAGMA foreign_keys = ON;

-- Chequeos rápidos (se muestran en consola)
.headers on
.mode column
.width 14 14 14 14
SELECT COUNT(*) AS dim_tiempo   FROM dim_tiempo;
SELECT COUNT(*) AS dim_cliente  FROM dim_cliente;
SELECT COUNT(*) AS dim_producto FROM dim_producto;
SELECT COUNT(*) AS fact_ventas  FROM fact_ventas;

-- Fin
