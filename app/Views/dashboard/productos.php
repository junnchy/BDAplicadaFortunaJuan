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
        <i class="fas fa-box me-2"></i>
        Análisis de Productos
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <select class="form-select" id="yearFilter" onchange="updateProductAnalysis()">
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
            Análisis de Productos
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
                            <option value="cantidad">Cantidad Vendida</option>
                            <option value="margen">Margen</option>
                            <option value="transacciones">Transacciones</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="topCount" class="form-label">Mostrar Top:</label>
                        <select class="form-select" id="topCount" onchange="updateCharts()">
                            <option value="10">Top 10</option>
                            <option value="20">Top 20</option>
                            <option value="50">Top 50</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="chartTypeSelect" class="form-label">Tipo de Gráfico:</label>
                        <select class="form-select" id="chartTypeSelect" onchange="updateCharts()">
                            <option value="bar">Barras</option>
                            <option value="horizontalBar">Barras Horizontales</option>
                            <option value="pie">Circular</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-primary w-100" onclick="updateProductAnalysis()">
                            <i class="fas fa-sync-alt me-1"></i>
                            Actualizar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas Generales -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card stat-card bg-primary">
            <div class="card-body text-white">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            Total Productos
                        </div>
                        <div class="stat-number">
                            <?= number_format($product_stats['total_productos'] ?? 0) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-boxes fa-2x text-white-50"></i>
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
                            Producto Top Ventas
                        </div>
                        <div class="stat-number small">
                            <?= $product_stats['top_producto_ventas'] ?? 'N/A' ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-crown fa-2x text-white-50"></i>
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
                            Producto Top Margen
                        </div>
                        <div class="stat-number small">
                            <?= $product_stats['top_producto_margen'] ?? 'N/A' ?>
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
                            Ventas Promedio
                        </div>
                        <div class="stat-number">
                            $<?= number_format($product_stats['promedio_ventas'] ?? 0, 2) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calculator fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos de Productos -->
<div class="row mb-4">
    <!-- Gráfico Principal -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0" id="main-chart-title">
                    <i class="fas fa-chart-bar me-2"></i>
                    Top Productos por Ventas
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="productsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Comparación -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Distribución Top 10
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

<!-- Análisis Comparativo -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-balance-scale me-2"></i>
                    Análisis Comparativo: Ventas vs Margen
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 400px;">
                    <canvas id="scatterChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla Detallada de Productos -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-table me-2"></i>
                    Ranking Detallado de Productos
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="productsTable">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>
                                    <i class="fas fa-box me-1"></i>
                                    Producto
                                </th>
                                <th class="text-end">
                                    <i class="fas fa-dollar-sign me-1"></i>
                                    Total Ventas
                                </th>
                                <th class="text-end">
                                    <i class="fas fa-cubes me-1"></i>
                                    Cantidad
                                </th>
                                <th class="text-end">
                                    <i class="fas fa-percent me-1"></i>
                                    Margen
                                </th>
                                <th class="text-end">
                                    <i class="fas fa-receipt me-1"></i>
                                    Transacciones
                                </th>
                                <th class="text-end">
                                    <i class="fas fa-calculator me-1"></i>
                                    Precio Prom.
                                </th>
                                <th class="text-center">
                                    <i class="fas fa-chart-line me-1"></i>
                                    Performance
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($product_data)): ?>
                                <?php foreach ($product_data as $index => $product): ?>
                                    <tr>
                                        <td class="fw-bold"><?= $index + 1 ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="product-indicator bg-primary"></div>
                                                <span><?= htmlspecialchars($product['producto_nombre'] ?? ('Producto ' . $product['producto_sk'])) ?></span>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            $<?= number_format($product['total_ventas'], 2) ?>
                                        </td>
                                        <td class="text-end">
                                            <?= number_format($product['cantidad_total']) ?>
                                        </td>
                                        <td class="text-end">
                                            $<?= number_format($product['margen_total'], 2) ?>
                                        </td>
                                        <td class="text-end">
                                            <?= number_format($product['transacciones']) ?>
                                        </td>
                                        <td class="text-end">
                                            $<?= number_format($product['precio_promedio'], 2) ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $performance = $product['performance_score'] ?? 0;
                                            if ($performance >= 80) {
                                                $badge = 'success';
                                                $icon = 'fa-star';
                                                $text = 'Excelente';
                                            } elseif ($performance >= 60) {
                                                $badge = 'warning';
                                                $icon = 'fa-thumbs-up';
                                                $text = 'Bueno';
                                            } else {
                                                $badge = 'danger';
                                                $icon = 'fa-thumbs-down';
                                                $text = 'Regular';
                                            }
                                            ?>
                                            <span class="badge bg-<?= $badge ?>">
                                                <i class="fas <?= $icon ?> me-1"></i>
                                                <?= $text ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">
                                        <i class="fas fa-info-circle me-2"></i>
                                        No hay datos de productos disponibles
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
let productsChart = null;
let distributionChart = null;
let scatterChart = null;

document.addEventListener('DOMContentLoaded', function() {
    initCharts();
    addCustomStyles();
});

function initCharts() {
    initProductsChart();
    initDistributionChart();
    initScatterChart();
}

function initProductsChart() {
    const ctx = document.getElementById('productsChart').getContext('2d');
    const productData = <?= json_encode($product_data ?? []) ?>;
    
    productsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: productData.slice(0, 10).map(item => item.producto_nombre || `Producto ${item.producto_sk}`),
            datasets: [{
                label: 'Ventas ($)',
                data: productData.slice(0, 10).map(item => parseFloat(item.total_ventas)),
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
            }
        }
    });
}

function initDistributionChart() {
    const ctx = document.getElementById('distributionChart').getContext('2d');
    const productData = <?= json_encode($product_data ?? []) ?>;
    
    distributionChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: productData.slice(0, 10).map(item => item.producto_nombre || `Prod. ${item.producto_sk}`),
            datasets: [{
                data: productData.slice(0, 10).map(item => parseFloat(item.total_ventas)),
                backgroundColor: generateProductColors(10),
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
                        boxWidth: 12,
                        padding: 8,
                        font: {
                            size: 10
                        }
                    }
                }
            }
        }
    });
}

function initScatterChart() {
    const ctx = document.getElementById('scatterChart').getContext('2d');
    const productData = <?= json_encode($product_data ?? []) ?>;
    
    const scatterData = productData.map(item => ({
        x: parseFloat(item.total_ventas),
        y: parseFloat(item.margen_total),
        label: item.producto_nombre || `Producto ${item.producto_sk}`
    }));

    scatterChart = new Chart(ctx, {
        type: 'scatter',
        data: {
            datasets: [{
                label: 'Productos',
                data: scatterData,
                backgroundColor: 'rgba(255, 99, 132, 0.6)',
                borderColor: 'rgba(255, 99, 132, 1)',
                pointRadius: 8,
                pointHoverRadius: 12
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
                            const point = context.raw;
                            return `${point.label}: Ventas $${point.x.toLocaleString()}, Margen $${point.y.toLocaleString()}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Total Ventas ($)'
                    },
                    ticks: {
                        callback: function(value) {
                            return formatCurrency(value);
                        }
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Margen ($)'
                    },
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

function updateProductAnalysis() {
    const year = document.getElementById('yearFilter').value;
    
    showLoading('productsTable');
    
    // Recargar página con nuevo año
    window.location.href = `/dashboard/productos?year=${year}`;
}

function updateCharts() {
    const metric = document.getElementById('metricSelect').value;
    const topCount = parseInt(document.getElementById('topCount').value);
    const chartType = document.getElementById('chartTypeSelect').value;
    
    updateMainChart(metric, topCount, chartType);
    updateChartTitle(metric);
}

function updateMainChart(metric, topCount, chartType) {
    if (!productsChart) return;
    
    const productData = <?= json_encode($product_data ?? []) ?>;
    const limitedData = productData.slice(0, topCount);
    
    let dataValues = [];
    let label = '';
    
    switch(metric) {
        case 'ventas':
            dataValues = limitedData.map(item => parseFloat(item.total_ventas));
            label = 'Ventas ($)';
            break;
        case 'cantidad':
            dataValues = limitedData.map(item => parseInt(item.cantidad_total));
            label = 'Cantidad Vendida';
            break;
        case 'margen':
            dataValues = limitedData.map(item => parseFloat(item.margen_total));
            label = 'Margen ($)';
            break;
        case 'transacciones':
            dataValues = limitedData.map(item => parseInt(item.transacciones));
            label = 'Transacciones';
            break;
    }
    
    // Actualizar tipo de gráfico si es necesario
    if (productsChart.config.type !== chartType) {
        productsChart.destroy();
        const ctx = document.getElementById('productsChart').getContext('2d');
        
        const config = {
            type: chartType === 'horizontalBar' ? 'bar' : chartType,
            data: {
                labels: limitedData.map(item => item.producto_nombre || `Producto ${item.producto_sk}`),
                datasets: [{
                    label: label,
                    data: dataValues,
                    backgroundColor: chartType === 'pie' ? 
                        generateProductColors(limitedData.length) : 
                        'rgba(54, 162, 235, 0.6)',
                    borderColor: chartType === 'pie' ? 
                        generateProductColors(limitedData.length) : 
                        'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: chartType === 'horizontalBar' ? 'y' : 'x',
                plugins: {
                    legend: {
                        display: chartType === 'pie'
                    }
                }
            }
        };
        
        if (chartType !== 'pie') {
            config.options.scales = {
                [chartType === 'horizontalBar' ? 'x' : 'y']: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return metric === 'ventas' || metric === 'margen' ? 
                                formatCurrency(value) : 
                                value.toLocaleString();
                        }
                    }
                }
            };
        }
        
        productsChart = new Chart(ctx, config);
    } else {
        // Solo actualizar datos
        productsChart.data.labels = limitedData.map(item => item.producto_nombre || `Producto ${item.producto_sk}`);
        productsChart.data.datasets[0].data = dataValues;
        productsChart.data.datasets[0].label = label;
        productsChart.update();
    }
}

function updateChartTitle(metric) {
    const titles = {
        'ventas': 'Top Productos por Ventas',
        'cantidad': 'Top Productos por Cantidad',
        'margen': 'Top Productos por Margen',
        'transacciones': 'Top Productos por Transacciones'
    };
    
    const titleElement = document.getElementById('main-chart-title');
    if (titleElement && titles[metric]) {
        titleElement.innerHTML = `<i class="fas fa-chart-bar me-2"></i>${titles[metric]}`;
    }
}

function generateProductColors(count) {
    const colors = [
        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
        '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384',
        '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
    ];
    
    return Array.from({length: count}, (_, i) => colors[i % colors.length]);
}

function addCustomStyles() {
    const style = document.createElement('style');
    style.textContent = `
        .product-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
            display: inline-block;
        }
        .chart-container {
            position: relative;
            height: 350px;
        }
        .stat-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
    `;
    document.head.appendChild(style);
}
</script>
<?= $this->endSection() ?>