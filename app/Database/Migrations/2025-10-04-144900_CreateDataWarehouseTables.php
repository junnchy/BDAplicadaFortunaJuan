<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDataWarehouseTables extends Migration
{
    public function up()
    {
        // Dimensión Tiempo
        $this->forge->addField([
            'tiempo_sk' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'fecha_natural' => [
                'type' => 'DATE',
            ],
            'año' => [
                'type'     => 'SMALLINT',
                'unsigned' => true,
            ],
            'trimestre' => [
                'type'     => 'TINYINT',
                'unsigned' => true,
            ],
            'mes' => [
                'type'     => 'TINYINT',
                'unsigned' => true,
            ],
            'semana' => [
                'type'     => 'TINYINT',
                'unsigned' => true,
            ],
            'dia' => [
                'type'     => 'TINYINT',
                'unsigned' => true,
            ],
            'dia_semana' => [
                'type'     => 'TINYINT',
                'unsigned' => true,
            ],
            'nombre_dia' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'nombre_mes' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'trimestre_nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
            ],
            'es_fin_semana' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'es_feriado' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'nombre_feriado' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'semana_año' => [
                'type'     => 'TINYINT',
                'unsigned' => true,
            ],
            'dia_año' => [
                'type'     => 'SMALLINT',
                'unsigned' => true,
            ],
            'fecha_primera_semana' => [
                'type' => 'DATE',
            ],
            'fecha_ultimo_mes' => [
                'type' => 'DATE',
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
        
        $this->forge->addKey('tiempo_sk', true);
        $this->forge->addUniqueKey('fecha_natural');
        $this->forge->addKey(['año', 'mes']);
        $this->forge->addKey(['año', 'trimestre']);
        $this->forge->addKey('semana_año');
        $this->forge->createTable('dim_tiempo');

        // Dimensión Producto
        $this->forge->addField([
            'producto_sk' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'producto_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'producto_nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
            'familia_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'familia_nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'categoria' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'subcategoria' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'precio_lista' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,4',
            ],
            'costo_estandar' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,4',
            ],
            'margen_bruto' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
            ],
            'descripcion' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'activo' => [
                'type'    => 'BOOLEAN',
                'default' => true,
            ],
            'fecha_lanzamiento' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'fecha_descontinuacion' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'unidad_medida' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'unidad',
            ],
            'peso_kg' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,3',
                'null'       => true,
            ],
            'dimensiones' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'color' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'marca' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'scd_version' => [
                'type'     => 'INT',
                'unsigned' => true,
                'default'  => 1,
            ],
            'fecha_efectiva_desde' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
            'fecha_efectiva_hasta' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'es_actual' => [
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
        
        $this->forge->addKey('producto_sk', true);
        $this->forge->addKey(['producto_id', 'es_actual']);
        $this->forge->addKey('familia_id');
        $this->forge->addKey('categoria');
        $this->forge->addKey('activo');
        $this->forge->createTable('dim_producto');

        // Dimensión Cliente
        $this->forge->addField([
            'cliente_sk' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'cliente_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'cliente_nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
            'segmento' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'tipo_cliente' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'telefono' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'direccion' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'ciudad' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'estado_provincia' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'pais' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'region' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'codigo_postal' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'fecha_registro' => [
                'type' => 'DATE',
            ],
            'activo' => [
                'type'    => 'BOOLEAN',
                'default' => true,
            ],
            'limite_credito' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => true,
            ],
            'score_credito' => [
                'type'     => 'SMALLINT',
                'unsigned' => true,
                'null'     => true,
            ],
            'preferencias' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'fecha_primera_compra' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'fecha_ultima_compra' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'total_compras_historicas' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'numero_ordenes_historicas' => [
                'type'     => 'INT',
                'unsigned' => true,
                'default'  => 0,
            ],
            'scd_version' => [
                'type'     => 'INT',
                'unsigned' => true,
                'default'  => 1,
            ],
            'fecha_efectiva_desde' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
            'fecha_efectiva_hasta' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'es_actual' => [
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
        
        $this->forge->addKey('cliente_sk', true);
        $this->forge->addKey(['cliente_id', 'es_actual']);
        $this->forge->addKey('segmento');
        $this->forge->addKey('pais');
        $this->forge->addKey('region');
        $this->forge->addKey('activo');
        $this->forge->createTable('dim_cliente');

        // Tabla de Hechos - Ventas
        $this->forge->addField([
            'venta_sk' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'tiempo_sk' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'producto_sk' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'cliente_sk' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'orden_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'linea_numero' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'cantidad' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,3',
            ],
            'precio_unitario' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,4',
            ],
            'monto_linea' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'descuento_monto' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'descuento_porcentaje' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0,
            ],
            'impuesto_monto' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'costo_unitario' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,4',
            ],
            'costo_total' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'margen_monto' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'margen_porcentaje' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
            ],
            'monto_neto' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'moneda' => [
                'type'       => 'VARCHAR',
                'constraint' => 3,
                'default'    => 'USD',
            ],
            'tipo_cambio' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,6',
                'default'    => 1,
            ],
            'canal_venta' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'Online',
            ],
            'vendedor_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'promocion_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'facturado' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'fecha_factura' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'numero_factura' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'etl_run_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
        ]);
        
        $this->forge->addKey('venta_sk', true);
        $this->forge->addKey('tiempo_sk');
        $this->forge->addKey('producto_sk');
        $this->forge->addKey('cliente_sk');
        $this->forge->addKey(['tiempo_sk', 'producto_sk', 'cliente_sk']);
        $this->forge->addKey('orden_id');
        $this->forge->addKey('etl_run_id');
        // Note: Foreign keys commented for SQLite compatibility
        // $this->forge->addForeignKey('tiempo_sk', 'dim_tiempo', 'tiempo_sk', 'RESTRICT', 'CASCADE');
        // $this->forge->addForeignKey('producto_sk', 'dim_producto', 'producto_sk', 'RESTRICT', 'CASCADE');
        // $this->forge->addForeignKey('cliente_sk', 'dim_cliente', 'cliente_sk', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('fact_ventas');
    }

    public function down()
    {
        $this->forge->dropTable('fact_ventas', true);
        $this->forge->dropTable('dim_cliente', true);
        $this->forge->dropTable('dim_producto', true);
        $this->forge->dropTable('dim_tiempo', true);
    }
}