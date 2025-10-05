<?php

namespace App\Controllers\Api;

/**
 * Controlador API simplificado para dimensiones
 */
class DimensionesSimpleController extends BaseApiController
{
    /**
     * GET /api/dimensiones-simple/tiempo
     */
    public function tiempo()
    {
        try {
            if (!$this->isAuthenticated()) {
                return $this->unauthorizedResponse();
            }

            $params = $this->processQueryParams();
            $db = \Config\Database::connect();
            $builder = $db->table('dim_tiempo');

            // Filtros
            $anio = service('request')->getGet('anio');
            if ($anio) {
                $builder->where('año', $anio);
            }

            // Contar total
            $totalQuery = clone $builder;
            $total = $totalQuery->countAllResults(false);

            // Paginación
            $offset = ($params['page'] - 1) * $params['limit'];
            $builder->limit($params['limit'], $offset);
            $builder->orderBy('fecha_natural', 'DESC');

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
            log_message('error', 'Error en DimensionesSimpleController::tiempo: ' . $e->getMessage());
            return $this->errorResponse('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/dimensiones-simple/resumen
     */
    public function resumen()
    {
        try {
            if (!$this->isAuthenticated()) {
                return $this->unauthorizedResponse();
            }

            $db = \Config\Database::connect();
            
            $resumen = [
                'tiempo' => [
                    'total_fechas' => $db->table('dim_tiempo')->countAllResults(),
                    'anio_min' => $db->table('dim_tiempo')->selectMin('año')->get()->getRow()->año,
                    'anio_max' => $db->table('dim_tiempo')->selectMax('año')->get()->getRow()->año
                ],
                'ventas' => [
                    'total_registros' => $db->table('fact_ventas')->countAllResults(),
                    'ordenes_unicas' => $db->table('fact_ventas')->distinct()->select('orden_id')->countAllResults()
                ]
            ];

            return $this->successResponse($resumen);

        } catch (\Exception $e) {
            log_message('error', 'Error en DimensionesSimpleController::resumen: ' . $e->getMessage());
            return $this->errorResponse('Error: ' . $e->getMessage(), 500);
        }
    }
}