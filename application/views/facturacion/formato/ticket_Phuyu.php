<?php
if (!function_exists('texto_ticket')) {
    function texto_ticket($texto)
    {
        return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('numero_ticket')) {
    function numero_ticket($monto)
    {
        return number_format((float)$monto, 2);
    }
}

if (!function_exists('fecha_ticket')) {
    function fecha_ticket($fecha)
    {
        if (empty($fecha) || $fecha === '0000-00-00') return '-';
        $ts = strtotime($fecha);
        return $ts ? date('d/m/Y', $ts) : $fecha;
    }
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
$mesaRestaurante = trim((string)($mesa_restaurante ?? ''));
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
        /* Alto contraste para impresoras termicas: texto negro sin depender
           de fondos impresos. No aplicar filtros al QR ni al logo original. */
        @page {
            margin: 2mm 2mm 4mm 2mm;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, DejaVu Sans, sans-serif;
            color: #000000;
            background: #ffffff;
            font-size: 11px;
            line-height: 1.35;
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
            color: #000000;
        }

        .tiny {
            font-size: 10px;
        }

        .small {
            font-size: 10px;
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
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
            color: #000000;
            line-height: 1.2;
        }

        .brand-meta {
            font-size: 10px;
            line-height: 1.35;
            color: #000000;
            margin-top: 2px;
        }

        .doc-box {
            border: 1px solid #000000;
            border-radius: 8px;
            padding: 6px 6px 5px 6px;
            margin-bottom: 6px;
            text-align: center;
            background: #ffffff;
        }

        .doc-ruc {
            font-size: 10px;
            font-weight: bold;
            color: #000000;
            margin-bottom: 2px;
        }

        .doc-type {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: #000000;
            margin-bottom: 2px;
        }

        .doc-serie {
            font-size: 14px;
            font-weight: bold;
            color: #000000;
        }

        .block {
            margin-bottom: 6px;
            border-top: 1px dashed #000000;
            border-bottom: 1px dashed #000000;
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
            color: #000000;
        }

        .value {
            display: inline-block;
            width: 71%;
            vertical-align: top;
            color: #000000;
        }

        .obs {
            margin-top: 4px;
            padding: 5px 6px;
            background: #ffffff;
            border-left: 3px solid #000000;
            border-radius: 5px;
            font-size: 10px;
            color: #000000;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            margin-bottom: 6px;
        }

        table.items thead th {
            font-size: 10px;
            text-transform: uppercase;
            color: #000000;
            background: #ffffff;
            padding: 4px 3px;
            border: 1px solid #000000;
        }

        table.items tbody td {
            font-size: 10px;
            padding: 4px 3px;
            border-bottom: 1px dashed #000000;
            vertical-align: top;
        }

        .item-num {
            width: 8%;
            text-align: center;
        }

        table.items { table-layout: fixed; }
        table.items .item-desc {
            width: auto;
            padding: 6px 2px 1px;
            border-bottom: 0;
            overflow-wrap: anywhere;
        }
        table.items .item-values td {
            padding: 2px 2px 6px;
            font-size: 11px;
        }
        .item-values .item-pu, .item-values .item-imp {
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }
        .value { overflow-wrap: anywhere; }
        thead { display: table-header-group; }
        tr, .doc-box, .grand-total, .qr-wrap { break-inside: avoid; }


        .item-und {
            width: 25%;
            text-align: center;
        }

        .item-cant {
            width: 25%;
            text-align: right;
        }

        .item-pu {
            width: 25%;
            text-align: right;
        }

        .item-imp {
            width: 25%;
            text-align: right;
        }

        .prod {
            font-size: 11px;
            font-weight: bold;
            color: #000000;
            line-height: 1.25;
        }

        .prod-extra {
            font-size: 10px;
            color: #000000;
            line-height: 1.2;
            margin-top: 1px;
        }

        .amount-box {
            border: 1px solid #000000;
            border-radius: 8px;
            background: #ffffff;
            padding: 6px;
            margin-bottom: 6px;
        }

        .amount-title {
            font-size: 10px;
            text-transform: uppercase;
            font-weight: bold;
            color: #000000;
            margin-bottom: 4px;
        }

        .amount-text {
            font-size: 10px;
            font-weight: bold;
            color: #000000;
            line-height: 1.35;
        }

        table.totals {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        table.totals td {
            padding: 3px 2px;
            font-size: 10px;
        }

        .totals-label {
            color: #000000;
            font-weight: bold;
        }

        .totals-value {
            text-align: right;
            color: #000000;
            font-weight: bold;
        }

        .grand-total td {
            border-top: 1px solid #000000;
            border-bottom: 1px solid #000000;
            padding-top: 5px;
            padding-bottom: 5px;
            color: #000000;
            font-size: 16px;
            font-weight: bold;
            background: #ffffff;
        }

        .pay-box {
            border: 1px solid #000000;
            border-radius: 8px;
            background: #ffffff;
            padding: 6px;
            margin-bottom: 6px;
        }

        .qr-wrap {
            text-align: center;
            margin-top: 8px;
            margin-bottom: 6px;
        }

        .qr-wrap img {
            width: 28mm;
            height: 28mm;
            object-fit: contain;
        }

        .consult {
            font-size: 10px;
            color: #000000;
            text-align: center;
            line-height: 1.3;
            margin-top: 4px;
            word-break: break-word;
        }

        .thanks {
            text-align: center;
            font-size: 10px;
            color: #000000;
            line-height: 1.35;
            margin-top: 6px;
        }

        .footer-phuyu {
            margin-top: 8px;
            padding-top: 6px;
            border-top: 1px dashed #000000;
            text-align: center;
        }

        .footer-phuyu-brand {
            font-size: 10px;
            font-weight: bold;
            color: #000000;
        }

        .footer-phuyu-text {
            font-size: 10px;
            color: #000000;
            line-height: 1.3;
            margin-top: 2px;
        }

        .footer-phuyu-site {
            font-size: 10px;
            font-weight: bold;
            color: #000000;
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
            <?= texto_ticket($sucursalData['direccion'] ?? '-') ?><br>
            <?= texto_ticket($sucursalData['telefonos'] ?? '-') ?>
        </div>
    </div>

    <div class="doc-box">
        <div class="doc-ruc">RUC: <?= texto_ticket($rucEmpresa) ?></div>
        <div class="doc-type"><?= texto_ticket($comprobante) ?></div>
        <div class="doc-serie"><?= texto_ticket($serieNumero) ?></div>
    </div>

    <?php if (!empty($nota_credito)): ?>
    <div class="block">
        <div class="strong">COMPROBANTE AFECTADO</div>
        <div><?= texto_ticket($nota_credito['comprobante']) ?></div>
        <div class="strong"><?= texto_ticket($nota_credito['numero']) ?></div>
        <div><strong>Motivo:</strong> <?= texto_ticket($nota_credito['motivo']) ?></div>
        <?php if ($nota_credito['descripcion'] !== ''): ?>
        <div class="value" style="width:100%; word-break:break-word"><?= texto_ticket($nota_credito['descripcion']) ?></div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

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
        <!-- <div class="row">
            <span class="label">Vendedor</span>
            <span class="value"><?= texto_ticket($vendedorNombre) ?></span>
        </div>  -->
        <?php if ($mesaRestaurante !== ''): ?>
        <div class="row">
            <span class="label">Mesa</span>
            <span class="value strong"><?= texto_ticket($mesaRestaurante) ?></span>
        </div>
        <?php endif; ?>
        <?php if (!empty($placa) && $placa !== '-'): ?>
        <div class="row">
            <span class="label">Placa</span>
            <span class="value"><?= texto_ticket($placa) ?></span>
        </div>
        <?php endif; ?>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th class="item-cant">Cant.</th>
                <th class="item-und">Unidad</th>
                <th class="item-pu">P. unit.</th>
                <th class="item-imp">Importe</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($detalle)): ?>
                <?php foreach ($detalle as $i => $item): ?>
                    <tr>
                        <!-- Nombre a todo el ancho: evita comprimir productos largos. -->
                        <td colspan="4" class="item-desc">
                            <div class="prod"><?= texto_ticket($item['producto'] ?? '') ?></div>
                            <?php
                            $productoDescripcion = trim((string)($item['producto'] ?? ''));
                            $descripcionExtra = trim((string)($item['descripcion'] ?? ''));

                            if (
                                $productoDescripcion !== '' &&
                                strncasecmp($descripcionExtra, $productoDescripcion, strlen($productoDescripcion)) === 0
                            ) {
                                $descripcionExtra = trim(substr($descripcionExtra, strlen($productoDescripcion)));
                                $descripcionExtra = preg_replace('/^[\s\-:|]+/u', '', $descripcionExtra);
                            }
                            ?>
                            <?php if ($mesaRestaurante === '' && $descripcionExtra !== ''): ?>
                                <div class="prod-extra"><?= nl2br(texto_ticket($descripcionExtra)) ?></div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr class="item-values">
                        <td class="item-cant"><?= numero_ticket($item['cantidad'] ?? 0) ?></td>
                        <td class="item-und"><?= texto_ticket($item['unidad'] ?? '-') ?></td>
                        <td class="item-pu"><?= numero_ticket($item['preciounitario'] ?? 0) ?></td>
                        <td class="item-imp"><?= numero_ticket($item['subtotal'] ?? 0) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="center">No hay detalles para mostrar.</td>
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
        <div class="footer-phuyu-brand">PhuyuSystem</div>
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
