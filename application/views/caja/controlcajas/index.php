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
						<div class="h-100 border rounded-2 p-3" style="background:#f5f8ff;border-color:#cbd7ff !important;">
							<div class="d-flex align-items-center gap-3">
								<div class="d-flex align-items-center justify-content-center rounded-circle" style="width:42px;height:42px;background:#e6edff;color:#3f5fa8;">
									<i class="bi bi-arrow-down-circle fs-4"></i>
								</div>
								<div class="flex-grow-1">
									<div class="text-uppercase small fw-semibold" style="color:#3f5fa8;">Ingresos</div>
									<div class="d-flex justify-content-between align-items-center mt-1">
										<span class="text-muted">Caja</span>
										<strong>S/. <?php echo number_format($sc_ingresos,2);?></strong>
									</div>
									<div class="d-flex justify-content-between align-items-center">
										<span class="text-muted">Banco</span>
										<strong>S/. <?php echo number_format($sb_ingresos,2);?></strong>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-12 col-lg-4">
						<div class="h-100 border rounded-2 p-3" style="background:#fff5f5;border-color:#ffc9c9 !important;">
							<div class="d-flex align-items-center gap-3">
								<div class="d-flex align-items-center justify-content-center rounded-circle" style="width:42px;height:42px;background:#ffe3e3;color:#c92a2a;">
									<i class="bi bi-arrow-up-circle fs-4"></i>
								</div>
								<div class="flex-grow-1">
									<div class="text-uppercase small fw-semibold" style="color:#c92a2a;">Egresos</div>
									<div class="d-flex justify-content-between align-items-center mt-1">
										<span class="text-muted">Caja</span>
										<strong>S/. <?php echo number_format($sc_egresos,2);?></strong>
									</div>
									<div class="d-flex justify-content-between align-items-center">
										<span class="text-muted">Banco</span>
										<strong>S/. <?php echo number_format($sb_egresos,2);?></strong>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-12 col-lg-4">
						<div class="h-100 border rounded-2 p-3" style="background:#f2fffd;border-color:#b8efea !important;">
							<div class="d-flex align-items-center gap-3">
								<div class="d-flex align-items-center justify-content-center rounded-circle" style="width:42px;height:42px;background:#d9fbf7;color:#009688;">
									<i class="bi bi-wallet2 fs-4"></i>
								</div>
								<div class="flex-grow-1">
									<div class="text-uppercase small fw-semibold" style="color:#009688;">Saldo actual</div>
									<div class="d-flex justify-content-between align-items-center mt-1">
										<span class="text-muted">Caja</span>
										<strong id="saldo_actual" style="color:#009688;">S/. <?php echo number_format($sc_actual,2);?></strong>
									</div>
									<div class="d-flex justify-content-between align-items-center">
										<span class="text-muted">Banco</span>
										<strong>S/. <?php echo number_format($sb_actual,2);?></strong>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="phuyu-table-wrap">
					<table class="table table-hover table-striped align-middle">
						<thead>
							<tr>
								<th rowspan="2" style="width:145px;">Forma de pago</th>
								<th rowspan="2" class="text-center" style="width:90px;">Trans.</th>
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
								$item = 0; $total = 0; $neto_efectivo = 0; $totaltransacciones = 0; $totalci=0; $totalpi = 0; $totali = 0; $totalce = 0; $totalpe =0; $totale = 0;
								foreach ($tipopagos as $key => $value) {
									$item = $item + 1;
									$totaltransacciones = $totaltransacciones + (int)$value["transacciones"];
									$totalci = $totalci + round($value["ingresosconfirmados"],2);
									$totalpi = $totalpi + round($value["ingresospendientes"],2);
									$totali = $totali + round($value["ingresos"],2);
									$totalce = $totalce + round($value["egresosconfirmados"],2);
									$totalpe = $totalpe + round($value["egresospendientes"],2);
									$totale = $totale + round($value["egresos"],2);
									$total = $total + round(($value["ingresos"] - $value["egresos"]),2);
									$neto_fila = round(($value["ingresos"] - $value["egresos"]),2);
									if (strtoupper(trim($value["descripcion"])) == "EFECTIVO") {
										$neto_efectivo = $neto_fila;
									}
									$color_neto = ($neto_fila < 0) ? "text-danger" : "text-success";
									$fondo_ingreso = "background:#f3fbf7;color:#0f5132;";
									$fondo_egreso = "background:#fff5f5;color:#842029;";
							?>
								<tr>
									<td>
										<button type="button" class="btn btn-link p-0 text-decoration-none fw-bold" v-on:click='ver_tipopago(<?php echo (int)$value["codtipopago"];?>, <?php echo json_encode($value["descripcion"]);?>)'>
											<?php echo $value["descripcion"];?>
										</button>
									</td>
									<td class="text-center"><?php echo $value["transacciones"];?></td>
									<td style="<?php echo $fondo_ingreso;?>" class="fw-semibold">S/. <?php echo number_format($value["ingresosconfirmados"],2);?></td>
									<td style="<?php echo $fondo_ingreso;?>">S/. <?php echo number_format($value["ingresospendientes"],2);?></td>
									<td style="<?php echo $fondo_ingreso;?>" class="fw-bold">S/. <?php echo number_format($value["ingresos"],2);?></td>
									<td style="<?php echo $fondo_egreso;?>" class="fw-semibold">S/. <?php echo number_format($value["egresosconfirmados"],2);?></td>
									<td style="<?php echo $fondo_egreso;?>">S/. <?php echo number_format($value["egresospendientes"],2);?></td>
									<td style="<?php echo $fondo_egreso;?>" class="fw-bold">S/. <?php echo number_format($value["egresos"],2);?></td>
									<td><b class="<?php echo $color_neto;?>">S/. <?php echo number_format($neto_fila,2);?></b></td>
								</tr>
							<?php } ?>
						</tbody>
						<tfoot>
							<tr>
								<th>Total neto</th>
								<th class="text-center"><?php echo $totaltransacciones; ?></th>
								<th style="background:#f3fbf7;color:#0f5132;">S/. <?php echo number_format($totalci,2);?></th>
								<th style="background:#f3fbf7;color:#0f5132;">S/. <?php echo number_format($totalpi,2);?></th>
								<th style="background:#f3fbf7;color:#0f5132;">S/. <?php echo number_format($totali,2);?></th>
								<th style="background:#fff5f5;color:#842029;">S/. <?php echo number_format($totalce,2);?></th>
								<th style="background:#fff5f5;color:#842029;">S/. <?php echo number_format($totalpe,2);?></th>
								<th style="background:#fff5f5;color:#842029;">S/. <?php echo number_format($totale,2);?></th>
								<th>S/. <?php echo number_format($total,2);?></th>
							</tr>
							<tr>
								<?php
									$color_efectivo = ($neto_efectivo < 0) ? "#d43f3a" : "#06B8AC";
									$total = $neto_efectivo + $caja[0]["saldoinicialcaja"];
									$color = ($total <= 0) ? "#d43f3a" : "#06B8AC";
								?>
								<th colspan="9" style="background:#fbfcfd;">
									<div class="d-flex flex-wrap justify-content-end gap-2 py-2">
										<div class="border rounded-2 px-3 py-2 bg-white" style="min-width:170px;">
											<div class="text-muted small fw-semibold text-uppercase">Saldo inicial</div>
											<div class="fw-bold">S/. <?php echo number_format($caja[0]["saldoinicialcaja"],2);?></div>
										</div>
										<div class="border rounded-2 px-3 py-2 bg-white" style="min-width:170px;">
											<div class="text-muted small fw-semibold text-uppercase">Efectivo</div>
											<div class="fw-bold" style="color:<?php echo $color_efectivo;?>;">S/. <?php echo number_format($neto_efectivo,2);?></div>
										</div>
										<div class="border rounded-2 px-3 py-2" style="min-width:190px;background:#f8fffe;border-color:#b9efea !important;">
											<div class="text-muted small fw-semibold text-uppercase">Total</div>
											<div class="fw-bold" style="font-size:20px;color:<?php echo $color;?>;">S/. <?php echo number_format($total,2);?></div>
										</div>
									</div>
								</th>
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

		<div id="modal_tipopago_detalle" class="modal fade" tabindex="-1" aria-hidden="true">
			<div class="modal-dialog modal-xl modal-dialog-scrollable">
				<div class="modal-content border-0 shadow-lg">
					<div class="modal-header bg-light">
						<div>
							<div class="text-muted small text-uppercase fw-semibold">Detalle por forma de pago</div>
							<h5 class="modal-title mb-0 fw-bold">
								Movimientos - {{ detalle_tipopago.tipopago }}
							</h5>
						</div>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<div class="phuyu_cargando my-4" v-if="detalle_tipopago.cargando">
							<div class="overlay-spinner"></div>
						</div>
						<div v-if="!detalle_tipopago.cargando">
							<div class="row g-2 mb-3">
								<div class="col-12 col-md-4">
									<div class="border rounded-2 p-2 bg-light h-100">
										<div class="text-muted small">Ingresos</div>
										<div class="fs-5 fw-bold text-success">S/. {{ moneda(detalle_tipopago.totales.ingresos) }}</div>
									</div>
								</div>
								<div class="col-12 col-md-4">
									<div class="border rounded-2 p-2 bg-light h-100">
										<div class="text-muted small">Egresos</div>
										<div class="fs-5 fw-bold text-danger">S/. {{ moneda(detalle_tipopago.totales.egresos) }}</div>
									</div>
								</div>
								<div class="col-12 col-md-4">
									<div class="border rounded-2 p-2 bg-light h-100">
										<div class="text-muted small">Neto</div>
										<div class="fs-5 fw-bold" v-bind:class="detalle_tipopago.totales.neto < 0 ? 'text-danger' : 'text-success'">
											S/. {{ moneda(detalle_tipopago.totales.neto) }}
										</div>
									</div>
								</div>
							</div>
							<div class="table-responsive">
								<table class="table table-sm table-hover align-middle mb-0">
									<thead class="table-light">
										<tr>
											<th style="width:110px;">Fecha / hora</th>
											<th>Recibo</th>
											<th>Concepto</th>
											<th>Razón social</th>
											<th>Referencia</th>
											<th class="text-center">Tipo</th>
											<th class="text-center">Estado</th>
											<th class="text-end">Importe</th>
										</tr>
									</thead>
									<tbody>
										<tr v-if="detalle_tipopago.lista.length == 0">
											<td colspan="8" class="text-center text-muted py-4">Sin movimientos para esta forma de pago.</td>
										</tr>
										<tr v-for="item in detalle_tipopago.lista" v-bind:style="item.tipomovimiento == 2 ? 'background:#fff5f5;' : 'background:#f8fffb;'">
											<td>
												<div class="fw-semibold">{{ item.fechamovimiento }}</div>
												<div class="small text-muted">{{ item.horamovimiento || '--:--:--' }}</div>
											</td>
											<td><span class="fw-semibold">{{ item.recibo }}</span></td>
											<td>{{ item.concepto }}</td>
											<td style="max-width:220px;">
												<div class="text-truncate" v-bind:title="item.razonsocial">
													{{ item.razonsocial }}
												</div>
											</td>
											<td>
												<div v-if="item.referencia">{{ item.referencia }}</div>
												<div v-if="!item.referencia" class="text-muted">-</div>
												<div
													v-if="item.referencia && item.documento_ref && item.referencia.toUpperCase().trim() == 'INGRESO POR VENTA'"
													class="small text-primary fw-semibold mt-1"
												>
													Comprobante: {{ item.documento_ref }}
												</div>
											</td>
											<td class="text-center">
												<span class="badge bg-success-subtle text-success border border-success-subtle" v-if="item.tipomovimiento == 1">Ingreso</span>
												<span class="badge bg-danger" v-if="item.tipomovimiento == 2">Egreso</span>
											</td>
											<td class="text-center">
												<span class="badge bg-success" v-if="item.cobrado == 1">Confirmado</span>
												<span class="badge bg-secondary" v-if="item.cobrado == 0">Pendiente</span>
											</td>
											<td class="text-end fw-bold" v-bind:class="item.tipomovimiento == 2 ? 'text-danger' : 'text-success'">
												S/. {{ moneda(item.importe_r) }}
											</td>
										</tr>
									</tbody>
									<tfoot>
										<tr class="table-light">
											<th colspan="5">Totales</th>
											<th class="text-end text-success">Ing.: S/. {{ moneda(detalle_tipopago.totales.ingresos) }}</th>
											<th class="text-end text-danger">Egr.: S/. {{ moneda(detalle_tipopago.totales.egresos) }}</th>
											<th class="text-end">Neto: S/. {{ moneda(detalle_tipopago.totales.neto) }}</th>
										</tr>
									</tfoot>
								</table>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_caja/controlcaja.js"> </script>
