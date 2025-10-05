<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use DateTime;
use DateInterval;

class DimTiempoSeeder extends Seeder
{
    public function run()
    {
        // Poblar dimensión tiempo para los años 2020-2026
        $startDate = new DateTime('2020-01-01');
        $endDate = new DateTime('2026-12-31');
        
        $feriados = $this->getFeriados();
        $data = [];
        
        $current = clone $startDate;
        while ($current <= $endDate) {
            $fecha = $current->format('Y-m-d');
            $año = (int)$current->format('Y');
            $mes = (int)$current->format('n');
            $dia = (int)$current->format('j');
            $diaSemanaNúmero = (int)$current->format('N'); // 1=Lunes, 7=Domingo
            $semanaAño = (int)$current->format('W');
            $diaAño = (int)$current->format('z') + 1;
            
            // Calcular trimestre
            $trimestre = ceil($mes / 3);
            
            // Nombres
            $nombreDia = $this->getNombreDia($diaSemanaNúmero);
            $nombreMes = $this->getNombreMes($mes);
            $trimestreNombre = 'Q' . $trimestre;
            
            // Flags
            $esFinSemana = in_array($diaSemanaNúmero, [6, 7]);
            $esFeriado = in_array($fecha, $feriados);
            $nombreFeriado = $esFeriado ? $this->getNombreFeriado($fecha, $feriados) : null;
            
            // Fechas relacionadas
            $fechaPrimerSemana = clone $current;
            $fechaPrimerSemana->setISODate($año, $semanaAño, 1);
            
            $fechaUltimoMes = new DateTime($año . '-' . str_pad($mes, 2, '0', STR_PAD_LEFT) . '-01');
            $fechaUltimoMes->add(new DateInterval('P1M'))->sub(new DateInterval('P1D'));
            
            $data[] = [
                'fecha_natural' => $fecha,
                'año' => $año,
                'trimestre' => $trimestre,
                'mes' => $mes,
                'semana' => $semanaAño,
                'dia' => $dia,
                'dia_semana' => $diaSemanaNúmero,
                'nombre_dia' => $nombreDia,
                'nombre_mes' => $nombreMes,
                'trimestre_nombre' => $trimestreNombre,
                'es_fin_semana' => $esFinSemana,
                'es_feriado' => $esFeriado,
                'nombre_feriado' => $nombreFeriado,
                'semana_año' => $semanaAño,
                'dia_año' => $diaAño,
                'fecha_primera_semana' => $fechaPrimerSemana->format('Y-m-d'),
                'fecha_ultimo_mes' => $fechaUltimoMes->format('Y-m-d'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            
            // Insertar en lotes de 100
            if (count($data) >= 100) {
                $this->db->table('dim_tiempo')->insertBatch($data);
                $data = [];
            }
            
            $current->add(new DateInterval('P1D'));
        }
        
        // Insertar datos restantes
        if (!empty($data)) {
            $this->db->table('dim_tiempo')->insertBatch($data);
        }
    }
    
    private function getFeriados(): array
    {
        // Feriados fijos y variables más comunes
        return [
            '2020-01-01', '2020-05-01', '2020-07-09', '2020-08-17', '2020-12-25',
            '2021-01-01', '2021-05-01', '2021-07-09', '2021-08-17', '2021-12-25',
            '2022-01-01', '2022-05-01', '2022-07-09', '2022-08-17', '2022-12-25',
            '2023-01-01', '2023-05-01', '2023-07-09', '2023-08-17', '2023-12-25',
            '2024-01-01', '2024-05-01', '2024-07-09', '2024-08-17', '2024-12-25',
            '2025-01-01', '2025-05-01', '2025-07-09', '2025-08-17', '2025-12-25',
            '2026-01-01', '2026-05-01', '2026-07-09', '2026-08-17', '2026-12-25',
        ];
    }
    
    private function getNombreFeriado(string $fecha, array $feriados): ?string
    {
        $feriadosNombres = [
            '01-01' => 'Año Nuevo',
            '05-01' => 'Día del Trabajador',
            '07-09' => 'Día de la Independencia',
            '08-17' => 'Muerte de San Martín',
            '12-25' => 'Navidad',
        ];
        
        $mesDay = substr($fecha, 5);
        return $feriadosNombres[$mesDay] ?? null;
    }
    
    private function getNombreDia(int $numeroDia): string
    {
        $dias = [
            1 => 'Lunes',
            2 => 'Martes', 
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo'
        ];
        
        return $dias[$numeroDia];
    }
    
    private function getNombreMes(int $numeroMes): string
    {
        $meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre'
        ];
        
        return $meses[$numeroMes];
    }
}