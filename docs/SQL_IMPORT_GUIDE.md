# Sistema de Importación SQL - Guía de Uso

## Descripción
El sistema incluye un comando CLI para importar datos desde archivos SQL directamente al Data Warehouse del sistema ETL.

## Comando Principal
```bash
php spark etl:import-sql <archivo_sql> [opciones]
```

## Opciones Disponibles
- `--preview` : Muestra una vista previa sin ejecutar la importación
- `--backup` : Crea un respaldo antes de la importación (futuro)
- `--force` : Omite confirmaciones (futuro)

## Ubicación de Archivos
Los archivos SQL deben colocarse en:
```
writable/sql_imports/
```

## Estructura de Tablas Soportadas

### dim_tiempo
Campos obligatorios:
- `tiempo_sk` (INTEGER, PK)
- `fecha_natural` (DATE)
- `año` (SMALLINT)
- `trimestre` (TINYINT)
- `mes` (TINYINT)
- `semana` (TINYINT)
- `dia` (TINYINT)
- `dia_semana` (TINYINT)
- `nombre_dia` (VARCHAR)
- `nombre_mes` (VARCHAR)
- `trimestre_nombre` (VARCHAR)
- `es_fin_semana` (INT, default 0)
- `es_feriado` (INT, default 0)
- `semana_año` (TINYINT)
- `dia_año` (SMALLINT)
- `fecha_primera_semana` (DATE)
- `fecha_ultimo_mes` (DATE)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

### dim_producto
Campos obligatorios:
- `producto_sk` (INTEGER, PK)
- `producto_id` (VARCHAR)
- `producto_nombre` (VARCHAR)
- `familia_id` (VARCHAR) - REQUERIDO
- `familia_nombre` (VARCHAR)
- `categoria` (VARCHAR)
- `precio_lista` (DECIMAL)
- `costo_estandar` (DECIMAL)
- `margen_bruto` (DECIMAL)
- `activo` (INT, default 1)
- `unidad_medida` (VARCHAR, default 'unidad')
- `scd_version` (INT, default 1)
- `fecha_efectiva_desde` (TIMESTAMP)
- `es_actual` (INT, default 1)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

### fact_ventas
Campos obligatorios:
- `tiempo_sk` (BIGINT, FK)
- `producto_sk` (BIGINT, FK)
- `cliente_sk` (BIGINT, FK)
- `orden_id` (VARCHAR)
- `linea_numero` (INT)
- `cantidad` (DECIMAL)
- `precio_unitario` (DECIMAL)
- `monto_linea` (DECIMAL)
- `costo_unitario` (DECIMAL)
- `costo_total` (DECIMAL)
- `margen_monto` (DECIMAL)
- `margen_porcentaje` (DECIMAL)
- `monto_neto` (DECIMAL)
- `created_at` (TIMESTAMP)

## Ejemplo de Uso

### 1. Vista Previa
```bash
php spark etl:import-sql writable/sql_imports/mis_datos.sql --preview
```

### 2. Importación Completa
```bash
php spark etl:import-sql writable/sql_imports/mis_datos.sql
```

## Ejemplo de Archivo SQL

```sql
-- Insertar dimensión tiempo
INSERT INTO dim_tiempo (
    tiempo_sk, fecha_natural, año, trimestre, mes, semana, dia, dia_semana,
    nombre_dia, nombre_mes, trimestre_nombre, es_fin_semana, es_feriado,
    semana_año, dia_año, fecha_primera_semana, fecha_ultimo_mes, created_at, updated_at
) VALUES 
(20240401, '2024-04-01', 2024, 2, 4, 14, 1, 2, 'Lunes', 'Abril', 'Q2', 0, 0, 14, 92, '2024-04-01', '2024-04-30', datetime('now'), datetime('now'));

-- Insertar producto
INSERT INTO dim_producto (
    producto_sk, producto_id, producto_nombre, familia_id, familia_nombre, categoria,
    precio_lista, costo_estandar, margen_bruto, activo, unidad_medida, 
    scd_version, fecha_efectiva_desde, es_actual, created_at, updated_at
) VALUES 
(2001, 'PROD004', 'Monitor 4K', 'FAM003', 'Monitores', 'Electrónicos',
 299.99, 220.00, 79.99, 1, 'unidad', 1, datetime('now'), 1, datetime('now'), datetime('now'));

-- Insertar venta
INSERT INTO fact_ventas (
    tiempo_sk, producto_sk, cliente_sk, orden_id, linea_numero,
    cantidad, precio_unitario, monto_linea, costo_unitario, costo_total,
    margen_monto, margen_porcentaje, monto_neto, created_at
) VALUES 
(20240401, 2001, 1, 'ORD004', 1, 1, 299.99, 299.99, 220.00, 220.00, 79.99, 26.67, 299.99, datetime('now'));
```

## Formatos Soportados
- **SQL**: Archivos .sql con statements INSERT
- **CSV**: Archivos .csv (futuro)
- **JSON**: Archivos .json (futuro)

## Validaciones
El sistema valida:
- ✅ Existencia del archivo
- ✅ Sintaxis SQL básica
- ✅ Tablas destino válidas
- ✅ Estructura de columnas
- ✅ Restricciones NOT NULL
- ✅ Claves foráneas

## Manejo de Errores
- Los errores se reportan por statement individual
- La importación continúa aunque fallen algunos statements
- Se muestra un resumen final con estadísticas

## Archivos de Ejemplo
En `writable/sql_imports/` encontrarás:
- `ventas_final.sql` - Ejemplo completo con datos de prueba
- `ejemplo_ventas.sql` - Versión inicial (puede tener errores)

## Integración con Dashboard
Los datos importados aparecerán automáticamente en:
- Dashboard principal (`/dashboard`)
- Reportes de ventas
- Gráficos de tendencias
- Drill-down por períodos

## Notas Importantes
1. **Claves SK**: Usar formato `YYYYMMDD` para `tiempo_sk`
2. **Familia ID**: Campo obligatorio en `dim_producto`
3. **Timestamps**: Usar `datetime('now')` para fechas actuales
4. **Decimales**: Usar punto (.) como separador decimal
5. **Strings**: Encerrar en comillas simples (')

## Solución de Problemas

### Error: "table X has no column named Y"
- Verificar estructura de tabla con: `PRAGMA table_info(tabla)`
- Corregir nombres de columnas en el SQL

### Error: "NOT NULL constraint failed"
- Agregar valores para todos los campos obligatorios
- Revisar la documentación de estructura arriba

### Error: "FOREIGN KEY constraint failed"
- Asegurar que las claves foráneas existan antes de usarlas
- Importar dimensiones antes que hechos

## Soporte
Para más información consultar:
- Documentación técnica en `docs/ARCHITECTURE.md`
- Logs del sistema en `writable/logs/`
- Base de datos: `writable/etl_dw_system.db`