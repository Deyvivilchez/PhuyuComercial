<?php
$modo_pdf = isset($modo_pdf) ? (bool) $modo_pdf : false;

function texto_doc($texto)
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

function numero_doc($monto)
{
    return number_format((float) $monto, 2);
}

function fecha_doc($fecha)
{
    if (empty($fecha) || $fecha === '0000-00-00') {
        return '-';
    }
    $ts = strtotime($fecha);
    return $ts ? date('d/m/Y', $ts) : $fecha;
}

$fechaEmision = date('d/m/Y');
$horaEmision = date('H:i');

$logoSrc = !empty($logo_src) ? $logo_src : (!empty($logo_url) ? $logo_url : '');
$qrSrc = !empty($qr_src) ? $qr_src : '';
$barcodeSrc = !empty($barcode_src) ? $barcode_src : '';

$direccionSucursal = $sucursal['direccion'] ?? '-';
$telefonosSucursal = $sucursal['telefonos'] ?? '-';
$documentoEmpresa = $empresa['documento'] ?? '-';
$fechaPedido = fecha_doc($venta['fechapedido'] ?? '');
$condicionPago = (int) ($venta['condicionpago'] ?? 0) === 1 ? 'CONTADO' : 'CRÉDITO';
$totalItems = is_array($detalle) ? count($detalle) : 0;
$glosa = trim((string) ($venta['descripcion'] ?? ''));
$cliente = $venta['cliente'] ?? '-';
$direccionCliente = !empty($venta['direccion']) ? $venta['direccion'] : '-';
$documentoCliente = !empty($venta['documento']) ? $venta['documento'] : '-';
$vendedor = $venta['vendedor'] ?? '-';
$usuario = $venta['usuario'] ?? '-';
$serieNumero = ($venta['seriecomprobante'] ?? '') . ' - ' . ($venta['nrocomprobante'] ?? '');
$nombreEmpresa = $nombre_empresa ?? '';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title><?= texto_doc(($venta['comprobante'] ?? 'PEDIDO') . ' ' . $serieNumero) ?></title>
    <link rel="icon" href="<?php echo base_url(); ?>public/img/icono-phuyu.ico">
    <style>
       @page {
    size: A4;
    margin:  5mm 9mm 8mm 9mm;
}

        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #241f31;
            background: <?=$modo_pdf ? '#ffffff': '#f5f0ff' ?>;
        }

        .acciones {
            width: 190mm;
            margin: 12px auto 0;
            text-align: right;
        }

        .acciones button,
        .acciones a {
            display: inline-block;
            padding: 10px 14px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            cursor: pointer;
            background: #581c87;
            color: #fff;
            font-size: 11px;
            margin-left: 8px;
        }

        /* .documento {
            width: 210mm;
            min-height: 297mm;
            margin: <?=$modo_pdf ? '0 auto': '10px auto 20px auto' ?>;
            background: #ffffff;
            box-shadow: <?=$modo_pdf ? 'none': '0 12px 28px rgba(88, 28, 135, 0.10)' ?>;
            position: relative;
            overflow: hidden;
            page-break-after: always;
        } */

        /* .page {
            position: relative;
            padding: 10mm 9mm 8mm 9mm;
        } */

        .page-content {
            position: relative;
            z-index: 2;
        }

        .watermark-wrap {
            position: fixed;
            top: 50%;
            left: 50%;
            width: 135mm;
            margin-left: -67.5mm;
            margin-top: -67.5mm;
            text-align: center;
            z-index: 0;
        }

        .watermark-wrap img {
            width: 100%;
            height: auto;
            opacity: 0.08;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .top-band {
            width: 100%;
            margin-bottom: 12px;
            table-layout: fixed;
        }

        .top-band td {
            vertical-align: top;
        }

        .title-col {
            width: 62%;
            padding-right: 8px;
        }

        .doc-col {
            width: 38%;
        }

        .title-ribbon {
            background: #581c87;
            color: #ffffff;
            border-radius: 0 22 0 22px;
            padding: 14px 16px 12px 16px;
            margin-bottom: 10px;
        }

        .doc-name {
            margin: 0;
            font-size: 22px;
            font-weight: bold;
            line-height: 1.05;
            letter-spacing: 0.2px;
            text-transform: uppercase;
        }

        .doc-serie {
            margin-top: 5px;
            font-size: 9px;
            opacity: 0.95;
        }

        .brand-block {
            border: 1px solid #e4d4ff;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.62);
            padding: 12px 14px;
        }

        .company-logo {
            margin: 0 0 8px 0;
        }

        .company-logo img {
            max-width: 220px;
            max-height: 120px;
            width: auto;
            height: auto;
        }

        .company-name {
            margin: 6px 0 3px 0;
            font-size: 13px;
            font-weight: bold;
            line-height: 1.35;
            text-transform: uppercase;
            color: #1d1430;
        }

        .company-meta {
            font-size: 8.8px;
            color: #6f6682;
            line-height: 1.5;
        }

        .doc-box {
            border: 1px solid #dfd0ff;
            background: rgba(250, 247, 255, 0.72);
            padding: 11px 12px;
            min-height: 150px;
            border-radius: 12px;
        }

        .doc-box-title {
            font-size: 10.4px;
            font-weight: bold;
            color: #581c87;
            margin-bottom: 7px;
            text-transform: uppercase;
            border-bottom: 1px solid #eadcff;
            padding-bottom: 5px;
        }

        .doc-row {
            font-size: 9.1px;
            line-height: 1.75;
            color: #4b425b;
        }

        .chip {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid #cab0ff;
            background: #efe6ff;
            color: #6d28d9;
        }

        .info-grid {
            margin-top: 6px;
            margin-bottom: 9px;
            table-layout: fixed;
        }

        .info-grid td {
            vertical-align: top;
        }

        .client-card {
            border: 1px solid #e4d4ff;
            background: rgba(255, 255, 255, 0.60);
            padding: 9px 10px;
            border-radius: 10px;
        }

        .card-title {
            margin: 0 0 7px 0;
            font-size: 10px;
            font-weight: bold;
            color: #581c87;
            text-transform: uppercase;
            border-bottom: 1px solid #dcc3ff;
            padding-bottom: 4px;
        }

        .mini-table td {
            padding: 4px 2px;
            font-size: 8.9px;
            vertical-align: top;
        }

        .mini-label {
            width: 18%;
            font-weight: bold;
            color: #7b728a;
        }

        .mini-value {
            width: 32%;
            color: #171220;
        }

        .section-title {
            margin: 10px 0 6px 0;
            font-size: 10.8px;
            font-weight: bold;
            text-transform: uppercase;
            color: #581c87;
            border-bottom: 2px solid #dcc3ff;
            padding-bottom: 4px;
            letter-spacing: 0.2px;
        }

        .detalle-table {
            margin-top: 4px;
            table-layout: fixed;
        }

        .detalle-table thead th {
            background: #4c1d95;
            color: #ffffff;
            padding: 8px 4px;
            font-size: 8.3px;
            text-transform: uppercase;
            border: 1px solid #d9c2ff;
            line-height: 1.2;
        }

        .detalle-table tbody td {
            border: 1px solid #eadcff;
            padding: 6px 5px;
            font-size: 8.8px;
            color: #241f31;
            vertical-align: top;
            line-height: 1.35;
            background: #ffffff;
        }

        .detalle-table tbody tr:nth-child(even) td {
            background: #fcf9ff;
        }

        .producto-nombre {
            font-weight: bold;
            color: #171220;
        }

        .producto-extra {
            color: #7b728a;
            font-size: 8.3px;
            margin-top: 2px;
        }

        .bottom-grid {
            margin-top: 8px;
            table-layout: fixed;
        }

        .bottom-grid td {
            vertical-align: top;
        }

        .son-col {
            width: 56%;
            padding-right: 7px;
        }

        .total-col {
            width: 44%;
        }

        .son-box {
            border: 1px solid #e4d4ff;
            background: rgba(255, 255, 255, 0.64);
            min-height: 98px;
            padding: 10px 11px;
            border-radius: 10px;
        }

        .son-label {
            font-size: 8.5px;
            font-weight: bold;
            color: #7b728a;
            text-transform: uppercase;
            margin-bottom: 7px;
        }

        .son-value {
            font-size: 13px;
            font-weight: bold;
            color: #171220;
            line-height: 1.45;
        }

        .agradecimiento {
            margin-top: 7px;
            font-size: 8px;
            color: #7b728a;
            line-height: 1.5;
        }

        .totales-box {
            border: 1px solid #e4d4ff;
            background: rgba(255, 255, 255, 0.68);
            border-radius: 10px;
            overflow: hidden;
        }

        .totales-box td {
            padding: 6px 9px;
            border-bottom: 1px solid #efe5ff;
            font-size: 8.8px;
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
            font-size: 9.4px;
        }

        .tech-grid {
            margin-top: 8px;
            table-layout: fixed;
        }

        .tech-grid td {
            vertical-align: top;
        }

        .qr-col {
            width: 28%;
            padding-right: 6px;
        }

        .barcode-col {
            width: 72%;
        }

        .qr-box,
        .barcode-box {
            border: 1px solid #e4d4ff;
            background: rgba(250, 247, 255, 0.72);
            border-radius: 10px;
            padding: 9px 10px;
        }

        .tech-title {
            margin: 0 0 6px 0;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            color: #581c87;
        }

        .qr-image {
            text-align: center;
        }

        .qr-image img {
            max-width: 88px;
            max-height: 88px;
        }

        .barcode-image {
            text-align: center;
        }

        .barcode-image img {
            width: 100%;
            max-width: 230px;
            max-height: 58px;
        }

        .barcode-text {
            margin-top: 4px;
            text-align: center;
            font-size: 8px;
            color: #6f6682;
            letter-spacing: 0.6px;
        }

        .obs-box {
            margin-top: 8px;
            border: 1px solid #e4d4ff;
            background: rgba(250, 247, 255, 0.72);
            padding: 8px 9px;
            font-size: 8px;
            color: #5e556e;
            line-height: 1.55;
            border-left: 3px solid #6d28d9;
            border-radius: 7px;
        }

        .footer-band {
            margin-top: 8px;
            background: #4c1d95;
            color: #ffffff;
            padding: 10px 11px;
            border-radius: 10px 10px 0 0;
        }

        .footer-band table td {
            padding: 0;
            vertical-align: middle;
        }

        .footer-left {
            width: 62%;
            font-size: 9px;
            line-height: 1.45;
            font-weight: bold;
        }

        .footer-right {
            width: 38%;
            text-align: right;
            font-size: 8.5px;
            line-height: 1.45;
        }

        .footer-small {
            font-size: 7.8px;
            opacity: 0.92;
            font-weight: normal;
        }

        @media print {
            body {
                background: #ffffff;
            }

            .acciones {
                display: none;
            }

            .documento {
                width: 100%;
                min-height: auto;
                margin: 0;
                box-shadow: none;
                page-break-after: auto;
            }
        }

        .footer-band {
            margin-top: 9px;
            background: #4c1d95;
            color: #ffffff;
            padding: 10px 12px 8px 12px;
            border-radius: 12px 12px 0 0;
        }

        .footer-main td {
            padding: 0;
            vertical-align: middle;
        }

        .footer-left {
            width: 62%;
            font-size: 9px;
            line-height: 1.4;
            font-weight: bold;
        }

        .footer-right {
            width: 38%;
            text-align: right;
            font-size: 8.8px;
            line-height: 1.4;
            font-weight: bold;
        }

        .footer-sub {
            margin-top: 5px;
            border-top: 1px solid rgba(255, 255, 255, 0.18);
        }

        .footer-sub td {
            padding-top: 5px;
            vertical-align: top;
        }

        .footer-sub-left {
            width: 55%;
            font-size: 7.7px;
            line-height: 1.35;
            opacity: 0.92;
        }

        .footer-sub-right {
            width: 45%;
            text-align: right;
            font-size: 7.6px;
            line-height: 1.35;
            opacity: 0.95;
        }

        .footer-sub-right strong {
            color: #ffffff;
        }
    </style>
</head>

<body>

    <?php if (!$modo_pdf): ?>
    <div class="acciones">
        <button onclick="window.print()">Imprimir</button>
        <a href="<?= site_url('TU_CONTROLADOR/a4pedido_pdf/' . ($venta['codpedido'] ?? 0)) ?>" target="_blank">Exportar
            PDF</a>
    </div>
    <?php endif; ?>

    <div class="documento">
        <div class="page">

            <?php if (!empty($logoSrc)): ?>
            <div class="watermark-wrap">
                <img src="<?= $logoSrc ?>" alt="Marca de agua">
            </div>
            <?php endif; ?>

            <div class="page-content">
                <div class="title-ribbon">
                    <div class="doc-name"><?= texto_doc($venta['comprobante'] ?? 'NOTA DE PEDIDO') ?></div>
                    <div class="doc-serie">Serie / Número: <?= texto_doc($serieNumero) ?></div>
                </div>
                <table class="top-band">
                    <tr>
                        <td class="title-col">


                            <div class="brand-block">
                                <div class="company-logo">
                                    <?php if (!empty($logoSrc)): ?>
                                    <img src="<?= $logoSrc ?>" alt="Logo">
                                    <?php endif; ?>
                                </div>

                                <div class="company-name"><?= texto_doc($nombreEmpresa) ?></div>
                                <div class="company-meta">
                                    <strong>RUC:</strong> <?= texto_doc($documentoEmpresa) ?><br>
                                    <?= texto_doc($direccionSucursal) ?><br>
                                    <?= texto_doc($telefonosSucursal) ?>
                                </div>
                            </div>
                        </td>

                        <td class="doc-col">
                            <div class="doc-box">
                                <div class="doc-box-title">Datos del comprobante</div>
                                <div class="doc-row"><strong>RUC:</strong> <?= texto_doc($documentoEmpresa) ?></div>
                                <div class="doc-row"><strong>Serie y número:</strong> <?= texto_doc($serieNumero) ?>
                                </div>
                                <div class="doc-row"><strong>Emisión:</strong> <?= $fechaEmision ?> &nbsp;
                                    <?= $horaEmision ?></div>
                                <div class="doc-row"><strong>Fecha pedido:</strong> <?= texto_doc($fechaPedido) ?></div>
                                <div class="doc-row"><strong>Condición:</strong>
                                    <strong class=""><?= texto_doc($condicionPago) ?></strong>
                                </div>
                                <!-- <div class="doc-row"><strong>Ítems:</strong> <?= (int) $totalItems ?></div> -->
                            </div>
                        </td>
                    </tr>
                </table>

                <table class="info-grid">
                    <tr>
                        <td>
                            <div class="client-card">
                                <div class="card-title">Datos del cliente y operación</div>
                                <table class="mini-table">
                                    <tr>
                                        <td class="mini-label">Código:</td>
                                        <td class="mini-value">
                                            <strong><?= '000' . texto_doc($venta['codpersona'] ?? '') ?></strong></td>
                                        <td class="mini-label">Vendedor:</td>
                                        <td class="mini-value"><strong><?= texto_doc($vendedor) ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td class="mini-label">Cliente:</td>
                                        <td class="mini-value"><strong><?= texto_doc($cliente) ?></strong></td>
                                        <td class="mini-label">Usuario:</td>
                                        <td class="mini-value"><strong><?= texto_doc($usuario) ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td class="mini-label">DNI / RUC:</td>
                                        <td class="mini-value"><?= texto_doc($documentoCliente) ?></td>
                                        <td class="mini-label">Moneda:</td>
                                        <td class="mini-value">SOLES</td>
                                    </tr>
                                    <tr>
                                        <td class="mini-label">Dirección:</td>
                                        <td class="mini-value"><?= texto_doc($direccionCliente) ?></td>
                                        <td class="mini-label">Glosa:</td>
                                        <td class="mini-value"><?= texto_doc($glosa !== '' ? $glosa : '-') ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>

                <div class="section-title">Detalle del pedido</div>

                <table class="detalle-table">
                    <thead>
                        <tr>
                            <th width="6%">Item</th>
                            <th width="42%">Descripción</th>
                            <th width="16%">Unidad</th>
                            <th width="12%">Cantidad</th>
                            <th width="12%">P. Unit.</th>
                            <th width="12%">Importe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($detalle)) : ?>
                        <?php foreach ($detalle as $i => $item) : ?>
                        <tr>
                            <td class="text-center"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></td>
                            <td>
                                <div class="producto-nombre"><?= texto_doc($item['producto'] ?? '') ?></div>
                                <?php if (!empty($item['descripcion'])) : ?>
                                <div class="producto-extra"><?= texto_doc($item['descripcion']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= texto_doc($item['unidad'] ?? '-') ?></td>
                            <td class="text-right"><?= numero_doc($item['cantidad'] ?? 0) ?></td>
                            <td class="text-right">S/ <?= numero_doc($item['preciounitario'] ?? 0) ?></td>
                            <td class="text-right">S/ <?= numero_doc($item['subtotal'] ?? 0) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else : ?>
                        <tr>
                            <td colspan="6" class="text-center">No hay detalles para mostrar.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <table class="bottom-grid">
                    <tr>
                        <td class="son-col">
                            <div class="son-box">
                                <div class="son-label">Importe en letras</div>
                                <div class="son-value">SON: <?= texto_doc($total_texto ?? '-') ?></div>
                                <?php if (!empty($parametros['agradecimiento'])) : ?>
                                <div class="agradecimiento"><?= texto_doc($parametros['agradecimiento']) ?></div>
                                <?php endif; ?>
                                <div class="qr-image">
                                    <?php if (!empty($qrSrc)): ?>
                                    <img src="<?= $qrSrc ?>" alt="QR">
                                    <?php else: ?>
                                    <div class="barcode-text">Sin QR</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="total-col">
                            <table class="totales-box">
                                <tr>
                                    <td class="totales-label">Subtotal</td>
                                    <td class="totales-value">S/ <?= numero_doc($venta['valorventa'] ?? 0) ?></td>
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
                                    <td class="totales-label">Total a pagar</td>
                                    <td class="totales-value">S/ <?= numero_doc($venta['importe'] ?? 0) ?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>



                <div class="obs-box">
                    <strong>Nota:</strong> El presente documento refleja la información registrada en el sistema al
                    momento de su emisión. Los importes, cantidades y condiciones mostrados corresponden al pedido
                    generado.
                </div>

                <div class="footer-band">
                    <table class="footer-main">
                        <tr>
                            <td class="footer-left">
                                <strong>PhuyuSystem</strong> ☁️<br>
                                <span class="footer-small">Gracias por ser parte de la nube que nos impulsa</span>
                            </td>
                            <td class="footer-right">
                                <span class="footer-small">Generado digitalmente por</span><br>
                                <strong>phuyusystem.com</strong>
                            </td>
                        </tr>
                    </table>
                </div>

            </div>
        </div>
    </div>
</body>

</html>
