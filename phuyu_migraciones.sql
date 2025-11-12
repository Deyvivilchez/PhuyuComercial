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
    CONSTRAINT fk_series_codproducto
        FOREIGN KEY (codproducto)
        REFERENCES almacen.productos (codproducto)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT uq_series_producto_serie UNIQUE (codproducto, serie_codigo)
);
