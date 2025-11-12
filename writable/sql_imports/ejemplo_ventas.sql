-- ============================================================================
-- Archivo SQL de Ejemplo para Importar Datos de Prueba (versión normalizada)
-- ============================================================================
-- Guarda este archivo como: writable/sql_imports/ejemplo_ventas.sql
-- 
-- Este archivo contiene datos de ejemplo para:
-- - Dimensión Tiempo (fechas de 2024)
-- - Dimensión Producto (15 productos variados)
-- - Dimensión Cliente (30 clientes con datos realistas)
-- - Hechos de Ventas (138 transacciones)
-- Nota: Dimensiones de vendedor y sucursal fueron removidas (no existen en el esquema actual)
--
-- Para importar desde terminal:
-- cd /Users/juanfortuna/Documents/UAI/BDA/TPFINALBDA
-- sqlite3 writable/etl_dw_system.db < writable/sql_imports/ejemplo_ventas.sql
-- ============================================================================

BEGIN TRANSACTION;
PRAGMA foreign_keys = OFF;

-- ============================================================================
-- PASO 1: LIMPIAR TODOS LOS DATOS EXISTENTES
-- ============================================================================
-- IMPORTANTE: Este script eliminará TODOS los datos existentes en las tablas
-- y cargará únicamente los datos de ejemplo

-- Primero eliminar hechos (por integridad referencial)
DELETE FROM fact_ventas;

-- Luego eliminar todas las dimensiones
DELETE FROM dim_tiempo;
DELETE FROM dim_producto;
DELETE FROM dim_cliente;

-- Eliminar tablas temporales de ejecuciones previas (si existieran)
DROP TABLE IF EXISTS tmp_tiempo_raw;
DROP TABLE IF EXISTS tmp_producto_raw;
DROP TABLE IF EXISTS tmp_cliente_raw;
DROP TABLE IF EXISTS tmp_fact_raw;

-- ============================================================================
-- PASO 2: INSERTAR DIMENSIÓN TIEMPO (60 fechas distribuidas en 2024)
-- ============================================================================
-- Crear tabla temporal con la forma del archivo actual
CREATE TEMP TABLE tmp_tiempo_raw (
	tiempo_sk INTEGER,
	fecha TEXT,
	año INTEGER,
	mes INTEGER,
	trimestre INTEGER,
	trimestre_nombre TEXT,
	dia_semana INTEGER,
	mes_nombre TEXT,
	nombre_dia TEXT
);
INSERT INTO tmp_tiempo_raw (tiempo_sk, fecha, año, mes, trimestre, trimestre_nombre, dia_semana, mes_nombre, nombre_dia) VALUES
-- Enero 2024
(1001, '2024-01-05', 2024, 1, 1, 'Q1', 5, 'Enero', 'Viernes'),
(1002, '2024-01-12', 2024, 1, 1, 'Q1', 5, 'Enero', 'Viernes'),
(1003, '2024-01-18', 2024, 1, 1, 'Q1', 4, 'Enero', 'Jueves'),
(1004, '2024-01-25', 2024, 1, 1, 'Q1', 4, 'Enero', 'Jueves'),
-- Febrero 2024
(1005, '2024-02-02', 2024, 2, 1, 'Q1', 5, 'Febrero', 'Viernes'),
(1006, '2024-02-09', 2024, 2, 1, 'Q1', 5, 'Febrero', 'Viernes'),
(1007, '2024-02-16', 2024, 2, 1, 'Q1', 5, 'Febrero', 'Viernes'),
(1008, '2024-02-23', 2024, 2, 1, 'Q1', 5, 'Febrero', 'Viernes'),
-- Marzo 2024
(1009, '2024-03-01', 2024, 3, 1, 'Q1', 5, 'Marzo', 'Viernes'),
(1010, '2024-03-08', 2024, 3, 1, 'Q1', 5, 'Marzo', 'Viernes'),
(1011, '2024-03-15', 2024, 3, 1, 'Q1', 5, 'Marzo', 'Viernes'),
(1012, '2024-03-22', 2024, 3, 1, 'Q1', 5, 'Marzo', 'Viernes'),
(1013, '2024-03-29', 2024, 3, 1, 'Q1', 5, 'Marzo', 'Viernes'),
-- Abril 2024
(1014, '2024-04-05', 2024, 4, 2, 'Q2', 5, 'Abril', 'Viernes'),
(1015, '2024-04-12', 2024, 4, 2, 'Q2', 5, 'Abril', 'Viernes'),
(1016, '2024-04-19', 2024, 4, 2, 'Q2', 5, 'Abril', 'Viernes'),
(1017, '2024-04-26', 2024, 4, 2, 'Q2', 5, 'Abril', 'Viernes'),
-- Mayo 2024
(1018, '2024-05-03', 2024, 5, 2, 'Q2', 5, 'Mayo', 'Viernes'),
(1019, '2024-05-10', 2024, 5, 2, 'Q2', 5, 'Mayo', 'Viernes'),
(1020, '2024-05-17', 2024, 5, 2, 'Q2', 5, 'Mayo', 'Viernes'),
(1021, '2024-05-24', 2024, 5, 2, 'Q2', 5, 'Mayo', 'Viernes'),
(1022, '2024-05-31', 2024, 5, 2, 'Q2', 5, 'Mayo', 'Viernes'),
-- Junio 2024
(1023, '2024-06-07', 2024, 6, 2, 'Q2', 5, 'Junio', 'Viernes'),
(1024, '2024-06-14', 2024, 6, 2, 'Q2', 5, 'Junio', 'Viernes'),
(1025, '2024-06-21', 2024, 6, 2, 'Q2', 5, 'Junio', 'Viernes'),
(1026, '2024-06-28', 2024, 6, 2, 'Q2', 5, 'Junio', 'Viernes'),
-- Julio 2024
(1027, '2024-07-05', 2024, 7, 3, 'Q3', 5, 'Julio', 'Viernes'),
(1028, '2024-07-12', 2024, 7, 3, 'Q3', 5, 'Julio', 'Viernes'),
(1029, '2024-07-19', 2024, 7, 3, 'Q3', 5, 'Julio', 'Viernes'),
(1030, '2024-07-26', 2024, 7, 3, 'Q3', 5, 'Julio', 'Viernes'),
-- Agosto 2024
(1031, '2024-08-02', 2024, 8, 3, 'Q3', 5, 'Agosto', 'Viernes'),
(1032, '2024-08-09', 2024, 8, 3, 'Q3', 5, 'Agosto', 'Viernes'),
(1033, '2024-08-16', 2024, 8, 3, 'Q3', 5, 'Agosto', 'Viernes'),
(1034, '2024-08-23', 2024, 8, 3, 'Q3', 5, 'Agosto', 'Viernes'),
(1035, '2024-08-30', 2024, 8, 3, 'Q3', 5, 'Agosto', 'Viernes'),
-- Septiembre 2024
(1036, '2024-09-06', 2024, 9, 3, 'Q3', 5, 'Septiembre', 'Viernes'),
(1037, '2024-09-13', 2024, 9, 3, 'Q3', 5, 'Septiembre', 'Viernes'),
(1038, '2024-09-20', 2024, 9, 3, 'Q3', 5, 'Septiembre', 'Viernes'),
(1039, '2024-09-27', 2024, 9, 3, 'Q3', 5, 'Septiembre', 'Viernes'),
-- Octubre 2024
(1040, '2024-10-04', 2024, 10, 4, 'Q4', 5, 'Octubre', 'Viernes'),
(1041, '2024-10-11', 2024, 10, 4, 'Q4', 5, 'Octubre', 'Viernes'),
(1042, '2024-10-18', 2024, 10, 4, 'Q4', 5, 'Octubre', 'Viernes'),
(1043, '2024-10-25', 2024, 10, 4, 'Q4', 5, 'Octubre', 'Viernes'),
-- Noviembre 2024
(1044, '2024-11-01', 2024, 11, 4, 'Q4', 5, 'Noviembre', 'Viernes'),
(1045, '2024-11-08', 2024, 11, 4, 'Q4', 5, 'Noviembre', 'Viernes'),
(1046, '2024-11-15', 2024, 11, 4, 'Q4', 5, 'Noviembre', 'Viernes'),
(1047, '2024-11-22', 2024, 11, 4, 'Q4', 5, 'Noviembre', 'Viernes'),
(1048, '2024-11-29', 2024, 11, 4, 'Q4', 5, 'Noviembre', 'Viernes'),
-- Diciembre 2024
(1049, '2024-12-06', 2024, 12, 4, 'Q4', 5, 'Diciembre', 'Viernes'),
(1050, '2024-12-13', 2024, 12, 4, 'Q4', 5, 'Diciembre', 'Viernes'),
(1051, '2024-12-20', 2024, 12, 4, 'Q4', 5, 'Diciembre', 'Viernes'),
(1052, '2024-12-27', 2024, 12, 4, 'Q4', 5, 'Diciembre', 'Viernes');

-- ============================================================================
-- PASO 3: INSERTAR DIMENSIÓN PRODUCTO (15 productos variados)
-- ============================================================================
-- Crear tabla temporal con la forma del archivo actual
CREATE TEMP TABLE tmp_producto_raw (
	producto_sk INTEGER,
	nombre TEXT,
	categoria TEXT,
	precio_lista REAL,
	costo_unitario REAL
);
INSERT INTO tmp_producto_raw (producto_sk, nombre, categoria, precio_lista, costo_unitario) VALUES
-- Electrónicos
(101, 'Laptop Gaming RTX 4060', 'Electrónicos', 1899.99, 1300.00),
(102, 'Laptop Oficina Core i5', 'Electrónicos', 899.99, 650.00),
(103, 'Monitor 4K 27 pulgadas', 'Electrónicos', 449.99, 280.00),
(104, 'Monitor Full HD 24 pulgadas', 'Electrónicos', 189.99, 120.00),
-- Accesorios
(105, 'Mouse Inalámbrico Logitech', 'Accesorios', 35.99, 18.00),
(106, 'Mouse Gaming RGB', 'Accesorios', 79.99, 45.00),
(107, 'Teclado Mecánico RGB', 'Accesorios', 129.99, 75.00),
(108, 'Webcam Full HD', 'Accesorios', 89.99, 50.00),
-- Audio
(109, 'Audífonos Bluetooth Premium', 'Audio', 249.99, 140.00),
(110, 'Audífonos Gaming 7.1', 'Audio', 159.99, 90.00),
(111, 'Parlantes Bluetooth', 'Audio', 79.99, 45.00),
-- Software
(112, 'Licencia Office 365 Anual', 'Software', 99.99, 60.00),
(113, 'Antivirus Premium 1 Año', 'Software', 49.99, 25.00),
-- Accesorios de red
(114, 'Router WiFi 6 Mesh', 'Redes', 299.99, 180.00),
(115, 'Disco Duro Externo 2TB', 'Almacenamiento', 119.99, 70.00);

-- ============================================================================
-- PASO 4: INSERTAR DIMENSIÓN CLIENTE (30 clientes con datos realistas)
-- ============================================================================
-- Crear tabla temporal con la forma del archivo actual
CREATE TEMP TABLE tmp_cliente_raw (
	cliente_sk INTEGER,
	nombre TEXT,
	email TEXT,
	telefono TEXT,
	ciudad TEXT,
	provincia TEXT,
	codigo_postal TEXT,
	fecha_registro TEXT,
	segmento TEXT
);
INSERT INTO tmp_cliente_raw (cliente_sk, nombre, email, telefono, ciudad, provincia, codigo_postal, fecha_registro, segmento) VALUES
-- Clientes Premium (Alta frecuencia y ticket)
(1001, 'Tech Solutions SA', 'contacto@techsolutions.com', '011-4567-8901', 'Buenos Aires', 'Buenos Aires', '1425', '2023-01-15', 'Corporativo'),
(1002, 'María González', 'maria.gonzalez@email.com', '011-4567-8902', 'Buenos Aires', 'Buenos Aires', '1426', '2023-02-20', 'Premium'),
(1003, 'Sistemas Integrales SRL', 'ventas@sistemasint.com', '011-4567-8903', 'Córdoba', 'Córdoba', '5000', '2023-03-10', 'Corporativo'),
(1004, 'Juan Pérez', 'juan.perez@email.com', '011-4567-8904', 'Rosario', 'Santa Fe', '2000', '2023-04-05', 'Premium'),
(1005, 'Laura Martínez', 'laura.martinez@email.com', '011-4567-8905', 'Buenos Aires', 'Buenos Aires', '1428', '2023-05-12', 'Premium'),

-- Clientes Regulares (Frecuencia media)
(1006, 'Carlos Rodríguez', 'carlos.rodriguez@email.com', '011-4567-8906', 'Mendoza', 'Mendoza', '5500', '2023-06-18', 'Regular'),
(1007, 'Ana López', 'ana.lopez@email.com', '011-4567-8907', 'Buenos Aires', 'Buenos Aires', '1430', '2023-07-22', 'Regular'),
(1008, 'Roberto Fernández', 'roberto.fernandez@email.com', '011-4567-8908', 'La Plata', 'Buenos Aires', '1900', '2023-08-14', 'Regular'),
(1009, 'Sofía Díaz', 'sofia.diaz@email.com', '011-4567-8909', 'Tucumán', 'Tucumán', '4000', '2023-09-03', 'Regular'),
(1010, 'Diego Sánchez', 'diego.sanchez@email.com', '011-4567-8910', 'Salta', 'Salta', '4400', '2023-10-11', 'Regular'),
(1011, 'Valeria Torres', 'valeria.torres@email.com', '011-4567-8911', 'Mar del Plata', 'Buenos Aires', '7600', '2023-11-05', 'Regular'),
(1012, 'Martín Ruiz', 'martin.ruiz@email.com', '011-4567-8912', 'Neuquén', 'Neuquén', '8300', '2023-12-01', 'Regular'),

-- Clientes Ocasionales (Baja frecuencia)
(1013, 'Patricia Gómez', 'patricia.gomez@email.com', '011-4567-8913', 'Buenos Aires', 'Buenos Aires', '1432', '2024-01-10', 'Ocasional'),
(1014, 'Fernando Castro', 'fernando.castro@email.com', '011-4567-8914', 'Córdoba', 'Córdoba', '5001', '2024-02-15', 'Ocasional'),
(1015, 'Gabriela Moreno', 'gabriela.moreno@email.com', '011-4567-8915', 'Rosario', 'Santa Fe', '2001', '2024-03-20', 'Ocasional'),
(1016, 'Alejandro Romero', 'alejandro.romero@email.com', '011-4567-8916', 'Mendoza', 'Mendoza', '5501', '2024-04-08', 'Ocasional'),
(1017, 'Carolina Silva', 'carolina.silva@email.com', '011-4567-8917', 'Buenos Aires', 'Buenos Aires', '1434', '2024-05-12', 'Ocasional'),
(1018, 'Lucas Benítez', 'lucas.benitez@email.com', '011-4567-8918', 'La Plata', 'Buenos Aires', '1901', '2024-06-05', 'Ocasional'),

-- Nuevos Clientes (Recientes, para simular clientes caídos)
(1019, 'Empresas del Sur SA', 'info@empresassur.com', '011-4567-8919', 'Bahía Blanca', 'Buenos Aires', '8000', '2024-01-20', 'Corporativo'),
(1020, 'Daniela Acosta', 'daniela.acosta@email.com', '011-4567-8920', 'San Juan', 'San Juan', '5400', '2024-02-25', 'Regular'),
(1021, 'Pablo Vargas', 'pablo.vargas@email.com', '011-4567-8921', 'Corrientes', 'Corrientes', '3400', '2024-03-15', 'Ocasional'),
(1022, 'Innovación Digital SRL', 'contacto@innovadigital.com', '011-4567-8922', 'Buenos Aires', 'Buenos Aires', '1436', '2024-04-10', 'Corporativo'),

-- Clientes VIP (Muy alta frecuencia)
(1023, 'Global Tech Argentina', 'ventas@globaltech.com.ar', '011-4567-8923', 'Buenos Aires', 'Buenos Aires', '1438', '2022-06-15', 'VIP'),
(1024, 'Mónica Herrera', 'monica.herrera@email.com', '011-4567-8924', 'Córdoba', 'Córdoba', '5002', '2022-08-20', 'VIP'),
(1025, 'Ricardo Medina', 'ricardo.medina@email.com', '011-4567-8925', 'Buenos Aires', 'Buenos Aires', '1440', '2022-10-12', 'VIP'),

-- Clientes adicionales
(1026, 'Soluciones Empresariales', 'info@solempresariales.com', '011-4567-8926', 'Santa Fe', 'Santa Fe', '3000', '2023-05-18', 'Corporativo'),
(1027, 'Claudia Ortiz', 'claudia.ortiz@email.com', '011-4567-8927', 'Tandil', 'Buenos Aires', '7000', '2023-07-22', 'Regular'),
(1028, 'Javier Molina', 'javier.molina@email.com', '011-4567-8928', 'Paraná', 'Entre Ríos', '3100', '2023-09-14', 'Regular'),
(1029, 'Tecnología Avanzada SA', 'ventas@tecavanzada.com', '011-4567-8929', 'Buenos Aires', 'Buenos Aires', '1442', '2023-11-08', 'Corporativo'),
(1030, 'Verónica Ríos', 'veronica.rios@email.com', '011-4567-8930', 'San Luis', 'San Luis', '5700', '2024-01-25', 'Ocasional');

-- ============================================================================
-- PASO 5: INSERTAR DIMENSIÓN VENDEDOR (5 vendedores)
-- ============================================================================
-- OMITIDO: El esquema actual no contiene dim_vendedor. Se ignoran estos datos.

-- ============================================================================
-- PASO 6: INSERTAR DIMENSIÓN SUCURSAL (3 sucursales)
-- ============================================================================
-- OMITIDO: El esquema actual no contiene dim_sucursal. Se ignoran estos datos.

-- ============================================================================
-- PASO 7: INSERTAR HECHOS DE VENTAS (120+ transacciones con variedad)
-- ============================================================================

-- ENERO 2024 - Inicio de año (15 transacciones)
-- Crear tabla temporal para hechos con la forma actual
CREATE TEMP TABLE tmp_fact_raw (
	venta_id TEXT,
	tiempo_sk INTEGER,
	producto_sk INTEGER,
	cliente_sk INTEGER,
	vendedor_sk INTEGER,
	sucursal_sk INTEGER,
	cantidad REAL,
	monto_linea REAL,
	costo_total REAL,
	margen_bruto REAL,
	descuento REAL
);
INSERT INTO tmp_fact_raw (venta_id, tiempo_sk, producto_sk, cliente_sk, vendedor_sk, sucursal_sk, cantidad, monto_linea, costo_total, margen_bruto, descuento) VALUES
('V2024-0001', 1001, 101, 1001, 101, 11, 2, 3799.98, 2600.00, 1199.98, 0),
('V2024-0002', 1001, 105, 1002, 102, 11, 5, 179.95, 90.00, 89.95, 0),
('V2024-0003', 1002, 103, 1003, 103, 13, 3, 1349.97, 840.00, 509.97, 0),
('V2024-0004', 1002, 107, 1004, 101, 12, 2, 259.98, 150.00, 109.98, 0),
('V2024-0005', 1003, 109, 1005, 104, 11, 1, 249.99, 140.00, 109.99, 0),
('V2024-0006', 1003, 112, 1023, 102, 11, 10, 999.90, 600.00, 399.90, 0),
('V2024-0007', 1004, 114, 1001, 105, 11, 2, 599.98, 360.00, 239.98, 0),
('V2024-0008', 1004, 106, 1006, 103, 12, 3, 239.97, 135.00, 104.97, 0),
('V2024-0009', 1004, 115, 1013, 101, 11, 1, 119.99, 70.00, 49.99, 0),
('V2024-0010', 1004, 110, 1007, 104, 11, 2, 319.98, 180.00, 139.98, 0),
('V2024-0011', 1001, 102, 1024, 102, 11, 1, 899.99, 650.00, 249.99, 0),
('V2024-0012', 1002, 104, 1008, 103, 12, 2, 379.98, 240.00, 139.98, 0),
('V2024-0013', 1003, 108, 1009, 105, 13, 1, 89.99, 50.00, 39.99, 0),
('V2024-0014', 1003, 113, 1025, 101, 11, 5, 249.95, 125.00, 124.95, 0),
('V2024-0015', 1004, 111, 1010, 104, 11, 2, 159.98, 90.00, 69.98, 0),

-- FEBRERO 2024 (18 transacciones)
('V2024-0016', 1005, 101, 1023, 102, 11, 1, 1899.99, 1300.00, 599.99, 0),
('V2024-0017', 1005, 105, 1002, 101, 11, 10, 359.90, 180.00, 179.90, 0),
('V2024-0018', 1006, 103, 1001, 103, 11, 2, 899.98, 560.00, 339.98, 0),
('V2024-0019', 1006, 107, 1024, 104, 11, 1, 129.99, 75.00, 54.99, 0),
('V2024-0020', 1007, 109, 1003, 105, 13, 2, 499.98, 280.00, 219.98, 0),
('V2024-0021', 1007, 106, 1005, 102, 11, 4, 319.96, 180.00, 139.96, 0),
('V2024-0022', 1008, 114, 1023, 101, 11, 1, 299.99, 180.00, 119.99, 0),
('V2024-0023', 1008, 102, 1004, 103, 12, 1, 899.99, 650.00, 249.99, 0),
('V2024-0024', 1008, 112, 1001, 104, 11, 15, 1499.85, 900.00, 599.85, 0),
('V2024-0025', 1005, 110, 1011, 102, 12, 1, 159.99, 90.00, 69.99, 0),
('V2024-0026', 1006, 115, 1014, 105, 13, 2, 239.98, 140.00, 99.98, 0),
('V2024-0027', 1006, 108, 1006, 101, 11, 1, 89.99, 50.00, 39.99, 0),
('V2024-0028', 1007, 104, 1012, 103, 12, 3, 569.97, 360.00, 209.97, 0),
('V2024-0029', 1007, 113, 1025, 104, 11, 8, 399.92, 200.00, 199.92, 0),
('V2024-0030', 1008, 111, 1007, 102, 11, 1, 79.99, 45.00, 34.99, 0),
('V2024-0031', 1008, 106, 1024, 105, 11, 2, 159.98, 90.00, 69.98, 0),
('V2024-0032', 1005, 101, 1026, 101, 11, 1, 1899.99, 1300.00, 599.99, 0),
('V2024-0033', 1006, 103, 1027, 103, 12, 1, 449.99, 280.00, 169.99, 0),

-- MARZO 2024 (20 transacciones)
('V2024-0034', 1009, 109, 1023, 102, 11, 3, 749.97, 420.00, 329.97, 0),
('V2024-0035', 1009, 105, 1001, 104, 11, 8, 287.92, 144.00, 143.92, 0),
('V2024-0036', 1010, 107, 1003, 105, 13, 2, 259.98, 150.00, 109.98, 0),
('V2024-0037', 1010, 114, 1024, 101, 11, 1, 299.99, 180.00, 119.99, 0),
('V2024-0038', 1011, 102, 1002, 103, 12, 1, 899.99, 650.00, 249.99, 0),
('V2024-0039', 1011, 112, 1023, 102, 11, 12, 1199.88, 720.00, 479.88, 0),
('V2024-0040', 1012, 106, 1004, 104, 11, 3, 239.97, 135.00, 104.97, 0),
('V2024-0041', 1012, 110, 1025, 105, 11, 2, 319.98, 180.00, 139.98, 0),
('V2024-0042', 1013, 115, 1005, 101, 12, 1, 119.99, 70.00, 49.99, 0),
('V2024-0043', 1013, 108, 1015, 103, 13, 2, 179.98, 100.00, 79.98, 0),
('V2024-0044', 1009, 104, 1028, 102, 12, 2, 379.98, 240.00, 139.98, 0),
('V2024-0045', 1010, 113, 1001, 104, 11, 6, 299.94, 150.00, 149.94, 0),
('V2024-0046', 1011, 111, 1008, 105, 11, 2, 159.98, 90.00, 69.98, 0),
('V2024-0047', 1011, 101, 1029, 101, 11, 1, 1899.99, 1300.00, 599.99, 0),
('V2024-0048', 1012, 103, 1023, 103, 11, 4, 1799.96, 1120.00, 679.96, 0),
('V2024-0049', 1012, 109, 1024, 102, 11, 1, 249.99, 140.00, 109.99, 0),
('V2024-0050', 1013, 107, 1003, 104, 13, 3, 389.97, 225.00, 164.97, 0),
('V2024-0051', 1013, 105, 1025, 105, 11, 6, 215.94, 108.00, 107.94, 0),
('V2024-0052', 1009, 114, 1001, 101, 11, 2, 599.98, 360.00, 239.98, 0),
('V2024-0053', 1010, 102, 1004, 103, 12, 1, 899.99, 650.00, 249.99, 0),

-- ABRIL - JUNIO 2024 (30 transacciones - Q2)
('V2024-0054', 1014, 101, 1023, 102, 11, 2, 3799.98, 2600.00, 1199.98, 0),
('V2024-0055', 1014, 112, 1001, 104, 11, 20, 1999.80, 1200.00, 799.80, 0),
('V2024-0056', 1015, 109, 1024, 105, 11, 2, 499.98, 280.00, 219.98, 0),
('V2024-0057', 1015, 103, 1003, 101, 13, 3, 1349.97, 840.00, 509.97, 0),
('V2024-0058', 1016, 114, 1025, 103, 11, 1, 299.99, 180.00, 119.99, 0),
('V2024-0059', 1016, 107, 1002, 102, 11, 2, 259.98, 150.00, 109.98, 0),
('V2024-0060', 1017, 106, 1005, 104, 12, 4, 319.96, 180.00, 139.96, 0),
('V2024-0061', 1017, 110, 1023, 105, 11, 3, 479.97, 270.00, 209.97, 0),
('V2024-0062', 1018, 102, 1001, 101, 11, 1, 899.99, 650.00, 249.99, 0),
('V2024-0063', 1018, 115, 1016, 103, 13, 2, 239.98, 140.00, 99.98, 0),
('V2024-0064', 1019, 105, 1024, 102, 11, 12, 431.88, 216.00, 215.88, 0),
('V2024-0065', 1019, 108, 1004, 104, 12, 1, 89.99, 50.00, 39.99, 0),
('V2024-0066', 1020, 113, 1025, 105, 11, 10, 499.90, 250.00, 249.90, 0),
('V2024-0067', 1020, 104, 1006, 101, 12, 3, 569.97, 360.00, 209.97, 0),
('V2024-0068', 1021, 111, 1009, 103, 11, 2, 159.98, 90.00, 69.98, 0),
('V2024-0069', 1021, 101, 1023, 102, 11, 1, 1899.99, 1300.00, 599.99, 0),
('V2024-0070', 1022, 109, 1001, 104, 11, 2, 499.98, 280.00, 219.98, 0),
('V2024-0071', 1022, 103, 1024, 105, 11, 2, 899.98, 560.00, 339.98, 0),
('V2024-0072', 1023, 112, 1003, 101, 13, 15, 1499.85, 900.00, 599.85, 0),
('V2024-0073', 1023, 114, 1025, 103, 11, 2, 599.98, 360.00, 239.98, 0),
('V2024-0074', 1024, 107, 1002, 102, 11, 3, 389.97, 225.00, 164.97, 0),
('V2024-0075', 1024, 106, 1023, 104, 11, 5, 399.95, 225.00, 174.95, 0),
('V2024-0076', 1025, 102, 1001, 105, 11, 2, 1799.98, 1300.00, 499.98, 0),
('V2024-0077', 1025, 110, 1024, 101, 11, 2, 319.98, 180.00, 139.98, 0),
('V2024-0078', 1026, 115, 1017, 103, 12, 1, 119.99, 70.00, 49.99, 0),
('V2024-0079', 1026, 105, 1025, 102, 11, 8, 287.92, 144.00, 143.92, 0),
('V2024-0080', 1014, 104, 1011, 104, 12, 2, 379.98, 240.00, 139.98, 0),
('V2024-0081', 1015, 108, 1012, 105, 13, 2, 179.98, 100.00, 79.98, 0),
('V2024-0082', 1016, 113, 1023, 101, 11, 7, 349.93, 175.00, 174.93, 0),
('V2024-0083', 1017, 111, 1001, 103, 11, 3, 239.97, 135.00, 104.97, 0),

-- JULIO - SEPTIEMBRE 2024 (30 transacciones - Q3)
('V2024-0084', 1027, 101, 1023, 102, 11, 3, 5699.97, 3900.00, 1799.97, 0),
('V2024-0085', 1027, 109, 1024, 104, 11, 2, 499.98, 280.00, 219.98, 0),
('V2024-0086', 1028, 103, 1001, 105, 11, 4, 1799.96, 1120.00, 679.96, 0),
('V2024-0087', 1028, 112, 1025, 101, 11, 18, 1799.82, 1080.00, 719.82, 0),
('V2024-0088', 1029, 114, 1003, 103, 13, 2, 599.98, 360.00, 239.98, 0),
('V2024-0089', 1029, 107, 1002, 102, 11, 4, 519.96, 300.00, 219.96, 0),
('V2024-0090', 1030, 106, 1023, 104, 11, 6, 479.94, 270.00, 209.94, 0),
('V2024-0091', 1030, 110, 1024, 105, 11, 3, 479.97, 270.00, 209.97, 0),
('V2024-0092', 1031, 102, 1001, 101, 11, 1, 899.99, 650.00, 249.99, 0),
('V2024-0093', 1031, 115, 1005, 103, 12, 3, 359.97, 210.00, 149.97, 0),
('V2024-0094', 1032, 105, 1025, 102, 11, 10, 359.90, 180.00, 179.90, 0),
('V2024-0095', 1032, 108, 1004, 104, 13, 2, 179.98, 100.00, 79.98, 0),
('V2024-0096', 1033, 113, 1023, 105, 11, 12, 599.88, 300.00, 299.88, 0),
('V2024-0097', 1033, 104, 1024, 101, 11, 4, 759.96, 480.00, 279.96, 0),
('V2024-0098', 1034, 111, 1006, 103, 12, 2, 159.98, 90.00, 69.98, 0),
('V2024-0099', 1034, 101, 1001, 102, 11, 1, 1899.99, 1300.00, 599.99, 0),
('V2024-0100', 1035, 109, 1025, 104, 11, 3, 749.97, 420.00, 329.97, 0),
('V2024-0101', 1035, 103, 1023, 105, 11, 3, 1349.97, 840.00, 509.97, 0),
('V2024-0102', 1036, 112, 1001, 101, 11, 22, 2199.78, 1320.00, 879.78, 0),
('V2024-0103', 1036, 114, 1003, 103, 13, 1, 299.99, 180.00, 119.99, 0),
('V2024-0104', 1037, 107, 1024, 102, 11, 3, 389.97, 225.00, 164.97, 0),
('V2024-0105', 1037, 106, 1025, 104, 11, 4, 319.96, 180.00, 139.96, 0),
('V2024-0106', 1038, 102, 1023, 105, 11, 2, 1799.98, 1300.00, 499.98, 0),
('V2024-0107', 1038, 110, 1002, 101, 11, 2, 319.98, 180.00, 139.98, 0),
('V2024-0108', 1039, 115, 1007, 103, 12, 2, 239.98, 140.00, 99.98, 0),
('V2024-0109', 1039, 105, 1001, 102, 11, 15, 539.85, 270.00, 269.85, 0),
('V2024-0110', 1027, 104, 1008, 104, 13, 3, 569.97, 360.00, 209.97, 0),
('V2024-0111', 1028, 108, 1024, 105, 11, 1, 89.99, 50.00, 39.99, 0),
('V2024-0112', 1029, 113, 1025, 101, 11, 9, 449.91, 225.00, 224.91, 0),
('V2024-0113', 1030, 111, 1023, 103, 11, 4, 319.96, 180.00, 139.96, 0),

-- OCTUBRE - DICIEMBRE 2024 (25 transacciones - Q4 con menos transacciones al final)
('V2024-0114', 1040, 101, 1023, 102, 11, 2, 3799.98, 2600.00, 1199.98, 0),
('V2024-0115', 1040, 109, 1001, 104, 11, 3, 749.97, 420.00, 329.97, 0),
('V2024-0116', 1041, 103, 1024, 105, 11, 5, 2249.95, 1400.00, 849.95, 0),
('V2024-0117', 1041, 112, 1025, 101, 11, 25, 2499.75, 1500.00, 999.75, 0),
('V2024-0118', 1042, 114, 1001, 103, 11, 3, 899.97, 540.00, 359.97, 0),
('V2024-0119', 1042, 107, 1023, 102, 11, 5, 649.95, 375.00, 274.95, 0),
('V2024-0120', 1043, 106, 1024, 104, 11, 7, 559.93, 315.00, 244.93, 0),
('V2024-0121', 1043, 110, 1025, 105, 11, 4, 639.96, 360.00, 279.96, 0),
('V2024-0122', 1044, 102, 1023, 101, 11, 2, 1799.98, 1300.00, 499.98, 0),
('V2024-0123', 1044, 115, 1003, 103, 13, 4, 479.96, 280.00, 199.96, 0),
('V2024-0124', 1045, 105, 1001, 102, 11, 20, 719.80, 360.00, 359.80, 0),
('V2024-0125', 1045, 108, 1024, 104, 12, 3, 269.97, 150.00, 119.97, 0),
('V2024-0126', 1046, 113, 1025, 105, 11, 15, 749.85, 375.00, 374.85, 0),
('V2024-0127', 1046, 104, 1023, 101, 11, 5, 949.95, 600.00, 349.95, 0),
('V2024-0128', 1047, 111, 1002, 103, 11, 3, 239.97, 135.00, 104.97, 0),
('V2024-0129', 1047, 101, 1001, 102, 11, 2, 3799.98, 2600.00, 1199.98, 0),
('V2024-0130', 1048, 109, 1024, 104, 11, 4, 999.96, 560.00, 439.96, 0),
('V2024-0131', 1048, 103, 1025, 105, 11, 4, 1799.96, 1120.00, 679.96, 0),
('V2024-0132', 1049, 112, 1023, 101, 11, 30, 2999.70, 1800.00, 1199.70, 0),
('V2024-0133', 1049, 114, 1001, 103, 11, 2, 599.98, 360.00, 239.98, 0),
('V2024-0134', 1050, 107, 1024, 102, 11, 4, 519.96, 300.00, 219.96, 0),
('V2024-0135', 1050, 106, 1025, 104, 11, 5, 399.95, 225.00, 174.95, 0),
('V2024-0136', 1051, 102, 1001, 105, 11, 1, 899.99, 650.00, 249.99, 0),
('V2024-0137', 1051, 110, 1023, 101, 11, 3, 479.97, 270.00, 209.97, 0),
('V2024-0138', 1052, 115, 1024, 103, 12, 3, 359.97, 210.00, 149.97, 0);

-- =============================================================
-- TRANSFORMACIÓN FINAL: Poblar tablas reales desde staging
-- =============================================================

-- Dim Tiempo
INSERT INTO dim_tiempo (
	fecha_natural, año, trimestre, mes, semana, dia, dia_semana, nombre_dia, nombre_mes,
	trimestre_nombre, es_fin_semana, es_feriado, semana_año, dia_año,
	fecha_primera_semana, fecha_ultimo_mes, created_at, updated_at
)
SELECT
	date(fecha) as fecha_natural,
	año,
	trimestre,
	mes,
	CAST(((CAST(strftime('%d', fecha) AS INTEGER) - 1) / 7) + 1 AS INTEGER) as semana,
	CAST(strftime('%d', fecha) AS INTEGER) as dia,
	(((CAST(strftime('%w', fecha) AS INTEGER) + 6) % 7) + 1) as dia_semana,
	nombre_dia,
	mes_nombre as nombre_mes,
	trimestre_nombre,
	CASE (((CAST(strftime('%w', fecha) AS INTEGER) + 6) % 7) + 1) WHEN 6 THEN 1 WHEN 7 THEN 1 ELSE 0 END as es_fin_semana,
	0 as es_feriado,
	CAST(strftime('%W', fecha) AS INTEGER) as semana_año,
	CAST(strftime('%j', fecha) AS INTEGER) as dia_año,
	date(fecha, 'weekday 1', '-7 days') as fecha_primera_semana,
	date(date(fecha), 'start of month', '+1 month', '-1 day') as fecha_ultimo_mes,
	CURRENT_TIMESTAMP,
	CURRENT_TIMESTAMP
FROM tmp_tiempo_raw
ORDER BY fecha_natural;

-- Dim Producto
INSERT INTO dim_producto (
	producto_sk, producto_id, producto_nombre, familia_id, familia_nombre, categoria, subcategoria,
	precio_lista, costo_estandar, margen_bruto, descripcion, activo, fecha_lanzamiento,
	fecha_descontinuacion, unidad_medida, peso_kg, dimensiones, color, marca,
	scd_version, fecha_efectiva_desde, fecha_efectiva_hasta, es_actual, created_at, updated_at
)
SELECT
	'P' || producto_sk,        -- producto_sk (surrogate igual al id de negocio)
	'P' || producto_sk,
	nombre,
	UPPER(REPLACE(categoria, ' ', '_')) as familia_id,
	categoria as familia_nombre,
	categoria,
	NULL,
	precio_lista,
	costo_unitario as costo_estandar,
	CASE WHEN precio_lista > 0 THEN ROUND(((precio_lista - costo_unitario) * 100.0) / precio_lista, 2) ELSE 0 END,
	NULL,
	1,
	NULL,
	NULL,
	'unidad',
	NULL,
	NULL,
	NULL,
	NULL,
	1,
	CURRENT_TIMESTAMP,
	NULL,
	1,
	CURRENT_TIMESTAMP,
	CURRENT_TIMESTAMP
FROM tmp_producto_raw;

-- Dim Cliente
INSERT INTO dim_cliente (
	cliente_sk, cliente_id, cliente_nombre, segmento, tipo_cliente, email, telefono, direccion,
	ciudad, estado_provincia, pais, region, codigo_postal, fecha_registro, activo,
	limite_credito, score_credito, preferencias, fecha_primera_compra, fecha_ultima_compra,
	total_compras_historicas, numero_ordenes_historicas, scd_version, fecha_efectiva_desde,
	fecha_efectiva_hasta, es_actual, created_at, updated_at
)
SELECT
	'C' || cliente_sk,         -- cliente_sk (surrogate igual al id de negocio)
	'C' || cliente_sk,
	nombre,
	segmento,
	CASE
		WHEN UPPER(nombre) LIKE '% SA' OR UPPER(nombre) LIKE '% SRL' OR UPPER(nombre) LIKE '%EMPRESA%' OR UPPER(nombre) LIKE '%SOLUCION%' OR UPPER(nombre) LIKE '%GLOBAL%'
			THEN 'Empresa'
		ELSE 'Persona'
	END,
	email,
	telefono,
	NULL,
	ciudad,
	provincia,
	'Argentina',
	'Centro',
	codigo_postal,
	date(fecha_registro),
	1,
	NULL,
	NULL,
	NULL,
	NULL,
	NULL,
	0,
	0,
	1,
	CURRENT_TIMESTAMP,
	NULL,
	1,
	CURRENT_TIMESTAMP,
	CURRENT_TIMESTAMP
FROM tmp_cliente_raw;

-- Hechos
INSERT INTO fact_ventas (
	tiempo_sk, producto_sk, cliente_sk,
	orden_id, linea_numero, cantidad, precio_unitario, monto_linea,
	descuento_monto, descuento_porcentaje, impuesto_monto,
	costo_unitario, costo_total, margen_monto, margen_porcentaje, monto_neto,
	moneda, tipo_cambio, canal_venta, vendedor_id, promocion_id,
	facturado, fecha_factura, numero_factura, etl_run_id, created_at
)
SELECT
	dt.tiempo_sk,
	dp.producto_sk,
	dc.cliente_sk,
	fr.venta_id,
	1,
	fr.cantidad,
	ROUND(fr.monto_linea / NULLIF(fr.cantidad,0), 4),
	fr.monto_linea,
	fr.descuento,
	CASE WHEN fr.monto_linea > 0 THEN ROUND(fr.descuento * 100.0 / fr.monto_linea, 2) ELSE 0 END,
	0,
	ROUND(fr.costo_total / NULLIF(fr.cantidad,0), 4),
	fr.costo_total,
	ROUND(fr.monto_linea - fr.costo_total, 2),
	CASE WHEN fr.monto_linea > 0 THEN ROUND((fr.monto_linea - fr.costo_total) * 100.0 / fr.monto_linea, 2) ELSE 0 END,
	ROUND(fr.monto_linea - fr.descuento, 2),
	'USD', 1, 'Online', NULL, NULL,
	0, NULL, NULL, strftime('%Y%m%d%H%M%S','now'), CURRENT_TIMESTAMP
FROM tmp_fact_raw fr
JOIN tmp_tiempo_raw tr ON tr.tiempo_sk = fr.tiempo_sk
JOIN dim_tiempo dt ON dt.fecha_natural = date(tr.fecha)
JOIN tmp_producto_raw pr ON pr.producto_sk = fr.producto_sk
JOIN dim_producto dp ON dp.producto_id = ('P' || pr.producto_sk) AND dp.es_actual = 1
JOIN tmp_cliente_raw cr ON cr.cliente_sk = fr.cliente_sk
JOIN dim_cliente dc ON dc.cliente_id = ('C' || cr.cliente_sk) AND dc.es_actual = 1;

COMMIT;
PRAGMA foreign_keys = ON;

-- ============================================================================
-- FIN DEL SCRIPT
-- ============================================================================
-- Total de registros insertados:
-- - Tiempos: 52 fechas
-- - Productos: 15 productos
-- - Clientes: 30 clientes
-- - Ventas: 138 transacciones
--
-- Este conjunto de datos permite:
-- ✅ Análisis temporal completo del año 2024
-- ✅ Análisis de clientes con diferentes perfiles (VIP, Premium, Regular, Ocasional)
-- ✅ Identificación de clientes caídos (algunos con última compra en enero-febrero)
-- ✅ Análisis de productos por categorías
-- ✅ Flujo staging -> DW verificado (transformaciones incluidas en el script)
-- ✅ Análisis de márgenes y rentabilidad
-- ============================================================================