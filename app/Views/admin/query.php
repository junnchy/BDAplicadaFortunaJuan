<?= $this->extend('layout/dashboard') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">💻 Ejecutor de Consultas SQL</h5>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-secondary" onclick="insertExample('SELECT')">SELECT</button>
                <button type="button" class="btn btn-sm btn-secondary" onclick="insertExample('COUNT')">COUNT</button>
                <button type="button" class="btn btn-sm btn-secondary" onclick="insertExample('JOIN')">JOIN</button>
                <button type="button" class="btn btn-sm btn-secondary" onclick="clearQuery()">Limpiar</button>
            </div>
        </div>
        <div class="card-body">
            <form action="<?= base_url() ?>dashboard/admin/database/query" method="POST">
                <div class="mb-3">
                    <label for="sql" class="form-label">Consulta SQL:</label>
                    <textarea class="form-control font-monospace" id="sql" name="sql" rows="8" 
                              placeholder="Escriba su consulta SQL aquí..."><?= isset($sql) ? esc($sql) : '' ?></textarea>
                    <div class="form-text">
                        <strong>Ejemplos:</strong>
                        <code>SELECT * FROM fact_ventas LIMIT 10;</code> |
                        <code>SELECT COUNT(*) FROM dim_producto;</code> |
                        <code>PRAGMA table_info(fact_ventas);</code>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-play"></i> Ejecutar Consulta
                    </button>
                    <?php if (isset($executionTime)): ?>
                        <small class="text-muted">
                            ⏱️ Tiempo de ejecución: <?= $executionTime ?>ms | 
                            📊 Registros: <?= isset($rowCount) ? $rowCount : 0 ?>
                        </small>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <?php if (isset($success)): ?>
        <div class="mt-4">
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <strong>✅ Consulta ejecutada exitosamente</strong>
                    <?php if (isset($rowCount) && $rowCount > 0): ?>
                        - Se obtuvieron <?= $rowCount ?> registro(s)
                    <?php endif; ?>
                </div>
                
                <?php if (isset($result) && !empty($result)): ?>
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">📋 Resultados</h6>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-secondary" onclick="exportResults('csv')">📄 CSV</button>
                                <button class="btn btn-outline-secondary" onclick="exportResults('json')">🔧 JSON</button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                <table class="table table-striped table-hover mb-0" id="results-table">
                                    <thead class="table-dark sticky-top">
                                        <tr>
                                            <?php if (!empty($result)): ?>
                                                <?php foreach (array_keys($result[0]) as $column): ?>
                                                    <th class="text-nowrap"><?= esc($column) ?></th>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($result as $row): ?>
                                            <tr>
                                                <?php foreach ($row as $value): ?>
                                                    <td class="text-nowrap">
                                                        <?php 
                                                        $displayValue = $value;
                                                        if (strlen($displayValue) > 50) {
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
                        </div>
                    </div>
                <?php elseif (isset($result)): ?>
                    <div class="alert alert-info">
                        <strong>ℹ️ Consulta ejecutada</strong> - No se obtuvieron resultados (operación exitosa)
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="alert alert-danger">
                    <strong>❌ Error en la consulta:</strong><br>
                    <code><?= esc($error) ?></code>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function insertExample(type) {
    const textarea = document.getElementById('sql');
    let example = '';
    
    switch(type) {
        case 'SELECT':
            example = 'SELECT * FROM fact_ventas LIMIT 10;';
            break;
        case 'COUNT':
            example = 'SELECT COUNT(*) as total FROM dim_producto;';
            break;
        case 'JOIN':
            example = `SELECT fv.producto_sk, dp.producto_nombre, SUM(fv.monto_linea) as total_ventas
FROM fact_ventas fv
LEFT JOIN dim_producto dp ON fv.producto_sk = dp.producto_sk
GROUP BY fv.producto_sk, dp.producto_nombre
ORDER BY total_ventas DESC
LIMIT 10;`;
            break;
    }
    
    textarea.value = example;
    textarea.focus();
}

function clearQuery() {
    document.getElementById('sql').value = '';
    document.getElementById('sql').focus();
}

function exportResults(format) {
    const table = document.getElementById('results-table');
    if (!table) return;
    
    if (format === 'csv') {
        exportTableToCSV(table);
    } else if (format === 'json') {
        exportTableToJSON(table);
    }
}

function exportTableToCSV(table) {
    const rows = table.querySelectorAll('tr');
    let csv = [];
    
    for (let i = 0; i < rows.length; i++) {
        const row = [], cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length; j++) {
            let cellData = cols[j].innerText.replace(/"/g, '""');
            row.push('"' + cellData + '"');
        }
        
        csv.push(row.join(','));
    }
    
    downloadFile(csv.join('\n'), 'query_results.csv', 'text/csv');
}

function exportTableToJSON(table) {
    const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.innerText);
    const rows = Array.from(table.querySelectorAll('tbody tr')).map(row => {
        const cells = Array.from(row.querySelectorAll('td')).map(td => td.innerText);
        const obj = {};
        headers.forEach((header, index) => {
            obj[header] = cells[index];
        });
        return obj;
    });
    
    downloadFile(JSON.stringify(rows, null, 2), 'query_results.json', 'application/json');
}

function downloadFile(content, fileName, contentType) {
    const a = document.createElement('a');
    const file = new Blob([content], {type: contentType});
    
    a.href = URL.createObjectURL(file);
    a.download = fileName;
    a.click();
    
    URL.revokeObjectURL(a.href);
}
</script>
<?= $this->endSection() ?>