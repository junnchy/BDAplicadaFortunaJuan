<?= $this->extend('layout/dashboard') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-chart-line me-2"></i>
        Dashboard Principal
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-outline-secondary" onclick="location.reload()">
                <i class="fas fa-sync-alt me-1"></i>
                Actualizar
            </button>
        </div>
    </div>
</div>

<!-- Tarjetas de Estadísticas Resumen -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            Total Transacciones
                        </div>
                        <div class="stat-number" id="total-transacciones">
                            <?= number_format($summary_stats['total_transacciones'] ?? 0) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-receipt fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            Ingresos Totales
                        </div>
                        <div class="stat-number" id="ingresos-totales">
                            $<?= number_format($summary_stats['ingresos_totales'] ?? 0, 2) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            Ticket Promedio
                        </div>
                        <div class="stat-number" id="ticket-promedio">
                            $<?= number_format($summary_stats['ticket_promedio'] ?? 0, 2) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-bar fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            Margen Total
                        </div>
                        <div class="stat-number" id="margen-total">
                            $<?= number_format($summary_stats['margen_total'] ?? 0, 2) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-pie fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Controles de Período -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-filter me-2"></i>
                    Filtros y Controles
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <label for="yearSelect" class="form-label">Año:</label>
                                                <select class="form-select" id="yearSelect">
                            <option value="2023">2023</option>
                            <option value="2024" selected>2024</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="chartType" class="form-label">Tipo de Gráfico:</label>
                        <select class="form-select" id="chartType">
                            <option value="sales_trend">Tendencia de Ventas</option>
                            <option value="top_products">Top Productos</option>
                            <option value="channel_distribution">Distribución por Canal</option>
                            <option value="margin_analysis">Análisis de Márgenes</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="periodSelect" class="form-label">Período:</label>
                                                <select class="form-select" id="periodSelect">
                            <option value="month">Mensual</option>
                            <option value="quarter">Trimestral</option>
                            <option value="year">Anual</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-primary w-100" onclick="updateDashboard()">
                            <i class="fas fa-sync-alt me-1"></i>
                            Actualizar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos Principales -->
<div class="row mb-4">
    <!-- Gráfico Principal -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0" id="chart-title">
                    <i class="fas fa-chart-line me-2"></i>
                    Tendencia de Ventas
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="mainChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel de Acciones Rápidas -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-bolt me-2"></i>
                    Acciones Rápidas
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="/dashboard/ventas" class="btn btn-outline-primary">
                        <i class="fas fa-chart-bar me-2"></i>
                        Análisis de Ventas
                    </a>
                    <a href="/dashboard/productos" class="btn btn-outline-success">
                        <i class="fas fa-box me-2"></i>
                        Análisis de Productos
                    </a>
                    <a href="/dashboard/temporal" class="btn btn-outline-info">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Análisis Temporal
                    </a>
                    <hr>
                    <button class="btn btn-outline-warning" onclick="exportData()">
                        <i class="fas fa-download me-2"></i>
                        Exportar Datos
                    </button>
                    <button class="btn btn-outline-secondary" onclick="refreshData()">
                        <i class="fas fa-sync-alt me-2"></i>
                        Ejecutar ETL
                    </button>
                </div>
            </div>
        </div>

        <!-- Mini estadísticas -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Información del Sistema
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <div class="fw-bold text-primary">
                                <?= $summary_stats['anios_total'] ?? 0 ?>
                            </div>
                            <small class="text-muted">Años de Datos</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="fw-bold text-success">
                            <?= $summary_stats['anio_inicio'] ?? 0 ?> - <?= $summary_stats['anio_fin'] ?? 0 ?>
                        </div>
                        <small class="text-muted">Período</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Resumen -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-table me-2"></i>
                    Navegación Rápida
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card border-primary">
                            <div class="card-body text-center">
                                <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                                <h5>Drill-Down de Ventas</h5>
                                <p class="text-muted">Navega por años, trimestres y meses</p>
                                <a href="/dashboard/ventas?level=year" class="btn btn-primary">
                                    Iniciar Análisis
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card border-success">
                            <div class="card-body text-center">
                                <i class="fas fa-box fa-3x text-success mb-3"></i>
                                <h5>Top Productos</h5>
                                <p class="text-muted">Productos más vendidos y rentables</p>
                                <a href="/dashboard/productos" class="btn btn-success">
                                    Ver Productos
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card border-info">
                            <div class="card-body text-center">
                                <i class="fas fa-calendar-alt fa-3x text-info mb-3"></i>
                                <h5>Análisis Temporal</h5>
                                <p class="text-muted">Patrones por día, semana y mes</p>
                                <a href="/dashboard/temporal" class="btn btn-info">
                                    Ver Temporal
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let mainChart = null;

function initMainChart() {
    const canvas = document.getElementById('mainChart');
    if (!canvas) {
        console.error('Canvas mainChart not found');
        return false;
    }
    
    try {
        const ctx = canvas.getContext('2d');
        mainChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Cargando...'],
                datasets: [{
                    label: 'Datos',
                    data: [0],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatCurrency(value);
                            }
                        }
                    }
                }
            }
        });
        console.log('Main chart initialized successfully');
        return true;
    } catch (error) {
        console.error('Error initializing main chart:', error);
        showError('Error al inicializar el gráfico principal');
        return false;
    }
}

function updateDashboard() {
    updateChart();
    // Aquí se pueden agregar más actualizaciones
}

function showError(message) {
    console.error(message);
    // Crear un alert temporal o mostrar en un elemento específico
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed';
    alertDiv.style.top = '80px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `
        <strong>Error:</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);
    
    // Auto-remover después de 5 segundos
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

function validateChartData(data, chartType) {
    if (!data || !Array.isArray(data) || data.length === 0) {
        return { valid: false, error: 'Datos vacíos o inválidos' };
    }
    
    // Verificar que los datos tengan las propiedades esperadas
    const firstItem = data[0];
    let requiredProps = [];
    
    switch(chartType) {
        case 'sales_trend':
            requiredProps = ['periodo', 'total'];
            break;
        case 'top_products':
            requiredProps = ['producto_sk', 'total_ventas'];
            break;
        case 'channel_distribution':
            requiredProps = ['canal_venta', 'total_ventas'];
            break;
        case 'margin_analysis':
            requiredProps = ['periodo', 'margen'];
            break;
        default:
            return { valid: false, error: 'Tipo de gráfico desconocido: ' + chartType };
    }
    
    for (let prop of requiredProps) {
        if (!(prop in firstItem)) {
            return { valid: false, error: `Propiedad ${prop} faltante en los datos` };
        }
    }
    
    return { valid: true };
}

function updateChart() {
    const chartType = document.getElementById('chartType').value;
    const period = document.getElementById('periodSelect').value;
    const year = document.getElementById('yearSelect').value;
    
    console.log('Updating chart with:', { chartType, period, year });
    
    // Mostrar indicador de carga en el contenedor del gráfico
    const chartContainer = document.querySelector('.chart-container');
    if (chartContainer) {
        chartContainer.style.opacity = '0.5';
    }
    
    fetch('/dashboard/ajaxChartData', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `chart_type=${chartType}&period=${period}&year=${year}`
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Chart data received:', data);
        if (data.success) {
            updateMainChart(data.data, chartType);
            updateChartTitle(chartType);
        } else {
            console.error('Server error:', data.error);
            showError('Error al cargar gráfico: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        showError('Error de conexión al cargar gráfico: ' + error.message);
    })
    .finally(() => {
        // Restaurar opacidad del contenedor
        if (chartContainer) {
            chartContainer.style.opacity = '1';
        }
    });
}

function updateMainChart(data, chartType) {
    console.log('updateMainChart called with:', { data, chartType, mainChart: !!mainChart });
    
    if (!mainChart) {
        console.error('mainChart is not initialized');
        showError('El gráfico principal no está inicializado');
        return;
    }
    
    // Validar datos
    const validation = validateChartData(data, chartType);
    if (!validation.valid) {
        console.warn('Invalid data:', validation.error);
        // Mostrar datos vacíos en lugar de error
        try {
            if (mainChart) mainChart.destroy();
            const canvas = document.getElementById('mainChart');
            const ctx = canvas.getContext('2d');
            mainChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Sin datos'],
                    datasets: [{
                        label: 'Sin información',
                        data: [0],
                        backgroundColor: 'rgba(200, 200, 200, 0.2)',
                        borderColor: 'rgba(200, 200, 200, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        } catch (error) {
            console.error('Error showing empty data:', error);
        }
        return;
    }
    
    let labels = [];
    let values = [];
    let chartTypeConfig = 'line';
    let label = 'Ventas';
    
    switch(chartType) {
        case 'sales_trend':
            labels = data.map(item => item.periodo);
            values = data.map(item => parseFloat(item.total));
            chartTypeConfig = 'line';
            label = 'Ventas ($)';
            break;
            
        case 'top_products':
            labels = data.map(item => `Producto ${item.producto_sk}`);
            values = data.map(item => parseFloat(item.total_ventas));
            chartTypeConfig = 'bar';
            label = 'Ventas por Producto ($)';
            break;
            
        case 'channel_distribution':
            labels = data.map(item => item.canal_venta);
            values = data.map(item => parseFloat(item.total_ventas));
            chartTypeConfig = 'pie';
            label = 'Ventas por Canal ($)';
            break;
            
        case 'margin_analysis':
            labels = data.map(item => item.periodo);
            values = data.map(item => parseFloat(item.margen));
            chartTypeConfig = 'bar';
            label = 'Margen ($)';
            break;
    }
    
    // Siempre recrear el gráfico para evitar problemas de estructura
    try {
        // Destruir gráfico existente
        if (mainChart) {
            mainChart.destroy();
        }
        
        // Crear nuevo gráfico
        const canvas = document.getElementById('mainChart');
        if (!canvas) {
            throw new Error('Canvas mainChart no encontrado');
        }
        
        const ctx = canvas.getContext('2d');
        mainChart = new Chart(ctx, {
            type: chartTypeConfig,
            data: {
                labels: labels,
                datasets: [{
                    label: label,
                    data: values,
                    backgroundColor: chartTypeConfig === 'pie' ? generateColors(values.length) : 'rgba(54, 162, 235, 0.2)',
                    borderColor: chartTypeConfig === 'pie' ? generateColors(values.length) : 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    fill: chartTypeConfig === 'line'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: chartTypeConfig === 'pie' ? 'right' : 'top',
                    }
                },
                scales: chartTypeConfig === 'pie' ? {} : {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatCurrency(value);
                            }
                        }
                    }
                }
            }
        });
        
        console.log('Chart recreated successfully with type:', chartTypeConfig);
        
    } catch (error) {
        console.error('Error recreating chart:', error);
        showError('Error al recrear el gráfico: ' + error.message);
    }
}

function updateChartTitle(chartType) {
    const titles = {
        'sales_trend': 'Tendencia de Ventas',
        'top_products': 'Top Productos',
        'channel_distribution': 'Distribución por Canal',
        'margin_analysis': 'Análisis de Márgenes'
    };
    
    const titleElement = document.getElementById('chart-title');
    if (titleElement && titles[chartType]) {
        titleElement.innerHTML = `<i class="fas fa-chart-line me-2"></i>${titles[chartType]}`;
    }
}

function generateColors(count) {
    const colors = [
        'rgba(255, 99, 132, 0.6)',
        'rgba(54, 162, 235, 0.6)',
        'rgba(255, 205, 86, 0.6)',
        'rgba(75, 192, 192, 0.6)',
        'rgba(153, 102, 255, 0.6)',
        'rgba(255, 159, 64, 0.6)',
        'rgba(201, 203, 207, 0.6)'
    ];
    
    return Array.from({length: count}, (_, i) => colors[i % colors.length]);
}

function formatCurrency(value) {
    if (typeof value !== 'number') {
        value = parseFloat(value) || 0;
    }
    return new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'ARS',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value);
}

function exportData() {
    const year = document.getElementById('yearSelect').value;
    window.open(`/api/ventas-simple?year=${year}&export=excel`, '_blank');
}

// Inicialización mejorada del dashboard
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard initializing...');
    
    // Función de diagnóstico
    function diagnoseEnvironment() {
        const issues = [];
        
        // Verificar Chart.js
        if (typeof Chart === 'undefined') {
            issues.push('Chart.js no está cargado');
        }
        
        // Verificar canvas
        const canvas = document.getElementById('mainChart');
        if (!canvas) {
            issues.push('Canvas mainChart no encontrado');
        }
        
        // Verificar controles
        const requiredElements = ['chartType', 'periodSelect', 'yearSelect'];
        requiredElements.forEach(id => {
            if (!document.getElementById(id)) {
                issues.push(`Control ${id} no encontrado`);
            }
        });
        
        if (issues.length > 0) {
            console.error('Diagnóstico de problemas:', issues);
            showError('Problemas encontrados: ' + issues.join(', '));
            return false;
        }
        
        console.log('Diagnóstico completado - todo OK');
        return true;
    }
    
    // Ejecutar diagnóstico
    if (!diagnoseEnvironment()) {
        return;
    }
    
    // Verificar que todos los elementos necesarios existan
    const requiredElements = ['chartType', 'periodSelect', 'yearSelect'];
    let allElementsFound = true;
    
    requiredElements.forEach(id => {
        const element = document.getElementById(id);
        if (!element) {
            console.error(`Required element '${id}' not found`);
            allElementsFound = false;
        } else {
            console.log(`Element '${id}' found`);
        }
    });
    
    if (!allElementsFound) {
        showError('Error: No se encontraron todos los controles necesarios');
        return;
    }
    
    // Configurar eventos de cambio para actualizar automáticamente
    document.getElementById('chartType').addEventListener('change', function() {
        console.log('Chart type changed to:', this.value);
        updateChart();
    });
    
    document.getElementById('periodSelect').addEventListener('change', function() {
        console.log('Period changed to:', this.value);
        updateChart();
    });
    
    document.getElementById('yearSelect').addEventListener('change', function() {
        console.log('Year changed to:', this.value);
        updateDashboard(); // Actualiza resumen y gráfico
        updateChart();
    });
    
    // Configurar botón de actualización
    const updateBtn = document.getElementById('updateChartBtn');
    if (updateBtn) {
        updateBtn.addEventListener('click', function() {
            console.log('Update button clicked');
            updateChart();
        });
    }
    
    // Inicializar el gráfico principal
    if (typeof Chart !== 'undefined') {
        const chartInitialized = initMainChart();
        if (chartInitialized) {
            updateChart(); // Cargar datos iniciales
            console.log('Dashboard initialized successfully');
        } else {
            console.error('Failed to initialize main chart');
            showError('Error: No se pudo inicializar el gráfico principal');
        }
    } else {
        console.error('Chart.js not loaded');
        showError('Error: Librería de gráficos no disponible');
    }
});
</script>
<?= $this->endSection() ?>