<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/welcome', 'Home::welcome'); // Página original de bienvenida

// Rutas de autenticación personalizadas
$routes->get('/login', 'AuthController::login');
$routes->post('/login', 'AuthController::login');
$routes->get('/logout', 'AuthController::logout');
$routes->get('/register', 'AuthController::register');

service('auth')->routes($routes);

// Dashboard Routes - Protegidas por autenticación
$routes->group('dashboard', ['filter' => 'session'], static function ($routes) {
    $routes->get('/', 'DashboardController::index');
    $routes->get('ventas', 'DashboardController::ventas');
    $routes->get('productos', 'DashboardController::productos');
    $routes->get('temporal', 'DashboardController::temporal');
    
    // AJAX endpoints para dashboard
    $routes->post('ajaxChartData', 'DashboardController::ajaxChartData');
    $routes->post('ajaxTemporalData', 'DashboardController::ajaxTemporalData');
    $routes->post('ajaxDrillDown', 'DashboardController::ajaxDrillDown');
    
    // Administrador de Base de Datos
    $routes->group('admin', static function ($routes) {
        $routes->get('database', 'DatabaseAdminController::index');
        $routes->get('database/table/(:segment)', 'DatabaseAdminController::table/$1');
        $routes->get('database/export/(:segment)', 'DatabaseAdminController::exportTable/$1');
        $routes->match(['get', 'post'], 'database/query', 'DatabaseAdminController::query');
        $routes->get('database/count/(:segment)', 'DatabaseAdminController::count/$1');
        $routes->get('database/stats', 'DatabaseAdminController::stats');
    });
});

// API Routes - Protegidas por autenticación
$routes->group('api', ['filter' => 'session'], static function ($routes) {
    
    // API de Ventas
    $routes->group('ventas', static function ($routes) {
        $routes->get('/', 'Api\VentasController::index');
        $routes->get('dashboard', 'Api\VentasController::dashboard');
        $routes->get('drill-down', 'Api\VentasController::drillDown');
        $routes->get('export', 'Api\VentasController::export');
    });
    
    // API de Ventas Simple (para testing)
    $routes->get('ventas-simple', 'Api\VentasSimpleController::index');
    $routes->get('ventas-simple/stats', 'Api\VentasSimpleController::stats');
    
    // API de Dimensiones Simple (para testing)
    $routes->get('dimensiones-simple/tiempo', 'Api\DimensionesSimpleController::tiempo');
    $routes->get('dimensiones-simple/resumen', 'Api\DimensionesSimpleController::resumen');
    
    // API de Dimensiones
    $routes->group('dimensiones', static function ($routes) {
        $routes->get('tiempo', 'Api\DimensionesController::tiempo');
        $routes->get('clientes', 'Api\DimensionesController::clientes');
        $routes->get('productos', 'Api\DimensionesController::productos');
        $routes->get('vendedores', 'Api\DimensionesController::vendedores');
        $routes->get('sucursales', 'Api\DimensionesController::sucursales');
    });
    
    // API de Reportes
    $routes->group('reportes', static function ($routes) {
        $routes->get('resumen-ejecutivo', 'Api\ReportesController::resumenEjecutivo');
        $routes->get('tendencias', 'Api\ReportesController::tendencias');
        $routes->get('comparativo', 'Api\ReportesController::comparativo');
        $routes->get('rankings', 'Api\ReportesController::rankings');
    });
    
    // API de ETL - Para monitoreo
    $routes->group('etl', static function ($routes) {
        $routes->get('status', 'Api\EtlController::status');
        $routes->get('runs', 'Api\EtlController::runs');
        $routes->get('runs/(:num)', 'Api\EtlController::runDetail/$1');
        $routes->post('trigger', 'Api\EtlController::trigger');
        $routes->get('logs', 'Api\EtlController::logs');
    });
});

// API Routes públicas (sin autenticación)
$routes->group('api/public', static function ($routes) {
    $routes->get('health', 'Api\HealthController::check');
    $routes->get('version', 'Api\HealthController::version');
    $routes->post('login', 'Api\AuthController::login');
    $routes->post('logout', 'Api\AuthController::logout');
});
