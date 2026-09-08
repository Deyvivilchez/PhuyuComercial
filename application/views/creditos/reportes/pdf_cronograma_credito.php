<?php
$fechaEmision = date('d/m/Y');
$horaEmision = date('H:i');
$hoy = date('Y-m-d');

$nombreEmpresa = !empty($_SESSION["phuyu_empresa"]) ? $_SESSION["phuyu_empresa"] : 'Mi Empresa';
$nombreCliente = isset($credito["razonsocial"]) ? $credito["razonsocial"] : '';
$documentoCliente = isset($credito["documento"]) ? $credito["documento"] : '-';
$direccionCliente = !empty($credito["direccion"]) ? $credito["direccion"] : '-';
$telefonoCliente = !empty($credito["telefono"]) ? $credito["telefono"] : '-';
$codcredito = isset($credito["codcredito"]) ? $credito["codcredito"] : '';

$logoPath = FCPATH . 'public/img/' . ($_SESSION['phuyu_logo'] ?? '');
$logoSrc = '';



if (!empty($_SESSION['phuyu_logo']) && file_exists($logoPath)) {
    $extension = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
    $mime = ($extension === 'jpg' || $extension === 'jpeg') ? 'jpeg' : 'png';
    $logoSrc = 'data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
}

function formato_fecha_pdf($fecha)
{
    if (empty($fecha) || $fecha === '0000-00-00') return '-';
    $ts = strtotime($fecha);
    return $ts ? date('d/m/Y', $ts) : $fecha;
}

function texto_pdf($texto)
{
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}

function numero_pdf($monto)
{
    return number_format((float)$monto, 2);
}

$fechaCredito = formato_fecha_pdf($credito["fechacredito"] ?? '');
$fechaVencimiento = formato_fecha_pdf($credito["fechavencimiento"] ?? '');
$totalCuotas = is_array($cuotas) ? count($cuotas) : 0;
?>
<html>
<head>

    <meta charset="utf-8">
    <title>Cronograma de crédito</title>
    <link rel="icon" href="<?php echo base_url(); ?>public/img/icono-phuyu.ico">


    <style>
        @page {
            margin: 10mm 9mm 10mm 9mm;
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

        .page-content {
            position: relative;
            z-index: 2;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .title-ribbon {
            background: #581c87;
            color: #ffffff;
            border-radius: 0 22px 0 22px;
            padding: 14px 16px 12px 16px;
            margin-bottom: 10px;
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
            margin-top: 5px;
            font-size: 9px;
            opacity: 0.95;
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

        .brand-block {
            border: 1px solid rgba(228, 212, 255, 0.80);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.66);
            padding: 12px 14px;
        }

        .company-logo {
            margin: 0 0 8px 0;
        }

        .company-logo img {
            max-width: 220px;
            max-height: 110px;
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
            border: 1px solid rgba(223, 208, 255, 0.85);
            background: rgba(250, 247, 255, 0.68);
            padding: 11px 12px;
            min-height: 148px;
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

        .estado-chip {
            display: inline-block;
            margin-top: 5px;
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid #cbd5e1;
            line-height: 1.2;
        }

        .estado-chip-pendiente {
            background: #fef3c7;
            color: #92400e;
            border-color: #fcd34d;
        }

        .estado-chip-pagado {
            background: #dcfce7;
            color: #166534;
            border-color: #86efac;
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
        .estado-box,
        .observacion,
        .footer-info {
            border: 1px solid rgba(228, 212, 255, 0.78);
            background: rgba(255, 255, 255, 0.64);
            border-radius: 10px;
            padding: 9px 10px;
        }

        .info-box {
            margin-bottom: 10px;
        }

        .estado-box,
        .observacion,
        .footer-info {
            margin-top: 10px;
        }

        .info-table td {
            padding: 4px 5px;
            font-size: 8.7pt;
            vertical-align: top;
        }

        .info-label {
            width: 16%;
            font-weight: bold;
            color: #7b728a;
        }

        .info-value {
            width: 34%;
            color: #171220;
        }

        .cronograma {
            margin-top: 4px;
            table-layout: fixed;
        }

        .cronograma thead th {
            background: #4c1d95;
            color: #ffffff;
            font-size: 7.7pt;
            padding: 8px 4px;
            border: 1px solid #d9c2ff;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .cronograma tbody td {
            font-size: 8pt;
            padding: 6px 4px;
            border: 1px solid rgba(234, 220, 255, 0.90);
            color: #241f31;
            line-height: 1.3;
            vertical-align: middle;
            word-wrap: break-word;
            background: rgba(255, 255, 255, 0.78);
        }

        .fila-par td {
            background: rgba(252, 249, 255, 0.88);
        }

        .fila-pagada td {
            background: #f0fdf4;
        }

        .fila-vencida td {
            background: #fff7ed;
        }

        .fila-total td {
            background: #f5edff;
            font-weight: bold;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 6.6pt;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 9px;
            border: 1px solid #cbd5e1;
            line-height: 1.2;
            white-space: nowrap;
        }

        .badge-pagado {
            background: #dcfce7;
            color: #166534;
            border-color: #86efac;
        }

        .badge-pendiente {
            background: #fef3c7;
            color: #92400e;
            border-color: #fcd34d;
        }

        .badge-vencido {
            background: #ffedd5;
            color: #c2410c;
            border-color: #fdba74;
        }

        .resumen-table {
            border: 1px solid rgba(228, 212, 255, 0.78);
            margin-top: 12px;
            margin-bottom: 12px;
            background: rgba(255, 255, 255, 0.66);
            border-radius: 10px;
            overflow: hidden;
        }

        .resumen-table th {
            background: rgba(250, 247, 255, 0.86);
            color: #581c87;
            font-size: 7.6pt;
            font-weight: bold;
            text-transform: uppercase;
            padding: 8px 6px;
            border: 1px solid rgba(234, 220, 255, 0.90);
        }

        .resumen-table td {
            font-size: 9.4pt;
            font-weight: bold;
            color: #171220;
            text-align: center;
            padding: 8px 6px;
            border: 1px solid rgba(234, 220, 255, 0.90);
        }

        .estado-box,
        .observacion,
        .footer-info {
            font-size: 8pt;
            color: #5e556e;
            line-height: 1.6;
        }

        .observacion {
            border-left: 3px solid #6d28d9;
            background: rgba(250, 247, 255, 0.68);
        }

        .footer-info strong {
            color: #581c87;
        }

        .signatures {
            margin-top: 24px;
        }

        .signatures td {
            width: 50%;
            vertical-align: top;
        }

        .sign-line {
            width: 80%;
            padding-top: 22px;
            border-top: 1px solid #c4b5fd;
            text-align: center;
            font-size: 8pt;
            color: #6f6682;
        }

        .footer-band {
            margin-top: 12px;
            background: linear-gradient(160deg, rgba(88, 28, 135, 0.96) 0%, rgba(40, 24, 79, 0.96) 100%);
            color: #ffffff;
            padding: 10px 12px;
            border-radius: 12px 12px 0 0;
        }

        .footer-main td {
            padding: 0;
            vertical-align: middle;
        }

        .footer-left {
            width: 60%;
            font-size: 9px;
            line-height: 1.4;
            font-weight: bold;
        }

        .footer-right {
            width: 40%;
            text-align: right;
            font-size: 8.6px;
            line-height: 1.4;
            font-weight: bold;
        }

        .footer-small {
            font-size: 7.7px;
            opacity: 0.92;
            font-weight: normal;
        }
    </style>
</head>

<body>
    <div class="page">

        <?php if (!empty($logoSrc)): ?>
            <div class="watermark-wrap">
                <img src="<?php echo $logoSrc; ?>">
            </div>
        <?php endif; ?>

        <div class="page-content">

            <div class="title-ribbon">
                <div class="doc-name">Cronograma de crédito</div>
                <div class="doc-serie">CR-<?php echo str_pad((string)$codcredito, 6, '0', STR_PAD_LEFT); ?> · Emitido el <?php echo $fechaEmision; ?> · <?php echo $horaEmision; ?></div>
            </div>

            <table class="top-band">
                <tr>
                    <td class="title-col">
                        <div class="brand-block">
                            <div class="company-logo">
                                <?php if (!empty($logoSrc)): ?>
                                    <img src="<?php echo $logoSrc; ?>">
                                <?php endif; ?>
                            </div>

                            <div class="company-name"><?php echo texto_pdf($nombreEmpresa); ?></div>
                            <div class="company-meta">
                                Documento financiero que presenta el detalle del crédito, el cronograma
                                de cuotas y el saldo pendiente correspondiente al cliente
                                <strong><?php echo texto_pdf($nombreCliente); ?></strong>.
                            </div>
                        </div>
                    </td>
                    <td class="doc-col">
                        <div class="doc-box">
                            <div class="doc-box-title">Datos del crédito</div>
                            <div class="doc-row"><strong>Código:</strong> CR-<?php echo str_pad((string)$codcredito, 6, '0', STR_PAD_LEFT); ?></div>
                            <div class="doc-row"><strong>Fecha crédito:</strong> <?php echo $fechaCredito; ?></div>
                            <div class="doc-row"><strong>Vencimiento:</strong> <?php echo $fechaVencimiento; ?></div>
                            <div class="doc-row"><strong>N° cuotas:</strong> <?php echo $totalCuotas; ?></div>
                            <div class="doc-row">
                                <strong>Estado:</strong>
                                <span class="estado-chip <?php echo $cuotas_pendientes > 0 ? 'estado-chip-pendiente' : 'estado-chip-pagado'; ?>">
                                    <?php echo $cuotas_pendientes > 0 ? 'Pendiente' : 'Pagado'; ?>
                                </span>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            <div class="section-title">Datos del crédito</div>
            <div class="info-box">
                <table class="info-table">
                    <tr>
                        <td class="info-label">Deudor:</td>
                        <td class="info-value"><strong><?php echo texto_pdf($nombreCliente); ?></strong></td>
                        <td class="info-label">N° de cuotas:</td>
                        <td class="info-value"><strong><?php echo $totalCuotas; ?></strong></td>
                    </tr>
                    <tr>
                        <td class="info-label">DNI / RUC:</td>
                        <td class="info-value"><strong><?php echo texto_pdf($documentoCliente); ?></strong></td>
                        <td class="info-label">Fecha crédito:</td>
                        <td class="info-value"><strong><?php echo $fechaCredito; ?></strong></td>
                    </tr>
                    <tr>
                        <td class="info-label">Monto:</td>
                        <td class="info-value"><strong>S/ <?php echo numero_pdf($credito["importe"] ?? 0); ?></strong></td>
                        <td class="info-label">Vencimiento:</td>
                        <td class="info-value"><strong><?php echo $fechaVencimiento; ?></strong></td>
                    </tr>
                    <tr>
                        <td class="info-label">Dirección:</td>
                        <td class="info-value"><?php echo texto_pdf($direccionCliente); ?></td>
                        <td class="info-label">Teléfono:</td>
                        <td class="info-value"><?php echo texto_pdf($telefonoCliente); ?></td>
                    </tr>
                </table>
            </div>

            <div class="section-title">Cronograma de pagos</div>
            <table class="cronograma">
                <thead>
                    <tr>
                        <th width="6%">#</th>
                        <th width="18%">Fecha</th>
                        <th width="17%">Importe</th>
                        <th width="17%">Interés</th>
                        <th width="17%">Cuota</th>
                        <th width="14%">Saldo</th>
                        <th width="11%">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($totalCuotas > 0): ?>
                        <?php $saldoCronograma = (float)($total_total ?? 0); ?>
                        <?php foreach ($cuotas as $index => $row): ?>
                            <?php
                            $estado = isset($row["estado"]) ? (int)$row["estado"] : 1;
                            $esPagado = ($estado === 0 || $estado === 2);

                            $claseFila = ($index % 2 === 0) ? 'fila-par' : '';
                            $badge = '<span class="badge badge-pendiente">Pendiente</span>';

                            if ($esPagado) {
                                $claseFila = 'fila-pagada';
                                $badge = '<span class="badge badge-pagado">Pagado</span>';
                            } elseif (!empty($row["fechavence"]) && $row["fechavence"] < $hoy) {
                                $claseFila = 'fila-vencida';
                                $badge = '<span class="badge badge-vencido">Vencido</span>';
                            }

                            $fechaCuota = formato_fecha_pdf($row["fechavence"] ?? '');
                            $importe = (float)($row["importe"] ?? 0);
                            $interes = (float)($row["interes"] ?? 0);
                            $totalFila = (float)($row["total"] ?? 0);

                            $saldoCronograma -= $totalFila;
                            if ($saldoCronograma < 0) {
                                $saldoCronograma = 0;
                            }
                            ?>
                            <tr class="<?php echo $claseFila; ?>">
                                <td class="text-center"><?php echo texto_pdf($row["nrocuota"] ?? ($index + 1)); ?></td>
                                <td class="text-center"><?php echo $fechaCuota; ?></td>
                                <td class="text-right">S/ <?php echo numero_pdf($importe); ?></td>
                                <td class="text-right">S/ <?php echo numero_pdf($interes); ?></td>
                                <td class="text-right">S/ <?php echo numero_pdf($totalFila); ?></td>
                                <td class="text-right">S/ <?php echo numero_pdf($saldoCronograma); ?></td>
                                <td class="text-center"><?php echo $badge; ?></td>
                            </tr>
                        <?php endforeach; ?>

                        <tr class="fila-total">
                            <td colspan="2" class="text-center">Totales</td>
                            <td class="text-right">S/ <?php echo numero_pdf($total_importe ?? 0); ?></td>
                            <td class="text-right">S/ <?php echo numero_pdf($total_interes ?? 0); ?></td>
                            <td class="text-right">S/ <?php echo numero_pdf($total_total ?? 0); ?></td>
                            <td class="text-right">S/ <?php echo numero_pdf($total_saldo ?? 0); ?></td>
                            <td class="text-center"><?php echo (int)$porcentajeCancelado; ?> %</td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No se encontraron cuotas registradas para este crédito.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="estado-box">
                <strong>Observaciones:</strong><br>
                <?php if ((int)$cuotas_vencidas > 0): ?>
                    Se registran <?php echo (int)$cuotas_vencidas; ?> cuotas vencidas pendientes de regularización.
                <?php else: ?>
                    Sin observaciones.
                <?php endif; ?>
            </div>

            <!-- <div class="observacion">
                <strong>Nota:</strong> El presente documento refleja la información vigente registrada en el sistema al momento de su emisión. Los montos, saldos, estados y fechas pueden modificarse por pagos posteriores, reprogramaciones o ajustes administrativos autorizados.
            </div> -->

            <div class="footer-info">
                <strong>Términos y condiciones</strong><br>
                • La información mostrada corresponde al cronograma vigente del crédito registrado.<br>
                • El presente formato se emite con fines de control, seguimiento y consulta administrativa.
            </div>

            <table class="signatures">
                <tr>
                    <td>
                        <div class="sign-line">Firma del responsable</div>
                    </td>
                    <td align="right">
                        <div class="sign-line" style="margin-left:auto;">Firma del cliente</div>
                    </td>
                </tr>
            </table>

            <div class="footer-band">
                <table class="footer-main">
                    <tr>
                        <td class="footer-left">
                            <strong>PhuyuSystem</strong> ☁️<br>
                            <span class="footer-small">Gracias por ser parte de la nube que nos impulsa</span>
                        </td>
                        <td class="footer-right">
                            <span class="footer-small">Generado digitalmente</span><br>
                            <strong>phuyusystem.com</strong>
                        </td>
                    </tr>
                </table>
            </div>

        </div>
    </div>
</body>
</html>
