-- Archivo SQL completamente corregido para importar datos al DW
-- Compatible con la estructura real de las tablas

-- Insertar datos en dim_tiempo (con estructura real)
INSERT INTO dim_tiempo (
    tiempo_sk, fecha_natural, año, trimestre, mes, semana, dia, dia_semana,
    nombre_dia, nombre_mes, trimestre_nombre, es_fin_semana, es_feriado,
    semana_año, dia_año, fecha_primera_semana, fecha_ultimo_mes, created_at, updated_at
) VALUES 
(20240101, '2024-01-01', 2024, 1, 1, 1, 1, 2, 'Lunes', 'Enero', 'Q1', 0, 0, 1, 1, '2024-01-01', '2024-01-31', datetime('now'), datetime('now')),
(20240215, '2024-02-15', 2024, 1, 2, 7, 15, 5, 'Jueves', 'Febrero', 'Q1', 0, 0, 7, 46, '2024-02-05', '2024-02-29', datetime('now'), datetime('now')),
(20240320, '2024-03-20', 2024, 1, 3, 12, 20, 4, 'Miércoles', 'Marzo', 'Q1', 0, 0, 12, 80, '2024-03-18', '2024-03-31', datetime('now'), datetime('now'));

-- Insertar datos en dim_producto (con estructura real y campos obligatorios)
INSERT INTO dim_producto (
    producto_sk, producto_id, producto_nombre, familia_id, familia_nombre, categoria,
    subcategoria, precio_lista, costo_estandar, margen_bruto, descripcion, activo,
    fecha_lanzamiento, unidad_medida, marca, scd_version, fecha_efectiva_desde,
    es_actual, created_at, updated_at
) VALUES 
(1001, 'PROD001', 'Laptop Dell Inspiron', 'FAM001', 'Computadoras', 'Electrónicos', 'Laptops', 
 850.00, 650.00, 200.00, 'Laptop Dell Inspiron 15 3000', 1, '2024-01-01', 'unidad', 'Dell', 
 1, datetime('now'), 1, datetime('now'), datetime('now')),

(1002, 'PROD002', 'Mouse Inalámbrico', 'FAM002', 'Accesorios', 'Electrónicos', 'Periféricos', 
 25.99, 18.00, 7.99, 'Mouse inalámbrico Logitech M185', 1, '2024-01-01', 'unidad', 'Logitech', 
 1, datetime('now'), 1, datetime('now'), datetime('now')),

(1003, 'PROD003', 'Teclado Mecánico', 'FAM002', 'Accesorios', 'Electrónicos', 'Periféricos', 
 129.99, 95.00, 34.99, 'Teclado mecánico Corsair K70', 1, '2024-01-01', 'unidad', 'Corsair', 
 1, datetime('now'), 1, datetime('now'), datetime('now'));

-- Insertar datos en fact_ventas (con estructura completa)
INSERT INTO fact_ventas (
    tiempo_sk, producto_sk, cliente_sk, orden_id, linea_numero,
    cantidad, precio_unitario, monto_linea, descuento_monto, descuento_porcentaje,
    impuesto_monto, costo_unitario, costo_total, margen_monto, margen_porcentaje,
    monto_neto, moneda, tipo_cambio, canal_venta, vendedor_id,
    promocion_id, facturado, fecha_factura, numero_factura, created_at
) VALUES 
(20240101, 1001, 1, 'ORD001', 1, 2, 850.00, 1700.00, 0.00, 0.00, 
 136.00, 650.00, 1300.00, 400.00, 23.53, 1836.00, 'USD', 1, 'Online', 'V001',
 NULL, 1, '2024-01-01', 'FAC001', datetime('now')),

(20240215, 1002, 2, 'ORD002', 1, 5, 25.99, 129.95, 5.00, 3.85,
 9.96, 18.00, 90.00, 39.95, 30.74, 134.91, 'USD', 1, 'Tienda', 'V002',
 'PROMO001', 1, '2024-02-15', 'FAC002', datetime('now')),

(20240320, 1003, 3, 'ORD003', 1, 1, 129.99, 129.99, 10.00, 7.69,
 9.60, 95.00, 95.00, 34.99, 26.93, 129.59, 'USD', 1, 'Online', 'V001',
 NULL, 1, '2024-03-20', 'FAC003', datetime('now'));