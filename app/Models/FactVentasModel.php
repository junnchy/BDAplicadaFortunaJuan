<?php

namespace App\Models;

use CodeIgniter\Model;

class FactVentasModel extends Model
{
    protected $table            = 'fact_ventas';
    protected $primaryKey       = 'venta_sk';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'tiempo_sk',
        'producto_sk',
        'cliente_sk',
        'orden_id',
        'linea_numero',
        'cantidad',
        'precio_unitario',
        'monto_linea',
        'descuento_monto',
        'descuento_porcentaje',
        'impuesto_monto',
        'costo_unitario',
        'costo_total',
        'margen_monto',
        'margen_porcentaje',
        'monto_neto',
        'moneda',
        'tipo_cambio',
        'canal_venta',
        'vendedor_id',
        'promocion_id',
        'facturado',
        'fecha_factura',
        'numero_factura',
        'etl_run_id'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'venta_sk'             => 'integer',
        'tiempo_sk'            => 'integer',
        'producto_sk'          => 'integer',
        'cliente_sk'           => 'integer',
        'linea_numero'         => 'integer',
        'cantidad'             => 'float',
        'precio_unitario'      => 'float',
        'monto_linea'          => 'float',
        'descuento_monto'      => 'float',
        'descuento_porcentaje' => 'float',
        'impuesto_monto'       => 'float',
        'costo_unitario'       => 'float',
        'costo_total'          => 'float',
        'margen_monto'         => 'float',
        'margen_porcentaje'    => 'float',
        'monto_neto'           => 'float',
        'tipo_cambio'          => 'float',
        'facturado'            => 'boolean',
        'etl_run_id'           => 'integer',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = null;

    /**
     * Obtiene ventas con filtros y agregaciones
     */
    public function getVentasConFiltros(array $filtros = [], array $agregaciones = [], string $nivelTiempo = 'dia'): array
    {
        $query = $this->db->table($this->table . ' fv')
            ->join('dim_tiempo dt', 'fv.tiempo_sk = dt.tiempo_sk')
            ->join('dim_producto dp', 'fv.producto_sk = dp.producto_sk')
            ->join('dim_cliente dc', 'fv.cliente_sk = dc.cliente_sk');

        // Seleccionar campos base según nivel de tiempo
        switch (strtolower($nivelTiempo)) {
            case 'año':
                $timeFields = 'dt.año';
                $groupBy = 'dt.año';
                break;
            case 'trimestre':
                $timeFields = 'dt.año, dt.trimestre, dt.trimestre_nombre';
                $groupBy = 'dt.año, dt.trimestre, dt.trimestre_nombre';
                break;
            case 'mes':
                $timeFields = 'dt.año, dt.mes, dt.nombre_mes';
                $groupBy = 'dt.año, dt.mes, dt.nombre_mes';
                break;
            case 'semana':
                $timeFields = 'dt.año, dt.semana_año, dt.fecha_primera_semana';
                $groupBy = 'dt.año, dt.semana_año, dt.fecha_primera_semana';
                break;
            default: // dia
                $timeFields = 'dt.fecha_natural';
                $groupBy = 'dt.fecha_natural';
        }

        // Construir SELECT con agregaciones
        $selectFields = [$timeFields];
        
        foreach ($agregaciones as $metrica) {
            switch ($metrica) {
                case 'total_ventas':
                    $selectFields[] = 'SUM(fv.monto_neto) as total_ventas';
                    break;
                case 'total_cantidad':
                    $selectFields[] = 'SUM(fv.cantidad) as total_cantidad';
                    break;
                case 'total_ordenes':
                    $selectFields[] = 'COUNT(DISTINCT fv.orden_id) as total_ordenes';
                    break;
                case 'total_margen':
                    $selectFields[] = 'SUM(fv.margen_monto) as total_margen';
                    break;
                case 'margen_promedio':
                    $selectFields[] = 'AVG(fv.margen_porcentaje) as margen_promedio';
                    break;
                case 'ticket_promedio':
                    $selectFields[] = 'AVG(fv.monto_neto) as ticket_promedio';
                    break;
                case 'clientes_unicos':
                    $selectFields[] = 'COUNT(DISTINCT fv.cliente_sk) as clientes_unicos';
                    break;
                case 'productos_unicos':
                    $selectFields[] = 'COUNT(DISTINCT fv.producto_sk) as productos_unicos';
                    break;
            }
        }

        $query->select(implode(', ', $selectFields));

        // Aplicar filtros
        $this->aplicarFiltros($query, $filtros);

        // Group by
        $query->groupBy($groupBy);

        // Ordenar por tiempo
        $orderField = strpos($groupBy, 'fecha_natural') !== false ? 'dt.fecha_natural' : 'dt.año, dt.mes';
        $query->orderBy($orderField, 'ASC');

        return $query->get()->getResultArray();
    }

    /**
     * Obtiene datos para drill-down en jerarquías
     */
    public function getDrillDownData(string $dimension, array $filtros = [], array $metricas = []): array
    {
        $query = $this->db->table($this->table . ' fv')
            ->join('dim_tiempo dt', 'fv.tiempo_sk = dt.tiempo_sk')
            ->join('dim_producto dp', 'fv.producto_sk = dp.producto_sk')
            ->join('dim_cliente dc', 'fv.cliente_sk = dc.cliente_sk');

        // Configurar campos según dimensión
        switch (strtolower($dimension)) {
            case 'producto':
                $selectFields = ['dp.familia_nombre', 'dp.producto_nombre', 'dp.categoria'];
                $groupBy = 'dp.familia_nombre, dp.producto_nombre, dp.categoria';
                break;
            case 'familia':
                $selectFields = ['dp.familia_nombre', 'dp.categoria'];
                $groupBy = 'dp.familia_nombre, dp.categoria';
                break;
            case 'cliente':
                $selectFields = ['dc.segmento', 'dc.cliente_nombre', 'dc.pais'];
                $groupBy = 'dc.segmento, dc.cliente_nombre, dc.pais';
                break;
            case 'region':
                $selectFields = ['dc.region', 'dc.pais'];
                $groupBy = 'dc.region, dc.pais';
                break;
            default:
                return [];
        }

        // Agregar métricas
        foreach ($metricas as $metrica) {
            switch ($metrica) {
                case 'ventas':
                    $selectFields[] = 'SUM(fv.monto_neto) as total_ventas';
                    break;
                case 'cantidad':
                    $selectFields[] = 'SUM(fv.cantidad) as total_cantidad';
                    break;
                case 'margen':
                    $selectFields[] = 'SUM(fv.margen_monto) as total_margen';
                    break;
                case 'ordenes':
                    $selectFields[] = 'COUNT(DISTINCT fv.orden_id) as total_ordenes';
                    break;
            }
        }

        $query->select(implode(', ', $selectFields));

        // Aplicar filtros
        $this->aplicarFiltros($query, $filtros);

        $query->groupBy($groupBy);
        $query->orderBy('SUM(fv.monto_neto)', 'DESC');

        return $query->get()->getResultArray();
    }

    /**
     * Obtiene comparaciones Year over Year o Month over Month
     */
    public function getComparacionTemporal(array $filtros = [], string $tipoComparacion = 'yoy'): array
    {
        // Implementar lógica de comparación temporal
        $fechaActual = $filtros['fecha_fin'] ?? date('Y-m-d');
        
        if ($tipoComparacion === 'yoy') {
            $fechaAnterior = date('Y-m-d', strtotime($fechaActual . ' -1 year'));
        } else {
            $fechaAnterior = date('Y-m-d', strtotime($fechaActual . ' -1 month'));
        }

        // Obtener datos del período actual
        $filtrosActual = $filtros;
        $datosActual = $this->getVentasConFiltros($filtrosActual, ['total_ventas', 'total_cantidad', 'total_margen']);

        // Obtener datos del período anterior
        $filtrosAnterior = $filtros;
        if (isset($filtrosAnterior['fecha_inicio'])) {
            $filtrosAnterior['fecha_inicio'] = date('Y-m-d', strtotime($filtrosAnterior['fecha_inicio'] . ' -1 year'));
        }
        if (isset($filtrosAnterior['fecha_fin'])) {
            $filtrosAnterior['fecha_fin'] = $fechaAnterior;
        }
        
        $datosAnterior = $this->getVentasConFiltros($filtrosAnterior, ['total_ventas', 'total_cantidad', 'total_margen']);

        return [
            'periodo_actual' => $datosActual,
            'periodo_anterior' => $datosAnterior,
            'tipo_comparacion' => $tipoComparacion
        ];
    }

    /**
     * Obtiene KPIs principales del dashboard
     */
    public function getKPIsPrincipales(array $filtros = []): array
    {
        $query = $this->db->table($this->table . ' fv')
            ->join('dim_tiempo dt', 'fv.tiempo_sk = dt.tiempo_sk')
            ->join('dim_producto dp', 'fv.producto_sk = dp.producto_sk')
            ->join('dim_cliente dc', 'fv.cliente_sk = dc.cliente_sk');

        $query->select('
            SUM(fv.monto_neto) as total_ventas,
            SUM(fv.cantidad) as total_cantidad,
            SUM(fv.margen_monto) as total_margen,
            AVG(fv.margen_porcentaje) as margen_promedio,
            COUNT(DISTINCT fv.orden_id) as total_ordenes,
            COUNT(DISTINCT fv.cliente_sk) as clientes_unicos,
            COUNT(DISTINCT fv.producto_sk) as productos_vendidos,
            AVG(fv.monto_neto) as ticket_promedio
        ');

        $this->aplicarFiltros($query, $filtros);

        return $query->get()->getRowArray() ?? [];
    }

    /**
     * Aplica filtros a la query
     */
    private function aplicarFiltros($query, array $filtros): void
    {
        if (!empty($filtros['fecha_inicio'])) {
            $query->where('dt.fecha_natural >=', $filtros['fecha_inicio']);
        }

        if (!empty($filtros['fecha_fin'])) {
            $query->where('dt.fecha_natural <=', $filtros['fecha_fin']);
        }

        if (!empty($filtros['producto_id'])) {
            $query->where('dp.producto_id', $filtros['producto_id']);
        }

        if (!empty($filtros['familia_id'])) {
            $query->where('dp.familia_id', $filtros['familia_id']);
        }

        if (!empty($filtros['cliente_id'])) {
            $query->where('dc.cliente_id', $filtros['cliente_id']);
        }

        if (!empty($filtros['segmento'])) {
            $query->where('dc.segmento', $filtros['segmento']);
        }

        if (!empty($filtros['pais'])) {
            $query->where('dc.pais', $filtros['pais']);
        }

        if (!empty($filtros['region'])) {
            $query->where('dc.region', $filtros['region']);
        }

        if (!empty($filtros['canal_venta'])) {
            $query->where('fv.canal_venta', $filtros['canal_venta']);
        }

        // Filtros adicionales
        if (isset($filtros['solo_facturados']) && $filtros['solo_facturados']) {
            $query->where('fv.facturado', true);
        }
    }
}