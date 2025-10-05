<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEtlControlTables extends Migration
{
    public function up()
    {
        // Tabla de ejecuciones ETL
        $this->forge->addField([
            'run_id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'command' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['running', 'success', 'failed', 'cancelled'],
                'default'    => 'running',
            ],
            'started_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'ended_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'parameters' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'total_records' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'default'  => 0,
            ],
            'processed_records' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'default'  => 0,
            ],
            'failed_records' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'default'  => 0,
            ],
            'execution_time_ms' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'memory_usage_mb' => [
                'type'     => 'DECIMAL',
                'constraint' => '10,2',
                'null'     => true,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
        ]);
        
        $this->forge->addKey('run_id', true);
        $this->forge->addKey('command');
        $this->forge->addKey(['status', 'started_at']);
        $this->forge->createTable('etl_runs');

        // Tabla de pasos de ejecución ETL
        $this->forge->addField([
            'step_id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'run_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'step_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['running', 'success', 'failed', 'skipped'],
                'default'    => 'running',
            ],
            'rows_affected' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'default'  => 0,
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'started_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'ended_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'execution_time_ms' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'additional_info' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
        ]);
        
        $this->forge->addKey('step_id', true);
        $this->forge->addKey('run_id');
        $this->forge->addKey(['run_id', 'step_name']);
        // Note: Foreign keys commented for SQLite compatibility
        // $this->forge->addForeignKey('run_id', 'etl_runs', 'run_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('etl_run_steps');

        // Tabla de errores ETL
        $this->forge->addField([
            'error_id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'run_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'step_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
            'error_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'error_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'error_message' => [
                'type' => 'TEXT',
            ],
            'error_data' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'stack_trace' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'severity' => [
                'type'       => 'ENUM',
                'constraint' => ['low', 'medium', 'high', 'critical'],
                'default'    => 'medium',
            ],
            'resolved' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
        ]);
        
        $this->forge->addKey('error_id', true);
        $this->forge->addKey('run_id');
        $this->forge->addKey('step_id');
        $this->forge->addKey(['error_type', 'severity']);
        $this->forge->addKey('created_at');
        // Note: Foreign keys commented for SQLite compatibility
        // $this->forge->addForeignKey('run_id', 'etl_runs', 'run_id', 'CASCADE', 'CASCADE');
        // $this->forge->addForeignKey('step_id', 'etl_run_steps', 'step_id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('etl_errors');

        // Tabla de configuración ETL
        $this->forge->addField([
            'config_id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'config_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'config_value' => [
                'type' => 'TEXT',
            ],
            'config_type' => [
                'type'       => 'ENUM',
                'constraint' => ['string', 'integer', 'boolean', 'json', 'decimal'],
                'default'    => 'string',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_active' => [
                'type'    => 'BOOLEAN',
                'default' => true,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
        ]);
        
        $this->forge->addKey('config_id', true);
        $this->forge->addUniqueKey('config_key');
        $this->forge->createTable('etl_config');
    }

    public function down()
    {
        $this->forge->dropTable('etl_errors', true);
        $this->forge->dropTable('etl_run_steps', true);
        $this->forge->dropTable('etl_runs', true);
        $this->forge->dropTable('etl_config', true);
    }
}