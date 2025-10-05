<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class EtlConfigSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'config_key'   => 'etl.batch_size',
                'config_value' => '1000',
                'config_type'  => 'integer',
                'description'  => 'Tamaño de lote para procesamiento ETL',
                'is_active'    => true,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
            [
                'config_key'   => 'etl.max_retries',
                'config_value' => '3',
                'config_type'  => 'integer',
                'description'  => 'Número máximo de reintentos para operaciones ETL',
                'is_active'    => true,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
            [
                'config_key'   => 'etl.log_level',
                'config_value' => 'info',
                'config_type'  => 'string',
                'description'  => 'Nivel de logging: debug, info, warning, error',
                'is_active'    => true,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
            [
                'config_key'   => 'etl.parallel_workers',
                'config_value' => '2',
                'config_type'  => 'integer',
                'description'  => 'Número de workers paralelos para ETL',
                'is_active'    => true,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
            [
                'config_key'   => 'etl.extract_chunk_size',
                'config_value' => '5000',
                'config_type'  => 'integer',
                'description'  => 'Tamaño de chunk para extracción de datos',
                'is_active'    => true,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
            [
                'config_key'   => 'etl.cleanup_days',
                'config_value' => '90',
                'config_type'  => 'integer',
                'description'  => 'Días de retención para logs de ETL',
                'is_active'    => true,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
            [
                'config_key'   => 'etl.enable_scd',
                'config_value' => 'true',
                'config_type'  => 'boolean',
                'description'  => 'Habilitar Slowly Changing Dimensions',
                'is_active'    => true,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
            [
                'config_key'   => 'etl.data_quality_checks',
                'config_value' => 'true',
                'config_type'  => 'boolean',
                'description'  => 'Habilitar checks de calidad de datos',
                'is_active'    => true,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
            [
                'config_key'   => 'etl.source_connection',
                'config_value' => 'default',
                'config_type'  => 'string',
                'description'  => 'Conexión de base de datos fuente',
                'is_active'    => true,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
            [
                'config_key'   => 'etl.target_connection',
                'config_value' => 'default',
                'config_type'  => 'string',
                'description'  => 'Conexión de base de datos destino',
                'is_active'    => true,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
            [
                'config_key'   => 'api.rate_limit_per_minute',
                'config_value' => '60',
                'config_type'  => 'integer',
                'description'  => 'Límite de requests por minuto para API',
                'is_active'    => true,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
            [
                'config_key'   => 'api.max_records_per_request',
                'config_value' => '1000',
                'config_type'  => 'integer',
                'description'  => 'Máximo número de registros por request de API',
                'is_active'    => true,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
            [
                'config_key'   => 'api.cache_ttl_seconds',
                'config_value' => '300',
                'config_type'  => 'integer',
                'description'  => 'Tiempo de vida del cache de API en segundos',
                'is_active'    => true,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
            [
                'config_key'   => 'dashboard.auto_refresh_seconds',
                'config_value' => '30',
                'config_type'  => 'integer',
                'description'  => 'Intervalo de auto-refresh del dashboard en segundos',
                'is_active'    => true,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
            [
                'config_key'   => 'dashboard.default_date_range',
                'config_value' => '30',
                'config_type'  => 'integer',
                'description'  => 'Rango de fechas por defecto en días para dashboard',
                'is_active'    => true,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('etl_config')->insertBatch($data);
    }
}