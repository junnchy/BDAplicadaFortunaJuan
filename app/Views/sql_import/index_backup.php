<?= $this->extend('layout/dashboard') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-database me-2"></i>
        Importación SQL
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#uploadModal">
                <i class="fas fa-upload me-1"></i>
                Subir SQL
            </button>
            <button type="button" class="btn btn-outline-secondary" onclick="location.reload()">
                <i class="fas fa-sync-alt me-1"></i>
                Actualizar
            </button>
        </div>
    </div>
</div>

<!-- Estado de las Tablas -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-table me-2"></i>
                    Estado de las Tablas
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($tables_info as $table): ?>
                        <div class="col-md-3 mb-3">
                            <div class="card table-status-card">
                                <div class="card-body text-center">
                                    <h6 class="card-title"><?= esc($table['name']) ?></h6>
                                    <div class="status-badge <?= $table['status'] === 'populated' ? 'bg-success' : ($table['status'] === 'empty' ? 'bg-warning' : 'bg-danger') ?>">
                                        <?php if ($table['status'] === 'populated'): ?>
                                            <i class="fas fa-check-circle"></i>
                                        <?php elseif ($table['status'] === 'empty'): ?>
                                            <i class="fas fa-exclamation-triangle"></i>
                                        <?php else: ?>
                                            <i class="fas fa-times-circle"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mt-2">
                                        <strong><?= number_format($table['records']) ?></strong>
                                        <small class="text-muted d-block">registros</small>
                                    </div>
                                    <button class="btn btn-sm btn-outline-primary mt-2" 
                                            onclick="showTableInfo('<?= esc($table['name']) ?>')">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Ver Info
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Archivos SQL Disponibles -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-file-code me-2"></i>
                    Archivos SQL Disponibles
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($sql_files)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Archivo</th>
                                    <th>Estado</th>
                                    <th>Tamaño</th>
                                    <th>Statements</th>
                                    <th>Modificado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sql_files as $file): ?>
                                    <tr class="<?= $file['imported'] ? 'table-success file-row-imported' : '' ?>">
                                        <td>
                                            <i class="fas fa-file-code text-primary me-2"></i>
                                            <strong><?= esc($file['name']) ?></strong>
                                            <?php if ($file['imported']): ?>
                                                <br><small class="text-success import-status">
                                                    <i class="fas fa-check-circle me-1"></i>
                                                    Importado: <?= date('d/m/Y H:i', strtotime($file['import_date'])) ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($file['imported']): ?>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle me-1"></i>
                                                    Importado
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">
                                                    <i class="fas fa-clock me-1"></i>
                                                    Pendiente
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($file['size_formatted']) ?></td>
                                        <td>
                                            <span class="badge bg-info"><?= number_format($file['lines']) ?></span>
                                        </td>
                                        <td>
                                            <small><?= date('d/m/Y H:i', $file['modified']) ?></small>
                                        </td>
                                        <td class="file-actions">
                                            <div class="action-buttons">
                                                <button class="btn btn-outline-primary btn-sm" 
                                                        onclick="previewFile('<?= esc($file['name']) ?>')"
                                                        title="Vista previa del archivo">
                                                    <i class="fas fa-eye me-1"></i>
                                                    Preview
                                                </button>
                                                <?php if (!$file['imported']): ?>
                                                    <button class="btn btn-success btn-sm" 
                                                            onclick="importFile('<?= esc($file['name']) ?>')"
                                                            title="Importar archivo a la base de datos">
                                                        <i class="fas fa-play me-1"></i>
                                                        Importar
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-warning btn-sm" 
                                                            onclick="reimportFile('<?= esc($file['name']) ?>')"
                                                            title="Re-importar archivo (sobrescribir datos)">
                                                        <i class="fas fa-redo me-1"></i>
                                                        Re-importar
                                                    </button>
                                                <?php endif; ?>
                                                <button class="btn btn-outline-danger btn-sm" 
                                                        onclick="deleteFile('<?= esc($file['name']) ?>')"
                                                        title="Eliminar archivo del servidor">
                                                    <i class="fas fa-trash me-1"></i>
                                                    Eliminar
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-file-code fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No hay archivos SQL disponibles</h5>
                        <p class="text-muted">Sube un archivo SQL para comenzar</p>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            <i class="fas fa-upload me-1"></i>
                            Subir primer archivo
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Subida de Archivos -->
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
                <form id="uploadForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="sql_file" class="form-label">Seleccionar archivo SQL</label>
                        <input type="file" class="form-control" id="sql_file" name="sql_file" accept=".sql" required>
                        <div class="form-text">Solo se permiten archivos .sql</div>
                    </div>
                    <div class="mb-3">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Tipos de archivos soportados:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Scripts de creación de tablas</li>
                                <li>Archivos de datos (INSERT statements)</li>
                                <li>Scripts de migración</li>
                            </ul>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="uploadButton">
                    <i class="fas fa-upload me-1"></i>
                    Subir Archivo
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Preview -->
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
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
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

<!-- Modal de Información de Tabla -->
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
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Cargando información...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Resultado de Importación -->
<div class="modal fade" id="resultModal" tabindex="-1">
    <div class="modal-dialog">
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
                    <i class="fas fa-sync-alt me-1"></i>
                    Actualizar Página
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.table-status-card {
    border: 1px solid #dee2e6;
    transition: all 0.2s;
}

.table-status-card:hover {
    border-color: #007bff;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 123, 255, 0.075);
}

.status-badge {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    color: white;
    font-size: 1.2em;
}

.file-preview {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 1rem;
    font-family: 'Courier New', monospace;
    font-size: 0.85em;
    max-height: 400px;
    overflow-y: auto;
}

.progress-container {
    margin: 1rem 0;
}

.btn-group-vertical .btn {
    border-radius: 0.375rem !important;
    margin-bottom: 2px;
}

.btn-group-vertical .btn:not(:last-child) {
    margin-bottom: 4px;
}

.table-success {
    background-color: rgba(25, 135, 84, 0.1) !important;
}

.import-status {
    font-size: 0.75rem;
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

<script>
console.log('SQL Import script loaded');
let currentPreviewFile = null;

// Esperar a que el DOM esté cargado
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, setting up event listeners');
    
    // Event listener para el botón de upload
    const uploadButton = document.getElementById('uploadButton');
    if (uploadButton) {
        uploadButton.addEventListener('click', function() {
            console.log('Upload button clicked');
            uploadFile();
        });
    } else {
        console.error('Upload button not found');
    }
});

// Función de upload simplificada
function uploadFile() {
    console.log('uploadFile function called');
    
    const fileInput = document.getElementById('sql_file');
    if (!fileInput) {
        console.error('File input not found');
        alert('Error: No se encontró el selector de archivos');
        return;
    }
    
    console.log('File input found:', fileInput);
    console.log('Files selected:', fileInput.files.length);
    
    if (!fileInput.files[0]) {
        alert('Por favor selecciona un archivo');
        return;
    }
    
    const selectedFile = fileInput.files[0];
    console.log('Selected file:', selectedFile.name, 'Size:', selectedFile.size);
    
    if (!selectedFile.name.endsWith('.sql')) {
        alert('Solo se permiten archivos .sql');
        return;
    }
    
    // Crear FormData
    const formData = new FormData();
    formData.append('sql_file', selectedFile);
    
    console.log('Sending upload request to /sql-import/upload');
    
    // Realizar upload
    fetch('/sql-import/upload', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response received, status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        
        if (data.success) {
            alert('✅ Archivo subido exitosamente: ' + data.filename);
            // Cerrar modal
            const modal = document.getElementById('uploadModal');
            const bootstrapModal = bootstrap.Modal.getInstance(modal);
            if (bootstrapModal) {
                bootstrapModal.hide();
            }
            // Recargar página después de un momento
            setTimeout(() => location.reload(), 1000);
        } else {
            alert('❌ Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        alert('❌ Error al subir archivo: ' + error.message);
    });
}

// Preview de archivo
function previewFile(filename) {
    console.log('previewFile called with:', filename);
    currentPreviewFile = filename;
    
    // Abrir modal usando Bootstrap 5
    const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
    previewModal.show();
    
    fetch('/sql-import/import', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `filename=${encodeURIComponent(filename)}&action=preview`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const preview = data.preview;
            document.getElementById('previewContent').innerHTML = `
                <div class="mb-3">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Archivo:</strong><br>
                            <code>${filename}</code>
                        </div>
                        <div class="col-md-3">
                            <strong>Líneas:</strong><br>
                            ${preview.total_lines.toLocaleString()}
                        </div>
                        <div class="col-md-3">
                            <strong>Statements:</strong><br>
                            ${preview.statements.toLocaleString()}
                        </div>
                        <div class="col-md-3">
                            <strong>Tamaño:</strong><br>
                            ${formatBytes(preview.size)}
                        </div>
                    </div>
                </div>
                <div class="file-preview">
                    ${preview.preview_lines.map(line => `<div>${escapeHtml(line)}</div>`).join('')}
                    ${preview.total_lines > 20 ? '<div class="text-muted mt-2">... y ' + (preview.total_lines - 20) + ' líneas más</div>' : ''}
                </div>
            `;
        } else {
            document.getElementById('previewContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error al cargar preview: ${data.error}
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('previewContent').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Error al cargar preview
            </div>
        `;
    });
}

// Importar archivo
function importFile(filename) {
    if (!confirm(`¿Estás seguro de que quieres importar ${filename}?`)) {
        return;
    }
    
    showProgress('Importando archivo...');
    
    fetch('/sql-import/import', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `filename=${encodeURIComponent(filename)}&action=import`
    })
    .then(response => response.json())
    .then(data => {
        hideProgress();
        showImportResult(data);
    })
    .catch(error => {
        hideProgress();
        console.error('Error:', error);
        showError('Error durante la importación');
    });
}

// Re-importar archivo
function reimportFile(filename) {
    if (!confirm(`¿Estás seguro de que quieres RE-IMPORTAR ${filename}?\n\nEsto puede sobrescribir datos existentes.`)) {
        return;
    }
    
    showProgress('Re-importando archivo...');
    
    fetch('/sql-import/import', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `filename=${encodeURIComponent(filename)}&action=import&force=true`
    })
    .then(response => response.json())
    .then(data => {
        hideProgress();
        showImportResult(data);
    })
    .catch(error => {
        hideProgress();
        console.error('Error:', error);
        showError('Error durante la re-importación');
    });
}

// Eliminar archivo
function deleteFile(filename) {
    if (!confirm(`¿Estás seguro de que quieres eliminar ${filename}?`)) {
        return;
    }
    
    fetch('/sql-import/delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `filename=${encodeURIComponent(filename)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Archivo eliminado');
            setTimeout(() => location.reload(), 1000);
        } else {
            showError(data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error al eliminar archivo');
    });
}

// Mostrar información de tabla
function showTableInfo(tableName) {
    console.log('showTableInfo called with:', tableName);
    $('#tableInfoModal').modal('show');
    
    fetch(`/sql-import/table-info?table=${encodeURIComponent(tableName)}`)
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            const info = data.info;
            let content = `
                <h6>Tabla: ${info.name}</h6>
                <p><strong>Registros:</strong> ${info.records.toLocaleString()}</p>
            `;
            
            if (info.sample && info.sample.length > 0) {
                content += `
                    <h6>Muestra de datos:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    ${info.columns.map(col => `<th>${col}</th>`).join('')}
                                </tr>
                            </thead>
                            <tbody>
                                ${info.sample.map(row => `
                                    <tr>
                                        ${info.columns.map(col => `<td>${row[col] || ''}</td>`).join('')}
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            }
            
            document.getElementById('tableInfoContent').innerHTML = content;
        } else {
            document.getElementById('tableInfoContent').innerHTML = `
                <div class="alert alert-danger">
                    Error al cargar información de la tabla: ${data.error || 'Error desconocido'}
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        document.getElementById('tableInfoContent').innerHTML = `
            <div class="alert alert-danger">
                Error al cargar información de la tabla: ${error.message}
            </div>
        `;
    });
}
}

// Mostrar resultado de importación
function showImportResult(data) {
    let content = '';
    
    if (data.success) {
        content = `
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                <strong>${data.message}</strong>
            </div>
            <div class="mt-3">
                <h6>Detalles:</h6>
                <ul>
                    <li><strong>Statements exitosos:</strong> ${data.details.successful}</li>
                    <li><strong>Statements fallidos:</strong> ${data.details.failed}</li>
                </ul>
            </div>
        `;
        
        if (data.details.errors && data.details.errors.length > 0) {
            content += `
                <div class="mt-3">
                    <h6>Errores:</h6>
                    <div class="alert alert-warning">
                        ${data.details.errors.map(error => `<div><small>${error}</small></div>`).join('')}
                    </div>
                </div>
            `;
        }
    } else {
        content = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Error en la importación:</strong> ${data.error}
            </div>
        `;
        
        if (data.details) {
            content += `
                <div class="mt-3">
                    <h6>Detalles:</h6>
                    <ul>
                        <li><strong>Statements exitosos:</strong> ${data.details.successful}</li>
                        <li><strong>Statements fallidos:</strong> ${data.details.failed}</li>
                    </ul>
                </div>
            `;
        }
    }
    
    document.getElementById('resultContent').innerHTML = content;
    $('#resultModal').modal('show');
}

// Funciones de utilidad
function showSuccess(message) {
    // Implementar notificación de éxito
    alert('✅ ' + message);
}

function showError(message) {
    // Implementar notificación de error
    alert('❌ ' + message);
}

function showProgress(message) {
    // Implementar barra de progreso
    console.log('Progress:', message);
}

function hideProgress() {
    // Ocultar barra de progreso
    console.log('Progress hidden');
}

function formatBytes(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

// Event listener para importar desde preview
document.getElementById('importFromPreview').addEventListener('click', function() {
    if (currentPreviewFile) {
        $('#previewModal').modal('hide');
        importFile(currentPreviewFile);
    }
});
</script>

<?= $this->endSection() ?>