<?php

namespace App\Commands;

use CodeIgniter\CLI\CLI;

class EtlTransform extends BaseEtlCommand
{
    protected $group        = 'ETL';
    protected $name         = 'etl:transform';
    protected $description  = 'Transforma datos del staging area aplicando reglas de negocio';

    protected $usage = 'etl:transform [options]';
    protected $arguments = [];
    protected $options = [
        '--entity'   => 'Especifica la entidad a transformar (all, dimensions, facts)',
        '--full-load' => 'Realiza transformación completa en lugar de incremental',
        '--dry-run'  => 'Ejecuta sin realizar cambios en BD',
    ];

    protected string $commandSignature = 'etl:transform';

    public function run(array $params): void
    {
        CLI::write('=================================', 'blue');
        CLI::write('🔄 ETL TRANSFORM - Transformación de Datos', 'blue');
        CLI::write('=================================', 'blue');

        // Validar precondiciones
        $this->validatePreconditions();

        // Obtener parámetros
        $entity = CLI::getOption('entity') ?? 'all';
        $fullLoad = CLI::getOption('full-load') !== null;
        $dryRun = CLI::getOption('dry-run') !== null;

        // Configurar parámetros de ejecución
        $parameters = [
            'entity' => $entity,
            'full_load' => $fullLoad,
            'dry_run' => $dryRun,
        ];

        CLI::write("📊 Parámetros de transformación:");
        CLI::write("  • Entidad: {$entity}");
        CLI::write("  • Carga completa: " . ($fullLoad ? 'Sí' : 'No'));
        CLI::write("  • Modo prueba: " . ($dryRun ? 'Sí' : 'No'));

        if ($dryRun) {
            CLI::write("⚠️  MODO PRUEBA: No se realizarán cambios en la BD", 'yellow');
        }

        // Iniciar logging ETL
        $this->startEtlRun($parameters);

        try {
            $stats = ['total_records' => 0, 'processed_records' => 0];

            // Ejecutar transformación según la entidad
            switch ($entity) {
                case 'all':
                    $stats = $this->transformAll($fullLoad, $dryRun);
                    break;
                case 'dimensions':
                    $stats = $this->transformDimensions($fullLoad, $dryRun);
                    break;
                case 'facts':
                    $stats = $this->transformFacts($fullLoad, $dryRun);
                    break;
                default:
                    CLI::error("Entidad no válida: {$entity}");
                    exit(1);
            }

            // Completar ejecución
            $this->completeEtlRun($stats);

        } catch (\Exception $e) {
            $this->handleEtlError($e);
        }
    }

    /**
     * Transforma todas las entidades
     */
    private function transformAll(bool $fullLoad, bool $dryRun): array
    {
        $totalStats = ['total_records' => 0, 'processed_records' => 0];

        // Primero transformar dimensiones (master data)
        $dimensionStats = $this->transformDimensions($fullLoad, $dryRun);
        $totalStats['total_records'] += $dimensionStats['rows_affected'] ?? 0;
        $totalStats['processed_records'] += $dimensionStats['rows_affected'] ?? 0;

        // Luego transformar hechos (transactional data)
        $factStats = $this->transformFacts($fullLoad, $dryRun);
        $totalStats['total_records'] += $factStats['rows_affected'] ?? 0;
        $totalStats['processed_records'] += $factStats['rows_affected'] ?? 0;

        return $totalStats;
    }

    /**
     * Transforma todas las dimensiones
     */
    private function transformDimensions(bool $fullLoad, bool $dryRun): array
    {
        $totalStats = ['total_records' => 0, 'processed_records' => 0];

        // Transformar dimensión productos
        $productStats = $this->transformDimProduct($fullLoad, $dryRun);
        $totalStats['total_records'] += $productStats['rows_affected'] ?? 0;
        $totalStats['processed_records'] += $productStats['rows_affected'] ?? 0;

        // Transformar dimensión clientes
        $customerStats = $this->transformDimCustomer($fullLoad, $dryRun);
        $totalStats['total_records'] += $customerStats['rows_affected'] ?? 0;
        $totalStats['processed_records'] += $customerStats['rows_affected'] ?? 0;

        return $totalStats;
    }

    /**
     * Transforma dimensión productos
     */
    private function transformDimProduct(bool $fullLoad, bool $dryRun): array
    {
        return $this->executeStep('transform_dim_product', function() use ($fullLoad, $dryRun) {
            CLI::write("🛍️  Transformando dimensión productos...");

            // Obtener datos del staging
            $stagingData = $this->db->table('stg_products')
                ->select('product_id, product_name, product_family, product_category')
                ->get()
                ->getResultArray();

            if (empty($stagingData)) {
                CLI::write("ℹ️  No hay productos en staging para transformar");
                return ['rows_affected' => 0];
            }

            CLI::write("📊 Encontrados " . count($stagingData) . " productos para transformar");

            if ($dryRun) {
                CLI::write("🔍 [DRY RUN] Se transformarían " . count($stagingData) . " productos");
                return ['rows_affected' => count($stagingData)];
            }

            $processedCount = 0;

            foreach ($stagingData as $product) {
                // Aplicar reglas de transformación
                $transformedProduct = [
                    'producto_id' => $product['product_id'],
                    'producto_nombre' => trim(ucwords(strtolower($product['product_name']))),
                    'familia_id' => substr($product['product_id'], 0, 3), // Extraer familia del ID
                    'familia_nombre' => trim(ucwords(strtolower($product['product_family']))),
                    'categoria' => trim(ucwords(strtolower($product['product_category']))),
                    'es_actual' => 1,
                    'fecha_inicio' => date('Y-m-d'),
                    'fecha_fin' => null,
                ];

                // Verificar si el producto ya existe
                $existing = $this->db->table('dim_producto')
                    ->where('producto_id', $product['product_id'])
                    ->where('es_actual', 1)
                    ->get()
                    ->getRowArray();

                if ($existing) {
                    // Verificar si hubo cambios (SCD Type 2)
                    $hasChanges = $existing['producto_nombre'] !== $transformedProduct['producto_nombre'] ||
                                 $existing['familia_nombre'] !== $transformedProduct['familia_nombre'] ||
                                 $existing['categoria'] !== $transformedProduct['categoria'];

                    if ($hasChanges) {
                        // Cerrar el registro anterior
                        $this->db->table('dim_producto')
                            ->where('producto_sk', $existing['producto_sk'])
                            ->update([
                                'es_actual' => 0,
                                'fecha_fin' => date('Y-m-d')
                            ]);

                        // Insertar nueva versión
                        $this->db->table('dim_producto')->insert($transformedProduct);
                        $processedCount++;
                    }
                } else {
                    // Nuevo producto
                    $this->db->table('dim_producto')->insert($transformedProduct);
                    $processedCount++;
                }
            }

            CLI::write("✅ Productos transformados: {$processedCount}");
            return ['rows_affected' => $processedCount];
        });
    }

    /**
     * Transforma dimensión clientes
     */
    private function transformDimCustomer(bool $fullLoad, bool $dryRun): array
    {
        return $this->executeStep('transform_dim_customer', function() use ($fullLoad, $dryRun) {
            CLI::write("👥 Transformando dimensión clientes...");

            // Obtener datos del staging
            $stagingData = $this->db->table('stg_customers')
                ->select('customer_id, customer_name, segment, country, state')
                ->get()
                ->getResultArray();

            if (empty($stagingData)) {
                CLI::write("ℹ️  No hay clientes en staging para transformar");
                return ['rows_affected' => 0];
            }

            CLI::write("📊 Encontrados " . count($stagingData) . " clientes para transformar");

            if ($dryRun) {
                CLI::write("🔍 [DRY RUN] Se transformarían " . count($stagingData) . " clientes");
                return ['rows_affected' => count($stagingData)];
            }

            $processedCount = 0;

            foreach ($stagingData as $customer) {
                // Aplicar reglas de transformación
                $transformedCustomer = [
                    'cliente_id' => $customer['customer_id'],
                    'cliente_nombre' => trim(ucwords(strtolower($customer['customer_name']))),
                    'segmento' => trim(ucfirst(strtolower($customer['segment']))),
                    'pais' => trim(ucwords(strtolower($customer['country']))),
                    'region' => trim(ucwords(strtolower($customer['state']))),
                    'es_actual' => 1,
                    'fecha_inicio' => date('Y-m-d'),
                    'fecha_fin' => null,
                ];

                // Verificar si el cliente ya existe
                $existing = $this->db->table('dim_cliente')
                    ->where('cliente_id', $customer['customer_id'])
                    ->where('es_actual', 1)
                    ->get()
                    ->getRowArray();

                if ($existing) {
                    // Verificar si hubo cambios (SCD Type 2)
                    $hasChanges = $existing['cliente_nombre'] !== $transformedCustomer['cliente_nombre'] ||
                                 $existing['segmento'] !== $transformedCustomer['segmento'] ||
                                 $existing['pais'] !== $transformedCustomer['pais'] ||
                                 $existing['region'] !== $transformedCustomer['region'];

                    if ($hasChanges) {
                        // Cerrar el registro anterior
                        $this->db->table('dim_cliente')
                            ->where('cliente_sk', $existing['cliente_sk'])
                            ->update([
                                'es_actual' => 0,
                                'fecha_fin' => date('Y-m-d')
                            ]);

                        // Insertar nueva versión
                        $this->db->table('dim_cliente')->insert($transformedCustomer);
                        $processedCount++;
                    }
                } else {
                    // Nuevo cliente
                    $this->db->table('dim_cliente')->insert($transformedCustomer);
                    $processedCount++;
                }
            }

            CLI::write("✅ Clientes transformados: {$processedCount}");
            return ['rows_affected' => $processedCount];
        });
    }

    /**
     * Transforma tabla de hechos
     */
    private function transformFacts(bool $fullLoad, bool $dryRun): array
    {
        return $this->executeStep('transform_fact_ventas', function() use ($fullLoad, $dryRun) {
            CLI::write("📈 Transformando hechos de ventas...");

            // Query complejo para obtener datos transformados
            $sql = "
                SELECT 
                    ol.order_id,
                    ol.line_number,
                    o.order_date,
                    ol.product_id,
                    o.customer_id,
                    ol.quantity,
                    ol.unit_price,
                    COALESCE(p.cost, ol.unit_price * 0.7) as unit_cost,
                    ol.line_total,
                    (ol.quantity * COALESCE(p.cost, ol.unit_price * 0.7)) as line_cost,
                    (ol.line_total - (ol.quantity * COALESCE(p.cost, ol.unit_price * 0.7))) as line_margin
                FROM stg_order_lines ol
                INNER JOIN stg_orders o ON ol.order_id = o.order_id
                LEFT JOIN stg_products p ON ol.product_id = p.product_id
                WHERE ol.order_id IS NOT NULL 
                  AND o.order_date IS NOT NULL
                  AND ol.product_id IS NOT NULL
                  AND o.customer_id IS NOT NULL
            ";

            $stagingData = $this->db->query($sql)->getResultArray();

            if (empty($stagingData)) {
                CLI::write("ℹ️  No hay líneas de venta en staging para transformar");
                return ['rows_affected' => 0];
            }

            CLI::write("📊 Encontradas " . count($stagingData) . " líneas de venta para transformar");

            if ($dryRun) {
                CLI::write("🔍 [DRY RUN] Se transformarían " . count($stagingData) . " líneas de venta");
                return ['rows_affected' => count($stagingData)];
            }

            $processedCount = 0;
            $skippedCount = 0;

            foreach ($stagingData as $line) {
                try {
                    // Obtener SKs de las dimensiones
                    $tiempoSk = $this->getDimensionSk('dim_tiempo', 'fecha_natural', $line['order_date']);
                    $productoSk = $this->getDimensionSk('dim_producto', 'producto_id', $line['product_id'], true);
                    $clienteSk = $this->getDimensionSk('dim_cliente', 'cliente_id', $line['customer_id'], true);

                    if (!$tiempoSk || !$productoSk || !$clienteSk) {
                        CLI::write("⚠️  Saltando línea: SKs no encontrados para orden {$line['order_id']}", 'yellow');
                        $skippedCount++;
                        continue;
                    }

                    // Calcular métricas
                    $montoNeto = (float) $line['line_total'];
                    $costoTotal = (float) $line['line_cost'];
                    $margenMonto = $montoNeto - $costoTotal;
                    $margenPorcentaje = $montoNeto > 0 ? ($margenMonto / $montoNeto) * 100 : 0;

                    // Preparar registro de hechos
                    $factRecord = [
                        'tiempo_sk' => $tiempoSk,
                        'producto_sk' => $productoSk,
                        'cliente_sk' => $clienteSk,
                        'orden_id' => $line['order_id'],
                        'linea_numero' => $line['line_number'],
                        'cantidad' => (int) $line['quantity'],
                        'precio_unitario' => (float) $line['unit_price'],
                        'costo_unitario' => (float) $line['unit_cost'],
                        'monto_neto' => $montoNeto,
                        'costo_total' => $costoTotal,
                        'margen_monto' => $margenMonto,
                        'margen_porcentaje' => round($margenPorcentaje, 2),
                    ];

                    // Verificar si ya existe (evitar duplicados)
                    $existing = $this->db->table('fact_ventas')
                        ->where('orden_id', $line['order_id'])
                        ->where('linea_numero', $line['line_number'])
                        ->get()
                        ->getRowArray();

                    if (!$existing) {
                        $this->db->table('fact_ventas')->insert($factRecord);
                        $processedCount++;
                    }

                } catch (\Exception $e) {
                    CLI::write("❌ Error procesando línea orden {$line['order_id']}: " . $e->getMessage(), 'red');
                    $skippedCount++;
                }
            }

            CLI::write("✅ Líneas de venta transformadas: {$processedCount}");
            if ($skippedCount > 0) {
                CLI::write("⚠️  Líneas saltadas: {$skippedCount}", 'yellow');
            }

            return ['rows_affected' => $processedCount];
        });
    }

    /**
     * Obtiene la clave sustituta de una dimensión
     */
    private function getDimensionSk(string $table, string $businessKey, $businessValue, bool $esActual = false): ?int
    {
        $query = $this->db->table($table)->where($businessKey, $businessValue);
        
        if ($esActual) {
            $query->where('es_actual', 1);
        }
        
        $result = $query->get()->getRowArray();
        
        if ($table === 'dim_tiempo') {
            return $result ? $result['tiempo_sk'] : null;
        } elseif ($table === 'dim_producto') {
            return $result ? $result['producto_sk'] : null;
        } elseif ($table === 'dim_cliente') {
            return $result ? $result['cliente_sk'] : null;
        }
        
        return null;
    }
}