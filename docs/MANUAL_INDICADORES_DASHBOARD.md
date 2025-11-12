# Manual de Indicadores del Dashboard - Sistema de Business Intelligence

## Tabla de Contenidos
1. [Dashboard Principal](#dashboard-principal)
2. [Análisis de Ventas](#análisis-de-ventas)
3. [Análisis Temporal](#análisis-temporal)
4. [Análisis de Productos](#análisis-de-productos)
5. [Análisis de Clientes](#análisis-de-clientes)
6. [Interpretación de Semáforos](#interpretación-de-semáforos)
7. [Guía de Toma de Decisiones](#guía-de-toma-de-decisiones)

---

## Dashboard Principal

### 1. Total Ventas
- **Descripción**: Suma total de ventas en el período seleccionado
- **Fórmula**: `SUM(total_venta)`
- **Unidad**: Valor monetario ($)
- **Interpretación**:
  - Refleja el volumen total de negocio generado
  - Permite comparar períodos (año actual vs anterior)
  - Identifica tendencias de crecimiento o decrecimiento
- **Valor para Decisiones**:
  - **Alto**: Indica salud financiera positiva
  - **Bajo**: Requiere acciones de impulso comercial
  - **Tendencia bajista**: Señal de alerta para revisar estrategias

### 2. Transacciones
- **Descripción**: Número total de operaciones realizadas
- **Fórmula**: `COUNT(DISTINCT venta_id)`
- **Unidad**: Cantidad numérica
- **Interpretación**:
  - Mide la actividad comercial del negocio
  - Volumen de operaciones independiente del monto
  - Indicador de penetración en el mercado
- **Valor para Decisiones**:
  - **Alto**: Buena frecuencia de compra, base de clientes activa
  - **Bajo**: Necesidad de activar campañas de captación
  - **Comparado con ventas**: Identifica si el problema es volumen o ticket promedio

### 3. Ticket Promedio
- **Descripción**: Valor promedio por transacción
- **Fórmula**: `Total Ventas / Total Transacciones`
- **Unidad**: Valor monetario ($)
- **Interpretación**:
  - Indica el valor medio de cada operación
  - Refleja el poder adquisitivo de los clientes
  - Mide efectividad de estrategias de upselling
- **Valor para Decisiones**:
  - **Alto**: Clientes compran productos de mayor valor
  - **Bajo**: Oportunidad para estrategias de cross-selling
  - **Tendencia creciente**: Éxito en estrategias de valor agregado

### 4. Margen Total
- **Descripción**: Diferencia entre ventas y costos
- **Fórmula**: `SUM(margen_bruto)`
- **Unidad**: Valor monetario ($)
- **Interpretación**:
  - Rentabilidad absoluta del negocio
  - Capacidad de generar beneficios
  - No incluye gastos operativos
- **Valor para Decisiones**:
  - **Alto**: Buena salud financiera, productos rentables
  - **Bajo**: Revisar estructura de costos o precios
  - **Negativo**: Situación crítica que requiere acción inmediata

### 5. Gráficos Dinámicos

#### Tendencia de Ventas
- **Descripción**: Evolución temporal de las ventas
- **Visualización**: Línea temporal
- **Interpretación**:
  - Patrones estacionales
  - Tendencias de crecimiento/decrecimiento
  - Impacto de acciones comerciales
- **Valor para Decisiones**:
  - Planificación de inventario
  - Diseño de campañas estacionales
  - Proyecciones de crecimiento

#### Top Productos
- **Descripción**: Productos con mayores ventas
- **Visualización**: Barras horizontales
- **Interpretación**:
  - Identificación de productos estrella
  - Concentración de ventas (ley de Pareto)
  - Productos a potenciar
- **Valor para Decisiones**:
  - Gestión de stock prioritario
  - Foco de marketing
  - Negociación con proveedores

---

## Análisis de Ventas

### Indicadores de Nivel (Year/Quarter/Month)

#### 1. Total Ventas del Período
- **Descripción**: Ventas acumuladas en el nivel jerárquico seleccionado
- **Unidad**: Valor monetario ($)
- **Sistema de Semáforo**: No aplica (métrica absoluta)
- **Valor para Decisiones**:
  - Comparación entre períodos similares
  - Identificación de mejores/peores períodos
  - Base para presupuestos futuros

#### 2. Total Transacciones
- **Descripción**: Operaciones realizadas en el período
- **Unidad**: Cantidad numérica
- **Sistema de Semáforo**: No aplica
- **Valor para Decisiones**:
  - Mide actividad del negocio
  - Comparación con períodos anteriores
  - Identifica períodos de alta/baja actividad

#### 3. Ticket Promedio del Período
- **Descripción**: Valor medio por operación
- **Fórmula**: `Ventas del Período / Transacciones del Período`
- **Sistema de Semáforo**: No aplica
- **Valor para Decisiones**:
  - Evalúa calidad de las ventas
  - Detecta cambios en comportamiento de compra
  - Mide impacto de promociones

#### 4. Margen del Período
- **Descripción**: Rentabilidad acumulada
- **Unidad**: Valor monetario ($)
- **Sistema de Semáforo**: No aplica
- **Valor para Decisiones**:
  - Evaluación de rentabilidad temporal
  - Comparación de márgenes entre períodos
  - Identificación de períodos más/menos rentables

### Funcionalidad Drill-Down

#### Navegación Jerárquica
- **Niveles**: Año → Trimestre → Mes
- **Propósito**: Análisis granular de rendimiento
- **Interpretación**:
  - Permite identificar períodos específicos de éxito/fracaso
  - Detecta patrones estacionales detallados
  - Facilita análisis causa-efecto
- **Valor para Decisiones**:
  - Diagnóstico preciso de problemas
  - Replicación de estrategias exitosas
  - Planificación táctica por período

---

## Análisis Temporal

### KPIs Principales

#### 1. Ventas Totales
- **Descripción**: Suma de ventas en período analizado
- **Sistema de Semáforo**: No aplica (métrica base)
- **Valor para Decisiones**: Punto de referencia para otros indicadores

#### 2. Promedio Diario
- **Descripción**: Ventas promedio por día
- **Fórmula**: `Total Ventas / Días del Período`
- **Unidad**: Valor monetario ($)
- **Interpretación**:
  - Normaliza ventas para comparación
  - Elimina efecto de cantidad de días
  - Permite comparar períodos desiguales
- **Valor para Decisiones**:
  - Benchmark de rendimiento diario
  - Identificación de metas diarias realistas
  - Base para incentivos de ventas

#### 3. Mejor Día
- **Descripción**: Día con mayores ventas registradas
- **Unidad**: Fecha + Valor monetario
- **Interpretación**:
  - Pico máximo de rendimiento
  - Puede estar asociado a eventos especiales
  - Referencia de potencial máximo
- **Valor para Decisiones**:
  - Identificación de factores de éxito
  - Replicación de condiciones exitosas
  - Establecimiento de metas ambiciosas

#### 4. Tendencia
- **Descripción**: Dirección del comportamiento de ventas
- **Valores**: Creciente / Decreciente / Estable
- **Sistema de Semáforo**:
  - 🟢 **Verde (Creciente)**: Tendencia positiva superior al 5%
  - 🟡 **Amarillo (Estable)**: Variación entre -5% y +5%
  - 🔴 **Rojo (Decreciente)**: Tendencia negativa inferior al -5%
- **Interpretación**:
  - Dirección general del negocio
  - Momentum comercial
  - Efectividad de estrategias actuales
- **Valor para Decisiones**:
  - 🟢 **Verde**: Mantener estrategias actuales, considerar expansión
  - 🟡 **Amarillo**: Evaluar nuevas iniciativas, no hay urgencia
  - 🔴 **Rojo**: Acción inmediata requerida, revisar estrategias

### Análisis por Día de Semana

#### Tabla Detallada
**Columnas:**

1. **Día de la Semana**
   - Lunes a Domingo
   - Permite identificar patrones semanales

2. **Total Ventas**
   - Suma de ventas por día de la semana
   - Base para comparación entre días

3. **Transacciones**
   - Número de operaciones por día
   - Mide actividad independiente del monto

4. **Ticket Promedio**
   - Valor medio por transacción
   - `Ventas / Transacciones`

5. **Performance**
   - Indicador comparativo de rendimiento
   - **Fórmula**: `(Ventas del Día / Ventas del Mejor Día) × 100`
   - **Sistema de Semáforo**:
     - 🟢 **Verde (>80%)**: Día de alto rendimiento
     - 🟡 **Amarillo (60-80%)**: Rendimiento medio
     - 🔴 **Rojo (<60%)**: Día de bajo rendimiento
   - **Interpretación**:
     - Compara cada día contra el mejor día
     - Normaliza el rendimiento en escala 0-100
     - Facilita identificación visual de patrones
   - **Valor para Decisiones**:
     - 🟢 **Verde**: Días óptimos para promociones premium
     - 🟡 **Amarillo**: Días para mantener operación estándar
     - 🔴 **Rojo**: Días para promociones agresivas o reducir costos operativos

### Gráficos Temporales

#### 1. Tendencias Temporales (Línea)
- **Descripción**: Evolución de ventas en el tiempo
- **Interpretación**:
  - Visualiza ciclos y patrones
  - Identifica outliers (picos y valles)
  - Muestra efectividad de acciones en el tiempo
- **Valor para Decisiones**:
  - Planificación de recursos
  - Timing de campañas
  - Proyecciones basadas en histórico

#### 2. Estacionalidad (Radar)
- **Descripción**: Patrón de ventas por mes
- **Interpretación**:
  - Identifica meses fuertes y débiles
  - Visualiza estacionalidad del negocio
  - Compara rendimiento relativo mensual
- **Valor para Decisiones**:
  - Gestión de inventario estacional
  - Planificación de campañas por temporada
  - Preparación financiera para meses bajos

#### 3. Por Día de Semana (Barras)
- **Descripción**: Comparación de rendimiento semanal
- **Interpretación**:
  - Identifica días más/menos productivos
  - Patrones de comportamiento del cliente
  - Eficiencia operativa por día
- **Valor para Decisiones**:
  - Optimización de turnos de personal
  - Programación de mantenimiento
  - Diseño de promociones específicas por día

---

## Análisis de Productos

### KPIs de Productos

#### 1. Total Productos Activos
- **Descripción**: Cantidad de productos con ventas
- **Unidad**: Cantidad numérica
- **Interpretación**:
  - Diversidad del catálogo activo
  - Productos con movimiento real
  - Base para análisis de concentración
- **Valor para Decisiones**:
  - Evaluación de amplitud de oferta
  - Identificación de productos sin rotación
  - Optimización de catálogo

#### 2. Ventas Totales
- **Descripción**: Suma de ventas de todos los productos
- **Sistema de Semáforo**: No aplica
- **Valor para Decisiones**: Métrica agregada de rendimiento

#### 3. Margen Promedio
- **Descripción**: Rentabilidad media por producto
- **Fórmula**: `SUM(margen) / COUNT(productos)`
- **Unidad**: Valor monetario ($)
- **Sistema de Semáforo**:
  - 🟢 **Verde (>30%)**: Alta rentabilidad
  - 🟡 **Amarillo (15-30%)**: Rentabilidad aceptable
  - 🔴 **Rojo (<15%)**: Rentabilidad baja o negativa
- **Interpretación**:
  - Salud financiera del mix de productos
  - Equilibrio entre volumen y rentabilidad
  - Efectividad de la estrategia de precios
- **Valor para Decisiones**:
  - 🟢 **Verde**: Mantener estrategia de precios
  - 🟡 **Amarillo**: Revisar productos de baja rentabilidad
  - 🔴 **Rojo**: Reestructurar precios o eliminar productos

#### 4. Unidades Vendidas
- **Descripción**: Cantidad total de productos vendidos
- **Unidad**: Unidades
- **Interpretación**:
  - Volumen físico de movimiento
  - Complementa el análisis de valor monetario
  - Indicador de rotación
- **Valor para Decisiones**:
  - Gestión de logística y almacenamiento
  - Planificación de compras
  - Negociación por volumen

### Tabla Detallada de Productos

**Columnas:**

1. **Ranking**
   - Posición según métrica seleccionada
   - Facilita identificación rápida

2. **Producto**
   - Nombre o código del producto
   - Identificador único

3. **Ventas**
   - Total de ventas del producto
   - **Sistema de Semáforo** (relativo al top):
     - 🟢 **Verde**: Top 20% (productos estrella)
     - 🟡 **Amarillo**: 20-60% (productos estándar)
     - 🔴 **Rojo**: Bottom 40% (productos de baja rotación)

4. **Unidades**
   - Cantidad vendida
   - Permite identificar productos de alto volumen/bajo precio

5. **Margen**
   - Rentabilidad del producto
   - **Sistema de Semáforo**:
     - 🟢 **Verde**: Margen >30%
     - 🟡 **Amarillo**: Margen 15-30%
     - 🔴 **Rojo**: Margen <15%

6. **Margen %**
   - Porcentaje de rentabilidad
   - `(Margen / Ventas) × 100`
   - **Interpretación crítica**: Producto puede tener alto margen absoluto pero bajo %

7. **Transacciones**
   - Frecuencia de compra del producto
   - Indica popularidad y preferencia

8. **Performance**
   - Rendimiento comparativo del producto
   - **Fórmula**: `(Ventas del Producto / Ventas del Top 1) × 100`
   - **Sistema de Semáforo**:
     - 🟢 **Verde (>70%)**: Producto de alto rendimiento
     - 🟡 **Amarillo (30-70%)**: Rendimiento medio
     - 🔴 **Rojo (<30%)**: Bajo rendimiento relativo
   - **Valor para Decisiones**:
     - Identificación de productos a potenciar
     - Decisiones de descatalogación
     - Priorización en exhibición

### Análisis Comparativo (Scatter Plot)

#### Gráfico de Dispersión: Ventas vs Margen
- **Eje X**: Total de Ventas
- **Eje Y**: Margen Total
- **Interpretación por Cuadrante**:
  - **Superior Derecho** 🟢: Alto Margen + Altas Ventas (Productos ESTRELLA)
  - **Superior Izquierdo** 🟡: Alto Margen + Bajas Ventas (Productos NICHO - oportunidad)
  - **Inferior Derecho** 🟡: Bajo Margen + Altas Ventas (Productos VOLUMEN)
  - **Inferior Izquierdo** 🔴: Bajo Margen + Bajas Ventas (Candidatos a ELIMINAR)
- **Valor para Decisiones**:
  - **Estrella**: Mantener stock, promocionar, proteger precio
  - **Nicho**: Aumentar visibilidad, evaluar escalar
  - **Volumen**: Optimizar costos, evaluar subir precio
  - **Eliminar**: Descatalogar o liquidar

---

## Análisis de Clientes

### KPIs de Clientes

#### 1. Total Clientes Activos
- **Descripción**: Clientes con compras en el período
- **Unidad**: Cantidad numérica
- **Interpretación**:
  - Base de clientes real
  - Alcance del negocio
  - Cartera activa
- **Valor para Decisiones**:
  - Tamaño del mercado capturado
  - Base para cálculo de ratios (LTV, frecuencia)
  - Meta de retención

#### 2. Total Ventas
- **Descripción**: Suma de ventas a todos los clientes
- **Sistema de Semáforo**: No aplica
- **Valor para Decisiones**: Métrica agregada

#### 3. Ticket Promedio por Cliente
- **Descripción**: Valor medio de compra
- **Fórmula**: `Total Ventas / Total Clientes`
- **Unidad**: Valor monetario ($)
- **Sistema de Semáforo**:
  - 🟢 **Verde**: Por encima del promedio histórico +10%
  - 🟡 **Amarillo**: Dentro del rango ±10%
  - 🔴 **Rojo**: Por debajo del promedio -10%
- **Interpretación**:
  - Valor promedio que cada cliente aporta
  - Refleja capacidad de gasto
  - Indicador de estrategias de upselling
- **Valor para Decisiones**:
  - 🟢 **Verde**: Estrategias efectivas, mantener
  - 🟡 **Amarillo**: Explorar mejoras incrementales
  - 🔴 **Rojo**: Revisar estrategias de venta, segmentar mejor

#### 4. Margen Promedio por Cliente
- **Descripción**: Rentabilidad media por cliente
- **Fórmula**: `Total Margen / Total Clientes`
- **Unidad**: Valor monetario ($)
- **Sistema de Semáforo**:
  - 🟢 **Verde**: Margen >25% del ticket promedio
  - 🟡 **Amarillo**: Margen 15-25%
  - 🔴 **Rojo**: Margen <15%
- **Interpretación**:
  - Rentabilidad por relación cliente
  - Calidad financiera de la base de clientes
  - Eficiencia de la mezcla de productos vendidos
- **Valor para Decisiones**:
  - Identificación de clientes rentables
  - Segmentación por valor
  - Estrategias de retención diferenciadas

#### 5. Frecuencia Promedio
- **Descripción**: Número medio de compras por cliente
- **Fórmula**: `Total Transacciones / Total Clientes`
- **Unidad**: Operaciones por cliente
- **Sistema de Semáforo**:
  - 🟢 **Verde**: Frecuencia >5 compras/año
  - 🟡 **Amarillo**: 2-5 compras/año
  - 🔴 **Rojo**: <2 compras/año
- **Interpretación**:
  - Lealtad y engagement del cliente
  - Recurrencia de compra
  - Efectividad de retención
- **Valor para Decisiones**:
  - 🟢 **Verde**: Clientes leales, programas de fidelización
  - 🟡 **Amarillo**: Oportunidad de aumentar frecuencia
  - 🔴 **Rojo**: Riesgo de abandono, activar campañas

### Análisis de Frecuencia de Operaciones

#### Segmentación por Frecuencia
- **1-2 operaciones**: Clientes ocasionales
- **3-5 operaciones**: Clientes regulares
- **6-10 operaciones**: Clientes frecuentes
- **11-20 operaciones**: Clientes muy frecuentes
- **20+ operaciones**: Clientes VIP

**Sistema de Semáforo por Segmento**:
- 🟢 **Verde (6+ operaciones)**: Clientes de alta frecuencia
  - **Acción**: Programas de lealtad, acceso exclusivo
- 🟡 **Amarillo (3-5 operaciones)**: Clientes con potencial
  - **Acción**: Incentivos para aumentar frecuencia
- 🔴 **Rojo (1-2 operaciones)**: Clientes en riesgo
  - **Acción**: Campañas de activación, descuentos especiales

**Interpretación del Gráfico**:
- Distribución de la base de clientes
- Identificación de concentración
- Potencial de crecimiento por segmento

**Valor para Decisiones**:
- Diseño de programas de fidelización
- Segmentación de comunicaciones
- Asignación de recursos de marketing

### Clientes Caídos - Oportunidades de Reactivación

#### Criterios de Identificación
- **Definición**: Clientes sin compras en los últimos 90 días
- **Filtro**: Historial previo de al menos 2 compras
- **Priorización**: Por valor total histórico

**Tabla de Clientes Caídos:**

1. **Cliente**
   - Nombre o identificador
   - Permite contacto directo

2. **Última Compra**
   - Fecha de última transacción
   - **Sistema de Semáforo** (días sin comprar):
     - 🟡 **Amarillo (90-180 días)**: Recuperación viable
     - 🟠 **Naranja (180-365 días)**: Recuperación difícil
     - 🔴 **Rojo (>365 días)**: Recuperación muy difícil

3. **Días Sin Comprar**
   - Tiempo transcurrido desde última compra
   - Urgencia de acción

4. **Compras Históricas**
   - Total de operaciones realizadas
   - Indica valor histórico del cliente

5. **Total Histórico**
   - Ventas acumuladas del cliente
   - **Sistema de Semáforo**:
     - 🟢 **Verde (>$50,000)**: Cliente de alto valor
     - 🟡 **Amarillo ($10,000-$50,000)**: Cliente medio
     - 🔴 **Rojo (<$10,000)**: Cliente de bajo valor

6. **Margen Histórico**
   - Rentabilidad generada
   - Prioriza clientes rentables

7. **Ticket Promedio**
   - Valor medio de sus compras
   - Potencial de recuperación

8. **Performance**
   - Indicador de calidad del cliente
   - **Fórmula**: `(Total Histórico / Total del Mejor Cliente) × 100`
   - **Sistema de Semáforo**:
     - 🟢 **Verde (>60%)**: Cliente premium a recuperar con alta prioridad
     - 🟡 **Amarillo (30-60%)**: Cliente valioso, prioridad media
     - 🔴 **Rojo (<30%)**: Cliente de bajo valor, prioridad baja

**Estrategias de Reactivación por Performance**:
- 🟢 **Verde (Alta)**: Contacto personal, ofertas VIP, descuentos exclusivos
- 🟡 **Amarilla (Media)**: Email marketing personalizado, promociones dirigidas
- 🔴 **Roja (Baja)**: Campañas masivas, descuentos genéricos

### Tabla Top Clientes Detallado

**Columnas:**

1. **Ranking**
   - Posición según métrica seleccionada
   - Identifica clientes más valiosos

2. **Cliente**
   - Nombre o código
   - Identificación única

3. **Total Ventas**
   - Suma de compras del cliente
   - **Sistema de Semáforo**:
     - 🟢 **Verde**: Top 20% de clientes
     - 🟡 **Amarillo**: 20-70% medio
     - 🔴 **Rojo**: Bottom 30%

4. **Margen**
   - Rentabilidad generada
   - Diferencia clave: ventas ≠ rentabilidad

5. **Margen %**
   - Porcentaje de rentabilidad
   - `(Margen / Ventas) × 100`
   - **Sistema de Semáforo**:
     - 🟢 **Verde (>30%)**: Cliente muy rentable
     - 🟡 **Amarillo (20-30%)**: Rentabilidad aceptable
     - 🔴 **Rojo (<20%)**: Rentabilidad baja

6. **Órdenes Únicas**
   - Número de compras diferentes
   - Mide frecuencia y lealtad

7. **Ticket Promedio**
   - Valor medio por compra
   - `Total Ventas / Órdenes Únicas`

8. **Última Compra**
   - Fecha de última transacción
   - **Sistema de Semáforo** (recencia):
     - 🟢 **Verde (<30 días)**: Cliente activo
     - 🟡 **Amarillo (30-90 días)**: Cliente regular
     - 🔴 **Rojo (>90 días)**: Cliente en riesgo

9. **Performance**
   - Rendimiento comparativo
   - **Fórmula**: `(Ventas del Cliente / Ventas del Top 1) × 100`
   - **Sistema de Semáforo**:
     - 🟢 **Verde (>70%)**: Cliente estrella
     - 🟡 **Amarillo (40-70%)**: Cliente importante
     - 🔴 **Rojo (<40%)**: Cliente estándar

---

## Interpretación de Semáforos

### Sistema General de Colores

#### 🟢 Verde - EXCELENTE
- **Significado**: Rendimiento óptimo o superior a objetivos
- **Acción**: Mantener estrategias actuales, documentar mejores prácticas
- **Prioridad**: Baja intervención, alta observación
- **Ejemplos**:
  - Margen >30%
  - Performance >80%
  - Tendencia creciente >5%
  - Frecuencia >5 compras/año

#### 🟡 Amarillo - ACEPTABLE
- **Significado**: Rendimiento dentro de rangos normales
- **Acción**: Monitorear, buscar mejoras incrementales
- **Prioridad**: Media intervención, consideración de optimización
- **Ejemplos**:
  - Margen 15-30%
  - Performance 60-80%
  - Tendencia estable ±5%
  - Frecuencia 2-5 compras/año

#### 🔴 Rojo - ALERTA
- **Significado**: Rendimiento por debajo de objetivos mínimos
- **Acción**: Intervención inmediata requerida
- **Prioridad**: Alta intervención, análisis de causas
- **Ejemplos**:
  - Margen <15%
  - Performance <60%
  - Tendencia decreciente <-5%
  - Frecuencia <2 compras/año

#### 🟠 Naranja - ADVERTENCIA (cuando aplica)
- **Significado**: Situación intermedia entre amarillo y rojo
- **Acción**: Planificar acciones correctivas
- **Prioridad**: Media-alta intervención
- **Ejemplos**:
  - Clientes caídos 180-365 días
  - Productos con margen 10-15%

### Interpretación Contextual

#### Combinaciones de Semáforos

**Cliente: Verde en Ventas + Rojo en Margen**
- **Interpretación**: Cliente de alto volumen pero baja rentabilidad
- **Decisión**: Revisar estructura de descuentos, intentar mejorar mix de productos

**Cliente: Rojo en Ventas + Verde en Margen**
- **Interpretación**: Cliente pequeño pero muy rentable
- **Decisión**: Explorar crecimiento, productos premium, relación de calidad

**Producto: Verde en Ventas + Amarillo en Margen**
- **Interpretación**: Producto popular con rentabilidad mejorable
- **Decisión**: Optimizar costos o considerar ajuste de precio moderado

**Producto: Amarillo en Ventas + Verde en Margen**
- **Interpretación**: Producto nicho rentable
- **Decisión**: Evaluar estrategias para aumentar volumen

---

## Guía de Toma de Decisiones

### Matriz de Decisiones por Indicador

#### Para VENTAS

| Semáforo | Situación | Decisión Inmediata | Decisión Estratégica |
|----------|-----------|-------------------|---------------------|
| 🟢 Verde | Ventas >objetivo | Mantener momentum | Evaluar expansión, nuevos mercados |
| 🟡 Amarillo | Ventas estables | Probar nuevas tácticas | Revisar estrategia de mediano plazo |
| 🔴 Rojo | Ventas bajo objetivo | Promociones urgentes | Reestructurar enfoque comercial |

#### Para MARGEN

| Semáforo | Situación | Decisión Inmediata | Decisión Estratégica |
|----------|-----------|-------------------|---------------------|
| 🟢 Verde | Margen >30% | Proteger precios | Invertir en valor agregado |
| 🟡 Amarillo | Margen 15-30% | Revisar costos | Optimizar eficiencia operativa |
| 🔴 Rojo | Margen <15% | Analizar estructura | Replantear modelo de negocio |

#### Para CLIENTES

| Segmento | Características | Estrategia | Inversión |
|----------|----------------|-----------|-----------|
| VIP (🟢) | Alta frecuencia + Alto valor | Programas exclusivos, atención personalizada | Alta |
| Potencial (🟡) | Frecuencia media + Valor medio | Incentivos para aumentar frecuencia | Media |
| En Riesgo (🔴) | Baja frecuencia o caídos | Campañas de reactivación | Baja-Media |
| Descarte | Bajo valor + Inactivo >2 años | Sin inversión activa | Nula |

#### Para PRODUCTOS

| Cuadrante | Ventas | Margen | Estrategia | Acción |
|-----------|--------|--------|-----------|--------|
| Estrella (🟢) | Alto | Alto | Mantener y proteger | Stock prioritario, promoción |
| Volumen (🟡) | Alto | Bajo | Optimizar rentabilidad | Reducir costos, subir precio |
| Nicho (🟡) | Bajo | Alto | Aumentar visibilidad | Marketing focalizado |
| Eliminar (🔴) | Bajo | Bajo | Descatalogar | Liquidación o eliminación |

### Flujo de Análisis Recomendado

#### 1. Análisis Semanal (Operativo)
- ✅ Revisar Dashboard Principal
- ✅ Verificar semáforos rojos en todos los módulos
- ✅ Identificar clientes caídos de alto valor
- ✅ Monitorear stock de productos estrella
- 🎯 **Objetivo**: Corrección táctica inmediata

#### 2. Análisis Mensual (Táctico)
- ✅ Revisar tendencias temporales
- ✅ Analizar performance de productos
- ✅ Evaluar segmentación de clientes
- ✅ Comparar vs. mes anterior
- 🎯 **Objetivo**: Ajustes de mediano plazo

#### 3. Análisis Trimestral (Estratégico)
- ✅ Análisis profundo de drill-down de ventas
- ✅ Evaluación de estacionalidad
- ✅ Revisión de cartera de productos
- ✅ Análisis de segmentación y LTV
- 🎯 **Objetivo**: Decisiones estratégicas

### Alertas Críticas que Requieren Acción Inmediata

#### 🚨 Nivel CRÍTICO (Acción Hoy)
1. **Tendencia decreciente sostenida por 3+ semanas** 🔴
   - Reunión urgente del equipo comercial
   - Análisis de causas (competencia, estacionalidad, calidad)
   - Implementación de plan de contingencia

2. **Margen negativo en cualquier categoría** 🔴
   - Suspender ventas del producto/cliente si es necesario
   - Revisión inmediata de estructura de costos
   - Ajuste de precios o eliminación

3. **Cliente VIP (top 10%) con >60 días sin comprar** 🔴
   - Contacto personal inmediato
   - Oferta personalizada
   - Investigación de motivos

#### ⚠️ Nivel ALTO (Acción Esta Semana)
1. **Performance <60% en producto top 20** 🟡→🔴
   - Revisar competencia
   - Evaluar campaña de impulso
   - Análisis de feedback de clientes

2. **Más de 20% de clientes caídos** 🟡
   - Campaña masiva de reactivación
   - Revisar propuesta de valor
   - Benchmark con competencia

3. **Ticket promedio cayendo 2+ semanas consecutivas** 🟡
   - Revisar estrategias de cross-selling
   - Capacitación al equipo de ventas
   - Promociones de bundling

#### ℹ️ Nivel MEDIO (Acción Este Mes)
1. **Concentración >70% en top 10 productos** 🟡
   - Diversificar oferta
   - Desarrollar nuevos productos
   - Reducir dependencia

2. **Frecuencia promedio <3 compras/año** 🟡
   - Implementar programa de fidelización
   - Aumentar touchpoints con clientes
   - Mejorar experiencia de compra

### Uso Combinado de Indicadores

#### Análisis de Rentabilidad Total
```
Cliente Ideal = 🟢 Ventas + 🟢 Margen + 🟢 Frecuencia + 🟢 Recencia
Cliente a Desarrollar = 🟡 Ventas + 🟢 Margen + 🟡 Frecuencia
Cliente a Revisar = 🟢 Ventas + 🔴 Margen
Cliente a Eliminar = 🔴 Ventas + 🔴 Margen + 🔴 Recencia
```

#### Análisis de Producto Estratégico
```
Producto Estrella = 🟢 Ventas + 🟢 Margen + 🟢 Performance
Producto a Potenciar = 🟡 Ventas + 🟢 Margen
Producto Problemático = 🟢 Ventas + 🔴 Margen
Producto a Eliminar = 🔴 Ventas + 🔴 Margen + 🔴 Performance
```

---

## Dashboards Específicos - Interpretación Rápida

### Dashboard Principal
**Uso**: Vista ejecutiva diaria
**Foco**: ¿Cómo vamos hoy/esta semana/este mes?
**Decisiones**: Tácticas de corto plazo

### Análisis de Ventas
**Uso**: Exploración de períodos
**Foco**: ¿Qué períodos son mejores/peores y por qué?
**Decisiones**: Planificación estacional, presupuestos

### Análisis Temporal
**Uso**: Identificación de patrones
**Foco**: ¿Cuándo vender más y cuándo esperar menos?
**Decisiones**: Recursos, inventario, campañas

### Análisis de Productos
**Uso**: Gestión de catálogo
**Foco**: ¿Qué vender, qué promocionar, qué eliminar?
**Decisiones**: Mix de productos, precios, stock

### Análisis de Clientes
**Uso**: Gestión de relaciones
**Foco**: ¿A quién vender, cómo retener, cómo reactivar?
**Decisiones**: Segmentación, fidelización, marketing

---

## Apéndice: Glosario de Términos

- **Performance**: Métrica comparativa que normaliza valores entre 0-100 tomando el máximo como referencia
- **Drill-Down**: Navegación desde datos agregados hacia datos más detallados
- **Semáforo**: Sistema de alertas visuales mediante colores (verde/amarillo/rojo)
- **KPI**: Key Performance Indicator (Indicador Clave de Rendimiento)
- **LTV**: Lifetime Value (Valor del Cliente en su Ciclo de Vida)
- **Ticket Promedio**: Valor medio de una transacción
- **Margen Bruto**: Diferencia entre precio de venta y costo de adquisición
- **Recencia**: Tiempo transcurrido desde la última compra
- **Frecuencia**: Número de compras en un período
- **Cliente Caído**: Cliente previamente activo sin compras recientes (>90 días)
- **Top N**: Los N elementos de mejor rendimiento según métrica seleccionada

---

**Última actualización**: Noviembre 2025  
**Versión**: 1.0  
**Autor**: Sistema de Business Intelligence - TPFINALBDA
