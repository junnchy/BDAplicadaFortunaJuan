<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStagingTables extends Migration
{
    public function up()
    {
        // Tabla staging para órdenes
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'order_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'customer_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'order_date' => [
                'type' => 'DATE',
            ],
            'order_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'total_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'currency' => [
                'type'       => 'VARCHAR',
                'constraint' => 3,
                'default'    => 'USD',
            ],
            'source_system' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'OLTP',
            ],
            'extract_date' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
            'processed' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addKey('order_id');
        $this->forge->addKey('customer_id');
        $this->forge->addKey(['extract_date', 'processed']);
        $this->forge->createTable('stg_orders');

        // Tabla staging para líneas de órdenes
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'order_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'line_number' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'product_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'quantity' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,3',
            ],
            'unit_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,4',
            ],
            'line_total' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'discount_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'tax_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'source_system' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'OLTP',
            ],
            'extract_date' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
            'processed' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addKey('order_id');
        $this->forge->addKey('product_id');
        $this->forge->addKey(['extract_date', 'processed']);
        $this->forge->createTable('stg_order_lines');

        // Tabla staging para productos
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'product_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'product_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
            'product_category' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'product_family' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'list_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,4',
            ],
            'cost' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,4',
            ],
            'is_active' => [
                'type'    => 'BOOLEAN',
                'default' => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'specifications' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'source_system' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'OLTP',
            ],
            'extract_date' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
            'processed' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addKey('product_id');
        $this->forge->addKey('product_family');
        $this->forge->addKey(['extract_date', 'processed']);
        $this->forge->createTable('stg_products');

        // Tabla staging para clientes
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'customer_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'customer_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
            'customer_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'segment' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'address' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'city' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'state' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'country' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'postal_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'registration_date' => [
                'type' => 'DATE',
            ],
            'is_active' => [
                'type'    => 'BOOLEAN',
                'default' => true,
            ],
            'credit_limit' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => true,
            ],
            'source_system' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'OLTP',
            ],
            'extract_date' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
            'processed' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addKey('customer_id');
        $this->forge->addKey('segment');
        $this->forge->addKey('country');
        $this->forge->addKey(['extract_date', 'processed']);
        $this->forge->createTable('stg_customers');
    }

    public function down()
    {
        $this->forge->dropTable('stg_customers', true);
        $this->forge->dropTable('stg_products', true);
        $this->forge->dropTable('stg_order_lines', true);
        $this->forge->dropTable('stg_orders', true);
    }
}