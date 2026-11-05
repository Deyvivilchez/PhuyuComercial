-- Permiso independiente de anulacion; se asigna desde Administracion > Usuarios.
BEGIN;
ALTER TABLE seguridad.usuarios
    ADD COLUMN IF NOT EXISTS eliminar_venta integer NOT NULL DEFAULT 0;
COMMIT;
