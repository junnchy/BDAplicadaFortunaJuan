<?php

namespace App\Libraries;

/**
 * Utilidad para importar diferentes formatos de datos al sistema ETL
 */
class DataImporter
{
    private $db;
    private $logger;
    
    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->logger = service('logger');
    }
    
    /**
     * Importar desde archivo SQL
     */
    public function importFromSql(string $filePath, array $options = []): array
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("Archivo no encontrado: $filePath");
        }
        
        $content = file_get_contents($filePath);
        $statements = $this->parseSqlStatements($content);
        
        $results = [
            'total' => count($statements),
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        foreach ($statements as $statement) {
            try {
                $this->db->query($statement);
                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = $e->getMessage();
                $this->logger->error("Error ejecutando SQL: " . $e->getMessage());
            }
        }
        
        return $results;
    }
    
    /**
     * Importar desde CSV con mapeo a tablas ETL
     */
    public function importFromCsv(string $filePath, string $targetTable, array $columnMap = []): array
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("Archivo no encontrado: $filePath");
        }
        
        $handle = fopen($filePath, 'r');
        $headers = fgetcsv($handle);
        
        $results = [
            'rows_processed' => 0,
            'rows_inserted' => 0,
            'errors' => []
        ];
        
        while (($data = fgetcsv($handle)) !== false) {
            $results['rows_processed']++;
            
            try {
                $mappedData = $this->mapCsvRow($headers, $data, $columnMap);
                $this->db->table($targetTable)->insert($mappedData);
                $results['rows_inserted']++;
            } catch (\Exception $e) {
                $results['errors'][] = "Fila {$results['rows_processed']}: " . $e->getMessage();
            }
        }
        
        fclose($handle);
        return $results;
    }
    
    /**
     * Importar desde JSON con transformación a esquema estrella
     */
    public function importFromJson(string $filePath, array $tableMapping = []): array
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("Archivo no encontrado: $filePath");
        }
        
        $content = file_get_contents($filePath);
        $data = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException("JSON inválido: " . json_last_error_msg());
        }
        
        return $this->processJsonData($data, $tableMapping);
    }
    
    /**
     * Validar estructura de datos antes de importar
     */
    public function validateData(array $data, string $targetTable): array
    {
        $validation = [
            'valid' => true,
            'errors' => [],
            'warnings' => []
        ];
        
        // Obtener estructura de la tabla
        $fields = $this->db->getFieldData($targetTable);
        $requiredFields = array_filter($fields, fn($field) => !$field->nullable);
        
        foreach ($data as $row) {
            foreach ($requiredFields as $field) {
                if (!isset($row[$field->name]) || empty($row[$field->name])) {
                    $validation['errors'][] = "Campo requerido faltante: {$field->name}";
                    $validation['valid'] = false;
                }
            }
        }
        
        return $validation;
    }
    
    private function parseSqlStatements(string $content): array
    {
        // Remover comentarios
        $content = preg_replace('/--.*$/m', '', $content);
        $content = preg_replace('/\/\*.*?\*\//s', '', $content);
        
        // Dividir por punto y coma
        $statements = explode(';', $content);
        
        return array_filter(
            array_map('trim', $statements),
            fn($stmt) => !empty($stmt)
        );
    }
    
    private function mapCsvRow(array $headers, array $data, array $columnMap): array
    {
        $mapped = [];
        
        foreach ($headers as $index => $header) {
            $targetColumn = $columnMap[$header] ?? $header;
            $mapped[$targetColumn] = $data[$index] ?? null;
        }
        
        return $mapped;
    }
    
    private function processJsonData(array $data, array $tableMapping): array
    {
        $results = [
            'tables_processed' => 0,
            'total_records' => 0,
            'errors' => []
        ];
        
        foreach ($tableMapping as $jsonPath => $tableName) {
            try {
                $records = $this->extractJsonPath($data, $jsonPath);
                
                foreach ($records as $record) {
                    $this->db->table($tableName)->insert($record);
                    $results['total_records']++;
                }
                
                $results['tables_processed']++;
            } catch (\Exception $e) {
                $results['errors'][] = "Error procesando $jsonPath: " . $e->getMessage();
            }
        }
        
        return $results;
    }
    
    private function extractJsonPath(array $data, string $path): array
    {
        $keys = explode('.', $path);
        $current = $data;
        
        foreach ($keys as $key) {
            if (!isset($current[$key])) {
                return [];
            }
            $current = $current[$key];
        }
        
        return is_array($current) ? $current : [$current];
    }
}