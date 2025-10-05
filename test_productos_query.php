<?php
require_once 'vendor/autoload.php';

// Test de la consulta de productos para diagnosticar el problema de timeout

echo "=== Test de Consulta de Productos ===\n";

$startTime = microtime(true);

try {
    // Conectar a SQLite directamente
    $db = new PDO('sqlite:writable/etl_dw_system.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "1. Conexión a BD exitosa\n";
    
    // Test 1: Consulta simple de count
    $stmt = $db->query("SELECT COUNT(*) as total FROM fact_ventas");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "2. Total registros fact_ventas: " . $count['total'] . "\n";
    
    // Test 2: Consulta agregada básica
    $stmt = $db->query("SELECT producto_sk, SUM(monto_linea) as total_ventas FROM fact_ventas GROUP BY producto_sk ORDER BY total_ventas DESC LIMIT 10");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "3. Top 10 productos por ventas:\n";
    foreach ($products as $i => $product) {
        echo "   " . ($i+1) . ". Producto {$product['producto_sk']}: $" . number_format($product['total_ventas'], 2) . "\n";
    }
    
    // Test 3: Consulta con JOIN
    echo "4. Probando JOIN con dim_producto...\n";
    $stmt = $db->query("SELECT fv.producto_sk, dp.producto_nombre, SUM(fv.monto_linea) as total_ventas 
                        FROM fact_ventas fv 
                        LEFT JOIN dim_producto dp ON fv.producto_sk = dp.producto_sk 
                        GROUP BY fv.producto_sk, dp.producto_nombre 
                        ORDER BY total_ventas DESC 
                        LIMIT 5");
    $products_with_names = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "5. Top 5 productos con nombres:\n";
    foreach ($products_with_names as $i => $product) {
        $name = $product['producto_nombre'] ?? 'Sin nombre';
        echo "   " . ($i+1) . ". {$name} (ID: {$product['producto_sk']}): $" . number_format($product['total_ventas'], 2) . "\n";
    }
    
    $endTime = microtime(true);
    $executionTime = $endTime - $startTime;
    
    echo "\n=== Resultado ===\n";
    echo "Tiempo total de ejecución: " . round($executionTime, 3) . " segundos\n";
    echo "Status: ÉXITO\n";
    
} catch (Exception $e) {
    $endTime = microtime(true);
    $executionTime = $endTime - $startTime;
    
    echo "\n=== ERROR ===\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "Tiempo hasta error: " . round($executionTime, 3) . " segundos\n";
}