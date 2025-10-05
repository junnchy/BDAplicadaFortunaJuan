SELECT p.referencia as producto_sk,
       p.idproducto as producto_id,
       p.descripcion as producto_nombre,
       fa.codfamilia as familia_id,
       fa.descripcion as familia_nombre,
       fa.descripcion as categoria,
       fa.descripcion as subcategoria,
       p.precio as precio_lista,
       l.costounitario as costo_estandar,
       (l.pvpunitario - l.costounitario) as margen_bruto,
       p.descripcion as descripcion,
       p.sevende as activo,
       '' as fecha_lanzamiento,
       '' as unidad_medida,
       '' as marca,
       1 as scd_version,
       now() as fecha_efectiva_desde,
       1 as es_actual,
       now() as created_at,
       now() as updated_at
FROM facturascli as f 
INNER JOIN lineasfacturascli as l ON l.idfactura = f.idfactura
INNER JOIN productos as p ON p.idproducto = l.idproducto
INNER JOIN clientes as c ON c.codcliente = f.codcliente
INNER JOIN familias as fa ON fa.codfamilia = p.codfamilia
WHERE f.margen > 0 AND f.fecha > '2025-09-20'
ORDER BY f.fecha ASC;