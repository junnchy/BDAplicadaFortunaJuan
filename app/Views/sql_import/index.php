<?= $this->extend('layout/default') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="fas fa-database me-2"></i>
                    Importar SQL
                </h2>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                    <i class="fas fa-upload me-1"></i>
                    Subir Archivo SQL
                </button>
            </div>

            <?php if (!empty($files)): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>
                            Archivos SQL Disponibles (<?= count($files) ?>)
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Archivo</th>
                                        <th>Tamaño</th>
                                        <th>Última Modificación</th>
                                        <th>Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($files as $file): ?>
                                        <tr class="<?= isset($file['imported']) && $file['imported'] ? 'file-row-imported' : '' ?>">
                                            <td>
                                                <i class="fas fa-file-code me-2 text-primary"></i>
                                                <strong><?= esc($file['name']) ?></strong>
                                            </td>
                                            <td><?= esc($file['size_formatted']) ?></td>
                                            <td><?= esc($file['modified']) ?></td>
                                            <td>
                                                <?php if (isset($file['imported']) && $file['imported']): ?>
                                                    <span class="status-indicator status-imported"></span>
                                                    <span class="badge bg-success">Importado</span>
                                                <?php else: ?>
                                                    <span class="status-indicator status-pending"></span>
                                                    <span class="badge bg-warning">Pendiente</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="action-buttons">
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-info" 
                                                            onclick="previewFile('<?= esc($file['name']) ?>')">
                                                        <i class="fas fa-eye me-1"></i>Preview
                                                    </button>
                                                    
                                                    <?php if (isset($file['imported']) && $file['imported']): ?>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-warning" 
                                                                onclick="reimportFile('<?= esc($file['name']) ?>')">
                                                            <i class="fas fa-redo me-1"></i>Re-importar
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-success" 
                                                                onclick="importFile('<?= esc($file['name']) ?>')">
                                                            <i class="fas fa-play me-1"></i>Importar
                                                        </button>
                                                    <?php endif; ?>
                                                    
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteFile('<?= esc($file['name']) ?>')">
                                                        <i class="fas fa-trash me-1"></i>Eliminar
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
            <?php else: ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-file-import fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No hay archivos SQL disponibles</h5>
                        <p class="text-muted">Sube tu primer archivo SQL para comenzar</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            <i class="fas fa-upload me-1"></i>
                            Subir Archivo SQL
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($tables)): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-table me-2"></i>
                            Tablas en la Base de Datos (<?= count($tables) ?>)
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tabla</th>
                                        <th>Registros</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tables as $table): ?>
                                        <tr>
                                            <td>
                                                <i class="fas fa-table me-2 text-success"></i>
                                                <strong><?= esc($table['name']) ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?= number_format($table['records']) ?> registros</span>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-primary" 
                                                        onclick="showTableInfo('<?= esc($table['name']) ?>')">
                                                    <i class="fas fa-info-circle me-1"></i>Info
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Upload -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-upload me-2"></i>
                    Subir Archivo SQL
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="uploadForm">
                    <div class="mb-3">
                        <label for="sql_file" class="form-label">Seleccionar archivo SQL:</label>
                        <input type="file" 
                               class="form-control" 
                               id="sql_file" 
                               name="sql_file" 
                               accept=".sql" 
                               required>
                        <div class="form-text">
                            Solo se permiten archivos .sql (máximo 50MB)
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="uploadButton">
                    <i class="fas fa-upload me-1"></i>
                    Subir Archivo
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Preview -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye me-2"></i>
                    Preview del Archivo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="previewContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2">Cargando preview...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-success" id="importFromPreview">
                    <i class="fas fa-play me-1"></i>
                    Importar Este Archivo
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Result -->
<div class="modal fade" id="resultModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i>
                    Resultado de Importación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="resultContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="location.reload()">
                    <i class="fas fa-refresh me-1"></i>
                    Actualizar Página
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Table Info -->
<div class="modal fade" id="tableInfoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-table me-2"></i>
                    Información de Tabla
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="tableInfoContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2">Cargando información de la tabla...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
.file-preview {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 1rem;
    max-height: 400px;
    overflow-y: auto;
    font-family: 'Courier New', monospace;
    font-size: 0.875rem;
    line-height: 1.4;
}

.file-preview div {
    white-space: pre-wrap;
    margin: 0;
    padding: 2px 0;
}

.file-preview div:nth-child(even) {
    background-color: rgba(0,0,0,0.02);
}

.file-preview .text-muted {
    background-color: transparent !important;
    font-style: italic;
}

.file-actions {
    min-width: 120px;
}

.status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 0.5rem;
}

.status-imported {
    background-color: #28a745;
}

.status-pending {
    background-color: #ffc107;
}

.file-row-imported {
    border-left: 4px solid #28a745 !important;
}

.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 110px;
}
</style>

<script src="/js/sql-import.js"></script>

<?= $this->endSection() ?>