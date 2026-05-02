<?php
function texto_doc($texto){
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}

function numero_doc($monto){
    return number_format((float)$monto, 2);
}

function fecha_doc($fecha){
    if (empty($fecha) || $fecha === '0000-00-00') return '-';
    $ts = strtotime($fecha);
    return $ts ? date('d/m/Y', $ts) : $fecha;
}

$serieNumero      = ($venta['seriecomprobante'] ?? '') . ' - ' . ($venta['nrocomprobante'] ?? '');
$fechaEmision     = fecha_doc($venta['fechacomprobante'] ?? '');
$fechaVence       = fecha_doc($fechavencimiento ?? '');
$cliente          = $venta['cliente'] ?? '-';
$documentoCliente = $venta['documento'] ?? '-';
$direccionCliente = $venta['direccion'] ?? '-';
$vendedor         = $empleado['razonsocial'] ?? '-';
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
    margin: 8mm 8mm 14mm 8mm;
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
    top: 50%;
    left: 50%;
    width: 120mm;
    opacity: 0.14;
    z-index: 0;
    text-align: center;
    transform: translate(-50%, -50%);
}

.watermark img {
    width: 100%;
    height: auto;
}

.content {
    position: relative;
    z-index: 2;
    padding-bottom: 22mm;
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
    bottom: 4mm;
    left: 0mm;
    right: 0mm;
    background: #4c1d95;
    color: #fff;
    padding: 7px 8px;
    border-radius: 8px 8px 0 0;
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

    <div class="title-ribbon">
        <div class="doc-name"><?= texto_doc($venta['comprobante'] ?? 'COMPROBANTE') ?></div>
        <div class="doc-serie"><?= texto_doc($serieNumero) ?> · <?= texto_doc($fechaEmision) ?></div>
    </div>

    <table class="top-grid">
        <tr>
            <td class="top-left">
                <div class="box">
                    <div class="company-logo">
                        <?php if (!empty($logo_src)): ?>
                            <img src="<?= $logo_src ?>" alt="Logo">
                        <?php endif; ?>
                    </div>
                    <div class="company-name"><?= texto_doc($nombre_empresa ?? '') ?></div>
                    <div class="company-meta">
                        <?php if (!empty($slogan)): ?>
                            <?= texto_doc($slogan) ?><br>
                        <?php endif; ?>
                        <?php if (!empty($publicidad)): ?>
                            <?= texto_doc($publicidad) ?><br>
                        <?php endif; ?>
                        <?= texto_doc($sucursal['direccion'] ?? '-') ?><br>
                        <?= texto_doc($sucursal['telefonos'] ?? '-') ?>
                    </div>
                </div>
            </td>
            <td class="top-right">
                <div class="box">
                    <div class="doc-box-title">Datos del comprobante</div>
                    <div class="doc-row"><strong>RUC:</strong> <?= texto_doc($empresa['documento'] ?? '-') ?></div>
                    <div class="doc-row"><strong>Serie:</strong> <?= texto_doc($serieNumero) ?></div>
                    <div class="doc-row"><strong>Fecha:</strong> <?= texto_doc($fechaEmision) ?></div>
                    <div class="doc-row"><strong>Vence:</strong> <?= texto_doc($fechaVence) ?></div>
                    <div class="doc-row"><strong>Pago:</strong> <?= texto_doc($condicionPago) ?></div>
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">Datos del cliente</div>
    <div class="box">
        <table class="info-box">
            <tr>
                <td class="info-label">Cliente:</td>
                <td class="info-value"><strong><?= texto_doc($cliente) ?></strong></td>
                <td class="info-label">Vendedor:</td>
                <td class="info-value"><strong><?= texto_doc($vendedor) ?></strong></td>
            </tr>
            <tr>
                <td class="info-label">DNI / RUC:</td>
                <td class="info-value"><?= texto_doc($documentoCliente) ?></td>
                <td class="info-label">Moneda:</td>
                <td class="info-value">SOLES</td>
            </tr>
            <tr>
                <td class="info-label">Dirección:</td>
                <td class="info-value"><?= texto_doc($direccionCliente) ?></td>
                <td class="info-label">Placa:</td>
                <td class="info-value"><?= texto_doc($venta['nroplaca'] ?? '-') ?></td>
            </tr>
        </table>
    </div>

 

    <div class="section-title">Detalle</div>
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
                <?php foreach ($detalle as $item): ?>
                    <tr>
                        <td class="text-center"><?= str_pad((string)($item['item'] ?? 0), 2, '0', STR_PAD_LEFT) ?></td>
                        <td><?= texto_doc(trim(($item['producto'] ?? '') . ' ' . ($item['descripcion'] ?? ''))) ?></td>
                        <td><?= texto_doc($item['unidad'] ?? '-') ?></td>
                        <td class="text-right"><?= numero_doc($item['cantidad'] ?? 0) ?></td>
                        <td class="text-right"><?= numero_doc($item['preciounitario'] ?? 0) ?></td>
                        <td class="text-right"><?= numero_doc($item['subtotal'] ?? 0) ?></td>
                    </tr>
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
                    <div class="son-value">SON: <?= texto_doc(strtoupper($total_texto) . ' Y 00/100 SOLES') ?></div>

                    <div class="extra-data">
                        <strong>Condición:</strong> <?= texto_doc($condicionPago) ?><br>
                        <strong>Vendedor:</strong> <?= texto_doc($vendedor) ?>
                    </div>

                    <?php if (!empty($qr_src)): ?>
                        <div class="qr-image">
                            <img src="<?= $qr_src ?>" alt="QR">
                        </div>
                    <?php endif; ?>
                </div>
            </td>

            <td class="bottom-right">
                <table class="totales-box">
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
