<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<style>
	.phuyu-company-profile .phuyu-company-logo {
		width: 112px;
		height: 112px;
		object-fit: cover;
		border-radius: 14px;
		border: 1px solid rgba(64, 81, 137, .16);
		background: #f3f6f9;
	}

	.phuyu-company-profile .phuyu-info-list {
		display: grid;
		grid-template-columns: repeat(2, minmax(0, 1fr));
		gap: .75rem;
		margin: 0;
		padding: 0;
		list-style: none;
	}

	.phuyu-company-profile .phuyu-info-item {
		display: flex;
		gap: .65rem;
		padding: .85rem;
		border: 1px solid rgba(64, 81, 137, .12);
		border-radius: .75rem;
		background: #f8f9fa;
		min-width: 0;
	}

	.phuyu-company-profile .phuyu-info-item i {
		color: #405189;
		font-size: 1.05rem;
		flex: 0 0 auto;
	}

	.phuyu-company-profile .phuyu-info-label {
		display: block;
		font-size: .68rem;
		font-weight: 800;
		letter-spacing: .04em;
		text-transform: uppercase;
		color: #878a99;
	}

	.phuyu-company-profile .phuyu-info-value {
		display: block;
		color: #212529;
		font-weight: 600;
		word-break: break-word;
	}

	.phuyu-company-profile .phuyu-status-card {
		border: 1px solid rgba(64, 81, 137, .12);
		border-radius: .75rem;
		padding: 1rem;
		background: #f8f9fa;
	}

	.phuyu-company-profile .phuyu-actions-note {
		color: #878a99;
		font-size: .82rem;
	}

	@media (max-width: 767.98px) {
		.phuyu-company-profile .phuyu-info-list {
			grid-template-columns: 1fr;
		}

		.phuyu-company-profile .phuyu-company-logo {
			width: 92px;
			height: 92px;
		}
	}
</style>

<div id="phuyu_datos" class="phuyu-velzon-list phuyu-company-profile">
	<input type="hidden" id="codempresa" value="<?php echo $_SESSION["phuyu_codempresa"];?>">

	<div class="phuyu-page-title">
		<span class="phuyu-page-icon"><i class="bi bi-building"></i></span>
		<div>
			<h4 class="mb-1">Empresa</h4>
			<p class="text-muted mb-0">Datos comerciales y configuración de facturación electrónica.</p>
		</div>
	</div>

	<div class="row g-3">
		<div class="col-12 col-xl-7">
			<div class="card phuyu-card h-100">
				<div class="card-body">
					<div class="d-flex flex-wrap gap-3 align-items-start mb-3">
						<img class="phuyu-company-logo" src="<?php echo base_url();?>public/img/empresa/<?php echo $info[0]['foto']?>" alt="Logo Empresa">
						<div class="flex-grow-1">
							<div class="d-flex flex-wrap align-items-center gap-2 mb-2">
								<h4 class="mb-0"><?php echo $info[0]["nombrecomercial"];?></h4>
								<span class="badge bg-light text-primary border">Facturación electrónica</span>
							</div>
							<div class="badge bg-light text-dark border mb-2">
								<i class="bi bi-card-text me-1"></i> RUC: <?php echo $info[0]["documento"];?>
							</div>
							<p class="text-muted mb-0"><?php echo $info[0]["direccion"];?></p>
						</div>
					</div>

					<ul class="phuyu-info-list">
						<li class="phuyu-info-item">
							<i class="bi bi-envelope"></i>
							<span>
								<span class="phuyu-info-label">Email</span>
								<span class="phuyu-info-value"><?php echo $info[0]["email"];?></span>
							</span>
						</li>
						<li class="phuyu-info-item">
							<i class="bi bi-telephone"></i>
							<span>
								<span class="phuyu-info-label">Telf./Cel.</span>
								<span class="phuyu-info-value"><?php echo $info[0]["telefono"];?></span>
							</span>
						</li>
						<li class="phuyu-info-item">
							<i class="bi bi-geo-alt"></i>
							<span>
								<span class="phuyu-info-label">Ubigeo</span>
								<span class="phuyu-info-value"><?php echo $empresa[0]["departamento"]."-".$empresa[0]["provincia"]."-".$empresa[0]["distrito"]." (".$empresa[0]["ubigeo"].")";?></span>
							</span>
						</li>
						<li class="phuyu-info-item">
							<i class="bi bi-shield-check"></i>
							<span>
								<span class="phuyu-info-label">Archivos PEM</span>
								<span class="phuyu-info-value"><?php echo $pen;?></span>
							</span>
						</li>
					</ul>
				</div>
			</div>
		</div>

		<div class="col-12 col-xl-5">
			<div class="card phuyu-card h-100">
				<div class="card-body">
					<h5 class="card-title mb-3">
						<i class="bi bi-receipt text-primary me-1"></i> Estado de emisión
					</h5>
					<div class="phuyu-status-card mb-3">
						<div class="d-flex flex-wrap gap-2">
							<?php if ($service[0]["sunatose"]==0) { ?>
								<span class="badge bg-light text-success border">
									<i class="bi bi-lightning-charge me-1"></i> Servicio: SUNAT
								</span>
							<?php } else { ?>
								<span class="badge bg-light text-success border">
									<i class="bi bi-bank me-1"></i> Servicio: OSE
								</span>
							<?php } ?>

							<?php if ($service[0]["serviceweb"]==0) { ?>
								<span class="badge bg-light text-primary border">
									<i class="bi bi-toggle-on me-1"></i> Estado: producción
								</span>
							<?php } else { ?>
								<span class="badge bg-light text-warning border">
									<i class="bi bi-hourglass-split me-1"></i> Estado: beta homologación
								</span>
							<?php } ?>
						</div>
					</div>

					<div class="d-grid gap-2">
						<button type="button" class="btn btn-primary" v-on:click="phuyu_editar()">
							<i class="bi bi-pencil-square me-1"></i> Configurar facturación
						</button>
						<button type="button" class="btn btn-outline-secondary" v-on:click="phuyu_copia()">
							<i class="bi bi-database me-1"></i> Copia de seguridad
						</button>
					</div>
				</div>
			</div>
		</div>

		<div class="col-12">
			<div class="card phuyu-card">
				<div class="card-body">
					<div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
						<div>
							<h5 class="card-title mb-1">
								<i class="bi bi-file-earmark-text text-primary me-1"></i> Datos de facturación
							</h5>
							<p class="phuyu-actions-note mb-0">Credenciales usadas para emisión y envío de comprobantes electrónicos.</p>
						</div>
						<a class="btn btn-outline-primary" download="<?php echo $service[0]['certificado_pfx'];?>" href="<?php echo base_url();?>sunat/certificado/<?php echo $service[0]['certificado_pfx'];?>">
							<i class="bi bi-cloud-arrow-down me-1"></i> Descargar certificado digital
						</a>
					</div>

					<ul class="phuyu-info-list">
						<li class="phuyu-info-item">
							<i class="bi bi-person"></i>
							<span>
								<span class="phuyu-info-label">Usuario SOL</span>
								<span class="phuyu-info-value"><?php echo $service[0]["usuariosol"];?></span>
							</span>
						</li>
						<li class="phuyu-info-item">
							<i class="bi bi-key"></i>
							<span>
								<span class="phuyu-info-label">Clave SOL</span>
								<span class="phuyu-info-value"><?php echo $service[0]["clavesol"];?></span>
							</span>
						</li>
						<li class="phuyu-info-item">
							<i class="bi bi-send"></i>
							<span>
								<span class="phuyu-info-label">Email envío</span>
								<span class="phuyu-info-value"><?php echo $service[0]["envioemail"];?></span>
							</span>
						</li>
						<li class="phuyu-info-item">
							<i class="bi bi-lock"></i>
							<span>
								<span class="phuyu-info-label">Email clave</span>
								<span class="phuyu-info-value"><?php echo $service[0]["claveemail"];?></span>
							</span>
						</li>
						<li class="phuyu-info-item">
							<i class="bi bi-patch-check"></i>
							<span>
								<span class="phuyu-info-label">Clave certificado</span>
								<span class="phuyu-info-value"><?php echo $service[0]["certificado_clave"];?></span>
							</span>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_empresa/index.js"></script>
