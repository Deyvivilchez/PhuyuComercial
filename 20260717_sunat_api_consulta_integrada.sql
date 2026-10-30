ALTER TABLE public.webservice
    ADD COLUMN IF NOT EXISTS sunat_api_client_id varchar(100) DEFAULT '',
    ADD COLUMN IF NOT EXISTS sunat_api_client_secret varchar(255) DEFAULT '';

ALTER TABLE sunat.kardexsunat
    ADD COLUMN IF NOT EXISTS consulta_api_estado varchar(10),
    ADD COLUMN IF NOT EXISTS consulta_api_fecha timestamp without time zone;
