-- Campos utilizados por Kardex_model::phuyu_kardexdetalle al retirar productos.
-- Aplicar solamente después de verificar el respaldo privado de la base de datos.
-- Conserva los movimientos existentes y permite ejecutar nuevamente la migración.
BEGIN;
SET LOCAL lock_timeout = '5s';
ALTER TABLE kardex.kardexalmacendetalle
    ADD COLUMN IF NOT EXISTS es_receta integer DEFAULT 0,
    ADD COLUMN IF NOT EXISTS codproducto_origen integer,
    ADD COLUMN IF NOT EXISTS codunidad_origen integer,
    ADD COLUMN IF NOT EXISTS item_origen integer,
    ADD COLUMN IF NOT EXISTS cantidad_origen numeric DEFAULT 0;
COMMIT;
