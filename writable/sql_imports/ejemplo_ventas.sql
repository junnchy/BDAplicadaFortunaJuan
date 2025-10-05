-- Ejemplo de archivo SQL para importar ventas
-- Guarda este archivo como: writable/sql_imports/ejemplo_ventas.sql

-- Limpiar datos existentes (opcional)
DELETE FROM fact_ventas;
DELETE FROM dim_tiempo WHERE tiempo_sk > 1000;
DELETE FROM dim_producto WHERE producto_sk > 100;

-- Insertar dimensiones de tiempo
INSERT INTO dim_tiempo (tiempo_sk, fecha, año, mes, trimestre, trimestre_nombre, dia_semana) VALUES
(1001, '2024-01-15', 2024, 1, 1, 'Q1', 2),
(1002, '2024-02-20', 2024, 2, 1, 'Q1', 3),
(1003, '2024-03-10', 2024, 3, 1, 'Q1', 7),
(1004, '2024-06-05', 2024, 6, 2, 'Q2', 4),
(1005, '2024-09-15', 2024, 9, 3, 'Q3', 1);

-- Insertar productos
INSERT INTO dim_producto (producto_sk, nombre, categoria, precio_lista) VALUES
(101, 'Laptop Gaming', 'Electrónicos', 1500.00),
(102, 'Mouse Inalámbrico', 'Accesorios', 25.99),
(103, 'Monitor 4K', 'Electrónicos', 399.99),
(104, 'Teclado Mecánico', 'Accesorios', 89.99),
(105, 'Audífonos Bluetooth', 'Audio', 159.99);

-- Insertar hechos de ventas
INSERT INTO fact_ventas (tiempo_sk, producto_sk, canal_venta, cantidad, monto_linea, margen_monto) VALUES
(1001, 101, 'Online', 2, 3000.00, 600.00),
(1001, 102, 'Tienda', 5, 129.95, 45.00),
(1002, 103, 'Online', 1, 399.99, 80.00),
(1002, 104, 'Online', 3, 269.97, 90.00),
(1003, 105, 'Tienda', 2, 319.98, 120.00),
(1004, 101, 'Online', 1, 1500.00, 300.00),
(1004, 103, 'Tienda', 2, 799.98, 160.00),
(1005, 102, 'Online', 10, 259.90, 90.00),
(1005, 104, 'Tienda', 1, 89.99, 30.00),
(1005, 105, 'Online', 3, 479.97, 180.00);