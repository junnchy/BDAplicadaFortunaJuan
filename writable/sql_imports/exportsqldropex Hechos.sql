SELECT
  l.idlinea AS venta_sk,
  l.idproducto AS producto_sk,
  c.codcliente AS cliente_sk,
  f.idfactura AS orden_id,
  l.idlinea AS linea_numero,
  l.cantidad AS cantidad,
  'ARS' AS moneda,
  l.pvpunitario AS precio_unitario,
  (l.pvpunitario * l.cantidad) AS monto_linea,
  l.costounitario AS costo_unitario,
  (l.costounitario * l.cantidad) AS costo_total,
  (l.pvpunitario * l.cantidad - l.costounitario * l.cantidad) AS margen_monto,
  CASE 
     WHEN (l.pvpunitario * l.cantidad) = 0 THEN 0
     ELSE ((l.pvpunitario * l.cantidad - l.costounitario * l.cantidad) / (l.pvpunitario * l.cantidad)) * 100
  END AS margen_porcentaje,
  (l.pvpunitario * l.cantidad) AS monto_neto,
  f.fecha AS fecha_factura,
  NOW() AS created_at
FROM facturascli f
INNER JOIN lineasfacturascli l 
    ON l.idfactura = f.idfactura
INNER JOIN productos p 
    ON p.idproducto = l.idproducto
INNER JOIN clientes c 
    ON c.codcliente = f.codcliente
WHERE f.fecha > '2025-01-01'
  AND (l.pvpunitario - l.costounitario) > 0 
  AND p.idproducto NOT IN (6632,5818)
  AND c.codgrupo != '9'
ORDER BY f.fecha ASC;