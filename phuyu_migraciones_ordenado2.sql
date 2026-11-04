 -- Reejecutable; no asigna enlaces ni modifica grupos existentes.
-- ============================================================
ALTER TABLE public.empresas
    ADD COLUMN IF NOT EXISTS soporte_whatsapp_grupo text NOT NULL DEFAULT '';

COMMENT ON COLUMN public.empresas.soporte_whatsapp_grupo IS
    'Enlace HTTPS de invitacion al grupo de soporte asignado a esta empresa en chat.whatsapp.com.';

-- ============================================================
-- FIN MIGRACIONES ORDENADAS 2
-- ============================================================
