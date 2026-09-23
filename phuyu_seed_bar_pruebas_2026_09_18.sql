-- ============================================================
-- PHUYU COMERCIAL - SEED BAR / RESTAURANTE DE PRUEBA
-- Fecha: 2026-09-18
-- Validado: idempotente; puede ejecutarse mas de una vez.
-- Que hace:
-- - Crea productos de prueba con prefijo BAR-*.
-- - Marca insumos y preparados para restaurante.
-- - Crea recetas basicas de bar/restaurante.
-- - Crea compras historicas de prueba para reportes/costoscompra.
-- Nota:
-- - Es data demo para base de pruebas. No correr en clientes reales.
-- ============================================================

DO $$
DECLARE
    v_codfamilia integer := 6; -- OTROS
    v_codlinea_alcohol integer := 2;
    v_codlinea_sin_alcohol integer := 1;
    v_codlinea_otros integer := 3;
    v_codmarca integer := 1; -- GENERICO
    v_codsucursal integer := 1;
    v_codalmacen integer := 1;
    v_codusuario integer := 1;
    v_codproveedor integer := 6; -- CERVECERIA SAN JUAN S.A.
    v_unidad integer := 1;
    v_botella integer := 7;
    v_kg integer := 24;
    v_litro integer := 29;
    v_codkardex integer;
BEGIN
    IF NOT EXISTS (SELECT 1 FROM public.socios WHERE codpersona = v_codproveedor) THEN
        v_codproveedor := 2; -- CLIENTE VARIOS como respaldo tecnico
    END IF;

    INSERT INTO almacen.productos (
        codfamilia, codlinea, codmarca, codempresa, codigo, descripcion,
        afectoicbper, controlstock, afectoigvcompra, afectoigvventa,
        foto, estado, calcular, paraventa, codatencion, caracteristicas,
        codoficial, sexo, codmodelo, codcolor, codtalla, tipo,
        comisionvendedor, controlarseries, es_venta, es_insumo, es_preparado, merma_porcentaje
    )
    VALUES
        (v_codfamilia, v_codlinea_alcohol, v_codmarca, 1, 'BAR-PISCO', 'BAR PISCO QUEBRANTA 750ML', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 1, 0, 0),
        (v_codfamilia, v_codlinea_alcohol, v_codmarca, 1, 'BAR-RON', 'BAR RON RUBIO 750ML', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 1, 0, 0),
        (v_codfamilia, v_codlinea_sin_alcohol, v_codmarca, 1, 'BAR-COLA', 'BAR GASEOSA COLA 1.5L', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 1, 0, 0),
        (v_codfamilia, v_codlinea_sin_alcohol, v_codmarca, 1, 'BAR-LIMON', 'BAR LIMON KG', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 1, 0, -10),
        (v_codfamilia, v_codlinea_sin_alcohol, v_codmarca, 1, 'BAR-HIELO', 'BAR HIELO KG', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 1, 0, -5),
        (v_codfamilia, v_codlinea_sin_alcohol, v_codmarca, 1, 'BAR-AZUCAR', 'BAR JARABE SIMPLE LITRO', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 1, 0, 0),
        (v_codfamilia, v_codlinea_otros, v_codmarca, 1, 'BAR-PAPA', 'BAR PAPA KG', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 1, 0, -18),
        (v_codfamilia, v_codlinea_otros, v_codmarca, 1, 'BAR-QUESO', 'BAR QUESO FRESCO KG', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 1, 0, 0),
        (v_codfamilia, v_codlinea_otros, v_codmarca, 1, 'BAR-AJI', 'BAR AJI AMARILLO KG', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 1, 0, -12),
        (v_codfamilia, v_codlinea_otros, v_codmarca, 1, 'BAR-LECHE', 'BAR LECHE EVAPORADA LITRO', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 1, 0, 0),
        (v_codfamilia, v_codlinea_alcohol, v_codmarca, 1, 'BAR-CHILCANO', 'BAR CHILCANO CLASICO', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 0, 1, 0),
        (v_codfamilia, v_codlinea_alcohol, v_codmarca, 1, 'BAR-CUBA', 'BAR CUBA LIBRE', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 0, 1, 0),
        (v_codfamilia, v_codlinea_otros, v_codmarca, 1, 'BAR-PAPA-HUANC', 'BAR PAPA A LA HUANCAINA', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 0, 1, 0)
    ON CONFLICT DO NOTHING;

    INSERT INTO almacen.productounidades (
        codproducto, codunidad, codsucursal, factor, preciocompra,
        pventapublico, pventamin, pventacredito, pventaxmayor, pventaadicional,
        preciocosto, gastos, codigobarra, estado
    )
    SELECT p.codproducto,
        CASE
            WHEN p.codigo IN ('BAR-PISCO', 'BAR-RON', 'BAR-COLA') THEN v_botella
            WHEN p.codigo IN ('BAR-AZUCAR', 'BAR-LECHE') THEN v_litro
            WHEN p.codigo IN ('BAR-CHILCANO', 'BAR-CUBA', 'BAR-PAPA-HUANC') THEN v_unidad
            ELSE v_kg
        END,
        v_codsucursal,
        1,
        CASE p.codigo
            WHEN 'BAR-PISCO' THEN 35 WHEN 'BAR-RON' THEN 32 WHEN 'BAR-COLA' THEN 7
            WHEN 'BAR-LIMON' THEN 5 WHEN 'BAR-HIELO' THEN 2 WHEN 'BAR-AZUCAR' THEN 6
            WHEN 'BAR-PAPA' THEN 3 WHEN 'BAR-QUESO' THEN 18 WHEN 'BAR-AJI' THEN 10
            WHEN 'BAR-LECHE' THEN 8 WHEN 'BAR-CHILCANO' THEN 6.95
            WHEN 'BAR-CUBA' THEN 5.76 WHEN 'BAR-PAPA-HUANC' THEN 3.96 ELSE 0 END,
        CASE p.codigo WHEN 'BAR-CHILCANO' THEN 18 WHEN 'BAR-CUBA' THEN 16 WHEN 'BAR-PAPA-HUANC' THEN 12 ELSE 0 END,
        CASE p.codigo WHEN 'BAR-CHILCANO' THEN 18 WHEN 'BAR-CUBA' THEN 16 WHEN 'BAR-PAPA-HUANC' THEN 12 ELSE 0 END,
        CASE p.codigo WHEN 'BAR-CHILCANO' THEN 18 WHEN 'BAR-CUBA' THEN 16 WHEN 'BAR-PAPA-HUANC' THEN 12 ELSE 0 END,
        CASE p.codigo WHEN 'BAR-CHILCANO' THEN 18 WHEN 'BAR-CUBA' THEN 16 WHEN 'BAR-PAPA-HUANC' THEN 12 ELSE 0 END,
        0,
        CASE p.codigo
            WHEN 'BAR-PISCO' THEN 35 WHEN 'BAR-RON' THEN 32 WHEN 'BAR-COLA' THEN 7
            WHEN 'BAR-LIMON' THEN 5 WHEN 'BAR-HIELO' THEN 2 WHEN 'BAR-AZUCAR' THEN 6
            WHEN 'BAR-PAPA' THEN 3 WHEN 'BAR-QUESO' THEN 18 WHEN 'BAR-AJI' THEN 10
            WHEN 'BAR-LECHE' THEN 8 WHEN 'BAR-CHILCANO' THEN 6.95
            WHEN 'BAR-CUBA' THEN 5.76 WHEN 'BAR-PAPA-HUANC' THEN 3.96 ELSE 0 END,
        0, p.codigo, 1
    FROM almacen.productos p
    WHERE p.codigo LIKE 'BAR-%'
    ON CONFLICT (codproducto, codunidad) DO UPDATE SET
        preciocompra = EXCLUDED.preciocompra,
        preciocosto = EXCLUDED.preciocosto,
        pventapublico = EXCLUDED.pventapublico,
        pventamin = EXCLUDED.pventamin,
        pventacredito = EXCLUDED.pventacredito,
        pventaxmayor = EXCLUDED.pventaxmayor,
        estado = 1;

    INSERT INTO almacen.productoubicacion (
        codalmacen, codproducto, codunidad, codsucursal, stockactual, stockactualreal,
        preciostockvalorizado, ventarecogo, comprarecogo, stockminimo, stockmaximo,
        estado, stockactualconvertido, factor, preciocompra, pventapublico, pventamin,
        pventacredito, pventaxmayor, pventaadicional, preciocosto, gastos, codigobarra
    )
    SELECT v_codalmacen, pu.codproducto, pu.codunidad, v_codsucursal,
        CASE WHEN p.es_insumo = 1 THEN 50 ELSE 0 END,
        CASE WHEN p.es_insumo = 1 THEN 50 ELSE 0 END,
        pu.preciocosto, 0, 0, 2, 200, 1,
        CASE WHEN p.es_insumo = 1 THEN 50 ELSE 0 END,
        1, pu.preciocompra, pu.pventapublico, pu.pventamin, pu.pventacredito,
        pu.pventaxmayor, pu.pventaadicional, pu.preciocosto, pu.gastos, pu.codigobarra
    FROM almacen.productounidades pu
    INNER JOIN almacen.productos p ON p.codproducto = pu.codproducto
    WHERE p.codigo LIKE 'BAR-%'
    ON CONFLICT (codalmacen, codproducto, codunidad) DO UPDATE SET
        stockactual = EXCLUDED.stockactual,
        stockactualreal = EXCLUDED.stockactualreal,
        stockactualconvertido = EXCLUDED.stockactualconvertido,
        preciocompra = EXCLUDED.preciocompra,
        preciocosto = EXCLUDED.preciocosto,
        pventapublico = EXCLUDED.pventapublico,
        pventamin = EXCLUDED.pventamin,
        pventacredito = EXCLUDED.pventacredito,
        pventaxmayor = EXCLUDED.pventaxmayor,
        estado = 1;

    DELETE FROM restaurante.recetas
    WHERE codproducto IN (SELECT codproducto FROM almacen.productos WHERE codigo IN ('BAR-CHILCANO', 'BAR-CUBA', 'BAR-PAPA-HUANC'));

    INSERT INTO restaurante.recetas (codproducto, codunidad, item, codproducto_receta, codunidad_receta, cantidad, estado)
    SELECT plato.codproducto, v_unidad, d.item, ins.codproducto, d.codunidad, d.cantidad, 1
    FROM (
        VALUES
        ('BAR-CHILCANO', 1, 'BAR-PISCO', v_botella, 0.18),
        ('BAR-CHILCANO', 2, 'BAR-LIMON', v_kg, 0.08),
        ('BAR-CHILCANO', 3, 'BAR-HIELO', v_kg, 0.25),
        ('BAR-CHILCANO', 4, 'BAR-AZUCAR', v_litro, 0.03),
        ('BAR-CUBA', 1, 'BAR-RON', v_botella, 0.16),
        ('BAR-CUBA', 2, 'BAR-COLA', v_botella, 0.30),
        ('BAR-CUBA', 3, 'BAR-LIMON', v_kg, 0.04),
        ('BAR-CUBA', 4, 'BAR-HIELO', v_kg, 0.25),
        ('BAR-PAPA-HUANC', 1, 'BAR-PAPA', v_kg, 0.25),
        ('BAR-PAPA-HUANC', 2, 'BAR-QUESO', v_kg, 0.08),
        ('BAR-PAPA-HUANC', 3, 'BAR-AJI', v_kg, 0.03),
        ('BAR-PAPA-HUANC', 4, 'BAR-LECHE', v_litro, 0.06)
    ) AS d(codigo_plato, item, codigo_insumo, codunidad, cantidad)
    INNER JOIN almacen.productos plato ON plato.codigo = d.codigo_plato
    INNER JOIN almacen.productos ins ON ins.codigo = d.codigo_insumo;

    DELETE FROM kardex.kardexdetalle
    WHERE codkardex IN (SELECT codkardex FROM kardex.kardex WHERE seriecomprobante = 'BSE1' AND descripcion = 'SEED BAR PRUEBAS');
    DELETE FROM kardex.kardex
    WHERE seriecomprobante = 'BSE1' AND descripcion = 'SEED BAR PRUEBAS';

    INSERT INTO kardex.kardex (
        codsucursal, codalmacen, codpersona, codusuario, codmovimientotipo,
        condicionpago, codmoneda, tipocambio, fechacomprobante, fechakardex,
        codcomprobantetipo, seriecomprobante, nrocomprobante,
        valorventa, porcigv, igv, importe, descripcion, estado
    )
    VALUES (v_codsucursal, v_codalmacen, v_codproveedor, v_codusuario, 2, 1, 1, 1, current_date - 15, current_date - 15, 25, 'BSE1', '000001', 125.00, 0, 0, 125.00, 'SEED BAR PRUEBAS', 1)
    RETURNING codkardex INTO v_codkardex;

    INSERT INTO kardex.kardexdetalle (codkardex, codproducto, codunidad, item, cantidad, preciounitario, subtotal, igv, estado, preciocompra, preciocosto, factor)
    SELECT v_codkardex, p.codproducto, pu.codunidad, row_number() OVER (ORDER BY p.codigo), d.cantidad, d.precio, d.cantidad * d.precio, 0, 1, d.precio, d.precio, 1
    FROM (
        VALUES ('BAR-PISCO', 2, 34.00), ('BAR-RON', 2, 31.00), ('BAR-LIMON', 10, 4.50), ('BAR-HIELO', 20, 1.80), ('BAR-PAPA', 25, 2.80)
    ) AS d(codigo, cantidad, precio)
    INNER JOIN almacen.productos p ON p.codigo = d.codigo
    INNER JOIN almacen.productounidades pu ON pu.codproducto = p.codproducto;

    INSERT INTO kardex.kardex (
        codsucursal, codalmacen, codpersona, codusuario, codmovimientotipo,
        condicionpago, codmoneda, tipocambio, fechacomprobante, fechakardex,
        codcomprobantetipo, seriecomprobante, nrocomprobante,
        valorventa, porcigv, igv, importe, descripcion, estado
    )
    VALUES (v_codsucursal, v_codalmacen, v_codproveedor, v_codusuario, 2, 1, 1, 1, current_date - 3, current_date - 3, 25, 'BSE1', '000002', 148.00, 0, 0, 148.00, 'SEED BAR PRUEBAS', 1)
    RETURNING codkardex INTO v_codkardex;

    INSERT INTO kardex.kardexdetalle (codkardex, codproducto, codunidad, item, cantidad, preciounitario, subtotal, igv, estado, preciocompra, preciocosto, factor)
    SELECT v_codkardex, p.codproducto, pu.codunidad, row_number() OVER (ORDER BY p.codigo), d.cantidad, d.precio, d.cantidad * d.precio, 0, 1, d.precio, d.precio, 1
    FROM (
        VALUES ('BAR-PISCO', 2, 36.00), ('BAR-RON', 2, 33.50), ('BAR-LIMON', 10, 5.20), ('BAR-HIELO', 20, 2.00), ('BAR-PAPA', 25, 3.10)
    ) AS d(codigo, cantidad, precio)
    INNER JOIN almacen.productos p ON p.codigo = d.codigo
    INNER JOIN almacen.productounidades pu ON pu.codproducto = p.codproducto;
END $$;

-- Validacion rapida:
-- SELECT codigo, descripcion, es_venta, es_insumo, es_preparado, merma_porcentaje
-- FROM almacen.productos WHERE codigo LIKE 'BAR-%' ORDER BY codigo;
-- SELECT plato.codigo AS plato, ins.codigo AS insumo, r.cantidad
-- FROM restaurante.recetas r
-- JOIN almacen.productos plato ON plato.codproducto = r.codproducto
-- JOIN almacen.productos ins ON ins.codproducto = r.codproducto_receta
-- WHERE plato.codigo LIKE 'BAR-%'
-- ORDER BY plato.codigo, r.item;
-- SELECT k.fechacomprobante, p.codigo, kd.preciounitario
-- FROM kardex.kardex k
-- JOIN kardex.kardexdetalle kd ON kd.codkardex = k.codkardex
-- JOIN almacen.productos p ON p.codproducto = kd.codproducto
-- WHERE k.seriecomprobante = 'BSE1'
-- ORDER BY k.fechacomprobante, p.codigo;
