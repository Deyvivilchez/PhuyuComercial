-- PHUYU COMERCIAL - MIGRACIONES COMPLEMENTARIAS 2
-- Ejecutar despues de phuyu_migraciones_ordenado.sql sobre la base existente.
-- Antes de aplicar: verificar un respaldo recuperable y duradero privado de
-- la base de datos y de los archivos operativos indicados en AGENTS.md.
-- Detener la ejecucion ante errores (en psql: -v ON_ERROR_STOP=1).
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
-- CONSULTA INTEGRADA SUNAT, RECETAS Y PERMISO DE ELIMINAR VENTAS
-- Fuentes integradas:
--   20260717_sunat_api_consulta_integrada.sql
--   20261104_kardexalmacendetalle_recetas.sql
--   20261104_usuarios_eliminar_venta.sql
-- Estas columnas no estan incluidas en phuyu_migraciones_ordenado.sql.
-- Requiere las tablas base; si falta alguna, el bloque falla y se revierte.
-- Las tres incorporaciones se aplican juntas en esta transaccion.
-- IF NOT EXISTS permite ejecutar este bloque aunque ya se hayan aplicado
-- los scripts individuales; no corrige tipos/defaults de columnas existentes.
-- No elimina registros ni reemplaza credenciales, permisos o historial.
-- ============================================================
BEGIN;
SET LOCAL lock_timeout = '5s';

-- 1. Consulta integrada SUNAT: credenciales por empresa y ultima consulta.
-- No se incluyen claves reales. Los valores ya configurados se conservan.
ALTER TABLE public.webservice
    ADD COLUMN IF NOT EXISTS sunat_api_client_id varchar(100) DEFAULT '',
    ADD COLUMN IF NOT EXISTS sunat_api_client_secret varchar(255) DEFAULT '';

-- NULL indica que todavia no se ha registrado una consulta por la API.
ALTER TABLE sunat.kardexsunat
    ADD COLUMN IF NOT EXISTS consulta_api_estado varchar(10),
    ADD COLUMN IF NOT EXISTS consulta_api_fecha timestamp without time zone;

-- 2. Trazabilidad de recetas al retirar productos (Kardex_model).
-- Los movimientos previos conservan sus datos; las columnas nuevas toman
-- 0 para es_receta/cantidad_origen y NULL para referencias de origen.
ALTER TABLE kardex.kardexalmacendetalle
    ADD COLUMN IF NOT EXISTS es_receta integer DEFAULT 0,
    ADD COLUMN IF NOT EXISTS codproducto_origen integer,
    ADD COLUMN IF NOT EXISTS codunidad_origen integer,
    ADD COLUMN IF NOT EXISTS item_origen integer,
    ADD COLUMN IF NOT EXISTS cantidad_origen numeric DEFAULT 0;

-- 3. Permiso independiente de anulacion, asignado en Administracion > Usuarios.
-- Al crear la columna nadie recibe permiso de eliminar ventas (0).
-- Si ya existe, se respetan todos los permisos previamente asignados.
ALTER TABLE seguridad.usuarios
    ADD COLUMN IF NOT EXISTS eliminar_venta integer NOT NULL DEFAULT 0;

COMMIT;

-- ============================================================
-- FIN MIGRACIONES ORDENADAS 2
-- ============================================================
