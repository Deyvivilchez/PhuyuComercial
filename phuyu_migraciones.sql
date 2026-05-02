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