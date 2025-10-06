#!/usr/bin/env python3
"""
Script para convertir el archivo dim_cliente.sql original 
a la estructura correcta de la tabla dim_cliente
"""

import re
import sys

def convert_client_sql():
    input_file = 'writable/sql_imports/dim_cliente.sql'
    output_file = 'writable/sql_imports/dim_cliente_final.sql'
    
    try:
        with open(input_file, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Buscar todos los INSERT statements con regex
        pattern = r"INSERT INTO dim_cliente.*?\((.*?)\) VALUES \((.*?)\);"
        matches = re.findall(pattern, content, re.DOTALL)
        
        converted_lines = []
        converted_lines.append("-- Clientes convertidos a estructura final\n")
        
        for match in matches:
            columns_part = match[0].strip()
            values_part = match[1].strip()
            
            # Extraer valores individuales 
            values = [v.strip().strip("'") for v in re.split(r',(?=(?:[^"]*"[^"]*")*[^"]*$)', values_part)]
            
            if len(values) >= 11:  # Verificar que tenemos suficientes valores
                # Mapear los valores originales
                cliente_sk = values[0]
                cliente_id = values[1] 
                cliente_nombre = values[2].replace("'", "''")  # Escapar comillas
                segmento = values[3]
                estado_provincia = values[4]
                activo = values[5]
                scd_version = values[6]
                fecha_efectiva_desde = values[7]
                es_actual = values[8]
                created_at = values[9]
                updated_at = values[10]
                
                # Determinar tipo_cliente basado en el nombre
                if any(word in cliente_nombre.upper() for word in ['S.R.L', 'SRL', 'S.A.', 'SA', 'SOCIEDAD', 'EMPRESA', 'CIA', 'COMPAÑIA', 'MINISTERIO', 'CLUB']):
                    tipo_cliente = 'EMPRESA'
                else:
                    tipo_cliente = 'PARTICULAR'
                
                # Crear el INSERT adaptado
                insert_sql = f"""INSERT INTO dim_cliente (cliente_sk, cliente_id, cliente_nombre, segmento, tipo_cliente, email, telefono, direccion, ciudad, estado_provincia, pais, region, codigo_postal, fecha_registro, activo, limite_credito, score_credito, preferencias, fecha_primera_compra, fecha_ultima_compra, total_compras_historicas, numero_ordenes_historicas, scd_version, fecha_efectiva_desde, fecha_efectiva_hasta, es_actual, created_at, updated_at) VALUES ({cliente_sk}, '{cliente_id}', '{cliente_nombre}', '{segmento}', '{tipo_cliente}', '', '', '', '', '{estado_provincia}', 'Argentina', 'LITORAL', '', '2025-10-05', {activo}, 0, 500, '{{}}', NULL, NULL, 0, 0, {scd_version}, '{fecha_efectiva_desde}', NULL, {es_actual}, '{created_at}', '{updated_at}');"""
                
                converted_lines.append(insert_sql)
        
        # Escribir archivo convertido
        with open(output_file, 'w', encoding='utf-8') as f:
            f.write('\n'.join(converted_lines))
        
        print(f"✓ Conversión completada: {len(converted_lines)-1} registros convertidos")
        print(f"✓ Archivo generado: {output_file}")
        
    except Exception as e:
        print(f"✗ Error durante la conversión: {e}")
        return False
    
    return True

if __name__ == "__main__":
    convert_client_sql()