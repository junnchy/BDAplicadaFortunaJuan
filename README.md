# ETL Data Warehousing System - CodeIgniter 4

Sistema completo de ETL y Data Warehousing con CodeIgniter 4, implementando modelo Estrella, autenticación robusta y API REST para dashboards interactivos.

## 🎯 **Arquitectura del Sistema**

```
[BD Transaccional] → [Staging] → [DW Estrella] → [API CI4] → [Dashboard]
```

### **Flujo de Datos**
1. **Extracción**: Datos de BD transaccional → Tablas Staging (`stg_*`)
2. **Transformación**: Limpieza, normalización y enriquecimiento
3. **Carga**: Poblado de dimensiones y tabla de hechos
4. **Consumo**: API REST con filtros y agregaciones
5. **Visualización**: Dashboard con drill-down/up

## 🏗️ **Componentes Principales**

### **1. Autenticación & Autorización**
- **CodeIgniter Shield**: Sistema robusto de auth
- **Roles**: Admin, Analyst, Viewer
- **Features**: Login/logout, reset password, protección de rutas
- **Seguridad**: CSRF, rate limiting, validación de entrada

### **2. Data Warehouse - Modelo Estrella**
- **Dimensiones**: `dim_tiempo`, `dim_producto`, `dim_cliente`
- **Hechos**: `fact_ventas` (granular por línea)
- **Jerarquías**: Tiempo (año→mes→día), Producto (familia→producto)
- **Claves**: Surrogate keys (BIGINT), índices optimizados

### **3. ETL Pipeline**
- **Comandos Spark**: `etl:extract|transform|load|backfill`
- **Control**: Tablas de logging y control de ejecución
- **Características**: Idempotente, configurable, con reintentos

### **4. API REST**
- **Endpoints**: `/api/v1/ventas` con filtros completos
- **Funcionalidades**: 
  - Filtros: rango fechas, producto, cliente, familia
  - Agregaciones: sum/avg/count, YoY/WoW
  - Niveles: día/semana/mes/año
- **Seguridad**: Autenticada, rate limiting

### **5. Dashboard Interactivo**
- **Drill-down/up**: Por tiempo, producto, cliente
- **Visualización**: Consumo de API en tiempo real
- **Controles**: Filtros dinámicos, cambio de niveles

## 📋 **Plan de Fases**

| Fase | Descripción | Duración | Artefactos |
|------|-------------|----------|------------|
| **F1** | Arquitectura & Plan | 1 día | Diagramas, checklist, esquemas |
| **F2** | Auth/Autorización | 2 días | Shield, migraciones, tests auth |
| **F3** | Staging | 1 día | Migraciones `stg_*`, índices |
| **F4** | DW Estrella | 2 días | Dimensiones, hechos, vistas |
| **F5** | ETL Pipeline | 3 días | Comandos spark, control, tests |
| **F6** | API REST | 2 días | Endpoints, filtros, OpenAPI |
| **F7** | Dashboard | 2 días | Frontend, drill-down/up |
| **F8** | Operación & Calidad | 1 día | Tests, métricas, documentación |

## 🚀 **Inicio Rápido**

```bash
# 1. Configurar entorno
cp env .env
# Editar .env con configuración de BD

# 2. Instalar dependencias
composer install

# 3. Ejecutar migraciones
php spark migrate

# 4. Seed de datos iniciales
php spark db:seed AuthSeeder

# 5. Ejecutar ETL
php spark etl:extract
php spark etl:transform
php spark etl:load

# 6. Servir aplicación
php spark serve
```

## 📊 **Criterios de Aceptación (DoD)**

✅ **Autenticación**
- Login funcional (Shield)
- Roles y permisos
- Reset de contraseña
- Rutas protegidas

✅ **Base de Datos**
- `php spark migrate` levanta todas las tablas
- Modelo Estrella implementado
- Índices optimizados

✅ **ETL**
- `php spark etl:*` ejecuta sin errores
- Procesos idempotentes
- Logging y control

✅ **API**
- Endpoints REST funcionales
- Filtros y agregaciones
- Autenticación requerida

✅ **Dashboard**
- Drill-down/up implementado
- Consumo de API
- Filtros interactivos

✅ **Calidad**
- Tests PHPUnit verdes
- Documentación completa
- OpenAPI especificado

## 🛠️ **Stack Tecnológico**

- **Backend**: CodeIgniter 4, PHP 8.x
- **Base de Datos**: MariaDB/MySQL (utf8mb4, InnoDB)
- **Autenticación**: CodeIgniter Shield
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla/Alpine.js)
- **Testing**: PHPUnit
- **API**: REST JSON, OpenAPI 3.0

## 📁 **Estructura del Proyecto**

```
app/
├── Commands/           # Comandos ETL (spark)
├── Controllers/        # API y Dashboard controllers
├── Models/            # Modelos para DW y Auth
├── Database/
│   ├── Migrations/    # Todas las migraciones
│   └── Seeds/         # Seeds de datos
├── Filters/           # Middleware de autenticación
└── Views/             # Dashboard views
```

## 🔧 **Configuración**

Ver archivos de configuración en `/app/Config/` para personalizar:
- Database.php: Conexiones de BD
- Routes.php: Rutas de API y dashboard
- Filters.php: Middleware de seguridad

---

**Autor**: Juan Fortuna  
**Curso**: Big Data Aplicado - UAI  
**Fecha**: Octubre 2025
