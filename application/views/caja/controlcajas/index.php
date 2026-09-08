<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_datos" class="phuyu-velzon-list">
	<input type="hidden" id="estadocaja" value="<?php echo $_SESSION['phuyu_codcontroldiario'];?>">
	<input type="hidden" id="f_arqueo" value="<?php echo date('Y-m-d');?>">

	<div class="phuyu-page-title">
		<div class="phuyu-page-icon"><i class="bi bi-cash-coin"></i></div>
		<div>
			<div class="text-muted small text-uppercase fw-semibold">Tesoreria</div>
			<h4 class="mb-0 fw-bold"><?php echo $_SESSION["phuyu_caja"];?> al dia <?php echo date("d / m / Y");?></h4>
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb mb-0 mt-1">
					<li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
					<li class="breadcrumb-item active" aria-current="page">Control de caja</li>
				</ol>
			</nav>
		</div>
	</div>

	<div class="phuyu_body">
		<div class="card phuyu-card">
			<div class="card-body">
				<div class="phuyu-actions mb-3">
					<button type="button" class="btn btn-warning" v-on:click="pdf_arqueo_caja()">
						<i class="bi bi-printer me-1"></i> Arqueo actual
					</button>
					<button type="button" class="btn btn-success" v-on:click="pdf_arqueo_excel()">
						<i class="bi bi-download me-1"></i> Arqueo actual
					</button>
					<a href="<?php echo base_url();?>phuyu/w/caja/arqueos" class="btn btn-primary">
						<i class="bi bi-clock-history me-1"></i> Cierres anteriores
					</a>
					<button type="button" class="btn btn-danger" v-on:click="phuyu_cerrarcaja()">
						<i class="bi bi-lock me-1"></i> Cerrar caja actual
					</button>
					<a href="<?php echo base_url();?>phuyu/w/caja/precobranza" class="btn btn-info">
						<i class="bi bi-wallet2 me-1"></i> Pre cobranza
					</a>
				</div>

				<?php
					$sc_ingresos = $saldocaja["ingresos"]; $sc_egresos = $saldocaja["egresos"]; $sc_actual = $saldocaja["total"];
					$sb_ingresos = $saldobanco["ingresos"]; $sb_egresos = $saldobanco["egresos"]; $sb_actual = $saldobanco["total"];
				?>

				<div class="row g-3 mb-3">
					<div class="col-12 col-lg-4">
						<div class="alert alert-primary text-center h-100">
							<strong>Ingresos</strong>
							<h5><b>Caja:</b> S/. <?php echo round($sc_ingresos,2);?></h5>
							<h5><b>Banco:</b> S/. <?php echo round($sb_ingresos,2);?></h5>
						</div>
					</div>
					<div class="col-12 col-lg-4">
						<div class="alert alert-danger text-center h-100">
							<strong>Egresos</strong>
							<h5><b>Caja:</b> S/. <?php echo round($sc_egresos,2);?></h5>
							<h5><b>Banco:</b> S/. <?php echo round($sb_egresos,2);?></h5>
						</div>
					</div>
					<div class="col-12 col-lg-4">
						<div class="alert alert-success text-center h-100">
							<strong>Saldo actual</strong>
							<h5><b>Caja:</b> <b id="saldo_actual">S/. <?php echo round($sc_actual,2);?></b></h5>
							<h5><b>Banco:</b> S/. <?php echo round($sb_actual,2);?></h5>
						</div>
					</div>
				</div>

				<div class="phuyu-table-wrap">
					<table class="table table-hover table-striped align-middle">
						<thead>
							<tr>
								<th rowspan="2">Forma de pago</th>
								<th rowspan="2">Transacciones</th>
								<th colspan="3" class="text-center">Ingresos</th>
								<th colspan="3" class="text-center">Egresos</th>
								<th rowspan="2">Total</th>
							</tr>
							<tr>
								<th>Confirmado</th>
								<th>Pendiente</th>
								<th>Total</th>
								<th>Confirmado</th>
								<th>Pendiente</th>
								<th>Total</th>
							</tr>
						</thead>
						<tbody>
							<?php
								$item = 0; $total = 0; $neto = 0; $totalci=0; $totalpi = 0; $totali = 0; $totalce = 0; $totalpe =0; $totale = 0;
								foreach ($tipopagos as $key => $value) {
									$item = $item + 1;
									$totalci = $totalci + round($value["ingresosconfirmados"],2);
									$totalpi = $totalpi + round($value["ingresospendientes"],2);
									$totali = $totali + round($value["ingresos"],2);
									$totalce = $totalce + round($value["egresosconfirmados"],2);
									$totalpe = $totalpe + round($value["egresospendientes"],2);
									$totale = $totale + round($value["egresos"],2);
									$total = $total + round(($value["ingresos"] - $value["egresos"]),2);
									if ($item == 1) {
										$neto = round(($value["ingresos"] - $value["egresos"]),2);
									}
							?>
								<tr>
									<td><b><?php echo $value["descripcion"];?></b></td>
									<td><?php echo $value["transacciones"];?></td>
									<td><?php echo $value["ingresosconfirmados"];?></td>
									<td><?php echo $value["ingresospendientes"];?></td>
									<td><?php echo $value["ingresos"];?></td>
									<td><?php echo $value["egresosconfirmados"];?></td>
									<td><?php echo $value["egresospendientes"];?></td>
									<td><?php echo $value["egresos"];?></td>
									<td>S/. <?php echo round(($value["ingresos"] - $value["egresos"]),2);?></td>
								</tr>
							<?php } ?>
						</tbody>
						<tfoot>
							<tr>
								<th colspan="2">Total neto</th>
								<th>S/. <?php echo number_format($totalci,2);?></th>
								<th>S/. <?php echo number_format($totalpi,2);?></th>
								<th>S/. <?php echo number_format($totali,2);?></th>
								<th>S/. <?php echo number_format($totalce,2);?></th>
								<th>S/. <?php echo number_format($totalpe,2);?></th>
								<th>S/. <?php echo number_format($totale,2);?></th>
								<th>S/. <?php echo number_format($total,2);?></th>
							</tr>
							<tr>
								<th colspan="8">Total caja (solo transacciones en efectivo)</th>
								<?php
									$total = $neto + $caja[0]["saldoinicialcaja"];
									if ($total <= 0) {
										$color = "color:#d43f3a;font-size:20px;";
									}else{
										$color = "color:#06B8AC;font-size:20px;";
									}
								?>
								<th><b style="<?php echo $color;?>">S/. <?php echo number_format($total,2);?></b></th>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		</div>

		<div id="modal_reportes" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog modal-fullscreen-xxl-down">
				<div class="modal-content" align="center" style="border-radius:0px">
					<div class="modal-header">
						<h5 class="modal-title"><b><?php echo $_SESSION["phuyu_empresa"]." - ".$_SESSION["phuyu_sucursal"];?></b></h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body" id="reportes_modal" style="height:450px;padding:0px;">
						<iframe id="phuyu_pdf" src="" style="width:100%; height:100%; border:none;"> </iframe>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_caja/controlcaja.js"> </script>
