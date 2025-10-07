console.log('SQL Import JS loaded');

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
        console.log('Upload button event listener added');
    } else {
        console.error('Upload button not found');
    }
});

// Función de upload
function uploadFile() {
    console.log('uploadFile function called');
    
    const fileInput = document.getElementById('sql_file');
    if (!fileInput) {
        console.error('File input not found');
        alert('Error: No se encontró el selector de archivos');
        return;
    }
    
    console.log('File input found, files selected:', fileInput.files.length);
    
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
            throw new Error('HTTP ' + response.status + ': ' + response.statusText);
        }
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        
        if (data.success) {
            alert('✅ Archivo subido exitosamente: ' + data.filename);
            // Cerrar modal
            const modal = document.getElementById('uploadModal');
            if (modal && window.bootstrap) {
                const bootstrapModal = bootstrap.Modal.getInstance(modal);
                if (bootstrapModal) {
                    bootstrapModal.hide();
                }
            }
            // Recargar página después de un momento
            setTimeout(function() {
                location.reload();
            }, 1000);
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
    
    // Abrir modal
    const previewModal = document.getElementById('previewModal');
    if (previewModal && window.bootstrap) {
        const modal = new bootstrap.Modal(previewModal);
        modal.show();
    }
    
    fetch('/sql-import/import', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'filename=' + encodeURIComponent(filename) + '&action=preview'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const preview = data.preview;
            const content = '<div class="mb-3">' +
                '<div class="row">' +
                '<div class="col-md-3"><strong>Archivo:</strong><br><code>' + filename + '</code></div>' +
                '<div class="col-md-3"><strong>Líneas:</strong><br>' + preview.total_lines.toLocaleString() + '</div>' +
                '<div class="col-md-3"><strong>Statements:</strong><br>' + preview.statements.toLocaleString() + '</div>' +
                '<div class="col-md-3"><strong>Tamaño:</strong><br>' + formatBytes(preview.size) + '</div>' +
                '</div></div>' +
                '<div class="file-preview">' +
                preview.preview_lines.map(function(line) { return '<div>' + escapeHtml(line) + '</div>'; }).join('') +
                (preview.total_lines > 20 ? '<div class="text-muted mt-2">... y ' + (preview.total_lines - 20) + ' líneas más</div>' : '') +
                '</div>';
            
            document.getElementById('previewContent').innerHTML = content;
        } else {
            document.getElementById('previewContent').innerHTML = 
                '<div class="alert alert-danger">' +
                '<i class="fas fa-exclamation-triangle me-2"></i>' +
                'Error al cargar preview: ' + (data.error || 'Error desconocido') +
                '</div>';
        }
    })
    .catch(error => {
        console.error('Preview error:', error);
        document.getElementById('previewContent').innerHTML = 
            '<div class="alert alert-danger">' +
            '<i class="fas fa-exclamation-triangle me-2"></i>' +
            'Error al cargar preview: ' + error.message +
            '</div>';
    });
}

// Importar archivo
function importFile(filename) {
    if (!confirm('¿Estás seguro de que quieres importar ' + filename + '?')) {
        return;
    }
    
    console.log('Importing file:', filename);
    
    fetch('/sql-import/import', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'filename=' + encodeURIComponent(filename) + '&action=import'
    })
    .then(response => response.json())
    .then(data => {
        showImportResult(data);
    })
    .catch(error => {
        console.error('Import error:', error);
        alert('Error durante la importación: ' + error.message);
    });
}

// Re-importar archivo
function reimportFile(filename) {
    if (!confirm('¿Estás seguro de que quieres RE-IMPORTAR ' + filename + '?\n\nEsto puede sobrescribir datos existentes.')) {
        return;
    }
    
    console.log('Re-importing file:', filename);
    
    fetch('/sql-import/import', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'filename=' + encodeURIComponent(filename) + '&action=import&force=true'
    })
    .then(response => response.json())
    .then(data => {
        showImportResult(data);
    })
    .catch(error => {
        console.error('Re-import error:', error);
        alert('Error durante la re-importación: ' + error.message);
    });
}

// Eliminar archivo
function deleteFile(filename) {
    if (!confirm('¿Estás seguro de que quieres eliminar ' + filename + '?')) {
        return;
    }
    
    fetch('/sql-import/delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'filename=' + encodeURIComponent(filename)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Archivo eliminado');
            setTimeout(function() {
                location.reload();
            }, 1000);
        } else {
            alert('❌ Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        alert('Error al eliminar archivo: ' + error.message);
    });
}

// Mostrar información de tabla
function showTableInfo(tableName) {
    console.log('showTableInfo called with:', tableName);
    
    const tableModal = document.getElementById('tableInfoModal');
    if (tableModal && window.bootstrap) {
        const modal = new bootstrap.Modal(tableModal);
        modal.show();
    }
    
    fetch('/sql-import/table-info?table=' + encodeURIComponent(tableName))
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const info = data.info;
            let content = '<h6>Tabla: ' + info.name + '</h6>' +
                         '<p><strong>Registros:</strong> ' + info.records.toLocaleString() + '</p>';
            
            if (info.sample && info.sample.length > 0) {
                content += '<h6>Muestra de datos:</h6>' +
                          '<div class="table-responsive">' +
                          '<table class="table table-sm">' +
                          '<thead><tr>' +
                          info.columns.map(function(col) { return '<th>' + col + '</th>'; }).join('') +
                          '</tr></thead>' +
                          '<tbody>' +
                          info.sample.map(function(row) {
                              return '<tr>' + 
                                     info.columns.map(function(col) { return '<td>' + (row[col] || '') + '</td>'; }).join('') +
                                     '</tr>';
                          }).join('') +
                          '</tbody></table></div>';
            }
            
            document.getElementById('tableInfoContent').innerHTML = content;
        } else {
            document.getElementById('tableInfoContent').innerHTML = 
                '<div class="alert alert-danger">Error al cargar información de la tabla: ' + (data.error || 'Error desconocido') + '</div>';
        }
    })
    .catch(error => {
        console.error('Table info error:', error);
        document.getElementById('tableInfoContent').innerHTML = 
            '<div class="alert alert-danger">Error al cargar información de la tabla: ' + error.message + '</div>';
    });
}

// Mostrar resultado de importación
function showImportResult(data) {
    let content = '';
    
    if (data.success) {
        content = '<div class="alert alert-success">' +
                 '<i class="fas fa-check-circle me-2"></i>' +
                 '<strong>' + data.message + '</strong>' +
                 '</div>';
        
        if (data.details) {
            content += '<div class="mt-3"><h6>Detalles:</h6><ul>' +
                      '<li><strong>Statements exitosos:</strong> ' + data.details.successful + '</li>' +
                      '<li><strong>Statements fallidos:</strong> ' + data.details.failed + '</li>' +
                      '</ul></div>';
            
            if (data.details.errors && data.details.errors.length > 0) {
                content += '<div class="mt-3"><h6>Errores:</h6>' +
                          '<div class="alert alert-warning">' +
                          data.details.errors.map(function(error) { return '<div><small>' + error + '</small></div>'; }).join('') +
                          '</div></div>';
            }
        }
    } else {
        content = '<div class="alert alert-danger">' +
                 '<i class="fas fa-exclamation-triangle me-2"></i>' +
                 '<strong>Error en la importación:</strong> ' + data.error +
                 '</div>';
        
        if (data.details) {
            content += '<div class="mt-3"><h6>Detalles:</h6><ul>' +
                      '<li><strong>Statements exitosos:</strong> ' + data.details.successful + '</li>' +
                      '<li><strong>Statements fallidos:</strong> ' + data.details.failed + '</li>' +
                      '</ul></div>';
        }
    }
    
    document.getElementById('resultContent').innerHTML = content;
    
    const resultModal = document.getElementById('resultModal');
    if (resultModal && window.bootstrap) {
        const modal = new bootstrap.Modal(resultModal);
        modal.show();
    }
}

// Funciones de utilidad
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
document.addEventListener('DOMContentLoaded', function() {
    const importFromPreviewBtn = document.getElementById('importFromPreview');
    if (importFromPreviewBtn) {
        importFromPreviewBtn.addEventListener('click', function() {
            if (currentPreviewFile) {
                const previewModal = document.getElementById('previewModal');
                if (previewModal && window.bootstrap) {
                    const modal = bootstrap.Modal.getInstance(previewModal);
                    if (modal) {
                        modal.hide();
                    }
                }
                importFile(currentPreviewFile);
            }
        });
    }
});