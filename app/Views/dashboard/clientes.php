<?= $this->extend('layout/dashboard') ?>

<?= $this->section('content') ?>

<?php if (isset($error_message)): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <?= $error_message ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-users me-2"></i>
        Análisis de Clientes
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <select class="form-select" id="yearFilter" onchange="updateClientAnalysis()">
                <?php foreach ($available_years as $year): ?>
                    <option value="<?= $year ?>" <?= $year == $current_year ? 'selected' : '' ?>>
                        <?= $year ?>
                    </option>
                <?php endforeach; ?>
            </select>
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
            Análisis de Clientes
        </li>
    </ol>
</nav>

<!-- Filtros y Controles -->
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
                        <label for="metricSelect" class="form-label">Métrica:</label>
                        <select class="form-select" id="metricSelect" onchange="updateCharts()">
                            <option value="ventas">Total Ventas</option>
                            <option value="margen">Total Margen</option>
                            <option value="transacciones">Num. Operaciones</option>
                            <option value="ticket_promedio">Ticket Promedio</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="segmentFilter" class="form-label">Segmento:</label>
                        <select class="form-select" id="segmentFilter" onchange="updateCharts()">
                            <option value="all">Todos los Segmentos</option>
                            <?php foreach ($client_segments as $segment => $data): ?>
                            <option value="<?= strtolower($segment) ?>"><?= $segment ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="topCount" class="form-label">Mostrar Top:</label>
                        <select class="form-select" id="topCount" onchange="updateCharts()">
                            <option value="20">Top 20</option>
                            <option value="50" selected>Top 50</option>
                            <option value="100">Top 100</option>
                            <option value="150">Top 150</option>
                            <option value="200">Top 200</option>
                            <option value="300">Top 300</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="button" class="btn btn-primary" onclick="updateCharts()">
                                <i class="fas fa-sync-alt me-1"></i>
                                Actualizar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- KPIs Row -->
<div class="row mb-4">
    <div class="col-xl-2-4 col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Total Clientes</p>
                        <h4 class="mb-0"><?= number_format($stats['total_clients']) ?></h4>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="fas fa-users fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2-4 col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Ventas Totales</p>
                        <h4 class="mb-0">$<?= number_format($stats['total_sales'], 0) ?></h4>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="fas fa-dollar-sign fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2-4 col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Margen Total</p>
                        <h4 class="mb-0">$<?= number_format($stats['total_margin'], 0) ?></h4>
                        <small class="text-success"><?= number_format($stats['margin_percentage'], 1) ?>%</small>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="fas fa-chart-line fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2-4 col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Ticket Promedio</p>
                        <h4 class="mb-0">$<?= number_format($stats['avg_ticket'], 0) ?></h4>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="fas fa-receipt fa-2x text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2-4 col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Clientes Caídos</p>
                        <h4 class="mb-0 text-danger"><?= number_format($caidos_stats['total_caidos']) ?></h4>
                        <small class="text-muted">+10 días sin compra</small>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="fas fa-user-times fa-2x text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <!-- Top Clientes por Valor -->
    <div class="col-xl-6 col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-trophy me-2"></i>
                    Top 10 Clientes por Valor
                </h5>
            </div>
            <div class="card-body">
                <div style="position: relative; height: 400px;">
                    <canvas id="topClientsChart"></canvas>
                </div>
                <div class="mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted">Total Top 10:</small>
                        <span class="badge bg-success" id="topClientsTotal">$0</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">% del Total:</small>
                        <span class="badge bg-info" id="topClientsPercentage">0%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Frecuencia de Operaciones INTERACTIVO -->
    <div class="col-xl-6 col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-column me-2"></i>
                    Frecuencia de Operaciones
                    <small class="text-muted">(Click para ver clientes)</small>
                </h5>
            </div>
            <div class="card-body">
                <div style="position: relative; height: 400px;">
                    <canvas id="frequencyChart"></canvas>
                </div>
                <div class="mt-3" id="frequencyDetails">
                    <?php foreach ($frequency_analysis as $segment => $data): ?>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted"><?= $segment ?>:</span>
                        <strong><?= number_format($data['count']) ?> clientes</strong>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Nueva Sección: Clientes Caídos -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
                    Clientes Caídos - Oportunidades de Reactivación
                    <span class="badge bg-secondary"><?= count($clientes_caidos ?? []) ?> clientes</span>
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="text-center">
                            <h3 class="text-danger"><?= number_format($caidos_stats['total_caidos'] ?? 0) ?></h3>
                            <p class="text-muted mb-0">Clientes Caídos</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <h3 class="text-warning"><?= $caidos_stats['promedio_dias_sin_compra'] ?? 0 ?></h3>
                            <p class="text-muted mb-0">Días Promedio sin Compra</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <h3 class="text-info">$<?= number_format($caidos_stats['ventas_perdidas_potencial'] ?? 0, 0) ?></h3>
                            <p class="text-muted mb-0">Potencial Histórico</p>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($clientes_caidos)): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm" id="caidosTable">
                        <thead class="table-danger">
                            <tr>
                                <th>Cliente</th>
                                <th>Segmento</th>
                                <th>Última Compra</th>
                                <th>Días sin Compra</th>
                                <th>Categoría</th>
                                <th>Compras Históricas</th>
                                <th>Ventas Históricas</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach (array_slice($clientes_caidos, 0, 20) as $caido): ?>
                            <tr>
                                <td>
                                    <strong><?= esc($caido['cliente_nombre']) ?></strong>
                                    <br><small class="text-muted">ID: <?= $caido['cliente_sk'] ?></small>
                                    <?php if (!empty($caido['email'])): ?>
                                    <br><small class="text-muted"><?= esc($caido['email']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= esc($caido['segmento']) ?></span>
                                </td>
                                <td>
                                    <small><?= date('d/m/Y', strtotime($caido['ultima_compra'])) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $caido['dias_sin_compra'] > 60 ? 'danger' : ($caido['dias_sin_compra'] > 30 ? 'warning' : 'info') ?>">
                                        <?= $caido['dias_sin_compra'] ?> días
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $caido['categoria_caido'] == 'Crítico' ? 'danger' : ($caido['categoria_caido'] == 'Alto Riesgo' ? 'warning' : 'info') ?>">
                                        <?= $caido['categoria_caido'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?= number_format($caido['total_compras_historicas']) ?></span>
                                </td>
                                <td>
                                    <span class="text-success fw-bold">$<?= number_format($caido['total_ventas_historicas'], 0) ?></span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-success btn-sm" onclick="reactivateClient(<?= $caido['cliente_sk'] ?>)" title="Campaña de Reactivación">
                                            <i class="fas fa-envelope"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="viewClientDetail(<?= $caido['cliente_sk'] ?>)" title="Ver Detalle">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (count($clientes_caidos) > 20): ?>
                <div class="text-center mt-3">
                    <button type="button" class="btn btn-outline-primary" onclick="showAllCaidos()">
                        Ver todos los <?= count($clientes_caidos) ?> clientes caídos
                    </button>
                </div>
                <?php endif; ?>
                <?php else: ?>
                <div class="alert alert-success text-center">
                    <h4><i class="fas fa-check-circle text-success me-2"></i>¡Excelente!</h4>
                    <p>No hay clientes sin compras en más de 10 días. Todos los clientes están activos.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Clientes -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-star me-2"></i>
                    Top Clientes Detallado
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-sm" id="clientesTable">
                        <thead class="table-dark">
                            <tr>
                                <th>Rank</th>
                                <th>Cliente</th>
                                <th>Segmento</th>
                                <th>Ventas</th>
                                <th>Margen</th>
                                <th>% Margen</th>
                                <th>Operaciones</th>
                                <th>Órdenes</th>
                                <th>Ticket Prom.</th>
                                <th>Última Compra</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($client_data as $index => $cliente): ?>
                            <?php 
                                // Calcular días sin compra para mostrar indicador
                                $dias_sin_compra = 0;
                                if ($cliente['ultima_compra'] && $cliente['ultima_compra'] !== 'N/A') {
                                    $ultima_compra_date = new DateTime($cliente['ultima_compra']);
                                    $hoy = new DateTime();
                                    $dias_sin_compra = $hoy->diff($ultima_compra_date)->days;
                                }
                                $es_caido = $dias_sin_compra > 10;
                            ?>
                            <tr class="<?= $es_caido ? 'table-warning' : '' ?>">
                                <td>
                                    <span class="badge bg-primary">#<?= $index + 1 ?></span>
                                    <?php if ($es_caido): ?>
                                    <span class="badge bg-danger ms-1" title="Cliente sin compras por más de 10 días">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div>
                                        <strong><?= esc($cliente['cliente_nombre']) ?></strong>
                                        <br>
                                        <small class="text-muted">ID: <?= $cliente['cliente_sk'] ?></small>
                                        <?php if ($cliente['email']): ?>
                                        <br><small class="text-muted"><?= esc($cliente['email']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= esc($cliente['segmento']) ?></span>
                                </td>
                                <td>
                                    <span class="text-success fw-bold">
                                        $<?= number_format($cliente['total_ventas'], 0) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-warning fw-bold">
                                        $<?= number_format($cliente['total_margen'], 0) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $cliente['margen_porcentaje'] > 20 ? 'success' : ($cliente['margen_porcentaje'] > 10 ? 'warning' : 'danger') ?>">
                                        <?= number_format($cliente['margen_porcentaje'], 1) ?>%
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-primary">
                                        <?= number_format($cliente['transacciones']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?= number_format($cliente['ordenes_unicas']) ?>
                                    </span>
                                    <?php if ($cliente['items_por_orden'] > 0): ?>
                                    <br><small class="text-muted"><?= number_format($cliente['items_por_orden'], 1) ?> items/orden</small>
                                    <?php endif; ?>
                                </td>
                                <td>$<?= number_format($cliente['ticket_promedio'], 0) ?></td>
                                <td>
                                    <small><?= $cliente['ultima_compra'] ?></small>
                                    <?php if ($dias_sin_compra > 0): ?>
                                    <br>
                                    <span class="badge bg-<?= $dias_sin_compra > 30 ? 'danger' : ($dias_sin_compra > 10 ? 'warning' : 'success') ?> badge-sm">
                                        <?= $dias_sin_compra ?> días
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="viewClientDetail(<?= $cliente['cliente_sk'] ?>)" title="Ver Detalle">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-info btn-sm" onclick="viewClientHistory(<?= $cliente['cliente_sk'] ?>)" title="Ver Historial">
                                            <i class="fas fa-history"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Variables globales
let topClientsChart = null;
let frequencyChart = null;

// Datos de clientes por frecuencia (para interactividad)
const frequencyDetails = <?= json_encode($frequency_details) ?>;

// Inicializar cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    console.log('Dashboard de clientes cargado');
});

// Paleta de colores consistente
const colors = [
    '#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1',
    '#fd7e14', '#20c997', '#6c757d', '#17a2b8', '#f8f9fa'
];

// Inicializar gráficos
function initializeCharts() {
    // Gráfico de Top Clientes por Valor
    const topClientsCtx = document.getElementById('topClientsChart').getContext('2d');
    const clientData = <?= json_encode(array_slice($client_data, 0, 10)) ?>;
    
    const topClientsLabels = clientData.map(cliente => {
        // Acortar nombres largos
        let name = cliente.cliente_nombre;
        return name.length > 15 ? name.substring(0, 15) + '...' : name;
    });
    
    const topClientsValues = clientData.map(cliente => parseFloat(cliente.total_ventas));
    const topClientsMargins = clientData.map(cliente => parseFloat(cliente.total_margen || 0));
    
    // Calcular totales para mostrar en el resumen
    const totalTopClients = topClientsValues.reduce((a, b) => a + b, 0);
    const totalAllSales = <?= $stats['total_sales'] ?>;
    const percentage = ((totalTopClients / totalAllSales) * 100).toFixed(1);
    
    document.getElementById('topClientsTotal').textContent = '$' + totalTopClients.toLocaleString();
    document.getElementById('topClientsPercentage').textContent = percentage + '%';
    
    topClientsChart = new Chart(topClientsCtx, {
        type: 'bar',
        data: {
            labels: topClientsLabels,
            datasets: [
                {
                    label: 'Ventas ($)',
                    data: topClientsValues,
                    backgroundColor: colors[0],
                    borderColor: colors[0],
                    borderWidth: 1,
                    yAxisID: 'y'
                },
                {
                    label: 'Margen ($)',
                    data: topClientsMargins,
                    backgroundColor: colors[2],
                    borderColor: colors[2],
                    borderWidth: 1,
                    type: 'line',
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 10,
                        font: {
                            size: 11
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.dataset.label || '';
                            const value = context.parsed.y;
                            if (label === 'Ventas ($)') {
                                return `${label}: $${value.toLocaleString()}`;
                            } else {
                                const marginPercent = ((value / context.parsed.y) * 100).toFixed(1);
                                return `${label}: $${value.toLocaleString()}`;
                            }
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        font: {
                            size: 9
                        },
                        maxRotation: 45
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Ventas ($)'
                    },
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        },
                        font: {
                            size: 10
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Margen ($)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        },
                        font: {
                            size: 10
                        }
                    }
                }
            }
        }
    });

    // Gráfico de Frecuencia de Operaciones INTERACTIVO
    const frequencyCtx = document.getElementById('frequencyChart').getContext('2d');
    const frequencyData = <?= json_encode(array_values(array_map(function($data) { return $data['count']; }, $frequency_analysis))) ?>;
    const frequencyLabels = <?= json_encode(array_keys($frequency_analysis)) ?>;
    
    frequencyChart = new Chart(frequencyCtx, {
        type: 'bar',
        data: {
            labels: frequencyLabels,
            datasets: [{
                label: 'Clientes',
                data: frequencyData,
                backgroundColor: ['#007bff', '#17a2b8', '#6f42c1', '#6c757d'],
                borderWidth: 1,
                hoverBackgroundColor: ['#0056b3', '#138496', '#5a2d91', '#495057']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed.y / total) * 100).toFixed(1);
                            return `${context.parsed.y} clientes (${percentage}%) - Click para ver detalles`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: {
                            size: 12
                        }
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 12
                        }
                    }
                }
            },
            onClick: function(event, elements) {
                if (elements.length > 0) {
                    const index = elements[0].index;
                    const segment = frequencyLabels[index];
                    showFrequencyDetails(segment);
                }
            },
            onHover: function(event, elements) {
                event.native.target.style.cursor = elements.length > 0 ? 'pointer' : 'default';
            }
        }
    });
}

// Funciones de actualización
function updateClientAnalysis() {
    const year = document.getElementById('yearFilter').value;
    window.location.href = `/dashboard/clientes?year=${year}`;
}

function updateCharts() {
    const metrica = document.getElementById('metricSelect').value;
    const segmento = document.getElementById('segmentFilter').value;
    const topCount = document.getElementById('topCount').value;
    
    // Mostrar indicador de carga
    const loadingText = 'Cargando...';
    if (topClientsChart) {
        topClientsChart.data.labels = [loadingText];
        topClientsChart.data.datasets[0].data = [0];
        topClientsChart.update();
    }
    
    // Realizar petición AJAX
    fetch('/dashboard/clientesFilter', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `metrica=${metrica}&segmento=${segmento}&topCount=${topCount}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateTopClientsChart(data.data, data.metrica);
            updateClientTable(data.data, data.metrica);
        } else {
            console.error('Error:', data.error);
            alert('Error al actualizar los datos: ' + (data.error || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error en la petición:', error);
        alert('Error de conexión al actualizar los datos');
    });
}

function updateTopClientsChart(clientData, metrica) {
    if (!topClientsChart) return;
    
    // Preparar datos para el gráfico
    const labels = clientData.slice(0, 10).map(cliente => {
        let name = cliente.cliente_nombre;
        return name.length > 15 ? name.substring(0, 15) + '...' : name;
    });
    
    let values, margins;
    let yAxisLabel, tooltipPrefix;
    
    switch (metrica) {
        case 'margen':
            values = clientData.slice(0, 10).map(cliente => cliente.total_margen);
            margins = clientData.slice(0, 10).map(cliente => cliente.total_ventas);
            yAxisLabel = 'Margen ($)';
            tooltipPrefix = '$';
            break;
        case 'transacciones':
            values = clientData.slice(0, 10).map(cliente => cliente.transacciones);
            margins = clientData.slice(0, 10).map(cliente => cliente.total_margen);
            yAxisLabel = 'Transacciones';
            tooltipPrefix = '';
            break;
        case 'ticket_promedio':
            values = clientData.slice(0, 10).map(cliente => cliente.ticket_promedio);
            margins = clientData.slice(0, 10).map(cliente => cliente.total_margen);
            yAxisLabel = 'Ticket Promedio ($)';
            tooltipPrefix = '$';
            break;
        default:
            values = clientData.slice(0, 10).map(cliente => cliente.total_ventas);
            margins = clientData.slice(0, 10).map(cliente => cliente.total_margen);
            yAxisLabel = 'Ventas ($)';
            tooltipPrefix = '$';
            break;
    }
    
    // Actualizar datos del gráfico
    topClientsChart.data.labels = labels;
    topClientsChart.data.datasets[0].data = values;
    topClientsChart.data.datasets[0].label = yAxisLabel;
    topClientsChart.data.datasets[1].data = margins;
    
    // Actualizar configuración de escalas
    topClientsChart.options.scales.y.title.text = yAxisLabel;
    topClientsChart.options.plugins.tooltip.callbacks.label = function(context) {
        const label = context.dataset.label || '';
        const value = context.parsed.y;
        if (label.includes('Margen')) {
            return `${label}: $${value.toLocaleString()}`;
        } else {
            return `${label}: ${tooltipPrefix}${value.toLocaleString()}`;
        }
    };
    
    // Actualizar totales
    const total = values.reduce((a, b) => a + b, 0);
    document.getElementById('topClientsTotal').textContent = tooltipPrefix + total.toLocaleString();
    
    topClientsChart.update();
}

function updateClientTable(clientData, metrica) {
    const tableBody = document.querySelector('#clientesTable tbody');
    if (!tableBody) return;
    
    let html = '';
    clientData.forEach((cliente, index) => {
        const margenPorcentaje = cliente.total_ventas > 0 ? 
            ((cliente.total_margen / cliente.total_ventas) * 100) : 0;
        
        html += `
            <tr>
                <td>
                    <span class="badge bg-primary">#${index + 1}</span>
                </td>
                <td>
                    <div>
                        <strong>${cliente.cliente_nombre}</strong>
                        <br>
                        <small class="text-muted">ID: ${cliente.cliente_sk}</small>
                    </div>
                </td>
                <td>
                    <span class="badge bg-info">${cliente.segmento}</span>
                </td>
                <td>
                    <span class="text-success fw-bold">
                        $${cliente.total_ventas.toLocaleString()}
                    </span>
                </td>
                <td>
                    <span class="text-warning fw-bold">
                        $${cliente.total_margen.toLocaleString()}
                    </span>
                </td>
                <td>
                    <span class="badge bg-${margenPorcentaje > 20 ? 'success' : (margenPorcentaje > 10 ? 'warning' : 'danger')}">
                        ${margenPorcentaje.toFixed(1)}%
                    </span>
                </td>
                <td>
                    <span class="badge bg-primary">
                        ${cliente.transacciones.toLocaleString()}
                    </span>
                </td>
                <td>
                    <span class="badge bg-secondary">
                        ${cliente.ordenes_unicas ? cliente.ordenes_unicas.toLocaleString() : 'N/A'}
                    </span>
                    ${cliente.ordenes_unicas && cliente.transacciones ? 
                        `<br><small class="text-muted">${(cliente.transacciones / cliente.ordenes_unicas).toFixed(1)} items/orden</small>` : 
                        ''
                    }
                </td>
                <td>$${cliente.ticket_promedio.toLocaleString()}</td>
                <td>
                    <small>${cliente.ultima_compra}</small>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="viewClientDetail(${cliente.cliente_sk})" title="Ver Detalle">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-outline-info btn-sm" onclick="viewClientHistory(${cliente.cliente_sk})" title="Ver Historial">
                            <i class="fas fa-history"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tableBody.innerHTML = html;
}

// Función para mostrar detalles de frecuencia (NUEVA)
function showFrequencyDetails(segment) {
    const clients = frequencyDetails[segment] || [];
    
    if (clients.length === 0) {
        alert('No hay clientes en este segmento');
        return;
    }
    
    const detailsDiv = document.getElementById('frequencyDetails');
    
    let html = `
        <div class="mt-3">
            <h6 class="text-primary mb-3">
                <i class="fas fa-users me-2"></i>
                Clientes en "${segment}" (${clients.length})
            </h6>
            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                <table class="table table-sm table-hover">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th>Cliente</th>
                            <th>Ops</th>
                            <th>Ventas</th>
                            <th>Margen</th>
                            <th>Última</th>
                        </tr>
                    </thead>
                    <tbody>
    `;
    
    clients.slice(0, 20).forEach(client => {
        html += `
            <tr>
                <td>
                    <strong>${client.cliente_nombre}</strong>
                    <br><small class="text-muted">${client.segmento}</small>
                </td>
                <td>
                    <span class="badge bg-primary">${client.transacciones}</span>
                </td>
                <td>
                    <span class="text-success">$${parseFloat(client.total_ventas).toLocaleString()}</span>
                </td>
                <td>
                    <span class="text-warning">$${parseFloat(client.total_margen || 0).toLocaleString()}</span>
                </td>
                <td>
                    <small>${client.ultima_compra || 'N/A'}</small>
                </td>
            </tr>
        `;
    });
    
    html += `
                    </tbody>
                </table>
            </div>
            ${clients.length > 20 ? `<small class="text-muted">Mostrando los primeros 20 de ${clients.length} clientes</small>` : ''}
            <div class="mt-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="hideFrequencyDetails()">
                    <i class="fas fa-times me-1"></i>
                    Cerrar Detalles
                </button>
            </div>
        </div>
    `;
    
    detailsDiv.innerHTML = html;
}

// Función para ocultar detalles de frecuencia
function hideFrequencyDetails() {
    const detailsDiv = document.getElementById('frequencyDetails');
    const originalContent = `
        <?php foreach ($frequency_analysis as $segment => $data): ?>
        <div class="d-flex justify-content-between mb-1">
            <span class="text-muted"><?= $segment ?>:</span>
            <strong><?= number_format($data['count']) ?> clientes</strong>
        </div>
        <?php endforeach; ?>
    `;
    detailsDiv.innerHTML = originalContent;
}

// Funciones de acción
function viewClientDetail(clienteId) {
    // Crear modal con información del cliente
    const clientData = <?= json_encode($client_data) ?>;
    const cliente = clientData.find(c => c.cliente_sk == clienteId);
    
    if (!cliente) {
        alert(`Cliente ${clienteId} no encontrado en los datos actuales`);
        return;
    }
    
    const modalContent = `
        <div class="modal fade" id="clientDetailModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-user me-2"></i>
                            Detalle del Cliente: ${cliente.cliente_nombre}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary">Información General</h6>
                                <p><strong>ID:</strong> ${cliente.cliente_sk}</p>
                                <p><strong>Nombre:</strong> ${cliente.cliente_nombre}</p>
                                <p><strong>Email:</strong> ${cliente.email || 'No disponible'}</p>
                                <p><strong>Segmento:</strong> <span class="badge bg-info">${cliente.segmento}</span></p>
                                <p><strong>Ciudad:</strong> ${cliente.ciudad || 'No disponible'}</p>
                                <p><strong>País:</strong> ${cliente.pais || 'No disponible'}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-success">Métricas de Ventas</h6>
                                <p><strong>Total Ventas:</strong> <span class="text-success">$${cliente.total_ventas.toLocaleString()}</span></p>
                                <p><strong>Total Margen:</strong> <span class="text-warning">$${cliente.total_margen.toLocaleString()}</span></p>
                                <p><strong>% Margen:</strong> <span class="badge bg-${cliente.margen_porcentaje > 20 ? 'success' : 'warning'}">${cliente.margen_porcentaje.toFixed(1)}%</span></p>
                                <p><strong>Transacciones:</strong> ${cliente.transacciones}</p>
                                <p><strong>Órdenes Únicas:</strong> ${cliente.ordenes_unicas}</p>
                                <p><strong>Ticket Promedio:</strong> $${cliente.ticket_promedio.toLocaleString()}</p>
                                <p><strong>Última Compra:</strong> ${cliente.ultima_compra}</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary" onclick="viewClientHistory(${clienteId})">Ver Historial</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remover modal existente si existe
    const existingModal = document.getElementById('clientDetailModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Agregar modal al DOM
    document.body.insertAdjacentHTML('beforeend', modalContent);
    
    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('clientDetailModal'));
    modal.show();
}

function viewClientHistory(clienteId) {
    const modal = bootstrap.Modal.getInstance(document.getElementById('clientDetailModal'));
    if (modal) modal.hide();
    
    alert(`Funcionalidad de historial para cliente ${clienteId} - Se implementaría con una vista detallada de transacciones históricas`);
}

// Nuevas funciones para clientes caídos
function reactivateClient(clienteId) {
    if (confirm(`¿Desea iniciar una campaña de reactivación para el cliente ${clienteId}?`)) {
        alert(`Iniciando campaña de reactivación para cliente ${clienteId}`);
        // Aquí se implementaría la lógica de reactivación
    }
}

function showAllCaidos() {
    const tableBody = document.querySelector('#caidosTable tbody');
    const showAllBtn = document.querySelector('.btn-outline-primary[onclick="showAllCaidos()"]');
    
    if (!tableBody || !showAllBtn) return;
    
    // Obtener todos los clientes caídos del PHP
    const allCaidos = <?= json_encode($clientes_caidos) ?>;
    
    // Limpiar el tbody actual
    let html = '';
    
    allCaidos.forEach(caido => {
        html += `
            <tr>
                <td>
                    <strong>${caido.cliente_nombre}</strong>
                    <br><small class="text-muted">ID: ${caido.cliente_sk}</small>
                    ${caido.email ? `<br><small class="text-muted">${caido.email}</small>` : ''}
                </td>
                <td>
                    <span class="badge bg-secondary">${caido.segmento}</span>
                </td>
                <td>
                    <small>${new Date(caido.ultima_compra).toLocaleDateString('es-ES')}</small>
                </td>
                <td>
                    <span class="badge bg-${caido.dias_sin_compra > 60 ? 'danger' : (caido.dias_sin_compra > 30 ? 'warning' : 'info')}">
                        ${caido.dias_sin_compra} días
                    </span>
                </td>
                <td>
                    <span class="badge bg-${caido.categoria_caido === 'Crítico' ? 'danger' : (caido.categoria_caido === 'Alto Riesgo' ? 'warning' : 'info')}">
                        ${caido.categoria_caido}
                    </span>
                </td>
                <td>
                    <span class="badge bg-primary">${caido.total_compras_historicas.toLocaleString()}</span>
                </td>
                <td>
                    <span class="text-success fw-bold">$${caido.total_ventas_historicas.toLocaleString()}</span>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="reactivateClient(${caido.cliente_sk})" title="Campaña de Reactivación">
                            <i class="fas fa-envelope"></i>
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="viewClientDetail(${caido.cliente_sk})" title="Ver Detalle">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tableBody.innerHTML = html;
    
    // Cambiar el botón para mostrar menos
    showAllBtn.textContent = 'Mostrar solo los primeros 20';
    showAllBtn.onclick = showLessCaidos;
}

function showLessCaidos() {
    const tableBody = document.querySelector('#caidosTable tbody');
    const showLessBtn = document.querySelector('.btn-outline-primary[onclick="showLessCaidos()"]');
    
    if (!tableBody || !showLessBtn) return;
    
    // Obtener solo los primeros 20 clientes caídos
    const allCaidos = <?= json_encode($clientes_caidos) ?>;
    const first20 = allCaidos.slice(0, 20);
    
    // Limpiar el tbody actual
    let html = '';
    
    first20.forEach(caido => {
        html += `
            <tr>
                <td>
                    <strong>${caido.cliente_nombre}</strong>
                    <br><small class="text-muted">ID: ${caido.cliente_sk}</small>
                    ${caido.email ? `<br><small class="text-muted">${caido.email}</small>` : ''}
                </td>
                <td>
                    <span class="badge bg-secondary">${caido.segmento}</span>
                </td>
                <td>
                    <small>${new Date(caido.ultima_compra).toLocaleDateString('es-ES')}</small>
                </td>
                <td>
                    <span class="badge bg-${caido.dias_sin_compra > 60 ? 'danger' : (caido.dias_sin_compra > 30 ? 'warning' : 'info')}">
                        ${caido.dias_sin_compra} días
                    </span>
                </td>
                <td>
                    <span class="badge bg-${caido.categoria_caido === 'Crítico' ? 'danger' : (caido.categoria_caido === 'Alto Riesgo' ? 'warning' : 'info')}">
                        ${caido.categoria_caido}
                    </span>
                </td>
                <td>
                    <span class="badge bg-primary">${caido.total_compras_historicas.toLocaleString()}</span>
                </td>
                <td>
                    <span class="text-success fw-bold">$${caido.total_ventas_historicas.toLocaleString()}</span>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="reactivateClient(${caido.cliente_sk})" title="Campaña de Reactivación">
                            <i class="fas fa-envelope"></i>
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="viewClientDetail(${caido.cliente_sk})" title="Ver Detalle">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tableBody.innerHTML = html;
    
    // Cambiar el botón para mostrar todos
    showLessBtn.textContent = `Ver todos los ${allCaidos.length} clientes caídos`;
    showLessBtn.onclick = showAllCaidos;
}
</script>

<style>
/* CSS para mejorar el layout de 5 columnas */
.col-xl-2-4 {
    flex: 0 0 auto;
    width: 20%;
}

@media (max-width: 1199.98px) {
    .col-xl-2-4 {
        width: 50%;
    }
}

@media (max-width: 767.98px) {
    .col-xl-2-4 {
        width: 100%;
    }
}
</style>
<?= $this->endSection() ?>
