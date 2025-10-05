<?php

namespace App\Controllers\Api;

use App\Models\FactVentasModel;
use App\Models\DimTiempoModel;

/**
 * Controlador API para consultas de ventas
 * Endpoints para acceder a datos del data warehouse
 */
class VentasController extends BaseApiController
{
    protected FactVentasModel $factVentasModel;
    protected DimTiempoModel $dimTiempoModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->factVentasModel = new FactVentasModel();
        $this->dimTiempoModel = new DimTiempoModel();
    }

    /**
     * GET /api/ventas
     * Obtener datos de ventas con filtros y agregaciones
     */
    public function index()
    {
        try {
            // Verificar autenticación
            if (!$this->isAuthenticated()) {
                return $this->unauthorizedResponse();
            }

            // Procesar parámetros
            $params = $this->processQueryParams();
            
            // Obtener filtros específicos
            $dateFrom = service('request')->getGet('date_from');
            $dateTo = service('request')->getGet('date_to');
            $clienteId = service('request')->getGet('cliente_id');
            $productoId = service('request')->getGet('producto_id');
            $tipoVenta = service('request')->getGet('tipo_venta');
            $aggregation = service('request')->getGet('aggregation') ?? 'none';

            // Construir consulta base con la estructura correcta de Star Schema
            $builder = $this->factVentasModel->builder();
            
            // Joins con dimensiones para obtener información descriptiva
            $builder->select([
                'fact_ventas.venta_sk',
                'fact_ventas.orden_id',
                'fact_ventas.linea_numero',
                'fact_ventas.cantidad',
                'fact_ventas.precio_unitario',
                'fact_ventas.monto_linea',
                'fact_ventas.descuento_monto',
                'fact_ventas.margen_monto',
                'fact_ventas.margen_porcentaje',
                'fact_ventas.monto_neto',
                'fact_ventas.canal_venta',
                'fact_ventas.vendedor_id',
                'fact_ventas.created_at',
                'dt.fecha_natural',
                'dt.año',
                'dt.mes',
                'dt.trimestre',
                'dt.nombre_dia',
                'dt.es_fin_semana'
            ]);
            
            $builder->join('dim_tiempo dt', 'fact_ventas.tiempo_sk = dt.tiempo_sk', 'left');

            // Aplicar filtros usando la estructura de Star Schema
            if ($dateFrom) {
                $builder->where('dt.fecha_natural >=', $dateFrom);
            }
            
            if ($dateTo) {
                $builder->where('dt.fecha_natural <=', $dateTo);
            }
            
            if ($clienteId) {
                $builder->where('fact_ventas.cliente_sk', $clienteId);
            }
            
            if ($productoId) {
                $builder->where('fact_ventas.producto_sk', $productoId);
            }
            
            // Filtros adicionales disponibles
            $canalVenta = service('request')->getGet('canal_venta');
            if ($canalVenta) {
                $builder->where('fact_ventas.canal_venta', $canalVenta);
            }

            // Aplicar agregaciones si se solicitan
            if ($aggregation !== 'none') {
                return $this->getAggregatedData($aggregation, $params['filters']);
            }

            // Aplicar ordenamiento
            if (!empty($params['sort'])) {
                foreach ($params['sort'] as $field => $direction) {
                    $builder->orderBy($field, $direction);
                }
            } else {
                $builder->orderBy('dt.fecha_natural', 'DESC');
            }

            // Contar total de registros para paginación
            $totalQuery = clone $builder;
            $total = $totalQuery->countAllResults(false);

            // Aplicar paginación
            $offset = ($params['page'] - 1) * $params['limit'];
            $builder->limit($params['limit'], $offset);

            // Ejecutar consulta
            $ventas = $builder->get()->getResultArray();

            // Calcular totales para el conjunto filtrado
            $totales = $this->calculateTotals($params['filters']);

            // Preparar respuesta
            $response = [
                'data' => $ventas,
                'pagination' => [
                    'current_page' => $params['page'],
                    'per_page' => $params['limit'],
                    'total' => $total,
                    'total_pages' => ceil($total / $params['limit'])
                ],
                'totals' => $totales,
                'filters_applied' => $params['filters']
            ];

            return $this->successResponse($response);

        } catch (\Exception $e) {
            log_message('error', 'Error en VentasController::index: ' . $e->getMessage());
            return $this->errorResponse('Error al obtener datos de ventas', 500);
        }
    }

    /**
     * GET /api/ventas/dashboard
     * Datos agregados para dashboard principal
     */
    public function dashboard()
    {
        try {
            if (!$this->isAuthenticated()) {
                return $this->unauthorizedResponse();
            }

            // Obtener período (último mes por defecto)
            $dateFrom = service('request')->getGet('date_from') ?? date('Y-m-01');
            $dateTo = service('request')->getGet('date_to') ?? date('Y-m-d');

            $data = [
                'resumen_periodo' => $this->getResumenPeriodo($dateFrom, $dateTo),
                'ventas_por_dia' => $this->getVentasPorDia($dateFrom, $dateTo),
                'top_productos' => $this->getTopProductos($dateFrom, $dateTo),
                'top_clientes' => $this->getTopClientes($dateFrom, $dateTo),
                'ventas_por_canal' => $this->getVentasPorCanal($dateFrom, $dateTo),
                'rendimiento_vendedores' => $this->getRendimientoVendedores($dateFrom, $dateTo)
            ];

            return $this->successResponse($data);

        } catch (\Exception $e) {
            log_message('error', 'Error en VentasController::dashboard: ' . $e->getMessage());
            return $this->errorResponse('Error al generar dashboard', 500);
        }
    }

    /**
     * GET /api/ventas/drill-down
     * Implementa funcionalidad de drill-down/drill-up
     */
    public function drillDown()
    {
        try {
            if (!$this->isAuthenticated()) {
                return $this->unauthorizedResponse();
            }

            $level = service('request')->getGet('level') ?? 'year';
            $parentId = service('request')->getGet('parent_id');
            $dateFrom = service('request')->getGet('date_from');
            $dateTo = service('request')->getGet('date_to');

            $data = $this->getDrillDownData($level, $parentId, $dateFrom, $dateTo);

            return $this->successResponse([
                'level' => $level,
                'parent_id' => $parentId,
                'data' => $data,
                'drill_options' => $this->getDrillOptions($level)
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en VentasController::drillDown: ' . $e->getMessage());
            return $this->errorResponse('Error en drill-down', 500);
        }
    }

    /**
     * Obtener datos agregados según tipo de agregación
     */
    private function getAggregatedData(string $aggregation, array $filters): object
    {
        $builder = $this->factVentasModel->builder();
        
        switch ($aggregation) {
            case 'daily':
                $builder->select([
                    'DATE(fecha_venta) as fecha',
                    'COUNT(*) as num_ventas',
                    'SUM(cantidad) as total_cantidad',
                    'SUM(total_venta) as total_ventas',
                    'AVG(total_venta) as promedio_venta',
                    'SUM(margen_bruto) as total_margen'
                ]);
                $builder->groupBy('DATE(fecha_venta)');
                $builder->orderBy('fecha', 'DESC');
                break;

            case 'monthly':
                $builder->select([
                    'strftime("%Y-%m", fecha_venta) as periodo',
                    'COUNT(*) as num_ventas',
                    'SUM(cantidad) as total_cantidad',
                    'SUM(total_venta) as total_ventas',
                    'AVG(total_venta) as promedio_venta',
                    'SUM(margen_bruto) as total_margen'
                ]);
                $builder->groupBy('strftime("%Y-%m", fecha_venta)');
                $builder->orderBy('periodo', 'DESC');
                break;

            case 'by_product':
                $builder->select([
                    'producto_id',
                    'COUNT(*) as num_ventas',
                    'SUM(cantidad) as total_cantidad',
                    'SUM(total_venta) as total_ventas',
                    'AVG(precio_unitario) as precio_promedio',
                    'SUM(margen_bruto) as total_margen'
                ]);
                $builder->groupBy('producto_id');
                $builder->orderBy('total_ventas', 'DESC');
                break;

            case 'by_client':
                $builder->select([
                    'cliente_id',
                    'COUNT(*) as num_ventas',
                    'SUM(cantidad) as total_cantidad',
                    'SUM(total_venta) as total_ventas',
                    'AVG(total_venta) as ticket_promedio',
                    'SUM(margen_bruto) as total_margen'
                ]);
                $builder->groupBy('cliente_id');
                $builder->orderBy('total_ventas', 'DESC');
                break;
        }

        // Aplicar filtros comunes
        $this->applyCommonFilters($builder, $filters);

        $results = $builder->get()->getResultArray();

        return $this->successResponse([
            'aggregation_type' => $aggregation,
            'data' => $results,
            'summary' => $this->calculateAggregationSummary($results)
        ]);
    }

    /**
     * Calcular totales para el conjunto de datos
     */
    private function calculateTotals(array $filters): array
    {
        $builder = $this->factVentasModel->builder();
        $builder->select([
            'COUNT(*) as total_registros',
            'SUM(cantidad) as cantidad_total',
            'SUM(total_venta) as ventas_total',
            'AVG(total_venta) as venta_promedio',
            'SUM(margen_bruto) as margen_total',
            'AVG(margen_bruto) as margen_promedio'
        ]);

        $this->applyCommonFilters($builder, $filters);

        return $builder->get()->getRowArray() ?? [];
    }

    /**
     * Aplicar filtros comunes a las consultas
     */
    private function applyCommonFilters($builder, array $filters): void
    {
        foreach ($filters as $field => $value) {
            if (empty($value)) continue;

            switch ($field) {
                case 'date_from':
                    $builder->where('fecha_venta >=', $value);
                    break;
                case 'date_to':
                    $builder->where('fecha_venta <=', $value);
                    break;
                default:
                    if (strpos($field, '_id') !== false || in_array($field, ['tipo_venta', 'canal_venta'])) {
                        $builder->where($field, $value);
                    }
                    break;
            }
        }
    }

    /**
     * Obtener resumen del período
     */
    private function getResumenPeriodo(string $dateFrom, string $dateTo): array
    {
        $builder = $this->factVentasModel->builder();
        $builder->select([
            'COUNT(*) as total_ventas',
            'SUM(total_venta) as ingresos_total',
            'SUM(margen_bruto) as margen_total',
            'AVG(total_venta) as ticket_promedio',
            'COUNT(DISTINCT cliente_id) as clientes_unicos',
            'COUNT(DISTINCT producto_id) as productos_vendidos'
        ]);
        $builder->where('fecha_venta >=', $dateFrom);
        $builder->where('fecha_venta <=', $dateTo);

        return $builder->get()->getRowArray() ?? [];
    }

    /**
     * Obtener ventas por día
     */
    private function getVentasPorDia(string $dateFrom, string $dateTo): array
    {
        $builder = $this->factVentasModel->builder();
        $builder->select([
            'DATE(fecha_venta) as fecha',
            'SUM(total_venta) as total_dia',
            'COUNT(*) as num_ventas'
        ]);
        $builder->where('fecha_venta >=', $dateFrom);
        $builder->where('fecha_venta <=', $dateTo);
        $builder->groupBy('DATE(fecha_venta)');
        $builder->orderBy('fecha', 'ASC');

        return $builder->get()->getResultArray();
    }

    /**
     * Obtener top productos
     */
    private function getTopProductos(string $dateFrom, string $dateTo, int $limit = 10): array
    {
        $builder = $this->factVentasModel->builder();
        $builder->select([
            'producto_id',
            'SUM(total_venta) as total_ventas',
            'SUM(cantidad) as total_cantidad',
            'COUNT(*) as num_transacciones'
        ]);
        $builder->where('fecha_venta >=', $dateFrom);
        $builder->where('fecha_venta <=', $dateTo);
        $builder->groupBy('producto_id');
        $builder->orderBy('total_ventas', 'DESC');
        $builder->limit($limit);

        return $builder->get()->getResultArray();
    }

    /**
     * Obtener top clientes
     */
    private function getTopClientes(string $dateFrom, string $dateTo, int $limit = 10): array
    {
        $builder = $this->factVentasModel->builder();
        $builder->select([
            'cliente_id',
            'SUM(total_venta) as total_compras',
            'COUNT(*) as num_compras',
            'AVG(total_venta) as ticket_promedio'
        ]);
        $builder->where('fecha_venta >=', $dateFrom);
        $builder->where('fecha_venta <=', $dateTo);
        $builder->groupBy('cliente_id');
        $builder->orderBy('total_compras', 'DESC');
        $builder->limit($limit);

        return $builder->get()->getResultArray();
    }

    /**
     * Obtener ventas por canal
     */
    private function getVentasPorCanal(string $dateFrom, string $dateTo): array
    {
        $builder = $this->factVentasModel->builder();
        $builder->select([
            'canal_venta',
            'SUM(total_venta) as total_ventas',
            'COUNT(*) as num_ventas',
            'AVG(total_venta) as ticket_promedio'
        ]);
        $builder->where('fecha_venta >=', $dateFrom);
        $builder->where('fecha_venta <=', $dateTo);
        $builder->groupBy('canal_venta');
        $builder->orderBy('total_ventas', 'DESC');

        return $builder->get()->getResultArray();
    }

    /**
     * Obtener rendimiento de vendedores
     */
    private function getRendimientoVendedores(string $dateFrom, string $dateTo): array
    {
        $builder = $this->factVentasModel->builder();
        $builder->select([
            'vendedor_id',
            'SUM(total_venta) as total_ventas',
            'COUNT(*) as num_ventas',
            'AVG(total_venta) as ticket_promedio',
            'SUM(margen_bruto) as margen_generado'
        ]);
        $builder->where('fecha_venta >=', $dateFrom);
        $builder->where('fecha_venta <=', $dateTo);
        $builder->groupBy('vendedor_id');
        $builder->orderBy('total_ventas', 'DESC');

        return $builder->get()->getResultArray();
    }

    /**
     * Obtener datos de drill-down según nivel
     */
    private function getDrillDownData(string $level, ?string $parentId, ?string $dateFrom, ?string $dateTo): array
    {
        $builder = $this->factVentasModel->builder();
        
        switch ($level) {
            case 'year':
                $builder->select([
                    'strftime("%Y", fecha_venta) as anio',
                    'SUM(total_venta) as total_ventas',
                    'COUNT(*) as num_ventas'
                ]);
                $builder->groupBy('strftime("%Y", fecha_venta)');
                break;

            case 'quarter':
                if ($parentId) { // año específico
                    $builder->where('strftime("%Y", fecha_venta)', $parentId);
                }
                $builder->select([
                    'strftime("%Y", fecha_venta) as anio',
                    'CASE 
                        WHEN strftime("%m", fecha_venta) IN ("01","02","03") THEN "Q1"
                        WHEN strftime("%m", fecha_venta) IN ("04","05","06") THEN "Q2"
                        WHEN strftime("%m", fecha_venta) IN ("07","08","09") THEN "Q3"
                        ELSE "Q4"
                    END as trimestre',
                    'SUM(total_venta) as total_ventas',
                    'COUNT(*) as num_ventas'
                ]);
                $builder->groupBy('anio, trimestre');
                break;

            case 'month':
                if ($parentId) { // trimestre específico
                    // Lógica para filtrar por trimestre
                }
                $builder->select([
                    'strftime("%Y-%m", fecha_venta) as mes',
                    'SUM(total_venta) as total_ventas',
                    'COUNT(*) as num_ventas'
                ]);
                $builder->groupBy('strftime("%Y-%m", fecha_venta)');
                break;
        }

        if ($dateFrom) $builder->where('fecha_venta >=', $dateFrom);
        if ($dateTo) $builder->where('fecha_venta <=', $dateTo);

        $builder->orderBy('total_ventas', 'DESC');

        return $builder->get()->getResultArray();
    }

    /**
     * Obtener opciones de drill disponibles según nivel actual
     */
    private function getDrillOptions(string $currentLevel): array
    {
        $options = [
            'year' => ['quarter', 'month'],
            'quarter' => ['month'],
            'month' => []
        ];

        return [
            'can_drill_down' => !empty($options[$currentLevel]),
            'next_levels' => $options[$currentLevel] ?? [],
            'can_drill_up' => $currentLevel !== 'year'
        ];
    }

    /**
     * Calcular resumen de agregación
     */
    private function calculateAggregationSummary(array $results): array
    {
        if (empty($results)) return [];

        $totalVentas = array_sum(array_column($results, 'total_ventas'));
        $totalRegistros = array_sum(array_column($results, 'num_ventas'));

        return [
            'total_records' => count($results),
            'total_sales' => $totalVentas,
            'total_transactions' => $totalRegistros,
            'average_per_period' => $totalRegistros > 0 ? $totalVentas / count($results) : 0
        ];
    }
}