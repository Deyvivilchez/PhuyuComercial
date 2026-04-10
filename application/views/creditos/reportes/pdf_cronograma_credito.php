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
	<style>
		@page {
			margin: 14mm 12mm 15mm 12mm;
		}

		body {
			font-family: DejaVu Sans, sans-serif;
			font-size: 8.8pt;
			color: #1f2937;
			margin: 0;
			padding: 0;
		}

		.page {
			position: relative;
		}

		.watermark-wrap {
			position: fixed;
			top: 56%;
			left: 50%;
			width: 300px;
			height: 300px;
			margin-left: -150px;
			margin-top: -150px;
			z-index: -1;
			text-align: center;
		}

		.watermark-wrap img {
			width: 100%;
			height: auto;
			opacity: 0.11;
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

		.top-line {
			height: 4px;
			background: #1e40af;
			margin-bottom: 10px;
		}

		.header-box {
			margin-bottom: 12px;
		}

		.header-table td {
			vertical-align: top;
		}

		.header-left {
			width: 68%;
			padding-right: 10px;
		}

		.header-right {
			width: 32%;
			text-align: right;
		}

		.logo-box {
			margin-bottom: 4px;
		}

		.logo-box img {
			width: 70px;
			height: auto;
		}

		.empresa {
			font-size: 9.8pt;
			font-weight: bold;
			color: #0f172a;
			text-transform: uppercase;
			letter-spacing: 0.20px;
			line-height: 1.28;
			margin-bottom: 4px;
		}

		.doc-title {
			font-size: 17.5pt;
			font-weight: bold;
			color: #1e3a8a;
			margin: 0 0 6px 0;
			line-height: 1.05;
		}

		.doc-subtitle {
			font-size: 8.2pt;
			color: #475569;
			line-height: 1.65;
			text-align: justify;
		}

		.credit-box {
			border: 1px solid #dbe2ea;
			padding: 10px 11px;
			display: inline-block;
			min-width: 220px;
			text-align: left;
			background: #ffffff;
		}

		.credit-box-title {
			font-size: 11.2pt;
			font-weight: bold;
			color: #1e3a8a;
			margin-bottom: 6px;
		}

		.credit-box-row {
			font-size: 8.1pt;
			line-height: 1.75;
			color: #334155;
		}

		.estado-chip {
			display: inline-block;
			margin-top: 5px;
			padding: 3px 8px;
			border-radius: 10px;
			font-size: 6.8pt;
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
			margin-top: 12px;
			margin-bottom: 6px;
			font-size: 8.6pt;
			font-weight: bold;
			text-transform: uppercase;
			letter-spacing: 0.35px;
			color: #1e3a8a;
			border-bottom: 1px solid #93c5fd;
			padding-bottom: 3px;
		}

		.info-box {
			border: 1px solid #dbe2ea;
			padding: 8px 10px;
			margin-bottom: 10px;
			background: #ffffff;
		}

		.estado-box {
			border: 1px solid #dbe2ea;
			padding: 8px 10px;
			font-size: 8pt;
			color: #475569;
			margin-bottom: 10px;
			background: #ffffff;
			line-height: 1.6;
		}

		.observacion {
			margin-top: 10px;
			border: 1px solid #dbe2ea;
			padding: 9px 10px;
			font-size: 8pt;
			color: #475569;
			line-height: 1.65;
			background: #ffffff;
		}

		.info-table td {
			padding: 4px 5px;
			font-size: 8.4pt;
			vertical-align: top;
		}

		.info-label {
			width: 16%;
			font-weight: bold;
			color: #475569;
		}

		.info-value {
			width: 34%;
			color: #111827;
		}

		.estado-box {
			border: 1px solid #dbe2ea;
			padding: 8px 10px;
			font-size: 8pt;
			color: #475569;
			margin-bottom: 10px;
			background: #ffffff;
			line-height: 1.6;
		}

		.cronograma {
			margin-top: 4px;
			table-layout: fixed;
		}

		.cronograma thead th {
			background: #1e40af;
			color: #ffffff;
			font-size: 7.7pt;
			padding: 7px 4px;
			border: 1px solid #dbe2ea;
			text-transform: uppercase;
			line-height: 1.2;
		}

		.cronograma tbody td {
			font-size: 8pt;
			padding: 6px 4px;
			border: 1px solid #dbe2ea;
			color: #1f2937;
			line-height: 1.25;
			vertical-align: middle;
			word-wrap: break-word;
		}

		.fila-par td {
			background: rgba(252, 253, 255, 0.68);
		}

		.fila-pagada td {
			background: rgba(240, 253, 244, 0.58);
		}

		.fila-vencida td {
			background: rgba(255, 247, 237, 0.58);
		}

		.fila-total td {
			background: rgba(248, 250, 252, 0.72);
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
			border: 1px solid #dbe2ea;
			margin-top: 12px;
			margin-bottom: 12px;
			background: #ffffff;
		}

		.resumen-table th {
			background: #f8fafc;
			color: #475569;
			font-size: 7.6pt;
			font-weight: bold;
			text-transform: uppercase;
			padding: 8px 6px;
			border: 1px solid #dbe2ea;
		}

		.resumen-table td {
			font-size: 9.8pt;
			font-weight: bold;
			color: #0f172a;
			text-align: center;
			padding: 8px 6px;
			border: 1px solid #dbe2ea;
		}

		.observacion {
			margin-top: 10px;
			border: 1px solid #dbe2ea;
			padding: 9px 10px;
			font-size: 8pt;
			color: #475569;
			line-height: 1.65;
			background: #ffffff;
		}

		.footer-info {
			margin-top: 12px;
			font-size: 7.8pt;
			color: #64748b;
			line-height: 1.65;
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
			border-top: 1px solid #94a3b8;
			text-align: center;
			font-size: 8pt;
			color: #475569;
		}

		.footer-mini {
			margin-top: 12px;
			font-size: 7.3pt;
			color: #94a3b8;
			text-align: right;
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

		<div class="top-line"></div>

		<div class="header-box">
			<table class="header-table">
				<tr>
					<td class="header-left">
						<div class="logo-box">
							<?php if (!empty($logoSrc)): ?>
								<img src="<?php echo $logoSrc; ?>">
							<?php endif; ?>
						</div>
						<div class="empresa"><?php echo texto_pdf($nombreEmpresa); ?></div>
						<div class="doc-title">Cronograma de crédito</div>
						<div class="doc-subtitle">
							Documento financiero que presenta el detalle del crédito, el cronograma
							de cuotas y el saldo pendiente correspondiente al crédito del cliente
							<strong><?php echo texto_pdf($nombreCliente); ?></strong>.
						</div>
					</td>
					<td class="header-right">
						<div class="credit-box">
							<div class="credit-box-title">Boleta de Crédito</div>
							<div class="credit-box-row"><strong>Emisión:</strong> <?php echo $fechaEmision; ?> &nbsp; <?php echo $horaEmision; ?></div>
							<div class="credit-box-row"><strong>Código:</strong> CR-<?php echo str_pad((string)$codcredito, 6, '0', STR_PAD_LEFT); ?></div>
							<div class="credit-box-row"><strong>Estado:</strong>
								<span class="estado-chip <?php echo $cuotas_pendientes > 0 ? 'estado-chip-pendiente' : 'estado-chip-pagado'; ?>">
									<?php echo $cuotas_pendientes > 0 ? 'Pendiente' : 'Pagado'; ?>
								</span>
							</div>
						</div>
					</td>
				</tr>
			</table>
		</div>

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

		<div class="estado-box" style="margin-top: 10px;">
			<strong>Observaciones:</strong><br>
			<?php if ((int)$cuotas_vencidas > 0): ?>
				Se registran <?php echo (int)$cuotas_vencidas; ?> cuotas vencidas pendientes de regularización.
			<?php else: ?>
				Sin observaciones.
			<?php endif; ?>
		</div>

		<div class="observacion">
			<strong>Nota:</strong> El presente documento refleja la información vigente registrada en el sistema al momento de su emisión. Los montos, saldos, estados y fechas pueden modificarse por pagos posteriores, reprogramaciones o ajustes administrativos autorizados.
		</div>

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

		<div class="footer-mini">
			<?php echo texto_pdf($nombreEmpresa); ?> · CR-<?php echo str_pad((string)$codcredito, 6, '0', STR_PAD_LEFT); ?>
		</div>

	</div>
</body>

</html>