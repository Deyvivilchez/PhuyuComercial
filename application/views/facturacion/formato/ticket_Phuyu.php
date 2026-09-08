<?php
function texto_ticket($texto)
{
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}

function numero_ticket($monto)
{
    return number_format((float)$monto, 2);
}

function fecha_ticket($fecha)
{
    if (empty($fecha) || $fecha === '0000-00-00') return '-';
    $ts = strtotime($fecha);
    return $ts ? date('d/m/Y', $ts) : $fecha;
}

$empresaData   = is_array($empresa) && isset($empresa['documento']) ? $empresa : ($empresa[0] ?? []);
$sucursalData  = is_array($sucursal) && isset($sucursal['direccion']) ? $sucursal : ($sucursal[0] ?? []);
$ventaData     = is_array($venta) && isset($venta['cliente']) ? $venta : ($venta[0] ?? []);
$vendedorData  = is_array($vendedor) && isset($vendedor['razonsocial']) ? $vendedor : ($vendedor[0] ?? []);
$formatoData   = is_array($formato) && isset($formato['agradecimiento']) ? $formato : ($formato[0] ?? []);
$creditoData   = is_array($credito) && isset($credito['fechavencimiento']) ? $credito : ($credito[0] ?? []);
$totalesData   = is_array($totales) && isset($totales['gravado']) ? $totales : ($totales[0] ?? []);

$serieNumero = ($ventaData['seriecomprobante'] ?? '') . ' - ' . ($ventaData['nrocomprobante'] ?? '');
$fechaEmision = fecha_ticket($ventaData['fechacomprobante'] ?? '');
$fechaVencimiento = fecha_ticket($fechavencimiento ?? ($creditoData['fechavencimiento'] ?? ''));
$cliente = $ventaData['cliente'] ?? '-';
$documentoCliente = $ventaData['documento'] ?? '-';
$direccionCliente = $ventaData['direccion'] ?? '-';
$placa = $ventaData['nroplaca'] ?? '-';
$comprobante = $ventaData['comprobante'] ?? 'COMPROBANTE';
$nombreEmpresa = $nombre ?? ($empresaData['nombrecomercial'] ?? $empresaData['razonsocial'] ?? 'EMPRESA');
$rucEmpresa = $empresaData['documento'] ?? '-';
$direccionSucursal = $sucursalData['direccion'] ?? '-';
$telefonosSucursal = $sucursalData['telefonos'] ?? '-';
$vendedorNombre = $vendedorData['razonsocial'] ?? '-';
$vendedorTelefono = $vendedorData['telefono'] ?? '';
$condicionPago = ((int)($ventaData['condicionpago'] ?? 0) === 1)
    ? 'CONTADO'
    : 'CRÉDITO';

$importeEntregado = 0;
$vuelto = 0;
if (!empty($detallemovimiento) && isset($detallemovimiento[0])) {
    $importeEntregado = (float)($detallemovimiento[0]['importeentregado'] ?? 0);
    $vuelto = (float)($detallemovimiento[0]['vuelto'] ?? 0);
}

$logoSrc = $logo_src ?? '';
$qrSrc = $qr_src ?? '';
$agradecimiento = $formatoData['agradecimiento'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title><?= texto_ticket($comprobante . ' ' . $serieNumero) ?></title>
    <style>
        @page {
            margin: 2mm 2mm 4mm 2mm;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            color: #1f172a;
            background: #ffffff;
            font-size: 10px;
            line-height: 1.3;
        }

        .ticket {
            width: 76mm;
            margin: 0 auto;
            padding: 2mm 1.5mm 0 1.5mm;
            box-sizing: border-box;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .muted {
            color: #6b6575;
        }

        .tiny {
            font-size: 8px;
        }

        .small {
            font-size: 9px;
        }

        .strong {
            font-weight: bold;
        }

        .logo-wrap {
            text-align: center;
            margin-bottom: 4px;
        }

        .logo-wrap img {
            max-width: 150px;
            max-height: 70px;
            width: auto;
            height: auto;
        }

        .brand {
            text-align: center;
            margin-bottom: 6px;
        }

        .brand-name {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            color: #4c1d95;
            line-height: 1.2;
        }

        .brand-meta {
            font-size: 8.5px;
            line-height: 1.35;
            color: #5f586d;
            margin-top: 2px;
        }

        .doc-box {
            border: 1px solid #d9c2ff;
            border-radius: 8px;
            padding: 6px 6px 5px 6px;
            margin-bottom: 6px;
            text-align: center;
            background: #faf7ff;
        }

        .doc-ruc {
            font-size: 10px;
            font-weight: bold;
            color: #4c1d95;
            margin-bottom: 2px;
        }

        .doc-type {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: #1f172a;
            margin-bottom: 2px;
        }

        .doc-serie {
            font-size: 10px;
            font-weight: bold;
            color: #4c1d95;
        }

        .block {
            margin-bottom: 6px;
            border-top: 1px dashed #cdb9ef;
            border-bottom: 1px dashed #cdb9ef;
            padding: 5px 0;
        }

        .row {
            width: 100%;
            margin: 0 0 2px 0;
            clear: both;
        }

        .label {
            display: inline-block;
            width: 27%;
            vertical-align: top;
            font-weight: bold;
            color: #5e556e;
        }

        .value {
            display: inline-block;
            width: 71%;
            vertical-align: top;
            color: #1f172a;
        }

        .obs {
            margin-top: 4px;
            padding: 5px 6px;
            background: #faf7ff;
            border-left: 3px solid #6d28d9;
            border-radius: 5px;
            font-size: 8.5px;
            color: #5e556e;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            margin-bottom: 6px;
        }

        table.items thead th {
            font-size: 8px;
            text-transform: uppercase;
            color: #ffffff;
            background: #4c1d95;
            padding: 4px 3px;
            border: 1px solid #d9c2ff;
        }

        table.items tbody td {
            font-size: 8px;
            padding: 4px 3px;
            border-bottom: 1px dashed #e7dbfb;
            vertical-align: top;
        }

        .item-num {
            width: 8%;
            text-align: center;
        }

        .item-desc {
            width: 44%;
        }

        .item-und {
            width: 12%;
            text-align: center;
        }

        .item-cant {
            width: 12%;
            text-align: right;
        }

        .item-pu {
            width: 12%;
            text-align: right;
        }

        .item-imp {
            width: 12%;
            text-align: right;
        }

        .prod {
            font-weight: bold;
            color: #1f172a;
            line-height: 1.25;
        }

        .prod-extra {
            font-size: 7.5px;
            color: #746c83;
            line-height: 1.2;
            margin-top: 1px;
        }

        .amount-box {
            border: 1px solid #dcc9fb;
            border-radius: 8px;
            background: #fcfbff;
            padding: 6px;
            margin-bottom: 6px;
        }

        .amount-title {
            font-size: 8px;
            text-transform: uppercase;
            font-weight: bold;
            color: #6b5b95;
            margin-bottom: 4px;
        }

        .amount-text {
            font-size: 9px;
            font-weight: bold;
            color: #1f172a;
            line-height: 1.35;
        }

        table.totals {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        table.totals td {
            padding: 3px 2px;
            font-size: 8.5px;
        }

        .totals-label {
            color: #5e556e;
            font-weight: bold;
        }

        .totals-value {
            text-align: right;
            color: #1f172a;
            font-weight: bold;
        }

        .grand-total td {
            border-top: 1px solid #d8c3fb;
            border-bottom: 1px solid #d8c3fb;
            padding-top: 5px;
            padding-bottom: 5px;
            color: #4c1d95;
            font-size: 9.5px;
            font-weight: bold;
            background: #f7f2ff;
        }

        .pay-box {
            border: 1px solid #dcc9fb;
            border-radius: 8px;
            background: #faf7ff;
            padding: 6px;
            margin-bottom: 6px;
        }

        .qr-wrap {
            text-align: center;
            margin-top: 8px;
            margin-bottom: 6px;
        }

        .qr-wrap img {
            max-width: 85px;
            max-height: 85px;
        }

        .consult {
            font-size: 7.5px;
            color: #6b6575;
            text-align: center;
            line-height: 1.3;
            margin-top: 4px;
            word-break: break-word;
        }

        .thanks {
            text-align: center;
            font-size: 8px;
            color: #5e556e;
            line-height: 1.35;
            margin-top: 6px;
        }

        .footer-phuyu {
            margin-top: 8px;
            padding-top: 6px;
            border-top: 1px dashed #d5c3f5;
            text-align: center;
        }

        .footer-phuyu-brand {
            font-size: 8.5px;
            font-weight: bold;
            color: #4c1d95;
        }

        .footer-phuyu-text {
            font-size: 7px;
            color: #7a7286;
            line-height: 1.3;
            margin-top: 2px;
        }

        .footer-phuyu-site {
            font-size: 7.2px;
            font-weight: bold;
            color: #4c1d95;
            margin-top: 2px;
        }
    </style>
</head>
<body>
<div class="ticket">

    <?php if (!empty($logoSrc)): ?>
        <div class="logo-wrap">
            <img src="<?= $logoSrc ?>" alt="Logo">
        </div>
    <?php endif; ?>

    <div class="brand">
        <div class="brand-name"><?= texto_ticket($nombre) ?></div>
        <div class="brand-meta">
            RUC: <?= texto_ticket($empresaData['documento'] ?? '-') ?><br>
            <?= texto_ticket($sucursalData['direccion'] ?? '-') ?><br>
            <?= texto_ticket($sucursalData['telefonos'] ?? '-') ?>
        </div>
    </div>

    <div class="doc-box">
        <div class="doc-ruc">RUC: <?= texto_ticket($rucEmpresa) ?></div>
        <div class="doc-type"><?= texto_ticket($comprobante) ?></div>
        <div class="doc-serie"><?= texto_ticket($serieNumero) ?></div>
    </div>

    <div class="block">
        <div class="row">
            <span class="label">Fecha</span>
            <span class="value"><?= texto_ticket($fechaEmision) ?></span>
        </div>
        <div class="row">
            <span class="label">Cliente</span>
            <span class="value"><?= texto_ticket($cliente) ?></span>
        </div>
        <div class="row">
            <span class="label">Doc.</span>
            <span class="value"><?= texto_ticket($documentoCliente) ?></span>
        </div>
        <div class="row">
            <span class="label">Dirección</span>
            <span class="value"><?= texto_ticket($direccionCliente) ?></span>
        </div>
        <div class="row">
            <span class="label">Pago</span>
            <span class="value"><?= texto_ticket($condicionPago) ?></span>
        </div>
        <div class="row">
            <span class="label">Vence</span>
            <span class="value"><?= texto_ticket($fechaVencimiento) ?></span>
        </div>
        <div class="row">
            <span class="label">Vendedor</span>
            <span class="value"><?= texto_ticket($vendedorNombre) ?></span>
        </div>
        <?php if (!empty($placa) && $placa !== '-'): ?>
        <div class="row">
            <span class="label">Placa</span>
            <span class="value"><?= texto_ticket($placa) ?></span>
        </div>
        <?php endif; ?>
    </div>

    <div class="obs">
        <strong>Items:</strong>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th class="item-num">#</th>
                <th class="item-desc">Descripción</th>
                <th class="item-und">Und</th>
                <th class="item-cant">Cant</th>
                <th class="item-pu">P.U.</th>
                <th class="item-imp">Imp.</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($detalle)): ?>
                <?php foreach ($detalle as $i => $item): ?>
                    <tr>
                        <td class="item-num"><?= str_pad((string)($item['item'] ?? ($i + 1)), 2, '0', STR_PAD_LEFT) ?></td>
                        <td class="item-desc">
                            <div class="prod"><?= texto_ticket($item['producto'] ?? '') ?></div>
                            <?php if (!empty($item['descripcion'])): ?>
                                <div class="prod-extra"><?= texto_ticket($item['descripcion']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="item-und"><?= texto_ticket($item['unidad'] ?? '-') ?></td>
                        <td class="item-cant"><?= numero_ticket($item['cantidad'] ?? 0) ?></td>
                        <td class="item-pu"><?= numero_ticket($item['preciounitario'] ?? 0) ?></td>
                        <td class="item-imp"><?= numero_ticket($item['subtotal'] ?? 0) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="center">No hay detalles para mostrar.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="amount-box">
        <div class="amount-title">Importe en letras</div>
        <div class="amount-text"><?= texto_ticket($texto_importe) ?></div>
    </div>

    <table class="totals">
        <tr>
            <td class="totals-label">Op. gravadas</td>
            <td class="totals-value">S/ <?= numero_ticket($totalesData['gravado'] ?? 0) ?></td>
        </tr>
        <tr>
            <td class="totals-label">Op. inafectas</td>
            <td class="totals-value">S/ <?= numero_ticket($totalesData['inafecto'] ?? 0) ?></td>
        </tr>
        <tr>
            <td class="totals-label">Op. exoneradas</td>
            <td class="totals-value">S/ <?= numero_ticket($totalesData['exonerado'] ?? 0) ?></td>
        </tr>
        <tr>
            <td class="totals-label">Op. gratuitas</td>
            <td class="totals-value">S/ <?= numero_ticket($totalesData['gratuito'] ?? 0) ?></td>
        </tr>
        <tr>
            <td class="totals-label">Descuento</td>
            <td class="totals-value">S/ <?= numero_ticket($ventaData['descglobal'] ?? 0) ?></td>
        </tr>
        <tr>
            <td class="totals-label">IGV</td>
            <td class="totals-value">S/ <?= numero_ticket($ventaData['igv'] ?? 0) ?></td>
        </tr>
        <tr class="grand-total">
            <td>Total</td>
            <td class="right">S/ <?= numero_ticket($ventaData['importe'] ?? 0) ?></td>
        </tr>
    </table>

    <?php if ((int)$efectivo === 1): ?>
    <div class="pay-box">
        <div class="row">
            <span class="label">Efectivo</span>
            <span class="value right">S/ <?= numero_ticket($importeEntregado) ?></span>
        </div>
        <div class="row">
            <span class="label">Vuelto</span>
            <span class="value right">S/ <?= numero_ticket($vuelto) ?></span>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($qrSrc)): ?>
        <div class="qr-wrap">
            <img src="<?= $qrSrc ?>" alt="QR">
        </div>
    <?php endif; ?>

    <?php if (!empty($parametros['urlconsultacomprobantes'])): ?>
        <div class="consult">
            <?= texto_ticket($parametros['urlconsultacomprobantes']) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($agradecimiento)): ?>
        <div class="thanks">
            <?= texto_ticket($agradecimiento) ?>
        </div>
    <?php endif; ?>

    <div class="footer-phuyu">
        <div class="footer-phuyu-brand">PhuyuSystem ☁</div>
        <div class="footer-phuyu-text">Gracias por ser parte de la nube que nos impulsa</div>
        <div class="footer-phuyu-site">phuyusystem.com</div>
    </div>

</div>

<script>
window.onload = function () {
    window.print();
};
</script>
</body>
</html>