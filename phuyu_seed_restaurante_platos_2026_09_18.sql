-- ============================================================
-- PHUYU COMERCIAL - SEED RESTAURANTE PLATOS / COMBINADOS
-- Fecha: 2026-09-18
-- Validado: idempotente; puede ejecutarse mas de una vez.
-- Que hace:
-- - Crea insumos con prefijo REST-*.
-- - Crea platos base con receta: ceviche, tallarin, papa a la huancaina, lomo saltado.
-- - Crea combinados nortenos por precio: S/ 15, S/ 20, S/ 25.
-- - Los combinados usan platos base como subrecetas/porciones.
-- Nota: data demo para base de pruebas. No correr en clientes reales.
-- ============================================================

DO $$
DECLARE
    v_codfamilia integer := 6; -- OTROS
    v_codlinea integer := 3; -- OTROS
    v_codmarca integer := 1; -- GENERICO
    v_codsucursal integer := 1;
    v_codalmacen integer := 1;
    v_unidad integer := 1;
    v_kg integer := 24;
    v_litro integer := 29;
BEGIN
    -- Insumos
    INSERT INTO almacen.productos (
        codfamilia, codlinea, codmarca, codempresa, codigo, descripcion,
        afectoicbper, controlstock, afectoigvcompra, afectoigvventa,
        foto, estado, calcular, paraventa, codatencion, caracteristicas,
        codoficial, sexo, codmodelo, codcolor, codtalla, tipo,
        comisionvendedor, controlarseries, es_venta, es_insumo, es_preparado, merma_porcentaje
    )
    VALUES
        (v_codfamilia, v_codlinea, v_codmarca, 1, 'REST-PESCADO', 'REST PESCADO FRESCO KG', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 1, 0, -8),
        (v_codfamilia, v_codlinea, v_codmarca, 1, 'REST-LIMON', 'REST LIMON KG', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 1, 0, -12),
        (v_codfamilia, v_codlinea, v_codmarca, 1, 'REST-CEBOLLA', 'REST CEBOLLA ROJA KG', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 1, 0, -10),
        (v_codfamilia, v_codlinea, v_codmarca, 1, 'REST-CAMOTE', 'REST CAMOTE KG', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 1, 0, -15),
        (v_codfamilia, v_codlinea, v_codmarca, 1, 'REST-CHOCLO', 'REST CHOCLO KG', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 1, 0, -5),
        (v_codfamilia, v_codlinea, v_codmarca, 1, 'REST-CULANTRO', 'REST CULANTRO KG', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 1, 0, -20),
        (v_codfamilia, v_codlinea, v_codmarca, 1, 'REST-AJI-LIMO', 'REST AJI LIMO KG', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 1, 0, -15),
        (v_codfamilia, v_codlinea, v_codmarca, 1, 'REST-FIDEO', 'REST FIDEO TALLARIN KG', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 1, 0, 120),
        (v_codfamilia, v_codlinea, v_codmarca, 1, 'REST-POLLO', 'REST POLLO KG', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 1, 0, -8),
        (v_codfamilia, v_codlinea, v_codmarca, 1, 'REST-TOMATE', 'REST TOMATE KG', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 1, 0, -10),
        (v_codfamilia, v_codlinea, v_codmarca, 1, 'REST-ZANAHORIA', 'REST ZANAHORIA KG', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 1, 0, -8),
        (v_codfamilia, v_codlinea, v_codmarca, 1, 'REST-SILLAO', 'REST SILLAO LITRO', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 1, 0, 0),
        (v_codfamilia, v_codlinea, v_codmarca, 1, 'REST-PAPA', 'REST PAPA BLANCA KG', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 1, 0, -18),
        (v_codfamilia, v_codlinea, v_codmarca, 1, 'REST-AJI-AMARILLO', 'REST AJI AMARILLO KG', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 1, 0, -12),
        (v_codfamilia, v_codlinea, v_codmarca, 1, 'REST-QUESO', 'REST QUESO FRESCO KG', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 1, 0, 0),
        (v_codfamilia, v_codlinea, v_codmarca, 1, 'REST-LECHE', 'REST LECHE EVAPORADA LITRO', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 1, 0, 0),
        (v_codfamilia, v_codlinea, v_codmarca, 1, 'REST-GALLETA', 'REST GALLETA SALADA KG', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 1, 0, 0),
        (v_codfamilia, v_codlinea, v_codmarca, 1, 'REST-HUEVO', 'REST HUEVO UNIDAD', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 1, 0, 0),
        (v_codfamilia, v_codlinea, v_codmarca, 1, 'REST-ACEITE', 'REST ACEITE LITRO', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 1, 0, 0),
        (v_codfamilia, v_codlinea, v_codmarca, 1, 'REST-CARNE', 'REST CARNE DE RES KG', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 1, 0, -7),
        (v_codfamilia, v_codlinea, v_codmarca, 1, 'REST-ARROZ', 'REST ARROZ KG', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 1, 0, 200)
    ON CONFLICT DO NOTHING;

    -- Platos base y combinados
    INSERT INTO almacen.productos (
        codfamilia, codlinea, codmarca, codempresa, codigo, descripcion,
        afectoicbper, controlstock, afectoigvcompra, afectoigvventa,
        foto, estado, calcular, paraventa, codatencion, caracteristicas,
        codoficial, sexo, codmodelo, codcolor, codtalla, tipo,
        comisionvendedor, controlarseries, es_venta, es_insumo, es_preparado, merma_porcentaje
    )
    VALUES
        (v_codfamilia, v_codlinea, v_codmarca, 1, 'REST-CEVICHE', 'CEVICHE CLASICO', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 0, 1, 0),
        (v_codfamilia, v_codlinea, v_codmarca, 1, 'REST-TALLARIN', 'TALLARIN SALTADO', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 0, 1, 0),
        (v_codfamilia, v_codlinea, v_codmarca, 1, 'REST-PAPA-HUANC', 'PAPA A LA HUANCAINA', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 0, 1, 0),
        (v_codfamilia, v_codlinea, v_codmarca, 1, 'REST-LOMO', 'LOMO SALTADO', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 0, 1, 0),
        (v_codfamilia, v_codlinea, v_codmarca, 1, 'REST-COMB-NORTE-15', 'COMBINADO NORTE S/ 15', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 0, 1, 0),
        (v_codfamilia, v_codlinea, v_codmarca, 1, 'REST-COMB-NORTE-20', 'COMBINADO NORTENO 1 S/ 20', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 0, 1, 0),
        (v_codfamilia, v_codlinea, v_codmarca, 1, 'REST-COMB-NORTE-25', 'COMBINADO NORTENO 2 S/ 25', 0, 1, 1, 0, 'default.png', 1, 0, 1, 0, '', '', 0, 0, 0, 0, 1, 0, 0, 1, 0, 1, 0)
    ON CONFLICT DO NOTHING;

    -- Unidades/precios de productos e insumos
    INSERT INTO almacen.productounidades (
        codproducto, codunidad, codsucursal, factor, preciocompra,
        pventapublico, pventamin, pventacredito, pventaxmayor, pventaadicional,
        preciocosto, gastos, codigobarra, estado
    )
    SELECT p.codproducto,
        CASE
            WHEN p.codigo IN ('REST-LECHE','REST-SILLAO','REST-ACEITE') THEN v_litro
            WHEN p.codigo IN ('REST-HUEVO','REST-CEVICHE','REST-TALLARIN','REST-PAPA-HUANC','REST-LOMO','REST-COMB-NORTE-15','REST-COMB-NORTE-20','REST-COMB-NORTE-25') THEN v_unidad
            ELSE v_kg
        END,
        v_codsucursal,
        1,
        CASE p.codigo
            WHEN 'REST-PESCADO' THEN 18 WHEN 'REST-LIMON' THEN 5 WHEN 'REST-CEBOLLA' THEN 4 WHEN 'REST-CAMOTE' THEN 3
            WHEN 'REST-CHOCLO' THEN 4 WHEN 'REST-CULANTRO' THEN 8 WHEN 'REST-AJI-LIMO' THEN 12 WHEN 'REST-FIDEO' THEN 7
            WHEN 'REST-POLLO' THEN 10 WHEN 'REST-TOMATE' THEN 4 WHEN 'REST-ZANAHORIA' THEN 3 WHEN 'REST-SILLAO' THEN 8
            WHEN 'REST-PAPA' THEN 3 WHEN 'REST-AJI-AMARILLO' THEN 10 WHEN 'REST-QUESO' THEN 18 WHEN 'REST-LECHE' THEN 8
            WHEN 'REST-GALLETA' THEN 12 WHEN 'REST-HUEVO' THEN 0.60 WHEN 'REST-ACEITE' THEN 9 WHEN 'REST-CARNE' THEN 28
            WHEN 'REST-ARROZ' THEN 4.20
            WHEN 'REST-CEVICHE' THEN 8.50 WHEN 'REST-TALLARIN' THEN 6.80 WHEN 'REST-PAPA-HUANC' THEN 3.80 WHEN 'REST-LOMO' THEN 11.80
            WHEN 'REST-COMB-NORTE-15' THEN 6.20 WHEN 'REST-COMB-NORTE-20' THEN 8.60 WHEN 'REST-COMB-NORTE-25' THEN 11.50
            ELSE 0 END,
        CASE p.codigo
            WHEN 'REST-CEVICHE' THEN 18 WHEN 'REST-TALLARIN' THEN 15 WHEN 'REST-PAPA-HUANC' THEN 10 WHEN 'REST-LOMO' THEN 22
            WHEN 'REST-COMB-NORTE-15' THEN 15 WHEN 'REST-COMB-NORTE-20' THEN 20 WHEN 'REST-COMB-NORTE-25' THEN 25
            ELSE 0 END,
        CASE p.codigo
            WHEN 'REST-CEVICHE' THEN 18 WHEN 'REST-TALLARIN' THEN 15 WHEN 'REST-PAPA-HUANC' THEN 10 WHEN 'REST-LOMO' THEN 22
            WHEN 'REST-COMB-NORTE-15' THEN 15 WHEN 'REST-COMB-NORTE-20' THEN 20 WHEN 'REST-COMB-NORTE-25' THEN 25
            ELSE 0 END,
        CASE p.codigo
            WHEN 'REST-CEVICHE' THEN 18 WHEN 'REST-TALLARIN' THEN 15 WHEN 'REST-PAPA-HUANC' THEN 10 WHEN 'REST-LOMO' THEN 22
            WHEN 'REST-COMB-NORTE-15' THEN 15 WHEN 'REST-COMB-NORTE-20' THEN 20 WHEN 'REST-COMB-NORTE-25' THEN 25
            ELSE 0 END,
        CASE p.codigo
            WHEN 'REST-CEVICHE' THEN 18 WHEN 'REST-TALLARIN' THEN 15 WHEN 'REST-PAPA-HUANC' THEN 10 WHEN 'REST-LOMO' THEN 22
            WHEN 'REST-COMB-NORTE-15' THEN 15 WHEN 'REST-COMB-NORTE-20' THEN 20 WHEN 'REST-COMB-NORTE-25' THEN 25
            ELSE 0 END,
        0,
        CASE p.codigo
            WHEN 'REST-PESCADO' THEN 18 WHEN 'REST-LIMON' THEN 5 WHEN 'REST-CEBOLLA' THEN 4 WHEN 'REST-CAMOTE' THEN 3
            WHEN 'REST-CHOCLO' THEN 4 WHEN 'REST-CULANTRO' THEN 8 WHEN 'REST-AJI-LIMO' THEN 12 WHEN 'REST-FIDEO' THEN 7
            WHEN 'REST-POLLO' THEN 10 WHEN 'REST-TOMATE' THEN 4 WHEN 'REST-ZANAHORIA' THEN 3 WHEN 'REST-SILLAO' THEN 8
            WHEN 'REST-PAPA' THEN 3 WHEN 'REST-AJI-AMARILLO' THEN 10 WHEN 'REST-QUESO' THEN 18 WHEN 'REST-LECHE' THEN 8
            WHEN 'REST-GALLETA' THEN 12 WHEN 'REST-HUEVO' THEN 0.60 WHEN 'REST-ACEITE' THEN 9 WHEN 'REST-CARNE' THEN 28
            WHEN 'REST-ARROZ' THEN 4.20
            WHEN 'REST-CEVICHE' THEN 8.50 WHEN 'REST-TALLARIN' THEN 6.80 WHEN 'REST-PAPA-HUANC' THEN 3.80 WHEN 'REST-LOMO' THEN 11.80
            WHEN 'REST-COMB-NORTE-15' THEN 6.20 WHEN 'REST-COMB-NORTE-20' THEN 8.60 WHEN 'REST-COMB-NORTE-25' THEN 11.50
            ELSE 0 END,
        0, p.codigo, 1
    FROM almacen.productos p
    WHERE p.codigo LIKE 'REST-%'
    ON CONFLICT (codproducto, codunidad) DO UPDATE SET
        preciocompra = EXCLUDED.preciocompra,
        preciocosto = EXCLUDED.preciocosto,
        pventapublico = EXCLUDED.pventapublico,
        pventamin = EXCLUDED.pventamin,
        pventacredito = EXCLUDED.pventacredito,
        pventaxmayor = EXCLUDED.pventaxmayor,
        codigobarra = EXCLUDED.codigobarra,
        estado = 1;

    INSERT INTO almacen.productoubicacion (
        codalmacen, codproducto, codunidad, codsucursal, stockactual, stockactualreal,
        preciostockvalorizado, ventarecogo, comprarecogo, stockminimo, stockmaximo,
        estado, stockactualconvertido, factor, preciocompra, pventapublico, pventamin,
        pventacredito, pventaxmayor, pventaadicional, preciocosto, gastos, codigobarra
    )
    SELECT v_codalmacen, pu.codproducto, pu.codunidad, v_codsucursal,
        CASE WHEN p.es_insumo = 1 THEN 80 ELSE 0 END,
        CASE WHEN p.es_insumo = 1 THEN 80 ELSE 0 END,
        pu.preciocosto, 0, 0, 2, 300, 1,
        CASE WHEN p.es_insumo = 1 THEN 80 ELSE 0 END,
        1, pu.preciocompra, pu.pventapublico, pu.pventamin, pu.pventacredito,
        pu.pventaxmayor, pu.pventaadicional, pu.preciocosto, pu.gastos, pu.codigobarra
    FROM almacen.productounidades pu
    JOIN almacen.productos p ON p.codproducto = pu.codproducto
    WHERE p.codigo LIKE 'REST-%'
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
    WHERE codproducto IN (SELECT codproducto FROM almacen.productos WHERE codigo IN (
        'REST-CEVICHE','REST-TALLARIN','REST-PAPA-HUANC','REST-LOMO',
        'REST-COMB-NORTE-15','REST-COMB-NORTE-20','REST-COMB-NORTE-25'
    ));

    INSERT INTO restaurante.recetas (codproducto, codunidad, item, codproducto_receta, codunidad_receta, cantidad, estado)
    SELECT plato.codproducto, v_unidad, d.item, ins.codproducto, d.codunidad, d.cantidad, 1
    FROM (
        VALUES
        -- Ceviche clasico
        ('REST-CEVICHE', 1, 'REST-PESCADO', v_kg, 0.220),
        ('REST-CEVICHE', 2, 'REST-LIMON', v_kg, 0.120),
        ('REST-CEVICHE', 3, 'REST-CEBOLLA', v_kg, 0.080),
        ('REST-CEVICHE', 4, 'REST-CAMOTE', v_kg, 0.100),
        ('REST-CEVICHE', 5, 'REST-CHOCLO', v_kg, 0.080),
        ('REST-CEVICHE', 6, 'REST-CULANTRO', v_kg, 0.010),
        ('REST-CEVICHE', 7, 'REST-AJI-LIMO', v_kg, 0.010),
        -- Tallarin saltado
        ('REST-TALLARIN', 1, 'REST-FIDEO', v_kg, 0.150),
        ('REST-TALLARIN', 2, 'REST-POLLO', v_kg, 0.100),
        ('REST-TALLARIN', 3, 'REST-CEBOLLA', v_kg, 0.050),
        ('REST-TALLARIN', 4, 'REST-TOMATE', v_kg, 0.060),
        ('REST-TALLARIN', 5, 'REST-ZANAHORIA', v_kg, 0.040),
        ('REST-TALLARIN', 6, 'REST-SILLAO', v_litro, 0.030),
        ('REST-TALLARIN', 7, 'REST-ACEITE', v_litro, 0.020),
        -- Papa a la huancaina
        ('REST-PAPA-HUANC', 1, 'REST-PAPA', v_kg, 0.250),
        ('REST-PAPA-HUANC', 2, 'REST-AJI-AMARILLO', v_kg, 0.030),
        ('REST-PAPA-HUANC', 3, 'REST-QUESO', v_kg, 0.080),
        ('REST-PAPA-HUANC', 4, 'REST-LECHE', v_litro, 0.060),
        ('REST-PAPA-HUANC', 5, 'REST-GALLETA', v_kg, 0.020),
        ('REST-PAPA-HUANC', 6, 'REST-HUEVO', v_unidad, 0.500),
        -- Lomo saltado
        ('REST-LOMO', 1, 'REST-CARNE', v_kg, 0.180),
        ('REST-LOMO', 2, 'REST-PAPA', v_kg, 0.220),
        ('REST-LOMO', 3, 'REST-CEBOLLA', v_kg, 0.070),
        ('REST-LOMO', 4, 'REST-TOMATE', v_kg, 0.070),
        ('REST-LOMO', 5, 'REST-ARROZ', v_kg, 0.090),
        ('REST-LOMO', 6, 'REST-SILLAO', v_litro, 0.030),
        ('REST-LOMO', 7, 'REST-ACEITE', v_litro, 0.040),
        -- Combinados por porcion de platos base
        ('REST-COMB-NORTE-15', 1, 'REST-CEVICHE', v_unidad, 0.350),
        ('REST-COMB-NORTE-15', 2, 'REST-TALLARIN', v_unidad, 0.350),
        ('REST-COMB-NORTE-15', 3, 'REST-PAPA-HUANC', v_unidad, 0.300),
        ('REST-COMB-NORTE-20', 1, 'REST-CEVICHE', v_unidad, 0.450),
        ('REST-COMB-NORTE-20', 2, 'REST-TALLARIN', v_unidad, 0.350),
        ('REST-COMB-NORTE-20', 3, 'REST-PAPA-HUANC', v_unidad, 0.350),
        ('REST-COMB-NORTE-25', 1, 'REST-CEVICHE', v_unidad, 0.500),
        ('REST-COMB-NORTE-25', 2, 'REST-TALLARIN', v_unidad, 0.400),
        ('REST-COMB-NORTE-25', 3, 'REST-PAPA-HUANC', v_unidad, 0.400),
        ('REST-COMB-NORTE-25', 4, 'REST-LOMO', v_unidad, 0.250)
    ) AS d(codigo_plato, item, codigo_insumo, codunidad, cantidad)
    JOIN almacen.productos plato ON plato.codigo = d.codigo_plato
    JOIN almacen.productos ins ON ins.codigo = d.codigo_insumo;
END $$;

-- Validacion rapida:
-- SELECT codigo, descripcion, es_insumo, es_preparado, pventapublico
-- FROM almacen.productos p
-- JOIN almacen.productounidades pu ON pu.codproducto=p.codproducto
-- WHERE codigo LIKE 'REST-%'
-- ORDER BY codigo;
--
-- SELECT plato.codigo AS plato, ins.codigo AS insumo, r.cantidad
-- FROM restaurante.recetas r
-- JOIN almacen.productos plato ON plato.codproducto = r.codproducto
-- JOIN almacen.productos ins ON ins.codproducto = r.codproducto_receta
-- WHERE plato.codigo LIKE 'REST-%'
-- ORDER BY plato.codigo, r.item;
