-- Archivo SQL corregido para importar datos al DW
-- Estructura compatible con las tablas reales del sistema ETL

-- Insertar datos en dim_tiempo (usando fecha_natural)
INSERT INTO dim_tiempo (
    tiempo_sk, fecha_natural, fecha_iso, anio, mes, dia, trimestre, 
    mes_nombre, dia_semana, dia_semana_nombre, semana_anio
) VALUES 
(20240101, '2024-01-01', '2024-01-01', 2024, 1, 1, 1, 'Enero', 2, 'Lunes', 1),
(20240215, '2024-02-15', '2024-02-15', 2024, 2, 15, 1, 'Febrero', 5, 'Jueves', 7),
(20240320, '2024-03-20', '2024-03-20', 2024, 3, 20, 1, 'Marzo', 4, 'Miércoles', 12);

-- Insertar datos en dim_producto (usando producto_nombre)
INSERT INTO dim_producto (
    producto_sk, producto_id, producto_nombre, categoria, subcategoria, 
    marca, precio_lista, activo
) VALUES 
(1001, 'PROD001', 'Laptop Dell Inspiron', 'Electrónicos', 'Computadoras', 'Dell', 850.00, 1),
(1002, 'PROD002', 'Mouse Inalámbrico', 'Electrónicos', 'Accesorios', 'Logitech', 25.99, 1),
(1003, 'PROD003', 'Teclado Mecánico', 'Electrónicos', 'Accesorios', 'Corsair', 129.99, 1);

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