<?php

namespace App\Controllers\Api;

/**
 * Controlador API simplificado para datos de ventas
 */
class VentasSimpleController extends BaseApiController
{
    /**
     * GET /api/ventas-simple
     * Obtener datos básicos de ventas 
     */
    public function index()
    {
        try {
            if (!$this->isAuthenticated()) {
                return $this->unauthorizedResponse();
            }

            $params = $this->processQueryParams();
            
            $db = \Config\Database::connect();
            
            // Consulta simple a fact_ventas con join a dim_tiempo
            $builder = $db->table('fact_ventas fv');
            $builder->select([
                'fv.venta_sk',
                'fv.orden_id',
                'fv.cantidad',
                'fv.precio_unitario',
                'fv.monto_linea',
                'fv.margen_monto',
                'fv.canal_venta',
                'fv.vendedor_id',
                'dt.fecha_natural',
                'dt.año',
                'dt.mes',
                'dt.nombre_dia'
            ]);
            
            $builder->join('dim_tiempo dt', 'fv.tiempo_sk = dt.tiempo_sk', 'left');

            // Filtros básicos
            $dateFrom = service('request')->getGet('date_from');
            $dateTo = service('request')->getGet('date_to');
            
            if ($dateFrom) {
                $builder->where('dt.fecha_natural >=', $dateFrom);
            }
            
            if ($dateTo) {
                $builder->where('dt.fecha_natural <=', $dateTo);
            }

            // Contar total
            $totalQuery = clone $builder;
            $total = $totalQuery->countAllResults(false);

            // Paginación
            $offset = ($params['page'] - 1) * $params['limit'];
            $builder->limit($params['limit'], $offset);
            
            // Ordenamiento
            $builder->orderBy('dt.fecha_natural', 'DESC');

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
            log_message('error', 'Error en VentasSimpleController::index: ' . $e->getMessage());
            return $this->errorResponse('Error al obtener datos: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/ventas-simple/stats
     * Estadísticas básicas
     */
    public function stats()
    {
        try {
            if (!$this->isAuthenticated()) {
                return $this->unauthorizedResponse();
            }

            $db = \Config\Database::connect();
            
            // Estadísticas básicas
            $builder = $db->table('fact_ventas');
            $stats = $builder->select([
                'COUNT(*) as total_ventas',
                'SUM(monto_linea) as ingresos_totales',
                'AVG(monto_linea) as ticket_promedio',
                'SUM(margen_monto) as margen_total',
                'COUNT(DISTINCT orden_id) as ordenes_unicas'
            ])->get()->getRowArray();

            return $this->successResponse($stats);

        } catch (\Exception $e) {
            log_message('error', 'Error en VentasSimpleController::stats: ' . $e->getMessage());
            return $this->errorResponse('Error al obtener estadísticas: ' . $e->getMessage(), 500);
        }
    }
}