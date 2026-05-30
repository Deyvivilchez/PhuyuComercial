-- agregar campos para validad si el producto debe llevar series o no

ALTER TABLE almacen.productos
ADD COLUMN IF NOT EXISTS controlarseries INTEGER NOT NULL DEFAULT 0;

CREATE TABLE IF NOT EXISTS almacen.series (
id_serie SERIAL PRIMARY KEY, -- autoincremental
codproducto INTEGER NOT NULL, -- relación con productos
serie_codigo VARCHAR(120) NOT NULL, -- número de serie o código único
estado VARCHAR(20) NOT NULL DEFAULT 'EN_ALMACEN' CHECK (
    estado IN (
        'EN_ALMACEN',
        'RESERVADO',
        'VENDIDO',
        'DADO_BAJA',
        'EN_PROVEEDOR'
    )
),
comprobante VARCHAR(120), -- doc. de venta o guía (opcional)
fecha_ingreso TIMESTAMPTZ, -- ingreso al almacén
fecha_egreso TIMESTAMPTZ, -- salida (venta/baja)
motivo VARCHAR(200), -- motivo del egreso o cambio de estado
    codalmacen INTEGER,
codkardex INTEGER,
    codkardex_egreso INTEGER,
    codsucursal INTEGER,
CONSTRAINT fk_series_codproducto FOREIGN KEY (codproducto) REFERENCES almacen.productos (codproducto) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT uq_series_producto_serie UNIQUE (codproducto, serie_codigo)
);

-- actualizacion de iconos de modulos de acuerdo a nueva plantilla

UPDATE seguridad.modulos
SET
    icono = CASE codmodulo
        WHEN 1 THEN 'ri-shopping-bag-3-line' -- Ventas
        WHEN 2 THEN 'ri-truck-line' -- Logistica
        WHEN 3 THEN 'ri-store-2-line' -- Almacen
        WHEN 4 THEN 'ri-wallet-3-line' -- Tesoreria
        WHEN 5 THEN 'ri-exchange-dollar-line' -- Creditos
        WHEN 6 THEN 'ri-settings-3-line' -- Administracion
        WHEN 7 THEN 'ri-bar-chart-box-line' -- Reportes
        WHEN 8 THEN 'ri-file-upload-line' -- CPE / envio de facturacion electronica
        WHEN 17 THEN 'ri-restaurant-2-line' -- Restobar
        WHEN 118 THEN 'ri-plant-line' -- Agricola
        WHEN 121 THEN 'ri-bar-chart-box-line' -- Reportes
        WHEN 125 THEN 'ri-exchange-dollar-line' -- Creditos
        ELSE icono
    END
WHERE
    codmodulo IN (
        1,
        2,
        3,
        4,
        5,
        6,
        7,
        8,
        17,
        118,
        121,
        125
    )
    AND codpadre = 0;

-- fin actualizacion de iconos de modulos de acuerdo a nueva plantilla

-- unificacion de modulos duplicados de Reportes y Creditos

UPDATE seguridad.modulos
SET
    descripcion = CASE codmodulo
        WHEN 51 THEN 'Cuentas por cobrar'
        WHEN 52 THEN 'Cuentas por pagar'
        WHEN 53 THEN 'Lista por cobrar'
        WHEN 54 THEN 'Lista por pagar'
        ELSE descripcion
    END
WHERE codmodulo IN (51, 52, 53, 54);

UPDATE seguridad.modulos
SET codpadre = 7,
    orden = CASE codmodulo
WHEN 71 THEN 1 -- Ventas
WHEN 113 THEN 2 -- Pedidos
WHEN 115 THEN 3 -- Proformas
WHEN 77 THEN 4 -- Vendedores
WHEN 72 THEN 5 -- Compras
WHEN 73 THEN 6 -- Productos
WHEN 76 THEN 7 -- Ingresos y salidas de almacen
WHEN 74 THEN 8 -- Caja y bancos
WHEN 75 THEN 9 -- Creditos
        WHEN 117 THEN 10 -- Cuotas
        WHEN 120 THEN 11 -- Prestamos
        WHEN 104 THEN 12 -- Utilidades
        WHEN 114 THEN 13 -- Personas
        ELSE orden
    END
WHERE
    codmodulo IN (
        71,
        113,
        115,
        77,
        72,
        73,
        76,
        74,
        75,
        117,
        120,
        104,
        114
    );

UPDATE seguridad.modulos
SET codpadre = 5,
    orden = CASE codmodulo
WHEN 51 THEN 1 -- Cuentas por cobrar
WHEN 53 THEN 2 -- Lista por cobrar
        WHEN 110 THEN 3 -- Lista de cobranza
WHEN 52 THEN 4 -- Cuentas por pagar
WHEN 54 THEN 5 -- Lista por pagar
        WHEN 111 THEN 6 -- Lista de pagos
        ELSE orden
    END
WHERE codmodulo IN (51, 53, 110, 52, 54, 111);

UPDATE seguridad.modulos
SET
    estado = 0
WHERE
    codmodulo IN (121, 125)
    AND codpadre = 0;

-- fin unificacion de modulos duplicados de Reportes y Creditos

-- horas de apertura y cierre para arqueos de caja

ALTER TABLE caja.controldiario
ADD COLUMN IF NOT EXISTS horaapertura TIME WITHOUT TIME ZONE;

ALTER TABLE caja.controldiario
ALTER COLUMN horaapertura SET DEFAULT CURRENT_TIME;

ALTER TABLE caja.controldiario
ADD COLUMN IF NOT EXISTS horacierre TIME WITHOUT TIME ZONE;

-- fin horas de apertura y cierre para arqueos de caja

-- /usr/bin/php7.4 $(which composer) install

--/usr/bin/php7.4 $(which composer) dump-autoload -o.
---  los comoando s de arriba son para acutlizar el composer y generar el
-- autoload optimizado para que reconozca las nuevas clases de migraciones y se puedan ejecutar sin problemas.

-- /usr/bin/php7.4 $(which composer) install   # para proyectos viejos
-- /usr/bin/php8.2 $(which composer) install   # para proyectos nuevos
-- ============================================================
-- MODULO HOTEL - TABLAS BASE
-- ============================================================

CREATE SCHEMA IF NOT EXISTS hotel;

CREATE TABLE IF NOT EXISTS hotel.habitacion_tipos (
    codhabitaciontipo serial PRIMARY KEY,
    descripcion varchar(120) NOT NULL,
    capacidad integer DEFAULT 1,
    preciobase numeric DEFAULT 0,
    estado integer DEFAULT 1
);

CREATE TABLE IF NOT EXISTS hotel.ambientes (
    codambiente serial PRIMARY KEY,
    codsucursal integer NOT NULL,
    descripcion varchar(120) NOT NULL,
    aforo integer DEFAULT 0,
    estado integer DEFAULT 1
);

CREATE TABLE IF NOT EXISTS hotel.habitaciones (
    codhabitacion serial PRIMARY KEY,
    codsucursal integer NOT NULL,
    codambiente integer NOT NULL DEFAULT 0,
    codhabitaciontipo integer NOT NULL REFERENCES hotel.habitacion_tipos (codhabitaciontipo),
    numero varchar(20) NOT NULL,
    piso varchar(20),
    capacidad integer DEFAULT 1,
    preciobase numeric DEFAULT 0,
    situacion integer DEFAULT 1,
    estado integer DEFAULT 1,
    UNIQUE (codsucursal, numero)
);

ALTER TABLE hotel.habitaciones
ADD COLUMN IF NOT EXISTS codambiente integer NOT NULL DEFAULT 0;

-- ============================================================
-- MODULO HOTEL - CARACTERISTICAS DINAMICAS DE HABITACIONES
-- ============================================================
-- Objetivo:
--   Permitir agregar y asignar caracteristicas como aire acondicionado,
--   TV, WIFI, frigobar, jacuzzi, etc. sin modificar la estructura de
--   hotel.habitaciones.
--
-- Diseno:
--   hotel.caracteristicas              = catalogo reutilizable
--   hotel.habitacion_caracteristicas   = relacion N:N habitacion-caracteristica
--
-- Importante:
--   NO agregar columnas a hotel.habitaciones para cada caracteristica.
--   El formulario de habitaciones y recepcion deben cargar este catalogo.

CREATE TABLE IF NOT EXISTS hotel.caracteristicas (
    codcaracteristica serial PRIMARY KEY,
    descripcion varchar(120) NOT NULL,
    icono varchar(80) DEFAULT 'ri-checkbox-circle-line',
    orden integer DEFAULT 0,
    estado integer DEFAULT 1,
    UNIQUE (descripcion)
);

CREATE TABLE IF NOT EXISTS hotel.habitacion_caracteristicas (
    codhabitacion integer NOT NULL REFERENCES hotel.habitaciones (codhabitacion),
    codcaracteristica integer NOT NULL REFERENCES hotel.caracteristicas (codcaracteristica),
    estado integer DEFAULT 1,
    PRIMARY KEY (
        codhabitacion,
        codcaracteristica
    )
);

CREATE TABLE IF NOT EXISTS hotel.reservas (
    codreserva serial PRIMARY KEY,
    codsucursal integer NOT NULL,
    codpersona integer NOT NULL,
    codusuario integer NOT NULL,
    fechareserva date DEFAULT now(),
    fechallegada date NOT NULL,
    fechasalida date NOT NULL,
    cliente varchar,
    direccion varchar,
    observacion text,
    situacion integer DEFAULT 1,
    estado integer DEFAULT 1
);

CREATE TABLE IF NOT EXISTS hotel.reserva_habitaciones (
    codreserva integer NOT NULL REFERENCES hotel.reservas (codreserva),
    codhabitacion integer NOT NULL REFERENCES hotel.habitaciones (codhabitacion),
    precio numeric DEFAULT 0,
    estado integer DEFAULT 1,
    PRIMARY KEY (codreserva, codhabitacion)
);

CREATE TABLE IF NOT EXISTS hotel.estadias (
    codestadia serial PRIMARY KEY,
    codreserva integer,
    codsucursal integer NOT NULL,
    codalmacen integer NOT NULL,
    codpersona integer NOT NULL,
    codusuario integer NOT NULL,
    codempleado integer DEFAULT 0,
    codkardex integer DEFAULT 0,
    fecha_checkin date NOT NULL,
    hora_checkin time DEFAULT now(),
    fecha_checkout date,
    hora_checkout time,
    noches integer DEFAULT 0,
    alojamiento numeric DEFAULT 0,
    consumos numeric DEFAULT 0,
    importe numeric DEFAULT 0,
    cliente varchar,
    direccion varchar,
    observacion text,
    situacion integer DEFAULT 1,
    estado integer DEFAULT 1
);

CREATE TABLE IF NOT EXISTS hotel.estadia_habitaciones (
    codestadia integer NOT NULL REFERENCES hotel.estadias (codestadia),
    codhabitacion integer NOT NULL REFERENCES hotel.habitaciones (codhabitacion),
    precio_noche numeric DEFAULT 0,
    noches integer DEFAULT 0,
    subtotal numeric DEFAULT 0,
    estado integer DEFAULT 1,
    PRIMARY KEY (codestadia, codhabitacion)
);

CREATE TABLE IF NOT EXISTS hotel.estadia_huespedes (
    codestadia integer NOT NULL REFERENCES hotel.estadias (codestadia),
    codpersona integer NOT NULL,
    titular integer DEFAULT 0,
    estado integer DEFAULT 1,
    PRIMARY KEY (codestadia, codpersona)
);

CREATE TABLE IF NOT EXISTS hotel.estadia_cambios_habitacion (
    codcambio serial PRIMARY KEY,
    codestadia integer NOT NULL REFERENCES hotel.estadias (codestadia),
    codhabitacion_origen integer NOT NULL REFERENCES hotel.habitaciones (codhabitacion),
    codhabitacion_destino integer NOT NULL REFERENCES hotel.habitaciones (codhabitacion),
    codusuario integer NOT NULL,
    fecha date DEFAULT now(),
    hora time DEFAULT now(),
    observacion text,
    estado integer DEFAULT 1
);

CREATE TABLE IF NOT EXISTS hotel.consumos_habitacion (
    codconsumo serial PRIMARY KEY,
    codestadia integer NOT NULL REFERENCES hotel.estadias (codestadia),
    codhabitacion integer NOT NULL REFERENCES hotel.habitaciones (codhabitacion),
    codproducto integer NOT NULL,
    codunidad integer NOT NULL,
    id_serie integer DEFAULT 0,
    item integer NOT NULL,
    cantidad numeric DEFAULT 0,
    preciounitario numeric DEFAULT 0,
    preciosinigv numeric DEFAULT 0,
    preciobruto numeric DEFAULT 0,
    preciorefunitario numeric DEFAULT 0,
    codafectacionigv char(2) DEFAULT '20',
    igv numeric DEFAULT 0,
    valorventa numeric DEFAULT 0,
    subtotal numeric DEFAULT 0,
    descripcion text,
    codkardex integer DEFAULT 0,
    fechafacturado date,
    situacion integer DEFAULT 1,
    estado integer DEFAULT 1
);

ALTER TABLE hotel.consumos_habitacion
ADD COLUMN IF NOT EXISTS codkardex integer DEFAULT 0;

ALTER TABLE hotel.consumos_habitacion
ADD COLUMN IF NOT EXISTS fechafacturado date;

ALTER TABLE hotel.consumos_habitacion
ADD COLUMN IF NOT EXISTS id_serie integer DEFAULT 0;

CREATE TABLE IF NOT EXISTS hotel.temporadas (
    codtemporada serial PRIMARY KEY,
    codsucursal integer NOT NULL,
    descripcion varchar(120) NOT NULL,
    fechadesde date NOT NULL,
    fechahasta date NOT NULL,
    factor numeric DEFAULT 1,
    estado integer DEFAULT 1
);

CREATE TABLE IF NOT EXISTS hotel.tarifas (
    codtarifa serial PRIMARY KEY,
    codsucursal integer NOT NULL,
    codhabitaciontipo integer NOT NULL,
    codtemporada integer,
    precio numeric DEFAULT 0,
    estado integer DEFAULT 1
);

CREATE TABLE IF NOT EXISTS hotel.limpieza_habitaciones (
    codlimpieza serial PRIMARY KEY,
    codhabitacion integer NOT NULL,
    codusuario integer NOT NULL,
    codresponsable integer DEFAULT 0,
    fecha date DEFAULT now(),
    hora time DEFAULT now(),
    tipo_limpieza varchar(40) DEFAULT 'normal',
    checklist text,
    observacion text,
    estado_orden integer DEFAULT 1,
    fecha_fin date,
    hora_fin time,
    estado integer DEFAULT 1
);

ALTER TABLE hotel.limpieza_habitaciones ADD COLUMN IF NOT EXISTS tipo_limpieza varchar(40) DEFAULT 'normal';
ALTER TABLE hotel.limpieza_habitaciones ADD COLUMN IF NOT EXISTS checklist text;
ALTER TABLE hotel.limpieza_habitaciones ADD COLUMN IF NOT EXISTS estado_orden integer DEFAULT 1;
ALTER TABLE hotel.limpieza_habitaciones ADD COLUMN IF NOT EXISTS fecha_fin date;
ALTER TABLE hotel.limpieza_habitaciones ADD COLUMN IF NOT EXISTS hora_fin time;

CREATE TABLE IF NOT EXISTS hotel.mantenimiento_habitaciones (
    codmantenimiento serial PRIMARY KEY,
    codhabitacion integer NOT NULL,
    codusuario integer NOT NULL,
    codresponsable integer DEFAULT 0,
    fecha_inicio date DEFAULT now(),
    fecha_fin date,
    hora_inicio time DEFAULT now(),
    hora_fin time,
    tipo_mantenimiento varchar(40) DEFAULT 'correctivo',
    prioridad varchar(20) DEFAULT 'media',
    descripcion_problema text,
    trabajos_realizados text,
    materiales text,
    observacion text,
    situacion integer DEFAULT 1,
    estado_orden integer DEFAULT 1,
    estado integer DEFAULT 1
);

ALTER TABLE hotel.mantenimiento_habitaciones ADD COLUMN IF NOT EXISTS tipo_mantenimiento varchar(40) DEFAULT 'correctivo';
ALTER TABLE hotel.mantenimiento_habitaciones ADD COLUMN IF NOT EXISTS prioridad varchar(20) DEFAULT 'media';
ALTER TABLE hotel.mantenimiento_habitaciones ADD COLUMN IF NOT EXISTS descripcion_problema text;
ALTER TABLE hotel.mantenimiento_habitaciones ADD COLUMN IF NOT EXISTS trabajos_realizados text;
ALTER TABLE hotel.mantenimiento_habitaciones ADD COLUMN IF NOT EXISTS materiales text;
ALTER TABLE hotel.mantenimiento_habitaciones ADD COLUMN IF NOT EXISTS estado_orden integer DEFAULT 1;
ALTER TABLE hotel.mantenimiento_habitaciones ADD COLUMN IF NOT EXISTS hora_inicio time DEFAULT now();
ALTER TABLE hotel.mantenimiento_habitaciones ADD COLUMN IF NOT EXISTS hora_fin time;

UPDATE hotel.mantenimiento_habitaciones
SET estado_orden = 3
WHERE situacion = 2
  AND COALESCE(estado_orden, 1) = 1;

-- Normaliza reservas antiguas: antes situacion=3 se usaba como cancelada.
-- En el flujo actual 3 significa EN HOSPEDAJE.
UPDATE hotel.reservas r
SET situacion = 0
WHERE r.situacion = 3
  AND NOT EXISTS (
      SELECT 1
      FROM hotel.estadias e
      WHERE e.codreserva = r.codreserva
        AND e.estado = 1
  );

CREATE INDEX IF NOT EXISTS idx_hotel_habitaciones_sucursal_situacion ON hotel.habitaciones (
    codsucursal,
    situacion,
    estado
);

CREATE INDEX IF NOT EXISTS idx_hotel_ambientes_sucursal ON hotel.ambientes (codsucursal, estado);

CREATE INDEX IF NOT EXISTS idx_hotel_habitaciones_ambiente ON hotel.habitaciones (codambiente, estado);

-- Indices para listar y filtrar habitaciones por caracteristicas.
CREATE INDEX IF NOT EXISTS idx_hotel_caracteristicas_estado ON hotel.caracteristicas (estado, orden);

CREATE INDEX IF NOT EXISTS idx_hotel_habitacion_caracteristicas_caracteristica ON hotel.habitacion_caracteristicas (codcaracteristica, estado);

CREATE INDEX IF NOT EXISTS idx_hotel_reservas_fechas ON hotel.reservas (
    codsucursal,
    fechallegada,
    fechasalida,
    situacion,
    estado
);

CREATE INDEX IF NOT EXISTS idx_hotel_estadias_activa ON hotel.estadias (
    codsucursal,
    situacion,
    estado
);

CREATE INDEX IF NOT EXISTS idx_hotel_consumos_estadia ON hotel.consumos_habitacion (codestadia, situacion, estado);

CREATE INDEX IF NOT EXISTS idx_hotel_consumos_kardex ON hotel.consumos_habitacion (codkardex, estado);

CREATE INDEX IF NOT EXISTS idx_hotel_estadia_cambios ON hotel.estadia_cambios_habitacion (codestadia, estado);

-- Fin migraciones para hotel

-- ============================================================
-- MODULO HOTEL - MENU, PERMISOS Y CONFIGURACION BASE
-- ============================================================

CREATE TABLE IF NOT EXISTS hotel.configuraciones (
    codconfiguracion serial PRIMARY KEY,
    codsucursal integer NOT NULL,
    codproducto_alojamiento integer NOT NULL DEFAULT 0,
    codunidad_alojamiento integer NOT NULL DEFAULT 0,
    estado integer NOT NULL DEFAULT 1,
    UNIQUE (codsucursal)
);

INSERT INTO
    hotel.habitacion_tipos (
        descripcion,
        capacidad,
        preciobase,
        estado
    )
SELECT 'SIMPLE', 1, 0, 1
WHERE
    NOT EXISTS (
        SELECT 1
        FROM hotel.habitacion_tipos
        WHERE
            upper(descripcion) = 'SIMPLE'
    );

INSERT INTO
    hotel.habitacion_tipos (
        descripcion,
        capacidad,
        preciobase,
        estado
    )
SELECT 'DOBLE', 2, 0, 1
WHERE
    NOT EXISTS (
        SELECT 1
        FROM hotel.habitacion_tipos
        WHERE
            upper(descripcion) = 'DOBLE'
    );

INSERT INTO
    hotel.habitacion_tipos (
        descripcion,
        capacidad,
        preciobase,
        estado
    )
SELECT 'MATRIMONIAL', 2, 0, 1
WHERE
    NOT EXISTS (
        SELECT 1
        FROM hotel.habitacion_tipos
        WHERE
            upper(descripcion) = 'MATRIMONIAL'
    );

-- Caracteristicas base del hotel.
-- Estos INSERT son idempotentes: pueden ejecutarse varias veces sin duplicar.
-- Para agregar nuevas caracteristicas no se modifica hotel.habitaciones;
-- solo se registra una nueva fila en hotel.caracteristicas.

INSERT INTO
    hotel.caracteristicas (
        descripcion,
        icono,
        orden,
        estado
    )
SELECT 'AIRE ACONDICIONADO', 'ri-windy-line', 1, 1
WHERE
    NOT EXISTS (
        SELECT 1
        FROM hotel.caracteristicas
        WHERE
            upper(descripcion) = 'AIRE ACONDICIONADO'
    );

INSERT INTO
    hotel.caracteristicas (
        descripcion,
        icono,
        orden,
        estado
    )
SELECT 'TV', 'ri-tv-line', 2, 1
WHERE
    NOT EXISTS (
        SELECT 1
        FROM hotel.caracteristicas
        WHERE
            upper(descripcion) = 'TV'
    );

INSERT INTO
    hotel.caracteristicas (
        descripcion,
        icono,
        orden,
        estado
    )
SELECT 'WIFI', 'ri-wifi-line', 3, 1
WHERE
    NOT EXISTS (
        SELECT 1
        FROM hotel.caracteristicas
        WHERE
            upper(descripcion) = 'WIFI'
    );

INSERT INTO
    hotel.caracteristicas (
        descripcion,
        icono,
        orden,
        estado
    )
SELECT 'FRIGOBAR', 'ri-fridge-line', 4, 1
WHERE
    NOT EXISTS (
        SELECT 1
        FROM hotel.caracteristicas
        WHERE
            upper(descripcion) = 'FRIGOBAR'
    );

INSERT INTO
    hotel.caracteristicas (
        descripcion,
        icono,
        orden,
        estado
    )
SELECT 'JACUZZI', 'ri-water-flash-line', 5, 1
WHERE
    NOT EXISTS (
        SELECT 1
        FROM hotel.caracteristicas
        WHERE
            upper(descripcion) = 'JACUZZI'
    );

INSERT INTO
    hotel.caracteristicas (
        descripcion,
        icono,
        orden,
        estado
    )
SELECT 'AGUA CALIENTE', 'ri-temp-hot-line', 6, 1
WHERE
    NOT EXISTS (
        SELECT 1
        FROM hotel.caracteristicas
        WHERE
            upper(descripcion) = 'AGUA CALIENTE'
    );

INSERT INTO
    hotel.caracteristicas (
        descripcion,
        icono,
        orden,
        estado
    )
SELECT 'BANO PRIVADO', 'ri-hotel-bed-line', 7, 1
WHERE
    NOT EXISTS (
        SELECT 1
        FROM hotel.caracteristicas
        WHERE
            upper(descripcion) = 'BANO PRIVADO'
    );

INSERT INTO
    hotel.caracteristicas (
        descripcion,
        icono,
        orden,
        estado
    )
SELECT 'CAJA FUERTE', 'ri-safe-2-line', 8, 1
WHERE
    NOT EXISTS (
        SELECT 1
        FROM hotel.caracteristicas
        WHERE
            upper(descripcion) = 'CAJA FUERTE'
    );

-- Fin caracteristicas dinamicas de habitaciones.

INSERT INTO
    hotel.configuraciones (
        codsucursal,
        codproducto_alojamiento,
        codunidad_alojamiento,
        estado
    )
SELECT s.codsucursal, 0, 0, 1
FROM public.sucursales s
WHERE
    s.estado = 1
    AND NOT EXISTS (
        SELECT 1
        FROM hotel.configuraciones c
        WHERE
            c.codsucursal = s.codsucursal
    );

INSERT INTO
    hotel.ambientes (
        codsucursal,
        descripcion,
        aforo,
        estado
    )
SELECT s.codsucursal, '1ER PISO', 0, 1
FROM public.sucursales s
WHERE
    s.estado = 1
    AND NOT EXISTS (
        SELECT 1
        FROM hotel.ambientes a
        WHERE
            a.codsucursal = s.codsucursal
            AND upper(a.descripcion) = '1ER PISO'
            AND a.estado = 1
    );

UPDATE hotel.habitaciones h
SET
    codambiente = a.codambiente
FROM hotel.ambientes a
WHERE
    h.codsucursal = a.codsucursal
    AND h.codambiente = 0
    AND a.estado = 1
    AND a.codambiente = (
        SELECT min(a2.codambiente)
        FROM hotel.ambientes a2
        WHERE
            a2.codsucursal = h.codsucursal
            AND a2.estado = 1
    );

DO $$
DECLARE
    v_codproducto integer;
    v_codunidad integer;
BEGIN
    SELECT codunidad INTO v_codunidad
    FROM almacen.unidades
    WHERE estado = 1
    ORDER BY CASE WHEN upper(descripcion) LIKE '%UNIDAD%' THEN 0 ELSE 1 END, codunidad
    LIMIT 1;

    IF v_codunidad IS NULL THEN
        RAISE EXCEPTION 'No existe unidad activa en almacen.unidades para crear ALOJAMIENTO';
    END IF;

    SELECT codproducto INTO v_codproducto
    FROM almacen.productos
    WHERE estado = 1
      AND (upper(codigo) = 'HOTEL-ALOJ' OR upper(descripcion) = 'ALOJAMIENTO')
    ORDER BY codproducto
    LIMIT 1;

    IF v_codproducto IS NULL THEN
        INSERT INTO almacen.productos (
            codfamilia, codlinea, codmarca, codempresa, codigo, descripcion,
            afectoicbper, controlstock, afectoigvcompra, afectoigvventa,
            foto, estado, calcular, paraventa, codatencion, caracteristicas,
            codoficial, sexo, codmodelo, codcolor, codtalla, tipo, comisionvendedor,
            controlarseries
        )
        VALUES (
            0, 0, 0, 1, 'HOTEL-ALOJ', 'ALOJAMIENTO',
            0, 0, 0, 0,
            'default.png', 1, 0, 1, 0, '',
            '', 0, 0, 0, 0, 2, 0,
            0
        )
        RETURNING codproducto INTO v_codproducto;
    ELSE
        UPDATE almacen.productos
        SET codigo = COALESCE(NULLIF(codigo, ''), 'HOTEL-ALOJ'),
            descripcion = 'ALOJAMIENTO',
            controlstock = 0,
            paraventa = 1,
            tipo = 2,
            estado = 1
        WHERE codproducto = v_codproducto;
    END IF;

    INSERT INTO almacen.productounidades (
        codproducto, codunidad, codsucursal, factor, preciocompra,
        pventapublico, pventamin, pventacredito, pventaxmayor,
        pventaadicional, preciocosto, gastos, codigobarra, estado
    )
    SELECT v_codproducto, v_codunidad, s.codsucursal, 1, 0,
           0, 0, 0, 0,
           0, 0, 0, 'HOTEL-ALOJ', 1
    FROM public.sucursales s
    WHERE s.estado = 1
      AND NOT EXISTS (
          SELECT 1
          FROM almacen.productounidades pu
          WHERE pu.codproducto = v_codproducto
            AND pu.codunidad = v_codunidad
            AND pu.codsucursal = s.codsucursal
      );

    INSERT INTO almacen.productoubicacion (
        codalmacen, codproducto, codunidad, codsucursal, stockactual,
        stockactualreal, preciostockvalorizado, ventarecogo, comprarecogo,
        stockminimo, stockmaximo, estado, stockactualconvertido, factor,
        preciocompra, pventapublico, pventamin, pventacredito, pventaxmayor,
        pventaadicional, preciocosto, gastos, codigobarra, stockpedido,
        stockproveedor, codafectacionigvcompra, codafectacionigvventa
    )
    SELECT a.codalmacen, v_codproducto, v_codunidad, a.codsucursal, 0,
           0, 0, 0, 0,
           0, 0, 1, 0, 1,
           0, 0, 0, 0, 0,
           0, 0, 0, 'HOTEL-ALOJ', 0,
           0, 1, 9
    FROM almacen.almacenes a
    WHERE a.estado = 1
      AND NOT EXISTS (
          SELECT 1
          FROM almacen.productoubicacion pu
          WHERE pu.codalmacen = a.codalmacen
            AND pu.codproducto = v_codproducto
            AND pu.codunidad = v_codunidad
      );

    UPDATE hotel.configuraciones
    SET codproducto_alojamiento = v_codproducto,
        codunidad_alojamiento = v_codunidad
    WHERE estado = 1
      AND (codproducto_alojamiento = 0 OR codunidad_alojamiento = 0);
END $$;

DO $$
DECLARE
    v_hotel integer;
    v_recepcion integer;
    v_habitaciones integer;
    v_ambientes integer;
    v_reservas integer;
    v_limpieza integer;
    v_mantenimiento integer;
    v_reportes integer;
BEGIN
    PERFORM setval(
        pg_get_serial_sequence('seguridad.modulos', 'codmodulo'),
        COALESCE((SELECT max(codmodulo) FROM seguridad.modulos), 0) + 1,
        false
    );

    SELECT codmodulo INTO v_hotel
    FROM seguridad.modulos
    WHERE url = 'hotel'
    LIMIT 1;

    IF v_hotel IS NULL THEN
        INSERT INTO seguridad.modulos (descripcion, icono, url, codpadre, orden, estado, nuevo, editar, anular, consultar, codsistema)
        VALUES ('Hotel', 'ri-hotel-bed-line', 'hotel', 0, 18, 1, 1, 1, 1, 1, 1)
        RETURNING codmodulo INTO v_hotel;
    ELSE
        UPDATE seguridad.modulos
        SET descripcion = 'Hotel',
            icono = 'ri-hotel-bed-line',
            codpadre = 0,
            estado = 1,
            codsistema = 1
        WHERE codmodulo = v_hotel;
    END IF;

    SELECT codmodulo INTO v_recepcion FROM seguridad.modulos WHERE url = 'hotel/recepcion' LIMIT 1;
    IF v_recepcion IS NULL THEN
        INSERT INTO seguridad.modulos (descripcion, icono, url, codpadre, orden, estado, nuevo, editar, anular, consultar, codsistema)
        VALUES ('Recepcion', 'ri-door-open-line', 'hotel/recepcion', v_hotel, 1, 1, 1, 1, 1, 1, 1)
        RETURNING codmodulo INTO v_recepcion;
    ELSE
        UPDATE seguridad.modulos SET codpadre = v_hotel, orden = 1, estado = 1, codsistema = 1 WHERE codmodulo = v_recepcion;
    END IF;

    SELECT codmodulo INTO v_habitaciones FROM seguridad.modulos WHERE url = 'hotel/habitaciones' LIMIT 1;
    IF v_habitaciones IS NULL THEN
        INSERT INTO seguridad.modulos (descripcion, icono, url, codpadre, orden, estado, nuevo, editar, anular, consultar, codsistema)
        VALUES ('Habitaciones', 'ri-home-8-line', 'hotel/habitaciones', v_hotel, 2, 1, 1, 1, 1, 1, 1)
        RETURNING codmodulo INTO v_habitaciones;
    ELSE
        UPDATE seguridad.modulos SET codpadre = v_hotel, orden = 2, estado = 1, codsistema = 1 WHERE codmodulo = v_habitaciones;
    END IF;

    SELECT codmodulo INTO v_ambientes FROM seguridad.modulos WHERE url = 'hotel/ambientes' LIMIT 1;
    IF v_ambientes IS NULL THEN
        INSERT INTO seguridad.modulos (descripcion, icono, url, codpadre, orden, estado, nuevo, editar, anular, consultar, codsistema)
        VALUES ('Ambientes hotel', 'ri-building-2-line', 'hotel/ambientes', v_hotel, 3, 1, 1, 1, 1, 1, 1)
        RETURNING codmodulo INTO v_ambientes;
    ELSE
        UPDATE seguridad.modulos SET codpadre = v_hotel, orden = 3, estado = 1, codsistema = 1 WHERE codmodulo = v_ambientes;
    END IF;

    SELECT codmodulo INTO v_reservas FROM seguridad.modulos WHERE url = 'hotel/reservas' LIMIT 1;
    IF v_reservas IS NULL THEN
        INSERT INTO seguridad.modulos (descripcion, icono, url, codpadre, orden, estado, nuevo, editar, anular, consultar, codsistema)
        VALUES ('Reservas', 'ri-calendar-check-line', 'hotel/reservas', v_hotel, 4, 1, 1, 1, 1, 1, 1)
        RETURNING codmodulo INTO v_reservas;
    ELSE
        UPDATE seguridad.modulos SET codpadre = v_hotel, orden = 4, estado = 1, codsistema = 1 WHERE codmodulo = v_reservas;
    END IF;

    SELECT codmodulo INTO v_limpieza FROM seguridad.modulos WHERE url = 'hotel/limpieza' LIMIT 1;
    IF v_limpieza IS NULL THEN
        INSERT INTO seguridad.modulos (descripcion, icono, url, codpadre, orden, estado, nuevo, editar, anular, consultar, codsistema)
        VALUES ('Limpieza', 'ri-brush-3-line', 'hotel/limpieza', v_hotel, 5, 1, 1, 1, 1, 1, 1)
        RETURNING codmodulo INTO v_limpieza;
    ELSE
        UPDATE seguridad.modulos SET codpadre = v_hotel, orden = 5, estado = 1, codsistema = 1 WHERE codmodulo = v_limpieza;
    END IF;

    SELECT codmodulo INTO v_mantenimiento FROM seguridad.modulos WHERE url = 'hotel/mantenimiento' LIMIT 1;
    IF v_mantenimiento IS NULL THEN
        INSERT INTO seguridad.modulos (descripcion, icono, url, codpadre, orden, estado, nuevo, editar, anular, consultar, codsistema)
        VALUES ('Mantenimiento', 'ri-tools-line', 'hotel/mantenimiento', v_hotel, 6, 1, 1, 1, 1, 1, 1)
        RETURNING codmodulo INTO v_mantenimiento;
    ELSE
        UPDATE seguridad.modulos SET codpadre = v_hotel, orden = 6, estado = 1, codsistema = 1 WHERE codmodulo = v_mantenimiento;
    END IF;

    SELECT codmodulo INTO v_reportes FROM seguridad.modulos WHERE url = 'hotel/reportes' LIMIT 1;
    IF v_reportes IS NULL THEN
        INSERT INTO seguridad.modulos (descripcion, icono, url, codpadre, orden, estado, nuevo, editar, anular, consultar, codsistema)
        VALUES ('Reportes hotel', 'ri-bar-chart-box-line', 'hotel/reportes', v_hotel, 7, 1, 1, 1, 1, 1, 1)
        RETURNING codmodulo INTO v_reportes;
    ELSE
        UPDATE seguridad.modulos SET codpadre = v_hotel, orden = 7, estado = 1, codsistema = 1 WHERE codmodulo = v_reportes;
    END IF;

    INSERT INTO seguridad.moduloperfiles (codmodulo, codperfil, nuevo, editar, anular)
    SELECT m.codmodulo, p.codperfil, 1, 1, 1
    FROM seguridad.modulos m
    CROSS JOIN seguridad.perfiles p
    WHERE m.codmodulo IN (v_hotel, v_recepcion, v_habitaciones, v_ambientes, v_reservas, v_limpieza, v_mantenimiento, v_reportes)
      AND NOT EXISTS (
          SELECT 1
          FROM seguridad.moduloperfiles mp
          WHERE mp.codmodulo = m.codmodulo
            AND mp.codperfil = p.codperfil
      );
END $$;

INSERT INTO
    seguridad.moduloperfiles (
        codmodulo,
        codperfil,
        nuevo,
        editar,
        anular
    )
SELECT m.codmodulo, p.codperfil, 1, 1, 1
FROM seguridad.modulos m
    CROSS JOIN seguridad.perfiles p
WHERE (
        m.url = 'hotel'
        OR m.url LIKE 'hotel/%'
    )
    AND NOT EXISTS (
        SELECT 1
        FROM seguridad.moduloperfiles mp
        WHERE
            mp.codmodulo = m.codmodulo
            AND mp.codperfil = p.codperfil
    );

-- Producto/servicio ALOJAMIENTO:
-- Esta migracion crea o reutiliza almacen.productos = ALOJAMIENTO
-- y lo registra automaticamente en hotel.configuraciones por sucursal.

-- ============================================================
-- FIN MODULO HOTEL
-- ============================================================
