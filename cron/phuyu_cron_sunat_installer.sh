#!/bin/sh
set -eu

# Instalador restringido del cron maestro SUNAT.
# Uso:
#   phuyu_cron_sunat_installer.sh demo.comercial.phuyusystem.com
#
# Este script esta pensado para ejecutarse con sudo desde el panel web.
# Solo acepta nombres de proyecto tipo dominio y solo escribe en /etc/cron.d/
# con el formato esperado por Phuyu.

PROJECT="${1:-}"

case "$PROJECT" in
    *[!a-z0-9.-]*|"")
        echo "Nombre de proyecto no permitido" >&2
        exit 1
        ;;
esac

PROJECT_DIR="/var/www/$PROJECT"
CRON_NAME="$(printf '%s' "$PROJECT" | sed 's/[^a-z0-9_-]/-/g')"
CRON_FILE="/etc/cron.d/phuyu-$CRON_NAME-programacion-sunat"

if [ ! -f "$PROJECT_DIR/index.php" ]; then
    echo "No existe index.php en $PROJECT_DIR" >&2
    exit 1
fi

TMP_FILE="$(mktemp)"
trap 'rm -f "$TMP_FILE"' EXIT

{
    echo "# Cron base para Programacion de envios CPE / SUNAT"
    echo "# Proyecto: $PROJECT"
    echo "# Ejecuta cada minuto el despachador interno de tareas programadas."
    echo "* * * * * www-data cd $PROJECT_DIR && /usr/bin/php7.4 index.php facturacion/programacionsunat/cron >/dev/null 2>&1"
} > "$TMP_FILE"

install -o root -g root -m 0644 "$TMP_FILE" "$CRON_FILE"
echo "$CRON_FILE"
