<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_datos" class="phuyu-velzon-list">
	<input type="hidden" id="estadocaja" value="<?php echo $_SESSION['phuyu_codcontroldiario'];?>">
	<input type="hidden" id="saldarautomaticamente" value="<?php echo $automatico[0]['estado'];?>" name="">

	<div class="phuyu-page-title">
		<div class="phuyu-page-icon"><i class="bi bi-cash-coin"></i></div>
		<div>
			<div class="text-muted small text-uppercase fw-semibold">Tesoreria</div>
			<h4 class="mb-0 fw-bold">Caja cerrada</h4>
			<div class="text-muted"><?php echo $_SESSION["phuyu_caja"];?> al dia <?php echo date("d / m / Y");?></div>
		</div>
	</div>

	<div class="phuyu_body">
		<div class="card phuyu-card">
			<div class="card-body">
				<div class="text-center mb-4">
					<button type="button" class="btn btn-primary btn-lg" v-on:click="phuyu_aperturar()" v-bind:disabled="estado==1">
						<i class="bi bi-unlock me-1"></i> Aperturar caja actual
					</button>
				</div>

				<div class="row g-3">
					<div class="col-12 col-lg-4">
						<div class="card border h-100">
							<div class="card-body">
								<h5 class="fw-bold mb-3">Reporte de movimientos</h5>
								<div class="row g-3">
									<div class="col-12 col-md-6">
										<label class="form-label">Fecha desde</label>
										<input type="date" id="f_desde" class="form-control" autocomplete="off" value="<?php echo date('Y-m-d');?>">
									</div>
									<div class="col-12 col-md-6">
										<label class="form-label">Fecha hasta</label>
										<input type="date" id="f_hasta" class="form-control" autocomplete="off" value="<?php echo date('Y-m-d');?>">
									</div>
									<div class="col-12 text-center">
										<button type="button" class="btn btn-warning" v-on:click="pdf_movimientos()">
											<i class="bi bi-printer me-1"></i> Imprimir movimientos
										</button>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="col-12 col-lg-4">
						<div class="card border h-100">
							<div class="card-body">
								<h5 class="fw-bold mb-3">Reporte de arqueo</h5>
								<div class="row g-3">
									<div class="col-12">
										<label class="form-label">Fecha apertura</label>
										<input type="date" id="f_arqueo" class="form-control" autocomplete="off" value="<?php echo date('Y-m-d');?>">
									</div>
									<div class="col-12 text-center">
										<button type="button" class="btn btn-info" v-on:click="pdf_arqueo()">
											<i class="bi bi-printer me-1"></i> Imprimir arqueo
										</button>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="col-12 col-lg-4">
						<div class="alert alert-warning text-center">
							<strong>Saldo caja</strong>
							<h2 class="fw-bold mb-0">S/. <?php echo number_format(round($saldocaja["total"],2),2);?></h2>
						</div>
						<div class="alert alert-success text-center mb-0">
							<strong>Saldo banco</strong>
							<h2 class="fw-bold mb-0">S/. <?php echo number_format(round($saldobanco["total"],2),2);?></h2>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="modal_reportes" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-fullscreen-sm-down" style="margin:0px;">
			<div class="modal-content" align="center" style="border-radius:0px">
				<div class="modal-header">
					<h4 class="modal-title">
						<b><?php echo $_SESSION["phuyu_empresa"]." - ".$_SESSION["phuyu_sucursal"];?> </b>
					</h4>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</div>
				<div class="modal-body" id="reportes_modal" style="height:450px;padding:0px;">
					<iframe id="phuyu_pdf" src="" style="width:100%; height:100%; border:none;"> </iframe>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_caja/controlcaja.js"> </script>
