<?php

namespace App\Commands;

use CodeIgniter\CLI\CLI;

class EtlLoad extends BaseEtlCommand
{
    protected $group        = 'ETL';
    protected $name         = 'etl:load';
    protected $description  = 'Carga datos finales en el Data Warehouse y actualiza vistas de agregación';

    protected $usage = 'etl:load [options]';
    protected $arguments = [];
    protected $options = [
        '--target'   => 'Especifica el destino de carga (all, aggregations, indexes)',
        '--rebuild'  => 'Reconstruye completamente las estructuras',
        '--dry-run'  => 'Ejecuta sin realizar cambios en BD',
    ];

    protected string $commandSignature = 'etl:load';

    public function run(array $params): void
    {
        CLI::write('=================================', 'blue');
        CLI::write('🔄 ETL LOAD - Carga en Data Warehouse', 'blue');
        CLI::write('=================================', 'blue');

        // Validar precondiciones
        $this->validatePreconditions();

        // Obtener parámetros
        $target = CLI::getOption('target') ?? 'all';
        $rebuild = CLI::getOption('rebuild') !== null;
        $dryRun = CLI::getOption('dry-run') !== null;

        // Configurar parámetros de ejecución
        $parameters = [
            'target' => $target,
            'rebuild' => $rebuild,
            'dry_run' => $dryRun,
        ];

        CLI::write("📊 Parámetros de carga:");
        CLI::write("  • Destino: {$target}");
        CLI::write("  • Reconstruir: " . ($rebuild ? 'Sí' : 'No'));
        CLI::write("  • Modo prueba: " . ($dryRun ? 'Sí' : 'No'));

        if ($dryRun) {
            CLI::write("⚠️  MODO PRUEBA: No se realizarán cambios en la BD", 'yellow');
        }

        // Iniciar logging ETL
        $this->startEtlRun($parameters);

        try {
            $stats = ['total_records' => 0, 'processed_records' => 0];

            // Ejecutar carga según el destino
            switch ($target) {
                case 'all':
                    $stats = $this->loadAll($rebuild, $dryRun);
                    break;
                case 'aggregations':
                    $stats = $this->loadAggregations($rebuild, $dryRun);
                    break;
                case 'indexes':
                    $stats = $this->loadIndexes($rebuild, $dryRun);
                    break;
                default:
                    CLI::error("Destino no válido: {$target}");
                    exit(1);
            }

            // Completar ejecución
            $this->completeEtlRun($stats);

        } catch (\Exception $e) {
            $this->handleEtlError($e);
        }
    }

    /**
     * Carga completa del data warehouse
     */
    private function loadAll(bool $rebuild, bool $dryRun): array
    {
        $totalStats = ['total_records' => 0, 'processed_records' => 0];

        // Validar calidad de datos
        $qualityStats = $this->validateDataWarehouseQuality($dryRun);
        $totalStats['total_records'] += $qualityStats['rows_affected'] ?? 0;
        $totalStats['processed_records'] += $qualityStats['rows_affected'] ?? 0;

        // Actualizar agregaciones
        $aggregationStats = $this->loadAggregations($rebuild, $dryRun);
        $totalStats['total_records'] += $aggregationStats['rows_affected'] ?? 0;
        $totalStats['processed_records'] += $aggregationStats['rows_affected'] ?? 0;

        // Optimizar índices
        $indexStats = $this->loadIndexes($rebuild, $dryRun);
        $totalStats['total_records'] += $indexStats['rows_affected'] ?? 0;
        $totalStats['processed_records'] += $indexStats['rows_affected'] ?? 0;

        // Limpiar staging area (opcional)
        if (!$dryRun && ($this->config['etl.clean_staging_after_load'] ?? false)) {
            $cleanStats = $this->cleanStagingArea($dryRun);
            $totalStats['total_records'] += $cleanStats['rows_affected'] ?? 0;
            $totalStats['processed_records'] += $cleanStats['rows_affected'] ?? 0;
        }

        return $totalStats;
    }

    /**
     * Valida calidad de datos en el data warehouse
     */
    private function validateDataWarehouseQuality(bool $dryRun): array
    {
        return $this->executeStep('validate_data_quality', function() use ($dryRun) {
            CLI::write("🔍 Validando calidad de datos...");

            $issues = [];
            $validationCount = 0;

            // Validar integridad referencial
            $orphanFacts = $this->db->query("
                SELECT COUNT(*) as count 
                FROM fact_ventas fv 
                LEFT JOIN dim_tiempo dt ON fv.tiempo_sk = dt.tiempo_sk
                LEFT JOIN dim_producto dp ON fv.producto_sk = dp.producto_sk  
                LEFT JOIN dim_cliente dc ON fv.cliente_sk = dc.cliente_sk
                WHERE dt.tiempo_sk IS NULL 
                   OR dp.producto_sk IS NULL 
                   OR dc.cliente_sk IS NULL
            ")->getRowArray();

            if ($orphanFacts['count'] > 0) {
                $issues[] = "Registros huérfanos en fact_ventas: {$orphanFacts['count']}";
            }
            $validationCount++;

            // Validar rangos de fechas
            $dateIssues = $this->db->query("
                SELECT COUNT(*) as count 
                FROM fact_ventas fv
                INNER JOIN dim_tiempo dt ON fv.tiempo_sk = dt.tiempo_sk
                WHERE dt.fecha_natural < '2020-01-01' 
                   OR dt.fecha_natural > DATE('now', '+1 year')
            ")->getRowArray();

            if ($dateIssues['count'] > 0) {
                $issues[] = "Fechas fuera de rango válido: {$dateIssues['count']}";
            }
            $validationCount++;

            // Validar métricas negativas
            $negativeMetrics = $this->db->query("
                SELECT COUNT(*) as count 
                FROM fact_ventas 
                WHERE cantidad < 0 
                   OR precio_unitario < 0 
                   OR monto_neto < 0
            ")->getRowArray();

            if ($negativeMetrics['count'] > 0) {
                $issues[] = "Métricas con valores negativos: {$negativeMetrics['count']}";
            }
            $validationCount++;

            // Validar duplicados en dimensiones
            $duplicateProducts = $this->db->query("
                SELECT COUNT(*) as count
                FROM (
                    SELECT producto_id, COUNT(*) 
                    FROM dim_producto 
                    WHERE es_actual = 1 
                    GROUP BY producto_id 
                    HAVING COUNT(*) > 1
                ) duplicates
            ")->getRowArray();

            if ($duplicateProducts['count'] > 0) {
                $issues[] = "Productos duplicados activos: {$duplicateProducts['count']}";
            }
            $validationCount++;

            if ($dryRun) {
                CLI::write("🔍 [DRY RUN] Se validarían {$validationCount} reglas de calidad");
                if (!empty($issues)) {
                    CLI::write("⚠️  [DRY RUN] Problemas detectados:", 'yellow');
                    foreach ($issues as $issue) {
                        CLI::write("  • {$issue}", 'yellow');
                    }
                }
                return ['rows_affected' => $validationCount];
            }

            if (!empty($issues)) {
                CLI::write("⚠️  Problemas de calidad detectados:", 'yellow');
                foreach ($issues as $issue) {
                    CLI::write("  • {$issue}", 'yellow');
                }
                
                // Log issues pero no fallar el proceso
                $this->logError('DATA_QUALITY', 'Problemas de calidad detectados', [
                    'issues' => $issues,
                    'validation_count' => $validationCount
                ]);
            } else {
                CLI::write("✅ Todos los controles de calidad pasaron");
            }

            return ['rows_affected' => $validationCount];
        });
    }

    /**
     * Carga y actualiza vistas de agregación
     */
    private function loadAggregations(bool $rebuild, bool $dryRun): array
    {
        return $this->executeStep('load_aggregations', function() use ($rebuild, $dryRun) {
            CLI::write("📊 Actualizando vistas de agregación...");

            $viewsProcessed = 0;
            $views = ['vw_ventas_diarias', 'vw_ventas_mensuales', 'vw_ventas_productos'];

            if ($dryRun) {
                CLI::write("🔍 [DRY RUN] Se actualizarían " . count($views) . " vistas de agregación");
                return ['rows_affected' => count($views)];
            }

            foreach ($views as $view) {
                try {
                    if ($rebuild) {
                        // En SQLite no se pueden refrescar vistas, pero podemos verificar que existan
                        $result = $this->db->query("SELECT name FROM sqlite_master WHERE type='view' AND name='{$view}'")->getRowArray();
                        if ($result) {
                            CLI::write("✓ Vista {$view} verificada");
                            $viewsProcessed++;
                        } else {
                            CLI::write("⚠️  Vista {$view} no encontrada", 'yellow');
                        }
                    } else {
                        // Verificar acceso a la vista ejecutando una consulta simple
                        $count = $this->db->query("SELECT COUNT(*) as count FROM {$view}")->getRowArray();
                        CLI::write("✓ Vista {$view}: {$count['count']} registros");
                        $viewsProcessed++;
                    }
                } catch (\Exception $e) {
                    CLI::write("❌ Error en vista {$view}: " . $e->getMessage(), 'red');
                }
            }

            CLI::write("✅ Vistas de agregación procesadas: {$viewsProcessed}");
            return ['rows_affected' => $viewsProcessed];
        });
    }

    /**
     * Optimiza índices del data warehouse
     */
    private function loadIndexes(bool $rebuild, bool $dryRun): array
    {
        return $this->executeStep('optimize_indexes', function() use ($rebuild, $dryRun) {
            CLI::write("🔧 Optimizando índices...");

            if ($dryRun) {
                CLI::write("🔍 [DRY RUN] Se optimizarían los índices del data warehouse");
                return ['rows_affected' => 1];
            }

            $optimizedCount = 0;

            try {
                // En SQLite, ejecutar ANALYZE para actualizar estadísticas
                $this->db->query("ANALYZE");
                CLI::write("✓ Estadísticas de índices actualizadas");
                $optimizedCount++;

                // Verificar integridad de la base de datos
                $integrity = $this->db->query("PRAGMA integrity_check")->getRowArray();
                if ($integrity && strtolower($integrity['integrity_check']) === 'ok') {
                    CLI::write("✓ Integridad de base de datos verificada");
                    $optimizedCount++;
                } else {
                    CLI::write("⚠️  Problemas de integridad detectados", 'yellow');
                }

                // Optimizar base de datos (VACUUM)
                if ($rebuild) {
                    CLI::write("🔄 Ejecutando VACUUM...");
                    $this->db->query("VACUUM");
                    CLI::write("✓ Base de datos optimizada");
                    $optimizedCount++;
                }

            } catch (\Exception $e) {
                CLI::write("❌ Error optimizando índices: " . $e->getMessage(), 'red');
            }

            CLI::write("✅ Optimizaciones completadas: {$optimizedCount}");
            return ['rows_affected' => $optimizedCount];
        });
    }

    /**
     * Limpia el staging area después de la carga exitosa
     */
    private function cleanStagingArea(bool $dryRun): array
    {
        return $this->executeStep('clean_staging', function() use ($dryRun) {
            CLI::write("🧹 Limpiando staging area...");

            $stagingTables = ['stg_products', 'stg_customers', 'stg_orders', 'stg_order_lines'];
            $cleanedTables = 0;

            if ($dryRun) {
                CLI::write("🔍 [DRY RUN] Se limpiarían " . count($stagingTables) . " tablas de staging");
                return ['rows_affected' => count($stagingTables)];
            }

            foreach ($stagingTables as $table) {
                try {
                    $count = $this->db->table($table)->countAllResults();
                    if ($count > 0) {
                        $this->db->table($table)->emptyTable();
                        CLI::write("✓ Tabla {$table} limpiada ({$count} registros eliminados)");
                    } else {
                        CLI::write("✓ Tabla {$table} ya estaba vacía");
                    }
                    $cleanedTables++;
                } catch (\Exception $e) {
                    CLI::write("❌ Error limpiando tabla {$table}: " . $e->getMessage(), 'red');
                }
            }

            CLI::write("✅ Tablas de staging limpiadas: {$cleanedTables}");
            return ['rows_affected' => $cleanedTables];
        });
    }

    /**
     * Genera reporte de estadísticas del data warehouse
     */
    private function generateDataWarehouseReport(): array
    {
        return $this->executeStep('generate_report', function() {
            CLI::write("📋 Generando reporte del data warehouse...");

            $report = [];

            // Estadísticas de dimensiones
            $report['dimensions'] = [
                'productos' => $this->db->table('dim_producto')->where('es_actual', 1)->countAllResults(),
                'clientes' => $this->db->table('dim_cliente')->where('es_actual', 1)->countAllResults(),
                'tiempo' => $this->db->table('dim_tiempo')->countAllResults(),
            ];

            // Estadísticas de hechos
            $factStats = $this->db->query("
                SELECT 
                    COUNT(*) as total_lineas,
                    SUM(cantidad) as total_cantidad,
                    SUM(monto_neto) as total_ventas,
                    SUM(margen_monto) as total_margen,
                    MIN(fecha_natural) as fecha_min,
                    MAX(fecha_natural) as fecha_max
                FROM fact_ventas fv
                INNER JOIN dim_tiempo dt ON fv.tiempo_sk = dt.tiempo_sk
            ")->getRowArray();

            $report['facts'] = $factStats;

            // Estadísticas de calidad
            $report['quality'] = [
                'completeness' => $this->calculateCompleteness(),
                'consistency' => $this->calculateConsistency(),
            ];

            CLI::write("📊 Reporte del Data Warehouse:");
            CLI::write("  • Productos activos: " . $report['dimensions']['productos']);
            CLI::write("  • Clientes activos: " . $report['dimensions']['clientes']);
            CLI::write("  • Días en dim_tiempo: " . $report['dimensions']['tiempo']);
            CLI::write("  • Total líneas de venta: " . ($factStats['total_lineas'] ?? 0));
            CLI::write("  • Período: " . ($factStats['fecha_min'] ?? 'N/A') . " - " . ($factStats['fecha_max'] ?? 'N/A'));
            CLI::write("  • Ventas totales: $" . number_format($factStats['total_ventas'] ?? 0, 2));

            return ['rows_affected' => 1];
        });
    }

    /**
     * Calcula métricas de completitud de datos
     */
    private function calculateCompleteness(): array
    {
        return [
            'productos_con_familia' => $this->db->query("
                SELECT (COUNT(CASE WHEN familia_id IS NOT NULL THEN 1 END) * 100.0 / COUNT(*)) as percentage
                FROM dim_producto WHERE es_actual = 1
            ")->getRowArray()['percentage'] ?? 0,
            
            'clientes_con_region' => $this->db->query("
                SELECT (COUNT(CASE WHEN region IS NOT NULL THEN 1 END) * 100.0 / COUNT(*)) as percentage  
                FROM dim_cliente WHERE es_actual = 1
            ")->getRowArray()['percentage'] ?? 0,
        ];
    }

    /**
     * Calcula métricas de consistencia de datos
     */
    private function calculateConsistency(): array
    {
        return [
            'ventas_con_margen_positivo' => $this->db->query("
                SELECT (COUNT(CASE WHEN margen_monto >= 0 THEN 1 END) * 100.0 / COUNT(*)) as percentage
                FROM fact_ventas
            ")->getRowArray()['percentage'] ?? 0,
            
            'precios_consistentes' => $this->db->query("
                SELECT (COUNT(CASE WHEN precio_unitario > 0 AND costo_unitario > 0 THEN 1 END) * 100.0 / COUNT(*)) as percentage
                FROM fact_ventas  
            ")->getRowArray()['percentage'] ?? 0,
        ];
    }
}