<?= $this->extend('layout/dashboard') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">📋 Tabla: <?= esc($tableName) ?></h5>
            <div class="btn-group">
                <span class="badge bg-primary">Total: <?= number_format($totalRecords) ?> registros</span>
                <a href="/dashboard/admin/database" class="btn btn-sm btn-secondary">← Volver</a>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Información de la tabla -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6>📊 Estructura de la Tabla</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Campo</th>
                                    <th>Tipo</th>
                                    <th>Nulo</th>
                                    <th>Clave</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($columns as $column): ?>
                                    <tr>
                                        <td><code><?= esc($column['name']) ?></code></td>
                                        <td><span class="badge bg-info"><?= esc($column['type']) ?></span></td>
                                        <td><?= $column['notnull'] ? '❌' : '✅' ?></td>
                                        <td><?= $column['pk'] ? '🔑' : '' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <h6>🛠️ Herramientas</h6>
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary" onclick="showAllData()">
                            <i class="fas fa-eye"></i> Ver Todos los Datos
                        </button>
                        
                        <!-- Grupo de exportación -->
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-download"></i> Exportar Tabla
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="exportTable('csv')">
                                    <i class="fas fa-file-csv"></i> CSV (<?= number_format($totalRecords) ?> registros)
                                </a></li>
                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="exportTable('csv', 1000)">
                                    <i class="fas fa-file-csv"></i> CSV (1,000 registros)
                                </a></li>
                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="exportTable('json')">
                                    <i class="fas fa-file-code"></i> JSON (<?= number_format($totalRecords) ?> registros)
                                </a></li>
                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="exportTable('json', 1000)">
                                    <i class="fas fa-file-code"></i> JSON (1,000 registros)
                                </a></li>
                            </ul>
                        </div>
                        
                        <button class="btn btn-warning" onclick="analyzeData()">
                            <i class="fas fa-chart-bar"></i> Analizar Datos
                        </button>
                    </div>
                </div>
            </div>

            <!-- Paginación -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <nav aria-label="Paginación">
                        <ul class="pagination">
                            <?php if ($currentOffset > 0): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?offset=<?= max(0, $currentOffset - $currentLimit) ?>&limit=<?= $currentLimit ?>">« Anterior</a>
                                </li>
                            <?php endif; ?>
                            
                            <li class="page-item active">
                                <span class="page-link">
                                    Registros <?= $currentOffset + 1 ?> - <?= min($currentOffset + $currentLimit, $totalRecords) ?>
                                </span>
                            </li>
                            
                            <?php if ($currentOffset + $currentLimit < $totalRecords): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?offset=<?= $currentOffset + $currentLimit ?>&limit=<?= $currentLimit ?>">Siguiente »</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
                <div class="col-md-6 text-end">
                    <form method="GET" class="d-inline-flex align-items-center">
                        <label for="limit" class="form-label me-2 mb-0">Mostrar:</label>
                        <select name="limit" id="limit" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                            <option value="10" <?= $currentLimit == 10 ? 'selected' : '' ?>>10</option>
                            <option value="25" <?= $currentLimit == 25 ? 'selected' : '' ?>>25</option>
                            <option value="50" <?= $currentLimit == 50 ? 'selected' : '' ?>>50</option>
                            <option value="100" <?= $currentLimit == 100 ? 'selected' : '' ?>>100</option>
                        </select>
                        <input type="hidden" name="offset" value="<?= $currentOffset ?>">
                    </form>
                </div>
            </div>

            <!-- Datos de la tabla -->
            <?php if (!empty($tableData)): ?>
                <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark sticky-top">
                            <tr>
                                <?php foreach (array_keys($tableData[0]) as $column): ?>
                                    <th class="text-nowrap"><?= esc($column) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tableData as $row): ?>
                                <tr>
                                    <?php foreach ($row as $value): ?>
                                        <td class="text-nowrap">
                                            <?php 
                                            $displayValue = $value;
                                            if (is_null($displayValue)) {
                                                echo '<span class="text-muted fst-italic">NULL</span>';
                                            } elseif (strlen($displayValue) > 50) {
                                                echo '<span title="' . esc($displayValue) . '">' . esc(substr($displayValue, 0, 50)) . '...</span>';
                                            } else {
                                                echo esc($displayValue);
                                            }
                                            ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <h6>📭 Tabla Vacía</h6>
                    <p class="mb-0">Esta tabla no contiene datos actualmente.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function showAllData() {
    if (confirm('¿Mostrar todos los registros? Esto puede ser lento para tablas grandes.')) {
        window.location.href = '?limit=1000000&offset=0';
    }
}

function exportTable(format = 'csv', limit = null) {
    const tableName = '<?= esc($tableName) ?>';
    const baseUrl = '<?= base_url() ?>dashboard/admin/database/export/' + tableName;
    
    // Construir parámetros
    let params = new URLSearchParams();
    params.append('format', format);
    if (limit) {
        params.append('limit', limit);
    }
    
    const exportUrl = baseUrl + '?' + params.toString();
    
    // Mostrar mensaje de progreso
    const toast = document.createElement('div');
    toast.className = 'toast align-items-center text-white bg-info border-0 position-fixed';
    toast.style.top = '20px';
    toast.style.right = '20px';
    toast.style.zIndex = '9999';
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-download me-2"></i>
                Preparando exportación...
            </div>
        </div>
    `;
    document.body.appendChild(toast);
    
    // Crear enlace de descarga invisible
    const link = document.createElement('a');
    link.href = exportUrl;
    link.style.display = 'none';
    document.body.appendChild(link);
    
    // Iniciar descarga
    link.click();
    
    // Remover elementos temporales
    setTimeout(() => {
        document.body.removeChild(link);
        document.body.removeChild(toast);
        
        // Mostrar confirmación
        showSuccessToast(`Exportación ${format.toUpperCase()} iniciada`, 'success');
    }, 1000);
}

function analyzeData() {
    const tableName = '<?= esc($tableName) ?>';
    alert(`Análisis de datos para ${tableName} - Función en desarrollo...`);
}

function showSuccessToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0 position-fixed`;
    toast.style.top = '80px';
    toast.style.right = '20px';
    toast.style.zIndex = '9999';
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-check me-2"></i>
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
    `;
    document.body.appendChild(toast);
    
    // Auto-remove después de 3 segundos
    setTimeout(() => {
        if (document.body.contains(toast)) {
            document.body.removeChild(toast);
        }
    }, 3000);
}

// Agregar estilos para los toasts
const style = document.createElement('style');
style.textContent = `
    .toast {
        min-width: 300px;
        animation: slideInRight 0.3s ease-out;
    }
    
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
`;
document.head.appendChild(style);
</script>
<?= $this->endSection() ?>