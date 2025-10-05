<?php

namespace App\Models;

use CodeIgniter\Model;

class EtlRunStepModel extends Model
{
    protected $table            = 'etl_run_steps';
    protected $primaryKey       = 'step_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'run_id',
        'step_name',
        'status',
        'rows_affected',
        'error_message',
        'started_at',
        'ended_at',
        'execution_time_ms',
        'additional_info'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'step_id'           => 'integer',
        'run_id'            => 'integer', 
        'rows_affected'     => 'integer',
        'execution_time_ms' => '?integer',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'run_id'    => 'required|integer',
        'step_name' => 'required|max_length[100]',
        'status'    => 'required|in_list[running,success,failed,skipped]',
    ];

    /**
     * Inicia un nuevo paso ETL
     */
    public function startStep(int $runId, string $stepName): int
    {
        $data = [
            'run_id'     => $runId,
            'step_name'  => $stepName,
            'status'     => 'running',
            'started_at' => date('Y-m-d H:i:s'),
        ];

        return $this->insert($data);
    }

    /**
     * Completa un paso ETL exitosamente
     */
    public function completeStep(int $stepId, array $stats = []): bool
    {
        $data = [
            'status'             => 'success',
            'ended_at'           => date('Y-m-d H:i:s'),
            'rows_affected'      => $stats['rows_affected'] ?? 0,
            'execution_time_ms'  => $stats['execution_time_ms'] ?? 0,
        ];

        if (isset($stats['additional_info']) && $stats['additional_info'] !== null) {
            $data['additional_info'] = $stats['additional_info'];
        }

        return $this->update($stepId, $data);
    }

    /**
     * Marca un paso ETL como fallido
     */
    public function failStep(int $stepId, string $errorMessage = ''): bool
    {
        $data = [
            'status'        => 'failed',
            'ended_at'      => date('Y-m-d H:i:s'),
            'error_message' => $errorMessage,
        ];

        return $this->update($stepId, $data);
    }

    /**
     * Obtiene pasos de una ejecución específica
     */
    public function getStepsByRun(int $runId): array
    {
        return $this->where('run_id', $runId)
            ->orderBy('started_at', 'ASC')
            ->findAll();
    }

    /**
     * Obtiene estadísticas de pasos por comando
     */
    public function getStepStats(string $command = null, int $days = 7): array
    {
        $startDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        $query = $this->db->table($this->table . ' ers')
            ->join('etl_runs er', 'ers.run_id = er.run_id')
            ->select('
                ers.step_name,
                ers.status,
                COUNT(*) as total_executions,
                AVG(ers.execution_time_ms) as avg_execution_time,
                AVG(ers.rows_affected) as avg_rows_affected,
                SUM(ers.rows_affected) as total_rows_affected
            ')
            ->where('ers.started_at >=', $startDate);

        if ($command) {
            $query->where('er.command', $command);
        }

        return $query->groupBy('ers.step_name, ers.status')
            ->orderBy('ers.step_name, ers.status')
            ->get()
            ->getResultArray();
    }
}