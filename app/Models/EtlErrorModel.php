<?php

namespace App\Models;

use CodeIgniter\Model;

class EtlErrorModel extends Model
{
    protected $table            = 'etl_errors';
    protected $primaryKey       = 'error_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'run_id',
        'step_id',
        'error_type',
        'error_code',
        'error_message',
        'error_data',
        'stack_trace',
        'severity',
        'resolved'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'error_id'   => 'integer',
        'run_id'     => 'integer',
        'step_id'    => 'integer',
        'resolved'   => 'boolean',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'run_id'        => 'required|integer',
        'error_type'    => 'required|max_length[50]',
        'error_message' => 'required',
        'severity'      => 'required|in_list[low,medium,high,critical]',
    ];

    /**
     * Registra un nuevo error
     */
    public function logError(array $errorData): int
    {
        $data = [
            'run_id'        => $errorData['run_id'],
            'step_id'       => $errorData['step_id'] ?? null,
            'error_type'    => $errorData['error_type'],
            'error_code'    => $errorData['error_code'] ?? null,
            'error_message' => $errorData['error_message'],
            'error_data'    => isset($errorData['error_data']) ? json_encode($errorData['error_data']) : null,
            'stack_trace'   => isset($errorData['stack_trace']) ? json_encode($errorData['stack_trace']) : null,
            'severity'      => $errorData['severity'] ?? 'medium',
            'resolved'      => false,
        ];

        return $this->insert($data);
    }

    /**
     * Marca un error como resuelto
     */
    public function resolveError(int $errorId): bool
    {
        return $this->update($errorId, ['resolved' => true]);
    }

    /**
     * Obtiene errores por ejecución
     */
    public function getErrorsByRun(int $runId): array
    {
        return $this->where('run_id', $runId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Obtiene errores no resueltos
     */
    public function getUnresolvedErrors(string $severity = null): array
    {
        $query = $this->where('resolved', false);

        if ($severity) {
            $query->where('severity', $severity);
        }

        return $query->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Obtiene estadísticas de errores
     */
    public function getErrorStats(int $days = 7): array
    {
        $startDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        return $this->select('
                error_type,
                severity,
                COUNT(*) as total_errors,
                COUNT(CASE WHEN resolved = 0 THEN 1 END) as unresolved_errors,
                MAX(created_at) as last_occurrence
            ')
            ->where('created_at >=', $startDate)
            ->groupBy('error_type, severity')
            ->orderBy('total_errors', 'DESC')
            ->findAll();
    }

    /**
     * Obtiene errores críticos recientes
     */
    public function getCriticalErrors(int $hours = 24): array
    {
        $startDate = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));

        return $this->where('severity', 'critical')
            ->where('created_at >=', $startDate)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}