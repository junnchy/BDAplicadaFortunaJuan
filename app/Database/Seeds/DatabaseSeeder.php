<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        echo "🔧 Iniciando seeds del sistema ETL-DW...\n";
        
        // 1. Configuración ETL
        echo "📋 Poblando configuración ETL...\n";
        $this->call('EtlConfigSeeder');
        
        // 2. Dimensión tiempo
        echo "📅 Poblando dimensión tiempo (2020-2026)...\n";
        $this->call('DimTiempoSeeder');
        
        // 3. Datos de ejemplo
        echo "📊 Poblando datos de ejemplo...\n";
        $this->call('SampleDataSeeder');
        
        echo "✅ Seeds completados exitosamente!\n";
        echo "\n📈 Resumen de datos cargados:\n";
        
        // Mostrar estadísticas
        $this->showStats();
    }
    
    private function showStats()
    {
        $stats = [
            'etl_config' => $this->db->table('etl_config')->countAll(),
            'dim_tiempo' => $this->db->table('dim_tiempo')->countAll(),
            'dim_producto' => $this->db->table('dim_producto')->countAll(),
            'dim_cliente' => $this->db->table('dim_cliente')->countAll(),
            'fact_ventas' => $this->db->table('fact_ventas')->countAll(),
            'stg_products' => $this->db->table('stg_products')->countAll(),
            'stg_customers' => $this->db->table('stg_customers')->countAll(),
            'stg_orders' => $this->db->table('stg_orders')->countAll(),
            'stg_order_lines' => $this->db->table('stg_order_lines')->countAll(),
        ];
        
        foreach ($stats as $table => $count) {
            echo "  • {$table}: {$count} registros\n";
        }
        
        echo "\n🎯 El sistema está listo para usar:\n";
        echo "  • php spark migrate (ya ejecutado)\n";
        echo "  • php spark db:seed DatabaseSeeder (ya ejecutado)\n";
        echo "  • php spark serve (para iniciar el servidor)\n";
        echo "  • php spark etl:extract (para probar ETL)\n";
    }
}