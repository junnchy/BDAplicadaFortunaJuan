# Manual de Manipulación de Base de Datos
## Data Warehouse - Sistema ETL

**Fecha:** 11 de noviembre de 2025  
**Proyecto:** BDA - Trabajo Práctico Final  
**Base de Datos:** SQLite3 - `writable/etl_dw_system.db`

---

## 📑 Tabla de Contenidos

1. [Introducción](#introducción)
2. [Estructura de la Base de Datos](#estructura-de-la-base-de-datos)
3. [Acceso a la Base de Datos](#acceso-a-la-base-de-datos)
4. [Carga de Nuevos Archivos](#carga-de-nuevos-archivos)
5. [Borrado de Información](#borrado-de-información)
6. [Consultas Útiles](#consultas-útiles)
7. [Mantenimiento y Respaldos](#mantenimiento-y-respaldos)
8. [Solución de Problemas](#solución-de-problemas)

---

## 📖 Introducción

Este manual describe cómo manipular la base de datos del Data Warehouse, incluyendo:
- Carga de nuevos archivos SQL
- Eliminación de datos
- Consultas de verificación
- Mantenimiento general

### Requisitos Previos

- **SQLite3** instalado en el sistema
- Acceso a la terminal/consola
- Permisos de lectura/escritura en el directorio del proyecto
- Conocimientos básicos de SQL

---

## 🗄️ Estructura de la Base de Datos

### Tablas Principales

#### **Tablas de Dimensiones**

1. **`dim_tiempo`** - Dimensión temporal
   - `tiempo_sk` (PK)
   - `fecha_natural`, `año`, `mes`, `trimestre`
   - `dia_semana`, `nombre_dia`, `nombre_mes`

2. **`dim_cliente`** - Dimensión de clientes
   - `cliente_sk` (PK)
   - `cliente_nombre`, `email`, `segmento`
   - `ciudad`, `pais`, `activo`, `es_actual`

3. **`dim_producto`** - Dimensión de productos
   - `producto_sk` (PK)
   - `producto_nombre`, `descripcion`
   - `categoria`, `subcategoria`

4. **`dim_vendedor`** - Dimensión de vendedores
5. **`dim_sucursal`** - Dimensión de sucursales

#### **Tabla de Hechos**

**`fact_ventas`** - Tabla de hechos de ventas
- `venta_sk` (PK)
- `cliente_sk`, `producto_sk`, `tiempo_sk` (FKs)
- `monto_linea`, `cantidad`, `margen_monto`
- `orden_id`, `moneda`

#### **Tablas de Control ETL**

- `etl_config` - Configuración del proceso ETL
- `etl_runs` - Registro de ejecuciones
- `etl_run_steps` - Pasos de cada ejecución
- `etl_errors` - Errores registrados

---

## 🔌 Acceso a la Base de Datos

### Opción 1: Línea de Comandos (Recomendado)

```bash
# Navegar al directorio del proyecto
cd /Users/juanfortuna/Documents/UAI/BDA/TPFINALBDA

# Acceder a SQLite3
sqlite3 writable/etl_dw_system.db
```

### Opción 2: Modo Lectura (Solo Consultas)

```bash
sqlite3 writable/etl_dw_system.db "SELECT COUNT(*) FROM fact_ventas;"
```

### Comandos Útiles de SQLite3

Una vez dentro de SQLite3:

```sql
-- Listar todas las tablas
.tables

-- Ver estructura de una tabla
.schema fact_ventas

-- Ver estructura de todas las tablas
.schema

-- Activar headers en resultados
.headers on

-- Activar modo columna
.mode column

-- Salir
.quit
-- o
.exit
```

---

## 📥 Carga de Nuevos Archivos

### Método 1: Importación desde Archivo SQL

#### Paso 1: Preparar el Archivo SQL

Coloca tu archivo SQL en el directorio correspondiente:
- **Dimensiones:** `writable/sql_imports/`
- **Hechos:** `writable/sql_imports/`

Ejemplo de estructura de archivo:
```sql
-- dim_tiempo_2025.sql
INSERT INTO dim_tiempo (tiempo_sk, fecha_natural, año, mes, dia, trimestre, nombre_mes, nombre_dia, dia_semana) 
VALUES 
(20250101, '2025-01-01', 2025, 1, 1, 1, 'Enero', 'Miércoles', 3),
(20250102, '2025-01-02', 2025, 1, 2, 1, 'Enero', 'Jueves', 4);
```

#### Paso 2: Ejecutar la Importación

```bash
# Navegar al directorio del proyecto
cd /Users/juanfortuna/Documents/UAI/BDA/TPFINALBDA

# Importar archivo SQL
sqlite3 writable/etl_dw_system.db < writable/sql_imports/dim_tiempo_2025.sql

# Verificar la importación
sqlite3 writable/etl_dw_system.db "SELECT COUNT(*) FROM dim_tiempo WHERE año = 2025;"
```

#### Paso 3: Verificación

```sql
-- Dentro de SQLite3
SELECT COUNT(*) as total_registros FROM dim_tiempo;
SELECT MIN(fecha_natural), MAX(fecha_natural) FROM dim_tiempo;
```

### Método 2: Importación desde CSV

#### Preparar el CSV

Archivo: `productos_nuevos.csv`
```csv
producto_sk,producto_nombre,categoria,precio
10001,Producto A,Categoria 1,100.50
10002,Producto B,Categoria 2,200.00
```

#### Importar CSV

```bash
sqlite3 writable/etl_dw_system.db
```

Dentro de SQLite3:
```sql
-- Configurar modo CSV
.mode csv

-- Importar archivo
.import writable/sql_imports/productos_nuevos.csv temp_productos

-- Insertar en la tabla definitiva
INSERT INTO dim_producto (producto_sk, producto_nombre, categoria, precio)
SELECT producto_sk, producto_nombre, categoria, precio 
FROM temp_productos;

-- Eliminar tabla temporal
DROP TABLE temp_productos;

-- Verificar
SELECT * FROM dim_producto WHERE producto_sk >= 10001;
```

### Método 3: Inserción Manual de Datos

```bash
sqlite3 writable/etl_dw_system.db
```

```sql
-- Insertar un solo registro
INSERT INTO dim_cliente (
    cliente_sk, 
    cliente_nombre, 
    email, 
    segmento, 
    activo, 
    es_actual
) VALUES (
    99999, 
    'Nuevo Cliente Test', 
    'test@ejemplo.com', 
    'Premium', 
    1, 
    1
);

-- Insertar múltiples registros
INSERT INTO fact_ventas (
    venta_sk, cliente_sk, producto_sk, tiempo_sk, 
    monto_linea, cantidad, margen_monto, moneda
) VALUES 
(1000001, 1, 101, 20250101, 1000.00, 2, 200.00, 'ARS'),
(1000002, 2, 102, 20250102, 1500.00, 3, 300.00, 'ARS'),
(1000003, 3, 103, 20250103, 2000.00, 1, 400.00, 'ARS');

-- Verificar
SELECT * FROM fact_ventas WHERE venta_sk >= 1000001;
```

### Método 4: Carga con Scripts Python

Crear archivo: `scripts/cargar_datos.py`

```python
#!/usr/bin/env python3
import sqlite3
import pandas as pd

# Conectar a la base de datos
conn = sqlite3.connect('writable/etl_dw_system.db')

# Leer CSV con pandas
df = pd.read_csv('writable/sql_imports/nuevos_productos.csv')

# Insertar en la tabla
df.to_sql('dim_producto', conn, if_exists='append', index=False)

# Verificar
cursor = conn.cursor()
cursor.execute("SELECT COUNT(*) FROM dim_producto")
print(f"Total productos: {cursor.fetchone()[0]}")

# Cerrar conexión
conn.close()
print("✅ Datos cargados exitosamente")
```

Ejecutar:
```bash
python3 scripts/cargar_datos.py
```

---

## 🗑️ Borrado de Información

### ⚠️ PRECAUCIONES IMPORTANTES

**SIEMPRE hacer backup antes de borrar datos:**
```bash
# Crear backup
cp writable/etl_dw_system.db writable/backup_$(date +%Y%m%d_%H%M%S).db

# O usar el comando de SQLite
sqlite3 writable/etl_dw_system.db ".backup writable/backup.db"
```

### Borrado Selectivo

#### 1. Borrar Registros Específicos

```sql
-- Borrar ventas de un cliente específico
DELETE FROM fact_ventas 
WHERE cliente_sk = 12345;

-- Borrar ventas de un período específico
DELETE FROM fact_ventas 
WHERE tiempo_sk BETWEEN 20250101 AND 20250131;

-- Borrar productos de una categoría
DELETE FROM dim_producto 
WHERE categoria = 'Categoría Obsoleta';

-- Verificar antes de borrar
SELECT COUNT(*) FROM fact_ventas WHERE cliente_sk = 12345;
```

#### 2. Borrar con Condiciones Múltiples

```sql
-- Borrar ventas con monto bajo de clientes inactivos
DELETE FROM fact_ventas 
WHERE venta_sk IN (
    SELECT fv.venta_sk 
    FROM fact_ventas fv
    JOIN dim_cliente dc ON fv.cliente_sk = dc.cliente_sk
    WHERE dc.activo = 0 
    AND fv.monto_linea < 100
);
```

#### 3. Borrar Duplicados

```sql
-- Identificar duplicados
SELECT cliente_nombre, email, COUNT(*) as duplicados
FROM dim_cliente
GROUP BY cliente_nombre, email
HAVING COUNT(*) > 1;

-- Borrar duplicados manteniendo el registro más reciente
DELETE FROM dim_cliente
WHERE cliente_sk NOT IN (
    SELECT MAX(cliente_sk)
    FROM dim_cliente
    GROUP BY cliente_nombre, email
);
```

### Borrado Completo de Tablas

#### Vaciar una Tabla (Mantener Estructura)

```sql
-- Vaciar tabla de ventas
DELETE FROM fact_ventas;

-- Verificar
SELECT COUNT(*) FROM fact_ventas;
-- Debe retornar 0

-- Reiniciar contador (si hay AUTOINCREMENT)
DELETE FROM sqlite_sequence WHERE name='fact_ventas';
```

#### Borrar y Recrear Tabla

```sql
-- Guardar estructura
.schema fact_ventas > /tmp/fact_ventas_schema.sql

-- Borrar tabla
DROP TABLE fact_ventas;

-- Recrear tabla
-- (Pegar el SQL de la estructura aquí o ejecutar el archivo)
```

### Borrado en Cascada

```sql
-- Borrar cliente y todas sus ventas
BEGIN TRANSACTION;

-- Primero borrar las ventas (tabla de hechos)
DELETE FROM fact_ventas 
WHERE cliente_sk = 12345;

-- Luego borrar el cliente
DELETE FROM dim_cliente 
WHERE cliente_sk = 12345;

COMMIT;
```

### Borrado por Fecha

```sql
-- Borrar datos de un año específico
DELETE FROM fact_ventas 
WHERE tiempo_sk IN (
    SELECT tiempo_sk 
    FROM dim_tiempo 
    WHERE año = 2024
);

-- Borrar datos antiguos (más de 2 años)
DELETE FROM fact_ventas 
WHERE tiempo_sk IN (
    SELECT tiempo_sk 
    FROM dim_tiempo 
    WHERE fecha_natural < DATE('now', '-2 years')
);
```

### Script de Borrado Seguro

Crear archivo: `scripts/borrar_datos_seguros.sh`

```bash
#!/bin/bash

DB_PATH="writable/etl_dw_system.db"
BACKUP_DIR="writable/backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Crear directorio de backups
mkdir -p $BACKUP_DIR

# Hacer backup
echo "🔄 Creando backup..."
cp $DB_PATH "$BACKUP_DIR/backup_$TIMESTAMP.db"

# Confirmar con usuario
echo "⚠️  ¿Estás seguro de borrar los datos? (s/n)"
read -r respuesta

if [ "$respuesta" = "s" ]; then
    echo "🗑️  Borrando datos..."
    
    # Ejecutar el borrado
    sqlite3 $DB_PATH "DELETE FROM fact_ventas WHERE tiempo_sk < 20240101;"
    
    echo "✅ Datos borrados"
    echo "📦 Backup guardado en: $BACKUP_DIR/backup_$TIMESTAMP.db"
else
    echo "❌ Operación cancelada"
fi
```

Dar permisos y ejecutar:
```bash
chmod +x scripts/borrar_datos_seguros.sh
./scripts/borrar_datos_seguros.sh
```

---

## 🔍 Consultas Útiles

### Verificación de Datos

```sql
-- Contar registros en todas las tablas
SELECT 
    'fact_ventas' as tabla, COUNT(*) as registros FROM fact_ventas
UNION ALL
SELECT 'dim_tiempo', COUNT(*) FROM dim_tiempo
UNION ALL
SELECT 'dim_cliente', COUNT(*) FROM dim_cliente
UNION ALL
SELECT 'dim_producto', COUNT(*) FROM dim_producto;

-- Verificar integridad referencial
SELECT COUNT(*) as ventas_sin_cliente
FROM fact_ventas fv
LEFT JOIN dim_cliente dc ON fv.cliente_sk = dc.cliente_sk
WHERE dc.cliente_sk IS NULL;

-- Ver últimas ventas cargadas
SELECT * FROM fact_ventas 
ORDER BY venta_sk DESC 
LIMIT 10;

-- Estadísticas por tabla
SELECT 
    'Ventas Totales' as metrica, 
    SUM(monto_linea) as valor 
FROM fact_ventas
UNION ALL
SELECT 
    'Clientes Activos', 
    COUNT(*) 
FROM dim_cliente WHERE activo = 1;
```

### Detección de Problemas

```sql
-- Buscar valores NULL en campos importantes
SELECT COUNT(*) as nulos_cliente_nombre
FROM dim_cliente 
WHERE cliente_nombre IS NULL OR cliente_nombre = '';

-- Buscar fechas inválidas
SELECT * FROM dim_tiempo 
WHERE fecha_natural NOT LIKE '____-__-__';

-- Buscar montos negativos
SELECT * FROM fact_ventas 
WHERE monto_linea < 0 OR cantidad < 0;

-- Buscar duplicados
SELECT cliente_sk, COUNT(*) as duplicados
FROM dim_cliente
GROUP BY cliente_sk
HAVING COUNT(*) > 1;
```

---

## 💾 Mantenimiento y Respaldos

### Backup Automático

Crear archivo: `scripts/backup_diario.sh`

```bash
#!/bin/bash

DB_PATH="writable/etl_dw_system.db"
BACKUP_DIR="writable/backups"
FECHA=$(date +%Y%m%d)
BACKUP_FILE="$BACKUP_DIR/backup_$FECHA.db"

# Crear directorio si no existe
mkdir -p $BACKUP_DIR

# Hacer backup
echo "📦 Creando backup: $BACKUP_FILE"
cp $DB_PATH $BACKUP_FILE

# Comprimir backup
gzip $BACKUP_FILE

# Eliminar backups antiguos (más de 30 días)
find $BACKUP_DIR -name "backup_*.db.gz" -mtime +30 -delete

echo "✅ Backup completado"
```

### Optimización de Base de Datos

```sql
-- Analizar tablas
ANALYZE;

-- Reindexar
REINDEX;

-- Vaciar espacio no utilizado
VACUUM;

-- Ver tamaño de la base de datos
.databases
```

### Script de Optimización

```bash
#!/bin/bash

DB_PATH="writable/etl_dw_system.db"

echo "🔧 Optimizando base de datos..."

sqlite3 $DB_PATH "ANALYZE; REINDEX; VACUUM;"

echo "✅ Optimización completada"

# Mostrar tamaño
du -h $DB_PATH
```

---

## 🔧 Solución de Problemas

### Problema: Base de Datos Bloqueada

```bash
# Error: database is locked

# Solución 1: Ver procesos usando la DB
lsof writable/etl_dw_system.db

# Solución 2: Cerrar conexiones
pkill -f "sqlite3 writable/etl_dw_system.db"

# Solución 3: Reiniciar servidor PHP
pkill -f "php spark serve"
```

### Problema: Tabla Corrupta

```sql
-- Verificar integridad
PRAGMA integrity_check;

-- Si hay errores, intentar reparar
.recover
```

### Problema: Espacio en Disco

```bash
# Ver tamaño de la base de datos
du -h writable/etl_dw_system.db

# Liberar espacio
sqlite3 writable/etl_dw_system.db "VACUUM;"

# Borrar logs antiguos
rm writable/logs/*.log
```

### Problema: Consultas Lentas

```sql
-- Ver plan de ejecución
EXPLAIN QUERY PLAN 
SELECT * FROM fact_ventas WHERE cliente_sk = 123;

-- Crear índices
CREATE INDEX idx_fact_ventas_cliente ON fact_ventas(cliente_sk);
CREATE INDEX idx_fact_ventas_producto ON fact_ventas(producto_sk);
CREATE INDEX idx_fact_ventas_tiempo ON fact_ventas(tiempo_sk);

-- Ver índices existentes
.indexes fact_ventas
```

---

## 📋 Comandos Rápidos de Referencia

### Consultas Frecuentes

```bash
# Contar ventas
sqlite3 writable/etl_dw_system.db "SELECT COUNT(*) FROM fact_ventas;"

# Total vendido
sqlite3 writable/etl_dw_system.db "SELECT SUM(monto_linea) FROM fact_ventas;"

# Últimas 10 ventas
sqlite3 writable/etl_dw_system.db "SELECT * FROM fact_ventas ORDER BY venta_sk DESC LIMIT 10;"

# Clientes activos
sqlite3 writable/etl_dw_system.db "SELECT COUNT(*) FROM dim_cliente WHERE activo = 1;"

# Ventas por mes
sqlite3 writable/etl_dw_system.db "
SELECT dt.nombre_mes, SUM(fv.monto_linea) as total
FROM fact_ventas fv
JOIN dim_tiempo dt ON fv.tiempo_sk = dt.tiempo_sk
GROUP BY dt.mes, dt.nombre_mes
ORDER BY dt.mes;
"
```

### Exportar Datos

```bash
# Exportar a CSV
sqlite3 writable/etl_dw_system.db <<EOF
.headers on
.mode csv
.output export_ventas.csv
SELECT * FROM fact_ventas LIMIT 1000;
.quit
EOF

# Exportar SQL
sqlite3 writable/etl_dw_system.db .dump > backup_completo.sql

# Exportar solo una tabla
sqlite3 writable/etl_dw_system.db "SELECT * FROM dim_cliente" > clientes_export.txt
```

---

## 📞 Contacto y Soporte

Para problemas o consultas adicionales:
- **Documentación técnica:** `/docs/ARCHITECTURE.md`
- **Logs del sistema:** `writable/logs/`
- **Base de datos:** `writable/etl_dw_system.db`

---

**Última actualización:** 11 de noviembre de 2025  
**Versión:** 1.0
