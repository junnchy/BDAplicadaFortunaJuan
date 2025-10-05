<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    public function run()
    {
        // Poblar staging con datos de ejemplo
        $this->populateStaging();
        
        // Poblar dimensiones con datos transformados
        $this->populateDimensions();
        
        // Poblar hechos
        $this->populateFacts();
    }
    
    private function populateStaging()
    {
        // Productos de ejemplo
        $productos = [
            ['product_id' => 'LAPTOP001', 'product_name' => 'Laptop HP Pavilion', 'product_category' => 'Electrónicos', 'product_family' => 'Computadoras', 'list_price' => 899.99, 'cost' => 650.00],
            ['product_id' => 'MOUSE001', 'product_name' => 'Mouse Inalámbrico Logitech', 'product_category' => 'Electrónicos', 'product_family' => 'Accesorios', 'list_price' => 29.99, 'cost' => 15.00],
            ['product_id' => 'MONITOR001', 'product_name' => 'Monitor Samsung 24"', 'product_category' => 'Electrónicos', 'product_family' => 'Periféricos', 'list_price' => 199.99, 'cost' => 120.00],
            ['product_id' => 'KEYBOARD001', 'product_name' => 'Teclado Mecánico RGB', 'product_category' => 'Electrónicos', 'product_family' => 'Accesorios', 'list_price' => 79.99, 'cost' => 45.00],
            ['product_id' => 'WEBCAM001', 'product_name' => 'Webcam HD Logitech', 'product_category' => 'Electrónicos', 'product_family' => 'Accesorios', 'list_price' => 59.99, 'cost' => 35.00],
        ];
        
        foreach ($productos as &$producto) {
            $producto['extract_date'] = date('Y-m-d H:i:s');
            $producto['created_at'] = date('Y-m-d H:i:s');
            $producto['is_active'] = true;
            $producto['source_system'] = 'OLTP';
            $producto['processed'] = false;
        }
        
        $this->db->table('stg_products')->insertBatch($productos);
        
        // Clientes de ejemplo
        $clientes = [
            ['customer_id' => 'CUST001', 'customer_name' => 'Juan Pérez', 'customer_type' => 'Individual', 'segment' => 'Premium', 'city' => 'Buenos Aires', 'state' => 'CABA', 'country' => 'Argentina', 'registration_date' => '2023-01-15'],
            ['customer_id' => 'CUST002', 'customer_name' => 'María García', 'customer_type' => 'Individual', 'segment' => 'Regular', 'city' => 'Córdoba', 'state' => 'Córdoba', 'country' => 'Argentina', 'registration_date' => '2023-02-20'],
            ['customer_id' => 'CUST003', 'customer_name' => 'TechCorp SA', 'customer_type' => 'Empresa', 'segment' => 'Corporate', 'city' => 'Santiago', 'state' => 'Metropolitana', 'country' => 'Chile', 'registration_date' => '2023-01-10'],
            ['customer_id' => 'CUST004', 'customer_name' => 'Carlos López', 'customer_type' => 'Individual', 'segment' => 'Regular', 'city' => 'Lima', 'state' => 'Lima', 'country' => 'Perú', 'registration_date' => '2023-03-05'],
            ['customer_id' => 'CUST005', 'customer_name' => 'InnovateCo', 'customer_type' => 'Empresa', 'segment' => 'Corporate', 'city' => 'Bogotá', 'state' => 'Cundinamarca', 'country' => 'Colombia', 'registration_date' => '2023-01-25'],
        ];
        
        foreach ($clientes as &$cliente) {
            $cliente['extract_date'] = date('Y-m-d H:i:s');
            $cliente['created_at'] = date('Y-m-d H:i:s');
            $cliente['is_active'] = true;
            $cliente['source_system'] = 'OLTP';
            $cliente['processed'] = false;
            $cliente['email'] = strtolower(str_replace(' ', '.', $cliente['customer_name'])) . '@email.com';
            $cliente['credit_limit'] = $cliente['customer_type'] === 'Empresa' ? 50000.00 : 5000.00;
        }
        
        $this->db->table('stg_customers')->insertBatch($clientes);
        
        // Órdenes de ejemplo
        $ordenes = [
            ['order_id' => 'ORD001', 'customer_id' => 'CUST001', 'order_date' => '2024-01-15', 'order_status' => 'Completed', 'total_amount' => 929.98],
            ['order_id' => 'ORD002', 'customer_id' => 'CUST002', 'order_date' => '2024-01-16', 'order_status' => 'Completed', 'total_amount' => 199.99],
            ['order_id' => 'ORD003', 'customer_id' => 'CUST003', 'order_date' => '2024-01-17', 'order_status' => 'Completed', 'total_amount' => 2699.94],
            ['order_id' => 'ORD004', 'customer_id' => 'CUST004', 'order_date' => '2024-01-18', 'order_status' => 'Completed', 'total_amount' => 139.98],
            ['order_id' => 'ORD005', 'customer_id' => 'CUST005', 'order_date' => '2024-01-19', 'order_status' => 'Completed', 'total_amount' => 1799.97],
        ];
        
        foreach ($ordenes as &$orden) {
            $orden['extract_date'] = date('Y-m-d H:i:s');
            $orden['created_at'] = date('Y-m-d H:i:s');
            $orden['source_system'] = 'OLTP';
            $orden['processed'] = false;
            $orden['currency'] = 'USD';
        }
        
        $this->db->table('stg_orders')->insertBatch($ordenes);
        
        // Líneas de órdenes
        $lineas = [
            ['order_id' => 'ORD001', 'line_number' => 1, 'product_id' => 'LAPTOP001', 'quantity' => 1, 'unit_price' => 899.99, 'line_total' => 899.99],
            ['order_id' => 'ORD001', 'line_number' => 2, 'product_id' => 'MOUSE001', 'quantity' => 1, 'unit_price' => 29.99, 'line_total' => 29.99],
            ['order_id' => 'ORD002', 'line_number' => 1, 'product_id' => 'MONITOR001', 'quantity' => 1, 'unit_price' => 199.99, 'line_total' => 199.99],
            ['order_id' => 'ORD003', 'line_number' => 1, 'product_id' => 'LAPTOP001', 'quantity' => 3, 'unit_price' => 899.99, 'line_total' => 2699.97],
            ['order_id' => 'ORD004', 'line_number' => 1, 'product_id' => 'KEYBOARD001', 'quantity' => 1, 'unit_price' => 79.99, 'line_total' => 79.99],
            ['order_id' => 'ORD004', 'line_number' => 2, 'product_id' => 'WEBCAM001', 'quantity' => 1, 'unit_price' => 59.99, 'line_total' => 59.99],
            ['order_id' => 'ORD005', 'line_number' => 1, 'product_id' => 'LAPTOP001', 'quantity' => 2, 'unit_price' => 899.99, 'line_total' => 1799.98],
        ];
        
        foreach ($lineas as &$linea) {
            $linea['extract_date'] = date('Y-m-d H:i:s');
            $linea['created_at'] = date('Y-m-d H:i:s');
            $linea['source_system'] = 'OLTP';
            $linea['processed'] = false;
            $linea['discount_amount'] = 0;
            $linea['tax_amount'] = round($linea['line_total'] * 0.21, 2); // 21% IVA
        }
        
        $this->db->table('stg_order_lines')->insertBatch($lineas);
    }
    
    private function populateDimensions()
    {
        // Poblar dim_producto
        $productos = $this->db->table('stg_products')->get()->getResultArray();
        $dimProductos = [];
        
        foreach ($productos as $producto) {
            $dimProductos[] = [
                'producto_id' => $producto['product_id'],
                'producto_nombre' => $producto['product_name'],
                'familia_id' => strtoupper(substr($producto['product_family'], 0, 3)),
                'familia_nombre' => $producto['product_family'],
                'categoria' => $producto['product_category'],
                'precio_lista' => $producto['list_price'],
                'costo_estandar' => $producto['cost'],
                'margen_bruto' => round((($producto['list_price'] - $producto['cost']) / $producto['list_price']) * 100, 2),
                'activo' => $producto['is_active'],
                'fecha_lanzamiento' => date('Y-m-d'),
                'unidad_medida' => 'unidad',
                'scd_version' => 1,
                'fecha_efectiva_desde' => date('Y-m-d H:i:s'),
                'es_actual' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }
        
        $this->db->table('dim_producto')->insertBatch($dimProductos);
        
        // Poblar dim_cliente
        $clientes = $this->db->table('stg_customers')->get()->getResultArray();
        $dimClientes = [];
        
        $regiones = [
            'Argentina' => 'América del Sur',
            'Chile' => 'América del Sur',
            'Perú' => 'América del Sur',
            'Colombia' => 'América del Sur',
        ];
        
        foreach ($clientes as $cliente) {
            $dimClientes[] = [
                'cliente_id' => $cliente['customer_id'],
                'cliente_nombre' => $cliente['customer_name'],
                'segmento' => $cliente['segment'],
                'tipo_cliente' => $cliente['customer_type'],
                'email' => $cliente['email'],
                'telefono' => '+' . rand(1000000000, 9999999999),
                'ciudad' => $cliente['city'],
                'estado_provincia' => $cliente['state'],
                'pais' => $cliente['country'],
                'region' => $regiones[$cliente['country']] ?? 'Otra',
                'fecha_registro' => $cliente['registration_date'],
                'activo' => $cliente['is_active'],
                'limite_credito' => $cliente['credit_limit'],
                'score_credito' => rand(600, 850),
                'scd_version' => 1,
                'fecha_efectiva_desde' => date('Y-m-d H:i:s'),
                'es_actual' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }
        
        $this->db->table('dim_cliente')->insertBatch($dimClientes);
    }
    
    private function populateFacts()
    {
        // Obtener las claves surrogate
        $productos = $this->db->table('dim_producto')->select('producto_sk, producto_id')->get()->getResultArray();
        $clientes = $this->db->table('dim_cliente')->select('cliente_sk, cliente_id')->get()->getResultArray();
        
        $productoMap = array_column($productos, 'producto_sk', 'producto_id');
        $clienteMap = array_column($clientes, 'cliente_sk', 'cliente_id');
        
        // Obtener las líneas de órdenes
        $lineas = $this->db->table('stg_order_lines ol')
            ->join('stg_orders o', 'ol.order_id = o.order_id')
            ->select('ol.*, o.order_date, o.customer_id')
            ->get()->getResultArray();
        
        $factVentas = [];
        
        foreach ($lineas as $linea) {
            // Obtener tiempo_sk
            $tiempoSk = $this->db->table('dim_tiempo')
                ->select('tiempo_sk')
                ->where('fecha_natural', $linea['order_date'])
                ->get()->getRow()->tiempo_sk ?? 1;
            
            $productoSk = $productoMap[$linea['product_id']] ?? 1;
            $clienteSk = $clienteMap[$linea['customer_id']] ?? 1;
            
            // Calcular costos y márgenes
            $costoUnitario = $this->db->table('dim_producto')
                ->select('costo_estandar')
                ->where('producto_sk', $productoSk)
                ->get()->getRow()->costo_estandar ?? 0;
            
            $costoTotal = $costoUnitario * $linea['quantity'];
            $margenMonto = $linea['line_total'] - $costoTotal;
            $margenPorcentaje = $linea['line_total'] > 0 ? ($margenMonto / $linea['line_total']) * 100 : 0;
            
            $factVentas[] = [
                'tiempo_sk' => $tiempoSk,
                'producto_sk' => $productoSk,
                'cliente_sk' => $clienteSk,
                'orden_id' => $linea['order_id'],
                'linea_numero' => $linea['line_number'],
                'cantidad' => $linea['quantity'],
                'precio_unitario' => $linea['unit_price'],
                'monto_linea' => $linea['line_total'],
                'descuento_monto' => $linea['discount_amount'],
                'impuesto_monto' => $linea['tax_amount'],
                'costo_unitario' => $costoUnitario,
                'costo_total' => $costoTotal,
                'margen_monto' => $margenMonto,
                'margen_porcentaje' => round($margenPorcentaje, 2),
                'monto_neto' => $linea['line_total'],
                'moneda' => 'USD',
                'canal_venta' => 'Online',
                'facturado' => true,
                'fecha_factura' => $linea['order_date'],
                'numero_factura' => 'FAC-' . $linea['order_id'] . '-' . $linea['line_number'],
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }
        
        $this->db->table('fact_ventas')->insertBatch($factVentas);
    }
}