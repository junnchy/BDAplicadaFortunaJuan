<?php

namespace App\Commands;

use CodeIgniter\CLI\CLI;

class EtlExtract extends BaseEtlCommand
{
    protected $group        = 'ETL';
    protected $name         = 'etl:extract';
    protected $description  = 'Extrae datos desde fuentes externas hacia staging area';
    protected $usage        = 'etl:extract [options]';
    protected $arguments    = [];
    protected $options      = [
        '--source'     => 'Especifica la fuente de datos (all, orders, customers, products)',
        '--date-from'  => 'Fecha de inicio para extracción incremental (YYYY-MM-DD)',
        '--date-to'    => 'Fecha de fin para extracción incremental (YYYY-MM-DD)',
        '--full-load'  => 'Realiza carga completa en lugar de incremental',
        '--dry-run'    => 'Ejecuta sin realizar cambios en BD',
    ];

    protected string $commandSignature = 'etl:extract';

    public function run(array $params): void
    {
        CLI::write('=================================', 'blue');
        CLI::write('🔄 ETL EXTRACT - Extracción de Datos', 'blue');
        CLI::write('=================================', 'blue');

        // Validar precondiciones
        $this->validatePreconditions();

        // Obtener parámetros
        $source = CLI::getOption('source') ?? 'all';
        $dateFrom = CLI::getOption('date-from');
        $dateTo = CLI::getOption('date-to') ?? date('Y-m-d');
        $fullLoad = CLI::getOption('full-load') !== null;
        $dryRun = CLI::getOption('dry-run') !== null;

        // Configurar parámetros de ejecución
        $parameters = [
            'source' => $source,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'full_load' => $fullLoad,
            'dry_run' => $dryRun,
        ];

        CLI::write("📊 Parámetros de extracción:");
        CLI::write("  • Fuente: {$source}");
        CLI::write("  • Fecha desde: " . ($dateFrom ?? 'Auto'));
        CLI::write("  • Fecha hasta: {$dateTo}");
        CLI::write("  • Carga completa: " . ($fullLoad ? 'Sí' : 'No'));
        CLI::write("  • Modo prueba: " . ($dryRun ? 'Sí' : 'No'));

        if ($dryRun) {
            CLI::write("⚠️  MODO PRUEBA: No se realizarán cambios en la BD", 'yellow');
        }

        // Iniciar logging ETL
        $this->startEtlRun($parameters);

        try {
            $stats = ['total_records' => 0, 'processed_records' => 0];

            // Ejecutar extracción según la fuente
            switch ($source) {
                case 'all':
                    $stats = $this->extractAll($dateFrom, $dateTo, $fullLoad, $dryRun);
                    break;
                case 'orders':
                    $stats = $this->extractOrders($dateFrom, $dateTo, $fullLoad, $dryRun);
                    break;
                case 'customers':
                    $stats = $this->extractCustomers($fullLoad, $dryRun);
                    break;
                case 'products':
                    $stats = $this->extractProducts($fullLoad, $dryRun);
                    break;
                default:
                    throw new \InvalidArgumentException("Fuente no válida: {$source}");
            }

            // Completar ejecución
            $this->completeEtlRun($stats);

        } catch (\Exception $e) {
            $this->failEtlRun($e->getMessage());
        }
    }

    /**
     * Extrae todos los datos
     */
    private function extractAll(?string $dateFrom, string $dateTo, bool $fullLoad, bool $dryRun): array
    {
        $totalStats = ['total_records' => 0, 'processed_records' => 0];

        // Extraer productos (master data)
        $productStats = $this->extractProducts($fullLoad, $dryRun);
        $totalStats['total_records'] += $productStats['rows_affected'] ?? 0;
        $totalStats['processed_records'] += $productStats['rows_affected'] ?? 0;

        // Extraer clientes (master data)
        $customerStats = $this->extractCustomers($fullLoad, $dryRun);
        $totalStats['total_records'] += $customerStats['rows_affected'] ?? 0;
        $totalStats['processed_records'] += $customerStats['rows_affected'] ?? 0;

        // Extraer órdenes (transactional data)
        $orderStats = $this->extractOrders($dateFrom, $dateTo, $fullLoad, $dryRun);
        $totalStats['total_records'] += $orderStats['rows_affected'] ?? 0;
        $totalStats['processed_records'] += $orderStats['rows_affected'] ?? 0;

        return $totalStats;
    }

    /**
     * Extrae datos de órdenes
     */
    private function extractOrders(?string $dateFrom, string $dateTo, bool $fullLoad, bool $dryRun): array
    {
        return $this->executeStep('extract_orders', function() use ($dateFrom, $dateTo, $fullLoad, $dryRun) {
            CLI::write("📦 Extrayendo órdenes...");

            // Simular extracción desde sistema fuente
            $sourceData = $this->getSourceOrders($dateFrom, $dateTo, $fullLoad);
            
            if (empty($sourceData)) {
                CLI::write("ℹ️  No hay órdenes para extraer en el rango especificado");
                return ['rows_affected' => 0];
            }

            CLI::write("📊 Encontradas " . count($sourceData) . " órdenes para extraer");

            if ($dryRun) {
                CLI::write("🔍 [DRY RUN] Se procesarían " . count($sourceData) . " órdenes");
                return ['rows_affected' => count($sourceData)];
            }

            // Limpiar staging si es carga completa
            if ($fullLoad) {
                $this->db->table('stg_orders')->emptyTable();
                $this->db->table('stg_order_lines')->emptyTable();
                CLI::write("🧹 Tablas de staging limpiadas para carga completa");
            }

            // Procesar órdenes en lotes
            $orderStats = $this->processBatch($sourceData, function($batch) {
                return $this->insertOrdersBatch($batch);
            });

            // Extraer líneas de órdenes
            $orderIds = array_column($sourceData, 'order_id');
            $orderLinesData = $this->getSourceOrderLines($orderIds);
            
            if (!empty($orderLinesData)) {
                CLI::write("📊 Extrayendo " . count($orderLinesData) . " líneas de órdenes");
                $linesStats = $this->processBatch($orderLinesData, function($batch) {
                    return $this->insertOrderLinesBatch($batch);
                });
                
                $orderStats['total_records'] += $linesStats['total_records'];
                $orderStats['processed_records'] += $linesStats['processed_records'];
            }

            return $orderStats;
        });
    }

    /**
     * Extrae datos de clientes
     */
    private function extractCustomers(bool $fullLoad, bool $dryRun): array
    {
        return $this->executeStep('extract_customers', function() use ($fullLoad, $dryRun) {
            CLI::write("👥 Extrayendo clientes...");

            $sourceData = $this->getSourceCustomers($fullLoad);
            
            if (empty($sourceData)) {
                CLI::write("ℹ️  No hay clientes para extraer");
                return ['rows_affected' => 0];
            }

            CLI::write("📊 Encontrados " . count($sourceData) . " clientes para extraer");

            if ($dryRun) {
                CLI::write("🔍 [DRY RUN] Se procesarían " . count($sourceData) . " clientes");
                return ['rows_affected' => count($sourceData)];
            }

            if ($fullLoad) {
                $this->db->table('stg_customers')->emptyTable();
                CLI::write("🧹 Tabla stg_customers limpiada para carga completa");
            }

            return $this->processBatch($sourceData, function($batch) {
                return $this->insertCustomersBatch($batch);
            });
        });
    }

    /**
     * Extrae datos de productos
     */
    private function extractProducts(bool $fullLoad, bool $dryRun): array
    {
        return $this->executeStep('extract_products', function() use ($fullLoad, $dryRun) {
            CLI::write("🛍️  Extrayendo productos...");

            $sourceData = $this->getSourceProducts($fullLoad);
            
            if (empty($sourceData)) {
                CLI::write("ℹ️  No hay productos para extraer");
                return ['rows_affected' => 0];
            }

            CLI::write("📊 Encontrados " . count($sourceData) . " productos para extraer");

            if ($dryRun) {
                CLI::write("🔍 [DRY RUN] Se procesarían " . count($sourceData) . " productos");
                return ['rows_affected' => count($sourceData)];
            }

            if ($fullLoad) {
                $this->db->table('stg_products')->emptyTable();
                CLI::write("🧹 Tabla stg_products limpiada para carga completa");
            }

            return $this->processBatch($sourceData, function($batch) {
                return $this->insertProductsBatch($batch);
            });
        });
    }

    /**
     * Simula obtención de órdenes desde sistema fuente
     */
    private function getSourceOrders(?string $dateFrom, string $dateTo, bool $fullLoad): array
    {
        // En un escenario real, esto consultaría la BD transaccional
        // Por ahora simularemos datos
        
        if ($fullLoad) {
            // Retornar todos los datos históricos
            return $this->generateSampleOrders(50, '2024-01-01', $dateTo);
        }

        // Extracción incremental
        $fromDate = $dateFrom ?? $this->getLastExtractDate('orders');
        return $this->generateSampleOrders(10, $fromDate, $dateTo);
    }

    /**
     * Simula obtención de líneas de órdenes
     */
    private function getSourceOrderLines(array $orderIds): array
    {
        // Simular 2-3 líneas por orden
        $lines = [];
        foreach ($orderIds as $orderId) {
            $numLines = rand(1, 3);
            for ($i = 1; $i <= $numLines; $i++) {
                $lines[] = [
                    'order_id' => $orderId,
                    'line_number' => $i,
                    'product_id' => 'PROD' . str_pad(rand(1, 10), 3, '0', STR_PAD_LEFT),
                    'quantity' => rand(1, 5),
                    'unit_price' => rand(10, 500) + rand(0, 99) / 100,
                    'line_total' => 0, // Se calculará
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                ];
                $lines[array_key_last($lines)]['line_total'] = 
                    $lines[array_key_last($lines)]['quantity'] * $lines[array_key_last($lines)]['unit_price'];
                $lines[array_key_last($lines)]['tax_amount'] = 
                    $lines[array_key_last($lines)]['line_total'] * 0.21;
            }
        }
        return $lines;
    }

    /**
     * Obtiene datos de clientes fuente
     */
    private function getSourceCustomers(bool $fullLoad): array
    {
        // Simular datos de clientes
        return $this->generateSampleCustomers($fullLoad ? 100 : 10);
    }

    /**
     * Obtiene datos de productos fuente
     */
    private function getSourceProducts(bool $fullLoad): array
    {
        // Simular datos de productos
        return $this->generateSampleProducts($fullLoad ? 50 : 5);
    }

    /**
     * Inserta lote de órdenes en staging
     */
    private function insertOrdersBatch(array $orders): int
    {
        foreach ($orders as &$order) {
            $order['extract_date'] = date('Y-m-d H:i:s');
            $order['created_at'] = date('Y-m-d H:i:s');
            $order['source_system'] = 'OLTP';
            $order['processed'] = false;
        }

        $this->db->table('stg_orders')->insertBatch($orders);
        return count($orders);
    }

    /**
     * Inserta lote de líneas de órdenes en staging
     */
    private function insertOrderLinesBatch(array $lines): int
    {
        foreach ($lines as &$line) {
            $line['extract_date'] = date('Y-m-d H:i:s');
            $line['created_at'] = date('Y-m-d H:i:s');
            $line['source_system'] = 'OLTP';
            $line['processed'] = false;
        }

        $this->db->table('stg_order_lines')->insertBatch($lines);
        return count($lines);
    }

    /**
     * Inserta lote de clientes en staging
     */
    private function insertCustomersBatch(array $customers): int
    {
        foreach ($customers as &$customer) {
            $customer['extract_date'] = date('Y-m-d H:i:s');
            $customer['created_at'] = date('Y-m-d H:i:s');
            $customer['source_system'] = 'OLTP';
            $customer['processed'] = false;
        }

        $this->db->table('stg_customers')->insertBatch($customers);
        return count($customers);
    }

    /**
     * Inserta lote de productos en staging
     */
    private function insertProductsBatch(array $products): int
    {
        foreach ($products as &$product) {
            $product['extract_date'] = date('Y-m-d H:i:s');
            $product['created_at'] = date('Y-m-d H:i:s');
            $product['source_system'] = 'OLTP';
            $product['processed'] = false;
        }

        $this->db->table('stg_products')->insertBatch($products);
        return count($products);
    }

    /**
     * Obtiene la última fecha de extracción
     */
    private function getLastExtractDate(string $entity): string
    {
        $table = "stg_{$entity}";
        $result = $this->db->table($table)
            ->selectMax('extract_date')
            ->get()
            ->getRow();

        return $result && $result->extract_date 
            ? date('Y-m-d', strtotime($result->extract_date))
            : date('Y-m-d', strtotime('-7 days'));
    }

    /**
     * Genera órdenes de ejemplo
     */
    private function generateSampleOrders(int $count, string $fromDate, string $toDate): array
    {
        $orders = [];
        $startTime = strtotime($fromDate);
        $endTime = strtotime($toDate);
        
        for ($i = 1; $i <= $count; $i++) {
            $randomTime = rand($startTime, $endTime);
            $orders[] = [
                'order_id' => 'ORD' . str_pad($i + 1000, 6, '0', STR_PAD_LEFT),
                'customer_id' => 'CUST' . str_pad(rand(1, 100), 3, '0', STR_PAD_LEFT),
                'order_date' => date('Y-m-d', $randomTime),
                'order_status' => 'Completed',
                'total_amount' => rand(50, 2000) + rand(0, 99) / 100,
                'currency' => 'USD',
            ];
        }
        
        return $orders;
    }

    /**
     * Genera clientes de ejemplo
     */
    private function generateSampleCustomers(int $count): array
    {
        $customers = [];
        $segments = ['Premium', 'Regular', 'Corporate'];
        $countries = ['Argentina', 'Chile', 'Perú', 'Colombia'];
        $types = ['Individual', 'Empresa'];
        
        for ($i = 1; $i <= $count; $i++) {
            $customers[] = [
                'customer_id' => 'CUST' . str_pad($i + 100, 3, '0', STR_PAD_LEFT),
                'customer_name' => 'Cliente ' . $i,
                'customer_type' => $types[array_rand($types)],
                'segment' => $segments[array_rand($segments)],
                'email' => 'cliente' . $i . '@email.com',
                'city' => 'Ciudad ' . $i,
                'state' => 'Estado ' . $i,
                'country' => $countries[array_rand($countries)],
                'registration_date' => date('Y-m-d', strtotime('-' . rand(30, 365) . ' days')),
                'is_active' => true,
                'credit_limit' => rand(1000, 50000),
            ];
        }
        
        return $customers;
    }

    /**
     * Genera productos de ejemplo
     */
    private function generateSampleProducts(int $count): array
    {
        $products = [];
        $families = ['Computadoras', 'Accesorios', 'Periféricos', 'Audio', 'Video'];
        $categories = ['Electrónicos', 'Tecnología', 'Oficina'];
        
        for ($i = 1; $i <= $count; $i++) {
            $listPrice = rand(20, 1000) + rand(0, 99) / 100;
            $cost = $listPrice * (rand(40, 70) / 100);
            
            $products[] = [
                'product_id' => 'PROD' . str_pad($i + 100, 3, '0', STR_PAD_LEFT),
                'product_name' => 'Producto ' . $i,
                'product_category' => $categories[array_rand($categories)],
                'product_family' => $families[array_rand($families)],
                'list_price' => $listPrice,
                'cost' => $cost,
                'is_active' => true,
                'description' => 'Descripción del producto ' . $i,
            ];
        }
        
        return $products;
    }
}