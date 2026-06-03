<?php
/**
 * Panel web para gestionar el cron base de Programacion de envios CPE.
 *
 * Este archivo puede integrarse dentro del panel:
 *   /phuyu/w/facturacion/programacionsunat
 *
 * Tambien puede probarse directo pasando la URL del panel:
 *   /cron/gestionar_cron_sunat.php?panel_url=https://demo.comercial.phuyusystem.com/phuyu/w/facturacion/programacionsunat
 *
 * Importante:
 *   Crear archivos en /etc/cron.d normalmente requiere permisos de root.
 *   Si PHP corre como www-data sin permisos, el panel mostrara el error.
 */

declare(strict_types=1);

/**
 * Escapa texto para imprimirlo en HTML de forma segura.
 */
function phuyu_cron_h(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

/**
 * Obtiene la URL del panel actual.
 *
 * Si el script se integra dentro del panel, usa la URL actual.
 * Si se prueba de forma independiente, permite recibir ?panel_url=...
 */
function phuyu_cron_obtener_url_panel(): string
{
    if (!empty($_GET['panel_url'])) {
        return trim((string) $_GET['panel_url']);
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $esquema = $https ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? trim((string) $_SERVER['HTTP_HOST']) : '';
    $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';

    if ($host === '') {
        throw new RuntimeException('No se pudo detectar el host del proyecto.');
    }

    return $esquema . '://' . $host . $uri;
}

/**
 * Extrae el nombre del proyecto desde la URL del panel.
 *
 * Ejemplo:
 *   https://demo.comercial.phuyusystem.com/phuyu/w/facturacion/programacionsunat
 * Devuelve:
 *   demo.comercial.phuyusystem.com
 */
function phuyu_cron_extraer_nombre_proyecto(string $urlPanel): string
{
    $host = parse_url($urlPanel, PHP_URL_HOST);

    if (!is_string($host) || trim($host) === '') {
        throw new RuntimeException('No se pudo extraer el proyecto desde la URL del panel.');
    }

    $host = strtolower(trim($host));

    if (!preg_match('/^[a-z0-9.-]+$/', $host)) {
        throw new RuntimeException('El nombre del proyecto contiene caracteres no permitidos.');
    }

    return $host;
}

/**
 * Valida que la ruta final quede dentro de /var/www y pertenezca al proyecto.
 */
function phuyu_cron_ruta_proyecto(string $nombreProyecto): string
{
    return '/var/www/' . $nombreProyecto;
}

/**
 * Construye el nombre exacto del archivo cron base.
 *
 * Formato solicitado:
 *   phuyu-<nombre_proyecto>-programacion-sunat
 */
function phuyu_cron_archivo_cron(string $nombreProyecto): string
{
    return '/etc/cron.d/phuyu-' . $nombreProyecto . '-programacion-sunat';
}

/**
 * Genera el contenido del cron que ejecutara el proceso cada minuto.
 */
function phuyu_cron_contenido(string $nombreProyecto, string $rutaProyecto): string
{
    return "# Cron base para Programacion de envios CPE / SUNAT\n"
        . "# Proyecto: " . $nombreProyecto . "\n"
        . "# Ejecuta cada minuto el despachador interno de tareas programadas.\n"
        . "* * * * * www-data cd " . $rutaProyecto . " && /usr/bin/php7.4 index.php facturacion/programacionsunat/cron >/dev/null 2>&1\n";
}

/**
 * Crea o actualiza el archivo cron y aplica permisos 644.
 */
function phuyu_cron_guardar(string $archivoCron, string $contenidoCron): void
{
    if (file_put_contents($archivoCron, $contenidoCron, LOCK_EX) === false) {
        throw new RuntimeException('No se pudo crear el cron base. Verifique permisos de escritura sobre /etc/cron.d/.');
    }

    if (!chmod($archivoCron, 0644)) {
        throw new RuntimeException('El cron fue creado, pero no se pudo aplicar chmod 644.');
    }
}

$estado = 'error';
$mensaje = '';
$detalle = [];
$cronExiste = false;

try {
    // 1. Detectar proyecto desde URL del panel.
    $urlPanel = phuyu_cron_obtener_url_panel();
    $nombreProyecto = phuyu_cron_extraer_nombre_proyecto($urlPanel);
    $rutaProyecto = phuyu_cron_ruta_proyecto($nombreProyecto);
    $archivoCron = phuyu_cron_archivo_cron($nombreProyecto);
    $contenidoCron = phuyu_cron_contenido($nombreProyecto, $rutaProyecto);

    // 2. Verificar si existe cron base.
    $cronExiste = file_exists($archivoCron);

    // 3. Crear o recrear cron al recibir POST.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $accion = isset($_POST['accion_cron']) ? (string) $_POST['accion_cron'] : '';

        if (!in_array($accion, ['crear', 'recrear'], true)) {
            throw new RuntimeException('Accion no permitida.');
        }

        phuyu_cron_guardar($archivoCron, $contenidoCron);
        $cronExiste = true;
        $estado = 'ok';
        $mensaje = $accion === 'crear'
            ? 'Cron base creado correctamente'
            : 'Cron base recreado correctamente';
    } elseif ($cronExiste) {
        $estado = 'ok';
        $mensaje = 'Cron base encontrado';
    } else {
        $estado = 'pendiente';
        $mensaje = 'Cron base no encontrado';
    }

    $detalle = [
        'URL panel detectada' => $urlPanel,
        'Proyecto detectado' => $nombreProyecto,
        'Ruta base del proyecto' => $rutaProyecto,
        'Archivo cron base' => $archivoCron,
        'Comando programado' => trim($contenidoCron),
    ];
} catch (Throwable $e) {
    $estado = 'error';
    $mensaje = $e->getMessage();
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Programacion de envios CPE</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 24px;
            color: #202124;
            background: #f7f7f7;
        }
        .phuyu-cron-panel {
            max-width: 920px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 20px;
        }
        .phuyu-cron-estado {
            margin: 0 0 16px;
            font-size: 20px;
        }
        .ok { color: #146c2e; }
        .pendiente { color: #9a5b00; }
        .error { color: #b00020; }
        .phuyu-cron-detalle {
            display: grid;
            grid-template-columns: 190px minmax(0, 1fr);
            gap: 8px 14px;
            margin: 16px 0;
        }
        .phuyu-cron-detalle dt {
            font-weight: bold;
        }
        .phuyu-cron-detalle dd {
            margin: 0;
            min-width: 0;
        }
        code {
            display: inline-block;
            max-width: 100%;
            overflow-wrap: anywhere;
            background: #f1f3f4;
            border-radius: 4px;
            padding: 3px 5px;
        }
        .phuyu-cron-acciones {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 16px;
        }
        button {
            border: 1px solid #1a73e8;
            background: #1a73e8;
            color: #fff;
            border-radius: 4px;
            padding: 10px 14px;
            cursor: pointer;
        }
        button.secundario {
            background: #fff;
            color: #1a73e8;
        }
    </style>
</head>
<body>
    <section class="phuyu-cron-panel">
        <h1>Programacion de envios CPE</h1>

        <!-- Mensaje dinamico del estado del cron base. -->
        <h2 class="phuyu-cron-estado <?php echo phuyu_cron_h($estado); ?>">
            <?php echo phuyu_cron_h($mensaje); ?>
        </h2>

        <!-- Detalle tecnico para confirmar que el proyecto detectado es correcto. -->
        <?php if (!empty($detalle)): ?>
            <dl class="phuyu-cron-detalle">
                <?php foreach ($detalle as $etiqueta => $valor): ?>
                    <dt><?php echo phuyu_cron_h((string) $etiqueta); ?></dt>
                    <dd><code><?php echo phuyu_cron_h((string) $valor); ?></code></dd>
                <?php endforeach; ?>
            </dl>
        <?php endif; ?>

        <!-- Acciones: crear si no existe, recrear si ya existe. -->
        <?php if ($estado === 'pendiente'): ?>
            <form method="post" class="phuyu-cron-acciones">
                <input type="hidden" name="accion_cron" value="crear">
                <button type="submit">Crear cron base</button>
            </form>
        <?php elseif ($estado === 'ok'): ?>
            <form method="post" class="phuyu-cron-acciones">
                <input type="hidden" name="accion_cron" value="recrear">
                <button type="submit" class="secundario">Recrear cron base</button>
            </form>
        <?php endif; ?>

        <p>
            Este cron base ejecuta cada minuto el despachador interno. El despachador revisa la base de datos,
            genera resumenes y envia comprobantes CPE a SUNAT segun las programaciones activas.
        </p>
    </section>
</body>
</html>
