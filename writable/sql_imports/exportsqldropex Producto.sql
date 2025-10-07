SELECT 
  p.idproducto AS producto_sk,
  p.idproducto AS producto_id,
  p.descripcion AS producto_nombre,
  fa.codfamilia AS familia_id,
  fa.descripcion AS familia_nombre,
  fa.descripcion AS categoria,
  fa.descripcion AS subcategoria,
  p.precio AS precio_lista,
  -- tomamos el costo unitario promedio como costo estándar
  AVG(l.costounitario) AS costo_estandar,
  -- margen promedio
  AVG(l.pvpunitario - l.costounitario) AS margen_bruto,
  p.descripcion AS descripcion,
  p.sevende AS activo,
  '' AS fecha_lanzamiento,
  'unidad' AS unidad_medida,
  p.peso AS peso_kg,
  '' AS marca,
  1 AS scd_version,
  NOW() AS fecha_efectiva_desde,
  1 AS es_actual,
  NOW() AS created_at,
  NOW() AS updated_at
FROM facturascli f
INNER JOIN lineasfacturascli l ON l.idfactura = f.idfactura
INNER JOIN productos p ON p.idproducto = l.idproducto
INNER JOIN clientes c ON c.codcliente = f.codcliente
INNER JOIN familias fa ON fa.codfamilia = p.codfamilia
WHERE f.margen > 0 
  AND f.fecha > '2025-01-01'
  AND p.idproducto NOT IN (6632,5818)  
GROUP BY 
  p.idproducto,
  p.descripcion,
  fa.codfamilia,
  fa.descripcion,
  p.precio,
  p.sevende,
  p.peso
ORDER BY p.idproducto ASC;
