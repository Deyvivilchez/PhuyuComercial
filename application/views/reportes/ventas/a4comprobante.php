<?php
$modo_pdf = isset($modo_pdf) ? (bool)$modo_pdf : false;

function texto_doc($texto)
{
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}

function numero_doc($monto)
{
    return number_format((float)$monto, 2);
}

function fecha_doc($fecha)
{
    if (empty($fecha) || $fecha === '0000-00-00') return '-';
    $ts = strtotime($fecha);
    return $ts ? date('d/m/Y', $ts) : $fecha;
}

$fechaEmision = fecha_doc($venta['fechacomprobante'] ?? '');
$serieNumero = ($venta['seriecomprobante'] ?? '') . ' - ' . ($venta['nrocomprobante'] ?? '');
$documentoCliente = $venta['documento'] ?? '-';
$direccionCliente = $venta['direccion'] ?? '-';
$cliente = $venta['cliente'] ?? '-';
$usuario = $_SESSION['phuyu_usuario'] ?? '-';
$vendedor = $empleado['razonsocial'] ?? '-';
$mesaRestaurante = trim((string)($mesa_restaurante ?? ''));
$condicionPago = ((int)($venta['condicionpago'] ?? 0) === 1)
    ? 'CONTADO'
    : 'CRÉDITO' . (!empty($credito['nrodias']) ? ': ' . $credito['nrodias'] . ' días' : '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title><?= texto_doc(($venta['comprobante'] ?? 'COMPROBANTE') . ' ' . $serieNumero) ?></title>
    <style>
        @page {
            margin: 10mm 9mm 14mm 9mm;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 9pt;
            color: #241f31;
        }

        .page {
            position: relative;
        }

        .page-content {
            position: relative;
            z-index: 2;
            padding-bottom: 30mm;
        }

        .watermark-wrap {
            position: fixed;
            top: 50%;
            left: 50%;
            width: 160mm;
            text-align: center;
            z-index: 0;
            transform: translate(-50%, -50%);
        }

        .watermark-wrap img {
            width: 100%;
            height: auto;
            opacity: 0.12;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }

        .title-ribbon {
            background: #581c87;
            color: #ffffff;
            border-radius: 0 22px 0 22px;
            padding: 14px 18px 11px 18px;
            margin-bottom: 8px;
        }

        .doc-name {
            margin: 0;
            font-size: 21px;
            font-weight: bold;
            line-height: 1.05;
            letter-spacing: 0.2px;
            text-transform: uppercase;
        }

        .doc-serie {
            margin-top: 4px;
            font-size: 8.8px;
            opacity: 0.95;
        }

        .doc-tagline {
            margin-top: 3px;
            font-size: 7.8px;
            opacity: 0.88;
            font-style: italic;
            letter-spacing: 0.1px;
        }

        .top-band {
            width: 100%;
            margin-bottom: 10px;
            table-layout: fixed;
        }

        .top-band td {
            vertical-align: top;
        }

        .title-col {
            width: 64%;
            padding-right: 8px;
        }

        .doc-col {
            width: 36%;
        }

        .brand-block {
            border: 1px solid rgba(228, 212, 255, 0.70);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.50);
            padding: 12px 14px 11px 14px;
        }

        .company-logo {
            margin: 0 0 10px 0;
            text-align: left;
        }

        .company-logo img {
            max-width: 250px;
            max-height: 130px;
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
            font-size: 8.6px;
            color: #6f6682;
            line-height: 1.55;
        }

        .doc-box {
            border: 1px solid rgba(223, 208, 255, 0.68);
            background: rgba(250, 247, 255, 0.58);
            padding: 11px 12px;
            min-height: 150px;
            border-radius: 12px;
        }

        .doc-box-title {
            font-size: 10.2px;
            font-weight: bold;
            color: #581c87;
            margin-bottom: 8px;
            text-transform: uppercase;
            border-bottom: 1px solid #eadcff;
            padding-bottom: 5px;
        }

        .doc-row {
            font-size: 8.9px;
            line-height: 1.8;
            color: #4b425b;
        }

        .section-title {
            margin-top: 10px;
            margin-bottom: 6px;
            font-size: 10.6px;
            font-weight: bold;
            text-transform: uppercase;
            color: #581c87;
            border-bottom: 2px solid #dcc3ff;
            padding-bottom: 4px;
            letter-spacing: 0.2px;
        }

        .info-box,
        .observacion,
        .footer-info,
        .son-box {
            border: 1px solid rgba(228, 212, 255, 0.60);
            background: rgba(255, 255, 255, 0.44);
            border-radius: 10px;
            padding: 6px 8px;
        }

        .info-box {
            margin-bottom: 4px;
        }

        .observacion,
        .footer-info {
            margin-top: 4px;
        }

        .info-table td {
            padding: 2px 4px;
            font-size: 8.1pt;
            vertical-align: top;
        }

        .info-box .info-table td {
            background: transparent;
        }

        .info-label {
            width: 15%;
            font-weight: bold;
            color: #7b728a;
        }

        .info-value {
            width: 35%;
            color: #171220;
        }

        .detalle-table {
            margin-top: 4px;
            table-layout: fixed;
        }

        .detalle-table thead th {
            background: #4c1d95;
            color: #ffffff;
            font-size: 7.7pt;
            padding: 8px 4px;
            border: 1px solid #d9c2ff;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .detalle-table tbody td {
            font-size: 8pt;
            padding: 6px 4px;
            border: 1px solid rgba(234, 220, 255, 0.74);
            color: #241f31;
            line-height: 1.3;
            vertical-align: middle;
            background: rgba(255, 255, 255, 0.66);
        }

        .detalle-table tbody tr:nth-child(even) td {
            background: rgba(252, 249, 255, 0.74);
        }

        .bottom-grid {
            margin-top: 7px;
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

        .son-label {
            font-size: 8.5px;
            font-weight: bold;
            color: #7b728a;
            text-transform: uppercase;
            margin-bottom: 7px;
        }

        .son-value {
            font-size: 12.4px;
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

        .qr-image {
            margin-top: 10px;
            text-align: center;
        }

        .qr-image img {
            max-width: 90px;
            max-height: 90px;
        }

        .qr-text {
            margin-top: 4px;
            font-size: 7.6px;
            color: #6f6682;
            text-align: center;
            line-height: 1.35;
        }

        .proforma-note {
            margin-top: 12px;
            border: 1px solid #e4d4ff;
            border-left: 4px solid #6d28d9;
            border-radius: 10px;
            background: rgba(250, 247, 255, 0.76);
            padding: 10px 12px;
        }

        .proforma-note-title {
            font-size: 9px;
            font-weight: bold;
            color: #581c87;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .proforma-note-text {
            font-size: 8.1px;
            color: #5e556e;
            line-height: 1.45;
        }

        .totales-box {
            border: 1px solid rgba(228, 212, 255, 0.60);
            background: rgba(255, 255, 255, 0.50);
            border-radius: 10px;
            overflow: hidden;
        }

        .totales-box td {
            padding: 6px 9px;
            border-bottom: 1px solid rgba(239, 229, 255, 0.92);
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
            background: rgba(245, 237, 255, 0.92);
        }

        .total-final .totales-label,
        .total-final .totales-value {
            color: #581c87;
            font-size: 9.4px;
        }

        .observacion,
        .footer-info {
            font-size: 8pt;
            color: #5e556e;
            line-height: 1.6;
        }

        .brand-footnote {
            margin-top: 5px;
            padding-top: 4px;
            border-top: 1px solid rgba(228, 212, 255, 0.65);
            text-align: center;
            font-size: 7px;
            color: #7b728a;
            line-height: 1.2;
        }

        .brand-footnote strong {
            color: #581c87;
            font-weight: bold;
        }

        .observacion {
            border-left: 3px solid #6d28d9;
            background: rgba(250, 247, 255, 0.54);
        }

        .footer-info strong {
            color: #581c87;
        }

        .cuentas {
            margin-top: 10px;
            font-size: 8pt;
            color: #5e556e;
            line-height: 1.6;
        }

        .cuenta-item {
            margin-bottom: 8px;
        }

        .leyenda {
            margin-top: 10px;
            text-align: center;
            font-size: 7.8px;
            color: #6f6682;
            line-height: 1.45;
        }

        .footer-band-fixed {
            position: fixed;
            left: 9mm;
            right: 9mm;
            bottom: 8mm;
            background: linear-gradient(160deg, rgba(88, 28, 135, 0.96) 0%, rgba(40, 24, 79, 0.96) 100%);
            color: #ffffff;
            padding: 10px 12px;
            border-radius: 12px 12px 0 0;
            z-index: 3;
        }

        .footer-band-fixed table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-band-fixed td {
            padding: 0;
            vertical-align: middle;
        }

        .footer-fixed-left {
            width: 60%;
            font-size: 9px;
            line-height: 1.4;
            font-weight: bold;
        }

        .footer-fixed-right {
            width: 40%;
            text-align: right;
            font-size: 8.6px;
            line-height: 1.4;
            font-weight: bold;
        }

        .footer-fixed-small {
            font-size: 7.7px;
            opacity: 0.92;
            font-weight: normal;
        }
    </style>
</head>
<body>
    <div class="page">

        <?php if (!empty($logo_src)): ?>
            <div class="watermark-wrap">
                <img src="<?= $logo_src ?>" alt="Marca de agua">
            </div>
        <?php endif; ?>

        <div class="footer-band-fixed">
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

        <div class="page-content">

            <div class="title-ribbon">
                <div class="doc-name"><?= texto_doc($venta['comprobante'] ?? 'COMPROBANTE') ?></div>
                <div class="doc-serie"><?= texto_doc($serieNumero) ?> · Emitido el <?= texto_doc($fechaEmision) ?></div>
            </div>

            <table class="top-band">
                <tr>
                    <td class="title-col">
                        <div class="brand-block">
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
                                <?php if (!empty($principal) && !empty($sucursal) && ($principal['codsucursal'] ?? 0) != ($sucursal['codsucursal'] ?? 0)): ?>
                                    <strong>PRINCIPAL:</strong> <?= texto_doc($principal['direccion'] ?? '-') ?><br>
                                    <strong>SUCURSAL:</strong> <?= texto_doc($sucursal['direccion'] ?? '-') ?><br>
                                <?php else: ?>
                                    <?= texto_doc($sucursal['direccion'] ?? '-') ?><br>
                                <?php endif; ?>
                                <?= texto_doc($sucursal['telefonos'] ?? '-') ?>
                            </div>
                        </div>
                    </td>

                    <td class="doc-col">
                        <div class="doc-box">
                            <div class="doc-box-title">Datos del comprobante</div>
                            <div class="doc-row"><strong>RUC:</strong> <?= texto_doc($empresa['documento'] ?? '-') ?></div>
                            <div class="doc-row"><strong>Serie y número:</strong> <?= texto_doc($serieNumero) ?></div>
                            <div class="doc-row"><strong>Fecha emisión:</strong> <?= texto_doc($fechaEmision) ?></div>
                            <div class="doc-row"><strong>Fecha vencimiento:</strong> <?= texto_doc(fecha_doc($fechavencimiento ?? '')) ?></div>
                            <div class="doc-row"><strong>Condición:</strong> <?= texto_doc($condicionPago) ?></div>
                            <div class="doc-row"><strong>Moneda:</strong> SOLES</div>
                            <?php if ($mesaRestaurante !== ''): ?>
                            <div class="doc-row"><strong>Mesa:</strong> <?= texto_doc($mesaRestaurante) ?></div>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            </table>

            <div class="section-title">Datos del cliente y operación</div>
            <div class="info-box">
                <table class="info-table">
                    <tr>
                        <td class="info-label">Código:</td>
                        <td class="info-value"><strong>000<?= texto_doc($venta['codpersona'] ?? '') ?></strong></td>
                        <td class="info-label">Vendedor:</td>
                        <td class="info-value"><strong><?= texto_doc($vendedor) ?></strong></td>
                    </tr>
                    <tr>
                        <td class="info-label">Razón social:</td>
                        <td class="info-value"><strong><?= texto_doc($cliente) ?></strong></td>
                        <td class="info-label">Usuario:</td>
                        <td class="info-value"><strong><?= texto_doc($usuario) ?></strong></td>
                    </tr>
                    <tr>
                        <td class="info-label">DNI / RUC:</td>
                        <td class="info-value"><?= texto_doc($documentoCliente) ?></td>
                        <td class="info-label">Placa:</td>
                        <td class="info-value"><?= texto_doc($venta['nroplaca'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="info-label">Dirección:</td>
                        <td class="info-value"><?= texto_doc($direccionCliente) ?></td>
                        <td class="info-label">Comprobante:</td>
                        <td class="info-value"><?= texto_doc($venta['oficial'] ?? '-') ?></td>
                    </tr>
                </table>
            </div>

            <div class="section-title">Detalle del comprobante</div>
            <table class="detalle-table">
                <thead>
                    <tr>
                        <th width="6%">Item</th>
                        <th width="40%">Descripción</th>
                        <th width="15%">Und. medida</th>
                        <th width="13%">Cantidad</th>
                        <th width="13%">P. unitario</th>
                        <th width="13%">Importe</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($detalle)): ?>
                        <?php foreach ($detalle as $i => $item): ?>
                            <tr>
                                <td class="text-center"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></td>
                                <td><?= texto_doc(trim(
                                    ($item['producto'] ?? '') .
                                    ($mesaRestaurante === '' ? ' ' . ($item['descripcion'] ?? '') : '')
                                )) ?></td>
                                <td><?= texto_doc($item['unidad'] ?? '-') ?></td>
                                <td class="text-right"><?= numero_doc($item['cantidad'] ?? 0) ?></td>
                                <td class="text-right">S/ <?= numero_doc($item['preciounitario'] ?? 0) ?></td>
                                <td class="text-right">S/ <?= numero_doc($item['subtotal'] ?? 0) ?></td>
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
                    <td class="son-col">
                        <div class="son-box">
                            <div class="son-label">Importe en letras</div>
                            <div class="son-value">SON: <?= texto_doc(strtoupper($total_texto) . ' Y 00/100 SOLES') ?></div>

                            <?php if (!empty($qr_src)): ?>
                                <div class="qr-image">
                                    <img src="<?= $qr_src ?>" alt="QR">
                                </div>
                            <?php elseif (!empty($es_proforma)): ?>
                                <div class="proforma-note">
                                    <div class="proforma-note-title">Proforma valida para cotizacion</div>
                                    <div class="proforma-note-text">
                                        Documento informativo no valido como comprobante de pago.<br>
                                        Precios sujetos a disponibilidad y confirmacion de venta.
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (empty($es_proforma) && !empty($parametros['urlconsultacomprobantes'])): ?>
                                <div class="qr-text"><?= texto_doc($parametros['urlconsultacomprobantes']) ?></div>
                            <?php endif; ?>

                            <?php if (!empty($formato['agradecimiento'])): ?>
                                <div class="agradecimiento"><?= texto_doc($formato['agradecimiento']) ?></div>
                            <?php endif; ?>
                        </div>
                    </td>

                    <td class="total-col">
                        <table class="totales-box">
                            <tr>
                                <td class="totales-label">Op. gravadas</td>
                                <td class="totales-value">S/ <?= numero_doc(($totales['gravado'] ?? 0) - ($venta['igv'] ?? 0)) ?></td>
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
                            <tr>
                                <td class="totales-label">ICBPER</td>
                                <td class="totales-value">S/ <?= numero_doc($venta['icbper'] ?? 0) ?></td>
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
                <div class="footer-info cuentas">
                    <strong>Cuentas corrientes</strong><br>
                    <?php foreach ($cuentascorrientes as $cta): ?>
                        <div class="cuenta-item">
                            <strong><?= texto_doc($cta['banco'] ?? '-') ?></strong><br>
                            CC: <?= texto_doc($cta['nroctacte'] ?? '-') ?><br>
                            CCI: <?= texto_doc($cta['descripcion'] ?? '-') ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

         

            <?php if (!empty($venta['conleyendaamazonia'])): ?>
                <div class="leyenda">
                    <?php
                    if (($formato['tipoconleyendaamazonia'] ?? 0) == 1) {
                        echo texto_doc(($parametros['codleyendapamazonia'] ?? '') . ' - ' . ($parametros['leyendapamazonia'] ?? ''));
                    } elseif (($formato['tipoconleyendaamazonia'] ?? 0) == 2) {
                        echo texto_doc(($parametros['codleyendasamazonia'] ?? '') . ' - ' . ($parametros['leyendasamazonia'] ?? ''));
                    } else {
                        echo texto_doc(($parametros['codleyendapamazonia'] ?? '') . ' - ' . ($parametros['leyendapamazonia'] ?? ''));
                        echo '<br>';
                        echo texto_doc(($parametros['codleyendasamazonia'] ?? '') . ' - ' . ($parametros['leyendasamazonia'] ?? ''));
                    }
                    ?>
                </div>
            <?php endif; ?>

            <div class="brand-footnote">
                Impreso desde <strong>PhuyuSystem</strong> • Gracias por ser parte de la nube que nos inspira ☁️<br>
                <span>www.phuyusystem.com</span>
            </div>

        </div>
    </div>
</body>
</html>
