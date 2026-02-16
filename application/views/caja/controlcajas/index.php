<div id="phuyu_datos">
	<div class="row">
		<div class="col-12 col-md-6">
            <input type="hidden" id="estadocaja" value="<?php echo $_SESSION['phuyu_codcontroldiario'];?>">
			<h2><?php echo $_SESSION["phuyu_caja"];?> AL DIA <?php echo date("d / m / Y");?></h2> 
        </div>
    </div>
	<div class="phuyu_body">
		<div class="card">
			<div class="card-body">
				<div class="row">
					<input type="hidden" id="f_arqueo" value="<?php echo date('Y-m-d');?>">
					<div class="col-md-12">
						<button type="button" class="btn btn-warning btn-icon" v-on:click="pdf_arqueo_caja()">
							<b><i data-acorn-icon="print"></i> ARQUEO ACTUAL</b>
						</button>
						<button type="button" class="btn btn-success btn-icon" v-on:click="pdf_arqueo_excel()">
							<b><i data-acorn-icon="download"></i> ARQUEO ACTUAL</b>
						</button>
						<a href="<?php echo base_url();?>phuyu/w/caja/arqueos" class="btn btn-primary btn-icon">
							<b><i data-acorn-icon="arrow-right"></i> CIERRES DE CAJA ANTERIORES</b>
						</a>
						<button type="button" class="btn btn-danger btn-icon" v-on:click="phuyu_cerrarcaja()">
							<b><i data-acorn-icon="close-circle"></i> CERRAR CAJA ACTUAL</b>
						</button>
						<a href="<?php echo base_url();?>phuyu/w/caja/precobranza" class="btn btn-info btn-icon">
							<b><i data-acorn-icon="money"></i> PRE COBRANZA</b>
						</a>
					</div>
				</div><br>
				<div class="row">
					<?php
						$sc_ingresos = 0; $sc_egresos = 0; $sc_actual = 0; $sb_ingresos = 0; $sb_egresos = 0; $sb_actual = 0;
						$sc_ingresos = $saldocaja["ingresos"]; $sc_egresos = $saldocaja["egresos"]; $sc_actual = $saldocaja["total"];
						$sb_ingresos = $saldobanco["ingresos"]; $sb_egresos = $saldobanco["egresos"]; $sb_actual = $saldobanco["total"];
					?>

					<div class="col-md-4 col-xs-12">
						<div class="alert alert-primary text-center phuyu_caja_alert" role="alert">
							<strong>INGRESOS </strong>
							<h5> <b>CAJA:</b>  S/. <?php echo round($sc_ingresos,2);?> </h5>
							<h5> <b>BANCO:</b>  S/. <?php echo round($sb_ingresos,2);?> </h5>
						</div>
					</div>
					<div class="col-md-4 col-xs-12">
						<div class="alert alert-danger text-center phuyu_caja_alert" role="alert">
							<strong>EGRESOS</strong>
							<h5> <b>CAJA:</b>  S/. <?php echo round($sc_egresos,2);?> </h5>
							<h5> <b>BANCO:</b>  S/. <?php echo round($sb_egresos,2);?> </h5>
						</div>
					</div>
					<div class="col-md-4 col-xs-12">
						<div class="alert alert-success text-center" role="alert">
							<strong>SALDO&nbsp;ACTUAL </strong>
							<h5> <b>CAJA:</b>  <b id="saldo_actual">S/. <?php echo round($sc_actual,2);?></b> </h5>
							<h5> <b>BANCO:</b>  S/. <?php echo round($sb_actual,2);?> </h5>
						</div>
					</div>
				</div>
					<div class="row">
						<div class="col-xs-12">
			              	<div class="data-table-responsive-wrapper">
				                <table class="table table-striped table-bordered" style="margin-bottom:0px !important;font-size: 11px !important">
									<thead>
										<tr>
											<th rows="2">FORMA DE PAGO</th>
											<th rows="2">TRANSACCIONES</th>
											<th colspan="3" style="text-align: center">INGRESOS</th>
											<th colspan="3" style="text-align: center">EGRESOS</th>
											<th rows="2">TOTAL</th>
										</tr>
										<tr>
											<th colspan="2"></th>
											<th>CONFIRMADO</th>
											<th>PENDIENTE</th>
											<th>TOTAL</th>
											<th>CONFIRMADO</th>
											<th>PENDIENTE</th>
											<th>TOTAL</th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										<?php $item = 0; $total = 0; $neto = 0; $totalci=0; $totalpi = 0; $totali = 0; $totalce = 0; $totalpe =0; $totale = 0;
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
												} ?>
												<tr>
													<td style="font-size: 11px"> <b><?php echo $value["descripcion"];?></b> </td>
													<td style="font-size: 11px"><?php echo $value["transacciones"];?></td>
													<td style="font-size: 11px"><?php echo $value["ingresosconfirmados"];?></td>
													<td style="font-size: 11px"><?php echo $value["ingresospendientes"];?></td>
													<td style="font-size: 11px"><?php echo $value["ingresos"];?></td>
													<td style="font-size: 11px"><?php echo $value["egresosconfirmados"];?></td>
													<td style="font-size: 11px"><?php echo $value["egresospendientes"];?></td>
													<td style="font-size: 11px"><?php echo $value["egresos"];?></td>
													<td style="font-size: 11px">S/. <?php echo round(($value["ingresos"] - $value["egresos"]),2);?></td>
												</tr>
											<?php }
										?>
									</tbody>
									<tbody>
										<tr>
											<th colspan="2">TOTAL NETO</th>
											<th>S/. <?php echo number_format($totalci,2);?></th>
											<th>S/. <?php echo number_format($totalpi,2);?></th>
											<th>S/. <?php echo number_format($totali,2);?></th>
											<th>S/. <?php echo number_format($totalce,2);?></th>
											<th>S/. <?php echo number_format($totalpe,2);?></th>
											<th>S/. <?php echo number_format($totale,2);?></th>
											<th>S/. <?php echo number_format($total,2);?></th>
										</tr>
										<tr>
											<th colspan="8">TOTAL CAJA (SOLO TRANSACCIONES EN EFECTIVO)</th>
											<?php
												$total = $neto + $caja[0]["saldoinicialcaja"];
												if ($total <= 0) {
													$color = "color:#d43f3a;font-size:20px;";
												}else{
													$color = "color:#06B8AC;font-size:20px;";
												}
											?>
											<th> <b style="<?php echo $color;?>">S/. <?php echo number_format($total,2);?></b> </th>
										</tr>
									</tbody>
				                </table>
				            </div>
				        </div>
					</div>
			    </div>
			</div>

		    <div id="modal_reportes" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
				<div class="modal-dialog modal-fullscreen-xxl-down">
					<div class="modal-content" align="center" style="border-radius:0px">
						<div class="modal-header modal-phuyu-titulo">
							<h5 class="modal-title"> <b><?php echo $_SESSION["phuyu_empresa"]." - ".$_SESSION["phuyu_sucursal"];?></b> </h5>
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
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_caja/controlcaja.js"> </script>
<script>
	if (typeof AcornIcons !== 'undefined') {
      new AcornIcons().replace();
    }
    if (typeof Icons !== 'undefined') {
      const icons = new Icons();
    }
</script>