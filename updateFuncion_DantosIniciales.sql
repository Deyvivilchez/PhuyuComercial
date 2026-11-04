-- Actualiza la definicion; este archivo NO ejecuta el reinicio.
-- f_datosiniciales limpia datos operativos de TODA la base.
-- ai_codsucursal indica donde crear los datos iniciales, no limita la limpieza.
CREATE OR REPLACE FUNCTION public.f_datosiniciales(ai_codsucursal INTEGER)
RETURNS CHARACTER VARYING
LANGUAGE plpgsql
AS $$
DECLARE
    v_codproducto_alojamiento INTEGER;
    v_codunidad_alojamiento INTEGER;
    v_codcaja INTEGER;
    v_codempresa INTEGER;
    v_tabla TEXT;
    v_columna TEXT;
    v_secuencia TEXT;
    v_siguiente BIGINT;
BEGIN

    IF ai_codsucursal IS NULL THEN
        RAISE EXCEPTION 'Debe indicar una sucursal valida para cargar datos iniciales';
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM public.sucursales s
        WHERE s.codsucursal = ai_codsucursal
          AND s.estado = 1
    ) THEN
        RAISE EXCEPTION 'La sucursal % no existe o no esta activa', ai_codsucursal;
    END IF;

    SELECT s.codempresa INTO v_codempresa
    FROM public.sucursales s
    WHERE s.codsucursal = ai_codsucursal;

    -- Validar lo necesario antes de eliminar registros.
    IF NOT EXISTS (SELECT 1 FROM seguridad.usuarios WHERE codusuario = 1 AND estado = 1) THEN
        RAISE EXCEPTION 'No existe el usuario administrador activo #1';
    END IF;

    IF NOT EXISTS (SELECT 1 FROM almacen.almacenes WHERE codsucursal = ai_codsucursal AND estado = 1) THEN
        RAISE EXCEPTION 'No existe almacen activo para la sucursal %', ai_codsucursal;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM caja.comprobantes WHERE codsucursal = ai_codsucursal AND estado = 1) THEN
        RAISE EXCEPTION 'No existen comprobantes activos para la sucursal %. Configure caja.comprobantes antes de vender', ai_codsucursal;
    END IF;

    TRUNCATE TABLE
        -- NUEVO: HOTEL
        hotel.consumos_habitacion,
        hotel.estadia_cambios_habitacion,
        hotel.estadia_habitaciones,
        hotel.estadia_huespedes,
        hotel.estadia_pagos,
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
        public.usuariozonas,
        public.lotes,
        public.zonas,

        -- RESTAURANTE
        restaurante.atendidos,
        restaurante.ambientes,
        restaurante.mesas,
        restaurante.mesaskardex,
        restaurante.mesaspedido,
        restaurante.recetas_config,
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

    -- Conservar las unidades de medida reales y retirar las unidades basura
    -- que pudieron crearse al interpretar columnas numericas del Excel.
    DELETE FROM almacen.unidades u
    WHERE (
            (u.codunidad > 77 AND trim(u.descripcion) ~ '^[+-]?[0-9]+([.,][0-9]+)?$')
            OR (
                upper(trim(u.descripcion)) = 'UNIDAD'
                AND EXISTS (
                    SELECT 1
                    FROM almacen.unidades ub
                    WHERE ub.codunidad <> u.codunidad
                      AND upper(trim(ub.descripcion)) = 'UNIDAD (BIENES)'
                )
            )
          )
      AND NOT EXISTS (
            SELECT 1
            FROM almacen.productounidades pu
            WHERE pu.codunidad = u.codunidad
          );

    INSERT INTO almacen.unidades(descripcion, oficial, estado)
    SELECT base.descripcion, base.oficial, 1
    FROM (VALUES
        ('UNIDAD (BIENES)', 'NIU'),
        ('BALDE', 'BJ'),
        ('BOLSA', 'BG'),
        ('BOTELLAS', 'BO'),
        ('CAJA', 'BX'),
        ('DOCENA', 'DZN'),
        ('FARDO', 'BE'),
        ('KILOGRAMO', 'KGM'),
        ('LITRO', 'LTR'),
        ('PAQUETE', 'PK'),
        ('FRASCO', 'NIU'),
        ('SACO', 'BG'),
        ('SOBRE', 'NIU'),
        ('ROLLO', 'NIU')
    ) AS base(descripcion, oficial)
    WHERE NOT EXISTS (
        SELECT 1
        FROM almacen.unidades u
        WHERE upper(trim(u.descripcion)) = upper(base.descripcion)
    );

    UPDATE almacen.unidades u
    SET estado = 1
    WHERE upper(trim(u.descripcion)) IN (
        'UNIDAD (BIENES)', 'BALDE', 'BOLSA', 'BOTELLAS', 'CAJA', 'DOCENA',
        'FARDO', 'KILOGRAMO', 'LITRO', 'PAQUETE', 'FRASCO', 'SACO', 'SOBRE', 'ROLLO'
    );

    SELECT codunidad
    INTO v_codunidad_alojamiento
    FROM almacen.unidades
    WHERE estado = 1
      AND upper(trim(descripcion)) = 'UNIDAD (BIENES)'
    ORDER BY codunidad
    LIMIT 1;

    IF v_codunidad_alojamiento IS NULL THEN
        RAISE EXCEPTION 'No existe la unidad base UNIDAD (BIENES) para crear ALOJAMIENTO';
    END IF;

    UPDATE caja.comprobantes
    SET nrocorrelativo = 0,
        nroinicial = 1;

    -- Primero eliminar permisos que dependen de los usuarios a retirar.
    DELETE FROM seguridad.cajausuarios
    WHERE codusuario > 1;

    DELETE FROM seguridad.sucursalusuarios
    WHERE codusuario > 1;

    DELETE FROM seguridad.usuarios
    WHERE codusuario > 1;

    -- Conservar el empleado real del administrador aunque su ID no sea 1 o 2.
    DELETE FROM public.empleados e
    WHERE e.codpersona > 2
      AND NOT EXISTS (SELECT 1 FROM seguridad.usuarios u WHERE u.codempleado = e.codpersona);

    DELETE FROM public.socios
    WHERE codpersona > 2;

    -- Las empresas y el administrador pueden apuntar a personas con IDs mayores a 2.
    DELETE FROM public.personas p
    WHERE p.codpersona > 2
      AND NOT EXISTS (SELECT 1 FROM public.empresas e WHERE e.codpersona = p.codpersona)
      AND NOT EXISTS (SELECT 1 FROM public.empleados e WHERE e.codpersona = p.codpersona);

    -- DATOS BASE CAJA / VENTAS
    -- Esta funcion deja el sistema limpio para iniciar operacion en la nube.
    -- Se siembran configuraciones minimas cuando faltan.
    -- No se crea caja diaria abierta aqui porque eso debe hacerlo el flujo normal de apertura de caja.

    INSERT INTO caja.monedas (codmoneda, descripcion, simbolo, oficial, tipo, estado)
    VALUES
        (1, 'SOLES', 'S/', 'PEM', 1, 1),
        (2, 'DOLAR', '$', 'USD', 2, 1),
        (3, 'EURO', '€', 'EUR', 2, 1)
    ON CONFLICT (codmoneda) DO NOTHING;

    INSERT INTO caja.tipopagos (codtipopago, descripcion, ingreso, egreso, abono, cargo, estado)
    VALUES
        (0, 'CREDITO', NULL, NULL, NULL, NULL, 1),
        (1, 'EFECTIVO', 1, 1, 0, 0, 1),
        (2, 'CHEQUE', 1, 0, 0, 1, 1),
        (3, 'DEPÓSITO', 0, 1, 1, 1, 1),
        (4, 'RETIRO', 0, 0, 0, 0, 1),
        (5, 'TRANSFERENCIA', 1, 1, 1, 1, 1),
        (6, 'MASTERCARD', 1, 0, 0, 0, 1),
        (7, 'VISA', 1, 0, 0, 0, 1),
        (8, 'VALE', 0, 0, 0, 0, 1),
        (9, 'YAPE', 1, 0, 0, 0, 1),
        (10, 'PLIN', 1, 0, 0, 0, 1)
    ON CONFLICT (codtipopago) DO NOTHING;

    SELECT c.codcaja
    INTO v_codcaja
    FROM caja.cajas c
    WHERE c.codsucursal = ai_codsucursal
      AND c.estado = 1
    ORDER BY c.codcaja
    LIMIT 1;

    IF v_codcaja IS NULL THEN
        INSERT INTO caja.cajas (
            codsucursal,
            descripcion,
            direccion,
            telefonos,
            estado,
            saldarautomaticamente
        )
        VALUES (
            ai_codsucursal,
            'CAJA PRINCIPAL',
            '',
            '',
            1,
            0
        )
        RETURNING codcaja INTO v_codcaja;
    END IF;

    -- Mantener acceso del administrador a la sucursal y caja iniciales.
    INSERT INTO seguridad.sucursalusuarios(codsucursal, codusuario)
    VALUES (ai_codsucursal, 1)
    ON CONFLICT DO NOTHING;

    INSERT INTO seguridad.cajausuarios(codusuario, codsucursal, codcaja, estado)
    VALUES (1, ai_codsucursal, v_codcaja, 1)
    ON CONFLICT (codusuario, codsucursal, codcaja) DO UPDATE SET estado = 1;

    -- Los comprobantes no se inventan porque dependen de serie/tipo/documento de la empresa.
    -- Deben existir en la base plantilla o configurarse antes de vender.
    IF NOT EXISTS (
        SELECT 1
        FROM caja.comprobantes c
        WHERE c.codsucursal = ai_codsucursal
          AND c.estado = 1
    ) THEN
        RAISE EXCEPTION 'No existen comprobantes activos para la sucursal %. Configure caja.comprobantes antes de vender', ai_codsucursal;
    END IF;

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
        controlarseries,
        es_venta,
        es_insumo,
        es_preparado,
        merma_porcentaje
    )
    VALUES (
        0, 0, 0, v_codempresa,
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
        0,
        1,
        0,
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
    ON CONFLICT (codproducto, codunidad) DO NOTHING;

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
      AND a.codsucursal = ai_codsucursal
      AND NOT EXISTS (
          SELECT 1
          FROM almacen.productoubicacion pu
          WHERE pu.codalmacen = a.codalmacen
            AND pu.codproducto = v_codproducto_alojamiento
            AND pu.codunidad = v_codunidad_alojamiento
      );

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

    -- VALIDACIONES FINALES PARA VENTAS Y HOTEL
    -- Despues de ejecutar esta funcion, el usuario debe abrir caja desde el sistema.
    -- Si intenta vender sin apertura de caja, el detalle de pago puede fallar.

    IF NOT EXISTS (
        SELECT 1
        FROM almacen.almacenes a
        WHERE a.codsucursal = ai_codsucursal
          AND a.estado = 1
    ) THEN
        RAISE EXCEPTION 'No existe almacen activo para la sucursal %. Configure almacen.almacenes antes de vender', ai_codsucursal;
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM almacen.productoubicacion pu
        WHERE pu.codsucursal = ai_codsucursal
          AND pu.estado = 1
    ) THEN
        RAISE EXCEPTION 'No existen productos ubicados para la sucursal %. Agregue productos/stock antes de vender', ai_codsucursal;
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM hotel.configuraciones hc
        WHERE hc.codsucursal = ai_codsucursal
          AND hc.estado = 1
          AND hc.codproducto_alojamiento > 0
          AND hc.codunidad_alojamiento > 0
    ) THEN
        RAISE EXCEPTION 'No existe configuracion activa de hotel para la sucursal %. Revise hotel.configuraciones', ai_codsucursal;
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM hotel.habitacion_tipos ht
        WHERE ht.estado = 1
    ) THEN
        RAISE EXCEPTION 'No existen tipos de habitacion activos. Revise hotel.habitacion_tipos';
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM hotel.ambientes ha
        WHERE ha.codsucursal = ai_codsucursal
          AND ha.estado = 1
    ) THEN
        RAISE EXCEPTION 'No existe ambiente inicial de hotel para la sucursal %. Revise hotel.ambientes', ai_codsucursal;
    END IF;

    -- ALTER SEQUENCE RESTART es transaccional: un error revierte tambien
    -- los contadores, a diferencia de setval sobre tablas que se conservan.
    FOR v_tabla, v_columna IN
        SELECT * FROM (VALUES
            ('seguridad.usuarios', 'codusuario'),
            ('public.personas', 'codpersona'),
            ('caja.cajas', 'codcaja'),
            ('caja.monedas', 'codmoneda'),
            ('caja.tipopagos', 'codtipopago'),
            ('almacen.unidades', 'codunidad'),
            ('almacen.productos', 'codproducto'),
            ('seguridad.cajausuarios', 'codcajausuario'),
            ('hotel.configuraciones', 'codconfiguracion'),
            ('hotel.habitacion_tipos', 'codhabitaciontipo'),
            ('hotel.caracteristicas', 'codcaracteristica'),
            ('hotel.ambientes', 'codambiente')
        ) AS secuencias(tabla, columna)
    LOOP
        v_secuencia := pg_get_serial_sequence(v_tabla, v_columna);
        IF v_secuencia IS NOT NULL THEN
            EXECUTE format('SELECT COALESCE(MAX(%I), 0) + 1 FROM %s', v_columna, v_tabla::regclass)
            INTO v_siguiente;
            EXECUTE format('ALTER SEQUENCE %s RESTART WITH %s', v_secuencia::regclass, v_siguiente);
        END IF;
    END LOOP;

    RETURN 'BASE DE DATOS CON DATOS INICIALES';

END;
$$;
