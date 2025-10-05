<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAggregationViews extends Migration
{
    public function up()
    {
        // Vista de ventas por día (simplificada para SQLite)
        $this->db->query("
            CREATE VIEW vw_ventas_diarias AS
            SELECT 
                dt.fecha_natural,
                dt.año,
                dt.mes,
                dt.trimestre,
                dt.nombre_mes,
                COUNT(DISTINCT fv.orden_id) as total_ordenes,
                COUNT(fv.venta_sk) as total_lineas,
                SUM(fv.cantidad) as total_cantidad,
                SUM(fv.monto_neto) as total_ventas,
                SUM(fv.costo_total) as total_costos,
                SUM(fv.margen_monto) as total_margen,
                AVG(fv.margen_porcentaje) as margen_promedio,
                COUNT(DISTINCT fv.cliente_sk) as clientes_unicos,
                COUNT(DISTINCT fv.producto_sk) as productos_unicos
            FROM fact_ventas fv
            INNER JOIN dim_tiempo dt ON fv.tiempo_sk = dt.tiempo_sk
            GROUP BY 
                dt.tiempo_sk,
                dt.fecha_natural,
                dt.año,
                dt.mes,
                dt.trimestre,
                dt.nombre_mes
        ");

        // Vista de ventas por mes
        $this->db->query("
            CREATE VIEW vw_ventas_mensuales AS
            SELECT 
                dt.año,
                dt.mes,
                dt.trimestre,
                dt.nombre_mes,
                COUNT(DISTINCT fv.orden_id) as total_ordenes,
                COUNT(fv.venta_sk) as total_lineas,
                SUM(fv.cantidad) as total_cantidad,
                SUM(fv.monto_neto) as total_ventas,
                SUM(fv.costo_total) as total_costos,
                SUM(fv.margen_monto) as total_margen,
                AVG(fv.margen_porcentaje) as margen_promedio,
                COUNT(DISTINCT fv.cliente_sk) as clientes_unicos,
                COUNT(DISTINCT fv.producto_sk) as productos_unicos
            FROM fact_ventas fv
            INNER JOIN dim_tiempo dt ON fv.tiempo_sk = dt.tiempo_sk
            GROUP BY 
                dt.año,
                dt.mes,
                dt.trimestre,
                dt.nombre_mes
        ");

        // Vista de ventas por producto
        $this->db->query("
            CREATE VIEW vw_ventas_productos AS
            SELECT 
                dp.producto_id,
                dp.producto_nombre,
                dp.familia_id,
                dp.familia_nombre,
                dp.categoria,
                COUNT(DISTINCT fv.orden_id) as total_ordenes,
                COUNT(fv.venta_sk) as total_lineas,
                SUM(fv.cantidad) as total_cantidad,
                SUM(fv.monto_neto) as total_ventas,
                SUM(fv.costo_total) as total_costos,
                SUM(fv.margen_monto) as total_margen,
                AVG(fv.margen_porcentaje) as margen_promedio,
                COUNT(DISTINCT fv.cliente_sk) as clientes_unicos,
                MIN(dt.fecha_natural) as primera_venta,
                MAX(dt.fecha_natural) as ultima_venta
            FROM fact_ventas fv
            INNER JOIN dim_producto dp ON fv.producto_sk = dp.producto_sk
            INNER JOIN dim_tiempo dt ON fv.tiempo_sk = dt.tiempo_sk
            WHERE dp.es_actual = 1
            GROUP BY 
                dp.producto_sk,
                dp.producto_id,
                dp.producto_nombre,
                dp.familia_id,
                dp.familia_nombre,
                dp.categoria
        ");
    }

    public function down()
    {
        $this->db->query("DROP VIEW IF EXISTS vw_ventas_productos");
        $this->db->query("DROP VIEW IF EXISTS vw_ventas_mensuales");
        $this->db->query("DROP VIEW IF EXISTS vw_ventas_diarias");
    }
}