-- ============================================================
-- PHUYU COMERCIAL - MIGRACIONES GENERALES
-- ============================================================
-- Orden recomendado de ejecucion:
--   1. Productos con series
--   2. Actualizacion y unificacion de modulos existentes
--   3. Caja - horas de apertura y cierre
--   4. Hotel - tablas base
--   5. Hotel - indices
--   6. Hotel - configuracion y datos iniciales
--   7. Hotel - producto/servicio ALOJAMIENTO
--   8. Hotel - menu y permisos
--   9. Programacion automatica SUNAT / CPE
-- ============================================================

-- ============================================================
-- 1. PRODUCTOS CON SERIES
-- ============================================================

ALTER TABLE almacen.productos
ADD COLUMN IF NOT EXISTS controlarseries INTEGER NOT NULL DEFAULT 0;

CREATE TABLE IF NOT EXISTS almacen.series (
    id_serie SERIAL PRIMARY KEY,
    codproducto INTEGER NOT NULL,
    serie_codigo VARCHAR(120) NOT NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'EN_ALMACEN' CHECK (
        estado IN (
            'EN_ALMACEN',
            'RESERVADO',
            'VENDIDO',
            'DADO_BAJA',
            'EN_PROVEEDOR'
        )
    ),
    comprobante VARCHAR(120),
    fecha_ingreso TIMESTAMPTZ,
    fecha_egreso TIMESTAMPTZ,
    motivo VARCHAR(200),
    codalmacen INTEGER,
    codkardex INTEGER,
    codkardex_egreso INTEGER,
    codsucursal INTEGER,
    CONSTRAINT fk_series_codproducto
        FOREIGN KEY (codproducto)
        REFERENCES almacen.productos (codproducto)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT uq_series_producto_serie
        UNIQUE (codproducto, serie_codigo)
);

-- ============================================================
-- 2. ACTUALIZACION Y UNIFICACION DE MODULOS EXISTENTES
-- ============================================================

UPDATE seguridad.modulos
SET icono = CASE codmodulo
    WHEN 1 THEN 'ri-shopping-bag-3-line'       -- Ventas
    WHEN 2 THEN 'ri-truck-line'                -- Logistica
    WHEN 3 THEN 'ri-store-2-line'              -- Almacen
    WHEN 4 THEN 'ri-wallet-3-line'             -- Tesoreria
    WHEN 5 THEN 'ri-exchange-dollar-line'      -- Creditos
    WHEN 6 THEN 'ri-settings-3-line'           -- Administracion
    WHEN 7 THEN 'ri-bar-chart-box-line'        -- Reportes
    WHEN 8 THEN 'ri-file-upload-line'          -- CPE / facturacion electronica
    WHEN 17 THEN 'ri-restaurant-2-line'        -- Restobar
    WHEN 118 THEN 'ri-plant-line'              -- Agricola
    WHEN 121 THEN 'ri-bar-chart-box-line'      -- Reportes duplicado
    WHEN 125 THEN 'ri-exchange-dollar-line'    -- Creditos duplicado
    ELSE icono
END
WHERE codmodulo IN (1, 2, 3, 4, 5, 6, 7, 8, 17, 118, 121, 125)
  AND codpadre = 0;

UPDATE seguridad.modulos
SET descripcion = CASE codmodulo
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
        WHEN 71 THEN 1    -- Ventas
        WHEN 113 THEN 2   -- Pedidos
        WHEN 115 THEN 3   -- Proformas
        WHEN 77 THEN 4    -- Vendedores
        WHEN 72 THEN 5    -- Compras
        WHEN 73 THEN 6    -- Productos
        WHEN 76 THEN 7    -- Ingresos y salidas de almacen
        WHEN 74 THEN 8    -- Caja y bancos
        WHEN 75 THEN 9    -- Creditos
        WHEN 117 THEN 10  -- Cuotas
        WHEN 120 THEN 11  -- Prestamos
        WHEN 104 THEN 12  -- Utilidades
        WHEN 114 THEN 13  -- Personas
        ELSE orden
    END
WHERE codmodulo IN (71, 113, 115, 77, 72, 73, 76, 74, 75, 117, 120, 104, 114);

UPDATE seguridad.modulos
SET codpadre = 5,
    orden = CASE codmodulo
        WHEN 51 THEN 1   -- Cuentas por cobrar
        WHEN 53 THEN 2   -- Lista por cobrar
        WHEN 110 THEN 3  -- Lista de cobranza
        WHEN 52 THEN 4   -- Cuentas por pagar
        WHEN 54 THEN 5   -- Lista por pagar
        WHEN 111 THEN 6  -- Lista de pagos
        ELSE orden
    END
WHERE codmodulo IN (51, 53, 110, 52, 54, 111);

UPDATE seguridad.modulos
SET estado = 0
WHERE codmodulo IN (121, 125)
  AND codpadre = 0;

-- ============================================================
-- 3. CAJA - HORAS DE APERTURA Y CIERRE
-- ============================================================

ALTER TABLE caja.controldiario
ADD COLUMN IF NOT EXISTS horaapertura TIME WITHOUT TIME ZONE;

ALTER TABLE caja.controldiario
ALTER COLUMN horaapertura SET DEFAULT CURRENT_TIME;

ALTER TABLE caja.controldiario
ADD COLUMN IF NOT EXISTS horacierre TIME WITHOUT TIME ZONE;

-- ============================================================
-- 4. MODULO HOTEL - TABLAS BASE
-- ============================================================

CREATE SCHEMA IF NOT EXISTS hotel;

CREATE TABLE IF NOT EXISTS hotel.habitacion_tipos (
    codhabitaciontipo SERIAL PRIMARY KEY,
    descripcion VARCHAR(120) NOT NULL,
    capacidad INTEGER DEFAULT 1,
    preciobase NUMERIC DEFAULT 0,
    estado INTEGER DEFAULT 1
);

CREATE TABLE IF NOT EXISTS hotel.ambientes (
    codambiente SERIAL PRIMARY KEY,
    codsucursal INTEGER NOT NULL,
    descripcion VARCHAR(120) NOT NULL,
    aforo INTEGER DEFAULT 0,
    estado INTEGER DEFAULT 1
);

CREATE TABLE IF NOT EXISTS hotel.habitaciones (
    codhabitacion SERIAL PRIMARY KEY,
    codsucursal INTEGER NOT NULL,
    codambiente INTEGER NOT NULL DEFAULT 0,
    codhabitaciontipo INTEGER NOT NULL REFERENCES hotel.habitacion_tipos (codhabitaciontipo),
    numero VARCHAR(20) NOT NULL,
    piso VARCHAR(20),
    capacidad INTEGER DEFAULT 1,
    preciobase NUMERIC DEFAULT 0,
    situacion INTEGER DEFAULT 1,
    estado INTEGER DEFAULT 1,
    UNIQUE (codsucursal, numero)
);

ALTER TABLE hotel.habitaciones
ADD COLUMN IF NOT EXISTS codambiente INTEGER NOT NULL DEFAULT 0;

CREATE TABLE IF NOT EXISTS hotel.caracteristicas (
    codcaracteristica SERIAL PRIMARY KEY,
    descripcion VARCHAR(120) NOT NULL,
    icono VARCHAR(80) DEFAULT 'ri-checkbox-circle-line',
    orden INTEGER DEFAULT 0,
    estado INTEGER DEFAULT 1,
    UNIQUE (descripcion)
);

CREATE TABLE IF NOT EXISTS hotel.habitacion_caracteristicas (
    codhabitacion INTEGER NOT NULL REFERENCES hotel.habitaciones (codhabitacion),
    codcaracteristica INTEGER NOT NULL REFERENCES hotel.caracteristicas (codcaracteristica),
    estado INTEGER DEFAULT 1,
    PRIMARY KEY (codhabitacion, codcaracteristica)
);

CREATE TABLE IF NOT EXISTS hotel.reservas (
    codreserva SERIAL PRIMARY KEY,
    codsucursal INTEGER NOT NULL,
    codpersona INTEGER NOT NULL,
    codusuario INTEGER NOT NULL,
    fechareserva DATE DEFAULT NOW(),
    fechallegada DATE NOT NULL,
    fechasalida DATE NOT NULL,
    cliente VARCHAR,
    direccion VARCHAR,
    observacion TEXT,
    situacion INTEGER DEFAULT 1,
    estado INTEGER DEFAULT 1
);

CREATE TABLE IF NOT EXISTS hotel.reserva_habitaciones (
    codreserva INTEGER NOT NULL REFERENCES hotel.reservas (codreserva),
    codhabitacion INTEGER NOT NULL REFERENCES hotel.habitaciones (codhabitacion),
    precio NUMERIC DEFAULT 0,
    estado INTEGER DEFAULT 1,
    PRIMARY KEY (codreserva, codhabitacion)
);

CREATE TABLE IF NOT EXISTS hotel.estadias (
    codestadia SERIAL PRIMARY KEY,
    codreserva INTEGER,
    codsucursal INTEGER NOT NULL,
    codalmacen INTEGER NOT NULL,
    codpersona INTEGER NOT NULL,
    codusuario INTEGER NOT NULL,
    codempleado INTEGER DEFAULT 0,
    codkardex INTEGER DEFAULT 0,
    fecha_checkin DATE NOT NULL,
    hora_checkin TIME DEFAULT NOW(),
    fecha_checkout DATE,
    hora_checkout TIME,
    noches INTEGER DEFAULT 0,
    alojamiento NUMERIC DEFAULT 0,
    consumos NUMERIC DEFAULT 0,
    importe NUMERIC DEFAULT 0,
    cliente VARCHAR,
    direccion VARCHAR,
    observacion TEXT,
    situacion INTEGER DEFAULT 1,
    estado INTEGER DEFAULT 1
);

CREATE TABLE IF NOT EXISTS hotel.estadia_habitaciones (
    codestadia INTEGER NOT NULL REFERENCES hotel.estadias (codestadia),
    codhabitacion INTEGER NOT NULL REFERENCES hotel.habitaciones (codhabitacion),
    precio_noche NUMERIC DEFAULT 0,
    noches INTEGER DEFAULT 0,
    subtotal NUMERIC DEFAULT 0,
    estado INTEGER DEFAULT 1,
    PRIMARY KEY (codestadia, codhabitacion)
);

CREATE TABLE IF NOT EXISTS hotel.estadia_huespedes (
    codestadia INTEGER NOT NULL REFERENCES hotel.estadias (codestadia),
    codpersona INTEGER NOT NULL,
    titular INTEGER DEFAULT 0,
    estado INTEGER DEFAULT 1,
    PRIMARY KEY (codestadia, codpersona)
);

CREATE TABLE IF NOT EXISTS hotel.estadia_cambios_habitacion (
    codcambio SERIAL PRIMARY KEY,
    codestadia INTEGER NOT NULL REFERENCES hotel.estadias (codestadia),
    codhabitacion_origen INTEGER NOT NULL REFERENCES hotel.habitaciones (codhabitacion),
    codhabitacion_destino INTEGER NOT NULL REFERENCES hotel.habitaciones (codhabitacion),
    codusuario INTEGER NOT NULL,
    fecha DATE DEFAULT NOW(),
    hora TIME DEFAULT NOW(),
    observacion TEXT,
    estado INTEGER DEFAULT 1
);

CREATE TABLE IF NOT EXISTS hotel.consumos_habitacion (
    codconsumo SERIAL PRIMARY KEY,
    codestadia INTEGER NOT NULL REFERENCES hotel.estadias (codestadia),
    codhabitacion INTEGER NOT NULL REFERENCES hotel.habitaciones (codhabitacion),
    codproducto INTEGER NOT NULL,
    codunidad INTEGER NOT NULL,
    id_serie INTEGER DEFAULT 0,
    item INTEGER NOT NULL,
    cantidad NUMERIC DEFAULT 0,
    preciounitario NUMERIC DEFAULT 0,
    preciosinigv NUMERIC DEFAULT 0,
    preciobruto NUMERIC DEFAULT 0,
    preciorefunitario NUMERIC DEFAULT 0,
    codafectacionigv CHAR(2) DEFAULT '20',
    igv NUMERIC DEFAULT 0,
    valorventa NUMERIC DEFAULT 0,
    subtotal NUMERIC DEFAULT 0,
    descripcion TEXT,
    codkardex INTEGER DEFAULT 0,
    fechafacturado DATE,
    situacion INTEGER DEFAULT 1,
    estado INTEGER DEFAULT 1
);

ALTER TABLE hotel.consumos_habitacion
ADD COLUMN IF NOT EXISTS codkardex INTEGER DEFAULT 0;

ALTER TABLE hotel.consumos_habitacion
ADD COLUMN IF NOT EXISTS fechafacturado DATE;

ALTER TABLE hotel.consumos_habitacion
ADD COLUMN IF NOT EXISTS id_serie INTEGER DEFAULT 0;

CREATE TABLE IF NOT EXISTS hotel.temporadas (
    codtemporada SERIAL PRIMARY KEY,
    codsucursal INTEGER NOT NULL,
    descripcion VARCHAR(120) NOT NULL,
    fechadesde DATE NOT NULL,
    fechahasta DATE NOT NULL,
    factor NUMERIC DEFAULT 1,
    estado INTEGER DEFAULT 1
);

CREATE TABLE IF NOT EXISTS hotel.tarifas (
    codtarifa SERIAL PRIMARY KEY,
    codsucursal INTEGER NOT NULL,
    codhabitaciontipo INTEGER NOT NULL,
    codtemporada INTEGER,
    precio NUMERIC DEFAULT 0,
    estado INTEGER DEFAULT 1
);

CREATE TABLE IF NOT EXISTS hotel.limpieza_habitaciones (
    codlimpieza SERIAL PRIMARY KEY,
    codhabitacion INTEGER NOT NULL,
    codusuario INTEGER NOT NULL,
    codresponsable INTEGER DEFAULT 0,
    fecha DATE DEFAULT NOW(),
    hora TIME DEFAULT NOW(),
    observacion TEXT,
    estado INTEGER DEFAULT 1
);

CREATE TABLE IF NOT EXISTS hotel.mantenimiento_habitaciones (
    codmantenimiento SERIAL PRIMARY KEY,
    codhabitacion INTEGER NOT NULL,
    codusuario INTEGER NOT NULL,
    codresponsable INTEGER DEFAULT 0,
    fecha_inicio DATE DEFAULT NOW(),
    fecha_fin DATE,
    observacion TEXT,
    situacion INTEGER DEFAULT 1,
    estado INTEGER DEFAULT 1
);

CREATE TABLE IF NOT EXISTS hotel.configuraciones (
    codconfiguracion SERIAL PRIMARY KEY,
    codsucursal INTEGER NOT NULL,
    codproducto_alojamiento INTEGER NOT NULL DEFAULT 0,
    codunidad_alojamiento INTEGER NOT NULL DEFAULT 0,
    estado INTEGER NOT NULL DEFAULT 1,
    UNIQUE (codsucursal)
);

-- ============================================================
-- 5. MODULO HOTEL - INDICES
-- ============================================================

CREATE INDEX IF NOT EXISTS idx_hotel_habitaciones_sucursal_situacion
ON hotel.habitaciones (codsucursal, situacion, estado);

CREATE INDEX IF NOT EXISTS idx_hotel_ambientes_sucursal
ON hotel.ambientes (codsucursal, estado);

CREATE INDEX IF NOT EXISTS idx_hotel_habitaciones_ambiente
ON hotel.habitaciones (codambiente, estado);

CREATE INDEX IF NOT EXISTS idx_hotel_caracteristicas_estado
ON hotel.caracteristicas (estado, orden);

CREATE INDEX IF NOT EXISTS idx_hotel_habitacion_caracteristicas_caracteristica
ON hotel.habitacion_caracteristicas (codcaracteristica, estado);

CREATE INDEX IF NOT EXISTS idx_hotel_reservas_fechas
ON hotel.reservas (codsucursal, fechallegada, fechasalida, situacion, estado);

CREATE INDEX IF NOT EXISTS idx_hotel_estadias_activa
ON hotel.estadias (codsucursal, situacion, estado);

CREATE INDEX IF NOT EXISTS idx_hotel_consumos_estadia
ON hotel.consumos_habitacion (codestadia, situacion, estado);

CREATE INDEX IF NOT EXISTS idx_hotel_consumos_kardex
ON hotel.consumos_habitacion (codkardex, estado);

CREATE INDEX IF NOT EXISTS idx_hotel_estadia_cambios
ON hotel.estadia_cambios_habitacion (codestadia, estado);

-- ============================================================
-- 6. MODULO HOTEL - CONFIGURACION Y DATOS INICIALES
-- ============================================================

INSERT INTO hotel.habitacion_tipos (descripcion, capacidad, preciobase, estado)
SELECT 'SIMPLE', 1, 0, 1
WHERE NOT EXISTS (
    SELECT 1
    FROM hotel.habitacion_tipos
    WHERE UPPER(descripcion) = 'SIMPLE'
);

INSERT INTO hotel.habitacion_tipos (descripcion, capacidad, preciobase, estado)
SELECT 'DOBLE', 2, 0, 1
WHERE NOT EXISTS (
    SELECT 1
    FROM hotel.habitacion_tipos
    WHERE UPPER(descripcion) = 'DOBLE'
);

INSERT INTO hotel.habitacion_tipos (descripcion, capacidad, preciobase, estado)
SELECT 'MATRIMONIAL', 2, 0, 1
WHERE NOT EXISTS (
    SELECT 1
    FROM hotel.habitacion_tipos
    WHERE UPPER(descripcion) = 'MATRIMONIAL'
);

INSERT INTO hotel.caracteristicas (descripcion, icono, orden, estado)
SELECT 'AIRE ACONDICIONADO', 'ri-windy-line', 1, 1
WHERE NOT EXISTS (
    SELECT 1 FROM hotel.caracteristicas WHERE UPPER(descripcion) = 'AIRE ACONDICIONADO'
);

INSERT INTO hotel.caracteristicas (descripcion, icono, orden, estado)
SELECT 'TV', 'ri-tv-line', 2, 1
WHERE NOT EXISTS (
    SELECT 1 FROM hotel.caracteristicas WHERE UPPER(descripcion) = 'TV'
);

INSERT INTO hotel.caracteristicas (descripcion, icono, orden, estado)
SELECT 'WIFI', 'ri-wifi-line', 3, 1
WHERE NOT EXISTS (
    SELECT 1 FROM hotel.caracteristicas WHERE UPPER(descripcion) = 'WIFI'
);

INSERT INTO hotel.caracteristicas (descripcion, icono, orden, estado)
SELECT 'FRIGOBAR', 'ri-fridge-line', 4, 1
WHERE NOT EXISTS (
    SELECT 1 FROM hotel.caracteristicas WHERE UPPER(descripcion) = 'FRIGOBAR'
);

INSERT INTO hotel.caracteristicas (descripcion, icono, orden, estado)
SELECT 'JACUZZI', 'ri-water-flash-line', 5, 1
WHERE NOT EXISTS (
    SELECT 1 FROM hotel.caracteristicas WHERE UPPER(descripcion) = 'JACUZZI'
);

INSERT INTO hotel.caracteristicas (descripcion, icono, orden, estado)
SELECT 'AGUA CALIENTE', 'ri-temp-hot-line', 6, 1
WHERE NOT EXISTS (
    SELECT 1 FROM hotel.caracteristicas WHERE UPPER(descripcion) = 'AGUA CALIENTE'
);

INSERT INTO hotel.caracteristicas (descripcion, icono, orden, estado)
SELECT 'BANO PRIVADO', 'ri-hotel-bed-line', 7, 1
WHERE NOT EXISTS (
    SELECT 1 FROM hotel.caracteristicas WHERE UPPER(descripcion) = 'BANO PRIVADO'
);

INSERT INTO hotel.caracteristicas (descripcion, icono, orden, estado)
SELECT 'CAJA FUERTE', 'ri-safe-2-line', 8, 1
WHERE NOT EXISTS (
    SELECT 1 FROM hotel.caracteristicas WHERE UPPER(descripcion) = 'CAJA FUERTE'
);

INSERT INTO hotel.configuraciones (
    codsucursal,
    codproducto_alojamiento,
    codunidad_alojamiento,
    estado
)
SELECT s.codsucursal, 0, 0, 1
FROM public.sucursales s
WHERE s.estado = 1
  AND NOT EXISTS (
      SELECT 1
      FROM hotel.configuraciones c
      WHERE c.codsucursal = s.codsucursal
  );

INSERT INTO hotel.ambientes (codsucursal, descripcion, aforo, estado)
SELECT s.codsucursal, '1ER PISO', 0, 1
FROM public.sucursales s
WHERE s.estado = 1
  AND NOT EXISTS (
      SELECT 1
      FROM hotel.ambientes a
      WHERE a.codsucursal = s.codsucursal
        AND UPPER(a.descripcion) = '1ER PISO'
        AND a.estado = 1
  );

UPDATE hotel.habitaciones h
SET codambiente = a.codambiente
FROM hotel.ambientes a
WHERE h.codsucursal = a.codsucursal
  AND h.codambiente = 0
  AND a.estado = 1
  AND a.codambiente = (
      SELECT MIN(a2.codambiente)
      FROM hotel.ambientes a2
      WHERE a2.codsucursal = h.codsucursal
        AND a2.estado = 1
  );

-- ============================================================
-- 7. MODULO HOTEL - PRODUCTO / SERVICIO ALOJAMIENTO
-- ============================================================

DO $$
DECLARE
    v_codproducto INTEGER;
    v_codunidad INTEGER;
BEGIN
    SELECT codunidad
    INTO v_codunidad
    FROM almacen.unidades
    WHERE estado = 1
    ORDER BY
        CASE WHEN UPPER(descripcion) LIKE '%UNIDAD%' THEN 0 ELSE 1 END,
        codunidad
    LIMIT 1;

    IF v_codunidad IS NULL THEN
        RAISE EXCEPTION 'No existe unidad activa en almacen.unidades para crear ALOJAMIENTO';
    END IF;

    SELECT codproducto
    INTO v_codproducto
    FROM almacen.productos
    WHERE estado = 1
      AND (
          UPPER(codigo) = 'HOTEL-ALOJ'
          OR UPPER(descripcion) = 'ALOJAMIENTO'
      )
    ORDER BY codproducto
    LIMIT 1;

    IF v_codproducto IS NULL THEN
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
            0, 0, 0, 1, 'HOTEL-ALOJ', 'ALOJAMIENTO',
            0, 0, 0, 0, 'default.png', 1, 0, 1, 0, '',
            '', 0, 0, 0, 0, 2, 0, 0
        )
        RETURNING codproducto INTO v_codproducto;
    ELSE
        UPDATE almacen.productos
        SET codigo = COALESCE(NULLIF(codigo, ''), 'HOTEL-ALOJ'),
            descripcion = 'ALOJAMIENTO',
            controlstock = 0,
            paraventa = 1,
            tipo = 2,
            estado = 1,
            controlarseries = 0
        WHERE codproducto = v_codproducto;
    END IF;

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
    SELECT
        v_codproducto, v_codunidad, s.codsucursal, 1, 0,
        0, 0, 0, 0, 0, 0, 0, 'HOTEL-ALOJ', 1
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
        a.codalmacen, v_codproducto, v_codunidad, a.codsucursal,
        0, 0, 0, 0, 0, 0, 0, 1, 0, 1,
        0, 0, 0, 0, 0, 0, 0, 0, 'HOTEL-ALOJ', 0,
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
      AND (
          codproducto_alojamiento = 0
          OR codunidad_alojamiento = 0
      );
END $$;

-- ============================================================
-- 8. MODULO HOTEL - MENU Y PERMISOS
-- ============================================================

DO $$
DECLARE
    v_hotel INTEGER;
    v_recepcion INTEGER;
    v_habitaciones INTEGER;
    v_ambientes INTEGER;
    v_reservas INTEGER;
    v_limpieza INTEGER;
    v_mantenimiento INTEGER;
    v_reportes INTEGER;
BEGIN
    PERFORM SETVAL(
        PG_GET_SERIAL_SEQUENCE('seguridad.modulos', 'codmodulo'),
        COALESCE((SELECT MAX(codmodulo) FROM seguridad.modulos), 0) + 1,
        FALSE
    );

    SELECT codmodulo
    INTO v_hotel
    FROM seguridad.modulos
    WHERE url = 'hotel'
    LIMIT 1;

    IF v_hotel IS NULL THEN
        INSERT INTO seguridad.modulos (
            descripcion, icono, url, codpadre, orden, estado,
            nuevo, editar, anular, consultar, codsistema
        )
        VALUES (
            'Hotel', 'ri-hotel-bed-line', 'hotel', 0, 18, 1,
            1, 1, 1, 1, 1
        )
        RETURNING codmodulo INTO v_hotel;
    ELSE
        UPDATE seguridad.modulos
        SET descripcion = 'Hotel',
            icono = 'ri-hotel-bed-line',
            codpadre = 0,
            orden = 18,
            estado = 1,
            codsistema = 1
        WHERE codmodulo = v_hotel;
    END IF;

    SELECT codmodulo INTO v_recepcion
    FROM seguridad.modulos
    WHERE url = 'hotel/recepcion'
    LIMIT 1;

    IF v_recepcion IS NULL THEN
        INSERT INTO seguridad.modulos (descripcion, icono, url, codpadre, orden, estado, nuevo, editar, anular, consultar, codsistema)
        VALUES ('Recepcion', 'ri-door-open-line', 'hotel/recepcion', v_hotel, 1, 1, 1, 1, 1, 1, 1)
        RETURNING codmodulo INTO v_recepcion;
    ELSE
        UPDATE seguridad.modulos
        SET descripcion = 'Recepcion', icono = 'ri-door-open-line', codpadre = v_hotel,
            orden = 1, estado = 1, codsistema = 1
        WHERE codmodulo = v_recepcion;
    END IF;

    SELECT codmodulo INTO v_habitaciones
    FROM seguridad.modulos
    WHERE url = 'hotel/habitaciones'
    LIMIT 1;

    IF v_habitaciones IS NULL THEN
        INSERT INTO seguridad.modulos (descripcion, icono, url, codpadre, orden, estado, nuevo, editar, anular, consultar, codsistema)
        VALUES ('Habitaciones', 'ri-home-8-line', 'hotel/habitaciones', v_hotel, 2, 1, 1, 1, 1, 1, 1)
        RETURNING codmodulo INTO v_habitaciones;
    ELSE
        UPDATE seguridad.modulos
        SET descripcion = 'Habitaciones', icono = 'ri-home-8-line', codpadre = v_hotel,
            orden = 2, estado = 1, codsistema = 1
        WHERE codmodulo = v_habitaciones;
    END IF;

    SELECT codmodulo INTO v_ambientes
    FROM seguridad.modulos
    WHERE url = 'hotel/ambientes'
    LIMIT 1;

    IF v_ambientes IS NULL THEN
        INSERT INTO seguridad.modulos (descripcion, icono, url, codpadre, orden, estado, nuevo, editar, anular, consultar, codsistema)
        VALUES ('Ambientes hotel', 'ri-building-2-line', 'hotel/ambientes', v_hotel, 3, 1, 1, 1, 1, 1, 1)
        RETURNING codmodulo INTO v_ambientes;
    ELSE
        UPDATE seguridad.modulos
        SET descripcion = 'Ambientes hotel', icono = 'ri-building-2-line', codpadre = v_hotel,
            orden = 3, estado = 1, codsistema = 1
        WHERE codmodulo = v_ambientes;
    END IF;

    SELECT codmodulo INTO v_reservas
    FROM seguridad.modulos
    WHERE url = 'hotel/reservas'
    LIMIT 1;

    IF v_reservas IS NULL THEN
        INSERT INTO seguridad.modulos (descripcion, icono, url, codpadre, orden, estado, nuevo, editar, anular, consultar, codsistema)
        VALUES ('Reservas', 'ri-calendar-check-line', 'hotel/reservas', v_hotel, 4, 1, 1, 1, 1, 1, 1)
        RETURNING codmodulo INTO v_reservas;
    ELSE
        UPDATE seguridad.modulos
        SET descripcion = 'Reservas', icono = 'ri-calendar-check-line', codpadre = v_hotel,
            orden = 4, estado = 1, codsistema = 1
        WHERE codmodulo = v_reservas;
    END IF;

    SELECT codmodulo INTO v_limpieza
    FROM seguridad.modulos
    WHERE url = 'hotel/limpieza'
    LIMIT 1;

    IF v_limpieza IS NULL THEN
        INSERT INTO seguridad.modulos (descripcion, icono, url, codpadre, orden, estado, nuevo, editar, anular, consultar, codsistema)
        VALUES ('Limpieza', 'ri-brush-3-line', 'hotel/limpieza', v_hotel, 5, 1, 1, 1, 1, 1, 1)
        RETURNING codmodulo INTO v_limpieza;
    ELSE
        UPDATE seguridad.modulos
        SET descripcion = 'Limpieza', icono = 'ri-brush-3-line', codpadre = v_hotel,
            orden = 5, estado = 1, codsistema = 1
        WHERE codmodulo = v_limpieza;
    END IF;

    SELECT codmodulo INTO v_mantenimiento
    FROM seguridad.modulos
    WHERE url = 'hotel/mantenimiento'
    LIMIT 1;

    IF v_mantenimiento IS NULL THEN
        INSERT INTO seguridad.modulos (descripcion, icono, url, codpadre, orden, estado, nuevo, editar, anular, consultar, codsistema)
        VALUES ('Mantenimiento', 'ri-tools-line', 'hotel/mantenimiento', v_hotel, 6, 1, 1, 1, 1, 1, 1)
        RETURNING codmodulo INTO v_mantenimiento;
    ELSE
        UPDATE seguridad.modulos
        SET descripcion = 'Mantenimiento', icono = 'ri-tools-line', codpadre = v_hotel,
            orden = 6, estado = 1, codsistema = 1
        WHERE codmodulo = v_mantenimiento;
    END IF;

    SELECT codmodulo INTO v_reportes
    FROM seguridad.modulos
    WHERE url = 'hotel/reportes'
    LIMIT 1;

    IF v_reportes IS NULL THEN
        INSERT INTO seguridad.modulos (descripcion, icono, url, codpadre, orden, estado, nuevo, editar, anular, consultar, codsistema)
        VALUES ('Reportes hotel', 'ri-bar-chart-box-line', 'hotel/reportes', v_hotel, 7, 1, 1, 1, 1, 1, 1)
        RETURNING codmodulo INTO v_reportes;
    ELSE
        UPDATE seguridad.modulos
        SET descripcion = 'Reportes hotel', icono = 'ri-bar-chart-box-line', codpadre = v_hotel,
            orden = 7, estado = 1, codsistema = 1
        WHERE codmodulo = v_reportes;
    END IF;

    INSERT INTO seguridad.moduloperfiles (codmodulo, codperfil, nuevo, editar, anular)
    SELECT m.codmodulo, p.codperfil, 1, 1, 1
    FROM seguridad.modulos m
    CROSS JOIN seguridad.perfiles p
    WHERE m.codmodulo IN (
        v_hotel,
        v_recepcion,
        v_habitaciones,
        v_ambientes,
        v_reservas,
        v_limpieza,
        v_mantenimiento,
        v_reportes
    )
      AND NOT EXISTS (
          SELECT 1
          FROM seguridad.moduloperfiles mp
          WHERE mp.codmodulo = m.codmodulo
            AND mp.codperfil = p.codperfil
      );
END $$;

INSERT INTO seguridad.moduloperfiles (codmodulo, codperfil, nuevo, editar, anular)
SELECT m.codmodulo, p.codperfil, 1, 1, 1
FROM seguridad.modulos m
CROSS JOIN seguridad.perfiles p
WHERE (m.url = 'hotel' OR m.url LIKE 'hotel/%')
  AND NOT EXISTS (
      SELECT 1
      FROM seguridad.moduloperfiles mp
      WHERE mp.codmodulo = m.codmodulo
        AND mp.codperfil = p.codperfil
  );

-- ============================================================
-- 9. PROGRAMACION AUTOMATICA SUNAT / CPE
-- ============================================================

CREATE TABLE IF NOT EXISTS sunat.programacion_cpe (
    codprogramacion SERIAL PRIMARY KEY,
    codempresa INTEGER NOT NULL,
    codsucursal INTEGER,
    descripcion VARCHAR(160) NOT NULL,
    modo_envio VARCHAR(20) NOT NULL DEFAULT 'programado'
        CHECK (modo_envio IN ('inmediato', 'programado', 'manual')),
    procesar_facturas INTEGER NOT NULL DEFAULT 1,
    procesar_boletas INTEGER NOT NULL DEFAULT 1,
    procesar_notas_credito INTEGER NOT NULL DEFAULT 1,
    procesar_notas_debito INTEGER NOT NULL DEFAULT 1,
    procesar_resumenes INTEGER NOT NULL DEFAULT 1,
    procesar_bajas INTEGER NOT NULL DEFAULT 1,
    max_intentos INTEGER NOT NULL DEFAULT 3,
    limite_por_ejecucion INTEGER NOT NULL DEFAULT 20,
    activo INTEGER NOT NULL DEFAULT 1,
    estado INTEGER NOT NULL DEFAULT 1,
    creado_en TIMESTAMP WITHOUT TIME ZONE DEFAULT now(),
    actualizado_en TIMESTAMP WITHOUT TIME ZONE DEFAULT now()
);

CREATE TABLE IF NOT EXISTS sunat.programacion_cpe_horarios (
    codhorario SERIAL PRIMARY KEY,
    codprogramacion INTEGER NOT NULL REFERENCES sunat.programacion_cpe(codprogramacion) ON DELETE CASCADE,
    hora TIME WITHOUT TIME ZONE NOT NULL,
    accion VARCHAR(30) NOT NULL DEFAULT 'todo'
        CHECK (accion IN ('todo', 'generar_resumen', 'reenviar')),
    estado INTEGER NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS sunat.programacion_cpe_historial (
    codejecucion SERIAL PRIMARY KEY,
    codprogramacion INTEGER,
    codhorario INTEGER,
    codempresa INTEGER NOT NULL,
    codsucursal INTEGER,
    origen VARCHAR(20) NOT NULL DEFAULT 'auto',
    accion VARCHAR(30) NOT NULL DEFAULT 'todo',
    inicio TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now(),
    fin TIMESTAMP WITHOUT TIME ZONE,
    estado VARCHAR(20) NOT NULL DEFAULT 'procesando',
    cantidad_procesada INTEGER NOT NULL DEFAULT 0,
    cantidad_error INTEGER NOT NULL DEFAULT 0,
    respuesta_sunat TEXT,
    errores TEXT
);

CREATE TABLE IF NOT EXISTS sunat.programacion_cpe_cola (
    codcola SERIAL PRIMARY KEY,
    codprogramacion INTEGER,
    codempresa INTEGER NOT NULL,
    codsucursal INTEGER,
    tipo VARCHAR(30) NOT NULL,
    referencia VARCHAR(80) NOT NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'pendiente',
    prioridad INTEGER NOT NULL DEFAULT 0,
    intentos INTEGER NOT NULL DEFAULT 0,
    siguiente_intento TIMESTAMP WITHOUT TIME ZONE,
    ultimo_estado_sunat INTEGER,
    ultimo_mensaje TEXT,
    creado_en TIMESTAMP WITHOUT TIME ZONE DEFAULT now(),
    actualizado_en TIMESTAMP WITHOUT TIME ZONE DEFAULT now()
);

CREATE TABLE IF NOT EXISTS sunat.programacion_cpe_logs (
    codlog SERIAL PRIMARY KEY,
    codejecucion INTEGER REFERENCES sunat.programacion_cpe_historial(codejecucion) ON DELETE CASCADE,
    nivel VARCHAR(20) NOT NULL DEFAULT 'info',
    referencia VARCHAR(100),
    mensaje TEXT,
    creado_en TIMESTAMP WITHOUT TIME ZONE DEFAULT now()
);

CREATE INDEX IF NOT EXISTS idx_programacion_cpe_horarios_hora
    ON sunat.programacion_cpe_horarios (hora)
    WHERE estado = 1;

CREATE INDEX IF NOT EXISTS idx_programacion_cpe_cola_pendientes
    ON sunat.programacion_cpe_cola (codempresa, estado, siguiente_intento, prioridad);

CREATE UNIQUE INDEX IF NOT EXISTS uq_programacion_cpe_cola_pendiente
    ON sunat.programacion_cpe_cola (tipo, referencia)
    WHERE estado IN ('pendiente', 'procesando', 'error');

INSERT INTO seguridad.modulos (
    codmodulo, descripcion, icono, url, codpadre, orden, estado,
    nuevo, editar, anular, consultar,
    clavenuevo, clavemodificar, claveanular, claveconsultar, codsistema
)
SELECT
    126, 'Programacion envios CPE', 'ri-timer-flash-line',
    'facturacion/programacionsunat', 8, 99, 1,
    1, 1, 1, 1,
    '', '', '', '', 1
WHERE NOT EXISTS (
    SELECT 1 FROM seguridad.modulos WHERE url = 'facturacion/programacionsunat'
);

INSERT INTO seguridad.moduloperfiles (codmodulo, codperfil, nuevo, editar, anular)
SELECT 126, p.codperfil, 1, 1, 1
FROM seguridad.perfiles p
WHERE EXISTS (SELECT 1 FROM seguridad.modulos WHERE codmodulo = 126)
  AND NOT EXISTS (
    SELECT 1
    FROM seguridad.moduloperfiles mp
    WHERE mp.codmodulo = 126
      AND mp.codperfil = p.codperfil
  );

-- ============================================================
-- NOTAS PROGRAMACION AUTOMATICA SUNAT / CPE
-- ============================================================
-- Tablas creadas:
--   - sunat.programacion_cpe
--   - sunat.programacion_cpe_horarios
--   - sunat.programacion_cpe_historial
--   - sunat.programacion_cpe_cola
--   - sunat.programacion_cpe_logs
--
-- Inserts por defecto:
--   - seguridad.modulos:
--       codmodulo: 126
--       descripcion: Programacion envios CPE
--       url: facturacion/programacionsunat
--       codpadre: 8
--   - seguridad.moduloperfiles:
--       se asigna el modulo 126 a todos los perfiles existentes.
--
-- Cron sugerido:
--   * * * * * cd /Applications/XAMPP/xamppfiles/htdocs/sistemas/phuyu_comercial && php index.php 
--facturacion/programacionsunat/cron
-- ============================================================

-- ============================================================
-- COMANDOS RECOMENDADOS DESPUES DE AGREGAR MIGRACIONES PHP
-- ============================================================
-- Para proyectos viejos:
-- /usr/bin/php7.4 $(which composer) install
-- /usr/bin/php7.4 $(which composer) dump-autoload -o
--
-- Para proyectos nuevos:
-- /usr/bin/php8.2 $(which composer) install
-- /usr/bin/php8.2 $(which composer) dump-autoload -o
-- ============================================================
-- FIN MIGRACIONES
-- ============================================================
