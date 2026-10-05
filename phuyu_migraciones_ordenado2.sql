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
-- 004. USUARIOS: PERMISOS OPERATIVOS Y CAJAS ASIGNADAS
-- Fecha: 2026-10-05
-- Validado: idempotente; puede ejecutarse mas de una vez.
-- Que hace:
-- - Asegura el permiso por usuario para modificar precio en ventas.
-- - Crea la asignacion de cajas permitidas por usuario y sucursal.
-- - Inicializa accesos a cajas existentes segun las sucursales ya asignadas,
--   para mantener el comportamiento actual despues de migrar.
-- ============================================================

ALTER TABLE seguridad.usuarios
    ADD COLUMN IF NOT EXISTS editar_pventa integer NOT NULL DEFAULT 0;

UPDATE seguridad.usuarios u
SET editar_pventa = 1
FROM seguridad.perfiles p
WHERE p.codperfil = u.codperfil
AND (
    u.codperfil = 1
    OR UPPER(p.descripcion) LIKE '%ADMIN%'
    OR UPPER(p.descripcion) LIKE '%PROGRAMADOR%'
)
AND COALESCE(u.editar_pventa, 0) = 0;

CREATE TABLE IF NOT EXISTS seguridad.cajausuarios (
    codcajausuario serial PRIMARY KEY,
    codusuario integer NOT NULL,
    codsucursal integer NOT NULL,
    codcaja integer NOT NULL,
    estado integer NOT NULL DEFAULT 1
);

CREATE UNIQUE INDEX IF NOT EXISTS uq_cajausuarios_usuario_sucursal_caja
    ON seguridad.cajausuarios (codusuario, codsucursal, codcaja);

CREATE INDEX IF NOT EXISTS idx_cajausuarios_usuario_sucursal
    ON seguridad.cajausuarios (codusuario, codsucursal);

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM pg_constraint
        WHERE conname = 'fk_cajausuarios_usuario'
    ) THEN
        ALTER TABLE seguridad.cajausuarios
        ADD CONSTRAINT fk_cajausuarios_usuario
        FOREIGN KEY (codusuario)
        REFERENCES seguridad.usuarios(codusuario);
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM pg_constraint
        WHERE conname = 'fk_cajausuarios_sucursal'
    ) THEN
        ALTER TABLE seguridad.cajausuarios
        ADD CONSTRAINT fk_cajausuarios_sucursal
        FOREIGN KEY (codsucursal)
        REFERENCES public.sucursales(codsucursal);
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM pg_constraint
        WHERE conname = 'fk_cajausuarios_caja'
    ) THEN
        ALTER TABLE seguridad.cajausuarios
        ADD CONSTRAINT fk_cajausuarios_caja
        FOREIGN KEY (codcaja)
        REFERENCES caja.cajas(codcaja);
    END IF;
END $$;

INSERT INTO seguridad.cajausuarios (codusuario, codsucursal, codcaja)
SELECT su.codusuario, su.codsucursal, c.codcaja
FROM seguridad.sucursalusuarios su
INNER JOIN caja.cajas c ON c.codsucursal = su.codsucursal
WHERE c.estado = 1
AND NOT EXISTS (
    SELECT 1
    FROM seguridad.cajausuarios cu
    WHERE cu.codusuario = su.codusuario
    AND cu.codsucursal = su.codsucursal
    AND cu.codcaja = c.codcaja
);

-- ============================================================
-- 005. USUARIOS: PERMISO ESPECIAL PARA ELIMINAR VENTAS
-- Fecha: 2026-10-05
-- Validado: idempotente; puede ejecutarse mas de una vez.
-- Que hace:
-- - Agrega permiso por usuario para anular/eliminar ventas.
-- - Inicializa administradores/programadores con el permiso activo.
-- ============================================================

ALTER TABLE seguridad.usuarios
    ADD COLUMN IF NOT EXISTS eliminar_venta integer NOT NULL DEFAULT 0;

UPDATE seguridad.usuarios u
SET eliminar_venta = 1
FROM seguridad.perfiles p
WHERE p.codperfil = u.codperfil
AND (
    u.codperfil = 1
    OR UPPER(p.descripcion) LIKE '%ADMIN%'
    OR UPPER(p.descripcion) LIKE '%PROGRAMADOR%'
)
AND COALESCE(u.eliminar_venta, 0) = 0;

-- ============================================================
-- 006. RESTAURANTE: SERVICIOS POR TIPO DE PEDIDO
-- Fecha: 2026-10-05
-- Validado: idempotente; puede ejecutarse mas de una vez.
-- Que hace:
-- - Crea productos de servicio para Delivery y Para llevar si no existen.
-- - Agrega configuracion por sucursal para saber que producto usar.
-- - Crea unidad y ubicacion de almacen con stock no controlado y precio cero.
-- - Evita que el tipo de pedido use codigos de productos reales por accidente.
-- ============================================================

ALTER TABLE public.sucursales
    ADD COLUMN IF NOT EXISTS codproducto_delivery integer NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS codunidad_delivery integer NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS codproducto_llevar integer NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS codunidad_llevar integer NOT NULL DEFAULT 0;

DO $$
DECLARE
    v_coddelivery integer;
    v_codllevar integer;
    v_codunidad integer;
BEGIN
    SELECT codunidad INTO v_codunidad
    FROM almacen.unidades
    WHERE estado = 1
    ORDER BY CASE WHEN UPPER(descripcion) LIKE 'UNIDAD%' THEN 0 ELSE 1 END, codunidad
    LIMIT 1;

    IF v_codunidad IS NULL THEN
        RAISE EXCEPTION 'No existe una unidad activa para crear servicios de tipo pedido.';
    END IF;

    INSERT INTO almacen.productos (
        codfamilia, codlinea, codmarca, codempresa, codigo, descripcion,
        afectoicbper, controlstock, afectoigvcompra, afectoigvventa,
        estado, calcular, paraventa, tipo
    )
    SELECT
        0, 3, 1, 1, 'SERV_DELIVERY', 'SERVICIO DELIVERY',
        0, 0, 0, 0,
        1, 0, 1, 1
    WHERE NOT EXISTS (
        SELECT 1 FROM almacen.productos WHERE UPPER(codigo) = 'SERV_DELIVERY'
    );

    INSERT INTO almacen.productos (
        codfamilia, codlinea, codmarca, codempresa, codigo, descripcion,
        afectoicbper, controlstock, afectoigvcompra, afectoigvventa,
        estado, calcular, paraventa, tipo
    )
    SELECT
        0, 3, 1, 1, 'SERV_LLEVAR', 'SERVICIO PARA LLEVAR',
        0, 0, 0, 0,
        1, 0, 1, 1
    WHERE NOT EXISTS (
        SELECT 1 FROM almacen.productos WHERE UPPER(codigo) = 'SERV_LLEVAR'
    );

    SELECT codproducto INTO v_coddelivery
    FROM almacen.productos
    WHERE UPPER(codigo) = 'SERV_DELIVERY'
    ORDER BY codproducto
    LIMIT 1;

    SELECT codproducto INTO v_codllevar
    FROM almacen.productos
    WHERE UPPER(codigo) = 'SERV_LLEVAR'
    ORDER BY codproducto
    LIMIT 1;

    UPDATE almacen.productos
    SET controlstock = 0,
        paraventa = 1,
        estado = 1
    WHERE codproducto IN (v_coddelivery, v_codllevar);

    INSERT INTO almacen.productounidades (
        codproducto, codunidad, codsucursal, factor,
        preciocompra, pventapublico, pventamin, pventacredito, pventaxmayor,
        pventaadicional, preciocosto, gastos, codigobarra, estado
    )
    SELECT p.codproducto, v_codunidad, 0, 1,
           0, 0, 0, 0, 0,
           0, 0, 0, p.codigo, 1
    FROM almacen.productos p
    WHERE p.codproducto IN (v_coddelivery, v_codllevar)
    AND NOT EXISTS (
        SELECT 1
        FROM almacen.productounidades pu
        WHERE pu.codproducto = p.codproducto
        AND pu.codunidad = v_codunidad
    );

    UPDATE almacen.productounidades
    SET estado = 1
    WHERE codproducto IN (v_coddelivery, v_codllevar)
    AND codunidad = v_codunidad;

    INSERT INTO almacen.productoubicacion (
        codalmacen, codproducto, codunidad, codsucursal, stockactual,
        stockactualreal, preciostockvalorizado, ventarecogo, comprarecogo,
        stockminimo, stockmaximo, estado, stockactualconvertido, factor,
        preciocompra, pventapublico, pventamin, pventacredito, pventaxmayor,
        pventaadicional, preciocosto, gastos, codigobarra, codafectacionigvcompra,
        codafectacionigvventa
    )
    SELECT a.codalmacen, p.codproducto, v_codunidad, a.codsucursal, 0,
           0, 0, 0, 0,
           0, 0, 1, 0, 1,
           0, 0, 0, 0, 0,
           0, 0, 0, p.codigo, 1,
           20
    FROM almacen.almacenes a
    CROSS JOIN almacen.productos p
    WHERE a.estado = 1
    AND p.codproducto IN (v_coddelivery, v_codllevar)
    AND NOT EXISTS (
        SELECT 1
        FROM almacen.productoubicacion pu
        WHERE pu.codalmacen = a.codalmacen
        AND pu.codproducto = p.codproducto
        AND pu.codunidad = v_codunidad
    );

    UPDATE public.sucursales
    SET codproducto_delivery = v_coddelivery,
        codunidad_delivery = v_codunidad
    WHERE COALESCE(codproducto_delivery, 0) = 0;

    UPDATE public.sucursales
    SET codproducto_llevar = v_codllevar,
        codunidad_llevar = v_codunidad
    WHERE COALESCE(codproducto_llevar, 0) = 0;
END $$;

-- ============================================================
-- FIN MIGRACIONES ORDENADAS 2
-- ============================================================
