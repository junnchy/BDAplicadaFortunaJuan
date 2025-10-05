<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class DatabaseAdminController extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        // Obtener lista de tablas
        $tables = $this->db->listTables();
        
        $data = [
            'title' => 'Administrador de Base de Datos',
            'user' => auth()->user(),
            'tables' => $tables,
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => '/dashboard'],
                ['label' => 'DB Admin', 'url' => '']
            ]
        ];

        return view('admin/database', $data);
    }

    public function table($tableName = null)
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        if (!$tableName) {
            return redirect()->to('/admin/database');
        }

        // Verificar que la tabla existe
        $tables = $this->db->listTables();
        if (!in_array($tableName, $tables)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Tabla '$tableName' no encontrada");
        }

        // Obtener información de la tabla
        $query = $this->db->query("PRAGMA table_info($tableName)");
        $columns = $query->getResultArray();

        // Obtener datos (limitados)
        $limit = $this->request->getGet('limit') ?? 50;
        $offset = $this->request->getGet('offset') ?? 0;
        
        $dataQuery = $this->db->table($tableName)
                              ->limit($limit, $offset)
                              ->get();
        $tableData = $dataQuery->getResultArray();

        // Contar total de registros
        $totalQuery = $this->db->table($tableName)->countAllResults();

        $data = [
            'title' => "Tabla: $tableName",
            'user' => auth()->user(),
            'tableName' => $tableName,
            'columns' => $columns,
            'tableData' => $tableData,
            'totalRecords' => $totalQuery,
            'currentLimit' => $limit,
            'currentOffset' => $offset,
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => '/dashboard'],
                ['label' => 'DB Admin', 'url' => '/admin/database'],
                ['label' => $tableName, 'url' => '']
            ]
        ];

        return view('admin/table', $data);
    }

    public function exportTable($tableName = null)
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        if (!$tableName) {
            return $this->response->setJSON(['error' => 'Nombre de tabla requerido'], 400);
        }

        // Verificar que la tabla existe
        $tables = $this->db->listTables();
        if (!in_array($tableName, $tables)) {
            return $this->response->setJSON(['error' => "Tabla '$tableName' no encontrada"], 404);
        }

        try {
            // Obtener parámetros de exportación
            $limit = $this->request->getGet('limit') ?? null; // null = todos los registros
            $format = $this->request->getGet('format') ?? 'csv';
            
            // Construir la consulta
            $builder = $this->db->table($tableName);
            if ($limit) {
                $builder->limit($limit);
            }
            
            $query = $builder->get();
            $data = $query->getResultArray();
            
            if (empty($data)) {
                return $this->response->setJSON(['error' => 'No hay datos para exportar'], 404);
            }

            // Generar nombre de archivo con timestamp
            $timestamp = date('Y-m-d_H-i-s');
            $filename = "{$tableName}_export_{$timestamp}";
            
            switch ($format) {
                case 'json':
                    return $this->exportJSON($data, $filename);
                case 'csv':
                default:
                    return $this->exportCSV($data, $filename);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error exportando tabla: ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'Error interno del servidor'], 500);
        }
    }

    private function exportCSV($data, $filename)
    {
        // Crear el contenido CSV
        $csvContent = '';
        
        if (!empty($data)) {
            // Encabezados
            $headers = array_keys($data[0]);
            $csvContent .= implode(',', array_map([$this, 'escapeCsvValue'], $headers)) . "\n";
            
            // Datos
            foreach ($data as $row) {
                $csvContent .= implode(',', array_map([$this, 'escapeCsvValue'], array_values($row))) . "\n";
            }
        }

        // Configurar headers para descarga
        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', "attachment; filename=\"{$filename}.csv\"")
            ->setHeader('Cache-Control', 'no-cache, must-revalidate')
            ->setBody($csvContent);
    }

    private function exportJSON($data, $filename)
    {
        $jsonContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        return $this->response
            ->setHeader('Content-Type', 'application/json; charset=utf-8')
            ->setHeader('Content-Disposition', "attachment; filename=\"{$filename}.json\"")
            ->setHeader('Cache-Control', 'no-cache, must-revalidate')
            ->setBody($jsonContent);
    }

    private function escapeCsvValue($value)
    {
        // Escapar valores para CSV
        if ($value === null) {
            return '';
        }
        
        $value = (string) $value;
        
        // Si contiene comillas, comas o saltos de línea, encerrar en comillas y escapar
        if (strpos($value, '"') !== false || strpos($value, ',') !== false || strpos($value, "\n") !== false || strpos($value, "\r") !== false) {
            $value = '"' . str_replace('"', '""', $value) . '"';
        }
        
        return $value;
    }

    public function query()
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $data = [
            'title' => 'Ejecutor de Consultas SQL',
            'user' => auth()->user(),
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => '/dashboard'],
                ['label' => 'DB Admin', 'url' => '/admin/database'],
                ['label' => 'Query', 'url' => '']
            ]
        ];

        if ($this->request->getMethod() === 'POST') {
            $sql = trim($this->request->getPost('sql'));
            
            if (empty($sql)) {
                $data['error'] = 'Por favor ingrese una consulta SQL';
                $data['success'] = false;
                return view('admin/query', $data);
            }
            
            try {
                // Limitar resultados para consultas SELECT sin LIMIT
                if (stripos($sql, 'SELECT') === 0 && stripos($sql, 'LIMIT') === false) {
                    $sql = rtrim($sql, ';') . ' LIMIT 100;';
                }
                
                $startTime = microtime(true);
                $query = $this->db->query($sql);
                $endTime = microtime(true);
                
                $executionTime = round(($endTime - $startTime) * 1000, 2); // en millisegundos
                
                if ($query) {
                    $result = $query->getResultArray();
                    $data['result'] = $result;
                    $data['executionTime'] = $executionTime;
                    $data['rowCount'] = count($result);
                } else {
                    $data['result'] = [];
                    $data['executionTime'] = $executionTime;
                    $data['rowCount'] = 0;
                }
                
                $data['sql'] = $sql;
                $data['success'] = true;
                
            } catch (\Exception $e) {
                $data['error'] = $e->getMessage();
                $data['sql'] = $sql;
                $data['success'] = false;
            }
        }

        return view('admin/query', $data);
    }

    public function count($tableName = null)
    {
        if (!auth()->loggedIn()) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'No autorizado']);
        }

        if (!$tableName) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Tabla no especificada']);
        }

        try {
            $count = $this->db->table($tableName)->countAllResults();
            return $this->response->setJSON(['count' => $count]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }

    public function stats()
    {
        if (!auth()->loggedIn()) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'No autorizado']);
        }

        try {
            $tables = $this->db->listTables();
            $totalRecords = 0;
            
            foreach ($tables as $table) {
                $count = $this->db->table($table)->countAllResults();
                $totalRecords += $count;
            }

            // Calcular tamaño de la base de datos
            $dbPath = WRITEPATH . 'etl_dw_system.db';
            $dbSize = 'N/A';
            if (file_exists($dbPath)) {
                $size = filesize($dbPath);
                $units = ['B', 'KB', 'MB', 'GB'];
                $power = $size > 0 ? floor(log($size, 1024)) : 0;
                $dbSize = number_format($size / pow(1024, $power), 2) . ' ' . $units[$power];
            }

            return $this->response->setJSON([
                'totalRecords' => $totalRecords,
                'dbSize' => $dbSize
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }
}