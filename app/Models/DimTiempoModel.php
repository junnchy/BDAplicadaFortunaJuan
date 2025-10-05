<?php

namespace App\Models;

use CodeIgniter\Model;

class DimTiempoModel extends Model
{
    protected $table            = 'dim_tiempo';
    protected $primaryKey       = 'tiempo_sk';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'fecha_natural',
        'año',
        'trimestre',
        'mes',
        'semana',
        'dia',
        'dia_semana',
        'nombre_dia',
        'nombre_mes',
        'trimestre_nombre',
        'es_fin_semana',
        'es_feriado',
        'nombre_feriado',
        'semana_año',
        'dia_año',
        'fecha_primera_semana',
        'fecha_ultimo_mes'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'tiempo_sk'      => 'integer',
        'año'            => 'integer',
        'trimestre'      => 'integer',
        'mes'            => 'integer',
        'semana'         => 'integer',
        'dia'            => 'integer',
        'dia_semana'     => 'integer',
        'semana_año'     => 'integer',
        'dia_año'        => 'integer',
        'es_fin_semana'  => 'boolean',
        'es_feriado'     => 'boolean',
        'fecha_natural'  => 'datetime',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Obtiene el tiempo_sk para una fecha específica
     */
    public function getTiempoSkByFecha(string $fecha): ?int
    {
        $result = $this->select('tiempo_sk')
            ->where('fecha_natural', $fecha)
            ->first();

        return $result ? $result['tiempo_sk'] : null;
    }

    /**
     * Obtiene fechas por rango
     */
    public function getFechasByRango(string $fechaInicio, string $fechaFin): array
    {
        return $this->where('fecha_natural >=', $fechaInicio)
            ->where('fecha_natural <=', $fechaFin)
            ->orderBy('fecha_natural')
            ->findAll();
    }

    /**
     * Obtiene jerarquía de tiempo para drill-down
     */
    public function getJerarquiaTiempo(string $nivel = 'año', array $filtros = []): array
    {
        $select = [];
        $groupBy = [];

        switch (strtolower($nivel)) {
            case 'año':
                $select = ['año', 'COUNT(DISTINCT fecha_natural) as total_dias'];
                $groupBy = ['año'];
                break;
            case 'trimestre':
                $select = ['año', 'trimestre', 'trimestre_nombre', 'COUNT(DISTINCT fecha_natural) as total_dias'];
                $groupBy = ['año', 'trimestre', 'trimestre_nombre'];
                break;
            case 'mes':
                $select = ['año', 'mes', 'nombre_mes', 'COUNT(DISTINCT fecha_natural) as total_dias'];
                $groupBy = ['año', 'mes', 'nombre_mes'];
                break;
            case 'semana':
                $select = ['año', 'semana_año', 'fecha_primera_semana', 'COUNT(DISTINCT fecha_natural) as total_dias'];
                $groupBy = ['año', 'semana_año', 'fecha_primera_semana'];
                break;
            case 'dia':
                $select = ['fecha_natural', 'nombre_dia', 'es_fin_semana', 'es_feriado'];
                $groupBy = ['fecha_natural', 'nombre_dia', 'es_fin_semana', 'es_feriado'];
                break;
        }

        $query = $this->select(implode(', ', $select));

        // Aplicar filtros
        foreach ($filtros as $campo => $valor) {
            $query->where($campo, $valor);
        }

        return $query->groupBy(implode(', ', $groupBy))
            ->orderBy($groupBy[0])
            ->findAll();
    }

    /**
     * Obtiene información de período actual vs anterior
     */
    public function getComparacionPeriodos(string $fechaInicio, string $fechaFin, string $tipoPeriodo = 'month'): array
    {
        $fechaInicioAnterior = date('Y-m-d', strtotime($fechaInicio . " -1 {$tipoPeriodo}"));
        $fechaFinAnterior = date('Y-m-d', strtotime($fechaFin . " -1 {$tipoPeriodo}"));

        return [
            'periodo_actual' => $this->getFechasByRango($fechaInicio, $fechaFin),
            'periodo_anterior' => $this->getFechasByRango($fechaInicioAnterior, $fechaFinAnterior),
        ];
    }
}