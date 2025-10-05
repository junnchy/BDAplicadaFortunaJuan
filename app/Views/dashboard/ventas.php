<?= $this->extend('layout/dashboard') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-chart-bar me-2"></i>
        Análisis de Ventas
        <span class="text-muted">(<?= ucfirst($level) ?>)</span>
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <!-- Botones de navegación -->
        <div class="btn-group me-2">
            <?php if ($level !== 'year'): ?>
                <a href="/dashboard/ventas?level=<?= $parent_level ?><?= $parent_id ? '&id=' . $parent_id : '' ?>" 
                   class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-up me-1"></i>
                    Drill Up
                </a>
            <?php endif; ?>
            <a href="/dashboard" class="btn btn-outline-primary">
                <i class="fas fa-home me-1"></i>
                Dashboard
            </a>
        </div>
    </div>
</div>

<!-- Breadcrumb Navigation -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="/dashboard">
                <i class="fas fa-home"></i>
                Dashboard
            </a>
        </li>
        <li class="breadcrumb-item">
            <a href="/dashboard/ventas">Ventas</a>
        </li>
        <?php if (isset($breadcrumb_path) && !empty($breadcrumb_path)): ?>
            <?php foreach ($breadcrumb_path as $crumb): ?>
                <li class="breadcrumb-item">
                    <a href="<?= $crumb['url'] ?>"><?= $crumb['title'] ?></a>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
        <li class="breadcrumb-item active" aria-current="page">
            <?= ucfirst($level) ?> <?= $current_period ?? '' ?>
        </li>
    </ol>
</nav>

<!-- Estadísticas del Nivel Actual -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card bg-primary">
            <div class="card-body text-white">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            Total Ventas
                        </div>
                        <div class="stat-number">
                            $<?= number_format($level_stats['total_ventas'] ?? 0, 2) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card bg-success">
            <div class="card-body text-white">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            Transacciones
                        </div>
                        <div class="stat-number">
                            <?= number_format($level_stats['total_transacciones'] ?? 0) ?>
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
        <div class="card stat-card bg-info">
            <div class="card-body text-white">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            Ticket Promedio
                        </div>
                        <div class="stat-number">
                            $<?= number_format($level_stats['ticket_promedio'] ?? 0, 2) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calculator fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card bg-warning">
            <div class="card-body text-white">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            Margen Total
                        </div>
                        <div class="stat-number">
                            $<?= number_format($level_stats['margen_total'] ?? 0, 2) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-percent fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gráfico de Nivel Actual -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-area me-2"></i>
                    Ventas por <?= ucfirst($level) ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Distribución de Ventas
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="distributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Drill-Down -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-table me-2"></i>
                    Desglose Detallado
                    <?php if ($level !== 'month'): ?>
                        <small class="text-muted">(Haz clic para hacer drill-down)</small>
                    <?php endif; ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="drilldownTable">
                        <thead class="table-dark">
                            <tr>
                                <th>
                                    <i class="fas fa-calendar me-1"></i>
                                    <?= $level === 'year' ? 'Año' : ($level === 'quarter' ? 'Trimestre' : 'Mes') ?>
                                </th>
                                <th class="text-end">
                                    <i class="fas fa-dollar-sign me-1"></i>
                                    Total Ventas
                                </th>
                                <th class="text-end">
                                    <i class="fas fa-receipt me-1"></i>
                                    Transacciones
                                </th>
                                <th class="text-end">
                                    <i class="fas fa-calculator me-1"></i>
                                    Ticket Promedio
                                </th>
                                <th class="text-end">
                                    <i class="fas fa-percent me-1"></i>
                                    Margen
                                </th>
                                <th class="text-center">
                                    <i class="fas fa-chart-line me-1"></i>
                                    Tendencia
                                </th>
                                <?php if ($level !== 'month'): ?>
                                <th class="text-center">
                                    <i class="fas fa-search-plus me-1"></i>
                                    Acciones
                                </th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($drill_data)): ?>
                                <?php foreach ($drill_data as $index => $row): ?>
                                    <tr class="<?= $level !== 'month' ? 'cursor-pointer drill-row' : '' ?>" 
                                        <?= $level !== 'month' ? 'onclick="drillDown(\'' . $row['id'] . '\', \'' . htmlspecialchars($row['periodo']) . '\')"' : '' ?>>
                                        <td class="fw-bold">
                                            <?= htmlspecialchars($row['periodo']) ?>
                                        </td>
                                        <td class="text-end">
                                            $<?= number_format($row['total_ventas'], 2) ?>
                                        </td>
                                        <td class="text-end">
                                            <?= number_format($row['transacciones']) ?>
                                        </td>
                                        <td class="text-end">
                                            $<?= number_format($row['ticket_promedio'], 2) ?>
                                        </td>
                                        <td class="text-end">
                                            $<?= number_format($row['margen'], 2) ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $trend = $row['tendencia'] ?? 0;
                                            if ($trend > 0) {
                                                echo '<i class="fas fa-arrow-up text-success" title="Crecimiento"></i>';
                                            } elseif ($trend < 0) {
                                                echo '<i class="fas fa-arrow-down text-danger" title="Decrecimiento"></i>';
                                            } else {
                                                echo '<i class="fas fa-minus text-warning" title="Estable"></i>';
                                            }
                                            ?>
                                            <small class="ms-1"><?= number_format(abs($trend), 1) ?>%</small>
                                        </td>
                                        <?php if ($level !== 'month'): ?>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="event.stopPropagation(); drillDown('<?= $row['id'] ?>', '<?= htmlspecialchars($row['periodo']) ?>')">
                                                <i class="fas fa-search-plus"></i>
                                                Drill Down
                                            </button>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="<?= $level === 'month' ? '6' : '7' ?>" class="text-center text-muted">
                                        <i class="fas fa-info-circle me-2"></i>
                                        No hay datos disponibles para este período
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let salesChart = null;
let distributionChart = null;

document.addEventListener('DOMContentLoaded', function() {
    initCharts();
});

function initCharts() {
    // Gráfico de ventas principal
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    const salesData = <?= json_encode($chart_data['sales'] ?? []) ?>;
    
    salesChart = new Chart(salesCtx, {
        type: 'bar',
        data: {
            labels: salesData.map(item => item.periodo),
            datasets: [{
                label: 'Ventas ($)',
                data: salesData.map(item => parseFloat(item.total_ventas)),
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
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
            },
            onClick: function(event, elements) {
                if (elements.length > 0 && '<?= $level ?>' !== 'month') {
                    const index = elements[0].index;
                    const data = salesData[index];
                    drillDown(data.id, data.periodo);
                }
            }
        }
    });

    // Gráfico de distribución
    const distributionCtx = document.getElementById('distributionChart').getContext('2d');
    const distributionData = salesData.slice(0, 10); // Top 10 para el pie chart
    
    distributionChart = new Chart(distributionCtx, {
        type: 'pie',
        data: {
            labels: distributionData.map(item => item.periodo),
            datasets: [{
                data: distributionData.map(item => parseFloat(item.total_ventas)),
                backgroundColor: generateDistributionColors(distributionData.length),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 15,
                        padding: 10
                    }
                }
            }
        }
    });
}

function drillDown(id, periodo) {
    console.log('drillDown called with:', { id, periodo });
    
    const currentLevel = '<?= $level ?>';
    console.log('Current level:', currentLevel);
    
    let nextLevel = '';
    
    switch(currentLevel) {
        case 'year':
            nextLevel = 'quarter';
            break;
        case 'quarter':
            nextLevel = 'month';
            break;
        default:
            return; // Ya estamos en el nivel más bajo
    }
    
    showLoading('drilldownTable');
    
    // Navegar al siguiente nivel
    const url = `/dashboard/ventas?level=${nextLevel}&parent_id=${id}&year=<?= $selected_year ?>`;
    console.log('Drilling down to:', url);
    window.location.href = url;
}

function generateDistributionColors(count) {
    const baseColors = [
        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
        '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
    ];
    
    return Array.from({length: count}, (_, i) => baseColors[i % baseColors.length]);
}

// Añadir estilo hover para las filas de drill-down
document.addEventListener('DOMContentLoaded', function() {
    const style = document.createElement('style');
    style.textContent = `
        .drill-row {
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .drill-row:hover {
            background-color: rgba(13, 110, 253, 0.1);
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
    `;
    document.head.appendChild(style);
});
</script>
<?= $this->endSection() ?>