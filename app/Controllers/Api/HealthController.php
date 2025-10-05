<?php

namespace App\Controllers\Api;

use CodeIgniter\Controller;

/**
 * Controlador para endpoints de salud y monitoreo del sistema
 */
class HealthController extends Controller
{
    /**
     * GET /api/public/health
     * Verificar estado del sistema
     */
    public function check()
    {
        // Detectar si se solicita HTML
        $acceptHeader = $this->request->getHeaderLine('Accept');
        $wantsHtml = strpos($acceptHeader, 'text/html') !== false || 
                     $this->request->getGet('format') === 'html';
        
        try {
            $status = [
                'status' => 'healthy',
                'timestamp' => date('Y-m-d H:i:s'),
                'version' => $this->getVersion(),
                'checks' => []
            ];

            // Verificar base de datos
            try {
                $db = \Config\Database::connect();
                $db->query('SELECT 1');
                $status['checks']['database'] = 'ok';
            } catch (\Exception $e) {
                $status['checks']['database'] = 'error: ' . $e->getMessage();
                $status['status'] = 'unhealthy';
            }

            // Verificar cache
            try {
                $cache = \Config\Services::cache();
                $cache->save('health_check', time(), 60);
                $status['checks']['cache'] = 'ok';
            } catch (\Exception $e) {
                $status['checks']['cache'] = 'error: ' . $e->getMessage();
            }

            // Verificar espacio en disco
            $diskSpace = disk_free_space(WRITEPATH);
            $diskTotal = disk_total_space(WRITEPATH);
            $diskUsage = (($diskTotal - $diskSpace) / $diskTotal) * 100;
            
            $status['checks']['disk_usage'] = round($diskUsage, 2) . '%';
            if ($diskUsage > 90) {
                $status['status'] = 'warning';
            }

            // Verificar memoria
            $memoryUsage = memory_get_peak_usage(true);
            $memoryLimit = ini_get('memory_limit');
            $status['checks']['memory_usage'] = $this->formatBytes($memoryUsage);
            $status['checks']['memory_limit'] = $memoryLimit;

            // Verificar tabla críticas del ETL
            try {
                $db = \Config\Database::connect();
                
                $tables = ['fact_ventas', 'dim_tiempo', 'etl_runs'];
                foreach ($tables as $table) {
                    $count = $db->table($table)->countAllResults();
                    $status['checks']['table_' . $table] = $count . ' records';
                }
                
            } catch (\Exception $e) {
                $status['checks']['etl_tables'] = 'error: ' . $e->getMessage();
                $status['status'] = 'unhealthy';
            }

            $httpStatus = $status['status'] === 'healthy' ? 200 : 503;
            
            // Retornar vista HTML si se solicita
            if ($wantsHtml) {
                $data = [
                    'title' => 'Estado del Sistema',
                    'status' => $status,
                    'httpStatus' => $httpStatus
                ];
                return view('api/health', $data);
            }
            
            return $this->response
                ->setStatusCode($httpStatus)
                ->setJSON($status);

        } catch (\Exception $e) {
            $errorData = [
                'status' => 'error',
                'message' => 'Health check failed: ' . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            if ($wantsHtml) {
                $data = [
                    'title' => 'Error del Sistema',
                    'status' => $errorData,
                    'httpStatus' => 500
                ];
                return view('api/health', $data);
            }
            
            return $this->response
                ->setStatusCode(500)
                ->setJSON($errorData);
        }
    }

    /**
     * GET /api/public/version
     * Información de versión del sistema
     */
    public function version()
    {
        $versionInfo = [
            'application' => [
                'name' => 'ETL Data Warehouse System',
                'version' => $this->getVersion(),
                'environment' => ENVIRONMENT,
                'build_date' => $this->getBuildDate()
            ],
            'framework' => [
                'codeigniter' => \CodeIgniter\CodeIgniter::CI_VERSION,
                'php' => PHP_VERSION
            ],
            'database' => [
                'type' => 'SQLite3',
                'version' => $this->getSQLiteVersion()
            ],
            'features' => [
                'etl_pipeline' => 'enabled',
                'api_rest' => 'enabled',
                'authentication' => 'shield',
                'data_warehouse' => 'star_schema'
            ]
        ];

        return $this->response->setJSON($versionInfo);
    }

    /**
     * Obtener versión de la aplicación
     */
    private function getVersion(): string
    {
        // En un entorno real, esto vendría de un archivo de versión o git
        return '1.0.0';
    }

    /**
     * Obtener fecha de build
     */
    private function getBuildDate(): string
    {
        // En un entorno real, esto se generaría en el proceso de build
        return date('Y-m-d H:i:s');
    }

    /**
     * Obtener versión de SQLite
     */
    private function getSQLiteVersion(): string
    {
        try {
            $db = \Config\Database::connect();
            $result = $db->query('SELECT sqlite_version()')->getRow();
            return $result ? $result->{'sqlite_version()'} : 'unknown';
        } catch (\Exception $e) {
            return 'error: ' . $e->getMessage();
        }
    }

    /**
     * Formatear bytes en unidades legibles
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}