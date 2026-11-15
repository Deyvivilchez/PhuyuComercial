<?php
require_once __DIR__ . "/../../../helpers/descripcion_item_helper.php";
if (!function_exists('texto_doc')) {
    function texto_doc($texto){
        return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('numero_doc')) {
    function numero_doc($monto){
        return number_format((float)$monto, 2);
    }
}

if (!function_exists('fecha_doc')) {
    function fecha_doc($fecha){
        if (empty($fecha) || $fecha === '0000-00-00') return '-';
        $ts = strtotime($fecha);
        return $ts ? date('d/m/Y', $ts) : $fecha;
    }
}

$serieNumero      = ($venta['seriecomprobante'] ?? '') . ' - ' . ($venta['nrocomprobante'] ?? '');
$fechaEmision     = fecha_doc($venta['fechacomprobante'] ?? '');
$fechaVence       = fecha_doc($fechavencimiento ?? '');
$cliente          = $venta['cliente'] ?? '-';
$documentoCliente = $venta['documento'] ?? '-';
$direccionCliente = $venta['direccion'] ?? '-';
$vendedor         = $empleado['razonsocial'] ?? '-';
$mesaRestaurante  = trim((string)($mesa_restaurante ?? ''));
$condicionPago    = ((int)($venta['condicionpago'] ?? 0) === 1)
    ? 'CONTADO'
    : 'CRÉDITO' . (!empty($credito['nrodias']) ? ' · ' . $credito['nrodias'] . ' días' : '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title><?= texto_doc($venta['comprobante'] ?? 'Comprobante') ?></title>
<style>
@page {
    size: 148mm 210mm; margin: 7mm 7mm 16mm 7mm;
}

body {
    margin: 0;
    padding: 0;
    font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
    font-size: 8px;
    color: #241f31;
}

.watermark {
    position: fixed;
    top: 48mm;
    left: 6mm;
    width: 120mm;
    opacity: 0.17;
    z-index: 0;
    text-align: center;

}

.watermark img {
    width: 100%;
    height: auto;
}

.content {
    position: relative;
    z-index: 2;
    padding-bottom: 0;
}

table {
    width: 100%;
    border-collapse: collapse;
}

.text-right { text-align: right; }
.text-center { text-align: center; }

.title-ribbon {
    background: #581c87;
    color: #fff;
    padding: 10px 12px;
    border-radius: 0 16px 0 16px;
    margin-bottom: 8px;
}

.doc-name {
    font-size: 14px;
    font-weight: bold;
    line-height: 1.05;
    text-transform: uppercase;
}

.doc-serie {
    font-size: 7.8px;
    margin-top: 3px;
    opacity: 0.95;
}

.box {
    border: 1px solid #e4d4ff;
    border-radius: 8px;
    padding: 7px 8px;
    background: #faf7ff;
}

.top-grid {
    table-layout: fixed;
    margin-bottom: 8px;
}

.top-grid td {
    vertical-align: top;
}

.top-left {
    width: 56%;
    padding-right: 6px;
}

.top-right {
    width: 44%;
}

.company-logo {
    margin-bottom: 6px;
}

.company-logo img {
    max-width: 145px;
    max-height: 70px;
    width: auto;
    height: auto;
}

.company-name {
    font-size: 10px;
    font-weight: bold;
    color: #1d1430;
    text-transform: uppercase;
    margin-bottom: 2px;
}

.company-meta {
    font-size: 7.2px;
    color: #6f6682;
    line-height: 1.45;
}

.doc-box-title {
    font-size: 8px;
    font-weight: bold;
    color: #581c87;
    border-bottom: 1px solid #eadcff;
    padding-bottom: 4px;
    margin-bottom: 5px;
    text-transform: uppercase;
}

.doc-row {
    font-size: 7.4px;
    line-height: 1.6;
    color: #4b425b;
}

.section-title {
    margin: 8px 0 5px 0;
    font-size: 8.2px;
    font-weight: bold;
    text-transform: uppercase;
    color: #581c87;
    border-bottom: 2px solid #dcc3ff;
    padding-bottom: 3px;
}

.info-box td {
    padding: 3px 3px;
    font-size: 7.5px;
    vertical-align: top;
}

.info-label {
    width: 18%;
    font-weight: bold;
    color: #7b728a;
}

.info-value {
    width: 32%;
    color: #171220;
}

.obs-top {
    border-left: 3px solid #6d28d9;
    background: #faf7ff;
    border: 1px solid #e4d4ff;
    border-left-width: 3px;
    border-radius: 6px;
    padding: 6px 7px;
    font-size: 7.2px;
    color: #5e556e;
    line-height: 1.45;
    margin-bottom: 7px;
}

.detalle-table thead th {
    background: #4c1d95;
    color: white;
    font-size: 7px;
    padding: 5px 3px;
    border: 1px solid rgba(217, 194, 255, 0.75);
    text-transform: uppercase;
}

.detalle-table tbody td {
    border: 1px solid rgba(234, 220, 255, 0.56);
    padding: 4px 3px;
    font-size: 7px;
    background: rgba(255, 255, 255, 0.36);
    color: #241f31;
}

.detalle-table tbody tr:nth-child(even) td {
    background: rgba(252, 249, 255, 0.46);
}

.bottom-grid {
    table-layout: fixed;
    margin-top: 7px;
}

.bottom-grid td {
    vertical-align: top;
}

.bottom-left {
    width: 57%;
    padding-right: 6px;
}

.bottom-right {
    width: 43%;
}

.son-label {
    font-size: 7.5px;
    font-weight: bold;
    color: #7b728a;
    text-transform: uppercase;
    margin-bottom: 5px;
}

.son-value {
    font-size: 10px;
    font-weight: bold;
    color: #171220;
    line-height: 1.35;
}

.extra-data {
    margin-top: 6px;
    font-size: 7px;
    color: #5e556e;
    line-height: 1.45;
}

.qr-image {
    margin-top: 8px;
    text-align: center;
}

.qr-image img {
    max-width: 72px;
    max-height: 72px;
}

.proforma-note {
    margin-top: 8px;
    border: 1px solid #e4d4ff;
    border-left: 3px solid #6d28d9;
    border-radius: 8px;
    background: #faf7ff;
    padding: 7px 8px;
}

.proforma-note-title {
    font-size: 7.3px;
    font-weight: bold;
    color: #581c87;
    text-transform: uppercase;
    margin-bottom: 4px;
}

.proforma-note-text {
    font-size: 6.8px;
    color: #5e556e;
    line-height: 1.4;
}

.totales-box {
    border: 1px solid #e4d4ff;
    border-radius: 8px;
    overflow: hidden;
    background: #fff;
}

.totales-box td {
    padding: 5px 6px;
    border-bottom: 1px solid #efe5ff;
    font-size: 7.3px;
}

.totales-box tr:last-child td {
    border-bottom: none;
}

.totales-label {
    font-weight: bold;
    color: #7b728a;
}

.totales-value {
    font-weight: bold;
    color: #171220;
    text-align: right;
}

.total-final td {
    background: #f5edff;
}

.total-final .totales-label,
.total-final .totales-value {
    color: #581c87;
    font-size: 7.8px;
}

.footer-info,
.obs-bottom {
    border: 1px solid #e4d4ff;
    border-radius: 8px;
    background: #ffffff;
    padding: 7px 8px;
    font-size: 7px;
    color: #5e556e;
    line-height: 1.45;
    margin-top: 8px;
}

.obs-bottom {
    border-left: 3px solid #6d28d9;
    background: #faf7ff;
}

.footer-fixed {
    position: fixed;
    bottom: -11mm;
    height: 7mm;
    left: 0mm;
    right: 0mm;
    background: transparent;
    color: #654379;
    padding: 4px 0 0;
    border-top: 1px solid #d9c6e9;
    z-index: 3;
}

.footer-fixed table td {
    border: none;
    padding: 0;
    vertical-align: middle;
}

.footer-fixed-left {
    width: 60%;
    font-size: 7.5px;
    line-height: 1.35;
    font-weight: bold;
}

.footer-fixed-right {
    width: 40%;
    text-align: right;
    font-size: 7px;
    line-height: 1.35;
    font-weight: bold;
}

.footer-fixed-small {
    font-size: 6.5px;
    opacity: 0.92;
    font-weight: normal;
}
/* A5 vertical, misma identidad que la nota de credito. */
.invoice-kicker { font-size:6px; color:#80668e; letter-spacing:1px; border-top:3px solid #581c87; padding-top:5px; }
.invoice-header { margin-top:9px; margin-bottom:9px; table-layout:fixed; }
.invoice-logo { max-width:85px; max-height:60px; }
.invoice-company { font-size:10px; line-height:1.4; font-weight:bold; color:#251330; }
.invoice-company-meta { font-size:8px; color:#64586d; line-height:1.6; margin-top:4px; }
.invoice-slogan { font-size:7px; color:#766280; margin-top:4px; }
.invoice-type { color:#581c87; font-size:15px; font-weight:bold; line-height:1.2; }
.invoice-number { font-size:12px; font-weight:bold; margin-top:9px; color:#271737; }
.invoice-meta { background:#f6f2fa; border-top:1px solid #e8def0; border-bottom:1px solid #e8def0; }
.invoice-meta td { width:33.33%; padding:6px 7px; font-size:9px; line-height:1.5; }
.invoice-meta span, .invoice-caption { font-size:6.5px; color:#806c92; letter-spacing:0.5px; }
.invoice-client { border-top:1px solid #e9e1f0; border-bottom:1px solid #e9e1f0; }
.invoice-client td { padding:6px 0; font-size:9px; line-height:1.4; vertical-align:top; }
.invoice-caption { margin-bottom:3px; }
.section-title { font-size:8px; margin-top:10px; margin-bottom:5px; }
.detalle-table thead th { font-size:6.5pt; padding:5px 3px; }
.detalle-table tbody td { font-size:7pt; padding:5px 3px; line-height:1.3; }
.bottom-left { width:44%; padding-right:8px; }
.bottom-right { width:56%; }
.bottom-left .box { border:0; padding:4px 0; background:transparent; }
.son-value { font-size:9px; line-height:1.5; }
.qr-image img { max-width:76px; max-height:76px; }
.totales-box { border:0; background:transparent; }
.totales-box td { font-size:8px; padding:4px 6px; }
.total-final td { background:#581c87; padding-top:7px; padding-bottom:7px; }
.total-final .totales-label { color:#fff; font-size:10px; }
.total-final .totales-value { color:#fff; font-size:13px; }
<?php if (!empty($es_pedido)): ?>
/* Mantener la identidad aprobada, con filas compactas y texto sin negrita excesiva. */
body { color:#43384e; }
.invoice-company { font-size:10px; color:#3f2b50; }
.invoice-type { font-size:15px; letter-spacing:0; }
.invoice-meta b, .invoice-client b { font-weight:normal; }
.invoice-header { margin-top:7px; margin-bottom:6px; }
.invoice-meta td { padding:4px 6px; }
.invoice-client td { padding:4px 0; }
.section-title { margin-top:8px; margin-bottom:4px; }
.detalle-table thead th { background:#581c87; color:#fff; font-size:6.5pt; font-weight:normal; padding:5px 3px; }
.detalle-table tbody td { font-size:7pt; color:#43384e; line-height:1.25; padding:4px 3px; }
.detalle-table tbody td strong { font-weight:normal; }
.pedido-observaciones { margin:6px 0; padding:5px 7px; border-left:2px solid #8752ae; background:#faf7ff; color:#6d5c78; font-size:7px; line-height:1.4; }
.totales-label, .totales-value { font-weight:normal; }
.son-value { font-weight:normal; font-size:8px; line-height:1.45; }
.bottom-grid { margin-top:6px; }
.totales-box td { padding:3px 6px; }
.watermark { opacity:0.12; }
.total-final td { background:#581c87; padding-top:6px; padding-bottom:6px; }
.total-final .totales-label { color:#fff; font-weight:normal; font-size:10px; }
.total-final .totales-value { color:#fff; font-weight:bold; font-size:12px; }
<?php endif; ?>
</style>
</head>
<body>

<?php if (!empty($logo_src)): ?>
<div class="watermark">
    <img src="<?= $logo_src ?>" alt="Marca de agua">
</div>
<?php endif; ?>

<div class="footer-fixed">
    <table>
        <tr>
            <td class="footer-fixed-left">
                <strong>PhuyuSystem</strong> ☁️<br>
                <span class="footer-fixed-small">Gracias por ser parte de la nube que nos impulsa</span>
            </td>
            <td class="footer-fixed-right">
                <span class="footer-fixed-small">Generado digitalmente</span><br>
                <strong>phuyusystem.com</strong>
            </td>
        </tr>
    </table>
</div>

<div class="content">

    <div class="invoice-kicker"><?= !empty($es_pedido) ? 'PEDIDO COMERCIAL' : 'DOCUMENTO ELECTRÓNICO' ?> · <?= texto_doc($fechaEmision) ?></div>
    <table class="invoice-header"><tr>
        <td width="60%" style="padding-right:10px;vertical-align:top">
            <table><tr>
                <?php if (!empty($logo_src)): ?><td width="32%" style="padding-right:8px"><img class="invoice-logo" src="<?= $logo_src ?>" alt="Logo"></td><?php endif; ?>
                <td style="vertical-align:middle">
                    <div class="invoice-company"><?= texto_doc($nombre_empresa ?? '') ?></div>
                    <div class="invoice-company-meta">RUC <?= texto_doc($empresa['documento'] ?? '-') ?><br><?= texto_doc($sucursal['direccion'] ?? '-') ?><br><?= texto_doc($sucursal['telefonos'] ?? '') ?></div>
                </td>
            </tr></table>
            <?php if (!empty($slogan)): ?><div class="invoice-slogan"><?= texto_doc($slogan) ?></div><?php endif; ?>
            <?php if (!empty($publicidad)): ?><div class="invoice-slogan"><?= texto_doc($publicidad) ?></div><?php endif; ?>
        </td>
        <td width="40%" style="text-align:right;vertical-align:top">
            <div class="invoice-type"><?= texto_doc($venta['comprobante'] ?? 'COMPROBANTE') ?></div>
            <div class="invoice-number"><?= texto_doc($serieNumero) ?></div>
            <div class="invoice-company-meta">Emisión <?= texto_doc($fechaEmision) ?></div>
        </td>
    </tr></table>
    <table class="invoice-meta"><tr>
        <td><span>CONDICIÓN DE PAGO</span><br><b><?= texto_doc($condicionPago) ?></b></td>
        <td><span>MONEDA</span><br><b>Soles · PEN</b></td>
        <td><span>VENCIMIENTO</span><br><b><?= texto_doc($fechaVence) ?></b></td>
    </tr></table>
    <div class="section-title">01 · Datos del cliente</div>
    <table class="invoice-client"><tr>
        <td width="72%"><div class="invoice-caption">RAZÓN SOCIAL / NOMBRE</div><b><?= texto_doc($cliente) ?></b></td>
        <td width="28%"><div class="invoice-caption">DNI / RUC</div><b><?= texto_doc($documentoCliente) ?></b></td>
    </tr><tr><td colspan="2"><div class="invoice-caption">DIRECCIÓN</div><?= texto_doc($direccionCliente) ?></td></tr></table>
    <?php if (!empty($venta['nroplaca']) || $mesaRestaurante !== ''): ?>
    <div class="extra-data"><?php if (!empty($venta['nroplaca'])): ?>Placa: <?= texto_doc($venta['nroplaca']) ?><?php endif; ?>
    <?php if ($mesaRestaurante !== ''): ?> · Mesa: <?= texto_doc($mesaRestaurante) ?><?php endif; ?></div>
    <?php endif; ?>

    <?php if (!empty($es_pedido) && trim((string)($venta['descripcion'] ?? '')) !== ''): ?>
    <div class="pedido-observaciones"><b>Observaciones:</b><br><?= nl2br(texto_doc($venta['descripcion'])) ?></div>
    <?php endif; ?>
    <div class="section-title">02 · <?= !empty($es_pedido) ? 'Detalle del pedido' : 'Detalle del comprobante' ?></div>
    <table class="detalle-table">
        <thead>
            <tr>
                <th width="7%">Item</th>
                <th width="40%">Descripción</th>
                <th width="15%">Und.</th>
                <th width="12%">Cant.</th>
                <th width="13%">P. Unit.</th>
                <th width="13%">Importe</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($detalle)): ?>
                <?php foreach ($detalle as $i => $item): ?>
                    <tr>
                        <td class="text-center"><?= str_pad((string)($item['item'] ?? ($i + 1)), 2, '0', STR_PAD_LEFT) ?></td>
                        <?php $bloquesDescripcion = phuyu_descripcion_bloques($mesaRestaurante === '' ? ($item['descripcion'] ?? '') : ''); ?>
                        <td style="word-wrap:break-word"><strong><?= texto_doc($item['producto'] ?? '') ?></strong><?php if (!empty($bloquesDescripcion)): ?><br><?= nl2br(texto_doc(array_shift($bloquesDescripcion))) ?><?php endif; ?></td>
                        <td><?= texto_doc($item['unidad'] ?? '-') ?></td>
                        <td class="text-right"><?= numero_doc($item['cantidad'] ?? 0) ?></td>
                        <td class="text-right"><?= numero_doc($item['preciounitario'] ?? 0) ?></td>
                        <td class="text-right"><?= numero_doc($item['subtotal'] ?? 0) ?></td>
                    </tr>
                    <?php foreach ($bloquesDescripcion as $continuacion): ?>
                    <tr class="descripcion-continuacion">
                        <td></td><td colspan="5" style="text-align:left;word-wrap:break-word;line-height:1.5"><?= nl2br(texto_doc($continuacion)) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">No hay detalles para mostrar.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <table class="bottom-grid">
        <tr>
            <td class="bottom-left">
                <div class="box">
                    <div class="son-label">Importe en letras</div>
                    <div class="son-value"><?= texto_doc($importe_en_letras ?? ('SON: ' . strtoupper($total_texto) . ' Y ' . explode('.', number_format((float)($venta['importe'] ?? 0), 2, '.', ''))[1] . '/100 SOLES')) ?></div>

                    <?php if ($vendedor !== '-'): ?>
                    <div class="extra-data">
                        <strong>Vendedor:</strong> <?= texto_doc($vendedor) ?>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($qr_src)): ?>
                        <div class="qr-image">
                            <img src="<?= $qr_src ?>" alt="QR">
                        </div>
                        <?php if (!empty($parametros['urlconsultacomprobantes'])): ?>
                        <div style="font-size:6px;line-height:1.4;text-align:center;word-wrap:break-word;margin-top:4px"><?= texto_doc($parametros['urlconsultacomprobantes']) ?></div>
                        <?php endif; ?>
                    <?php elseif (!empty($es_proforma)): ?>
                        <div class="proforma-note">
                            <div class="proforma-note-title">Proforma valida para cotizacion</div>
                            <div class="proforma-note-text">
                                Documento informativo no valido como comprobante de pago.<br>
                                Precios sujetos a disponibilidad y confirmacion de venta.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </td>

            <td class="bottom-right">
                <table class="totales-box">
                    <?php if (!empty($es_pedido)): ?>
                    <tr><td class="totales-label">Subtotal</td><td class="totales-value">S/ <?= numero_doc($venta['valorventa'] ?? 0) ?></td></tr>
                    <?php else: ?>
                    <tr>
                        <td class="totales-label">Op. gravadas</td>
                        <td class="totales-value">S/ <?= numero_doc($totales['gravado'] ?? 0) ?></td>
                    </tr>
                    <tr>
                        <td class="totales-label">Op. inafectas</td>
                        <td class="totales-value">S/ <?= numero_doc($totales['inafecto'] ?? 0) ?></td>
                    </tr>
                    <tr>
                        <td class="totales-label">Op. exoneradas</td>
                        <td class="totales-value">S/ <?= numero_doc($totales['exonerado'] ?? 0) ?></td>
                    </tr>
                    <tr>
                        <td class="totales-label">Op. gratuitas</td>
                        <td class="totales-value">S/ <?= numero_doc($totales['gratuito'] ?? 0) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="totales-label">Descuento</td>
                        <td class="totales-value">S/ <?= numero_doc($venta['descglobal'] ?? 0) ?></td>
                    </tr>
                    <tr>
                        <td class="totales-label">IGV</td>
                        <td class="totales-value">S/ <?= numero_doc($venta['igv'] ?? 0) ?></td>
                    </tr>
                    <tr class="total-final">
                        <td class="totales-label">Total</td>
                        <td class="totales-value">S/ <?= numero_doc($venta['importe'] ?? 0) ?></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <?php if (!empty($es_pedido)): ?>
    <div class="extra-data" style="margin-top:8px;text-align:center">Documento comercial de pedido. No constituye comprobante de pago.</div>
    <?php endif; ?>

    <?php if (!empty($cuentascorrientes)): ?>
        <div class="footer-info">
            <strong>Cuentas corrientes</strong><br>
            <?php foreach ($cuentascorrientes as $cta): ?>
                <strong><?= texto_doc($cta['banco'] ?? '-') ?></strong> ·
                CC: <?= texto_doc($cta['nroctacte'] ?? '-') ?> ·
                CCI: <?= texto_doc($cta['descripcion'] ?? '-') ?><br>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>


</div>
</body>
</html>
