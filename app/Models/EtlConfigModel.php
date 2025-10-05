<?php

namespace App\Models;

use CodeIgniter\Model;

class EtlConfigModel extends Model
{
    protected $table            = 'etl_config';
    protected $primaryKey       = 'config_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'config_key',
        'config_value',
        'config_type',
        'description',
        'is_active'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'config_id' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'config_key'   => 'required|max_length[100]|is_unique[etl_config.config_key,config_id,{config_id}]',
        'config_value' => 'required',
        'config_type'  => 'required|in_list[string,integer,boolean,json,decimal]',
    ];

    /**
     * Obtiene todas las configuraciones activas
     */
    public function getActiveConfigs(): array
    {
        return $this->where('is_active', true)
            ->orderBy('config_key')
            ->findAll();
    }

    /**
     * Obtiene una configuración específica
     */
    public function getConfig(string $key): ?array
    {
        return $this->where('config_key', $key)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Obtiene valor de configuración con tipo correcto
     */
    public function getConfigValue(string $key, mixed $default = null): mixed
    {
        $config = $this->getConfig($key);
        
        if (!$config) {
            return $default;
        }

        return $this->castConfigValue($config['config_value'], $config['config_type']);
    }

    /**
     * Actualiza una configuración
     */
    public function updateConfig(string $key, string $value): bool
    {
        $existing = $this->where('config_key', $key)->first();
        
        if ($existing) {
            return $this->update($existing['config_id'], ['config_value' => $value]);
        }

        return false;
    }

    /**
     * Convierte valor según el tipo
     */
    private function castConfigValue(string $value, string $type): mixed
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
     * Obtiene configuraciones por prefijo
     */
    public function getConfigsByPrefix(string $prefix): array
    {
        return $this->like('config_key', $prefix, 'after')
            ->where('is_active', true)
            ->orderBy('config_key')
            ->findAll();
    }
}