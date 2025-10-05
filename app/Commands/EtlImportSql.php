<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class EtlImportSql extends BaseCommand
{
    protected $group       = 'ETL';
    protected $name        = 'etl:import-sql';
    protected $description = 'Importa datos desde un archivo SQL al sistema ETL';
    protected $usage       = 'etl:import-sql [options]';
    protected $arguments   = [];
    protected $options     = [
        '--file'   => 'Ruta al archivo SQL a importar',
        '--table'  => 'Tabla destino (opcional, se detecta automáticamente)',
        '--clean'  => 'Limpiar tabla antes de importar',
        '--preview' => 'Solo mostrar preview sin importar'
    ];

    public function run(array $params)
    {
        CLI::write('=== Importador SQL para Sistema ETL ===', 'yellow');
        
        $sqlFile = CLI::getOption('file');
        $targetTable = CLI::getOption('table');
        $cleanTable = CLI::getOption('clean');
        $preview = CLI::getOption('preview');
        
        // Si no se proporciona archivo, pedirlo
        if (!$sqlFile) {
            $sqlFile = CLI::prompt('Ruta al archivo SQL:', null, 'required');
        }
        
        // Verificar que el archivo existe
        if (!file_exists($sqlFile)) {
            CLI::error("Error: El archivo '$sqlFile' no existe.");
            return;
        }
        
        CLI::write("Procesando archivo: $sqlFile", 'green');
        
        try {
            // Leer el archivo SQL
            $sqlContent = file_get_contents($sqlFile);
            
            if (empty($sqlContent)) {
                CLI::error("Error: El archivo está vacío.");
                return;
            }
            
            // Analizar el contenido SQL
            $analysis = $this->analyzeSqlContent($sqlContent);
            
            CLI::write("=== Análisis del archivo SQL ===", 'cyan');
            CLI::write("Tipo detectado: {$analysis['type']}", 'white');
            CLI::write("Tablas encontradas: " . implode(', ', $analysis['tables']), 'white');
            CLI::write("Número de statements: {$analysis['statement_count']}", 'white');
            
            if ($preview) {
                CLI::write("\n=== PREVIEW MODE - No se importarán datos ===", 'yellow');
                $this->showPreview($analysis);
                return;
            }
            
            // Confirmar importación
            if (!CLI::prompt('¿Desea continuar con la importación?', ['y', 'n']) === 'y') {
                CLI::write('Importación cancelada.', 'yellow');
                return;
            }
            
            // Ejecutar importación
            $this->executeImport($sqlContent, $analysis, $targetTable, (bool)$cleanTable);
            
        } catch (\Exception $e) {
            CLI::error("Error durante la importación: " . $e->getMessage());
        }
    }
    
    private function analyzeSqlContent(string $content): array
    {
        $analysis = [
            'type' => 'unknown',
            'tables' => [],
            'statement_count' => 0,
            'statements' => []
        ];
        
        // Limpiar comentarios y dividir en statements
        $content = preg_replace('/--.*$/m', '', $content);
        $content = preg_replace('/\/\*.*?\*\//s', '', $content);
        
        $statements = array_filter(
            array_map('trim', explode(';', $content)),
            fn($stmt) => !empty($stmt)
        );
        
        $analysis['statement_count'] = count($statements);
        $analysis['statements'] = $statements;
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            
            // Detectar tipo de statement
            if (preg_match('/^CREATE\s+TABLE\s+`?(\w+)`?/i', $statement, $matches)) {
                $analysis['type'] = 'schema';
                $analysis['tables'][] = $matches[1];
            } elseif (preg_match('/^INSERT\s+INTO\s+`?(\w+)`?/i', $statement, $matches)) {
                $analysis['type'] = 'data';
                $analysis['tables'][] = $matches[1];
            } elseif (preg_match('/^UPDATE\s+`?(\w+)`?/i', $statement, $matches)) {
                $analysis['type'] = 'data';
                $analysis['tables'][] = $matches[1];
            }
        }
        
        $analysis['tables'] = array_unique($analysis['tables']);
        
        return $analysis;
    }
    
    private function showPreview(array $analysis): void
    {
        CLI::write("\n=== PREVIEW DE STATEMENTS ===", 'cyan');
        
        foreach (array_slice($analysis['statements'], 0, 5) as $i => $statement) {
            CLI::write("\nStatement " . ($i + 1) . ":", 'yellow');
            CLI::write(substr($statement, 0, 200) . (strlen($statement) > 200 ? '...' : ''), 'white');
        }
        
        if (count($analysis['statements']) > 5) {
            CLI::write("\n... y " . (count($analysis['statements']) - 5) . " statements más", 'light_gray');
        }
    }
    
    private function executeImport(string $content, array $analysis, ?string $targetTable, bool $cleanTable): void
    {
        $db = \Config\Database::connect();
        
        CLI::write("\n=== Iniciando importación ===", 'green');
        
        // Si es necesario limpiar tablas
        if ($cleanTable && !empty($analysis['tables'])) {
            CLI::write("Limpiando tablas...", 'yellow');
            foreach ($analysis['tables'] as $table) {
                try {
                    $db->query("DELETE FROM `$table`");
                    CLI::write("✓ Tabla $table limpiada", 'green');
                } catch (\Exception $e) {
                    CLI::write("⚠ No se pudo limpiar tabla $table: " . $e->getMessage(), 'red');
                }
            }
        }
        
        // Ejecutar statements
        $successful = 0;
        $failed = 0;
        
        foreach ($analysis['statements'] as $i => $statement) {
            $statement = trim($statement);
            if (empty($statement)) continue;
            
            try {
                CLI::showProgress($i + 1, count($analysis['statements']));
                
                // Ejecutar statement
                $db->query($statement);
                $successful++;
                
            } catch (\Exception $e) {
                $failed++;
                CLI::write("\n⚠ Error en statement " . ($i + 1) . ": " . $e->getMessage(), 'red');
                
                // Mostrar el statement problemático (primeros 100 chars)
                CLI::write("Statement: " . substr($statement, 0, 100) . "...", 'light_gray');
            }
        }
        
        CLI::write("\n\n=== Resultado de la importación ===", 'cyan');
        CLI::write("✓ Statements exitosos: $successful", 'green');
        if ($failed > 0) {
            CLI::write("✗ Statements fallidos: $failed", 'red');
        }
        
        // Mostrar estadísticas finales
        if (!empty($analysis['tables'])) {
            CLI::write("\n=== Estadísticas de tablas ===", 'cyan');
            foreach ($analysis['tables'] as $table) {
                try {
                    $count = $db->query("SELECT COUNT(*) as count FROM `$table`")->getRow()->count;
                    CLI::write("$table: $count registros", 'white');
                } catch (\Exception $e) {
                    CLI::write("$table: Error al contar registros", 'red');
                }
            }
        }
        
        CLI::write("\n🎉 Importación completada!", 'green');
    }
}