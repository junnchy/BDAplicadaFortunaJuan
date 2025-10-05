<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Dashboard - ETL Data Warehouse' ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --info-color: #0dcaf0;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --dark-color: #212529;
            --light-color: #f8f9fa;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            border-radius: 8px;
            margin: 2px 0;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .stat-card .card-body {
            padding: 1.5rem;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .chart-container {
            position: relative;
            height: 400px;
            margin: 20px 0;
        }

        .breadcrumb-custom {
            background: none;
            padding: 0;
            margin-bottom: 1rem;
        }

        .breadcrumb-custom .breadcrumb-item a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .breadcrumb-custom .breadcrumb-item a:hover {
            text-decoration: underline;
        }

        .drill-controls {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: var(--primary-color);
            color: white;
            border: none;
        }

        .btn-drill {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 5px;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 2rem;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
            }
            
            .stat-number {
                font-size: 2rem;
            }
            
            .chart-container {
                height: 300px;
            }
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 1.25rem;
        }

        .user-info {
            color: var(--secondary-color);
            font-size: 0.9rem;
        }

        .alert-custom {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/dashboard">
                <i class="fas fa-chart-line me-2"></i>
                ETL Data Warehouse
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i>
                        <?= esc($user->username ?? 'Usuario') ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/dashboard/profile">
                            <i class="fas fa-user-edit me-2"></i>Perfil
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/logout">
                            <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?= current_url() === site_url('dashboard') ? 'active' : '' ?>" href="/dashboard">
                                <i class="fas fa-home me-2"></i>
                                Dashboard Principal
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos(current_url(), 'dashboard/ventas') !== false ? 'active' : '' ?>" href="/dashboard/ventas">
                                <i class="fas fa-chart-bar me-2"></i>
                                Análisis de Ventas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos(current_url(), 'dashboard/productos') !== false ? 'active' : '' ?>" href="/dashboard/productos">
                                <i class="fas fa-box me-2"></i>
                                Análisis de Productos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos(current_url(), 'dashboard/temporal') !== false ? 'active' : '' ?>" href="/dashboard/temporal">
                                <i class="fas fa-calendar-alt me-2"></i>
                                Análisis Temporal
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted text-uppercase">
                                <span>Herramientas</span>
                            </h6>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/api/public/health">
                                <i class="fas fa-heartbeat me-2"></i>
                                Estado del Sistema
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos(current_url(), 'dashboard/admin/database') !== false ? 'active' : '' ?>" href="/dashboard/admin/database">
                                <i class="fas fa-database me-2"></i>
                                Administrador BD
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="refreshData()">
                                <i class="fas fa-sync-alt me-2"></i>
                                Actualizar ETL
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="pt-3 pb-2 mb-3">
                    <!-- Breadcrumb -->
                    <?php if (isset($breadcrumb) && !empty($breadcrumb)): ?>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-custom">
                            <?php foreach ($breadcrumb as $item): ?>
                                <?php if (isset($item['active']) && $item['active']): ?>
                                    <li class="breadcrumb-item active"><?= esc($item['label']) ?></li>
                                <?php else: ?>
                                    <li class="breadcrumb-item">
                                        <a href="<?= esc($item['url']) ?>"><?= esc($item['label']) ?></a>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ol>
                    </nav>
                    <?php endif; ?>

                    <!-- Page Content -->
                    <?= $this->renderSection('content') ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Configuración global
        const API_BASE_URL = '<?= site_url('api') ?>';
        const DASHBOARD_BASE_URL = '<?= site_url('dashboard') ?>';
        
        // Utilidades globales
        function formatCurrency(amount) {
            return new Intl.NumberFormat('es-ES', {
                style: 'currency',
                currency: 'USD'
            }).format(amount);
        }

        function formatNumber(number) {
            return new Intl.NumberFormat('es-ES').format(number);
        }

        function showLoading(containerId) {
            const container = document.getElementById(containerId);
            if (container) {
                container.innerHTML = `
                    <div class="loading">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2">Cargando datos...</p>
                    </div>
                `;
            }
        }

        function hideLoading() {
            const loadingElements = document.querySelectorAll('.loading');
            loadingElements.forEach(el => el.style.display = 'none');
        }

        function showError(message, containerId = null) {
            const errorHtml = `
                <div class="alert alert-danger alert-custom" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ${message}
                </div>
            `;
            
            if (containerId) {
                document.getElementById(containerId).innerHTML = errorHtml;
            } else {
                // Mostrar en toast o modal
                console.error(message);
            }
        }

        function refreshData() {
            if (confirm('¿Desea actualizar los datos del ETL? Esto puede tomar unos minutos.')) {
                fetch('/api/etl/trigger', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('ETL iniciado correctamente');
                        location.reload();
                    } else {
                        alert('Error al iniciar ETL: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error de conexión');
                });
            }
        }

        // Funciones de drill-down
        function drillDown(level, parentId = null, year = null) {
            const currentYear = year || new Date().getFullYear();
            const url = `${DASHBOARD_BASE_URL}/ventas?level=${level}&parent_id=${parentId}&year=${currentYear}`;
            window.location.href = url;
        }

        function drillUp(level, parentId = null, year = null) {
            let targetLevel = 'year';
            
            switch(level) {
                case 'month':
                    targetLevel = 'quarter';
                    break;
                case 'quarter':
                    targetLevel = 'year';
                    break;
            }
            
            drillDown(targetLevel, null, year);
        }

        // Inicialización
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar tooltips de Bootstrap
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>

    <?= $this->renderSection('scripts') ?>
</body>
</html>