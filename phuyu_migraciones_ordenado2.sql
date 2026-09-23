-- ============================================================
-- PHUYU COMERCIAL - MIGRACIONES ORDENADAS 2
-- Ejecutar despues de: phuyu_migraciones_ordenado.sql
-- Fecha inicio archivo: 2026-09-18
-- ============================================================

-- ============================================================
-- 001. RESTAURANTE: TIPIFICACION DE PRODUCTOS Y TRAZA DE RECETAS
-- Fecha: 2026-09-18
-- Validado: idempotente; puede ejecutarse mas de una vez.
-- Que hace:
-- - Agrega marcas opcionales para venta, insumo y preparado.
-- - Agrega datos de origen al movimiento de almacen para descontar
--   ingredientes de recetas sin perder el plato vendido en kardexdetalle.
-- - Crea configuracion de rendimiento de recetas para futuras porciones.
-- - Migra marcas existentes segun recetas ya registradas.
-- ============================================================

ALTER TABLE almacen.productos
    ADD COLUMN IF NOT EXISTS es_venta smallint NOT NULL DEFAULT 1,
    ADD COLUMN IF NOT EXISTS es_insumo smallint NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS es_preparado smallint NOT NULL DEFAULT 0;

ALTER TABLE kardex.kardexalmacendetalle
    ADD COLUMN IF NOT EXISTS es_receta smallint NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS codproducto_origen integer NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS codunidad_origen integer NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS item_origen integer NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS cantidad_origen numeric(18,6) NOT NULL DEFAULT 0;

CREATE TABLE IF NOT EXISTS restaurante.recetas_config (
    codproducto integer NOT NULL,
    codunidad integer NOT NULL,
    rendimiento numeric(18,6) NOT NULL DEFAULT 1,
    codunidad_rendimiento integer NOT NULL DEFAULT 0,
    estado smallint NOT NULL DEFAULT 1,
    fechacreado timestamp without time zone NOT NULL DEFAULT now(),
    PRIMARY KEY (codproducto, codunidad)
);

UPDATE almacen.productos p
SET es_preparado = 1
WHERE EXISTS (
    SELECT 1
    FROM restaurante.recetas r
    WHERE r.codproducto = p.codproducto
    AND r.estado = 1
);

UPDATE almacen.productos p
SET es_insumo = 1
WHERE EXISTS (
    SELECT 1
    FROM restaurante.recetas r
    WHERE r.codproducto_receta = p.codproducto
    AND r.estado = 1
);

-- ============================================================
-- 002. RESTAURANTE: MERMA / RENDIMIENTO COMO CARACTERISTICA DEL PRODUCTO
-- Fecha: 2026-09-18
-- Validado: idempotente; puede ejecutarse mas de una vez.
-- Que hace:
-- - Agrega un porcentaje de merma/rendimiento al producto.
-- - Valores negativos representan perdida: -20 = rinde 80%.
-- - Valores positivos representan ganancia/rendimiento extra: 15 = rinde 115%.
-- - Se usa para calcular costo real de recetas; no mueve stock.
-- ============================================================

ALTER TABLE almacen.productos
    ADD COLUMN IF NOT EXISTS merma_porcentaje numeric(10,4) NOT NULL DEFAULT 0;

-- ============================================================
-- 003. REPORTES: INDICES PARA HISTORIAL DE COSTOS DE COMPRA
-- Fecha: 2026-09-18
-- Validado: idempotente; puede ejecutarse mas de una vez.
-- Que hace:
-- - Agrega indices para consultar compras validas por fecha/producto.
-- - No crea tablas de historial; reutiliza kardex.kardexdetalle.
-- ============================================================

CREATE INDEX IF NOT EXISTS idx_kardex_compras_historial
    ON kardex.kardex (codmovimientotipo, estado, codalmacen, codsucursal, fechacomprobante, codkardex);

CREATE INDEX IF NOT EXISTS idx_kardexdetalle_producto_historial
    ON kardex.kardexdetalle (codproducto, codunidad, codkardex, estado);

SELECT setval(
    pg_get_serial_sequence('seguridad.modulos', 'codmodulo'),
    COALESCE((SELECT MAX(codmodulo) FROM seguridad.modulos), 0) + 1,
    false
);

INSERT INTO seguridad.modulos (
    descripcion, icono, url, codpadre, orden, estado,
    nuevo, editar, anular, consultar,
    clavenuevo, clavemodificar, claveanular, claveconsultar, codsistema
)
SELECT
    'Análisis de costos',
    '',
    'reportes/costoscompra',
    7,
    14,
    1,
    0,
    0,
    0,
    1,
    '',
    '',
    '',
    '',
    1
WHERE NOT EXISTS (
    SELECT 1
    FROM seguridad.modulos
    WHERE url = 'reportes/costoscompra'
);

INSERT INTO seguridad.moduloperfiles (codmodulo, codperfil, nuevo, editar, anular)
SELECT m.codmodulo, p.codperfil, 0, 0, 0
FROM seguridad.modulos m
INNER JOIN seguridad.perfiles p ON p.codperfil IN (1, 2, 3)
WHERE m.url = 'reportes/costoscompra'
AND NOT EXISTS (
    SELECT 1
    FROM seguridad.moduloperfiles mp
    WHERE mp.codmodulo = m.codmodulo
    AND mp.codperfil = p.codperfil
);

-- Validacion rapida:
-- SELECT m.url, string_agg(mp.codperfil::text, ',' ORDER BY mp.codperfil) AS perfiles
-- FROM seguridad.modulos m
-- LEFT JOIN seguridad.moduloperfiles mp ON mp.codmodulo = m.codmodulo
-- WHERE m.url = 'reportes/costoscompra'
-- GROUP BY m.url;

-- ============================================================
-- FIN MIGRACIONES ORDENADAS 2
-- ============================================================
