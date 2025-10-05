SELECT 
    c.codcliente            AS cliente_sk,
    c.codcliente            AS cliente_id,
    c.nombre                AS cliente_nombre,
    c.codgrupo              AS cliente_segmento,
    'santa fe'              AS provincia,
    1                       AS activo2,
    1                       AS scd_version,
    NOW()                   AS fecha_efectiva_desde,
    1                       AS es_actual,
    NOW()                   AS created_at,
    NOW()                   AS updated_at
FROM clientes c
WHERE c.codcliente IN (
    SELECT DISTINCT f.codcliente
    FROM facturascli f
    WHERE f.margen > 0
      AND f.fecha > '2025-09-20'
)
ORDER BY c.codcliente;
