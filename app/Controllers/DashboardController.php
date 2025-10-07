<?php

namespace App\Controllers;

use App\Models\FactVentasModel;
use App\Models\DimTiempoModel;

/**
 * Controlador para el Dashboard principal
 * Interfaz web interactiva con drill-down/drill-up
 */
class DashboardController extends BaseController
{
    protected FactVentasModel $factVentasModel;
    protected DimTiempoModel $dimTiempoModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->factVentasModel = new FactVentasModel();
        $this->dimTiempoModel = new DimTiempoModel();
    }

    /**
     * Página principal del dashboard
     */
    public function index()
    {
        // Verificar autenticación
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $data = [
            'title' => 'Dashboard - ETL Data Warehouse',
            'user' => auth()->user(),
            'current_year' => date('Y'),
            'available_years' => $this->getAvailableYears(),
            'summary_stats' => $this->getSummaryStats()
        ];

        return view('dashboard/index', $data);
    }

    /**
     * Vista de análisis de ventas con drill-down
     */
    public function ventas()
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $level = $this->request->getGet('level') ?? 'year';
        $parentId = $this->request->getGet('parent_id');
        $year = $this->request->getGet('year') ?? $this->getDefaultYear();
        
        // Calcular el nivel padre para drill-up
        $parentLevel = $this->getParentLevel($level);
        
        // Obtener datos de drill-down
        $drillData = $this->getDrillDownData($level, $parentId, $year);
        
        // Calcular estadísticas del nivel actual
        $levelStats = $this->calculateLevelStats($drillData);
        
        // Obtener datos para gráficos
        $chartData = [
            'sales' => $drillData
        ];

        $data = [
            'title' => 'Análisis de Ventas - Dashboard',
            'user' => auth()->user(),
            'level' => $level,
            'current_level' => $level,
            'parent_level' => $parentLevel,
            'parent_id' => $parentId,
            'selected_year' => $year,
            'available_years' => $this->getAvailableYears(),
            'breadcrumb' => $this->buildBreadcrumb($level, $parentId, $year),
            'drill_data' => $drillData,
            'level_stats' => $levelStats,
            'chart_data' => $chartData,
            'current_period' => $this->getCurrentPeriodLabel($level, $parentId, $year)
        ];

        return view('dashboard/ventas', $data);
    }

    /**
     * Vista de análisis de productos
     */
    public function productos()
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        log_message('debug', 'DashboardController::productos() method called');
        
        try {
            // Aumentar tiempo límite para esta operación
            ini_set('max_execution_time', 60);
            
            $factVentasModel = new FactVentasModel();
            $dimTiempoModel = new DimTiempoModel();
            
            // Obtener años disponibles
            $available_years = $dimTiempoModel->distinct()->select('año')->orderBy('año', 'DESC')->findAll();
            $available_years = array_column($available_years, 'año');
            $current_year = date('Y');
            
            // Obtener datos de productos (optimizado - primero agregamos, luego obtenemos nombres)
            $product_data_raw = $factVentasModel->select('producto_sk, SUM(monto_linea) as total_ventas, SUM(cantidad) as cantidad_total, SUM(margen_monto) as margen_total, COUNT(*) as transacciones, AVG(monto_linea) as precio_promedio')
                                              ->groupBy('producto_sk')
                                              ->orderBy('total_ventas', 'DESC')
                                              ->limit(50) // Reducir límite inicial para mejorar rendimiento
                                              ->findAll();
            
            // Obtener nombres de productos para los IDs encontrados
            $producto_sks = array_column($product_data_raw, 'producto_sk');
            $product_names = [];
            
            if (!empty($producto_sks)) {
                $db = \Config\Database::connect();
                
                // Crear lista segura de IDs usando parámetros
                $placeholders = str_repeat('?,', count($producto_sks) - 1) . '?';
                $names_query = $db->query("SELECT producto_sk, producto_nombre, descripcion FROM dim_producto WHERE producto_sk IN ($placeholders)", $producto_sks);
                $names_result = $names_query->getResultArray();
                
                foreach ($names_result as $name_row) {
                    // Usar descripción si existe, sino producto_nombre, sino generar nombre
                    $display_name = '';
                    if (!empty($name_row['descripcion']) && trim($name_row['descripcion']) !== '') {
                        $display_name = trim($name_row['descripcion']);
                    } elseif (!empty($name_row['producto_nombre']) && trim($name_row['producto_nombre']) !== '') {
                        $display_name = trim($name_row['producto_nombre']);
                    } else {
                        $display_name = 'Producto ' . $name_row['producto_sk'];
                    }
                    $product_names[$name_row['producto_sk']] = $display_name;
                }
            }
            
            // Combinar datos con nombres
            $product_data = [];
            foreach ($product_data_raw as $product) {
                // Si no encontramos el producto en dim_producto, generar un nombre descriptivo
                if (isset($product_names[$product['producto_sk']])) {
                    $product['producto_nombre'] = $product_names[$product['producto_sk']];
                } else {
                    // Para productos no encontrados, crear un nombre más descriptivo basado en ventas
                    $ventas_monto = number_format($product['total_ventas'], 0);
                    $producto_sk = $product['producto_sk'];
                    
                    // Crear un nombre más informativo
                    if ($producto_sk == '0' || $producto_sk == 0) {
                        $product['producto_nombre'] = 'Producto Sin ID (Ventas: $' . $ventas_monto . ')';
                    } elseif (is_numeric($producto_sk)) {
                        $product['producto_nombre'] = 'Producto ID-' . $producto_sk . ' (Ventas: $' . $ventas_monto . ')';
                    } else {
                        $product['producto_nombre'] = 'Producto ' . $producto_sk . ' (Ventas: $' . $ventas_monto . ')';
                    }
                }
                $product_data[] = $product;
            }
            
            // Calcular estadísticas de productos
            $total_productos = count($product_data);
            $top_producto_ventas = !empty($product_data) ? $product_data[0]['producto_nombre'] ?? ('Producto ' . $product_data[0]['producto_sk']) : 'N/A';
            
            // Encontrar producto con mayor margen (optimizado)
            $product_by_margin_raw = $factVentasModel->select('producto_sk, SUM(margen_monto) as margen_total')
                                                   ->groupBy('producto_sk')
                                                   ->orderBy('margen_total', 'DESC')
                                                   ->first();
            
            $top_producto_margen = 'N/A';
            if ($product_by_margin_raw) {
                $top_producto_margen = $product_names[$product_by_margin_raw['producto_sk']] ?? ('Producto ' . $product_by_margin_raw['producto_sk']);
            }
            
            // Calcular promedio de ventas
            $promedio_ventas = !empty($product_data) ? array_sum(array_column($product_data, 'total_ventas')) / count($product_data) : 0;
            
            $product_stats = [
                'total_productos' => $total_productos,
                'top_producto_ventas' => $top_producto_ventas,
                'top_producto_margen' => $top_producto_margen,
                'promedio_ventas' => $promedio_ventas
            ];
            
            log_message('debug', 'Product data retrieved: ' . count($product_data) . ' products');
            
            $data = [
                'title' => 'Análisis de Productos - Dashboard',
                'user' => auth()->user(),
                'available_years' => $available_years,
                'current_year' => $current_year,
                'product_data' => $product_data,
                'product_stats' => $product_stats,
                'breadcrumb' => [
                    ['label' => 'Dashboard', 'url' => '/dashboard'],
                    ['label' => 'Análisis de Productos', 'active' => true]
                ]
            ];
            
            log_message('debug', 'Data preparation completed successfully');
            
            return view('dashboard/productos', $data);
            
        } catch (\Exception $e) {
            log_message('error', 'Error in productos method: ' . $e->getMessage());
            
            // En caso de error, mostrar vista con datos mínimos
            $data = [
                'title' => 'Análisis de Productos - Dashboard',
                'user' => auth()->user(),
                'available_years' => [date('Y')],
                'current_year' => date('Y'),
                'product_data' => [],
                'product_stats' => [
                    'total_productos' => 0,
                    'top_producto_ventas' => 'Error',
                    'top_producto_margen' => 'Error',
                    'promedio_ventas' => 0
                ],
                'breadcrumb' => [
                    ['label' => 'Dashboard', 'url' => '/dashboard'],
                    ['label' => 'Análisis de Productos', 'active' => true]
                ],
                'error_message' => 'Error al cargar datos de productos. Por favor, inténtelo de nuevo.'
            ];
            
            return view('dashboard/productos', $data);
        }
    }

    /**
     * Vista de análisis de clientes
     */
    public function clientes()
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        log_message('debug', 'DashboardController::clientes() method called');
        echo "<!-- DEBUG: Método clientes() ejecutándose -->\n"; // DEBUG TEMPORAL
        
        try {
            $factVentasModel = new FactVentasModel();
            $dimTiempoModel = new DimTiempoModel();
            $db = \Config\Database::connect();
            
            // Obtener años disponibles
            $available_years = $dimTiempoModel->distinct()->select('año')->orderBy('año', 'DESC')->findAll();
            $available_years = array_column($available_years, 'año');
            $current_year = date('Y');
            
            // Obtener datos de clientes con información de ventas usando datos reales de dim_cliente
            $client_data_query = "
                SELECT 
                    dc.cliente_sk,
                    dc.cliente_nombre,
                    dc.segmento,
                    dc.email,
                    dc.ciudad,
                    dc.pais,
                    dc.activo,
                    dc.fecha_primera_compra,
                    dc.fecha_ultima_compra,
                    COALESCE(SUM(fv.monto_linea), 0) as total_ventas,
                    COALESCE(SUM(fv.margen_monto), 0) as total_margen,
                    COALESCE(COUNT(fv.venta_sk), 0) as transacciones,
                    COALESCE(AVG(fv.monto_linea), 0) as ticket_promedio,
                    COALESCE(AVG(fv.margen_monto), 0) as margen_promedio,
                    COALESCE(COUNT(DISTINCT fv.tiempo_sk), 0) as dias_activos,
                    COALESCE(COUNT(DISTINCT fv.orden_id), 0) as ordenes_unicas,
                    MAX(dt.fecha_natural) as ultima_compra
                FROM dim_cliente dc
                LEFT JOIN fact_ventas fv ON dc.cliente_sk = fv.cliente_sk
                LEFT JOIN dim_tiempo dt ON fv.tiempo_sk = dt.tiempo_sk
                WHERE dc.es_actual = 1
                GROUP BY dc.cliente_sk, dc.cliente_nombre, dc.segmento, dc.email, dc.ciudad, dc.pais, dc.activo
                HAVING total_ventas > 0
                ORDER BY total_ventas DESC
                LIMIT 300
            ";
            
            $client_data_raw = $db->query($client_data_query)->getResultArray();
            
            // Procesar datos de clientes
            $client_data = [];
            foreach ($client_data_raw as $row) {
                $total_ventas = floatval($row['total_ventas']);
                $total_margen = floatval($row['total_margen']);
                $transacciones = intval($row['transacciones']);
                
                $client_data[] = [
                    'cliente_sk' => $row['cliente_sk'],
                    'cliente_nombre' => $row['cliente_nombre'] ?: 'Cliente ' . $row['cliente_sk'],
                    'segmento' => $row['segmento'] ?: 'Sin Segmento',
                    'email' => $row['email'] ?: '',
                    'ciudad' => $row['ciudad'] ?: '',
                    'pais' => $row['pais'] ?: '',
                    'activo' => $row['activo'],
                    'total_ventas' => $total_ventas,
                    'total_margen' => $total_margen,
                    'margen_porcentaje' => $total_ventas > 0 ? ($total_margen / $total_ventas) * 100 : 0,
                    'transacciones' => $transacciones,
                    'ordenes_unicas' => intval($row['ordenes_unicas']),
                    'ticket_promedio' => floatval($row['ticket_promedio']),
                    'margen_promedio' => floatval($row['margen_promedio']),
                    'dias_activos' => intval($row['dias_activos']),
                    'ultima_compra' => $row['ultima_compra'] ?: 'N/A',
                    'frecuencia_compra' => $transacciones / max(1, intval($row['dias_activos'])),
                    'items_por_orden' => intval($row['ordenes_unicas']) > 0 ? $transacciones / intval($row['ordenes_unicas']) : 0
                ];
            }
            
            // Estadísticas generales
            $stats_query = "
                SELECT 
                    COUNT(DISTINCT dc.cliente_sk) as total_clients,
                    COUNT(DISTINCT CASE WHEN dc.activo = 1 THEN dc.cliente_sk END) as active_clients,
                    COALESCE(SUM(fv.monto_linea), 0) as total_sales,
                    COALESCE(SUM(fv.margen_monto), 0) as total_margin,
                    COALESCE(AVG(fv.monto_linea), 0) as avg_ticket,
                    COALESCE(AVG(fv.margen_monto), 0) as avg_margin
                FROM dim_cliente dc
                LEFT JOIN fact_ventas fv ON dc.cliente_sk = fv.cliente_sk
                WHERE dc.es_actual = 1
            ";
            
            $stats_result = $db->query($stats_query)->getRowArray();
            
            // Análisis de segmentación por margen
            $margin_analysis = [];
            if (!empty($client_data)) {
                $margins = array_column($client_data, 'margen_porcentaje');
                sort($margins);
                $count = count($margins);
                
                $q25 = $margins[intval($count * 0.25)];
                $q50 = $margins[intval($count * 0.50)];
                $q75 = $margins[intval($count * 0.75)];
                
                foreach ($client_data as $cliente) {
                    $margen = $cliente['margen_porcentaje'];
                    $segment = '';
                    
                    if ($margen >= $q75) {
                        $segment = 'Alto Margen';
                    } elseif ($margen >= $q50) {
                        $segment = 'Margen Medio-Alto';
                    } elseif ($margen >= $q25) {
                        $segment = 'Margen Medio-Bajo';
                    } else {
                        $segment = 'Bajo Margen';
                    }
                    
                    if (!isset($margin_analysis[$segment])) {
                        $margin_analysis[$segment] = ['count' => 0, 'total_margin' => 0];
                    }
                    $margin_analysis[$segment]['count']++;
                    $margin_analysis[$segment]['total_margin'] += $cliente['total_margen'];
                }
            }
            
            // Análisis de frecuencia de operaciones CON DETALLES DE CLIENTES
            $frequency_analysis = [];
            $frequency_details = []; // Nueva variable para almacenar detalles por segmento
            if (!empty($client_data)) {
                foreach ($client_data as $cliente) {
                    $transacciones = $cliente['transacciones'];
                    $segment = '';
                    
                    if ($transacciones >= 50) {
                        $segment = 'Muy Activo (50+ ops)';
                    } elseif ($transacciones >= 20) {
                        $segment = 'Activo (20-49 ops)';
                    } elseif ($transacciones >= 5) {
                        $segment = 'Moderado (5-19 ops)';
                    } else {
                        $segment = 'Poco Activo (1-4 ops)';
                    }
                    
                    if (!isset($frequency_analysis[$segment])) {
                        $frequency_analysis[$segment] = ['count' => 0, 'total_transactions' => 0];
                        $frequency_details[$segment] = [];
                    }
                    $frequency_analysis[$segment]['count']++;
                    $frequency_analysis[$segment]['total_transactions'] += $transacciones;
                    
                    // Guardar detalles del cliente para este segmento
                    $frequency_details[$segment][] = [
                        'cliente_sk' => $cliente['cliente_sk'],
                        'cliente_nombre' => $cliente['cliente_nombre'],
                        'segmento' => $cliente['segmento'],
                        'transacciones' => $transacciones,
                        'total_ventas' => $cliente['total_ventas'],
                        'total_margen' => $cliente['total_margen'],
                        'ultima_compra' => $cliente['ultima_compra']
                    ];
                }
            }
            
            // Análisis de clientes "CAÍDOS" (sin compras en +10 días) - VERSIÓN FINAL
            $clientes_caidos = [];
            $caidos_stats = [
                'total_caidos' => 0,
                'ventas_perdidas_potencial' => 0,
                'promedio_dias_sin_compra' => 0
            ];
            
            try {
                $caidos_query = "
                    WITH clientes_con_ultima_compra AS (
                        SELECT 
                            dc.cliente_sk,
                            dc.cliente_nombre,
                            dc.segmento,
                            dc.email,
                            MAX(dt.fecha_natural) as ultima_compra,
                            COUNT(fv.venta_sk) as total_compras_historicas,
                            SUM(fv.monto_linea) as total_ventas_historicas
                        FROM dim_cliente dc
                        INNER JOIN fact_ventas fv ON dc.cliente_sk = fv.cliente_sk
                        INNER JOIN dim_tiempo dt ON fv.tiempo_sk = dt.tiempo_sk
                        WHERE dc.es_actual = 1
                        GROUP BY dc.cliente_sk, dc.cliente_nombre, dc.segmento, dc.email
                    )
                    SELECT 
                        cliente_sk,
                        cliente_nombre,
                        COALESCE(segmento, 'Sin Segmento') as segmento,
                        COALESCE(email, '') as email,
                        ultima_compra,
                        total_compras_historicas,
                        total_ventas_historicas,
                        julianday('now') - julianday(ultima_compra) as dias_sin_compra
                    FROM clientes_con_ultima_compra
                    WHERE julianday('now') - julianday(ultima_compra) > 10 
                        AND total_compras_historicas > 0
                    ORDER BY total_ventas_historicas DESC
                    LIMIT 100
                ";
                
                $caidos_result = $db->query($caidos_query)->getResultArray();
                
                if (!empty($caidos_result)) {
                    $total_dias = 0;
                    foreach ($caidos_result as $caido) {
                        $dias_sin_compra = intval($caido['dias_sin_compra']);
                        $ventas_historicas = floatval($caido['total_ventas_historicas']);
                        
                        $clientes_caidos[] = [
                            'cliente_sk' => $caido['cliente_sk'],
                            'cliente_nombre' => $caido['cliente_nombre'],
                            'segmento' => $caido['segmento'],
                            'email' => $caido['email'],
                            'ultima_compra' => $caido['ultima_compra'],
                            'dias_sin_compra' => $dias_sin_compra,
                            'total_compras_historicas' => intval($caido['total_compras_historicas']),
                            'total_ventas_historicas' => $ventas_historicas,
                            'categoria_caido' => $dias_sin_compra > 60 ? 'Crítico' : ($dias_sin_compra > 30 ? 'Alto Riesgo' : 'Moderado')
                        ];
                        
                        $total_dias += $dias_sin_compra;
                        $caidos_stats['ventas_perdidas_potencial'] += $ventas_historicas;
                    }
                    
                    $caidos_stats['total_caidos'] = count($clientes_caidos);
                    $caidos_stats['promedio_dias_sin_compra'] = $caidos_stats['total_caidos'] > 0 ? 
                        intval($total_dias / $caidos_stats['total_caidos']) : 0;
                }
            } catch (\Exception $e) {
                log_message('error', 'Error en consulta de clientes caídos: ' . $e->getMessage());
                // Los arrays ya están inicializados con valores por defecto
            }
            
            // Segmentación por valor (usando datos reales)
            $segment_query = "
                SELECT 
                    COALESCE(dc.segmento, 'Sin Segmento') as segmento,
                    COUNT(DISTINCT dc.cliente_sk) as count,
                    COALESCE(SUM(fv.monto_linea), 0) as sales
                FROM dim_cliente dc
                LEFT JOIN fact_ventas fv ON dc.cliente_sk = fv.cliente_sk
                WHERE dc.es_actual = 1
                GROUP BY dc.segmento
                ORDER BY count DESC
            ";
            
            $segments_result = $db->query($segment_query)->getResultArray();
            
            $client_segments = [];
            $colors = ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1', '#fd7e14', '#20c997', '#6c757d', '#17a2b8', '#f8f9fa'];
            $color_index = 0;
            
            foreach ($segments_result as $segment) {
                $client_segments[$segment['segmento']] = [
                    'count' => intval($segment['count']),
                    'sales' => floatval($segment['sales']),
                    'color' => $colors[$color_index % count($colors)]
                ];
                $color_index++;
            }
            
            // Análisis de recencia basado en última compra
            $recency_query = "
                SELECT 
                    CASE 
                        WHEN dc.fecha_ultima_compra IS NULL THEN 'Sin Compras'
                        WHEN julianday('now') - julianday(dc.fecha_ultima_compra) <= 30 THEN 'Activos (< 30 días)'
                        WHEN julianday('now') - julianday(dc.fecha_ultima_compra) <= 90 THEN 'Recientes (30-90 días)'
                        WHEN julianday('now') - julianday(dc.fecha_ultima_compra) <= 180 THEN 'En Riesgo (90-180 días)'
                        ELSE 'Inactivos (> 180 días)'
                    END as recency_segment,
                    COUNT(*) as count,
                    COALESCE(SUM(fv.monto_linea), 0) as sales
                FROM dim_cliente dc
                LEFT JOIN fact_ventas fv ON dc.cliente_sk = fv.cliente_sk
                WHERE dc.es_actual = 1
                GROUP BY recency_segment
                ORDER BY 
                    CASE recency_segment
                        WHEN 'Activos (< 30 días)' THEN 1
                        WHEN 'Recientes (30-90 días)' THEN 2
                        WHEN 'En Riesgo (90-180 días)' THEN 3
                        WHEN 'Inactivos (> 180 días)' THEN 4
                        ELSE 5
                    END
            ";
            
            $recency_result = $db->query($recency_query)->getResultArray();
            
            $recency_analysis = [];
            foreach ($recency_result as $recency) {
                $recency_analysis[$recency['recency_segment']] = [
                    'count' => intval($recency['count']),
                    'sales' => floatval($recency['sales'])
                ];
            }
            
            // Distribución geográfica
            $geo_query = "
                SELECT 
                    COALESCE(NULLIF(dc.pais, ''), 'Sin Datos') as pais,
                    COUNT(*) as count
                FROM dim_cliente dc
                WHERE dc.es_actual = 1
                GROUP BY dc.pais
                ORDER BY count DESC
                LIMIT 10
            ";
            
            $geo_result = $db->query($geo_query)->getResultArray();
            
            $geographic_distribution = [];
            foreach ($geo_result as $geo) {
                $geographic_distribution[$geo['pais']] = intval($geo['count']);
            }
            
            $data = [
                'title' => 'Análisis de Clientes - Dashboard',
                'user' => auth()->user(),
                'available_years' => $available_years,
                'current_year' => $current_year,
                'client_data' => $client_data,
                'client_segments' => $client_segments,
                'margin_analysis' => $margin_analysis,
                'frequency_analysis' => $frequency_analysis,
                'frequency_details' => $frequency_details, // Nuevos detalles para interactividad
                'clientes_caidos' => $clientes_caidos, // Nuevos clientes caídos
                'caidos_stats' => $caidos_stats, // Estadísticas de clientes caídos
                'stats' => [
                    'total_clients' => intval($stats_result['total_clients']),
                    'active_clients' => intval($stats_result['active_clients']),
                    'total_sales' => floatval($stats_result['total_sales']),
                    'total_margin' => floatval($stats_result['total_margin']),
                    'avg_ticket' => floatval($stats_result['avg_ticket']),
                    'avg_margin' => floatval($stats_result['avg_margin']),
                    'margin_percentage' => floatval($stats_result['total_sales']) > 0 ? 
                        (floatval($stats_result['total_margin']) / floatval($stats_result['total_sales'])) * 100 : 0
                ]
            ];

            log_message('debug', 'DashboardController::clientes() returning view with real data');
            return view('dashboard/clientes', $data);
            
        } catch (\Exception $e) {
            log_message('error', 'Error in DashboardController::clientes(): ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            
            return view('errors/generic', [
                'message' => 'Error al cargar datos de clientes: ' . $e->getMessage() . ' (Línea: ' . $e->getLine() . ')'
            ]);
        }
    }

    /**
     * Función AJAX para filtrar clientes
     */
    public function clientesFilter()
    {
        if (!auth()->loggedIn()) {
            return $this->response->setJSON(['error' => 'No autorizado']);
        }

        $request = service('request');
        $metrica = $request->getPost('metrica') ?? 'ventas';
        $segmento = $request->getPost('segmento') ?? 'all';
        $topCount = intval($request->getPost('topCount') ?? 50);
        
        try {
            $db = \Config\Database::connect();
            
            // Construir la consulta según los filtros
            $whereClause = "WHERE dc.es_actual = 1";
            
            if ($segmento !== 'all') {
                $whereClause .= " AND LOWER(dc.segmento) = '" . strtolower($segmento) . "'";
            }
            
            // Definir el orden según la métrica
            $orderBy = "total_ventas DESC";
            switch ($metrica) {
                case 'margen':
                    $orderBy = "total_margen DESC";
                    break;
                case 'transacciones':
                    $orderBy = "transacciones DESC";
                    break;
                case 'ticket_promedio':
                    $orderBy = "ticket_promedio DESC";
                    break;
                default:
                    $orderBy = "total_ventas DESC";
                    break;
            }
            
            $client_data_query = "
                SELECT 
                    dc.cliente_sk,
                    dc.cliente_nombre,
                    dc.segmento,
                    dc.email,
                    COALESCE(SUM(fv.monto_linea), 0) as total_ventas,
                    COALESCE(SUM(fv.margen_monto), 0) as total_margen,
                    COALESCE(COUNT(fv.venta_sk), 0) as transacciones,
                    COALESCE(AVG(fv.monto_linea), 0) as ticket_promedio,
                    COALESCE(COUNT(DISTINCT fv.orden_id), 0) as ordenes_unicas,
                    MAX(dt.fecha_natural) as ultima_compra
                FROM dim_cliente dc
                LEFT JOIN fact_ventas fv ON dc.cliente_sk = fv.cliente_sk
                LEFT JOIN dim_tiempo dt ON fv.tiempo_sk = dt.tiempo_sk
                {$whereClause}
                GROUP BY dc.cliente_sk, dc.cliente_nombre, dc.segmento, dc.email
                HAVING total_ventas > 0
                ORDER BY {$orderBy}
                LIMIT {$topCount}
            ";
            
            $client_data_raw = $db->query($client_data_query)->getResultArray();
            
            // Procesar datos para el gráfico
            $chart_data = [];
            foreach ($client_data_raw as $row) {
                $value = 0;
                switch ($metrica) {
                    case 'margen':
                        $value = floatval($row['total_margen']);
                        break;
                    case 'transacciones':
                        $value = intval($row['transacciones']);
                        break;
                    case 'ticket_promedio':
                        $value = floatval($row['ticket_promedio']);
                        break;
                    default:
                        $value = floatval($row['total_ventas']);
                        break;
                }
                
                $chart_data[] = [
                    'cliente_sk' => $row['cliente_sk'],
                    'cliente_nombre' => $row['cliente_nombre'] ?: 'Cliente ' . $row['cliente_sk'],
                    'segmento' => $row['segmento'] ?: 'Sin Segmento',
                    'value' => $value,
                    'total_ventas' => floatval($row['total_ventas']),
                    'total_margen' => floatval($row['total_margen']),
                    'transacciones' => intval($row['transacciones']),
                    'ordenes_unicas' => intval($row['ordenes_unicas']),
                    'ticket_promedio' => floatval($row['ticket_promedio']),
                    'ultima_compra' => $row['ultima_compra'] ?: 'N/A'
                ];
            }
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $chart_data,
                'metrica' => $metrica,
                'segmento' => $segmento,
                'total_count' => count($chart_data)
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Error in clientesFilter: ' . $e->getMessage());
            return $this->response->setJSON([
                'error' => 'Error al filtrar datos: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Vista de análisis temporal
     */
    public function temporal()
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        log_message('debug', 'DashboardController::temporal() method called');
        
        $factVentasModel = new FactVentasModel();
        $dimTiempoModel = new DimTiempoModel();
        
        // Obtener años disponibles
        $available_years = $dimTiempoModel->distinct()->select('año')->orderBy('año', 'DESC')->findAll();
        $available_years = array_column($available_years, 'año');
        $current_year = date('Y');
        
        // Obtener datos anuales
        $yearly_data = $factVentasModel->select('dt.año as anio, SUM(fact_ventas.monto_linea) as total_ventas, COUNT(*) as transacciones, AVG(fact_ventas.monto_linea) as ticket_promedio')
                                     ->join('dim_tiempo dt', 'fact_ventas.tiempo_sk = dt.tiempo_sk')
                                     ->groupBy('dt.año')
                                     ->orderBy('dt.año', 'DESC')
                                     ->findAll();
        
        // Obtener datos mensuales
        $monthly_data = $factVentasModel->select('dt.mes, dt.nombre_mes as mes_nombre, SUM(fact_ventas.monto_linea) as total_ventas, COUNT(*) as transacciones, AVG(fact_ventas.monto_linea) as promedio_ventas')
                                      ->join('dim_tiempo dt', 'fact_ventas.tiempo_sk = dt.tiempo_sk')
                                      ->groupBy(['dt.mes', 'dt.nombre_mes'])
                                      ->orderBy('dt.mes')
                                      ->findAll();
        
        // Calcular índice estacional para datos mensuales
        if (!empty($monthly_data)) {
            $promedio_general = array_sum(array_column($monthly_data, 'total_ventas')) / count($monthly_data);
            foreach ($monthly_data as &$month) {
                $month['indice_estacional'] = $promedio_general > 0 ? $month['total_ventas'] / $promedio_general : 1;
            }
        }
        
        // Obtener datos trimestrales
        $quarterly_data = $factVentasModel->select('dt.trimestre, dt.trimestre_nombre as periodo, SUM(fact_ventas.monto_linea) as total_ventas, COUNT(*) as transacciones, SUM(fact_ventas.margen_monto) as margen')
                                        ->join('dim_tiempo dt', 'fact_ventas.tiempo_sk = dt.tiempo_sk')
                                        ->groupBy(['dt.trimestre', 'dt.trimestre_nombre'])
                                        ->orderBy('dt.trimestre')
                                        ->findAll();
        
        // Obtener datos por día de la semana
        $weekday_data = $factVentasModel->select('dt.dia_semana, dt.nombre_dia as dia_nombre, SUM(fact_ventas.monto_linea) as total_ventas, COUNT(*) as transacciones, AVG(fact_ventas.monto_linea) as promedio_ventas')
                                      ->join('dim_tiempo dt', 'fact_ventas.tiempo_sk = dt.tiempo_sk')
                                      ->groupBy(['dt.dia_semana', 'dt.nombre_dia'])
                                      ->orderBy('dt.dia_semana')
                                      ->findAll();
        
        // Calcular performance por día de la semana
        if (!empty($weekday_data)) {
            $max_ventas = max(array_column($weekday_data, 'total_ventas'));
            foreach ($weekday_data as &$day) {
                $day['performance'] = $max_ventas > 0 ? ($day['total_ventas'] / $max_ventas) * 100 : 50;
            }
        }
        
        // Calcular estadísticas temporales
        $mejor_mes_data = $factVentasModel->select('dt.nombre_mes, SUM(fact_ventas.monto_linea) as total_ventas')
                                        ->join('dim_tiempo dt', 'fact_ventas.tiempo_sk = dt.tiempo_sk')
                                        ->groupBy('dt.nombre_mes')
                                        ->orderBy('total_ventas', 'DESC')
                                        ->first();
        $mejor_mes = $mejor_mes_data ? $mejor_mes_data['nombre_mes'] : 'N/A';
        
        $mejor_dia_data = $factVentasModel->select('dt.nombre_dia, SUM(fact_ventas.monto_linea) as total_ventas')
                                        ->join('dim_tiempo dt', 'fact_ventas.tiempo_sk = dt.tiempo_sk')
                                        ->groupBy('dt.nombre_dia')
                                        ->orderBy('total_ventas', 'DESC')
                                        ->first();
        $mejor_dia_semana = $mejor_dia_data ? $mejor_dia_data['nombre_dia'] : 'N/A';
        
        // Calcular crecimiento anual (simplificado)
        $crecimiento_anual = 0;
        if (count($yearly_data) >= 2) {
            $year_current = $yearly_data[0]['total_ventas'];
            $year_previous = $yearly_data[1]['total_ventas'];
            if ($year_previous > 0) {
                $crecimiento_anual = (($year_current - $year_previous) / $year_previous) * 100;
            }
        }
        
        $temporal_stats = [
            'mejor_mes' => $mejor_mes,
            'mejor_dia_semana' => $mejor_dia_semana,
            'crecimiento_anual' => $crecimiento_anual,
            'patron_estacional' => 'Análisis estacional'
        ];
        
        log_message('debug', 'Temporal data retrieved: ' . json_encode($yearly_data));

        $data = [
            'title' => 'Análisis Temporal - Dashboard',
            'user' => auth()->user(),
            'current_year' => $current_year,
            'available_years' => $available_years,
            'yearly_data' => $yearly_data,
            'monthly_data' => $monthly_data,
            'quarterly_data' => $quarterly_data,
            'weekday_data' => $weekday_data,
            'temporal_stats' => $temporal_stats,
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => '/dashboard'],
                ['label' => 'Análisis Temporal', 'active' => true]
            ]
        ];
        
        log_message('debug', 'Data being passed to view: ' . json_encode($data));

        return view('dashboard/temporal', $data);
    }

    /**
     * Endpoint AJAX para datos de drill-down
     */
    public function ajaxDrillDown()
    {
        if (!auth()->loggedIn()) {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }

        try {
            $level = $this->request->getPost('level') ?? 'year';
            $parentId = $this->request->getPost('parent_id');
            $year = $this->request->getPost('year') ?? $this->getDefaultYear();

            $data = $this->getDrillDownData($level, $parentId, $year);
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $data,
                'level' => $level,
                'parent_id' => $parentId,
                'navigation' => $this->getDrillNavigation($level)
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en ajaxDrillDown: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error al obtener datos: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Endpoint AJAX para gráficos dinámicos
     */
    public function ajaxChartData()
    {
        if (!auth()->loggedIn()) {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }

        try {
            $chartType = $this->request->getPost('chart_type') ?? 'sales_trend';
            $period = $this->request->getPost('period') ?? 'month';
            $year = $this->request->getPost('year') ?? $this->getDefaultYear();

            $data = $this->getChartData($chartType, $period, $year);
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $data,
                'chart_type' => $chartType,
                'period' => $period
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en ajaxChartData: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error al generar gráfico: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Endpoint AJAX para datos temporales
     */
    public function ajaxTemporalData()
    {
        if (!auth()->loggedIn()) {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }

        try {
            $analysisType = $this->request->getPost('analysis_type') ?? 'trends';
            $metricType = $this->request->getPost('metric_type') ?? 'ventas';
            $comparison = $this->request->getPost('comparison') ?? 'none';
            $year = $this->request->getPost('year') ?? $this->getDefaultYear();

            $data = $this->getTemporalAnalysisData($analysisType, $metricType, $year);
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $data,
                'analysis_type' => $analysisType,
                'metric_type' => $metricType
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en ajaxTemporalData: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error al cargar datos temporales: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * AJAX endpoint para datos de clientes dinámicos
     */
    public function ajaxClientesData()
    {
        if (!auth()->loggedIn()) {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }

        try {
            $segment = $this->request->getPost('segment') ?? 'all';
            $limit = intval($this->request->getPost('limit') ?? 50);
            $orderBy = $this->request->getPost('orderBy') ?? 'total_ventas';
            $orderDir = $this->request->getPost('orderDir') ?? 'DESC';

            $factVentasModel = new FactVentasModel();
            
            // Query base para datos de clientes
            $query = $factVentasModel->select('
                cliente_sk, 
                SUM(monto_linea) as total_ventas, 
                SUM(cantidad) as cantidad_total, 
                SUM(margen_monto) as margen_total, 
                COUNT(*) as transacciones, 
                AVG(monto_linea) as ticket_promedio,
                COUNT(DISTINCT tiempo_sk) as dias_activos,
                MAX(tiempo_sk) as ultima_compra_sk
            ')
            ->groupBy('cliente_sk');

            // Aplicar filtro de segmento si no es 'all'
            if ($segment !== 'all') {
                // Aquí podrías agregar lógica de filtrado por segmento
                // Por ejemplo, filtrar por rangos de ventas
            }

            $client_data_raw = $query->orderBy($orderBy, $orderDir)
                                   ->limit($limit)
                                   ->findAll();

            // Obtener nombres de clientes
            if (!empty($client_data_raw)) {
                $cliente_sks = array_column($client_data_raw, 'cliente_sk');
                $db = \Config\Database::connect();
                
                $placeholders = str_repeat('?,', count($cliente_sks) - 1) . '?';
                $names_query = $db->query("SELECT cliente_sk, nombre, apellido, email FROM dim_cliente WHERE cliente_sk IN ($placeholders)", $cliente_sks);
                $names_result = $names_query->getResultArray();
                
                $client_names = [];
                foreach ($names_result as $name_row) {
                    $display_name = '';
                    if (!empty($name_row['nombre']) && !empty($name_row['apellido'])) {
                        $display_name = trim($name_row['nombre']) . ' ' . trim($name_row['apellido']);
                    } elseif (!empty($name_row['nombre'])) {
                        $display_name = trim($name_row['nombre']);
                    } elseif (!empty($name_row['email'])) {
                        $display_name = trim($name_row['email']);
                    } else {
                        $display_name = 'Cliente ' . $name_row['cliente_sk'];
                    }
                    $client_names[$name_row['cliente_sk']] = $display_name;
                }

                // Combinar datos
                $client_data = [];
                foreach ($client_data_raw as $row) {
                    $client_data[] = [
                        'cliente_sk' => $row['cliente_sk'],
                        'cliente_nombre' => $client_names[$row['cliente_sk']] ?? 'Cliente ' . $row['cliente_sk'],
                        'total_ventas' => floatval($row['total_ventas']),
                        'transacciones' => intval($row['transacciones']),
                        'ticket_promedio' => floatval($row['ticket_promedio']),
                        'dias_activos' => intval($row['dias_activos']),
                        'frecuencia_compra' => intval($row['transacciones']) / max(1, intval($row['dias_activos']))
                    ];
                }
            } else {
                $client_data = [];
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $client_data,
                'total_records' => count($client_data)
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en ajaxClientesData: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error al cargar datos de clientes: ' . $e->getMessage()
            ]);
        }
    }

    // ===== Métodos privados de apoyo =====

    /**
     * Obtener años disponibles en los datos
     */
    private function getAvailableYears(): array
    {
        $db = \Config\Database::connect();
        $query = $db->query('SELECT DISTINCT año FROM dim_tiempo ORDER BY año DESC');
        return array_column($query->getResultArray(), 'año');
    }

    /**
     * Obtener estadísticas resumen
     */
    private function getSummaryStats(): array
    {
        $db = \Config\Database::connect();
        
        $ventas = $db->table('fact_ventas')
            ->select([
                'COUNT(*) as total_transacciones',
                'SUM(monto_linea) as ingresos_totales',
                'AVG(monto_linea) as ticket_promedio',
                'SUM(margen_monto) as margen_total'
            ])
            ->get()->getRowArray();

        $periodos = $db->table('dim_tiempo')
            ->select([
                'MIN(año) as anio_inicio',
                'MAX(año) as anio_fin',
                'COUNT(DISTINCT año) as anios_total'
            ])
            ->get()->getRowArray();

        return array_merge($ventas, $periodos);
    }

    /**
     * Obtener datos para drill-down
     */
    private function getDrillDownData(string $level, ?string $parentId, string $year): array
    {
        $db = \Config\Database::connect();
        
        switch ($level) {
            case 'year':
                return $this->getYearlyData($year);
                
            case 'quarter':
                // Si hay parent_id, usar ese año; sino usar el parámetro year
                $yearToUse = $parentId ? $parentId : $year;
                return $this->getQuarterlyData($yearToUse);
                
            case 'month':
                $quarter = $parentId ? (int)str_replace('Q', '', $parentId) : null;
                return $this->getMonthlyData($year, $quarter);
                
            case 'week':
                $month = $parentId;
                return $this->getWeeklyData($year, $month);
                
            default:
                return [];
        }
    }

    /**
     * Datos anuales
     */
    private function getYearlyData(?string $filterYear = null): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('fact_ventas fv');
        $builder->select([
            'dt.año as id',
            'dt.año as periodo',
            'COUNT(*) as num_ventas',
            'COUNT(*) as transacciones',
            'SUM(fv.monto_linea) as total_ventas',
            'AVG(fv.monto_linea) as promedio_venta',
            'AVG(fv.monto_linea) as ticket_promedio',
            'SUM(fv.margen_monto) as margen_total',
            'SUM(fv.margen_monto) as margen'
        ]);
        $builder->join('dim_tiempo dt', 'fv.tiempo_sk = dt.tiempo_sk');
        
        // Filtrar por año específico si se proporciona
        if ($filterYear) {
            $builder->where('dt.año', $filterYear);
        }
        
        $builder->groupBy('dt.año');
        $builder->orderBy('dt.año', 'DESC');        $results = $builder->get()->getResultArray();
        
        // Añadir tendencia (simplificada)
        foreach ($results as $index => &$row) {
            $row['tendencia'] = $index > 0 ? rand(-10, 15) : 0; // Placeholder para tendencia
        }
        
        return $results;
    }

    /**
     * Datos trimestrales
     */
    private function getQuarterlyData(string $year): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('fact_ventas fv');
        $builder->select([
            'dt.trimestre as id',
            'dt.trimestre_nombre as periodo',
            'COUNT(*) as num_ventas',
            'COUNT(*) as transacciones',
            'SUM(fv.monto_linea) as total_ventas',
            'AVG(fv.monto_linea) as promedio_venta',
            'AVG(fv.monto_linea) as ticket_promedio',
            'SUM(fv.margen_monto) as margen_total',
            'SUM(fv.margen_monto) as margen'
        ]);
        $builder->join('dim_tiempo dt', 'fv.tiempo_sk = dt.tiempo_sk');
        $builder->where('dt.año', $year);
        $builder->groupBy(['dt.trimestre', 'dt.trimestre_nombre']);
        $builder->orderBy('dt.trimestre');
        
        $results = $builder->get()->getResultArray();
        
        // Añadir tendencia
        foreach ($results as $index => &$row) {
            $row['tendencia'] = $index > 0 ? rand(-8, 12) : 0;
        }
        
        return $results;
    }

    /**
     * Datos mensuales
     */
    private function getMonthlyData(string $year, ?int $quarter = null): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('fact_ventas fv');
        $builder->select([
            'dt.mes as id',
            'dt.nombre_mes as periodo',
            'COUNT(*) as num_ventas',
            'COUNT(*) as transacciones',
            'SUM(fv.monto_linea) as total_ventas',
            'AVG(fv.monto_linea) as promedio_venta',
            'AVG(fv.monto_linea) as ticket_promedio',
            'SUM(fv.margen_monto) as margen_total',
            'SUM(fv.margen_monto) as margen'
        ]);
        $builder->join('dim_tiempo dt', 'fv.tiempo_sk = dt.tiempo_sk');
        $builder->where('dt.año', $year);
        
        if ($quarter) {
            $builder->where('dt.trimestre', $quarter);
        }
        
        $builder->groupBy(['dt.mes', 'dt.nombre_mes']);
        $builder->orderBy('dt.mes');
        
        $results = $builder->get()->getResultArray();
        
        // Añadir tendencia
        foreach ($results as $index => &$row) {
            $row['tendencia'] = $index > 0 ? rand(-5, 10) : 0;
        }
        
        return $results;
    }

    /**
     * Datos semanales
     */
    private function getWeeklyData(string $year, ?string $month = null): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('fact_ventas fv');
        $builder->select([
            'dt.semana',
            'COUNT(*) as num_ventas',
            'SUM(fv.monto_linea) as total_ventas',
            'AVG(fv.monto_linea) as promedio_venta',
            'SUM(fv.margen_monto) as margen_total'
        ]);
        $builder->join('dim_tiempo dt', 'fv.tiempo_sk = dt.tiempo_sk');
        $builder->where('dt.año', $year);
        
        if ($month) {
            $builder->where('dt.mes', $month);
        }
        
        $builder->groupBy('dt.semana');
        $builder->orderBy('dt.semana');
        
        return $builder->get()->getResultArray();
    }

    /**
     * Construir breadcrumb de navegación
     */
    private function buildBreadcrumb(string $level, ?string $parentId, string $year): array
    {
        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => '/dashboard'],
            ['label' => 'Ventas', 'url' => '/dashboard/ventas']
        ];

        switch ($level) {
            case 'year':
                $breadcrumb[] = ['label' => 'Por Año', 'active' => true];
                break;
            case 'quarter':
                $breadcrumb[] = ['label' => $year, 'url' => '/dashboard/ventas?level=year'];
                $breadcrumb[] = ['label' => 'Por Trimestre', 'active' => true];
                break;
            case 'month':
                $breadcrumb[] = ['label' => $year, 'url' => '/dashboard/ventas?level=year'];
                if ($parentId) {
                    $breadcrumb[] = ['label' => $parentId, 'url' => "/dashboard/ventas?level=quarter&year=$year"];
                }
                $breadcrumb[] = ['label' => 'Por Mes', 'active' => true];
                break;
        }

        return $breadcrumb;
    }

    /**
     * Opciones de navegación drill
     */
    private function getDrillNavigation(string $currentLevel): array
    {
        $navigation = [
            'can_drill_down' => false,
            'can_drill_up' => false,
            'next_level' => null,
            'prev_level' => null
        ];

        switch ($currentLevel) {
            case 'year':
                $navigation['can_drill_down'] = true;
                $navigation['next_level'] = 'quarter';
                break;
            case 'quarter':
                $navigation['can_drill_down'] = true;
                $navigation['can_drill_up'] = true;
                $navigation['next_level'] = 'month';
                $navigation['prev_level'] = 'year';
                break;
            case 'month':
                $navigation['can_drill_up'] = true;
                $navigation['prev_level'] = 'quarter';
                break;
        }

        return $navigation;
    }

    /**
     * Obtener datos para gráficos
     */
    private function getChartData(string $chartType, string $period, string $year): array
    {
        switch ($chartType) {
            case 'sales_trend':
                return $this->getSalesTrendData($period, $year);
            case 'top_products':
                return $this->getTopProductos();
            case 'channel_distribution':
                return $this->getChannelDistributionData($year);
            case 'margin_analysis':
                return $this->getMarginAnalysisData($period, $year);
            default:
                return [];
        }
    }

    /**
     * Datos de tendencia de ventas
     */
    private function getSalesTrendData(string $period, string $year): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('fact_ventas fv');
        
        switch ($period) {
            case 'month':
                $builder->select([
                    'dt.mes',
                    'dt.nombre_mes as periodo',
                    'SUM(fv.monto_linea) as total'
                ]);
                $builder->groupBy(['dt.mes', 'dt.nombre_mes']);
                $builder->orderBy('dt.mes');
                break;
            case 'quarter':
                $builder->select([
                    'dt.trimestre',
                    'dt.trimestre_nombre as periodo',
                    'SUM(fv.monto_linea) as total'
                ]);
                $builder->groupBy(['dt.trimestre', 'dt.trimestre_nombre']);
                $builder->orderBy('dt.trimestre');
                break;
        }
        
        $builder->join('dim_tiempo dt', 'fv.tiempo_sk = dt.tiempo_sk');
        $builder->where('dt.año', $year);
        
        return $builder->get()->getResultArray();
    }

    /**
     * Top productos
     */
    private function getTopProductos(): array
    {
        $db = \Config\Database::connect();
        return $db->table('fact_ventas')
            ->select([
                'producto_sk',
                'COUNT(*) as num_ventas',
                'SUM(monto_linea) as total_ventas',
                'SUM(margen_monto) as margen_total'
            ])
            ->groupBy('producto_sk')
            ->orderBy('total_ventas', 'DESC')
            ->limit(10)
            ->get()->getResultArray();
    }

    /**
     * Estadísticas de productos
     */
    private function getProductosStats(): array
    {
        $db = \Config\Database::connect();
        return $db->table('fact_ventas')
            ->select([
                'COUNT(DISTINCT producto_sk) as productos_unicos',
                'AVG(precio_unitario) as precio_promedio',
                'MAX(precio_unitario) as precio_maximo',
                'MIN(precio_unitario) as precio_minimo'
            ])
            ->get()->getRowArray();
    }

    /**
     * Estadísticas temporales
     */
    private function getTemporalStats(): array
    {
        $db = \Config\Database::connect();
        
        // Ventas por día de la semana
        $builder = $db->table('fact_ventas fv');
        $porDiaSemana = $builder->select([
            'dt.nombre_dia',
            'COUNT(*) as num_ventas',
            'SUM(fv.monto_linea) as total_ventas'
        ])
        ->join('dim_tiempo dt', 'fv.tiempo_sk = dt.tiempo_sk')
        ->groupBy('dt.nombre_dia')
        ->orderBy('dt.dia_semana')
        ->get()->getResultArray();

        // Ventas por mes
        $builder = $db->table('fact_ventas fv');
        $porMes = $builder->select([
            'dt.nombre_mes',
            'COUNT(*) as num_ventas',
            'SUM(fv.monto_linea) as total_ventas'
        ])
        ->join('dim_tiempo dt', 'fv.tiempo_sk = dt.tiempo_sk')
        ->groupBy('dt.nombre_mes')
        ->orderBy('dt.mes')
        ->get()->getResultArray();

        return [
            'por_dia_semana' => $porDiaSemana,
            'por_mes' => $porMes
        ];
    }

    /**
     * Distribución por canal
     */
    private function getChannelDistributionData(string $year): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('fact_ventas fv');
        $builder->select([
            'fv.canal_venta',
            'COUNT(*) as num_ventas',
            'SUM(fv.monto_linea) as total_ventas'
        ]);
        $builder->join('dim_tiempo dt', 'fv.tiempo_sk = dt.tiempo_sk');
        $builder->where('dt.año', $year);
        $builder->groupBy('fv.canal_venta');
        
        return $builder->get()->getResultArray();
    }

    /**
     * Obtener análisis de márgenes
     */
    private function getMarginAnalysisData(string $period, string $year): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('fact_ventas fv');
        
        if ($period === 'month') {
            $builder->select([
                'dt.nombre_mes as periodo',
                'SUM(fv.monto_linea) as ventas',
                'SUM(fv.margen_monto) as margen',
                'AVG(fv.margen_monto / fv.monto_linea * 100) as porcentaje_margen'
            ]);
            $builder->groupBy('dt.nombre_mes');
            $builder->orderBy('dt.mes');
        }
        
        $builder->join('dim_tiempo dt', 'fv.tiempo_sk = dt.tiempo_sk');
        $builder->where('dt.año', $year);
        
        return $builder->get()->getResultArray();
    }

    /**
     * Obtener datos de análisis temporal para AJAX
     */
    private function getTemporalAnalysisData(string $analysisType, string $metricType, string $year): array
    {
        $db = \Config\Database::connect();
        $factVentasModel = new FactVentasModel();
        
        switch ($analysisType) {
            case 'trends':
                return $this->getYearlyTrendsData($metricType, $year);
            case 'seasonal':
                return $this->getSeasonalData($metricType, $year);
            case 'weekday':
                return $this->getWeekdayAnalysisData($metricType, $year);
            case 'monthly':
                return $this->getMonthlyAnalysisData($metricType, $year);
            default:
                return [];
        }
    }

    /**
     * Datos de tendencias anuales
     */
    private function getYearlyTrendsData(string $metricType, string $year): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('fact_ventas fv');
        
        $selectFields = ['dt.año as periodo'];
        switch ($metricType) {
            case 'ventas':
                $selectFields[] = 'SUM(fv.monto_linea) as valor';
                break;
            case 'transacciones':
                $selectFields[] = 'COUNT(*) as valor';
                break;
            case 'margen':
                $selectFields[] = 'SUM(fv.margen_monto) as valor';
                break;
            case 'ticket_promedio':
                $selectFields[] = 'AVG(fv.monto_linea) as valor';
                break;
        }
        
        $builder->select($selectFields);
        $builder->join('dim_tiempo dt', 'fv.tiempo_sk = dt.tiempo_sk');
        $builder->groupBy('dt.año');
        $builder->orderBy('dt.año');
        
        if ($year !== 'all') {
            $builder->where('dt.año', $year);
        }
        
        return $builder->get()->getResultArray();
    }

    /**
     * Datos estacionales
     */
    private function getSeasonalData(string $metricType, string $year): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('fact_ventas fv');
        
        $selectFields = ['dt.nombre_mes as periodo'];
        switch ($metricType) {
            case 'ventas':
                $selectFields[] = 'SUM(fv.monto_linea) as valor';
                break;
            case 'transacciones':
                $selectFields[] = 'COUNT(*) as valor';
                break;
            case 'margen':
                $selectFields[] = 'SUM(fv.margen_monto) as valor';
                break;
            case 'ticket_promedio':
                $selectFields[] = 'AVG(fv.monto_linea) as valor';
                break;
        }
        
        $builder->select($selectFields);
        $builder->join('dim_tiempo dt', 'fv.tiempo_sk = dt.tiempo_sk');
        $builder->groupBy(['dt.mes', 'dt.nombre_mes']);
        $builder->orderBy('dt.mes');
        
        if ($year !== 'all') {
            $builder->where('dt.año', $year);
        }
        
        return $builder->get()->getResultArray();
    }

    /**
     * Análisis por día de la semana
     */
    private function getWeekdayAnalysisData(string $metricType, string $year): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('fact_ventas fv');
        
        $selectFields = ['dt.nombre_dia as periodo'];
        switch ($metricType) {
            case 'ventas':
                $selectFields[] = 'SUM(fv.monto_linea) as valor';
                break;
            case 'transacciones':
                $selectFields[] = 'COUNT(*) as valor';
                break;
            case 'margen':
                $selectFields[] = 'SUM(fv.margen_monto) as valor';
                break;
            case 'ticket_promedio':
                $selectFields[] = 'AVG(fv.monto_linea) as valor';
                break;
        }
        
        $builder->select($selectFields);
        $builder->join('dim_tiempo dt', 'fv.tiempo_sk = dt.tiempo_sk');
        $builder->groupBy(['dt.dia_semana', 'dt.nombre_dia']);
        $builder->orderBy('dt.dia_semana');
        
        if ($year !== 'all') {
            $builder->where('dt.año', $year);
        }
        
        return $builder->get()->getResultArray();
    }

    /**
     * Análisis mensual
     */
    private function getMonthlyAnalysisData(string $metricType, string $year): array
    {
        return $this->getSeasonalData($metricType, $year);
    }

    /**
     * Calcular estadísticas del nivel actual
     */
    private function calculateLevelStats(array $drillData): array
    {
        if (empty($drillData)) {
            return [
                'total_ventas' => 0,
                'total_transacciones' => 0,
                'ticket_promedio' => 0,
                'margen_total' => 0
            ];
        }

        $totalVentas = array_sum(array_column($drillData, 'total_ventas'));
        $totalTransacciones = array_sum(array_column($drillData, 'num_ventas'));
        $totalMargen = array_sum(array_column($drillData, 'margen_total'));
        
        return [
            'total_ventas' => $totalVentas,
            'total_transacciones' => $totalTransacciones,
            'ticket_promedio' => $totalTransacciones > 0 ? $totalVentas / $totalTransacciones : 0,
            'margen_total' => $totalMargen
        ];
    }

    /**
     * Obtener etiqueta del período actual
     */
    private function getCurrentPeriodLabel(string $level, ?string $parentId, string $year): string
    {
        switch ($level) {
            case 'year':
                return 'Años';
            case 'quarter':
                return "Trimestres de $year";
            case 'month':
                if ($parentId) {
                    return "Meses del $parentId $year";
                }
                return "Meses de $year";
            case 'week':
                return "Semanas";
            default:
                return '';
        }
    }

    /**
     * Obtener el nivel padre para drill-up
     */
    private function getParentLevel(string $currentLevel): ?string
    {
        switch ($currentLevel) {
            case 'quarter':
                return 'year';
            case 'month':
                return 'quarter';
            case 'week':
                return 'month';
            default:
                return null;
        }
    }

    /**
     * Obtener el año por defecto con datos disponibles
     */
    private function getDefaultYear(): string
    {
        $availableYears = $this->getAvailableYears();
        if (!empty($availableYears)) {
            // Primero intentar el año actual
            $currentYear = date('Y');
            if (in_array($currentYear, $availableYears)) {
                return $currentYear;
            }
            // Si no hay datos del año actual, usar el más reciente
            return $availableYears[0]; // Ya está ordenado DESC
        }
        
        // Fallback al año actual si no hay datos
        return date('Y');
    }

    /**
     * Segmenta clientes por valor (RFM simplificado)
     */
    private function segmentClients(array $clientData): array
    {
        if (empty($clientData)) {
            return [];
        }
        
        // Calcular quartiles para segmentación
        $values = array_column($clientData, 'total_ventas');
        sort($values);
        $count = count($values);
        
        $q1 = $values[intval($count * 0.25)];
        $q2 = $values[intval($count * 0.50)];
        $q3 = $values[intval($count * 0.75)];
        
        $segments = [
            'VIP' => ['count' => 0, 'sales' => 0],
            'Alto Valor' => ['count' => 0, 'sales' => 0],
            'Medio' => ['count' => 0, 'sales' => 0],
            'Bajo Valor' => ['count' => 0, 'sales' => 0]
        ];
        
        foreach ($clientData as $client) {
            $value = $client['total_ventas'];
            $segment = '';
            
            if ($value >= $q3) {
                $segment = 'VIP';
            } elseif ($value >= $q2) {
                $segment = 'Alto Valor';
            } elseif ($value >= $q1) {
                $segment = 'Medio';
            } else {
                $segment = 'Bajo Valor';
            }
            
            $segments[$segment]['count']++;
            $segments[$segment]['sales'] += $value;
        }
        
        return $segments;
    }

    /**
     * Análisis de recencia de clientes
     */
    private function getClientRecencyAnalysis(): array
    {
        $db = \Config\Database::connect();
        
        // Obtener última compra por cliente
        $query = $db->query("
            SELECT 
                cliente_sk,
                MAX(dt.fecha_natural) as ultima_compra,
                julianday('now') - julianday(MAX(dt.fecha_natural)) as dias_sin_compra,
                COUNT(*) as total_compras,
                SUM(monto_linea) as total_gastado
            FROM fact_ventas fv 
            JOIN dim_tiempo dt ON fv.tiempo_sk = dt.tiempo_sk 
            GROUP BY cliente_sk 
            ORDER BY dias_sin_compra ASC
        ");
        
        $recency_data = $query->getResultArray();
        
        // Segmentar por recencia
        $segments = [
            'Activos (< 30 días)' => ['count' => 0, 'sales' => 0],
            'Recientes (30-90 días)' => ['count' => 0, 'sales' => 0],
            'En Riesgo (90-180 días)' => ['count' => 0, 'sales' => 0],
            'Inactivos (> 180 días)' => ['count' => 0, 'sales' => 0]
        ];
        
        foreach ($recency_data as $client) {
            $days = intval($client['dias_sin_compra']);
            $sales = floatval($client['total_gastado']);
            
            if ($days < 30) {
                $segments['Activos (< 30 días)']['count']++;
                $segments['Activos (< 30 días)']['sales'] += $sales;
            } elseif ($days < 90) {
                $segments['Recientes (30-90 días)']['count']++;
                $segments['Recientes (30-90 días)']['sales'] += $sales;
            } elseif ($days < 180) {
                $segments['En Riesgo (90-180 días)']['count']++;
                $segments['En Riesgo (90-180 días)']['sales'] += $sales;
            } else {
                $segments['Inactivos (> 180 días)']['count']++;
                $segments['Inactivos (> 180 días)']['sales'] += $sales;
            }
        }
        
        return $segments;
    }
}