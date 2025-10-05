<?php

namespace App\Controllers\Api;

/**
 * Controlador API para consultas de dimensiones
 * Proporciona acceso a las tablas de dimensión del data warehouse
 */
class DimensionesController extends BaseApiController
{
    /**
     * GET /api/dimensiones/tiempo
     * Obtener datos de la dimensión tiempo
     */
    public function tiempo()
    {
        try {
            if (!$this->isAuthenticated()) {
                return $this->unauthorizedResponse();
            }

            $params = $this->processQueryParams();
            
            // Filtros específicos
            $dateFrom = service('request')->getGet('date_from');
            $dateTo = service('request')->getGet('date_to');
            $anio = service('request')->getGet('anio');
            $mes = service('request')->getGet('mes');
            $trimestre = service('request')->getGet('trimestre');

            $db = \Config\Database::connect();
            $builder = $db->table('dim_tiempo');

            // Aplicar filtros
            if ($dateFrom) {
                $builder->where('fecha >=', $dateFrom);
            }
            if ($dateTo) {
                $builder->where('fecha <=', $dateTo);
            }
            if ($anio) {
                $builder->where('anio', $anio);
            }
            if ($mes) {
                $builder->where('mes', $mes);
            }
            if ($trimestre) {
                $builder->where('trimestre', $trimestre);
            }

            // Contar total
            $totalQuery = clone $builder;
            $total = $totalQuery->countAllResults(false);

            // Aplicar paginación
            $offset = ($params['page'] - 1) * $params['limit'];
            $builder->limit($params['limit'], $offset);

            // Ordenamiento
            $builder->orderBy('fecha', 'DESC');

            $results = $builder->get()->getResultArray();

            return $this->successResponse([
                'data' => $results,
                'pagination' => [
                    'current_page' => $params['page'],
                    'per_page' => $params['limit'],
                    'total' => $total,
                    'total_pages' => ceil($total / $params['limit'])
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en DimensionesController::tiempo: ' . $e->getMessage());
            return $this->errorResponse('Error al obtener dimensión tiempo', 500);
        }
    }

    /**
     * GET /api/dimensiones/clientes
     * Obtener información de clientes únicos
     */
    public function clientes()
    {
        try {
            if (!$this->isAuthenticated()) {
                return $this->unauthorizedResponse();
            }

            $params = $this->processQueryParams();
            
            $db = \Config\Database::connect();
            
            // Obtener clientes únicos de las ventas con estadísticas
            $builder = $db->table('fact_ventas fv');
            $builder->select([
                'fv.cliente_id',
                'COUNT(*) as total_compras',
                'SUM(fv.total_venta) as total_gastado',
                'AVG(fv.total_venta) as ticket_promedio',
                'MIN(fv.fecha_venta) as primera_compra',
                'MAX(fv.fecha_venta) as ultima_compra',
                'COUNT(DISTINCT fv.producto_id) as productos_diferentes'
            ]);
            $builder->groupBy('fv.cliente_id');

            // Filtros
            $search = service('request')->getGet('search');
            if ($search) {
                $builder->like('fv.cliente_id', $search);
            }

            // Contar total
            $totalQuery = clone $builder;
            $total = $totalQuery->countAllResults(false);

            // Aplicar ordenamiento
            $orderBy = service('request')->getGet('order_by') ?? 'total_gastado';
            $orderDir = service('request')->getGet('order_dir') ?? 'DESC';
            $builder->orderBy($orderBy, $orderDir);

            // Aplicar paginación
            $offset = ($params['page'] - 1) * $params['limit'];
            $builder->limit($params['limit'], $offset);

            $results = $builder->get()->getResultArray();

            return $this->successResponse([
                'data' => $results,
                'pagination' => [
                    'current_page' => $params['page'],
                    'per_page' => $params['limit'],
                    'total' => $total,
                    'total_pages' => ceil($total / $params['limit'])
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en DimensionesController::clientes: ' . $e->getMessage());
            return $this->errorResponse('Error al obtener clientes', 500);
        }
    }

    /**
     * GET /api/dimensiones/productos
     * Obtener información de productos
     */
    public function productos()
    {
        try {
            if (!$this->isAuthenticated()) {
                return $this->unauthorizedResponse();
            }

            $params = $this->processQueryParams();
            
            $db = \Config\Database::connect();
            
            // Obtener productos con estadísticas de ventas
            $builder = $db->table('fact_ventas fv');
            $builder->select([
                'fv.producto_id',
                'COUNT(*) as total_ventas',
                'SUM(fv.cantidad) as cantidad_vendida',
                'SUM(fv.total_venta) as ingresos_totales',
                'AVG(fv.precio_unitario) as precio_promedio',
                'SUM(fv.margen_bruto) as margen_total',
                'COUNT(DISTINCT fv.cliente_id) as clientes_diferentes'
            ]);
            $builder->groupBy('fv.producto_id');

            // Filtros
            $search = service('request')->getGet('search');
            if ($search) {
                $builder->like('fv.producto_id', $search);
            }

            $categoria = service('request')->getGet('categoria');
            if ($categoria) {
                // Asumiendo que producto_id contiene información de categoría
                $builder->like('fv.producto_id', $categoria);
            }

            // Contar total
            $totalQuery = clone $builder;
            $total = $totalQuery->countAllResults(false);

            // Aplicar ordenamiento
            $orderBy = service('request')->getGet('order_by') ?? 'ingresos_totales';
            $orderDir = service('request')->getGet('order_dir') ?? 'DESC';
            $builder->orderBy($orderBy, $orderDir);

            // Aplicar paginación
            $offset = ($params['page'] - 1) * $params['limit'];
            $builder->limit($params['limit'], $offset);

            $results = $builder->get()->getResultArray();

            return $this->successResponse([
                'data' => $results,
                'pagination' => [
                    'current_page' => $params['page'],
                    'per_page' => $params['limit'],
                    'total' => $total,
                    'total_pages' => ceil($total / $params['limit'])
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en DimensionesController::productos: ' . $e->getMessage());
            return $this->errorResponse('Error al obtener productos', 500);
        }
    }

    /**
     * GET /api/dimensiones/vendedores
     * Obtener información de vendedores
     */
    public function vendedores()
    {
        try {
            if (!$this->isAuthenticated()) {
                return $this->unauthorizedResponse();
            }

            $params = $this->processQueryParams();
            
            $db = \Config\Database::connect();
            
            // Obtener vendedores con estadísticas
            $builder = $db->table('fact_ventas fv');
            $builder->select([
                'fv.vendedor_id',
                'COUNT(*) as total_ventas',
                'SUM(fv.total_venta) as ingresos_generados',
                'AVG(fv.total_venta) as ticket_promedio',
                'SUM(fv.margen_bruto) as margen_generado',
                'COUNT(DISTINCT fv.cliente_id) as clientes_atendidos',
                'COUNT(DISTINCT fv.producto_id) as productos_vendidos'
            ]);
            $builder->groupBy('fv.vendedor_id');

            // Filtros de fecha
            $dateFrom = service('request')->getGet('date_from');
            $dateTo = service('request')->getGet('date_to');
            
            if ($dateFrom) {
                $builder->where('fv.fecha_venta >=', $dateFrom);
            }
            if ($dateTo) {
                $builder->where('fv.fecha_venta <=', $dateTo);
            }

            // Contar total
            $totalQuery = clone $builder;
            $total = $totalQuery->countAllResults(false);

            // Aplicar ordenamiento
            $orderBy = service('request')->getGet('order_by') ?? 'ingresos_generados';
            $orderDir = service('request')->getGet('order_dir') ?? 'DESC';
            $builder->orderBy($orderBy, $orderDir);

            // Aplicar paginación
            $offset = ($params['page'] - 1) * $params['limit'];
            $builder->limit($params['limit'], $offset);

            $results = $builder->get()->getResultArray();

            return $this->successResponse([
                'data' => $results,
                'pagination' => [
                    'current_page' => $params['page'],
                    'per_page' => $params['limit'],
                    'total' => $total,
                    'total_pages' => ceil($total / $params['limit'])
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en DimensionesController::vendedores: ' . $e->getMessage());
            return $this->errorResponse('Error al obtener vendedores', 500);
        }
    }

    /**
     * GET /api/dimensiones/sucursales
     * Obtener información de sucursales
     */
    public function sucursales()
    {
        try {
            if (!$this->isAuthenticated()) {
                return $this->unauthorizedResponse();
            }

            $params = $this->processQueryParams();
            
            $db = \Config\Database::connect();
            
            // Obtener sucursales con estadísticas
            $builder = $db->table('fact_ventas fv');
            $builder->select([
                'fv.sucursal_id',
                'COUNT(*) as total_ventas',
                'SUM(fv.total_venta) as ingresos_totales',
                'AVG(fv.total_venta) as ticket_promedio',
                'SUM(fv.margen_bruto) as margen_total',
                'COUNT(DISTINCT fv.cliente_id) as clientes_unicos',
                'COUNT(DISTINCT fv.vendedor_id) as vendedores_activos'
            ]);
            $builder->groupBy('fv.sucursal_id');

            // Filtros de fecha
            $dateFrom = service('request')->getGet('date_from');
            $dateTo = service('request')->getGet('date_to');
            
            if ($dateFrom) {
                $builder->where('fv.fecha_venta >=', $dateFrom);
            }
            if ($dateTo) {
                $builder->where('fv.fecha_venta <=', $dateTo);
            }

            // Contar total
            $totalQuery = clone $builder;
            $total = $totalQuery->countAllResults(false);

            // Aplicar ordenamiento
            $orderBy = service('request')->getGet('order_by') ?? 'ingresos_totales';
            $orderDir = service('request')->getGet('order_dir') ?? 'DESC';
            $builder->orderBy($orderBy, $orderDir);

            // Aplicar paginación
            $offset = ($params['page'] - 1) * $params['limit'];
            $builder->limit($params['limit'], $offset);

            $results = $builder->get()->getResultArray();

            return $this->successResponse([
                'data' => $results,
                'pagination' => [
                    'current_page' => $params['page'],
                    'per_page' => $params['limit'],
                    'total' => $total,
                    'total_pages' => ceil($total / $params['limit'])
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en DimensionesController::sucursales: ' . $e->getMessage());
            return $this->errorResponse('Error al obtener sucursales', 500);
        }
    }
}