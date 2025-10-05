<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> - ETL Data Warehouse</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --success-color: #198754;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #0dcaf0;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .status-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .status-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            max-width: 800px;
            width: 100%;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-healthy {
            background: var(--success-color);
            color: white;
        }

        .status-warning {
            background: var(--warning-color);
            color: #000;
        }

        .status-unhealthy, .status-error {
            background: var(--danger-color);
            color: white;
        }

        .check-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 0.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }

        .check-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .check-ok {
            border-left: 4px solid var(--success-color);
        }

        .check-warning {
            border-left: 4px solid var(--warning-color);
        }

        .check-error {
            border-left: 4px solid var(--danger-color);
        }

        .metric-value {
            font-weight: bold;
            font-size: 1.1rem;
        }

        .timestamp {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .refresh-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            border-radius: 50px;
            padding: 12px 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .system-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        .json-toggle {
            position: absolute;
            top: 15px;
            right: 15px;
        }
    </style>
</head>
<body>
    <div class="status-container">
        <div class="status-card position-relative">
            <!-- Botón para ver JSON -->
            <a href="<?= current_url() ?>?format=json" class="btn btn-outline-secondary btn-sm json-toggle">
                <i class="fas fa-code"></i> Ver JSON
            </a>

            <!-- Header del sistema -->
            <div class="system-info text-center">
                <h1 class="mb-2">
                    <i class="fas fa-heartbeat me-2"></i>
                    Estado del Sistema ETL
                </h1>
                <p class="mb-0">Data Warehouse & Analytics Platform</p>
            </div>

            <!-- Estado principal -->
            <div class="text-center mb-4">
                <?php 
                $statusClass = 'status-' . $status['status'];
                $statusIcon = match($status['status']) {
                    'healthy' => 'fas fa-check-circle',
                    'warning' => 'fas fa-exclamation-triangle',
                    'unhealthy' => 'fas fa-times-circle',
                    'error' => 'fas fa-skull-crossbones',
                    default => 'fas fa-question-circle'
                };
                ?>
                <div class="status-badge <?= $statusClass ?> <?= $status['status'] === 'healthy' ? 'pulse' : '' ?>">
                    <i class="<?= $statusIcon ?>"></i>
                    <?= ucfirst($status['status']) ?>
                </div>
                
                <div class="timestamp mt-2">
                    <i class="fas fa-clock me-1"></i>
                    Última verificación: <?= $status['timestamp'] ?>
                </div>
            </div>

            <!-- Información de versión -->
            <?php if (isset($status['version'])): ?>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="check-item check-ok">
                            <span><i class="fas fa-code-branch me-2"></i>Versión</span>
                            <span class="metric-value"><?= esc($status['version']) ?></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="check-item check-ok">
                            <span><i class="fas fa-server me-2"></i>Ambiente</span>
                            <span class="metric-value"><?= strtoupper(ENVIRONMENT) ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Verificaciones del sistema -->
            <?php if (isset($status['checks'])): ?>
                <h5 class="mb-3">
                    <i class="fas fa-clipboard-check me-2"></i>
                    Verificaciones del Sistema
                </h5>
                
                <div class="row">
                    <?php foreach ($status['checks'] as $checkName => $checkValue): ?>
                        <?php 
                        $isError = strpos($checkValue, 'error:') === 0;
                        $isWarning = strpos($checkValue, 'warning:') === 0;
                        $checkClass = $isError ? 'check-error' : ($isWarning ? 'check-warning' : 'check-ok');
                        
                        // Iconos específicos para cada tipo de verificación
                        $checkIcon = match(true) {
                            str_contains($checkName, 'database') => 'fas fa-database',
                            str_contains($checkName, 'cache') => 'fas fa-memory',
                            str_contains($checkName, 'disk') => 'fas fa-hdd',
                            str_contains($checkName, 'memory') => 'fas fa-microchip',
                            str_contains($checkName, 'table_') => 'fas fa-table',
                            default => 'fas fa-cog'
                        };
                        
                        // Formatear nombre legible
                        $displayName = match($checkName) {
                            'database' => 'Base de Datos',
                            'cache' => 'Sistema de Cache',
                            'disk_usage' => 'Uso de Disco',
                            'memory_usage' => 'Uso de Memoria',
                            'memory_limit' => 'Límite de Memoria',
                            default => str_replace(['table_', '_'], ['Tabla ', ' '], ucfirst($checkName))
                        };
                        ?>
                        
                        <div class="col-md-6">
                            <div class="check-item <?= $checkClass ?>">
                                <span>
                                    <i class="<?= $checkIcon ?> me-2"></i>
                                    <?= esc($displayName) ?>
                                </span>
                                <span class="metric-value">
                                    <?php if ($isError): ?>
                                        <i class="fas fa-times text-danger"></i>
                                        <?= esc(substr($checkValue, 7)) ?>
                                    <?php elseif ($isWarning): ?>
                                        <i class="fas fa-exclamation-triangle text-warning"></i>
                                        <?= esc(substr($checkValue, 9)) ?>
                                    <?php else: ?>
                                        <i class="fas fa-check text-success me-1"></i>
                                        <?= esc($checkValue) ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Mensaje de error -->
            <?php if (isset($status['message'])): ?>
                <div class="alert alert-danger mt-4">
                    <h5><i class="fas fa-exclamation-triangle me-2"></i>Error del Sistema</h5>
                    <p class="mb-0"><?= esc($status['message']) ?></p>
                </div>
            <?php endif; ?>

            <!-- Acciones rápidas -->
            <div class="text-center mt-4">
                <a href="<?= base_url() ?>dashboard" class="btn btn-primary me-2">
                    <i class="fas fa-tachometer-alt me-1"></i>
                    Ir al Dashboard
                </a>
                <a href="<?= base_url() ?>dashboard/admin/database" class="btn btn-outline-primary">
                    <i class="fas fa-database me-1"></i>
                    Admin Base de Datos
                </a>
            </div>
        </div>
    </div>

    <!-- Botón de actualizar -->
    <button class="btn btn-success refresh-btn" onclick="location.reload()">
        <i class="fas fa-sync-alt me-1"></i>
        Actualizar
    </button>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Auto-refresh cada 30 segundos -->
    <script>
        // Auto-refresh cada 30 segundos
        setTimeout(function() {
            location.reload();
        }, 30000);

        // Mostrar último tiempo de actualización
        const timestamp = new Date();
        console.log('Health check actualizado:', timestamp.toLocaleString());
    </script>
</body>
</html>