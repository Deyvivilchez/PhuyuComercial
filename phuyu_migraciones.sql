-- agregar campos para validad si el producto debe llevar series o no 

ALTER TABLE almacen.productos
ADD COLUMN controlarseries INTEGER NOT NULL DEFAULT 0;

CREATE TABLE IF NOT EXISTS almacen.series (
    id_serie        SERIAL PRIMARY KEY,                   -- autoincremental
    codproducto     INTEGER NOT NULL,                     -- relación con productos
    serie_codigo    VARCHAR(120) NOT NULL,                -- número de serie o código único
    estado          VARCHAR(20) NOT NULL
                        DEFAULT 'EN_ALMACEN'
                        CHECK (estado IN ('EN_ALMACEN','RESERVADO','VENDIDO','DADO_BAJA','EN_PROVEEDOR')),
    comprobante     VARCHAR(120),                         -- doc. de venta o guía (opcional)
    fecha_ingreso   TIMESTAMPTZ,                          -- ingreso al almacén
    fecha_egreso    TIMESTAMPTZ,                          -- salida (venta/baja)
    motivo          VARCHAR(200),                         -- motivo del egreso o cambio de estado
    codalmacen INTEGER,
    codkardex  INTEGER,
    codkardex_egreso INTEGER,
    codsucursal INTEGER,
    CONSTRAINT fk_series_codproducto
        FOREIGN KEY (codproducto)
        REFERENCES almacen.productos (codproducto)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT uq_series_producto_serie UNIQUE (codproducto, serie_codigo)
);


-- actualizacion de iconos de modulos de acuerdo a nueva plantilla 

UPDATE seguridad.modulos
SET icono = CASE codmodulo
    WHEN 1 THEN 'ri-shopping-bag-3-line'      -- Ventas
    WHEN 2 THEN 'ri-truck-line'               -- Logistica
    WHEN 3 THEN 'ri-store-2-line'             -- Almacen
    WHEN 4 THEN 'ri-wallet-3-line'            -- Tesoreria
    WHEN 5 THEN 'ri-exchange-dollar-line'     -- Creditos
    WHEN 6 THEN 'ri-settings-3-line'          -- Administracion
    WHEN 7 THEN 'ri-bar-chart-box-line'       -- Reportes
    WHEN 8 THEN 'ri-file-upload-line'         -- CPE / envio de facturacion electronica
    WHEN 17 THEN 'ri-restaurant-2-line'       -- Restobar
    WHEN 118 THEN 'ri-plant-line'             -- Agricola
    WHEN 121 THEN 'ri-bar-chart-box-line'     -- Reportes
    WHEN 125 THEN 'ri-exchange-dollar-line'   -- Creditos
    ELSE icono
END
WHERE codmodulo IN (1,2,3,4,5,6,7,8,17,118,121,125)
  AND codpadre = 0;

  -- fin actualizacion de iconos de modulos de acuerdo a nueva plantilla


-- unificacion de modulos duplicados de Reportes y Creditos

UPDATE seguridad.modulos
SET descripcion = CASE codmodulo
    WHEN 51 THEN 'Cuentas por cobrar'
    WHEN 52 THEN 'Cuentas por pagar'
    WHEN 53 THEN 'Lista por cobrar'
    WHEN 54 THEN 'Lista por pagar'
    ELSE descripcion
END
WHERE codmodulo IN (51,52,53,54);

UPDATE seguridad.modulos
SET codpadre = 7,
    orden = CASE codmodulo
        WHEN 71 THEN 1   -- Ventas
        WHEN 113 THEN 2  -- Pedidos
        WHEN 115 THEN 3  -- Proformas
        WHEN 77 THEN 4   -- Vendedores
        WHEN 72 THEN 5   -- Compras
        WHEN 73 THEN 6   -- Productos
        WHEN 76 THEN 7   -- Ingresos y salidas de almacen
        WHEN 74 THEN 8   -- Caja y bancos
        WHEN 75 THEN 9   -- Creditos
        WHEN 117 THEN 10 -- Cuotas
        WHEN 120 THEN 11 -- Prestamos
        WHEN 104 THEN 12 -- Utilidades
        WHEN 114 THEN 13 -- Personas
        ELSE orden
    END
WHERE codmodulo IN (71,113,115,77,72,73,76,74,75,117,120,104,114);

UPDATE seguridad.modulos
SET codpadre = 5,
    orden = CASE codmodulo
        WHEN 51 THEN 1  -- Cuentas por cobrar
        WHEN 53 THEN 2  -- Lista por cobrar
        WHEN 110 THEN 3 -- Lista de cobranza
        WHEN 52 THEN 4  -- Cuentas por pagar
        WHEN 54 THEN 5  -- Lista por pagar
        WHEN 111 THEN 6 -- Lista de pagos
        ELSE orden
    END
WHERE codmodulo IN (51,53,110,52,54,111);

UPDATE seguridad.modulos
SET estado = 0
WHERE codmodulo IN (121,125)
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