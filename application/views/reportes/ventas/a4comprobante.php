<?php
require_once __DIR__ . "/../../../helpers/descripcion_item_helper.php";
$modo_pdf = isset($modo_pdf) ? (bool)$modo_pdf : false;

if (!function_exists('texto_doc')) {
    function texto_doc($texto)
    {
        return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('numero_doc')) {
    function numero_doc($monto)
    {
        return number_format((float)$monto, 2);
    }
}

if (!function_exists('fecha_doc')) {
    function fecha_doc($fecha)
    {
        if (empty($fecha) || $fecha === '0000-00-00') return '-';
        $ts = strtotime($fecha);
        return $ts ? date('d/m/Y', $ts) : $fecha;
    }
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
            margin: 10mm 9mm 24mm 9mm;
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
            padding-bottom: 0;
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

        /* Pie compartido: fuera del flujo y dentro del margen reservado de cada hoja. */
        .footer-band-fixed {
            position: fixed;
            left: 0;
            right: 0;
            bottom: -16mm;
            height: 10mm;
            padding: 7px 0 0;
            border-top: 1px solid #d9c6e9;
            background: transparent;
            color: #654379;
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
        <?php if (!empty($nota_credito)): ?>
        /* La nota suma un bloque de referencia: compactar sin reducir legibilidad. */
        .company-logo img { max-width: 150px; max-height: 65px; }
        .company-logo { margin-bottom: 5px; }
        .brand-block { padding: 9px 12px; }
        .doc-box { padding: 9px 12px; }
        .info-table td { padding-top: 2px; padding-bottom: 2px; }
        .section-title { margin-top: 9px; margin-bottom: 4px; }
        .page-content { padding-bottom: 8mm; }
        .footer-band-fixed { background: #581c87; }
        .brand-footnote { display: none; }
        .title-ribbon { padding: 16px 18px 14px; border-radius: 0 20px 0 20px; }
        .doc-name { font-size: 23px; letter-spacing: 0; }
        .doc-serie { margin-top: 6px; font-size: 10px; }
        .brand-block { border: none; background: transparent; }
        .company-name { font-size: 15px; }
        .company-meta { font-size: 9.5px; color: #51475f; }
        .doc-box { min-height: 0; background: #faf7ff; border: 1px solid #e5d9f2; }
        .doc-row { font-size: 9.5px; line-height: 1.75; }
        .watermark-wrap { top: 73mm; left: 21mm; width: 150mm; transform: none; }
        .watermark-wrap img { opacity: 0.17; }
        .section-title { border-bottom: 1px solid #e4d5f3; letter-spacing: 0.5px; }
        .note-client td { padding: 6px 8px; vertical-align: top; }
        .note-caption { font-size: 8px; text-transform: uppercase; color: #6b5c7b; letter-spacing: 0.6px; }
        .note-client-name { font-size: 11px; font-weight: bold; color: #241533; line-height: 1.5; }
        .note-address { font-size: 10px; line-height: 1.5; }
        .note-reference { border-left: 4px solid #7c3bad; background: rgba(250,247,255,0.65); }
        .note-reference .info-label { width: 16%; color: #63318a; }
        .detalle-table th { padding: 9px 6px; }
        .detalle-table td { padding: 9px 6px; line-height: 1.5; }
        .son-value { font-size: 11px; line-height: 1.55; }
        .total-final td { background: #581c87; padding-top: 11px; padding-bottom: 11px; }
        .total-final .totales-label { font-size: 12px; color: #ffffff; }
        .total-final .totales-value { font-size: 17px; color: #ffffff; }
        .note-kicker { color: #77658b; font-size: 8px; letter-spacing: 1.4px; border-top: 4px solid #581c87; padding-top: 10px; }
        .note-kicker span { padding: 0 8px; color: #bca5d1; }
        .note-header { margin-top: 12px; margin-bottom: 10px; table-layout: fixed; }
        .note-header td { vertical-align: top; }
        .note-brand { width: 62%; padding-right: 18px; }
        .note-brand-grid { table-layout: fixed; }
        .note-brand-logo { width: 34%; padding-right: 12px; vertical-align: middle; }
        .note-brand-copy { width: 66%; vertical-align: middle; }
        .note-logo { max-width: 120px; max-height: 85px; margin: 0; }
        .note-company { font-size: 12px; color: #251330; font-weight: bold; line-height: 1.4; }
        .note-company-details { margin-top: 5px; font-size: 9px; color: #64586d; line-height: 1.6; }
        .note-identity { width: 38%; text-align: right; }
        .note-type { color: #581c87; font-size: 20px; line-height: 1.25; font-weight: bold; letter-spacing: -0.2px; }
        .note-electronic { margin-top: 4px; color: #897197; font-size: 8px; letter-spacing: 1.2px; }
        .note-number { font-size: 17px; font-weight: bold; color: #271737; margin-top: 12px; }
        .note-date { margin-top: 5px; font-size: 9px; color: #64586d; }
        .note-meta { background: #f6f2fa; border-top: 1px solid #e8def0; border-bottom: 1px solid #e8def0; margin-bottom: 10px; }
        .note-meta td { width: 33.33%; padding: 7px 13px; font-size: 10px; line-height: 1.6; }
        .note-meta span { font-size: 7px; letter-spacing: 0.7px; color: #806c92; }
        .section-title { border: 0; font-size: 9px; color: #a182bb; margin-top: 11px; margin-bottom: 6px; }
        .section-title span { color: #581c87; padding-left: 8px; }
        .info-box { border: 0; border-top: 1px solid #e9e1f0; border-bottom: 1px solid #e9e1f0; border-radius: 0; padding: 5px 0; background: transparent; }
        .note-client td { padding: 6px 0; }
        .note-reference-card { padding: 9px 13px; border: 1px solid #e5d8ef; border-left: 3px solid #8752ae; border-radius: 0 8px 8px 0; background: rgba(249,246,252,0.35); }
        .note-reference-grid td { vertical-align: top; padding-right: 12px; }
        .note-ref-number { font-size: 12px; font-weight: bold; margin-top: 5px; color: #332040; }
        .note-ref-motive { font-size: 11px; margin-top: 5px; color: #332040; }
        .note-observation { margin-top: 10px; padding-top: 8px; border-top: 1px solid #e9e0f1; font-size: 9px; color: #665570; }
        .detalle-table th { font-size: 8px; background: #581c87; border: 0; padding: 9px 6px; }
        .detalle-table td { font-size: 9px; border: 0; border-bottom: 1px solid #eae3f0; padding: 10px 6px; }
        .detalle-table tbody td { background: rgba(255,255,255,0.35); }
        .detalle-table tbody tr:nth-child(even) td { background: rgba(249,246,252,0.4); }
        .son-box { border: none; padding: 7px 0; background: transparent; }
        .son-label { color: #897694; letter-spacing: 0.8px; font-size: 7px; }
        .totales-box { border: 0; background: transparent; }
        .totales-label { font-weight: normal; color: #766580; }
        .totales-box td { padding: 5px 9px; }
        .footer-band-fixed { border-radius: 0; padding: 9px 0; background: transparent; border-top: 1px solid #d9c6e9; color: #654379; bottom: 3mm; }
        .footer-fixed-small { color: #8b7998; }
        /* Pie real en cada hoja. El margen inferior reserva su espacio. */
        @page { margin: 10mm 9mm 24mm 9mm; }
        .footer-band-fixed {
            position: fixed; left: 0; right: 0; bottom: -16mm;
            height: 10mm; padding: 7px 0 0; margin: 0;
            border-top: 1px solid #d9c6e9;
        }
        .footer-fixed-left, .footer-fixed-right { vertical-align: top; line-height: 1.5; }
        .footer-fixed-small { font-size: 7.5px; }
        .page-content { padding-bottom: 0; }

        <?php endif; ?>
        <?php if (!empty($nota_credito) && ($papel_nota ?? 'A4') === 'A5'): ?>
        /* A5 vertical: medidas propias, no reducir una hoja A4 al imprimir. */
        @page { size: 148mm 210mm; margin: 7mm 7mm 16mm 7mm; }
        body { font-size: 7.5pt; }
        .note-kicker { font-size: 6px; padding-top: 4px; border-top-width: 2px; }
        .note-header { margin-top: 3px; margin-bottom: 3px; }
        .note-logo { max-width: 80px; max-height: 43px; }
        .note-company { font-size: 10px; }
        .note-company-details { font-size: 7px; line-height: 1.3; margin-top: 2px; }
        .note-type { font-size: 15px; }
        .note-electronic { font-size: 6px; margin-top: 1px; }
        .note-number { font-size: 12px; margin-top: 3px; }
        .note-date { font-size: 7px; margin-top: 1px; }
        .note-meta { margin-bottom: 3px; }
        .note-meta td { padding: 3px 8px; font-size: 8px; line-height: 1.3; }
        .note-meta span { font-size: 6px; }
        .section-title { font-size: 7px; margin: 4px 0 2px; padding-bottom: 1px; }
        .info-box { padding: 2px 0; margin-bottom: 2px; }
        .note-client td { padding: 2px 0; }
        .note-caption { font-size: 6px; }
        .note-client-name, .note-address { font-size: 8px; line-height: 1.3; }
        .note-reference-card { padding: 4px 8px; }
        .note-ref-number, .note-ref-motive { font-size: 8px; margin-top: 2px; }
        .note-observation { font-size: 7px; margin-top: 3px; padding-top: 3px; }
        .detalle-table thead th { font-size: 7pt; padding: 4px; }
        .detalle-table tbody td { font-size: 7pt; padding: 3px 4px; line-height: 1.25; }
        .bottom-grid { margin-top: 3px; }
        .son-box { padding: 3px 0; }
        .son-label { font-size: 6px; margin-bottom: 3px; }
        .son-value { font-size: 8px; line-height: 1.35; }
        .qr-image { margin-top: 4px; }
        .qr-image img { max-width: 55px; max-height: 55px; }
        .qr-text, .agradecimiento { font-size: 6px; margin-top: 2px; }
        .totales-box td { font-size: 7px; padding: 2px 7px; }
        .total-final .totales-label { font-size: 9px; }
        .total-final .totales-value { font-size: 11px; }
        .total-final td { padding-top: 4px; padding-bottom: 4px; }
        .watermark-wrap { top: 28mm; left: 38mm; width: 115mm; }
        .footer-band-fixed { bottom: -9mm; height: 6mm; padding-top: 3px; }
        .footer-fixed-left, .footer-fixed-right { font-size: 7px; }
        .footer-fixed-small { font-size: 6px; }
        .note-brand { width: 60%; padding-right: 10px; }
        .note-brand-logo { width: 34%; padding-right: 7px; }
        .note-identity { width: 40%; }
        .note-company { font-size: 10px; }
        .note-company-details { font-size: 8px; }
        .note-type { font-size: 14px; }
        .note-number { font-size: 12px; }
        .note-meta td { padding: 5px 7px; font-size: 9px; }
        .note-caption { font-size: 7px; }
        .note-client-name, .note-address { font-size: 9px; }
        .note-ref-number, .note-ref-motive { font-size: 9px; }
        .note-reference-grid td { padding-right: 6px; }
        .note-observation { font-size: 8px; }
        .section-title { margin-top: 7px; font-size: 8px; }
        .detalle-table thead th { font-size: 6.5pt; padding: 5px 3px; }
        .detalle-table tbody td { font-size: 7pt; padding: 5px 3px; }
        .son-col { width: 44%; padding-right: 8px; }
        .total-col { width: 56%; }
        .son-value { font-size: 9px; }
        .qr-image img { max-width: 76px; max-height: 76px; }
        .totales-box td { font-size: 8px; padding: 4px 6px; }
        .total-final .totales-value { font-size: 13px; }
        .total-final .totales-label { font-size: 10px; }
        .watermark-wrap { top: 48mm; left: 6mm; width: 120mm; }
        .footer-band-fixed { bottom: -11mm; height: 7mm; }
        <?php endif; ?>
    </style>
</head>
<body>
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

    <div class="page">

        <?php if (!empty($logo_src)): ?>
            <div class="watermark-wrap">
                <img src="<?= $logo_src ?>" alt="Marca de agua">
            </div>
        <?php endif; ?>

        <div class="page-content">

            <?php if (!empty($nota_credito)): ?>
            <div class="note-kicker">DOCUMENTO ELECTRÓNICO <span>·</span> <?= texto_doc($fechaEmision) ?></div>
            <table class="note-header">
                <tr>
                    <td class="note-brand">
                        <table class="note-brand-grid"><tr>
                        <?php if (!empty($logo_src)): ?><td class="note-brand-logo"><img class="note-logo" src="<?= $logo_src ?>" alt="Logo de empresa"></td><?php endif; ?>
                        <td class="note-brand-copy"<?= empty($logo_src) ? ' style="width:100%"' : '' ?>>
                        <div class="note-company"><?= texto_doc($nombre_empresa ?? '') ?></div>
                        <div class="note-company-details">RUC <?= texto_doc($empresa['documento'] ?? '-') ?><br><?= texto_doc($sucursal['direccion'] ?? '-') ?><br><?= texto_doc($sucursal['telefonos'] ?? '') ?></div>
                        </td></tr></table>
                    </td>
                    <td class="note-identity">
                        <div class="note-type">NOTA DE CRÉDITO</div>
                        <div class="note-electronic">ELECTRÓNICA</div>
                        <div class="note-number"><?= texto_doc($serieNumero) ?></div>
                        <div class="note-date">Emisión <?= texto_doc($fechaEmision) ?></div>
                    </td>
                </tr>
            </table>
            <table class="note-meta"><tr>
                <td><span>CONDICIÓN DE PAGO</span><br><strong><?= texto_doc($condicionPago) ?></strong></td>
                <td><span>MONEDA</span><br><strong>Soles · PEN</strong></td>
                <td><span>TIPO DE DOCUMENTO</span><br><strong>Nota de crédito · 07</strong></td>
            </tr></table>
            <?php else: ?>
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

            <?php endif; ?>
            <div class="section-title"><?= !empty($nota_credito) ? '01 <span>Datos del cliente</span>' : 'Datos del cliente y operación' ?></div>
            <div class="info-box">
                <?php if (!empty($nota_credito)): ?>
                <table class="note-client">
                    <tr>
                        <td width="72%"><div class="note-caption">Razón social / nombre</div><div class="note-client-name"><?= texto_doc($cliente) ?></div></td>
                        <td width="28%"><div class="note-caption">DNI / RUC</div><div class="note-client-name"><?= texto_doc($documentoCliente) ?></div></td>
                    </tr>
                    <tr><td colspan="2"><div class="note-caption">Dirección</div><div class="note-address"><?= texto_doc($direccionCliente) ?></div></td></tr>
                </table>
                <?php else: ?>
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
                <?php endif; ?>
            </div>

            <?php if (!empty($nota_credito)): ?>
            <div class="section-title">02 <span>Referencia de la nota</span></div>
            <div class="note-reference-card">
                <table class="note-reference-grid"><tr>
                    <td width="40%"><div class="note-caption">COMPROBANTE AFECTADO</div><div class="note-ref-number"><?= texto_doc($nota_credito['referencia']) ?></div></td>
                    <td width="60%"><div class="note-caption">MOTIVO</div><div class="note-ref-motive"><?= texto_doc($nota_credito['motivo']) ?></div></td>
                </tr></table>
                <?php if (trim((string)$nota_credito['descripcion']) !== ''): ?>
                <div class="note-observation"><strong>Descripción:</strong> <?= texto_doc($nota_credito['descripcion']) ?></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="section-title"><?= !empty($nota_credito) ? '03 <span>Detalle de la nota</span>' : 'Detalle del comprobante' ?></div>
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
                                <?php $bloquesDescripcion = phuyu_descripcion_bloques($mesaRestaurante === '' ? ($item['descripcion'] ?? '') : ''); ?>
                        <td style="word-wrap:break-word"><strong><?= texto_doc($item['producto'] ?? '') ?></strong><?php if (!empty($bloquesDescripcion)): ?><br><?= nl2br(texto_doc(array_shift($bloquesDescripcion))) ?><?php endif; ?></td>
                                <td><?= texto_doc($item['unidad'] ?? '-') ?></td>
                                <td class="text-right"><?= numero_doc($item['cantidad'] ?? 0) ?></td>
                                <td class="text-right">S/ <?= numero_doc($item['preciounitario'] ?? 0) ?></td>
                                <td class="text-right">S/ <?= numero_doc($item['subtotal'] ?? 0) ?></td>
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
                    <td class="son-col">
                        <div class="son-box">
                            <div class="son-label">Importe en letras</div>
                            <div class="son-value"><?= texto_doc($importe_en_letras ?? ('SON: ' . strtoupper($total_texto ?? '') . ' Y 00/100 SOLES')) ?></div>

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



        </div>

    </div>
</body>
</html>
