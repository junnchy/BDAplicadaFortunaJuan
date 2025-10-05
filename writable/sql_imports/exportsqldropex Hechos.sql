SELECT  p.referencia as producto_sk,
        c.codcliente as cliente_sk,
        f.idfactura as orden_id,
        l.idlinea as linea_numero,
        l.cantidad as cantidad,
        l.pvpunitario as precio_unitario,
        l.pvpunitario * l.cantidad as monto_linea,
        l.costounitario as costo_unitario,
        l.costounitario * l.cantidad as costo_total,
        l.pvpunitario * l.cantidad - (l.costounitario * l.cantidad) as margen_monto,
        ((l.pvpunitario * l.cantidad - (l.costounitario * l.cantidad)) / (l.pvpunitario * l.cantidad)) * 100 as margen_porcentaje,
        l.pvpunitario * l.cantidad as monto_neto,
        f.fecha as fecha_factura,
        now() as created_at
FROM facturascli as f 
INNER JOIN lineasfacturascli as l ON l.idfactura = f.idfactura
INNER JOIN productos as p ON p.idproducto = l.idproducto
INNER JOIN clientes as c ON c.codcliente = f.codcliente
INNER JOIN familias as fa ON fa.codfamilia = p.codfamilia
WHERE f.margen > 0 AND f.fecha > '2025-09-20'
ORDER BY f.fecha ASC;