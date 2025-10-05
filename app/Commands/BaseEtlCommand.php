<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\Commands;
use CodeIgniter\Database\Config as Database;
use Psr\Log\LoggerInterface;
use App\Models\EtlConfigModel;
use App\Models\EtlRunModel;
use App\Models\EtlRunStepModel;
use App\Models\EtlErrorModel;

abstract class BaseEtlCommand extends BaseCommand
{
    protected string $commandSignature = '';
    
    protected EtlConfigModel $etlConfigModel;
    protected EtlRunModel $etlRunModel;
    protected EtlRunStepModel $etlStepModel;
    protected EtlErrorModel $etlErrorModel;
    
    protected $db;
    protected int $runId;
    protected array $config;
    protected int $startTime;
    protected int $batchSize = 1000;
    protected int $maxRetries = 3;
    
    /**
     * Constructor
     */
    public function __construct(LoggerInterface $logger, Commands $commands)
    {
        parent::__construct($logger, $commands);
        
        // Inicializar conexión de base de datos
        $this->db = Database::connect();
        
        // Inicializar modelos
        $this->etlRunModel = new EtlRunModel();
        $this->etlStepModel = new EtlRunStepModel();
        $this->etlErrorModel = new EtlErrorModel();
        
        // Solo cargar configuración si las tablas existen (no durante migraciones)
        if ($this->tablesExist()) {
            $this->loadConfig();
        } else {
            $this->config = [];
        }
    }

    /**
     * Verifica si las tablas ETL existen
     */
    protected function tablesExist(): bool
    {
        try {
            $db = Database::connect();
            
            // Verificar si existe la tabla etl_config
            $query = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='etl_config'");
            $result = $query->getRow();
            
            return $result !== null;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Carga la configuración del ETL
     */
    protected function loadConfig(): void
    {
        $this->etlConfigModel = new EtlConfigModel();
        $configs = $this->etlConfigModel->getActiveConfigs();
        
        $this->config = [];
        foreach ($configs as $config) {
            $this->config[$config['config_key']] = $config['config_value'];
        }
    }

    /**
     * Convierte valor de configuración al tipo correcto
     */
    protected function castConfigValue(string $value, string $type): mixed
    {
        return match ($type) {
            'integer' => (int) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'decimal' => (float) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Inicia el logging de ejecución ETL
     */
    protected function startEtlRun(array $parameters = []): int
    {
        CLI::write("🚀 Iniciando comando: {$this->commandSignature}", 'green');
        
        $this->startTime = time(); // Inicializar tiempo de inicio
        $this->runId = $this->etlRunModel->startRun($this->commandSignature, $parameters);
        
        if (!$this->runId) {
            CLI::error('Error al iniciar logging de ETL');
            exit(1);
        }
        
        CLI::write("📝 Run ID: {$this->runId}");
        return $this->runId;
    }

    /**
     * Completa el logging de ejecución ETL
     */
    protected function completeEtlRun(array $stats = []): void
    {
        $executionTime = (time() - $this->startTime) * 1000; // en ms
        $memoryUsage = memory_get_peak_usage(true) / 1024 / 1024; // en MB
        
        $finalStats = array_merge($stats, [
            'execution_time_ms' => $executionTime,
            'memory_usage_mb' => $memoryUsage,
        ]);
        
        $this->etlRunModel->completeRun($this->runId, $finalStats);
        
        CLI::write("✅ Comando completado exitosamente", 'green');
        CLI::write("⏱️  Tiempo de ejecución: " . ($executionTime / 1000) . " segundos");
        CLI::write("💾 Memoria utilizada: " . round($memoryUsage, 2) . " MB");
        
        if (isset($stats['total_records'])) {
            CLI::write("📊 Registros procesados: {$stats['total_records']}");
        }
    }

    /**
     * Marca la ejecución como fallida
     */
    protected function failEtlRun(string $error): void
    {
        $this->etlRunModel->failRun($this->runId, $error);
        CLI::error("❌ Comando falló: {$error}");
        exit(1);
    }

    /**
     * Ejecuta un paso ETL con logging
     */
    protected function executeStep(string $stepName, callable $stepFunction): mixed
    {
        CLI::write("🔄 Ejecutando paso: {$stepName}", 'yellow');
        
        $stepId = $this->etlStepModel->startStep($this->runId, $stepName);
        CLI::write("📝 Step ID creado: {$stepId}", 'cyan');
        
        $stepStartTime = microtime(true);
        
        try {
            $result = $stepFunction();
            
            $executionTime = (microtime(true) - $stepStartTime) * 1000;
            $rowsAffected = is_array($result) && isset($result['rows_affected']) 
                ? $result['rows_affected'] 
                : 0;
            
            $this->etlStepModel->completeStep($stepId, [
                'execution_time_ms' => $executionTime,
                'rows_affected' => $rowsAffected,
            ]);
            
            CLI::write("✅ Paso completado: {$stepName} ({$rowsAffected} registros)", 'green');
            
            return $result;
            
        } catch (\Exception $e) {
            CLI::write("❌ Error en paso {$stepName}: " . $e->getMessage(), 'red');
            $this->etlStepModel->failStep($stepId, $e->getMessage());
            $this->logError('STEP_EXECUTION', $e->getMessage(), [
                'step_name' => $stepName,
                'step_id' => $stepId,
            ]);
            
            throw $e;
        }
    }

    /**
     * Log de errores
     */
    protected function logError(string $errorType, string $message, array $errorData = []): void
    {
        $this->etlErrorModel->logError([
            'run_id' => $this->runId,
            'error_type' => $errorType,
            'error_message' => $message,
            'error_data' => $errorData,
            'stack_trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),
        ]);
    }

    /**
     * Ejecuta operación con reintentos
     */
    protected function executeWithRetry(callable $operation, int $maxRetries = null): mixed
    {
        $maxRetries = $maxRetries ?? $this->maxRetries;
        $attempt = 1;
        
        while ($attempt <= $maxRetries) {
            try {
                return $operation();
                
            } catch (\Exception $e) {
                if ($attempt === $maxRetries) {
                    throw $e;
                }
                
                CLI::write("⚠️  Intento {$attempt} falló, reintentando... ({$e->getMessage()})", 'yellow');
                $attempt++;
                sleep(min($attempt * 2, 10)); // Backoff exponencial con máximo de 10s
            }
        }
        
        // Esta línea nunca debería ejecutarse, pero evita el warning
        throw new \Exception('Todos los reintentos fallaron');
    }

    /**
     * Procesa datos en lotes
     */
    protected function processBatch(array $data, callable $processor): array
    {
        $totalRecords = count($data);
        $processedRecords = 0;
        $failedRecords = 0;
        
        $batches = array_chunk($data, $this->batchSize);
        $totalBatches = count($batches);
        
        foreach ($batches as $batchIndex => $batch) {
            $batchNumber = $batchIndex + 1;
            CLI::write("📦 Procesando lote {$batchNumber}/{$totalBatches} (" . count($batch) . " registros)");
            
            try {
                $result = $this->executeWithRetry(function() use ($processor, $batch) {
                    return $processor($batch);
                });
                
                $processedRecords += count($batch);
                
                // Mostrar progreso
                $progress = round(($processedRecords / $totalRecords) * 100, 1);
                CLI::write("✅ Lote completado. Progreso: {$progress}%");
                
            } catch (\Exception $e) {
                $failedRecords += count($batch);
                CLI::write("❌ Error en lote {$batchNumber}: {$e->getMessage()}", 'red');
                
                $this->logError('BATCH_PROCESSING', $e->getMessage(), [
                    'batch_number' => $batchNumber,
                    'batch_size' => count($batch),
                    'total_batches' => $totalBatches,
                ]);
            }
        }
        
        return [
            'total_records' => $totalRecords,
            'processed_records' => $processedRecords,
            'failed_records' => $failedRecords,
            'success_rate' => $totalRecords > 0 ? ($processedRecords / $totalRecords) * 100 : 0,
        ];
    }

    /**
     * Valida condiciones previas
     */
    protected function validatePreconditions(): void
    {
        // Verificar que no haya otra ejecución del mismo comando en curso
        if ($this->etlRunModel->hasRunningCommand($this->commandSignature)) {
            CLI::error("Ya hay una ejecución de '{$this->commandSignature}' en curso");
            exit(1);
        }
        
        // Verificar conexión a BD
        if (!$this->db->connID) {
            CLI::error("No se puede conectar a la base de datos");
            exit(1);
        }
    }

    /**
     * Valida calidad de datos
     */
    protected function validateDataQuality(string $tableName, array $rules = []): bool
    {
        if (!$this->config['etl.data_quality_checks'] ?? false) {
            return true;
        }
        
        CLI::write("🔍 Validando calidad de datos en {$tableName}");
        
        $issues = [];
        
        // Verificar registros duplicados si se especifica
        if (isset($rules['unique_fields'])) {
            $duplicates = $this->db->table($tableName)
                ->select(implode(', ', $rules['unique_fields']) . ', COUNT(*) as count')
                ->groupBy(implode(', ', $rules['unique_fields']))
                ->having('count >', 1)
                ->get()
                ->getResultArray();
                
            if (!empty($duplicates)) {
                $issues[] = "Registros duplicados encontrados: " . count($duplicates);
            }
        }
        
        // Verificar campos nulos si se especifica
        if (isset($rules['required_fields'])) {
            foreach ($rules['required_fields'] as $field) {
                $nullCount = $this->db->table($tableName)
                    ->where("{$field} IS NULL OR {$field} = ''")
                    ->countAllResults();
                    
                if ($nullCount > 0) {
                    $issues[] = "Campo requerido '{$field}' tiene {$nullCount} valores nulos";
                }
            }
        }
        
        if (!empty($issues)) {
            CLI::write("⚠️  Problemas de calidad encontrados:", 'yellow');
            foreach ($issues as $issue) {
                CLI::write("  • {$issue}", 'yellow');
            }
            
            $this->logError('DATA_QUALITY', 'Problemas de calidad de datos', [
                'table' => $tableName,
                'issues' => $issues,
            ]);
            
            return false;
        }
        
        CLI::write("✅ Validación de calidad completada");
        return true;
    }

    /**
     * Maneja errores del ETL
     */
    protected function handleEtlError(\Exception $e): void
    {
        $this->logError('ETL_EXECUTION', $e->getMessage(), [
            'command' => $this->commandSignature,
            'run_id' => $this->runId,
        ]);
        
        $this->etlRunModel->failRun($this->runId, $e->getMessage());
        
        CLI::error("❌ Comando falló: " . $e->getMessage());
        exit(1);
    }
}