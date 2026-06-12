<?php defined('BASEPATH') or exit('No direct script access allowed');

class Ventas extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('phuyu_model');
        $this->load->model('Caja_model');
        $this->load->model('Kardex_model');
    }

    private function phuyu_json($data)
    {
        $this->output->set_content_type('application/json', 'utf-8');
        echo json_encode($data);
    }

    private function phuyu_whatsapp_secret()
    {
        $secret = getenv('WHATSAPP_SHARE_SECRET');
        if ($secret !== false && $secret !== '') {
            return $secret;
        }

        return $this->config->item('encryption_key');
    }

    private function phuyu_whatsapp_token($codkardex, $formato, $expira)
    {
        return hash_hmac('sha256', (int)$codkardex . '|' . $formato . '|' . (int)$expira, $this->phuyu_whatsapp_secret());
    }

    private function phuyu_whatsapp_formato($formato)
    {
        $formato = strtolower(trim((string)$formato));
        return in_array($formato, ['a4', 'a5', 'ticket'], true) ? $formato : 'a4';
    }

    private function phuyu_whatsapp_telefono($telefono)
    {
        $telefono = preg_replace('/[\s\-\(\)]/', '', (string)$telefono);
        return preg_match('/^\+[1-9][0-9]{7,14}$/', $telefono) ? $telefono : '';
    }

    private function phuyu_whatsapp_url($codkardex, $formato)
    {
        $expira = time() + 86400;
        $token = $this->phuyu_whatsapp_token($codkardex, $formato, $expira);

        return base_url('facturacion/formato/' . $formato . '/' . (int)$codkardex) .
            '?wa=' . rawurlencode($token) . '&exp=' . $expira;
    }

    private function phuyu_whatsapp_url_publica($url)
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (empty($host)) {
            return false;
        }

        if (in_array(strtolower($host), ['localhost', '127.0.0.1', '::1'], true)) {
            return false;
        }

        if (preg_match('/(^|\.)local$/i', $host)) {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
        }

        return true;
    }

    private function phuyu_whatsapp_provider()
    {
        $provider = strtolower(trim((string)getenv('WHATSAPP_PROVIDER')));
        if ($provider !== '') {
            return $provider;
        }

        if (getenv('WHATSAPP_CLOUD_TOKEN') && getenv('WHATSAPP_CLOUD_PHONE_NUMBER_ID')) {
            return 'cloud';
        }

        if (getenv('TWILIO_ACCOUNT_SID') && getenv('TWILIO_AUTH_TOKEN') && getenv('TWILIO_WHATSAPP_FROM')) {
            return 'twilio';
        }

        return 'link';
    }

    private function phuyu_whatsapp_curl_json($url, $headers, $payload)
    {
        if (!function_exists('curl_init')) {
            return ['estado' => 0, 'mensaje' => 'La extensión cURL no está disponible en PHP.'];
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            return ['estado' => 0, 'mensaje' => 'No se pudo conectar con WhatsApp: ' . $error];
        }

        $body = json_decode($response, true);
        if ($httpCode >= 200 && $httpCode < 300) {
            return ['estado' => 1, 'mensaje' => 'Comprobante enviado por WhatsApp.'];
        }

        $mensaje = isset($body['error']['message']) ? $body['error']['message'] : $response;
        return ['estado' => 0, 'mensaje' => 'WhatsApp rechazó el envío: ' . $mensaje];
    }

    private function phuyu_whatsapp_imagen_base64($path)
    {
        if (empty($path) || !file_exists($path)) {
            return '';
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = ($extension === 'jpg' || $extension === 'jpeg') ? 'jpeg' : 'png';

        return 'data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    }

    private function phuyu_mesa_restaurante($codkardex)
    {
        $tablas = $this->db->query("
            SELECT
                to_regclass('kardex.kardexpedido') AS kardexpedido,
                to_regclass('restaurante.mesaspedido') AS mesaspedido
        ")->row_array();

        if (empty($tablas['kardexpedido']) || empty($tablas['mesaspedido'])) {
            return '';
        }

        $mesa = $this->db->query("
            SELECT string_agg(DISTINCT mp.nromesa::text, ' - ') AS numero
            FROM restaurante.mesaspedido mp
            WHERE mp.codpedido IN (
                SELECT p.codpedido
                FROM kardex.pedidos p
                WHERE p.codkardex = " . (int)$codkardex . "

                UNION

                SELECT kp.codpedido
                FROM kardex.kardexpedido kp
                WHERE kp.codkardex = " . (int)$codkardex . "
            )
        ")->row_array();

        return trim((string)($mesa['numero'] ?? ''));
    }

    private function phuyu_whatsapp_datos_pdf($codkardex, $formato)
    {
        $codkardex = (int)$codkardex;
        $codsucursal = (int)$_SESSION['phuyu_codsucursal'];

        $empresa = $this->db->query("
            SELECT documento, razonsocial, nombrecomercial
            FROM public.personas
            WHERE codpersona = 1
            LIMIT 1
        ")->row_array();

        $sucursal = $this->db->query("
            SELECT sucursal.*, empresa.*
            FROM public.sucursales AS sucursal
            INNER JOIN public.empresas AS empresa ON (sucursal.codempresa = empresa.codempresa)
            WHERE sucursal.codsucursal = {$codsucursal}
            LIMIT 1
        ")->row_array();

        $principal = $this->db->query("
            SELECT *
            FROM public.sucursales
            WHERE principal = 1 AND estado = 1
            LIMIT 1
        ")->row_array();

        $parametros = $this->db->query("
            SELECT *
            FROM public.empresas
            LIMIT 1
        ")->row_array();

        $venta = $this->db->query("
            SELECT
                k.codkardex,
                k.fechacomprobante,
                k.conleyendaamazonia,
                ct.descripcion AS comprobante,
                ct.oficial,
                k.codcomprobantetipo,
                k.seriecomprobante,
                k.nrocomprobante,
                p.documento,
                k.cliente,
                k.direccion,
                k.valorventa,
                k.descglobal,
                k.igv,
                k.importe,
                k.codempleado,
                k.condicionpago,
                k.nroplaca,
                k.codpersona,
                k.icbper
            FROM kardex.kardex AS k
            INNER JOIN public.personas AS p ON (k.codpersona = p.codpersona)
            INNER JOIN caja.comprobantetipos AS ct ON (k.codcomprobantetipo = ct.codcomprobantetipo)
            WHERE k.codkardex = {$codkardex}
            LIMIT 1
        ")->row_array();

        if (empty($venta)) {
            return ['estado' => 0, 'mensaje' => 'No se encontró la venta para generar el PDF.'];
        }

        $fechavencimiento = $venta['fechacomprobante'];
        $credito = [];
        if ((int)$venta['condicionpago'] === 2) {
            $credito = $this->db->query("
                SELECT *
                FROM kardex.creditos
                WHERE codkardex = {$codkardex}
                LIMIT 1
            ")->row_array();

            if (!empty($credito['fechavencimiento'])) {
                $fechavencimiento = $credito['fechavencimiento'];
            }
        }

        $empleado = $this->db->query("
            SELECT p.razonsocial
            FROM public.empleados e
            INNER JOIN public.personas p ON p.codpersona = e.codpersona
            WHERE e.codpersona = " . (int)$venta['codempleado'] . "
            LIMIT 1
        ")->row_array();

        $vendedor = $this->db->query("
            SELECT razonsocial, telefono
            FROM public.personas
            WHERE codpersona = " . (int)$venta['codempleado'] . "
            LIMIT 1
        ")->row_array();

        $totales = $this->db->query("
            SELECT
                (SELECT COALESCE(SUM(subtotal),0) FROM kardex.kardexdetalle WHERE codkardex = {$codkardex} AND codafectacionigv = '10') AS gravado,
                (SELECT COALESCE(SUM(subtotal),0) FROM kardex.kardexdetalle WHERE codkardex = {$codkardex} AND codafectacionigv = '20') AS exonerado,
                (SELECT COALESCE(SUM(subtotal),0) FROM kardex.kardexdetalle WHERE codkardex = {$codkardex} AND codafectacionigv = '30') AS inafecto,
                (SELECT COALESCE(SUM(subtotal),0) FROM kardex.kardexdetalle WHERE codkardex = {$codkardex} AND codafectacionigv = '21') AS gratuito
        ")->row_array();

        $detalle = $this->db->query("
            SELECT
                kd.item,
                kd.cantidad,
                p.descripcion AS producto,
                u.descripcion AS unidad,
                kd.preciounitario,
                kd.subtotal,
                kd.descripcion
            FROM kardex.kardexdetalle AS kd
            INNER JOIN almacen.productos AS p ON (p.codproducto = kd.codproducto)
            INNER JOIN almacen.unidades AS u ON (u.codunidad = kd.codunidad)
            WHERE kd.codkardex = {$codkardex}
            ORDER BY kd.item
        ")->result_array();

        $cuentascorrientes = $this->db->query("
            SELECT ct.*, b.descripcion AS banco
            FROM caja.ctasctes ct
            INNER JOIN caja.bancos b ON (ct.codbanco = b.codbanco)
            WHERE ct.codpersona = 1
        ")->result_array();

        $formatoComprobante = $this->db->query("
            SELECT *
            FROM caja.comprobantes
            WHERE codcomprobantetipo = " . (int)$venta['codcomprobantetipo'] . "
              AND seriecomprobante = " . $this->db->escape($venta['seriecomprobante']) . "
              AND codsucursal = {$codsucursal}
            LIMIT 1
        ")->row_array();

        if (empty($formatoComprobante)) {
            $formatoComprobante = [
                'nombrecomercial' => '',
                'logo' => '',
                'slogan' => '',
                'publicidad' => '',
                'agradecimiento' => '',
                'tipoconleyendaamazonia' => 0,
                'impresionlogo' => 1,
            ];
        }

        $nombre = $formatoComprobante['nombrecomercial'];
        if ($nombre === '') {
            $nombre = !empty($empresa['nombrecomercial']) ? $empresa['nombrecomercial'] : $empresa['razonsocial'];
        }

        if ($formatoComprobante['impresionlogo'] === '' || $formatoComprobante['impresionlogo'] === null) {
            $formatoComprobante['impresionlogo'] = 1;
        }

        $logoArchivo = !empty($formatoComprobante['logo'])
            ? FCPATH . 'public/img/empresa/' . $formatoComprobante['logo']
            : FCPATH . 'public/img/' . ($_SESSION['phuyu_logo'] ?? '');
        $logoSrc = $this->phuyu_whatsapp_imagen_base64($logoArchivo);

        $slogan = !empty($formatoComprobante['slogan']) ? $formatoComprobante['slogan'] : ($parametros['slogan'] ?? '');
        $publicidad = !empty($formatoComprobante['publicidad']) ? $formatoComprobante['publicidad'] : ($parametros['publicidad'] ?? '');

        $this->load->library('ciqrcode');
        $qrDir = FCPATH . 'sunat/webphuyu/';
        if (!is_dir($qrDir)) {
            @mkdir($qrDir, 0777, true);
        }

        $textoqr = $empresa['razonsocial'] . '|' .
            $venta['seriecomprobante'] . '|' .
            $venta['nrocomprobante'] . '|' .
            number_format((float)$venta['igv'], 2, '.', '') . '|' .
            number_format((float)$venta['importe'], 2, '.', '') . '|' .
            $venta['fechacomprobante'] . '|' .
            $venta['documento'];

        $qrFile = $qrDir . 'whatsapp_qr_' . $venta['seriecomprobante'] . '_' . $venta['nrocomprobante'] . '.png';
        $params = [
            'data' => $textoqr,
            'level' => 'H',
            'size' => 5,
            'savename' => $qrFile,
        ];
        $this->ciqrcode->generate($params);
        $qrSrc = $this->phuyu_whatsapp_imagen_base64($qrFile);

        $this->load->library('Number');
        $number = new Number();
        $totalTexto = $number->convertirNumeroEnLetras(round((float)$venta['importe'], 2));

        $totTotal = (string)number_format((float)$venta['importe'], 2, '.', '');
        $importeTexto = explode('.', $totTotal);
        $detalleImporteTexto = $number->convertirNumeroEnLetras($importeTexto[0]);
        $textoImporte = 'SON ' . strtoupper($detalleImporteTexto) . ' Y ' . $importeTexto[1] . '/100 SOLES';

        $movimiento = $this->db->query("
            SELECT codmovimiento
            FROM caja.movimientos
            WHERE codkardex = {$codkardex}
            LIMIT 1
        ")->row_array();

        $detallemovimiento = [];
        if (!empty($movimiento['codmovimiento'])) {
            $detallemovimiento = $this->db->query("
                SELECT importeentregado, vuelto
                FROM caja.movimientosdetalle
                WHERE codtipopago = 1
                  AND codmovimiento = " . (int)$movimiento['codmovimiento'] . "
            ")->result_array();
        }

        return [
            'estado' => 1,
            'data' => [
                'modo_pdf' => true,
                'empresa' => $empresa,
                'sucursal' => $sucursal,
                'principal' => $principal,
                'parametros' => $parametros,
                'venta' => $venta,
                'credito' => $credito,
                'empleado' => $empleado,
                'vendedor' => $vendedor,
                'totales' => $totales,
                'detalle' => $detalle,
                'cuentascorrientes' => $cuentascorrientes,
                'formato' => $formatoComprobante,
                'nombre_empresa' => $nombre,
                'nombre' => $nombre,
                'logo_src' => $logoSrc,
                'qr_src' => $qrSrc,
                'slogan' => $slogan,
                'publicidad' => $publicidad,
                'fechavencimiento' => $fechavencimiento,
                'total_texto' => $totalTexto,
                'texto_importe' => $textoImporte,
                'detallemovimiento' => $detallemovimiento,
                'efectivo' => !empty($detallemovimiento) ? 1 : 0,
                'logoEmpresa' => $_SESSION['phuyu_logo'] ?? '',
                'mesa_restaurante' => $this->phuyu_mesa_restaurante($codkardex),
            ],
        ];
    }

    private function phuyu_whatsapp_generar_pdf($codkardex, $formato, $filename)
    {
        $datos = $this->phuyu_whatsapp_datos_pdf($codkardex, $formato);
        if ($datos['estado'] != 1) {
            return $datos;
        }

        if ($formato === 'a5') {
            $view = 'reportes/ventas/a5venta';
            $paper = 'A5';
        } elseif ($formato === 'ticket') {
            $view = 'facturacion/formato/ticket_Phuyu';
            $items = count($datos['data']['detalle']);
            $height = max(650, min(1800, 560 + ($items * 34)));
            $paper = [0, 0, 226.77, $height];
        } else {
            $view = 'reportes/ventas/a4comprobante';
            $paper = 'A4';
        }

        $html = $this->load->view($view, $datos['data'], true);

        require_once FCPATH . 'vendor/autoload.php';

        $tempDir = FCPATH . 'application/cache/dompdf';
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0777, true);
        }

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('chroot', FCPATH);
        $options->set('tempDir', $tempDir);
        $options->set('fontDir', $tempDir);
        $options->set('fontCache', $tempDir);
        $options->set('dpi', 96);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper($paper, 'portrait');
        $dompdf->render();

        $dir = APPPATH . 'cache/whatsapp/';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        if (!is_dir($dir) || !is_writable($dir)) {
            return ['estado' => 0, 'mensaje' => 'No se puede escribir el PDF temporal de WhatsApp.'];
        }

        $path = $dir . uniqid('wa_', true) . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $filename);
        file_put_contents($path, $dompdf->output());

        return ['estado' => 1, 'path' => $path];
    }

    private function phuyu_whatsapp_subir_media_cloud($path, $filename)
    {
        if (!function_exists('curl_init')) {
            return ['estado' => 0, 'mensaje' => 'La extensión cURL no está disponible en PHP.'];
        }

        $token = getenv('WHATSAPP_CLOUD_TOKEN');
        $phoneNumberId = getenv('WHATSAPP_CLOUD_PHONE_NUMBER_ID');
        $apiVersion = getenv('WHATSAPP_CLOUD_API_VERSION');
        $apiVersion = $apiVersion !== false && $apiVersion !== '' ? $apiVersion : 'v20.0';

        if (!$token || !$phoneNumberId) {
            return ['estado' => 0, 'mensaje' => 'Faltan WHATSAPP_CLOUD_TOKEN o WHATSAPP_CLOUD_PHONE_NUMBER_ID.'];
        }

        if (!file_exists($path)) {
            return ['estado' => 0, 'mensaje' => 'No se encontró el PDF temporal para adjuntar.'];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'type' => 'application/pdf',
            'file' => new CURLFile($path, 'application/pdf', $filename),
        ];

        $ch = curl_init('https://graph.facebook.com/' . $apiVersion . '/' . rawurlencode($phoneNumberId) . '/media');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            return ['estado' => 0, 'mensaje' => 'No se pudo subir el PDF a WhatsApp: ' . $error];
        }

        $body = json_decode($response, true);
        if ($httpCode >= 200 && $httpCode < 300 && !empty($body['id'])) {
            return ['estado' => 1, 'media_id' => $body['id']];
        }

        $mensaje = isset($body['error']['message']) ? $body['error']['message'] : $response;
        return ['estado' => 0, 'mensaje' => 'WhatsApp rechazó el PDF: ' . $mensaje];
    }

    private function phuyu_whatsapp_enviar_cloud($telefono, $mensaje, $filename, $pdfPath)
    {
        if (!function_exists('curl_init')) {
            return ['estado' => 0, 'mensaje' => 'La extensión cURL no está disponible en PHP.'];
        }

        $media = $this->phuyu_whatsapp_subir_media_cloud($pdfPath, $filename);
        if (!isset($media['estado']) || (int)$media['estado'] !== 1 || empty($media['media_id'])) {
            return [
                'estado' => 0,
                'mensaje' => isset($media['mensaje']) ? $media['mensaje'] : 'No se pudo subir el PDF a WhatsApp.',
            ];
        }

        $token = getenv('WHATSAPP_CLOUD_TOKEN');
        $phoneNumberId = getenv('WHATSAPP_CLOUD_PHONE_NUMBER_ID');
        $apiVersion = getenv('WHATSAPP_CLOUD_API_VERSION');
        $apiVersion = $apiVersion !== false && $apiVersion !== '' ? $apiVersion : 'v20.0';

        if (!$token || !$phoneNumberId) {
            return ['estado' => 0, 'mensaje' => 'Faltan WHATSAPP_CLOUD_TOKEN o WHATSAPP_CLOUD_PHONE_NUMBER_ID.'];
        }

        $mediaId = trim((string)$media['media_id']);
        if ($mediaId === '') {
            return ['estado' => 0, 'mensaje' => 'WhatsApp no devolvió un MEDIA_ID válido para el PDF.'];
        }

        $url = 'https://graph.facebook.com/' . $apiVersion . '/' . rawurlencode($phoneNumberId) . '/messages';

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => ltrim($telefono, '+'),
            'type' => 'document',
            'document' => [
                'id' => $mediaId,
                'filename' => $filename,
                'caption' => $mensaje,
            ],
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            return ['estado' => 0, 'mensaje' => 'No se pudo conectar con WhatsApp: ' . $error];
        }

        $body = json_decode($response, true);
        if ($httpCode >= 200 && $httpCode < 300 && empty($body['error'])) {
            return [
                'estado' => 1,
                'mensaje' => 'Comprobante enviado por WhatsApp.',
                'meta' => $body,
                'media_id' => $mediaId,
            ];
        }

        $mensajeError = isset($body['error']['message']) ? $body['error']['message'] : $response;
        return [
            'estado' => 0,
            'mensaje' => 'Error WhatsApp: ' . $mensajeError,
            'media_id' => $mediaId,
        ];
    }
    private function phuyu_whatsapp_enviar_link_cloud($telefono, $mensaje)
    {
        if (!function_exists('curl_init')) {
            return ['estado' => 0, 'mensaje' => 'La extensión cURL no está disponible en PHP.'];
        }

        $token = getenv('WHATSAPP_CLOUD_TOKEN');
        $phoneNumberId = getenv('WHATSAPP_CLOUD_PHONE_NUMBER_ID');
        $apiVersion = getenv('WHATSAPP_CLOUD_API_VERSION');
        $apiVersion = $apiVersion !== false && $apiVersion !== '' ? $apiVersion : 'v20.0';

        if (!$token || !$phoneNumberId) {
            return ['estado' => 0, 'mensaje' => 'Faltan WHATSAPP_CLOUD_TOKEN o WHATSAPP_CLOUD_PHONE_NUMBER_ID.'];
        }

        $url = 'https://graph.facebook.com/' . $apiVersion . '/' . rawurlencode($phoneNumberId) . '/messages';

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => ltrim($telefono, '+'),
            'type' => 'text',
            'text' => [
                'preview_url' => true,
                'body' => $mensaje,
            ],
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            return ['estado' => 0, 'mensaje' => 'No se pudo conectar con WhatsApp: ' . $error];
        }

        $body = json_decode($response, true);
        if ($httpCode >= 200 && $httpCode < 300 && empty($body['error'])) {
            return [
                'estado' => 1,
                'mensaje' => 'Link enviado por WhatsApp.',
                'meta' => $body,
            ];
        }

        $mensajeError = isset($body['error']['message']) ? $body['error']['message'] : $response;
        return ['estado' => 0, 'mensaje' => 'Error WhatsApp: ' . $mensajeError];
    }

    private function phuyu_whatsapp_enviar_twilio($telefono, $mensaje, $mediaUrl, $formato)
    {
        if (!function_exists('curl_init')) {
            return ['estado' => 0, 'mensaje' => 'La extensión cURL no está disponible en PHP.'];
        }

        $sid = getenv('TWILIO_ACCOUNT_SID');
        $token = getenv('TWILIO_AUTH_TOKEN');
        $from = getenv('TWILIO_WHATSAPP_FROM');

        if (!$sid || !$token || !$from) {
            return ['estado' => 0, 'mensaje' => 'Faltan TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN o TWILIO_WHATSAPP_FROM.'];
        }

        $post = [
            'From' => strpos($from, 'whatsapp:') === 0 ? $from : 'whatsapp:' . $from,
            'To' => 'whatsapp:' . $telefono,
            'Body' => $mensaje,
        ];

        if ($formato !== 'ticket') {
            $post['MediaUrl'] = $mediaUrl;
        }

        $ch = curl_init('https://api.twilio.com/2010-04-01/Accounts/' . rawurlencode($sid) . '/Messages.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_USERPWD, $sid . ':' . $token);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            return ['estado' => 0, 'mensaje' => 'No se pudo conectar con Twilio: ' . $error];
        }

        $body = json_decode($response, true);
        if ($httpCode >= 200 && $httpCode < 300) {
            return ['estado' => 1, 'mensaje' => 'Comprobante enviado por WhatsApp.'];
        }

        $mensajeError = isset($body['message']) ? $body['message'] : $response;
        return ['estado' => 0, 'mensaje' => 'Twilio rechazó el envío: ' . $mensajeError];
    }

    public function index()
    {
        if ($this->input->is_ajax_request()) {
            if (isset($_SESSION['phuyu_codusuario'])) {
                /* CODIGO TEMPORAL DE LA IMPRESION */

                $formato = $this->db->query('select formato from caja.comprobantes where codsucursal = ' . $_SESSION['phuyu_codsucursal'])->result_array();
                if (count($formato) == 0) {
                    $_SESSION['phuyu_formato'] = 'a4';
                } else {
                    $_SESSION['phuyu_formato'] = $formato[0]['formato'];
                }

                /* FIN CODIGO TEMPORAL DE LA IMPRESION */

                $comprobante_almacen = $this->db
                    ->query(
                        "select count(*) as cantidad from caja.comprobantes
                        where (codcomprobantetipo=3 or codcomprobantetipo=4) and
                        codalmacen=" .
                            $_SESSION['phuyu_codalmacen'] .
                            ' and estado=1',
                    )
                    ->result_array();
                $almacen = $comprobante_almacen[0]['cantidad'];
                $caja = $_SESSION['phuyu_codcontroldiario'];
                $this->load->view('ventas/ventas/index', compact('almacen', 'caja'));
            } else {
                $this->load->view('phuyu/505');
            }
        } else {
            $this->load->view('phuyu/404');
        }
    }

    public function lista()
    {
        if ($this->input->is_ajax_request()) {
            $this->request = json_decode(file_get_contents('php://input'));
            $limit = 10;
            $offset = $this->request->pagina * $limit - $limit;

            if ($this->request->fechas->filtro == 0) {
                $fechas = '';
            } else {
                if (!empty($this->request->fechas->desde)) {
                    $fechas = "kardex.fechacomprobante>='" . $this->request->fechas->desde . "' and kardex.fechacomprobante<='" . $this->request->fechas->hasta . "' and";
                } else {
                    $fechas = "kardex.fechacomprobante<='" . $this->request->fechas->hasta . "' and";
                }
            }
            $lista = $this->db
                ->query(
                    "select kardex.hora,personas.documento,personas.telefono,kardex.cliente,kardex.codkardex, kardex.codcomprobantetipo, kardex.seriecomprobante,kardex.condicionpago, kardex.nrocomprobante, kardex.fechacomprobante,round(kardex.importe,2) as importe,kardex.estado, comprobantes.descripcion as tipo,comprobantes.abreviatura,
                    (
                        select string_agg(distinct mp.nromesa::text, ' - ')
                        from kardex.pedidos p
                        inner join restaurante.mesaspedido mp on mp.codpedido=p.codpedido
                        where p.codkardex=kardex.codkardex
                    ) as mesa_restaurante
                    from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where " .
                        $fechas .
                        " (UPPER(personas.documento) like UPPER('%" .
                        $this->request->buscar .
                        "%') or UPPER(personas.razonsocial) like UPPER('%" .
                        $this->request->buscar .
                        "%') or UPPER(kardex.cliente) like UPPER('%" .
                        $this->request->buscar .
                        "%') or UPPER(comprobantes.descripcion) like UPPER('%" .
                        $this->request->buscar .
                        "%') or UPPER(kardex.seriecomprobante) like UPPER('%" .
                        $this->request->buscar .
                        "%') or UPPER(kardex.nrocomprobante) like UPPER('%" .
                        $this->request->buscar .
                        "%') ) and kardex.codmovimientotipo=20 and kardex.codsucursal=" .
                        $_SESSION['phuyu_codsucursal'] .
                        ' order by kardex.fechacomprobante desc,kardex.hora desc offset ' .
                        $offset .
                        ' limit ' .
                        $limit,
                )
                ->result_array();

            foreach ($lista as $key => $value) {
                $info = $this->db->query('select codpedido from kardex.kardexpedido where codkardex=' . $value['codkardex'])->result_array();

                if (count($info) > 0) {
                    $pedido = $this->db->query('select seriecomprobante,nrocomprobante from kardex.pedidos where codpedido=' . $info[0]['codpedido'])->result_array();

                    $lista[$key]['referencia'] = $pedido[0]['seriecomprobante'] . '-' . $pedido[0]['nrocomprobante'];
                } else {
                    $lista[$key]['referencia'] = '';
                }

                $hora = explode('.', $lista[$key]['hora']);
                $lista[$key]['hora'] = $hora[0];

                $kardexsunat = $this->db->query('select estado from sunat.kardexsunat where codkardex = ' . $value['codkardex'])->result_array();

                if (count($kardexsunat)) {
                    $lista[$key]['estadosunat'] = $kardexsunat[0]['estado'];
                } else {
                    $lista[$key]['estadosunat'] = 2;
                }
            }

            $total = $this->db->query('select count(*) as total from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where ' . $fechas . " (UPPER(personas.documento) like UPPER('%" . $this->request->buscar . "%') or UPPER(personas.razonsocial) like UPPER('%" . $this->request->buscar . "%') or UPPER(personas.nombrecomercial) like UPPER('%" . $this->request->buscar . "%') or UPPER(comprobantes.descripcion) like UPPER('%" . $this->request->buscar . "%') or UPPER(kardex.seriecomprobante) like UPPER('%" . $this->request->buscar . "%') or UPPER(kardex.nrocomprobante) like UPPER('%" . $this->request->buscar . "%') ) and kardex.codmovimientotipo=20 and kardex.codsucursal=" . $_SESSION['phuyu_codsucursal'])->result_array();

            $paginas = floor($total[0]['total'] / $limit);
            if ($total[0]['total'] % $limit != 0) {
                $paginas = $paginas + 1;
            }

            $paginacion = [];
            $paginacion['total'] = $total[0]['total'];
            $paginacion['actual'] = $this->request->pagina;
            $paginacion['ultima'] = $paginas;
            $paginacion['desde'] = $offset;
            $paginacion['hasta'] = $offset + $limit;

            echo json_encode(['lista' => $lista, 'paginacion' => $paginacion]);
        } else {
            $this->load->view('phuyu/404');
        }
    }

    public function buscar_lista()
    {
        if ($this->input->is_ajax_request()) {
            $this->request = json_decode(file_get_contents('php://input'));
            $limit = 10;
            $offset = $this->request->pagina * $limit - $limit;

            if (isset($this->request->tabla) && $this->request->tabla == 'compra') {
                $movimiento = 2;
            } else {
                $movimiento = 20;
            }

            $lista = $this->db
                ->query(
                    "SELECT p.documento,kardex.cliente,kardex.codpersona,kardex.codalmacen,kardex.codkardex, kardex.codcomprobantetipo, kardex.seriecomprobante,kardex.condicionpago, kardex.nrocomprobante, kardex.fechacomprobante,round(kardex.importe,2) as importe,kardex.estado, comprobantes.descripcion as tipo,kardex.hora from kardex.kardex as kardex JOIN kardex.kardexdetalle kd ON kardex.codkardex = kd.codkardex JOIN public.personas as p on (kardex.codpersona=p.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) WHERE (UPPER(p.documento) ilike UPPER('%" .
                        $this->request->buscar .
                        "%') or UPPER(p.razonsocial) like UPPER('%" .
                        $this->request->buscar .
                        "%') or UPPER(kardex.cliente) like UPPER('%" .
                        $this->request->buscar .
                        "%') or UPPER(kardex.seriecomprobante) like UPPER('%" .
                        $this->request->buscar .
                        "%') or UPPER(kardex.nrocomprobante) like UPPER('%" .
                        $this->request->buscar .
                        "%') ) and kardex.codsucursal=" .
                        $_SESSION['phuyu_codsucursal'] .
                        ' and kardex.estado=1 and kardex.codmovimientotipo=' .
                        $movimiento .
                        ' GROUP BY kardex.codkardex, kardex.codsucursal,kardex.codpersona, kardex.importe, comprobantes.descripcion, kardex.cliente,kardex.fechacomprobante,kardex.hora,kardex.seriecomprobante,kardex.nrocomprobante,p.documento HAVING sum(kd.cantidadguia) < sum(kd.cantidad) order by kardex.nrocomprobante desc offset ' .
                        $offset .
                        ' limit ' .
                        $limit,
                )
                ->result_array();

            foreach ($lista as $key => $value) {
                $almacen = $this->db->query('select *from almacen.almacenes where codalmacen=' . $value['codalmacen'])->result_array();
                $persona = $this->db->query('select *from personas where codpersona=' . $value['codpersona'])->result_array();

                $lista[$key]['direccionpartida'] = $almacen[0]['direccion'];
                $lista[$key]['direcciondestino'] = $persona[0]['direccion'];

                $ubigeopartida = $this->db->query('select *from ubigeo where codubigeo=' . $almacen[0]['codubigeo'])->result_array();
                $lista[$key]['ubigeodescripcion'] = $ubigeopartida[0]['departamento'] . ', ' . $ubigeopartida[0]['provincia'] . ', ' . $ubigeopartida[0]['distrito'];
                $lista[$key]['ubigeopartida'] = $almacen[0]['codubigeo'];

                $ubigeodestino = $this->db->query('select *from ubigeo where codubigeo=' . $persona[0]['codubigeo'])->result_array();
                $lista[$key]['ubigeodescripciondestino'] = '';
                $lista[$key]['ubigeodestino'] = null;
                if (count($ubigeodestino)) {
                    $lista[$key]['ubigeodescripciondestino'] = $ubigeodestino[0]['departamento'] . ', ' . $ubigeodestino[0]['provincia'] . ', ' . $ubigeodestino[0]['distrito'];
                    $lista[$key]['ubigeodestino'] = $persona[0]['codubigeo'];
                }
            }

            $total = $this->db->query("select count(*) as total FROM kardex.kardex k JOIN kardex.kardexdetalle kd ON k.codkardex = kd.codkardex JOIN public.personas as p on (k.codpersona=p.codpersona) WHERE (UPPER(p.documento) ilike UPPER('%" . $this->request->buscar . "%') or UPPER(p.razonsocial) like UPPER('%" . $this->request->buscar . "%') or UPPER(k.cliente) like UPPER('%" . $this->request->buscar . "%') or UPPER(k.seriecomprobante) like UPPER('%" . $this->request->buscar . "%') or UPPER(k.nrocomprobante) like UPPER('%" . $this->request->buscar . "%') ) and k.codsucursal=" . $_SESSION['phuyu_codsucursal'] . ' and k.estado=1 and k.codmovimientotipo=' . $movimiento . ' HAVING sum(kd.cantidadguia) < sum(kd.cantidad)')->result_array();

            //print_r($total[0]["total"]);exit;
            $total = count($total) > 0 ? $total[0]['total'] : 0;
            $paginas = floor($total / $limit);
            if ($total % $limit != 0) {
                $paginas = $paginas + 1;
            }

            $paginacion = [];
            $paginacion['total'] = $total;
            $paginacion['actual'] = $this->request->pagina;
            $paginacion['ultima'] = $paginas;
            $paginacion['desde'] = $offset;
            $paginacion['hasta'] = $offset + $limit;

            echo json_encode(['lista' => $lista, 'paginacion' => $paginacion]);
        }
    }

    public function buscar()
    {
        if ($this->input->is_ajax_request()) {
            if (isset($_SESSION['phuyu_codusuario'])) {
                $this->load->view('ventas/ventas/buscar');
            } else {
                $this->load->view('phuyu/505');
            }
        } else {
            $this->load->view('phuyu/404');
        }
    }

    public function buscarproductos($codkardex)
    {
        if ($this->input->is_ajax_request()) {
            $productos = $this->db->query('select pd.*,p.descripcion,u.descripcion as unidad FROM kardex.kardexdetalle pd JOIN almacen.productos p ON pd.codproducto = p.codproducto JOIN almacen.unidades u ON pd.codunidad = u.codunidad where codkardex=' . $codkardex . ' and pd.cantidad > pd.cantidadguia')->result_array();

            foreach ($productos as $key => $value) {
                $unidades = $this->db->query('select *FROM almacen.v_productounidades pun where pun.codproducto=' . $value['codproducto'] . ' ')->result_array();

                $productos[$key]['unidades'] = $unidades[0]['unidades'];
            }

            echo json_encode($productos);
        } else {
            $this->load->view('phuyu/404');
        }
    }

    public function buscarventa($codkardex)
    {
        if ($this->input->is_ajax_request()) {
            $venta = $this->db->query('select k.*, comprobantes.descripcion as tipo FROM kardex.kardex k inner join caja.comprobantetipos as comprobantes on(k.codcomprobantetipo=comprobantes.codcomprobantetipo) where k.codkardex=' . $codkardex . ' and k.estado = 1')->result_array();
            echo json_encode($venta);
        } else {
            $this->load->view('phuyu/404');
        }
    }

    public function nuevo()
    {
        if ($this->input->is_ajax_request()) {
            if (isset($_SESSION['phuyu_codusuario'])) {
                $perfil = '';
                if ($_SESSION['phuyu_codperfil'] > 3) {
                    $perfil .= ' AND empleado.codpersona = ' . $_SESSION['phuyu_codempleado'];
                }
                $monedas = $this->db->query('select *from caja.monedas where estado=1 order by codmoneda asc')->result_array();
                $comprobantes = $this->db->query('select distinct(ct.codcomprobantetipo) as codigo, ct.* from caja.comprobantetipos as ct inner join caja.comprobantes as c on(ct.codcomprobantetipo=c.codcomprobantetipo) where c.codsucursal=' . $_SESSION['phuyu_codsucursal'] . ' and ct.venta = 1 and c.estado=1')->result_array();
                $conceptos = $this->db->query('select *from caja.conceptos where codconcepto=13 or codconcepto=15')->result_array();
                $tipopagos = $this->db->query('select *from caja.tipopagos where ingreso=1 and estado=1 order by codtipopago')->result_array();
                $vendedores = $this->db
                    ->query(
                        "select persona.codpersona,persona.razonsocial
                            from public.personas as persona
                            inner join public.empleados as
                            empleado on(persona.codpersona=empleado.codpersona) where empleado.estado=1 " .
                            $perfil .
                            '',
                    )
                    ->result_array();
                $sucursal = $this->db->query('select coalesce(codcomprobantetipo,12) as codcomprobantetipo, seriecomprobante from public.sucursales where codsucursal=' . $_SESSION['phuyu_codsucursal'])->result_array();
                $centrocostos = $this->db->query('select *from caja.centrocostos where estado=1')->result_array();
                $afectacionigv = $this->db->query('select *from afectacionigv where estado = 1')->result_array();
                $this->load->view('ventas/ventas/nuevo', compact('comprobantes', 'conceptos', 'tipopagos', 'vendedores', 'sucursal', 'centrocostos', 'monedas', 'afectacionigv'));
            } else {
                $this->load->view('phuyu/505');
            }
        } else {
            $this->load->view('phuyu/404');
        }
    }

    function ver($codregistro)
    {
        if ($this->input->is_ajax_request()) {
            if (isset($_SESSION['phuyu_codusuario'])) {
                $info = $this->db
                    ->query(
                        "select kardex.*,p.documento,(CASE WHEN condicionpago = 1 THEN 'CONTADO' ELSE 'CREDITO' END) AS pago,
                            mt.descripcion AS movimiento,
                            comprobantes.descripcion as tipo
                            from kardex.kardex as kardex
                            inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo)
                            INNER JOIN public.personas as p on (kardex.codpersona=p.codpersona)
                            INNER JOIN almacen.movimientotipos as mt on (kardex.codmovimientotipo=mt.codmovimientotipo)
                            where kardex.codkardex=" . $codregistro,
                    )
                    ->result_array();

                $detalle = $this->db->query('select kd.*,p.descripcion as producto,u.descripcion as unidad,p.codigo from kardex.kardexdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codkardex=' . $codregistro . ' and kd.estado=1 order by kd.item')->result_array();

                $pagos = $this->db
                    ->query(
                        "select p.descripcion as tipopago,
                            md.importe,
                            md.importeentregado,
                            md.vuelto,
                            md.nrodocbanco
                            from caja.movimientos as m
                            inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento)
                            inner join caja.tipopagos as p on(md.codtipopago=p.codtipopago)
                            where m.codkardex=" .
                            $codregistro .
                            ' and m.estado=1 order by p.codtipopago',
                    )
                    ->result_array();
                $this->load->view('ventas/ventas/ver', compact('info', 'detalle', 'pagos'));
            } else {
                $this->load->view('phuyu/505');
            }
        } else {
            $this->load->view('phuyu/404');
        }
    }

    function guardar_07032026()
    {
        if ($this->input->is_ajax_request()) {
            if (isset($_SESSION['phuyu_codusuario'])) {
                $this->request = json_decode(file_get_contents('php://input'));
                //echo 1;exit;
                //REVISAMOS SI EL PEDIDO SIGUE ACTIVO
                if ($this->request->codpedido != 0) {
                    $info = $this->db->query('select *from kardex.pedidos where codpedido=' . $this->request->codpedido)->result_array();
                    if ($info[0]['estado'] == 0) {
                        echo json_encode('e');
                        exit();
                    }
                }
                if ($this->request->codproforma != 0) {
                    $info = $this->db->query('select *from kardex.proformas where codproforma=' . $this->request->codproforma)->result_array();
                    if ($info[0]['estado'] == 0) {
                        echo json_encode('e');
                        exit();
                    }
                }
                $this->request->campos->codpersona = $this->request->codpersonapedido == 0 ? $this->request->campos->codpersona : $this->request->codpersonapedido;

                //VERIFICAMOS SI ES BOLETA Y EL IMPORTE SEA MENOR A 700
                if ($this->request->campos->codpersona == 2 && $this->request->campos->codcomprobantetipo == 12) {
                    if ($this->request->totales->importe >= 700) {
                        echo json_encode('e');
                        exit();
                    }
                }
                $this->request->campos->codlote = !isset($this->request->campos->codlote) || empty($this->request->campos->codlote) ? 0 : $this->request->campos->codlote;
                $this->db->trans_begin();

                /* REGISTRO KARDEX Y KARDEXDETALLE */
                $codkardex = $this->Kardex_model->phuyu_kardex($this->request->campos, $this->request->totales, 0);
                $codkardexalmacen = 0;
                $retirar = $this->request->campos->retirar;
                $estado = 1;
                if ($retirar == 1) {
                    $codkardexalmacen = $this->Kardex_model->phuyu_kardexalmacen($codkardex, 4, $this->request->campos);
                }

                $detalle = $this->Kardex_model->phuyu_kardexdetalle($codkardex, $codkardexalmacen, $this->request->detalle, $retirar, 0, $this->request->codpedido, $this->request->codproforma);

                //echo json_encode($detalle['success']);exit;
                if (!$detalle['success']) {
                    $data['estado'] = 0;
                    $data['informacion'] = $detalle;
                    echo json_encode($data);
                    exit();
                }
                if ($this->request->codpedido != 0) {
                    $detallepedido = $this->phuyu_model->phuyu_pedidodetalle($this->request->codpedido, $this->request->detalle);
                    if ($this->request->campos->terminarpedido == true) {
                        $campos = ['estadoproceso'];
                        $valores = [1];
                        $estado_u = $this->phuyu_model->phuyu_editar('kardex.pedidos', $campos, $valores, 'codpedido', $this->request->codpedido);
                    }
                }
                if ($this->request->codproforma != 0) {
                    $detalleproforma = $this->phuyu_model->phuyu_proformadetalle($this->request->codproforma, $this->request->detalle);
                    if ($this->request->campos->terminarpedido == true) {
                        $campos = ['estadoproceso'];
                        $valores = [1];

                        $estado_u = $this->phuyu_model->phuyu_editar('kardex.proformas', $campos, $valores, 'codproforma', $this->request->codproforma);
                    }
                }

                /* REGISTRO MOVIMIENTO DE CAJA */
                if ($this->request->campos->codmoneda != 1) {
                    $importe = round($this->request->totales->importe * $this->request->campos->tipocambio, 2);
                    $importemoneda = $this->request->totales->importe;
                } else {
                    $importe = $this->request->totales->importe;
                    $importemoneda = $this->request->totales->importe;
                }

                $codmovimiento = $this->Caja_model->phuyu_movimientos($codkardex, 1, 1, $importe, $this->request->campos, $importemoneda);

                if ($codmovimiento == 0) {
                    $data['estado'] = 3;
                    $data['informacion'] = 'La venta se interrumpió porque la caja que usted está utilizando está cerrada, vuelve a iniciar sesión';
                    echo json_encode($data);
                    exit();
                }

                if ($this->request->campos->condicionpago == 1) {
                    $estado = $this->Caja_model->phuyu_movimientosdetalle($codmovimiento, $this->request->pagos);
                }

                /* REGISTRO CREDITO POR COBRAR */

                if ($this->request->campos->condicionpago == 2) {
                    $persona = $this->db->query('select documento,d.abreviatura as tipo from public.personas p inner join public.documentotipos d on(p.coddocumentotipo=d.coddocumentotipo) where p.codpersona=' . $this->request->campos->codpersona)->result_array();

                    $estado = $this->Caja_model->phuyu_credito($codkardex, $codmovimiento, 1, $this->request->campos, $this->request->totales, $this->request->cuotas, $persona[0]['tipo'] . '-' . $persona[0]['documento']);
                }

                /* COMPROBANTE ELECTRONICO PARA SUNAT: REGISTRO EN KARDEX SUNAT */

                if ($this->request->campos->codcomprobantetipo == 10 || $this->request->campos->codcomprobantetipo == 12) {
                    $kardex = $this->db->query('select nrocomprobante from kardex.kardex where codkardex=' . $codkardex)->result_array();
                    if ($this->request->campos->codcomprobantetipo == 10) {
                        $xml = $_SESSION['phuyu_ruc'] . '-01-' . $this->request->campos->seriecomprobante . '-' . $kardex[0]['nrocomprobante'];
                    } else {
                        $xml = $_SESSION['phuyu_ruc'] . '-03-' . $this->request->campos->seriecomprobante . '-' . $kardex[0]['nrocomprobante'];
                    }
                    $campos = ['codkardex', 'codsucursal', 'codusuario', 'fechacreado', 'nombre_xml'];
                    $valores = [(int) $codkardex, (int) $_SESSION['phuyu_codsucursal'], (int) $_SESSION['phuyu_codusuario'], $this->request->campos->fechacomprobante, $xml];
                    $estado = $this->phuyu_model->phuyu_guardar('sunat.kardexsunat', $campos, $valores);
                }

                if ($this->db->trans_status() === false) {
                    $this->db->trans_rollback();
                    $estado = 0;
                } else {
                    if ($estado != 1) {
                        $this->db->trans_rollback();
                        $estado = 0;
                    }
                    $this->db->trans_commit();
                }

                $data['estado'] = $estado;
                $data['codkardex'] = $codkardex;

                if ($this->request->campos->condicionpago == 2 && $this->request->campos->inicial > 0) {
                    $data['info_credito'] = $this->db
                        ->query(
                            "
                            SELECT *
                            FROM kardex.creditos
                            WHERE codkardex = " .
                                (int) $codkardex .
                                "
                            AND estado <> 0
                            ORDER BY codcredito DESC
                            LIMIT 1 ",
                        )
                        ->row_array();
                }

                echo json_encode($data);
            } else {
                echo json_encode('e');
            }
        } else {
            $this->load->view('phuyu/404');
        }
    }



    function guardar_100326()
    {
        if ($this->input->is_ajax_request()) {
            if (isset($_SESSION['phuyu_codusuario'])) {
                $this->request = json_decode(file_get_contents('php://input'));

                // REVISAMOS SI EL PEDIDO SIGUE ACTIVO
                if ($this->request->codpedido != 0) {
                    $info = $this->db->query('select *from kardex.pedidos where codpedido=' . (int)$this->request->codpedido)->result_array();
                    if (count($info) > 0 && $info[0]['estado'] == 0) {
                        echo json_encode('e');
                        return;
                    }
                }

                if ($this->request->codproforma != 0) {
                    $info = $this->db->query('select *from kardex.proformas where codproforma=' . (int)$this->request->codproforma)->result_array();
                    if (count($info) > 0 && $info[0]['estado'] == 0) {
                        echo json_encode('e');
                        return;
                    }
                }

                $this->request->campos->codpersona = $this->request->codpersonapedido == 0
                    ? $this->request->campos->codpersona
                    : $this->request->codpersonapedido;

                // VALIDAMOS SI ES BOLETA Y EL IMPORTE SEA MENOR A 700
                if ($this->request->campos->codpersona == 2 && $this->request->campos->codcomprobantetipo == 12) {
                    if ($this->request->totales->importe >= 700) {
                        echo json_encode('e');
                        return;
                    }
                }

                $this->request->campos->codlote = !isset($this->request->campos->codlote) || empty($this->request->campos->codlote)
                    ? 0
                    : $this->request->campos->codlote;

                $this->db->trans_begin();

                try {
                    /* REGISTRO KARDEX Y KARDEX DETALLE */
                    $codkardex = $this->Kardex_model->phuyu_kardex($this->request->campos, $this->request->totales, 0);
                    $codkardexalmacen = 0;
                    $retirar = $this->request->campos->retirar;
                    $estado = 1;

                    if ($retirar == 1) {
                        $codkardexalmacen = $this->Kardex_model->phuyu_kardexalmacen($codkardex, 4, $this->request->campos);
                    }

                    $detalle = $this->Kardex_model->phuyu_kardexdetalle(
                        $codkardex,
                        $codkardexalmacen,
                        $this->request->detalle,
                        $retirar,
                        0,
                        $this->request->codpedido,
                        $this->request->codproforma
                    );

                    // SI FALLA STOCK O DETALLE, HACER ROLLBACK Y SALIR
                    if (!isset($detalle['success']) || !$detalle['success']) {
                        $this->db->trans_rollback();

                        $data = [];
                        $data['estado'] = 0;
                        $data['informacion'] = $detalle;
                        echo json_encode($data);
                        return;
                    }

                    if ($this->request->codpedido != 0) {
                        $this->phuyu_model->phuyu_pedidodetalle($this->request->codpedido, $this->request->detalle);

                        if ($this->request->campos->terminarpedido == true) {
                            $campos = ['estadoproceso'];
                            $valores = [1];
                            $this->phuyu_model->phuyu_editar('kardex.pedidos', $campos, $valores, 'codpedido', $this->request->codpedido);
                        }
                    }

                    if ($this->request->codproforma != 0) {
                        $this->phuyu_model->phuyu_proformadetalle($this->request->codproforma, $this->request->detalle);

                        if ($this->request->campos->terminarpedido == true) {
                            $campos = ['estadoproceso'];
                            $valores = [1];
                            $this->phuyu_model->phuyu_editar('kardex.proformas', $campos, $valores, 'codproforma', $this->request->codproforma);
                        }
                    }

                    /* REGISTRO MOVIMIENTO DE CAJA */
                    if ($this->request->campos->codmoneda != 1) {
                        $importe = round($this->request->totales->importe * $this->request->campos->tipocambio, 2);
                        $importemoneda = $this->request->totales->importe;
                    } else {
                        $importe = $this->request->totales->importe;
                        $importemoneda = $this->request->totales->importe;
                    }

                    $codmovimiento = $this->Caja_model->phuyu_movimientos(
                        $codkardex,
                        1,
                        1,
                        $importe,
                        $this->request->campos,
                        $importemoneda
                    );

                    if ($codmovimiento == 0) {
                        $this->db->trans_rollback();

                        $data = [];
                        $data['estado'] = 3;
                        $data['informacion'] = 'La venta se interrumpió porque la caja que usted está utilizando está cerrada, vuelve a iniciar sesión';
                        echo json_encode($data);
                        return;
                    }

                    if ($this->request->campos->condicionpago == 1) {
                        $estado = $this->Caja_model->phuyu_movimientosdetalle($codmovimiento, $this->request->pagos);
                        if ($estado != 1) {
                            $this->db->trans_rollback();

                            $data = [];
                            $data['estado'] = 0;
                            $data['informacion'] = 'No se pudo registrar el detalle del pago.';
                            echo json_encode($data);
                            return;
                        }
                    }

                    /* REGISTRO CREDITO POR COBRAR */
                    if ($this->request->campos->condicionpago == 2) {
                        $persona = $this->db->query(
                            'select documento,d.abreviatura as tipo ' .
                            'from public.personas p ' .
                            'inner join public.documentotipos d on(p.coddocumentotipo=d.coddocumentotipo) ' .
                            'where p.codpersona=' . (int)$this->request->campos->codpersona
                        )->result_array();

                        $estado = $this->Caja_model->phuyu_credito(
                            $codkardex,
                            $codmovimiento,
                            1,
                            $this->request->campos,
                            $this->request->totales,
                            $this->request->cuotas,
                            $persona[0]['tipo'] . '-' . $persona[0]['documento']
                        );

                        if ($estado != 1) {
                            $this->db->trans_rollback();

                            $data = [];
                            $data['estado'] = 0;
                            $data['informacion'] = 'No se pudo registrar el crédito.';
                            echo json_encode($data);
                            return;
                        }
                    }

                    /* COMPROBANTE ELECTRONICO PARA SUNAT */
                    if ($this->request->campos->codcomprobantetipo == 10 || $this->request->campos->codcomprobantetipo == 12) {
                        $kardex = $this->db->query('select nrocomprobante from kardex.kardex where codkardex=' . (int)$codkardex)->result_array();

                        if ($this->request->campos->codcomprobantetipo == 10) {
                            $xml = $_SESSION['phuyu_ruc'] . '-01-' . $this->request->campos->seriecomprobante . '-' . $kardex[0]['nrocomprobante'];
                        } else {
                            $xml = $_SESSION['phuyu_ruc'] . '-03-' . $this->request->campos->seriecomprobante . '-' . $kardex[0]['nrocomprobante'];
                        }

                        $campos = ['codkardex', 'codsucursal', 'codusuario', 'fechacreado', 'nombre_xml'];
                        $valores = [
                            (int)$codkardex,
                            (int)$_SESSION['phuyu_codsucursal'],
                            (int)$_SESSION['phuyu_codusuario'],
                            $this->request->campos->fechacomprobante,
                            $xml
                        ];

                        $estadoSunat = $this->phuyu_model->phuyu_guardar('sunat.kardexsunat', $campos, $valores);

                        if (!$estadoSunat) {
                            $this->db->trans_rollback();

                            $data = [];
                            $data['estado'] = 0;
                            $data['informacion'] = 'No se pudo registrar el comprobante para SUNAT.';
                            echo json_encode($data);
                            return;
                        }
                    }

                    if ($this->db->trans_status() === false) {
                        $this->db->trans_rollback();

                        $data = [];
                        $data['estado'] = 0;
                        $data['informacion'] = 'Ocurrió un problema al guardar la venta.';
                        echo json_encode($data);
                        return;
                    }

                    $this->db->trans_commit();

                    $data = [];
                    $data['estado'] = 1;
                    $data['codkardex'] = $codkardex;

                    if ($this->request->campos->condicionpago == 2 && $this->request->campos->inicial > 0) {
                        $data['info_credito'] = $this->db->query("
                        SELECT *
                        FROM kardex.creditos 
                        WHERE codkardex = " . (int)$codkardex . " 
                        AND estado <> 0 
                        ORDER BY codcredito DESC 
                        LIMIT 1
                    ")->row_array();
                    }

                    echo json_encode($data);
                    return;
                } catch (Throwable $e) {
                    $this->db->trans_rollback();

                    $data = [];
                    $data['estado'] = 0;
                    $data['informacion'] = $e->getMessage();
                    echo json_encode($data);
                    return;
                }
            } else {
                echo json_encode('e');
            }
        } else {
            $this->load->view('phuyu/404');
        }
    }


    function guardar()
    {
        if ($this->input->is_ajax_request()) {
            if (isset($_SESSION['phuyu_codusuario'])) {
                $this->request = json_decode(file_get_contents('php://input'));

                // REVISAMOS SI EL PEDIDO SIGUE ACTIVO
                if ($this->request->codpedido != 0) {
                    $info = $this->db->query('select *from kardex.pedidos where codpedido=' . (int)$this->request->codpedido)->result_array();
                    if (count($info) > 0 && $info[0]['estado'] == 0) {
                        echo json_encode('e');
                        return;
                    }
                }

                // REVISAMOS SI LA PROFORMA SIGUE ACTIVA
                if ($this->request->codproforma != 0) {
                    $info = $this->db->query('select *from kardex.proformas where codproforma=' . (int)$this->request->codproforma)->result_array();
                    if (count($info) > 0 && $info[0]['estado'] == 0) {
                        echo json_encode('e');
                        return;
                    }
                }

                $this->request->campos->codpersona = $this->request->codpersonapedido == 0
                    ? $this->request->campos->codpersona
                    : $this->request->codpersonapedido;

                // VALIDAMOS SI ES BOLETA Y EL IMPORTE SEA MENOR A 700
                if ($this->request->campos->codpersona == 2 && $this->request->campos->codcomprobantetipo == 12) {
                    if ($this->request->totales->importe >= 700) {
                        echo json_encode('e');
                        return;
                    }
                }

                $this->request->campos->codlote = !isset($this->request->campos->codlote) || empty($this->request->campos->codlote)
                    ? 0
                    : $this->request->campos->codlote;

                /*
            |--------------------------------------------------------------------------
            | PREVALIDAR STOCK ANTES DE GUARDAR
            |--------------------------------------------------------------------------
            */
                $productosSinStock = [];

                if (
                    isset($_SESSION['phuyu_stockalmacen']) &&
                    (int)$_SESSION['phuyu_stockalmacen'] === 1 &&
                    isset($this->request->detalle) &&
                    is_array($this->request->detalle)
                ) {
                    foreach ($this->request->detalle as $item) {
                        $codproducto = (int)$item->codproducto;
                        $codunidad   = (int)$item->codunidad;
                        $cantidad    = (float)$item->cantidad;

                        $stockInfo = $this->db->query("
                        SELECT 
                            p.descripcion,
                            p.controlstock,
                            u.descripcion AS unidad,
                            COALESCE(pu.stockactualconvertido, 0) AS stock
                        FROM almacen.productos p
                        INNER JOIN almacen.productoubicacion pu 
                            ON p.codproducto = pu.codproducto
                        INNER JOIN almacen.unidades u
                            ON pu.codunidad = u.codunidad
                        WHERE p.codproducto = {$codproducto}
                          AND pu.codunidad = {$codunidad}
                          AND pu.codalmacen = " . (int)$_SESSION['phuyu_codalmacen'] . "
                          AND p.estado = 1
                          AND pu.estado = 1
                        LIMIT 1
                    ")->result_array();

                        if (count($stockInfo) > 0) {
                            $controlProducto = (int)$stockInfo[0]['controlstock'];
                            $stockActual     = (float)$stockInfo[0]['stock'];

                            if ($controlProducto === 1 && $cantidad > $stockActual) {
                                $productosSinStock[] = [
                                    'producto' => $stockInfo[0]['descripcion'],
                                    'stock'    => number_format($stockActual, 4, '.', ''),
                                    'unidad'   => $stockInfo[0]['unidad']
                                ];
                            }
                        }
                    }
                }

                if (count($productosSinStock) > 0) {
                    echo json_encode([
                        'estado' => 0,
                        'informacion' => [
                            'success'  => false,
                            'stock'    => array_column($productosSinStock, 'stock'),
                            'producto' => array_column($productosSinStock, 'producto'),
                            'unidad'   => array_column($productosSinStock, 'unidad')
                        ]
                    ]);
                    return;
                }

                $this->db->trans_begin();

                try {
                    /* REGISTRO KARDEX Y KARDEX DETALLE */
                    $codkardex = $this->Kardex_model->phuyu_kardex($this->request->campos, $this->request->totales, 0);
                    $codkardexalmacen = 0;
                    $retirar = $this->request->campos->retirar;
                    $estado = 1;

                    if ($retirar == 1) {
                        $codkardexalmacen = $this->Kardex_model->phuyu_kardexalmacen($codkardex, 4, $this->request->campos);
                    }

                    $detalle = $this->Kardex_model->phuyu_kardexdetalle(
                        $codkardex,
                        $codkardexalmacen,
                        $this->request->detalle,
                        $retirar,
                        0,
                        $this->request->codpedido,
                        $this->request->codproforma
                    );

                    if (!isset($detalle['success']) || !$detalle['success']) {
                        $this->db->trans_rollback();

                        $data = [];
                        $data['estado'] = 0;
                        $data['informacion'] = $detalle;
                        echo json_encode($data);
                        return;
                    }

                    if ($this->request->codpedido != 0) {
                        $this->phuyu_model->phuyu_pedidodetalle($this->request->codpedido, $this->request->detalle);

                        if ($this->request->campos->terminarpedido == true) {
                            $campos = ['estadoproceso'];
                            $valores = [1];
                            $this->phuyu_model->phuyu_editar('kardex.pedidos', $campos, $valores, 'codpedido', $this->request->codpedido);
                        }
                    }

                    if ($this->request->codproforma != 0) {
                        $this->phuyu_model->phuyu_proformadetalle($this->request->codproforma, $this->request->detalle);

                        if ($this->request->campos->terminarpedido == true) {
                            $campos = ['estadoproceso'];
                            $valores = [1];
                            $this->phuyu_model->phuyu_editar('kardex.proformas', $campos, $valores, 'codproforma', $this->request->codproforma);
                        }
                    }

                    /* REGISTRO MOVIMIENTO DE CAJA */
                    if ($this->request->campos->codmoneda != 1) {
                        $importe = round($this->request->totales->importe * $this->request->campos->tipocambio, 2);
                        $importemoneda = $this->request->totales->importe;
                    } else {
                        $importe = $this->request->totales->importe;
                        $importemoneda = $this->request->totales->importe;
                    }

                    $codmovimiento = $this->Caja_model->phuyu_movimientos(
                        $codkardex,
                        1,
                        1,
                        $importe,
                        $this->request->campos,
                        $importemoneda
                    );

                    if ($codmovimiento == 0) {
                        $this->db->trans_rollback();

                        $data = [];
                        $data['estado'] = 3;
                        $data['informacion'] = 'La venta se interrumpió porque la caja que usted está utilizando está cerrada, vuelve a iniciar sesión';
                        echo json_encode($data);
                        return;
                    }

                    if ($this->request->campos->condicionpago == 1) {
                        $estado = $this->Caja_model->phuyu_movimientosdetalle($codmovimiento, $this->request->pagos);

                        if ($estado != 1) {
                            $this->db->trans_rollback();

                            $data = [];
                            $data['estado'] = 0;
                            $data['informacion'] = 'No se pudo registrar el detalle del pago.';
                            echo json_encode($data);
                            return;
                        }
                    }

                    /* REGISTRO CREDITO POR COBRAR */
                    if ($this->request->campos->condicionpago == 2) {
                        $persona = $this->db->query(
                            'select documento,d.abreviatura as tipo ' .
                            'from public.personas p ' .
                            'inner join public.documentotipos d on(p.coddocumentotipo=d.coddocumentotipo) ' .
                            'where p.codpersona=' . (int)$this->request->campos->codpersona
                        )->result_array();

                        $estado = $this->Caja_model->phuyu_credito(
                            $codkardex,
                            $codmovimiento,
                            1,
                            $this->request->campos,
                            $this->request->totales,
                            $this->request->cuotas,
                            $persona[0]['tipo'] . '-' . $persona[0]['documento']
                        );

                        if ($estado != 1) {
                            $this->db->trans_rollback();

                            $data = [];
                            $data['estado'] = 0;
                            $data['informacion'] = 'No se pudo registrar el crédito.';
                            echo json_encode($data);
                            return;
                        }
                    }

                    /* COMPROBANTE ELECTRONICO PARA SUNAT */
                    if ($this->request->campos->codcomprobantetipo == 10 || $this->request->campos->codcomprobantetipo == 12) {
                        $kardex = $this->db->query('select nrocomprobante from kardex.kardex where codkardex=' . (int)$codkardex)->result_array();

                        if ($this->request->campos->codcomprobantetipo == 10) {
                            $xml = $_SESSION['phuyu_ruc'] . '-01-' . $this->request->campos->seriecomprobante . '-' . $kardex[0]['nrocomprobante'];
                        } else {
                            $xml = $_SESSION['phuyu_ruc'] . '-03-' . $this->request->campos->seriecomprobante . '-' . $kardex[0]['nrocomprobante'];
                        }

                        $campos = ['codkardex', 'codsucursal', 'codusuario', 'fechacreado', 'nombre_xml'];
                        $valores = [
                            (int)$codkardex,
                            (int)$_SESSION['phuyu_codsucursal'],
                            (int)$_SESSION['phuyu_codusuario'],
                            $this->request->campos->fechacomprobante,
                            $xml
                        ];

                        $estadoSunat = $this->phuyu_model->phuyu_guardar('sunat.kardexsunat', $campos, $valores);

                        if (!$estadoSunat) {
                            $this->db->trans_rollback();

                            $data = [];
                            $data['estado'] = 0;
                            $data['informacion'] = 'No se pudo registrar el comprobante para SUNAT.';
                            echo json_encode($data);
                            return;
                        }
                    }

                    if ($this->db->trans_status() === false) {
                        $this->db->trans_rollback();

                        $data = [];
                        $data['estado'] = 0;
                        $data['informacion'] = 'Ocurrió un problema al guardar la venta.';
                        echo json_encode($data);
                        return;
                    }

                    $this->db->trans_commit();

                    $data = [];
                    $data['estado'] = 1;
                    $data['codkardex'] = $codkardex;

                    if ($this->request->campos->condicionpago == 2 && $this->request->campos->inicial > 0) {
                        $data['info_credito'] = $this->db->query("
                        SELECT *
                        FROM kardex.creditos 
                        WHERE codkardex = " . (int)$codkardex . " 
                        AND estado <> 0 
                        ORDER BY codcredito DESC 
                        LIMIT 1
                    ")->row_array();
                    }

                    echo json_encode($data);
                    return;
                } catch (Throwable $e) {
                    $this->db->trans_rollback();

                    $data = [];
                    $data['estado'] = 0;
                    $data['informacion'] = $e->getMessage();
                    echo json_encode($data);
                    return;
                }
            } else {
                echo json_encode('e');
            }
        } else {
            $this->load->view('phuyu/404');
        }
    }

    function editar()
    {
        if ($this->input->is_ajax_request()) {
            if (isset($_SESSION['phuyu_codusuario'])) {
                $this->request = json_decode(file_get_contents('php://input'));
                $info = $this->db->query('select kardex.codkardex,kardex.fechacomprobante,kardex.fechakardex, kardex.seriecomprobante, kardex.nrocomprobante,kardex.nroplaca,kardex.cliente,kardex.direccion,kardex.descripcion,personas.codpersona, personas.razonsocial,comprobantes.descripcion as tipo from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.codkardex=' . $this->request->codregistro)->result_array();
                $sunat_existe = $this->db->query('select estado from sunat.kardexsunat where codkardex=' . $this->request->codregistro)->result_array();
                if (count($sunat_existe) == 0) {
                    $sunat = 0;
                } else {
                    if ($sunat_existe[0]['estado'] == 0) {
                        $sunat = 0;
                    } else {
                        $sunat = 1;
                    }
                }
                $this->load->view('ventas/ventas/editar', compact('info', 'sunat'));
            } else {
                $this->load->view('phuyu/505');
            }
        } else {
            $this->load->view('phuyu/404');
        }
    }

    function editar_guardar()
    {
        if ($this->input->is_ajax_request()) {
            $this->request = json_decode(file_get_contents('php://input'));

            $campos = ['codpersona', 'fechacomprobante', 'fechakardex', 'cliente', 'direccion', 'descripcion', 'nroplaca'];
            $valores = [$this->request->codpersona, $this->request->fechacomprobante, $this->request->fechakardex, $this->request->cliente, $this->request->direccion, $this->request->descripcion, $this->request->nroplaca];
            $estado = $this->phuyu_model->phuyu_editar('kardex.kardex', $campos, $valores, 'codkardex', $this->request->codregistro);

            $campos = ['fechakardex'];
            $valores = [$this->request->fechakardex];
            $estado_u = $this->phuyu_model->phuyu_editar('kardex.kardexalmacen', $campos, $valores, 'codkardex', $this->request->codregistro);

            $campos = ['codpersona', 'fechacredito'];
            $valores = [$this->request->codpersona, $this->request->fechacomprobante];
            $estado_u = $this->phuyu_model->phuyu_editar('kardex.creditos', $campos, $valores, 'codkardex', $this->request->codregistro);
            $campos = ['codpersona', 'fechamovimiento'];
            $valores = [$this->request->codpersona, $this->request->fechacomprobante];
            $estado_u = $this->phuyu_model->phuyu_editar('caja.movimientos', $campos, $valores, 'codkardex', $this->request->codregistro);

            echo $estado;
        }
    }

    function eliminar()
    {
        if ($this->input->is_ajax_request()) {
            $this->request = json_decode(file_get_contents('php://input'));
            $this->db->trans_begin();

            //REVISAMOS SI LA VENTA YA ESTA ELIMINADO

            // REVISAMOS SI ESTA SUJETO A UNA NOTA DE CREDITO

            $comprobante = $this->db->query('select *from kardex.kardex where codkardex_ref=' . $this->request->codregistro . ' and estado<>0 and codmovimientotipo=8')->result_array();

            if (count($comprobante) > 0) {
                $this->db->trans_rollback();
                $data['estado'] = 5;
                $data['mensaje'] = 'La venta no puede ser anulada porque está sujeta a la nota de credito ' . $comprobante[0]['seriecomprobante'] . '-' . $comprobante[0]['nrocomprobante'];
                echo json_encode($data);
                exit();
            }

            $comprobante = $this->db->query('select *from kardex.kardex where codkardex=' . $this->request->codregistro . ' and estado<>0')->result_array();

            // REVISAMOS LA FECHA DE EMISION SI ES FACTURA O BOLETA

            $dteStart = new DateTime($comprobante[0]['fechacomprobante']);
            $dteEnd = new DateTime(date('Y-m-d'));
            $dteDiff = $dteStart->diff($dteEnd);
            $diferencia = $dteDiff->days;

            if ($comprobante[0]['codcomprobantetipo'] == 10 || $comprobante[0]['codcomprobantetipo'] == 12) {
                if ((int) $diferencia > 7) {
                    $this->db->trans_rollback();
                    $data['estado'] = 2;
                    $data['mensaje'] = 'Los dias máximos de anulación de ventas sobrepasó el límite, desea de todas maneras eliminar este comprobante para control interno?';
                    echo json_encode($data);
                    exit();
                }
            }

            // SI EXISTE EN CREDITOS //
            $credito = $this->db->query('select *from kardex.creditos where codkardex=' . $this->request->codregistro . ' and estado<>0')->result_array();
            if (count($credito) > 0) {
                $this->db->trans_rollback();
                $data['estado'] = 3;
                $data['mensaje'] = 'La venta no puede ser anulado porque está sujeto a un crédito, debe anular el crédito en cuentas por cobrar en el modulo AREA CREDITOS y volver a intentar';
                echo json_encode($data);
                exit();
            }

            // ACTUALIZAMOS PRODUCTOS UBICACION //
            $kardexalmacen = $this->db->query('select codkardexalmacen from kardex.kardexalmacen where codkardex=' . $this->request->codregistro)->result_array();

            $info = $this->db->query('select *from kardex.kardexdetalle where codkardex=' . $this->request->codregistro)->result_array();
            foreach ($info as $key => $value) {
                $existe = $this->db->query('select *from almacen.productoubicacion where codalmacen=' . $_SESSION['phuyu_codalmacen'] . ' and codproducto=' . $value['codproducto'] . ' and codunidad=' . $value['codunidad'])->result_array();
                $stock = $existe[0]['stockactual'] + $value['cantidad'];

                $cantidad_recogo = $value['cantidad'] - $value['recogido'];

                $campos = ['stockactual', 'ventarecogo'];
                $valores = [(float) $stock, (float) $existe[0]['ventarecogo'] - $cantidad_recogo];
                $f = ['codalmacen', 'codproducto', 'codunidad'];
                $v = [(int) $_SESSION['phuyu_codalmacen'], (int) $value['codproducto'], (int) $value['codunidad']];
                $estado = $this->phuyu_model->phuyu_editar_1('almacen.productoubicacion', $campos, $valores, $f, $v);

                // AUMENTAMOS EL STOCKACTUALCONVERTIDO

                $stockconvertido = $this->db->query('select *from almacen.productoubicacion where codalmacen=' . $_SESSION['phuyu_codalmacen'] . ' and codproducto=' . $value['codproducto'])->result_array();

                $factor = $this->db->query('select *from almacen.productounidades where codproducto=' . $value['codproducto'] . ' and codunidad=' . $value['codunidad'])->result_array();

                foreach ($stockconvertido as $k => $val) {
                    $stockc = 0;
                    $productounidad = $this->db->query('select *from almacen.productounidades where codproducto=' . $value['codproducto'] . ' and codunidad=' . $val['codunidad'])->result_array();

                    $stockc = ((float) $value['cantidad'] * (float) $factor[0]['factor']) / (float) $productounidad[0]['factor'];
                    $stockc = $stockc + $val['stockactualconvertido'];

                    $stockrecoger = ((float) $cantidad_recogo * (float) $factor[0]['factor']) / (float) $productounidad[0]['factor'];

                    $campos = ['stockactualconvertido', 'ventarecogoconvertido'];
                    $valores = [(float) $stockc, (float) $val['ventarecogoconvertido'] - (float) $stockrecoger];
                    $f = ['codalmacen', 'codproducto', 'codunidad'];
                    $v = [(int) $_SESSION['phuyu_codalmacen'], (int) $value['codproducto'], (int) $val['codunidad']];
                    $estado = $this->phuyu_model->phuyu_editar_1('almacen.productoubicacion', $campos, $valores, $f, $v);
                }
            }
            $estado = $this->phuyu_model->phuyu_eliminar('kardex.kardex', 'codkardex', $this->request->codregistro);
            if (count($kardexalmacen) > 0) {
                $estado = $this->phuyu_model->phuyu_eliminar('kardex.kardexalmacen', 'codkardexalmacen', $kardexalmacen[0]['codkardexalmacen']);
            }

            // REGISTRO KARDEX ANULADOS //
            $campos = ['codkardex', 'codsucursal', 'codusuario', 'fechaanulacion', 'observaciones'];
            $valores = [(int) $this->request->codregistro, (int) $_SESSION['phuyu_codsucursal'], (int) $_SESSION['phuyu_codusuario'], date('Y-m-d'), $this->request->observaciones];
            $estado = $this->phuyu_model->phuyu_guardar('kardex.kardexanulados', $campos, $valores);

            if (count($kardexalmacen) > 0) {
                $info = $this->db->query('select *from kardex.kardexalmacenanulado where codkardexalmacen=' . $kardexalmacen[0]['codkardexalmacen'])->result_array();
                if (count($info) == 0) {
                    // REGISTRO KARDEX ALMACEN ANULADOS //
                    $campos = ['codkardexalmacen', 'codsucursal', 'codusuario', 'fechaanulacion', 'observaciones'];
                    $valores = [(int) $kardexalmacen[0]['codkardexalmacen'], (int) $_SESSION['phuyu_codsucursal'], (int) $_SESSION['phuyu_codusuario'], date('Y-m-d'), $this->request->observaciones];
                    $estado = $this->phuyu_model->phuyu_guardar('kardex.kardexalmacenanulado', $campos, $valores);
                }
            }
            //VERIFICAMOS Y ELIMINAMOS EN LA TABLA KARDEXPEDIDO

            $info = $this->db->query('select *from kardex.kardexpedido where codkardex=' . $this->request->codregistro)->result_array();

            if (count($info) > 0) {
                $codpedido = 0;
                foreach ($info as $key => $value) {
                    $existepedido = $this->db->query('select *from kardex.pedidosdetalle where codpedido=' . $value['codpedido'] . ' and codproducto=' . $value['codproducto'] . ' and item=' . $value['itempedido'])->result_array();

                    $existeventa = $this->db->query('select *from kardex.kardexdetalle where codkardex=' . $value['codkardex'] . ' and codproducto=' . $value['codproducto'] . ' and item=' . $value['itemkardex'])->result_array();

                    $stock = $existepedido[0]['cantidadcomprobante'] - $existeventa[0]['cantidad'];

                    $campos = ['cantidadcomprobante'];
                    $valores = [(float) $stock];
                    $f = ['codpedido', 'codproducto', 'item'];
                    $v = [(int) $value['codpedido'], (int) $value['codproducto'], (int) $value['itempedido']];
                    $estado = $this->phuyu_model->phuyu_editar_1('kardex.pedidosdetalle', $campos, $valores, $f, $v);
                    $codpedido = $value['codpedido'];
                }
                $estado = $this->phuyu_model->phuyu_eliminar_total('kardex.kardexpedido', 'codkardex', $this->request->codregistro);

                $estadoproceso = $this->db->query('select *FROM kardex.pedidosdetalle pd where pd.codpedido=' . $codpedido . ' and pd.cantidad > pd.cantidadcomprobante')->result_array();

                if (count($estadoproceso) > 0) {
                    $data = ['estadoproceso' => 0];
                    $this->db->where('codpedido', $codpedido);
                    $estado = $this->db->update('kardex.pedidos', $data);
                }
            }

            // ANULAMOS EL MOVIMIENTO DE CAJA //
            $movi = $this->db->query('select codmovimiento from caja.movimientos where codkardex=' . $this->request->codregistro)->result_array();
            $estado = $this->phuyu_model->phuyu_eliminar('caja.movimientos', 'codmovimiento', $movi[0]['codmovimiento']);

            $campos = ['estado'];
            $valores = [0];
            $f = ['codmovimiento'];
            $v = [(int) $movi[0]['codmovimiento']];
            $estado = $this->phuyu_model->phuyu_editar_1('caja.movimientosdetalle', $campos, $valores, $f, $v);

            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                $estado = 0;
            } else {
                if ($estado != 1) {
                    $this->db->trans_rollback();
                    $estado = 0;
                }
                $this->db->trans_commit();
            }
            $data['estado'] = $estado;
            $data['mensaje'] = 'VENTA ANULADA CORRECTAMENTE';
            echo json_encode($data);
        } else {
            $this->load->view('phuyu/404');
        }
    }

    function eliminarinterno()
    {
        if ($this->input->is_ajax_request()) {
            $this->request = json_decode(file_get_contents('php://input'));
            $this->db->trans_begin();

            // ACTUALIZAMOS PRODUCTOS UBICACION //
            $kardexalmacen = $this->db->query('select codkardexalmacen from kardex.kardexalmacen where codkardex=' . $this->request->codregistro)->result_array();

            $info = $this->db->query('select *from kardex.kardexdetalle where codkardex=' . $this->request->codregistro)->result_array();
            foreach ($info as $key => $value) {
                $existe = $this->db->query('select *from almacen.productoubicacion where codalmacen=' . $_SESSION['phuyu_codalmacen'] . ' and codproducto=' . $value['codproducto'] . ' and codunidad=' . $value['codunidad'])->result_array();
                $stock = $existe[0]['stockactual'] + $value['cantidad'];

                $campos = ['stockactual'];
                $valores = [(float) $stock];
                $f = ['codalmacen', 'codproducto', 'codunidad'];
                $v = [(int) $_SESSION['phuyu_codalmacen'], (int) $value['codproducto'], (int) $value['codunidad']];
                $estado = $this->phuyu_model->phuyu_editar_1('almacen.productoubicacion', $campos, $valores, $f, $v);

                // AUMENTAMOS EL STOCKACTUALCONVERTIDO

                $stockconvertido = $this->db->query('select *from almacen.productoubicacion where codalmacen=' . $_SESSION['phuyu_codalmacen'] . ' and codproducto=' . $value['codproducto'])->result_array();

                $factor = $this->db->query('select *from almacen.productounidades where codproducto=' . $value['codproducto'] . ' and codunidad=' . $value['codunidad'])->result_array();

                foreach ($stockconvertido as $k => $val) {
                    $stockc = 0;
                    $productounidad = $this->db->query('select *from almacen.productounidades where codproducto=' . $value['codproducto'] . ' and codunidad=' . $val['codunidad'])->result_array();

                    $stockc = ((float) $value['cantidad'] * (float) $factor[0]['factor']) / (float) $productounidad[0]['factor'];
                    $stockc = $stockc + $val['stockactualconvertido'];
                    $campos = ['stockactualconvertido'];
                    $valores = [(float) $stockc];
                    $f = ['codalmacen', 'codproducto', 'codunidad'];
                    $v = [(int) $_SESSION['phuyu_codalmacen'], (int) $value['codproducto'], (int) $val['codunidad']];
                    $estado = $this->phuyu_model->phuyu_editar_1('almacen.productoubicacion', $campos, $valores, $f, $v);
                }
            }
            $estado = $this->phuyu_model->phuyu_eliminar('kardex.kardex', 'codkardex', $this->request->codregistro);
            if (count($kardexalmacen) > 0) {
                $estado = $this->phuyu_model->phuyu_eliminar('kardex.kardexalmacen', 'codkardexalmacen', $kardexalmacen[0]['codkardexalmacen']);
            }

            //VERIFICAMOS Y ELIMINAMOS EN LA TABLA KARDEXPEDIDO

            $info = $this->db->query('select *from kardex.kardexpedido where codkardex=' . $this->request->codregistro)->result_array();

            if (count($info) > 0) {
                $codpedido = 0;
                foreach ($info as $key => $value) {
                    $existepedido = $this->db->query('select *from kardex.pedidosdetalle where codpedido=' . $value['codpedido'] . ' and codproducto=' . $value['codproducto'] . ' and item=' . $value['itempedido'])->result_array();

                    $existeventa = $this->db->query('select *from kardex.kardexdetalle where codkardex=' . $value['codkardex'] . ' and codproducto=' . $value['codproducto'] . ' and item=' . $value['itemkardex'])->result_array();

                    $stock = $existepedido[0]['cantidadcomprobante'] - $existeventa[0]['cantidad'];

                    $campos = ['cantidadcomprobante'];
                    $valores = [(float) $stock];
                    $f = ['codpedido', 'codproducto', 'item'];
                    $v = [(int) $value['codpedido'], (int) $value['codproducto'], (int) $value['itempedido']];
                    $estado = $this->phuyu_model->phuyu_editar_1('kardex.pedidosdetalle', $campos, $valores, $f, $v);
                    $codpedido = $value['codpedido'];
                }
                $estado = $this->phuyu_model->phuyu_eliminar_total('kardex.kardexpedido', 'codkardex', $this->request->codregistro);

                $estadoproceso = $this->db->query('select *FROM kardex.pedidosdetalle pd where pd.codpedido=' . $codpedido . ' and pd.cantidad > pd.cantidadcomprobante')->result_array();

                if (count($estadoproceso) > 0) {
                    $data = ['estadoproceso' => 0];
                    $this->db->where('codpedido', $codpedido);
                    $estado = $this->db->update('kardex.pedidos', $data);
                }
            }

            // ANULAMOS EL MOVIMIENTO DE CAJA //
            $movi = $this->db->query('select codmovimiento from caja.movimientos where codkardex=' . $this->request->codregistro)->result_array();
            if (count($movi) > 0) {
                $movi = $this->db->query('select codmovimiento from caja.movimientos where codkardex=' . $this->request->codregistro)->result_array();
                $estado = $this->phuyu_model->phuyu_eliminar('caja.movimientos', 'codmovimiento', $movi[0]['codmovimiento']);

                $campos = ['estado'];
                $valores = [0];
                $f = ['codmovimiento'];
                $v = [(int) $movi[0]['codmovimiento']];
                $estado = $this->phuyu_model->phuyu_editar_1('caja.movimientosdetalle', $campos, $valores, $f, $v);
            }

            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                $estado = 0;
            } else {
                if ($estado != 1) {
                    $this->db->trans_rollback();
                    $estado = 0;
                }
                $this->db->trans_commit();
            }
            echo $estado;
        }
    }

    function restaurar()
    {
        if ($this->input->is_ajax_request()) {
            $this->request = json_decode(file_get_contents('php://input'));
            $this->db->trans_begin();

            // ACTUALIZAMOS PRODUCTOS UBICACION //
            $kardexalmacen = $this->db->query('select codkardexalmacen from kardex.kardexalmacen where codkardex=' . $this->request->codregistro)->result_array();

            $info = $this->db->query('select *from kardex.kardexdetalle where codkardex=' . $this->request->codregistro)->result_array();
            foreach ($info as $key => $value) {
                $existe = $this->db->query('select *from almacen.productoubicacion where codalmacen=' . $_SESSION['phuyu_codalmacen'] . ' and codproducto=' . $value['codproducto'] . ' and codunidad=' . $value['codunidad'])->result_array();
                $stock = $existe[0]['stockactual'] - $value['cantidad'];

                $campos = ['stockactual'];
                $valores = [(float) $stock];
                $f = ['codalmacen', 'codproducto', 'codunidad'];
                $v = [(int) $_SESSION['phuyu_codalmacen'], (int) $value['codproducto'], (int) $value['codunidad']];
                $estado = $this->phuyu_model->phuyu_editar_1('almacen.productoubicacion', $campos, $valores, $f, $v);

                // DISMINUIMOS EL STOCKACTUALCONVERTIDO

                $stockconvertido = $this->db->query('select *from almacen.productoubicacion where codalmacen=' . $_SESSION['phuyu_codalmacen'] . ' and codproducto=' . $value['codproducto'])->result_array();

                $factor = $this->db->query('select *from almacen.productounidades where codproducto=' . $value['codproducto'] . ' and codunidad=' . $value['codunidad'])->result_array();

                foreach ($stockconvertido as $k => $val) {
                    $stockc = 0;
                    $productounidad = $this->db->query('select *from almacen.productounidades where codproducto=' . $value['codproducto'] . ' and codunidad=' . $val['codunidad'])->result_array();

                    $stockc = ((float) $value['cantidad'] * (float) $factor[0]['factor']) / (float) $productounidad[0]['factor'];
                    $stockc = $val['stockactualconvertido'] - $stockc;
                    $campos = ['stockactualconvertido'];
                    $valores = [(float) $stockc];
                    $f = ['codalmacen', 'codproducto', 'codunidad'];
                    $v = [(int) $_SESSION['phuyu_codalmacen'], (int) $value['codproducto'], (int) $val['codunidad']];
                    $estado = $this->phuyu_model->phuyu_editar_1('almacen.productoubicacion', $campos, $valores, $f, $v);
                }
            }
            $estado = $this->phuyu_model->phuyu_restaurar('kardex.kardex', 'codkardex', $this->request->codregistro);
            $estado = $this->phuyu_model->phuyu_restaurar('kardex.kardexalmacen', 'codkardexalmacen', $kardexalmacen[0]['codkardexalmacen']);

            $estado = $this->phuyu_model->phuyu_eliminar_total('kardex.kardexanulados', 'codkardex', $this->request->codregistro);
            $estado = $this->phuyu_model->phuyu_eliminar_total('kardex.kardexalmacenanulado', 'codkardexalmacen', $kardexalmacen[0]['codkardexalmacen']);

            //VERIFICAMOS Y ELIMINAMOS EN LA TABLA KARDEXPEDIDO

            /*$info = $this->db->query("select *from kardex.kardexpedido where codkardex=".$this->request->codregistro)->result_array();

            if(count($info) > 0){
                $codpedido = 0;
                foreach ($info as $key => $value) {

                $existepedido = $this->db->query("select *from kardex.pedidosdetalle where codpedido=".$value["codpedido"]." and codproducto=".$value["codproducto"]." and item=".$value["itempedido"])->result_array();

                $existeventa = $this->db->query("select *from kardex.kardexdetalle where codkardex=".$value["codkardex"]." and codproducto=".$value["codproducto"]." and item=".$value["itemkardex"])->result_array();

                $stock = $existepedido[0]["cantidadcomprobante"] + $existeventa[0]["cantidad"];

                $campos = ["cantidadcomprobante"]; $valores = [(double)$stock];
                $f = ["codpedido","codproducto","item"];
                $v = [(int)$value["codpedido"],(int)$value["codproducto"],(int)$value["itempedido"]];
                $estado = $this->phuyu_model->phuyu_editar_1("kardex.pedidosdetalle", $campos, $valores, $f, $v);
                $codpedido = $value["codpedido"];
                }
                $estado = $this->phuyu_model->phuyu_eliminar_total("kardex.kardexpedido", "codkardex",$this->request->codregistro);

                $estadoproceso = $this->db->query("select *FROM kardex.pedidosdetalle pd where pd.codpedido=".$codpedido." and pd.cantidad > pd.cantidadcomprobante")->result_array();

                if(count($estadoproceso) > 0){
                $data = array("estadoproceso" => 0);
                $this->db->where("codpedido", $codpedido);
            $estado = $this->db->update("kardex.pedidos", $data);
                }
            }*/

            // ANULAMOS EL MOVIMIENTO DE CAJA //
            $movi = $this->db->query('select codmovimiento from caja.movimientos where codkardex=' . $this->request->codregistro)->result_array();
            if (count($movi) > 0) {
                $estado = $this->phuyu_model->phuyu_restaurar('caja.movimientos', 'codmovimiento', $movi[0]['codmovimiento']);

                $campos = ['estado'];
                $valores = [1];
                $f = ['codmovimiento'];
                $v = [(int) $movi[0]['codmovimiento']];
                $estado = $this->phuyu_model->phuyu_editar_1('caja.movimientosdetalle', $campos, $valores, $f, $v);
            }

            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                $estado = 0;
            } else {
                if ($estado != 1) {
                    $this->db->trans_rollback();
                    $estado = 0;
                }
                $this->db->trans_commit();
            }
            echo $estado;
        } else {
            $this->load->view('phuyu/404');
        }
    }

    function enviar_whatsapp()
    {
        if (!$this->input->is_ajax_request()) {
            $this->load->view('phuyu/404');
            return;
        }

        if (!isset($_SESSION['phuyu_codusuario'])) {
            $this->phuyu_json(['estado' => 0, 'mensaje' => 'La sesión expiró. Vuelve a iniciar sesión.']);
            return;
        }

        $this->request = json_decode(file_get_contents('php://input'));
        $codkardex = isset($this->request->codkardex) ? (int)$this->request->codkardex : 0;
        $telefono = isset($this->request->telefono) ? $this->phuyu_whatsapp_telefono($this->request->telefono) : '';
        $formato = isset($this->request->formato) ? $this->phuyu_whatsapp_formato($this->request->formato) : 'a4';

        $tipoEnvio = isset($this->request->tipo_envio) ? strtolower(trim((string)$this->request->tipo_envio)) : 'pdf';
        $tipoEnvio = in_array($tipoEnvio, ['pdf', 'link'], true) ? $tipoEnvio : 'pdf';


        if ($codkardex <= 0) {
            $this->phuyu_json(['estado' => 0, 'mensaje' => 'Debe seleccionar un comprobante válido.']);
            return;
        }

        if ($telefono === '') {
            $this->phuyu_json(['estado' => 0, 'mensaje' => 'Ingrese el teléfono con código de país. Ejemplo: +51999999999.']);
            return;
        }

        $venta = $this->db->query(
            "select k.codkardex,k.estado,k.seriecomprobante,k.nrocomprobante,k.cliente,ct.descripcion as comprobante,ct.oficial
            from kardex.kardex as k
            inner join caja.comprobantetipos as ct on(k.codcomprobantetipo=ct.codcomprobantetipo)
            where k.codkardex=? and k.codmovimientotipo=20 and k.codsucursal=? limit 1",
            [$codkardex, (int)$_SESSION['phuyu_codsucursal']]
        )->row_array();

        if (empty($venta)) {
            $this->phuyu_json(['estado' => 0, 'mensaje' => 'No se encontró el comprobante seleccionado.']);
            return;
        }

        if ((int)$venta['estado'] === 0) {
            $this->phuyu_json(['estado' => 0, 'mensaje' => 'No se puede enviar una venta anulada por WhatsApp.']);
            return;
        }

        $ruc = isset($_SESSION['phuyu_ruc']) ? $_SESSION['phuyu_ruc'] : 'comprobante';
        $filename = $ruc . '-' . $venta['oficial'] . '-' . $venta['seriecomprobante'] . '-' . $venta['nrocomprobante'] . '.pdf';
        $mensajeAdjunto = 'Hola, ' . $venta['cliente'] . '. Adjuntamos su comprobante ' .
            $venta['comprobante'] . ' ' . $venta['seriecomprobante'] . '-' . $venta['nrocomprobante'] . '.';

        $mediaUrl = $this->phuyu_whatsapp_url($codkardex, $formato);
        if ($tipoEnvio === 'link') {
            $mensajeLink = $mensajeAdjunto . "\n\nPuede descargarlo aquí: " . $mediaUrl;
            $waUrl = 'https://wa.me/' . ltrim($telefono, '+') . '?text=' . rawurlencode($mensajeLink);

            $this->phuyu_json([
                'estado' => 2,
                'mensaje' => 'Se abrirá WhatsApp con el link del comprobante listo para enviar.',
                'url' => $waUrl,
            ]);
            return;
        }

        $pdf = $this->phuyu_whatsapp_generar_pdf($codkardex, $formato, $filename);
        if ($pdf['estado'] != 1) {
            $this->phuyu_json($pdf);
            return;
        }

        $respuesta = $this->phuyu_whatsapp_enviar_cloud($telefono, $mensajeAdjunto, $filename, $pdf['path']);
        if (file_exists($pdf['path'])) {
            @unlink($pdf['path']);
        }

        $this->phuyu_json($respuesta);
    }

    function clonar()
    {
        if ($this->input->is_ajax_request()) {
            if (isset($_SESSION['phuyu_codusuario'])) {
                $this->request = json_decode(file_get_contents('php://input'));
                $info = $this->db->query('select kardex.codkardex,kardex.fechacomprobante,kardex.fechakardex,kardex.codmoneda, kardex.tipocambio,kardex.codcomprobantetipo,kardex.retirar,kardex.afectacaja,kardex.seriecomprobante, kardex.nrocomprobante,kardex.nroplaca,kardex.cliente,kardex.direccion,kardex.descripcion,personas.codpersona, personas.razonsocial,comprobantes.descripcion as tipo,kardex.flete,kardex.gastos,kardex.valorventa,kardex.descglobal,kardex.igv,kardex.importe,kardex.condicionpago from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.codkardex=' . $this->request->codregistro)->result_array();

                $info[0]['nrodias'] = 30;
                $info[0]['nrocuotas'] = 1;
                $info[0]['tasainteres'] = 0;
                $cuotas = [];
                if ($info[0]['condicionpago'] == 2) {
                    $credito = $this->db->query('select *from kardex.creditos where codkardex=' . $info[0]['codkardex'])->result_array();

                    if (count($credito) > 0) {
                        $info[0]['nrodias'] = $credito[0]['nrodias'];
                        $info[0]['nrocuotas'] = $credito[0]['nrocuotas'];
                        $info[0]['tasainteres'] = $credito[0]['tasainteres'];
                    }

                    $cuotas = $this->db->query('select *from kardex.cuotas where codcredito = ' . $credito[0]['codcredito'])->result_array();
                }

                $socio = $this->db->query('Select *from personas WHERE codpersona=' . $info[0]['codpersona'])->result_array();
                $detalle = $this->db->query('select kd.*,p.descripcion as producto,u.descripcion as unidad from kardex.kardexdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codkardex=' . $this->request->codregistro . ' and kd.estado=1 order by kd.item')->result_array();

                foreach ($detalle as $key => $value) {
                    $unn = $this->db
                        ->query(
                            "SELECT pun.unidades
           FROM almacen.v_productounidades pun
           WHERE pun.codproducto = " .
                                $value['codproducto'] .
                                ' and pun.codalmacen=' .
                                $_SESSION['phuyu_codalmacen'] .
                                '  ',
                        )
                        ->result_array();

                    $unidades = [];
                    $factores = [];
                    $logo = [];
                    $arreglo = [];
                    $putunidades = [];
                    $unidades = explode(';', $unn[0]['unidades']);
                    foreach ($unidades as $k => $v) {
                        $factores = explode('|', $unidades[$k]);
                        $logo = ['descripcion' => $factores[1], 'codunidad' => $factores[0], 'factor' => $factores[8]];
                        if ($factores[0] == $value['codunidad']) {
                            $detalle[$key]['stock'] = $factores[3];
                        }
                        array_push($putunidades, $logo);
                    }

                    $detalle[$key]['unidades'] = $putunidades;
                    $detalle[$key]['precio'] = round($detalle[$key]['preciounitario'], 2);
                    $detalle[$key]['cantidad'] = round($detalle[$key]['cantidad'], 2);
                    $detalle[$key]['control'] = 0;
                }

                echo json_encode(['campos' => $info, 'socio' => $socio, 'detalle' => $detalle, 'cuotas' => $cuotas]);
            } else {
                $this->load->view('phuyu/505');
            }
        } else {
            $this->load->view('phuyu/404');
        }
    }

    function formato($formato)
    {
        if ($this->input->is_ajax_request()) {
            $campos = ['formato'];
            $valores = [$formato];
            $f = ['codsucursal'];
            $v = [$_SESSION['phuyu_codsucursal']];
            $estado = $this->phuyu_model->phuyu_editar_1('caja.comprobantes', $campos, $valores, $f, $v);

            echo $formato;
        }
    }
}
