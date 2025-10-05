<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class EtlImportList extends BaseCommand
{
    protected $group = 'ETL';
    protected $name = 'etl:import-list';
    protected $description = 'Lista archivos de importación disponibles y estadísticas de la base de datos';

    public function run(array $params)
    {
        CLI::write('=== Sistema de Importación ETL - Estado ===', 'yellow');
        CLI::newLine();

        // Directorio de importaciones
        $importDir = WRITEPATH . 'sql_imports';
        
        // Crear directorio si no existe
        if (!is_dir($importDir)) {
            mkdir($importDir, 0755, true);
        }

        // Listar archivos disponibles
        $this->listImportFiles($importDir);
        CLI::newLine();

        // Mostrar estadísticas de la base de datos
        $this->showDatabaseStats();
        CLI::newLine();

        // Mostrar información de la última importación
        $this->showLastImportInfo();
    }

    private function listImportFiles(string $dir)
    {
        CLI::write('📁 Archivos de importación disponibles:', 'green');
        CLI::write(str_repeat('-', 50), 'dark_gray');

        $files = glob($dir . '/*.sql');
        
        if (empty($files)) {
            CLI::write('  No hay archivos SQL en ' . $dir, 'yellow');
            return;
        }

        foreach ($files as $file) {
            $filename = basename($file);
            $size = human_readable_size(filesize($file));
            $modified = date('Y-m-d H:i:s', filemtime($file));
            
            CLI::write(sprintf('  %-25s %8s  %s', $filename, $size, $modified), 'white');
        }
    }

    private function showDatabaseStats()
    {
        CLI::write('📊 Estadísticas de la base de datos:', 'green');
        CLI::write(str_repeat('-', 50), 'dark_gray');

        try {
            $db = \Config\Database::connect();
            
            // Estadísticas de fact_ventas
            $factCount = $db->table('fact_ventas')->countAllResults();
            $factSum = $db->table('fact_ventas')->selectSum('monto_neto')->get()->getRow()->monto_neto ?? 0;
            
            // Estadísticas de dimensiones
            $dimTiempo = $db->table('dim_tiempo')->countAllResults();
            $dimProducto = $db->table('dim_producto')->countAllResults();
            
            // Rango de fechas
            $fechaMin = $db->query("SELECT MIN(fecha_natural) as min_fecha FROM dim_tiempo WHERE tiempo_sk IN (SELECT DISTINCT tiempo_sk FROM fact_ventas)")->getRow()->min_fecha ?? 'N/A';
            $fechaMax = $db->query("SELECT MAX(fecha_natural) as max_fecha FROM dim_tiempo WHERE tiempo_sk IN (SELECT DISTINCT tiempo_sk FROM fact_ventas)")->getRow()->max_fecha ?? 'N/A';

            CLI::write(sprintf('  %-20s %10d registros', 'fact_ventas:', $factCount), 'white');
            CLI::write(sprintf('  %-20s %10d registros', 'dim_tiempo:', $dimTiempo), 'white');
            CLI::write(sprintf('  %-20s %10d registros', 'dim_producto:', $dimProducto), 'white');
            CLI::newLine();
            CLI::write(sprintf('  %-20s $%12.2f', 'Total ventas:', floatval($factSum)), 'cyan');
            CLI::write(sprintf('  %-20s %s', 'Fecha mínima:', $fechaMin), 'white');
            CLI::write(sprintf('  %-20s %s', 'Fecha máxima:', $fechaMax), 'white');

        } catch (\Exception $e) {
            CLI::write('  Error al obtener estadísticas: ' . $e->getMessage(), 'red');
        }
    }

    private function showLastImportInfo()
    {
        CLI::write('📋 Información de importaciones:', 'green');
        CLI::write(str_repeat('-', 50), 'dark_gray');

        try {
            $db = \Config\Database::connect();
            
            // Buscar ETL runs más recientes
            $lastRuns = $db->table('etl_runs')
                          ->orderBy('started_at', 'DESC')
                          ->limit(3)
                          ->get()
                          ->getResultArray();

            if (empty($lastRuns)) {
                CLI::write('  No hay registros de importaciones ETL', 'yellow');
                return;
            }

            foreach ($lastRuns as $run) {
                $status = $run['status'] === 'completed' ? '✅' : ($run['status'] === 'failed' ? '❌' : '⏳');
                $duration = $run['ended_at'] ? 
                    round((strtotime($run['ended_at']) - strtotime($run['started_at'])) / 60, 2) . ' min' : 
                    'En proceso';
                
                CLI::write(sprintf('  %s %s - %s (%s)', 
                    $status, 
                    $run['command'], 
                    $run['started_at'], 
                    $duration
                ), 'white');
                
                // Mostrar estadísticas adicionales si están disponibles
                if ($run['total_records'] > 0) {
                    CLI::write(sprintf('      Procesados: %d/%d registros', 
                        $run['processed_records'], 
                        $run['total_records']
                    ), 'dark_gray');
                }
            }

        } catch (\Exception $e) {
            CLI::write('  Error al obtener información de importaciones: ' . $e->getMessage(), 'red');
        }
    }
}

// Helper function
if (!function_exists('human_readable_size')) {
    function human_readable_size($bytes, $decimals = 2) {
        $size = array('B','KB','MB','GB','TB');
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . @$size[$factor];
    }
}