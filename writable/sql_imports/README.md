# Directorio para archivos SQL de importación

Este directorio está destinado a almacenar archivos SQL que serán importados al sistema ETL.

## Uso

1. Coloca tu archivo SQL en este directorio
2. Ejecuta el comando de importación:
   ```bash
   php spark etl:import-sql --file=writable/sql_imports/tu_archivo.sql
   ```

## Formatos soportados

- **DDL**: CREATE TABLE, ALTER TABLE
- **DML**: INSERT, UPDATE, DELETE  
- **Dumps completos**: Respaldos de bases de datos
- **Datos CSV convertidos a SQL**

## Opciones disponibles

- `--preview`: Ver qué se importará sin ejecutar
- `--clean`: Limpiar tablas antes de importar
- `--table`: Especificar tabla destino

## Ejemplos

```bash
# Preview del archivo
php spark etl:import-sql --file=writable/sql_imports/ventas.sql --preview

# Importar limpiando datos existentes  
php spark etl:import-sql --file=writable/sql_imports/ventas.sql --clean

# Importar solo a una tabla específica
php spark etl:import-sql --file=writable/sql_imports/ventas.sql --table=fact_ventas
```