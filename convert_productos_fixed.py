#!/usr/bin/env python3
"""
Script mejorado para convertir dim_productos.sql con manejo robusto de comillas
"""

import re

def escape_sql_value(value):
    """Escapar valores SQL apropiadamente"""
    if not value or value.strip() == 'NULL':
        return 'NULL'
    
    value = value.strip()
    
    # Si ya está entre comillas, extraer el contenido
    if value.startswith("'") and value.endswith("'"):
        inner_value = value[1:-1]
    else:
        inner_value = value
    
    # Escapar comillas simples duplicándolas
    escaped_value = inner_value.replace("'", "''")
    
    # Si es un número, no agregar comillas
    try:
        float(escaped_value)
        return escaped_value
    except ValueError:
        return f"'{escaped_value}'"

def parse_sql_insert(line):
    """Parsear una línea INSERT INTO con manejo robusto de valores"""
    # Buscar el patrón VALUES (...)
    match = re.search(r'VALUES\s*\((.*)\);?$', line, re.DOTALL)
    if not match:
        return None
    
    values_str = match.group(1)
    values = []
    current_value = ""
    in_quotes = False
    i = 0
    
    while i < len(values_str):
        char = values_str[i]
        
        if char == "'" and (i == 0 or values_str[i-1] != '\\'):
            in_quotes = not in_quotes
            current_value += char
        elif char == ',' and not in_quotes:
            values.append(current_value.strip())
            current_value = ""
        else:
            current_value += char
        i += 1
    
    # Agregar el último valor
    if current_value.strip():
        values.append(current_value.strip())
    
    return values

def convert_productos():
    input_file = 'writable/sql_imports/dim_producto.sql'
    output_file = 'writable/sql_imports/dim_producto_final.sql'
    
    try:
        with open(input_file, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Procesar línea por línea para manejar mejor los VALUES multilinea
        lines = content.split('\n')
        current_insert = ""
        converted_statements = []
        
        for line in lines:
            line = line.strip()
            if line.startswith('INSERT INTO dim_productos'):
                current_insert = line
            elif current_insert and line:
                current_insert += " " + line
                
            # Si tenemos un statement completo (termina con ;)
            if current_insert and current_insert.endswith(';'):
                values = parse_sql_insert(current_insert)
                if values and len(values) >= 20:
                    try:
                        # Mapear campos con escape apropiado
                        producto_sk = len(converted_statements) + 1
                        producto_id = escape_sql_value(values[0])
                        producto_nombre = escape_sql_value(values[1])
                        familia_id = escape_sql_value(values[2])
                        familia_nombre = escape_sql_value(values[3])
                        categoria_id = escape_sql_value(values[4])
                        categoria_nombre = escape_sql_value(values[5])
                        precio_lista = escape_sql_value(values[6])
                        precio_costo = escape_sql_value(values[7])
                        precio_venta = escape_sql_value(values[8])
                        margen_ganancia = escape_sql_value(values[9])
                        stock_actual = escape_sql_value(values[10])
                        stock_minimo = escape_sql_value(values[11])
                        unidad_medida = escape_sql_value(values[12])
                        descripcion = escape_sql_value(values[13]) if len(values) > 13 else 'NULL'
                        especificaciones = escape_sql_value(values[14]) if len(values) > 14 else 'NULL'
                        marca = escape_sql_value(values[15]) if len(values) > 15 else 'NULL'
                        proveedor = escape_sql_value(values[16]) if len(values) > 16 else 'NULL'
                        estado = escape_sql_value(values[17]) if len(values) > 17 else '1'
                        fecha_creacion = escape_sql_value(values[18]) if len(values) > 18 else "'2025-01-05 19:25:10'"
                        fecha_actualizacion = escape_sql_value(values[19]) if len(values) > 19 else 'NULL'
                        
                        # Generar INSERT statement
                        insert_sql = f"""INSERT INTO dim_producto (producto_sk, producto_id, producto_nombre, familia_id, familia_nombre, categoria_id, categoria_nombre, precio_lista, precio_costo, precio_venta, margen_ganancia, stock_actual, stock_minimo, unidad_medida, descripcion, especificaciones, marca, proveedor, estado, fecha_creacion, fecha_actualizacion, codigo_barras, peso, dimensiones, imagen_url) VALUES ({producto_sk}, {producto_id}, {producto_nombre}, {familia_id}, {familia_nombre}, {categoria_id}, {categoria_nombre}, {precio_lista}, {precio_costo}, {precio_venta}, {margen_ganancia}, {stock_actual}, {stock_minimo}, {unidad_medida}, {descripcion}, {especificaciones}, {marca}, {proveedor}, {estado}, {fecha_creacion}, {fecha_actualizacion}, NULL, NULL, NULL, NULL);"""
                        
                        converted_statements.append(insert_sql)
                        
                    except Exception as e:
                        print(f"Error procesando statement {len(converted_statements)+1}: {e}")
                
                current_insert = ""
        
        # Escribir archivo convertido
        with open(output_file, 'w', encoding='utf-8') as f:
            for stmt in converted_statements:
                f.write(stmt + '\n')
        
        print(f"✓ Conversión completada: {len(converted_statements)} registros convertidos")
        return True
        
    except Exception as e:
        print(f"✗ Error: {e}")
        return False

if __name__ == "__main__":
    convert_productos()