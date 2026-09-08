<style>
	#phuyu_operacion .phuyu-view-card {
		border: 1px solid rgba(64, 81, 137, .12);
		border-radius: 1rem;
		background: #fff;
		padding: 1rem;
	}

	#phuyu_operacion .phuyu-view-title {
		display: flex;
		align-items: center;
		gap: .65rem;
		color: #405189;
		font-weight: 800;
		margin-bottom: 1rem;
	}

	#phuyu_operacion .phuyu-view-title i {
		width: 38px;
		height: 38px;
		border-radius: 12px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		background: rgba(64, 81, 137, .1);
	}

	#phuyu_operacion .phuyu-info-grid {
		display: grid;
		grid-template-columns: repeat(2, minmax(0, 1fr));
		gap: .65rem;
	}

	#phuyu_operacion .phuyu-info-item {
		border: 1px solid #eef1f4;
		border-radius: 12px;
		padding: .65rem .75rem;
		background: #f8fafc;
	}

	#phuyu_operacion .phuyu-info-label {
		font-size: 10px;
		font-weight: 800;
		text-transform: uppercase;
		color: #878a99;
		margin-bottom: .2rem;
	}

	#phuyu_operacion .phuyu-table-wrapper {
		border: 1px solid #eef1f4;
		border-radius: 14px;
		overflow: auto;
	}

	#phuyu_operacion .phuyu-table {
		font-size: 12px;
		min-width: 760px;
		margin-bottom: 0;
	}

	#phuyu_operacion .phuyu-table thead th {
		background: #f3f6f9;
		font-weight: 800;
		text-transform: uppercase;
		white-space: nowrap;
		color: #343a40;
	}

	#phuyu_operacion .phuyu-total-banner {
		border-radius: 14px;
		background: rgba(10, 179, 156, .12);
		color: #087f6f;
		font-size: 22px;
		font-weight: 900;
		text-align: center;
		padding: .8rem 1rem;
	}

	@media (max-width: 767.98px) {
		#phuyu_operacion .phuyu-info-grid {
			grid-template-columns: 1fr;
		}
	}
</style>

<div id="phuyu_operacion">
	<div class="phuyu-view-card">
		<div class="phuyu-view-title">
			<i class="bi bi-receipt fs-5"></i>
			<div>
				<h5 class="mb-0">Compra 000<?php echo $info[0]["codkardex"];?></h5>
				<div class="text-muted small mt-1">
					<?php echo $info[0]["tipo"].': '.$info[0]["seriecomprobante"].'-'.$info[0]["nrocomprobante"];?>
				</div>
			</div>
		</div>

		<div class="phuyu-info-grid mb-3">
			<div class="phuyu-info-item">
				<div class="phuyu-info-label">Fecha compra</div>
				<div class="fw-semibold"><?php echo $info[0]["fechacomprobante"];?></div>
			</div>
			<div class="phuyu-info-item">
				<div class="phuyu-info-label">Fecha kardex</div>
				<div class="fw-semibold"><?php echo $info[0]["fechakardex"];?></div>
			</div>
			<div class="phuyu-info-item">
				<div class="phuyu-info-label">Documento</div>
				<div class="fw-semibold"><?php echo $info[0]["documento"];?></div>
			</div>
			<div class="phuyu-info-item">
				<div class="phuyu-info-label">Condición pago</div>
				<div class="fw-semibold"><?php echo $info[0]["pago"];?></div>
			</div>
			<div class="phuyu-info-item">
				<div class="phuyu-info-label">Proveedor</div>
				<div class="fw-semibold"><?php echo $info[0]["razonsocial"];?></div>
			</div>
			<div class="phuyu-info-item">
				<div class="phuyu-info-label">Movimiento</div>
				<div class="fw-semibold"><?php echo $info[0]["movimiento"];?></div>
			</div>
			<div class="phuyu-info-item">
				<div class="phuyu-info-label">Nombre comercial</div>
				<div class="fw-semibold"><?php echo $info[0]["nombrecomercial"];?></div>
			</div>
			<div class="phuyu-info-item">
				<div class="phuyu-info-label">Dirección</div>
				<div class="fw-semibold"><?php echo $info[0]["direccion"];?></div>
			</div>
		</div>

		<?php
			if ($info[0]["codmoneda"]!=1) {
				$simbolo = "$"; ?>
				<div class="alert alert-info py-2">
					<strong>Moneda:</strong> Dólar · <strong>Tipo cambio:</strong> <?php echo $info[0]["tipocambio"];?>
				</div>
			<?php }else{
				$simbolo = "S/.";
			}
		?>

		<h6 class="fw-bold text-primary mt-3 mb-2">
			<i class="bi bi-box-seam me-1"></i>
			Detalle de la compra
		</h6>
		<div class="phuyu-table-wrapper mb-3">
			<table class="table table-hover table-striped align-middle phuyu-table">
				<thead>
					<tr>
						<th>ID</th>
						<th>Código</th>
						<th>Producto</th>
						<th>Unidad</th>
						<th>Cantidad</th>
						<th>Precio</th>
						<th>Subtotal</th>
						<th style="width:70px;" class="text-center">Precios</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($detalle as $key => $value) { ?>
						<tr>
							<td><?php echo $value["codproducto"];?></td>
							<td><?php echo $value["codigo"];?></td>
							<td class="fw-semibold"><?php echo $value["producto"];?></td>
							<td><?php echo $value["unidad"];?></td>
							<td><?php echo round($value["cantidad"],3);?></td>
							<td><?php echo round($value["preciounitario"],4);?></td>
							<td><?php echo round($value["subtotal"],2);?></td>
							<td class="text-center">
								<button type="button" class="btn btn-success btn-sm" v-on:click="phuyu_masprecios(<?php echo $value["codproducto"].','."'".$value["producto"]."'".','.$value["preciosinigv"].','.$value["codunidad"].','.$value["igv"].','.$info[0]["tipocambio"].','.$info[0]["codmoneda"]; ?>)">
									<i class="bi bi-eye"></i>
								</button>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>

		<?php if (count($otros)>0) { ?>
			<h6 class="fw-bold text-primary mt-3 mb-2">
				<i class="bi bi-cash-coin me-1"></i>
				Otros gastos de la compra
			</h6>
			<div class="phuyu-table-wrapper mb-3">
				<table class="table table-sm table-hover align-middle mb-0">
					<tbody>
						<?php foreach ($otros as $key => $value) { ?>
							<tr>
								<td><?php echo $value["razonsocial"];?></td>
								<td class="text-end fw-bold"><?php echo number_format($value["importe"],2);?></td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
		<?php } ?>

		<h6 class="fw-bold text-primary mt-3 mb-2">
			<i class="bi bi-credit-card me-1"></i>
			Detalle de pagos
		</h6>
		<div class="phuyu-table-wrapper mb-3">
			<table class="table table-hover table-striped align-middle phuyu-table">
				<thead>
					<tr>
						<th>Tipo</th>
						<th>Entregado</th>
						<th>Importe</th>
						<th>Vuelto</th>
						<th>Nro doc</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($pagos as $key => $value) { ?>
						<tr>
							<td><?php echo $value["tipopago"];?></td>
							<td><?php echo round($value["importeentregado"],2);?></td>
							<td><?php echo round($value["importe"],2);?></td>
							<td><?php echo round($value["vuelto"],2);?></td>
							<td><?php echo $value["nrodocbanco"];?></td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>

		<div class="d-flex flex-wrap justify-content-center gap-2 mb-3">
			<span class="badge bg-danger-subtle text-danger fs-6">Descuentos: <?php echo $simbolo." ".number_format(round($info[0]["descuentos"],2) ,2);?></span>
			<span class="badge bg-success-subtle text-success fs-6">Valor compra: <?php echo $simbolo." ".number_format(round($info[0]["valorventa"],2) ,2);?></span>
			<span class="badge bg-warning-subtle text-warning fs-6">I.G.V: <?php echo $simbolo." ".number_format(round($info[0]["igv"],2) ,2);?></span>
			<span class="badge bg-warning-subtle text-warning fs-6">ICBPER: <?php echo $simbolo." ".number_format(round($info[0]["icbper"],2) ,2);?></span>
			<span class="badge bg-info-subtle text-info fs-6">Flete: <?php echo $simbolo." ".number_format(round($info[0]["flete"],2) ,2);?></span>
			<span class="badge bg-primary-subtle text-primary fs-6">Gastos: <?php echo $simbolo." ".number_format(round($info[0]["gastos"],2) ,2);?></span>
		</div>

		<div class="phuyu-total-banner">
			Total compra: <?php echo $simbolo." ".number_format(round($info[0]["importe"],2) ,2);?>
		</div>
	</div>

	<div id="modal_masprecios" data-bs-backdrop="static" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-xl">
			<div class="modal-content border-0">
				<div class="modal-header">
					<h5 class="modal-title">
						<i class="bi bi-tags me-1"></i>
						Actualización de precios | <span id="descripcionproducto"></span>
					</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</div>
				<div class="modal-body">
					<div class="phuyu_cargando" v-if="cargando">
						<div class="overlay-spinner"></div>
					</div>
					<div id="cuerpomasprecios"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_compras/ver.js"></script>
