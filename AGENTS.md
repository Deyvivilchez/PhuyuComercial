# Conservación de datos al actualizar

El propietario pide conservar en esta rama todos los archivos y ajustes de su facturación y de la empresa al actualizar el sistema.

- Antes de actualizar, cambiar de rama, limpiar archivos o aplicar migraciones, comprobar que exista un respaldo recuperable de los archivos operativos y de la base de datos.
- Conservar `sunat/` completo: certificados, claves, CDR, XML, comprobantes, resúmenes y demás archivos de facturación.
- Conservar también `application/sunat/`, `public/img/empresa/`, `public/img1/empresa/`, la configuración local en `application/config/`, `.env` y los ajustes locales del servidor web.
- Conservar en la base de datos la configuración de empresa, servicios de facturación, credenciales, series, correlativos e historial. Los ajustes de empresa y servicio se guardan, entre otras tablas, en `public.empresas` y `public.webservice`.
- Revisar archivos rastreados y no rastreados: `.gitignore` no protege un archivo ya rastreado. No usar limpiezas o reemplazos que eliminen estos datos.
- No incorporar certificados, claves, credenciales ni respaldos de datos a Git ni exponerlos en directorios públicos.
- Los respaldos en `/tmp` son temporales; conservar una copia duradera privada antes de una actualización definitiva.
