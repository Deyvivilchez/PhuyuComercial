CREATE OR REPLACE FUNCTION public.f_datosiniciales(ai_codsucursal INTEGER)
RETURNS CHARACTER VARYING
LANGUAGE plpgsql
AS $$
DECLARE
    v_codproducto_alojamiento INTEGER;
    v_codunidad_alojamiento INTEGER;
BEGIN

    TRUNCATE TABLE
        -- NUEVO: HOTEL
        hotel.consumos_habitacion,
        hotel.estadia_cambios_habitacion,
        hotel.estadia_habitaciones,
        hotel.estadia_huespedes,
        hotel.estadias,
        hotel.reserva_habitaciones,
        hotel.reservas,
        hotel.habitacion_caracteristicas,
        hotel.limpieza_habitaciones,
        hotel.mantenimiento_habitaciones,
        hotel.tarifas,
        hotel.temporadas,
        hotel.habitaciones,
        hotel.ambientes,
        hotel.caracteristicas,
        hotel.habitacion_tipos,
        hotel.configuraciones,

        -- NUEVO: SERIES
        almacen.series,

        -- ALMACEN
        almacen.atenciones,
        almacen.clase,
        almacen.familias,
        almacen.guiasr,
        almacen.guiasrdetalle,
        almacen.guiast,
        almacen.guiastdetalle,
        almacen.inventariodetalle,
        almacen.inventarios,
        almacen.kardexguiasr,
        almacen.kardexguiasrdetalle,
        almacen.lineas,
        almacen.lineasxsucursales,
        almacen.marcas,
        almacen.presentacion,
        almacen.principioactivo,
        almacen.productoubicacion,
        almacen.productounidades,
        almacen.productos,
        almacen.vehiculos,
        almacen.vehiculoscategorias,
        almacen.vehiculosmarcas,
        almacen.vehiculosmodelos,

        -- CAJA
        caja.controldiario,
        caja.ctasctes,
        caja.movimientos,
        caja.movimientosdetalle,
        caja.tipocambios,

        -- KARDEX
        kardex.creditos,
        kardex.creditospedidos,
        kardex.creditosproformas,
        kardex.creditosanulados,
        kardex.cuotas,
        kardex.cuotaspedidos,
        kardex.cuotasproformas,
        kardex.cuotaspagos,
        kardex.kardex,
        kardex.kardexalmacen,
        kardex.kardexalmacenanulado,
        kardex.kardexalmacendetalle,
        kardex.kardexanulados,
        kardex.kardexdetalle,
        kardex.kardexpedido,
        kardex.kardexproforma,
        kardex.pedidos,
        kardex.pedidosdetalle,
        kardex.proformas,
        kardex.proformasdetalle,

        -- PUBLIC
        public.lotes,
        public.zonas,

        -- RESTAURANTE
        restaurante.ambientes,
        restaurante.mesas,
        restaurante.mesaskardex,
        restaurante.mesaspedido,
        restaurante.recetas,

        -- SUNAT DOCUMENTOS
        sunat.guiasunat,
        sunat.kardexsunat,
        sunat.kardexsunatanulados,
        sunat.kardexsunatdetalle,
        sunat.resumenes,

        -- NUEVO: PROGRAMACION SUNAT / CPE
        sunat.programacion_cpe_logs,
        sunat.programacion_cpe_cola,
        sunat.programacion_cpe_historial,
        sunat.programacion_cpe_horarios,
        sunat.programacion_cpe

    RESTART IDENTITY CASCADE;

    UPDATE caja.comprobantes
    SET nrocorrelativo = 0,
        nroinicial = 1;

    DELETE FROM seguridad.sucursalusuarios
    WHERE codusuario > 1;

    DELETE FROM seguridad.usuarios
    WHERE codusuario > 1;

    ALTER SEQUENCE seguridad.usuarios_codusuario_seq RESTART WITH 2;

    DELETE FROM public.empleados
    WHERE codpersona > 2;

    DELETE FROM public.socios
    WHERE codpersona > 2;

    DELETE FROM public.personas
    WHERE codpersona > 2;

    ALTER SEQUENCE public.personas_codpersona_seq RESTART WITH 3;

    -- DATOS BASE ALMACEN

    INSERT INTO almacen.lineas(codlinea, descripcion, abreviatura)
    VALUES (0, 'SIN LINEA', 'S/L')
    ON CONFLICT DO NOTHING;

    INSERT INTO almacen.lineasxsucursales(codlinea, codsucursal)
    VALUES (0, ai_codsucursal)
    ON CONFLICT DO NOTHING;

    INSERT INTO almacen.marcas(codmarca, descripcion)
    VALUES (0, 'SIN MARCA')
    ON CONFLICT DO NOTHING;

    INSERT INTO almacen.familias(codfamilia, descripcion, abreviatura, codlinea)
    VALUES (0, 'SIN FAMILIA', 'S/F', 0)
    ON CONFLICT DO NOTHING;

    INSERT INTO almacen.clase(codclase, descripcion, abreviatura, codlinea, codfamilia)
    VALUES (0, 'SIN CLASE', 'S/C', 0, 0)
    ON CONFLICT DO NOTHING;

    -- ZONA BASE SEGUN SUCURSAL

    INSERT INTO public.zonas(descripcion, codubigeo)
    SELECT ub.distrito, su.codubigeo
    FROM public.sucursales su
    INNER JOIN public.ubigeo ub ON su.codubigeo = ub.codubigeo
    WHERE su.codsucursal = ai_codsucursal
    ON CONFLICT DO NOTHING;

    -- DATOS BASE HOTEL

    INSERT INTO hotel.habitacion_tipos(descripcion, capacidad, preciobase, estado)
    VALUES
        ('SIMPLE', 1, 0, 1),
        ('DOBLE', 2, 0, 1),
        ('MATRIMONIAL', 2, 0, 1)
    ON CONFLICT DO NOTHING;

    INSERT INTO hotel.caracteristicas(descripcion, icono, orden, estado)
    VALUES
        ('AIRE ACONDICIONADO', 'ri-windy-line', 1, 1),
        ('TV', 'ri-tv-line', 2, 1),
        ('WIFI', 'ri-wifi-line', 3, 1),
        ('FRIGOBAR', 'ri-fridge-line', 4, 1),
        ('JACUZZI', 'ri-water-flash-line', 5, 1),
        ('AGUA CALIENTE', 'ri-temp-hot-line', 6, 1),
        ('BANO PRIVADO', 'ri-hotel-bed-line', 7, 1),
        ('CAJA FUERTE', 'ri-safe-2-line', 8, 1)
    ON CONFLICT DO NOTHING;

    INSERT INTO hotel.ambientes(codsucursal, descripcion, aforo, estado)
    VALUES (ai_codsucursal, '1ER PISO', 0, 1)
    ON CONFLICT DO NOTHING;

    -- PRODUCTO ALOJAMIENTO

    SELECT codunidad
    INTO v_codunidad_alojamiento
    FROM almacen.unidades
    WHERE estado = 1
    ORDER BY
        CASE WHEN UPPER(descripcion) LIKE '%UNIDAD%' THEN 0 ELSE 1 END,
        codunidad
    LIMIT 1;

    IF v_codunidad_alojamiento IS NULL THEN
        RAISE EXCEPTION 'No existe unidad activa en almacen.unidades para crear ALOJAMIENTO';
    END IF;

    SELECT setval(
        pg_get_serial_sequence('almacen.productos', 'codproducto'),
        COALESCE((SELECT MAX(codproducto) FROM almacen.productos), 0) + 1,
        false
    );

    INSERT INTO almacen.productos (
        codfamilia,
        codlinea,
        codmarca,
        codempresa,
        codigo,
        descripcion,
        afectoicbper,
        controlstock,
        afectoigvcompra,
        afectoigvventa,
        foto,
        estado,
        calcular,
        paraventa,
        codatencion,
        caracteristicas,
        codoficial,
        sexo,
        codmodelo,
        codcolor,
        codtalla,
        tipo,
        comisionvendedor,
        controlarseries
    )
    VALUES (
        0, 0, 0, 1,
        'HOTEL-ALOJ',
        'ALOJAMIENTO',
        0, 0, 0, 0,
        'default.png',
        1, 0, 1, 0,
        '',
        '',
        0, 0, 0, 0,
        2,
        0,
        0
    )
    RETURNING codproducto INTO v_codproducto_alojamiento;

    INSERT INTO almacen.productounidades (
        codproducto,
        codunidad,
        codsucursal,
        factor,
        preciocompra,
        pventapublico,
        pventamin,
        pventacredito,
        pventaxmayor,
        pventaadicional,
        preciocosto,
        gastos,
        codigobarra,
        estado
    )
    VALUES (
        v_codproducto_alojamiento,
        v_codunidad_alojamiento,
        ai_codsucursal,
        1,
        0, 0, 0, 0, 0, 0, 0, 0,
        'HOTEL-ALOJ',
        1
    )
    ON CONFLICT ON CONSTRAINT pk_productosunidades DO NOTHING;

    INSERT INTO almacen.productoubicacion (
        codalmacen,
        codproducto,
        codunidad,
        codsucursal,
        stockactual,
        stockactualreal,
        preciostockvalorizado,
        ventarecogo,
        comprarecogo,
        stockminimo,
        stockmaximo,
        estado,
        stockactualconvertido,
        factor,
        preciocompra,
        pventapublico,
        pventamin,
        pventacredito,
        pventaxmayor,
        pventaadicional,
        preciocosto,
        gastos,
        codigobarra,
        stockpedido,
        stockproveedor,
        codafectacionigvcompra,
        codafectacionigvventa
    )
    SELECT
        a.codalmacen,
        v_codproducto_alojamiento,
        v_codunidad_alojamiento,
        a.codsucursal,
        0, 0, 0, 0, 0, 0, 0,
        1,
        0,
        1,
        0, 0, 0, 0, 0, 0, 0, 0,
        'HOTEL-ALOJ',
        0,
        0,
        1,
        9
    FROM almacen.almacenes a
    WHERE a.estado = 1
      AND a.codsucursal = ai_codsucursal;

    INSERT INTO hotel.configuraciones (
        codsucursal,
        codproducto_alojamiento,
        codunidad_alojamiento,
        estado
    )
    VALUES (
        ai_codsucursal,
        v_codproducto_alojamiento,
        v_codunidad_alojamiento,
        1
    )
    ON CONFLICT (codsucursal) DO UPDATE
    SET codproducto_alojamiento = EXCLUDED.codproducto_alojamiento,
        codunidad_alojamiento = EXCLUDED.codunidad_alojamiento,
        estado = 1;

    RETURN 'BASE DE DATOS CON DATOS INICIALES';

END;
$$;