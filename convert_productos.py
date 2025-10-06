#!/usr/bin/env python3
"""
Script para convertir el archivo dim_productos.sql original 
a la estructura correcta de la tabla dim_producto
"""

import re
import sys

def convert_product_sql():
    input_file = 'writable/sql_imports/dim_producto.sql'
    output_file = 'writable/sql_imports/dim_producto_final.sql'
    
    try:
        with open(input_file, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Buscar todos los INSERT statements con regex
        pattern = r"INSERT INTO dim_productos.*?\((.*?)\) VALUES \((.*?)\);"
        matches = re.findall(pattern, content, re.DOTALL)
        
        converted_lines = []
        converted_lines.append("-- Productos convertidos a estructura final\n")
        
        for match in matches:
            columns_part = match[0].strip()
            values_part = match[1].strip()
            
            # Extraer valores individuales 
            values = re.split(r',(?=(?:[^"\']*["\'][^"\']*["\'])*[^"\']*$)', values_part)
            values = [v.strip() for v in values]
            
            if len(values) >= 19:  # Verificar que tenemos suficientes valores
                # Mapear los valores originales
                producto_sk = values[0]
                producto_id = values[1] 
                producto_nombre = values[2].replace("'", "''") if values[2] != 'NULL' else values[2]
                familia_id = values[3]
                familia_nombre = values[4].replace("'", "''") if values[4] != 'NULL' else values[4]
                categoria = values[5].replace("'", "''") if values[5] != 'NULL' else values[5]
                subcategoria = values[6].replace("'", "''") if values[6] != 'NULL' else values[6]
                precio_lista = values[7]
                costo_estandar = values[8]
                margen_bruto = values[9]
                descripcion = values[10].replace("'", "''") if values[10] != 'NULL' else values[10]
                activo = values[11]
                fecha_lanzamiento = values[12]
                unidad_medida = values[13]
                marca = values[14]
                scd_version = values[15]
                fecha_efectiva_desde = values[16]
                es_actual = values[17]
                created_at = values[18]
                updated_at = values[19] if len(values) > 19 else values[18]
                
                # Crear el INSERT adaptado con todas las columnas necesarias
                insert_sql = f"""INSERT INTO dim_producto (producto_sk, producto_id, producto_nombre, familia_id, familia_nombre, categoria, subcategoria, precio_lista, costo_estandar, margen_bruto, descripcion, activo, fecha_lanzamiento, fecha_descontinuacion, unidad_medida, peso_kg, dimensiones, color, marca, scd_version, fecha_efectiva_desde, fecha_efectiva_hasta, es_actual, created_at, updated_at) VALUES ({producto_sk}, {producto_id}, {producto_nombre}, {familia_id}, {familia_nombre}, {categoria}, {subcategoria}, {precio_lista}, {costo_estandar}, {margen_bruto}, {descripcion}, {activo}, {fecha_lanzamiento}, NULL, {unidad_medida}, NULL, NULL, NULL, {marca}, {scd_version}, {fecha_efectiva_desde}, NULL, {es_actual}, {created_at}, {updated_at});"""
                
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
    convert_product_sql()