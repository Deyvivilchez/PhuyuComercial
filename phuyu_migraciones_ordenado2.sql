-- Reejecutable; no asigna enlaces ni modifica grupos existentes.
-- ============================================================
ALTER TABLE public.empresas
    ADD COLUMN IF NOT EXISTS soporte_whatsapp_grupo text NOT NULL DEFAULT '';

COMMENT ON COLUMN public.empresas.soporte_whatsapp_grupo IS
    'Enlace HTTPS de invitacion al grupo de soporte asignado a esta empresa en chat.whatsapp.com.';

-- ============================================================
-- CAJAS POR USUARIO
-- ============================================================
-- Antes de esta funcionalidad, los usuarios podian elegir cualquier caja
-- activa de sus sucursales autorizadas. Conservar ese acceso SOLO al crear
-- la tabla. Al repetir la migracion no se reponen permisos retirados desde
-- Administracion > Usuarios ni se modifican asignaciones existentes.
DO $$
BEGIN
    IF to_regclass('seguridad.cajausuarios') IS NULL THEN
        CREATE TABLE seguridad.cajausuarios (
            codcajausuario SERIAL PRIMARY KEY,
            codusuario INTEGER NOT NULL,
            codsucursal INTEGER NOT NULL,
            codcaja INTEGER NOT NULL,
            estado INTEGER NOT NULL DEFAULT 1,
            CONSTRAINT uq_cajausuarios_usuario_sucursal_caja
                UNIQUE (codusuario, codsucursal, codcaja)
        );

        INSERT INTO seguridad.cajausuarios (codusuario, codsucursal, codcaja, estado)
        SELECT DISTINCT su.codusuario, c.codsucursal, c.codcaja, 1
        FROM seguridad.sucursalusuarios su
        INNER JOIN seguridad.usuarios u ON u.codusuario = su.codusuario
        INNER JOIN public.sucursales s ON s.codsucursal = su.codsucursal
        INNER JOIN caja.cajas c ON c.codsucursal = su.codsucursal
        WHERE u.estado = 1 AND s.estado = 1 AND c.estado = 1;
    END IF;
END $$;

-- ============================================================
-- FIN MIGRACIONES ORDENADAS 2
-- ============================================================
