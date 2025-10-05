<?= $this->extend('layout/dashboard') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="row">
        <!-- Lista de Tablas -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">📊 Tablas de la Base de Datos</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php foreach ($tables as $table): ?>
                            <a href="<?= base_url() ?>/dashboard/admin/database/table/<?= esc($table) ?>" 
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <span>📋 <?= esc($table) ?></span>
                                <small class="badge bg-primary rounded-pill" id="count-<?= esc($table) ?>">...</small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Principal -->
        <div class="col-md-8">
            <div class="row">
                <!-- Estadísticas Generales -->
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">📈 Estadísticas de la Base de Datos</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h3 class="text-primary"><?= count($tables) ?></h3>
                                        <p class="mb-0">Tablas</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h3 class="text-success" id="total-records">...</h3>
                                        <p class="mb-0">Registros Totales</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h3 class="text-info" id="db-size">...</h3>
                                        <p class="mb-0">Tamaño BD</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h3 class="text-warning">SQLite</h3>
                                        <p class="mb-0">Motor</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones Rápidas -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">🛠️ Herramientas</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <a href="<?= base_url() ?>/dashboard/admin/database/query" class="btn btn-primary btn-lg w-100">
                                        <i class="fas fa-code"></i>
                                        Ejecutor de Consultas SQL
                                    </a>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <button class="btn btn-info btn-lg w-100" onclick="exportData()">
                                        <i class="fas fa-download"></i>
                                        Exportar Datos
                                    </button>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <button class="btn btn-success btn-lg w-100" onclick="showTableStats()">
                                        <i class="fas fa-chart-bar"></i>
                                        Estadísticas Detalladas
                                    </button>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <button class="btn btn-warning btn-lg w-100" onclick="optimizeDatabase()">
                                        <i class="fas fa-cog"></i>
                                        Optimizar BD
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Cargar conteos de registros para cada tabla
document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = '<?= base_url() ?>';
    
    <?php foreach ($tables as $table): ?>
        fetch(baseUrl + '/dashboard/admin/database/count/<?= esc($table) ?>', {
            method: 'GET',
            credentials: 'same-origin'
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                const element = document.getElementById('count-<?= esc($table) ?>');
                if (element && data.count !== undefined) {
                    element.textContent = data.count.toLocaleString();
                }
            })
            .catch(error => {
                console.error('Error fetching count for <?= esc($table) ?>:', error);
                const element = document.getElementById('count-<?= esc($table) ?>');
                if (element) {
                    element.textContent = 'Error';
                    element.classList.add('bg-danger');
                }
            });
    <?php endforeach; ?>
    
    // Cargar estadísticas generales
    loadGeneralStats();
});

function loadGeneralStats() {
    const baseUrl = '<?= base_url() ?>';
    
    fetch(baseUrl + '/dashboard/admin/database/stats', {
        method: 'GET',
        credentials: 'same-origin'
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            const totalElement = document.getElementById('total-records');
            const sizeElement = document.getElementById('db-size');
            
            if (totalElement && data.totalRecords !== undefined) {
                totalElement.textContent = data.totalRecords.toLocaleString();
            }
            if (sizeElement && data.dbSize !== undefined) {
                sizeElement.textContent = data.dbSize;
            }
        })
        .catch(error => {
            console.error('Error fetching general stats:', error);
            const totalElement = document.getElementById('total-records');
            const sizeElement = document.getElementById('db-size');
            
            if (totalElement) {
                totalElement.textContent = 'Error';
                totalElement.classList.add('text-danger');
            }
            if (sizeElement) {
                sizeElement.textContent = 'Error';
                sizeElement.classList.add('text-danger');
            }
        });
}

function exportData() {
    alert('Función de exportación en desarrollo...');
}

function showTableStats() {
    alert('Función de estadísticas detalladas en desarrollo...');
}

function optimizeDatabase() {
    if (confirm('¿Está seguro de que desea optimizar la base de datos?')) {
        alert('Función de optimización en desarrollo...');
    }
}
</script>
<?= $this->endSection() ?>