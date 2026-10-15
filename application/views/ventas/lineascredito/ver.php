<?php
	$linea = (isset($info) && count($info)>0) ? $info[0] : array();
?>

<style>
#phuyu_form .phuyu-ver-card {
	border: 0;
	border-radius: 1rem;
	box-shadow: 0 10px 35px rgba(15, 23, 42, .06);
}

#phuyu_form .phuyu-ver-hero {
	position: relative;
	overflow: hidden;
	border: 0;
	border-radius: 1rem;
	background: linear-gradient(135deg, #405189 0%, #5569a8 100%);
	color: #fff;
	box-shadow: 0 14px 38px rgba(64, 81, 137, .22);
}

#phuyu_form .phuyu-ver-hero::after {
	content: "";
	position: absolute;
	right: -45px;
	top: -55px;
	width: 180px;
	height: 180px;
	border-radius: 999px;
	background: rgba(255, 255, 255, .08);
	pointer-events: none;
}

#phuyu_form .phuyu-hero-icon {
	width: 46px;
	height: 46px;
	border-radius: 14px;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	background: rgba(255, 255, 255, .14);
	font-size: 24px;
	flex: 0 0 auto;
}

#phuyu_form .phuyu-hero-content {
	position: relative;
	z-index: 2;
}

#phuyu_form .phuyu-hero-title {
	color: #fff;
	font-size: 1.25rem;
	line-height: 1.25;
	text-shadow: 0 1px 2px rgba(15, 23, 42, .18);
}

#phuyu_form .phuyu-hero-code {
	color: rgba(255, 255, 255, .85);
	font-weight: 800;
}

#phuyu_form .phuyu-hero-subtitle {
	color: rgba(255, 255, 255, .76);
}

#phuyu_form .phuyu-badge-soft {
	display: inline-flex;
	align-items: center;
	gap: 5px;
	border-radius: 999px;
	font-weight: 800;
	font-size: 12px;
	padding: 6px 10px;
}

#phuyu_form .phuyu-section-title {
	display: flex;
	align-items: center;
	gap: .55rem;
	font-weight: 800;
	color: #405189;
	margin-bottom: 1rem;
}

#phuyu_form .phuyu-section-title i {
	width: 34px;
	height: 34px;
	border-radius: 10px;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	background: rgba(64, 81, 137, .10);
}

#phuyu_form .phuyu-metric {
	border: 1px solid rgba(64, 81, 137, .10);
	border-radius: .9rem;
	padding: .85rem;
	background: #fff;
	height: 100%;
	min-height: 104px;
	display: flex;
	flex-direction: column;
	justify-content: center;
}

#phuyu_form .phuyu-metric-label {
	font-size: 11px;
	line-height: 1.25;
	font-weight: 800;
	text-transform: uppercase;
	color: #6c757d;
	margin-bottom: 8px;
}

#phuyu_form .phuyu-metric-value {
	font-weight: 800;
	font-size: 13px;
	line-height: 1.35;
	color: #212529;
	word-break: break-word;
}

#phuyu_form .phuyu-metric-main {
	font-size: 1.15rem;
	line-height: 1.15;
	color: #0ab39c;
	letter-spacing: -.02em;
	word-break: normal;
}

#phuyu_form .phuyu-metric-main small {
	display: inline-block;
	font-size: .95rem;
	line-height: 1;
	margin-right: 4px;
	color: #0ab39c;
}

#phuyu_form .phuyu-info-table th {
	width: 165px;
	font-size: 12px;
	font-weight: 800;
	text-transform: uppercase;
	color: #6c757d;
	white-space: nowrap;
}

#phuyu_form .phuyu-info-table td {
	font-size: 13px;
	font-weight: 600;
	color: #343a40;
}

#phuyu_form .phuyu-empty-alert {
	border-radius: 1rem;
}

#phuyu_form .phuyu-detail-stack {
	display: flex;
	flex-direction: column;
	gap: 1rem;
}

#phuyu_form .phuyu-cond-grid {
	display: grid;
	grid-template-columns: repeat(2, minmax(0, 1fr));
	gap: .85rem;
}

#phuyu_form .phuyu-cond-item {
	border: 1px solid rgba(64, 81, 137, .10);
	border-radius: .8rem;
	padding: .75rem;
	background: #fbfcfe;
}

#phuyu_form .phuyu-info-list {
	display: flex;
	flex-direction: column;
	gap: .75rem;
}

#phuyu_form .phuyu-info-item {
	display: grid;
	grid-template-columns: 135px 1fr;
	gap: .75rem;
	padding-bottom: .75rem;
	border-bottom: 1px dashed rgba(64, 81, 137, .14);
}

#phuyu_form .phuyu-info-item:last-child {
	border-bottom: 0;
	padding-bottom: 0;
}

#phuyu_form .phuyu-info-label {
	font-size: 11px;
	font-weight: 800;
	text-transform: uppercase;
	color: #6c757d;
}

#phuyu_form .phuyu-info-value {
	font-size: 13px;
	font-weight: 700;
	line-height: 1.35;
	color: #212529;
}

@media (max-width: 575.98px) {
	#phuyu_form .phuyu-hero-title {
		font-size: 1.05rem;
	}

	#phuyu_form .phuyu-metric {
		min-height: auto;
	}

	#phuyu_form .phuyu-cond-grid {
		grid-template-columns: 1fr;
	}

	#phuyu_form .phuyu-info-item {
		grid-template-columns: 1fr;
		gap: .25rem;
	}
}
</style>

<div id="phuyu_form">
	<?php if(count($linea)==0){ ?>
		<div class="alert alert-warning phuyu-empty-alert mb-0">
			<i class="bi bi-exclamation-triangle me-1"></i>
			No se encontró la línea de crédito seleccionada.
		</div>
	<?php }else{ ?>
		<?php
			$tipoPosesion = "Sin definir";
			if((int)$linea["tipoposesion"]==0){ $tipoPosesion = "Propia"; }
			if((int)$linea["tipoposesion"]==1){ $tipoPosesion = "Alquilada"; }
			if((int)$linea["tipoposesion"]==2){ $tipoPosesion = "Alquiler compra"; }

			$ubicacion = trim(
				($linea["departamento"] ? $linea["departamento"] : "") . " " .
				($linea["provincia"] ? "/ ".$linea["provincia"] : "") . " " .
				($linea["distrito"] ? "/ ".$linea["distrito"] : "") . " " .
				($linea["zona"] ? "/ ".$linea["zona"] : "")
			);
		?>

		<div class="card phuyu-ver-hero mb-3">
			<div class="card-body position-relative">
				<div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
					<div class="d-flex gap-3 align-items-start phuyu-hero-content">
						<div class="phuyu-hero-icon">
							<i class="bi bi-credit-card-2-front"></i>
						</div>

						<div>
							<div class="text-uppercase small fw-bold phuyu-hero-subtitle">Línea de crédito</div>
							<h4 class="mb-1 phuyu-hero-title">
								<span class="phuyu-hero-code">#<?php echo $linea["codlote"]; ?></span> - <?php echo $linea["socio"]; ?>
							</h4>
							<div class="phuyu-hero-subtitle">
								<i class="bi bi-file-earmark-text me-1"></i>
								<?php echo $linea["documento"]; ?>
							</div>
						</div>
					</div>

					<div class="d-flex flex-wrap gap-2 justify-content-end phuyu-hero-content">
						<?php if((int)$linea["estado"]==1){ ?>
							<span class="phuyu-badge-soft bg-success-subtle text-success border border-success-subtle">
								<i class="bi bi-check-circle"></i> Válida
							</span>
						<?php }else{ ?>
							<span class="phuyu-badge-soft bg-danger-subtle text-danger border border-danger-subtle">
								<i class="bi bi-x-circle"></i> Anulada
							</span>
						<?php } ?>

						<?php if(trim($linea["verificado"])=="1"){ ?>
							<span class="phuyu-badge-soft bg-info-subtle text-info border border-info-subtle">
								<i class="bi bi-patch-check"></i> Verificada
							</span>
						<?php }else{ ?>
							<span class="phuyu-badge-soft bg-warning-subtle text-warning border border-warning-subtle">
								<i class="bi bi-patch-exclamation"></i> Sin verificar
							</span>
						<?php } ?>

						<?php if((int)$linea["liquidado"]==1){ ?>
							<span class="phuyu-badge-soft bg-secondary-subtle text-secondary border border-secondary-subtle">
								<i class="bi bi-lock"></i> Liquidada
							</span>
						<?php }else{ ?>
							<span class="phuyu-badge-soft bg-primary-subtle text-primary border border-primary-subtle">
								<i class="bi bi-unlock"></i> Vigente
							</span>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>

		<div class="row g-3 mb-3">
			<div class="col-12 col-md-6 col-xl-3">
				<div class="phuyu-metric">
					<div class="phuyu-metric-label">Crédito máximo</div>
					<div class="phuyu-metric-value phuyu-metric-main">
						<small>S/.</small><?php echo number_format((float)$linea["creditomaximo"],2); ?>
					</div>
				</div>
			</div>

			<div class="col-12 col-md-6 col-xl-3">
				<div class="phuyu-metric">
					<div class="phuyu-metric-label">Tasa de interés</div>
					<div class="phuyu-metric-value fs-5 text-primary">
						<?php echo number_format((float)$linea["tasainteres"],2); ?>%
					</div>
				</div>
			</div>

			<div class="col-12 col-md-6 col-xl-3">
				<div class="phuyu-metric">
					<div class="phuyu-metric-label">Periodo</div>
					<div class="phuyu-metric-value">
						<?php echo $linea["fechainicio"]; ?> <span class="text-muted">a</span> <?php echo $linea["fechafin"]; ?>
					</div>
				</div>
			</div>

			<div class="col-12 col-md-6 col-xl-3">
				<div class="phuyu-metric">
					<div class="phuyu-metric-label">Tipo posesión</div>
					<div class="phuyu-metric-value">
						<?php echo $tipoPosesion; ?>
					</div>
				</div>
			</div>
		</div>

		<div class="phuyu-detail-stack">
			<div class="card phuyu-ver-card">
				<div class="card-body">
					<div class="phuyu-section-title">
						<i class="bi bi-cash-coin"></i>
						<span>Condiciones</span>
					</div>

					<div class="phuyu-cond-grid">
						<div class="phuyu-cond-item">
							<div class="phuyu-metric-label">Inicio</div>
							<div class="phuyu-metric-value"><?php echo $linea["fechainicio"]; ?></div>
						</div>

						<div class="phuyu-cond-item">
							<div class="phuyu-metric-label">Fin</div>
							<div class="phuyu-metric-value"><?php echo $linea["fechafin"]; ?></div>
						</div>

						<div class="phuyu-cond-item">
							<div class="phuyu-metric-label">Área</div>
							<div class="phuyu-metric-value"><?php echo number_format((float)$linea["area"],2); ?></div>
						</div>

						<div class="phuyu-cond-item">
							<div class="phuyu-metric-label">Comprado</div>
							<div class="phuyu-metric-value">
								<?php echo ((int)$linea["comprado"]==1) ? "Sí" : "No"; ?>
							</div>
						</div>

						<div class="phuyu-cond-item">
							<div class="phuyu-metric-label">Tipo posesión</div>
							<div class="phuyu-metric-value"><?php echo $tipoPosesion; ?></div>
						</div>
					</div>
				</div>
			</div>

			<div class="card phuyu-ver-card">
				<div class="card-body">
					<div class="phuyu-section-title">
						<i class="bi bi-person-lines-fill"></i>
						<span>Datos generales</span>
					</div>

					<div class="phuyu-info-list">
						<div class="phuyu-info-item">
							<div class="phuyu-info-label">Socio</div>
							<div class="phuyu-info-value"><?php echo $linea["socio"]; ?></div>
						</div>

						<div class="phuyu-info-item">
							<div class="phuyu-info-label">Garante</div>
							<div class="phuyu-info-value"><?php echo $linea["garante"]; ?></div>
						</div>

						<div class="phuyu-info-item">
							<div class="phuyu-info-label">Sectorista</div>
							<div class="phuyu-info-value"><?php echo ($linea["sectorista"]) ? $linea["sectorista"] : "-"; ?></div>
						</div>

						<div class="phuyu-info-item">
							<div class="phuyu-info-label">Descripción</div>
							<div class="phuyu-info-value"><?php echo ($linea["descripcion"]) ? $linea["descripcion"] : "-"; ?></div>
						</div>

						<div class="phuyu-info-item">
							<div class="phuyu-info-label">Dirección</div>
							<div class="phuyu-info-value"><?php echo ($linea["direccion"]) ? $linea["direccion"] : "-"; ?></div>
						</div>

						<div class="phuyu-info-item">
							<div class="phuyu-info-label">Ubicación</div>
							<div class="phuyu-info-value"><?php echo ($ubicacion) ? $ubicacion : "-"; ?></div>
						</div>

						<div class="phuyu-info-item">
							<div class="phuyu-info-label">Observaciones</div>
							<div class="phuyu-info-value"><?php echo ($linea["observaciones"]) ? $linea["observaciones"] : "-"; ?></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php } ?>
</div>
