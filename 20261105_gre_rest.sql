BEGIN;
ALTER TABLE sunat.guiasunat ADD COLUMN IF NOT EXISTS ticket_gre varchar(100);
ALTER TABLE sunat.guiasunat ADD COLUMN IF NOT EXISTS conductor_nombres varchar(150);
ALTER TABLE sunat.guiasunat ADD COLUMN IF NOT EXISTS conductor_apellidos varchar(150);
COMMENT ON COLUMN sunat.guiasunat.ticket_gre IS 'Ticket REST GRE SUNAT; consultar antes de cualquier reenvio';
COMMIT;
