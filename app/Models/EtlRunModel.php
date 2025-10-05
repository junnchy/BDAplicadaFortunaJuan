<?php

namespace App\Models;

use CodeIgniter\Model;

class EtlRunModel extends Model
{
    protected $table            = 'etl_runs';
    protected $primaryKey       = 'run_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'command',
        'status',
        'started_at',
        'ended_at',
        'parameters',
        'total_records',
        'processed_records',
        'failed_records',
        'execution_time_ms',
        'memory_usage_mb'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'run_id'            => 'integer',
        'total_records'     => 'integer',
        'processed_records' => 'integer',
        'failed_records'    => 'integer',
        'execution_time_ms' => '?integer',
        'memory_usage_mb'   => '?float',
    ];
    
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'command' => 'required|string|max_length[100]',
        'status'  => 'required|in_list[running,success,failed,cancelled]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['setDefaultValues'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    protected function setDefaultValues(array $data): array
    {
        if (!isset($data['data']['started_at'])) {
            $data['data']['started_at'] = date('Y-m-d H:i:s');
        }
        
        if (!isset($data['data']['status'])) {
            $data['data']['status'] = 'running';
        }
        
        return $data;
    }

    /**
     * Inicia una nueva ejecución ETL
     */
    public function startRun(string $command, array $parameters = []): int
    {
        $data = [
            'command'    => $command,
            'status'     => 'running',
            'started_at' => date('Y-m-d H:i:s'),
            'parameters' => json_encode($parameters),
        ];

        return $this->insert($data);
    }

    /**
     * Completa una ejecución ETL exitosamente
     */
    public function completeRun(int $runId, array $stats = []): bool
    {
        $data = [
            'status'             => 'success',
            'ended_at'           => date('Y-m-d H:i:s'),
            'total_records'      => $stats['total_records'] ?? 0,
            'processed_records'  => $stats['processed_records'] ?? 0,
            'failed_records'     => $stats['failed_records'] ?? 0,
            'execution_time_ms'  => $stats['execution_time_ms'] ?? 0,
            'memory_usage_mb'    => $stats['memory_usage_mb'] ?? 0,
        ];

        return $this->update($runId, $data);
    }

    /**
     * Marca una ejecución ETL como fallida
     */
    public function failRun(int $runId, string $errorMessage = ''): bool
    {
        $data = [
            'status'    => 'failed',
            'ended_at'  => date('Y-m-d H:i:s'),
        ];

        return $this->update($runId, $data);
    }

    /**
     * Verifica si hay una ejecución en curso del comando especificado
     */
    public function hasRunningCommand(string $command): bool
    {
        $result = $this->where('command', $command)
                      ->where('status', 'running')
                      ->first();
        
        return $result !== null;
    }

    /**
     * Obtiene estadísticas de ejecuciones
     */
    public function getExecutionStats(int $days = 30): array
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $stats = $this->select('
                COUNT(*) as total_runs,
                SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as successful_runs,
                SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_runs,
                AVG(execution_time_ms) as avg_execution_time,
                SUM(total_records) as total_records_processed
            ')
            ->where('started_at >=', $cutoffDate)
            ->first();
            
        return $stats ?: [
            'total_runs' => 0,
            'successful_runs' => 0,
            'failed_runs' => 0,
            'avg_execution_time' => 0,
            'total_records_processed' => 0
        ];
    }

    /**
     * Obtiene el historial de ejecuciones recientes
     */
    public function getRecentRuns(int $limit = 10): array
    {
        return $this->orderBy('started_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Actualiza estadísticas de una ejecución en curso
     */
    public function updateRunStats(int $runId, array $stats): bool
    {
        $updateData = [];
        
        if (isset($stats['total_records'])) {
            $updateData['total_records'] = $stats['total_records'];
        }
        
        if (isset($stats['processed_records'])) {
            $updateData['processed_records'] = $stats['processed_records'];
        }
        
        if (isset($stats['failed_records'])) {
            $updateData['failed_records'] = $stats['failed_records'];
        }
        
        if (empty($updateData)) {
            return true;
        }
        
        return $this->update($runId, $updateData);
    }

    /**
     * Cancela una ejecución en curso
     */
    public function cancelRun(int $runId): bool
    {
        return $this->update($runId, [
            'status' => 'cancelled',
            'ended_at' => date('Y-m-d H:i:s')
        ]);
    }
}