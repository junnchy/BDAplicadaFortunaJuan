<?= $this->extend('layout/dashboard') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-calendar-alt me-2"></i>
        Análisis Temporal
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-outline-secondary" onclick="exportTemporalData()">
                <i class="fas fa-download me-1"></i>
                Exportar
            </button>
        </div>
        <div class="btn-group">
            <a href="/dashboard" class="btn btn-outline-primary">
                <i class="fas fa-home me-1"></i>
                Dashboard
            </a>
        </div>
    </div>
</div>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="/dashboard">
                <i class="fas fa-home"></i>
                Dashboard
            </a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
            Análisis Temporal
        </li>
    </ol>
</nav>

<!-- Controles de Período -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-filter me-2"></i>
                    Controles de Análisis Temporal
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <label for="yearRange" class="form-label">Año:</label>
                        <select class="form-select" id="yearRange" onchange="updateTemporalAnalysis()">
                            <option value="all">Todos los años</option>
                            <?php foreach ($available_years as $year): ?>
                                <option value="<?= $year ?>" <?= $year == $current_year ? 'selected' : '' ?>>
                                    <?= $year ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="analysisType" class="form-label">Análisis:</label>
                        <select class="form-select" id="analysisType" onchange="updateCharts()">
                            <option value="trends">Tendencias</option>
                            <option value="seasonal">Estacionalidad</option>
                            <option value="weekday">Por Día Semana</option>
                            <option value="monthly">Mensual</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="metricType" class="form-label">Métrica:</label>
                        <select class="form-select" id="metricType" onchange="updateCharts()">
                            <option value="ventas">Ventas</option>
                            <option value="transacciones">Transacciones</option>
                            <option value="margen">Margen</option>
                            <option value="ticket_promedio">Ticket Prom.</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="comparisonPeriod" class="form-label">Comparar con:</label>
                        <select class="form-select" id="comparisonPeriod" onchange="updateCharts()">
                            <option value="none">Sin comparación</option>
                            <option value="previous_year">Año anterior</option>
                            <option value="previous_quarter">Trimestre anterior</option>
                            <option value="previous_month">Mes anterior</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-primary w-100" onclick="updateTemporalAnalysis()">
                            <i class="fas fa-sync-alt me-1"></i>
                            Actualizar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas Temporales -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card stat-card bg-primary">
            <div class="card-body text-white">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            Mejor Mes
                        </div>
                        <div class="stat-number small">
                            <?= $temporal_stats['mejor_mes'] ?? 'N/A' ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-check fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card stat-card bg-success">
            <div class="card-body text-white">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            Mejor Día Semana
                        </div>
                        <div class="stat-number small">
                            <?= $temporal_stats['mejor_dia_semana'] ?? 'N/A' ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-day fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card stat-card bg-info">
            <div class="card-body text-white">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            Crecimiento Anual
                        </div>
                        <div class="stat-number">
                            <?= isset($temporal_stats['crecimiento_anual']) ? number_format($temporal_stats['crecimiento_anual'], 1) . '%' : 'N/A' ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card stat-card bg-warning">
            <div class="card-body text-white">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            Estacionalidad
                        </div>
                        <div class="stat-number small">
                            <?= $temporal_stats['patron_estacional'] ?? 'N/A' ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-leaf fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos Temporales -->
<div class="row mb-4">
    <!-- Gráfico Principal de Tendencias -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0" id="main-temporal-title">
                    <i class="fas fa-chart-line me-2"></i>
                    Tendencias de Ventas
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="temporalChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Estacionalidad -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Patrón Estacional
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="seasonalChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Análisis por Día de la Semana y Mes -->
<div class="row mb-4">
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-week me-2"></i>
                    Rendimiento por Día de la Semana
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="weekdayChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-bar me-2"></i>
                    Comparación Mensual
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Análisis Detallado -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-table me-2"></i>
                    Análisis Temporal Detallado
                </h5>
            </div>
            <div class="card-body">
                <!-- Tabs para diferentes vistas -->
                <ul class="nav nav-tabs" id="temporalTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="yearly-tab" data-bs-toggle="tab" data-bs-target="#yearly" type="button" role="tab">
                            <i class="fas fa-calendar me-1"></i>
                            Por Año
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="quarterly-tab" data-bs-toggle="tab" data-bs-target="#quarterly" type="button" role="tab">
                            <i class="fas fa-calendar-alt me-1"></i>
                            Por Trimestre
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="monthly-tab" data-bs-toggle="tab" data-bs-target="#monthly" type="button" role="tab">
                            <i class="fas fa-calendar-day me-1"></i>
                            Por Mes
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="weekday-tab" data-bs-toggle="tab" data-bs-target="#weekday" type="button" role="tab">
                            <i class="fas fa-calendar-week me-1"></i>
                            Por Día Semana
                        </button>
                    </li>
                </ul>

                <div class="tab-content mt-3" id="temporalTabContent">
                    <!-- Tab Anual -->
                    <div class="tab-pane fade show active" id="yearly" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Año</th>
                                        <th class="text-end">Ventas</th>
                                        <th class="text-end">Transacciones</th>
                                        <th class="text-end">Ticket Prom.</th>
                                        <th class="text-end">Crecimiento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($yearly_data)): ?>
                                        <?php foreach ($yearly_data as $year): ?>
                                            <tr>
                                                <td class="fw-bold"><?= $year['anio'] ?></td>
                                                <td class="text-end">$<?= number_format($year['total_ventas'], 2) ?></td>
                                                <td class="text-end"><?= number_format($year['transacciones']) ?></td>
                                                <td class="text-end">$<?= number_format($year['ticket_promedio'], 2) ?></td>
                                                <td class="text-end">
                                                    <?php
                                                    $growth = $year['crecimiento'] ?? 0;
                                                    $class = $growth >= 0 ? 'text-success' : 'text-danger';
                                                    $icon = $growth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
                                                    ?>
                                                    <span class="<?= $class ?>">
                                                        <i class="fas <?= $icon ?> me-1"></i>
                                                        <?= number_format(abs($growth), 1) ?>%
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab Trimestral -->
                    <div class="tab-pane fade" id="quarterly" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Trimestre</th>
                                        <th class="text-end">Ventas</th>
                                        <th class="text-end">Transacciones</th>
                                        <th class="text-end">Margen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($quarterly_data)): ?>
                                        <?php foreach ($quarterly_data as $quarter): ?>
                                            <tr>
                                                <td class="fw-bold"><?= $quarter['periodo'] ?></td>
                                                <td class="text-end">$<?= number_format($quarter['total_ventas'], 2) ?></td>
                                                <td class="text-end"><?= number_format($quarter['transacciones']) ?></td>
                                                <td class="text-end">$<?= number_format($quarter['margen'], 2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab Mensual -->
                    <div class="tab-pane fade" id="monthly" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Mes</th>
                                        <th class="text-end">Ventas</th>
                                        <th class="text-end">Transacciones</th>
                                        <th class="text-end">Índice Estacional</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($monthly_data)): ?>
                                        <?php foreach ($monthly_data as $month): ?>
                                            <tr>
                                                <td class="fw-bold"><?= $month['mes_nombre'] ?></td>
                                                <td class="text-end">$<?= number_format($month['total_ventas'], 2) ?></td>
                                                <td class="text-end"><?= number_format($month['transacciones']) ?></td>
                                                <td class="text-end">
                                                    <?php
                                                    $index = $month['indice_estacional'] ?? 1;
                                                    $class = $index > 1 ? 'text-success' : ($index < 1 ? 'text-danger' : 'text-warning');
                                                    ?>
                                                    <span class="<?= $class ?>">
                                                        <?= number_format($index, 2) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab Día de la Semana -->
                    <div class="tab-pane fade" id="weekday" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Día de la Semana</th>
                                        <th class="text-end">Ventas Promedio</th>
                                        <th class="text-end">Transacciones</th>
                                        <th class="text-end">Performance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($weekday_data)): ?>
                                        <?php foreach ($weekday_data as $day): ?>
                                            <tr>
                                                <td class="fw-bold"><?= $day['dia_nombre'] ?></td>
                                                <td class="text-end">$<?= number_format($day['promedio_ventas'], 2) ?></td>
                                                <td class="text-end"><?= number_format($day['transacciones']) ?></td>
                                                <td class="text-end">
                                                    <?php
                                                    $performance = $day['performance'] ?? 50;
                                                    if ($performance >= 80) {
                                                        $badge = 'success';
                                                        $text = 'Alto';
                                                    } elseif ($performance >= 60) {
                                                        $badge = 'warning';
                                                        $text = 'Medio';
                                                    } else {
                                                        $badge = 'danger';
                                                        $text = 'Bajo';
                                                    }
                                                    ?>
                                                    <span class="badge bg-<?= $badge ?>"><?= $text ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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
let temporalChart = null;
let seasonalChart = null;
let weekdayChart = null;
let monthlyChart = null;

document.addEventListener('DOMContentLoaded', function() {
    initTemporalCharts();
});

function initTemporalCharts() {
    initMainTemporalChart();
    initSeasonalChart();
    initWeekdayChart();
    initMonthlyChart();
}

function initMainTemporalChart() {
    const ctx = document.getElementById('temporalChart').getContext('2d');
    const yearlyData = <?= json_encode($yearly_data ?? []) ?>;
    
    temporalChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: yearlyData.map(item => item.anio),
            datasets: [{
                label: 'Ventas ($)',
                data: yearlyData.map(item => parseFloat(item.total_ventas)),
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
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
}

function initSeasonalChart() {
    const ctx = document.getElementById('seasonalChart').getContext('2d');
    const monthlyData = <?= json_encode($monthly_data ?? []) ?>;
    
    seasonalChart = new Chart(ctx, {
        type: 'radar',
        data: {
            labels: monthlyData.map(item => item.mes_nombre.substring(0, 3)),
            datasets: [{
                label: 'Índice Estacional',
                data: monthlyData.map(item => parseFloat(item.indice_estacional ?? 1)),
                borderColor: 'rgba(255, 99, 132, 1)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    beginAtZero: true,
                    max: 2
                }
            }
        }
    });
}

function initWeekdayChart() {
    const ctx = document.getElementById('weekdayChart').getContext('2d');
    const weekdayData = <?= json_encode($weekday_data ?? []) ?>;
    
    weekdayChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: weekdayData.map(item => item.dia_nombre),
            datasets: [{
                label: 'Ventas Promedio ($)',
                data: weekdayData.map(item => parseFloat(item.promedio_ventas)),
                backgroundColor: [
                    'rgba(255, 99, 132, 0.6)',
                    'rgba(54, 162, 235, 0.6)',
                    'rgba(255, 205, 86, 0.6)',
                    'rgba(75, 192, 192, 0.6)',
                    'rgba(153, 102, 255, 0.6)',
                    'rgba(255, 159, 64, 0.6)',
                    'rgba(199, 199, 199, 0.6)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
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
}

function initMonthlyChart() {
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyData = <?= json_encode($monthly_data ?? []) ?>;
    
    monthlyChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: monthlyData.map(item => item.mes_nombre),
            datasets: [{
                label: 'Ventas ($)',
                data: monthlyData.map(item => parseFloat(item.total_ventas)),
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderWidth: 2,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
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
}

function updateTemporalAnalysis() {
    const year = document.getElementById('yearRange').value;
    
    showLoading('temporalChart');
    
    // Recargar página con nuevo año
    if (year === 'all') {
        window.location.href = '/dashboard/temporal';
    } else {
        window.location.href = `/dashboard/temporal?year=${year}`;
    }
}

function updateCharts() {
    const analysisType = document.getElementById('analysisType').value;
    const metricType = document.getElementById('metricType').value;
    const comparisonPeriod = document.getElementById('comparisonPeriod').value;
    
    updateMainTemporalChart(analysisType, metricType, comparisonPeriod);
    updateTemporalTitle(analysisType, metricType);
}

function updateMainTemporalChart(analysisType, metricType, comparisonPeriod) {
    if (!temporalChart) return;
    
    // Aquí se implementaría la lógica para actualizar el gráfico
    // basado en el tipo de análisis y métrica seleccionada
    
    const year = document.getElementById('yearRange').value;
    
    fetch('/dashboard/ajaxTemporalData', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `analysis_type=${analysisType}&metric_type=${metricType}&comparison=${comparisonPeriod}&year=${year}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateTemporalChartData(data.data, metricType);
        } else {
            showError('Error al cargar datos temporales: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error de conexión al cargar datos temporales');
    });
}

function updateTemporalChartData(data, metricType) {
    if (!temporalChart || !data || data.length === 0) return;
    
    let values = [];
    let label = '';
    
    switch(metricType) {
        case 'ventas':
            values = data.map(item => parseFloat(item.total_ventas || 0));
            label = 'Ventas ($)';
            break;
        case 'transacciones':
            values = data.map(item => parseInt(item.transacciones || 0));
            label = 'Transacciones';
            break;
        case 'margen':
            values = data.map(item => parseFloat(item.margen || 0));
            label = 'Margen ($)';
            break;
        case 'ticket_promedio':
            values = data.map(item => parseFloat(item.ticket_promedio || 0));
            label = 'Ticket Promedio ($)';
            break;
    }
    
    temporalChart.data.labels = data.map(item => item.periodo);
    temporalChart.data.datasets[0].data = values;
    temporalChart.data.datasets[0].label = label;
    temporalChart.update();
}

function updateTemporalTitle(analysisType, metricType) {
    const types = {
        'trends': 'Tendencias',
        'seasonal': 'Estacionalidad',
        'weekday': 'Por Día de Semana',
        'monthly': 'Mensual'
    };
    
    const metrics = {
        'ventas': 'de Ventas',
        'transacciones': 'de Transacciones',
        'margen': 'de Márgenes',
        'ticket_promedio': 'de Ticket Promedio'
    };
    
    const titleElement = document.getElementById('main-temporal-title');
    if (titleElement && types[analysisType] && metrics[metricType]) {
        titleElement.innerHTML = `<i class="fas fa-chart-line me-2"></i>${types[analysisType]} ${metrics[metricType]}`;
    }
}

function exportTemporalData() {
    const year = document.getElementById('yearRange').value;
    const params = year !== 'all' ? `?year=${year}` : '';
    window.open(`/api/temporal-data${params}&export=excel`, '_blank');
}

// Añadir estilos para las tarjetas
document.addEventListener('DOMContentLoaded', function() {
    const style = document.createElement('style');
    style.textContent = `
        .chart-container {
            position: relative;
            height: 300px;
        }
        .stat-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        .nav-tabs .nav-link {
            color: #6c757d;
        }
        .nav-tabs .nav-link.active {
            color: #495057;
            background-color: #fff;
            border-color: #dee2e6 #dee2e6 #fff;
        }
    `;
    document.head.appendChild(style);
});
</script>
<?= $this->endSection() ?>