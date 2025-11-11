SELECT 
    c.codcliente            AS cliente_sk,
    c.codcliente            AS cliente_id,
    c.nombre                AS cliente_nombre,
    gc.nombre              AS segmento,
    'santa fe'              AS estado_provincia,
    1                       AS activo,
    1                       AS scd_version,
    NOW()                   AS fecha_efectiva_desde,
    1                       AS es_actual,
    NOW()                   AS created_at,
    NOW()                   AS updated_at
FROM clientes c
INNER join gruposclientes as gc 
on gc.codgrupo = c.codgrupo
WHERE c.codcliente IN (
    SELECT DISTINCT f.codcliente
    FROM facturascli f
    WHERE f.margen > 0
      AND f.fecha > '2025-01-01'
      AND c.codgrupo != '9'
)
ORDER BY c.codcliente;
