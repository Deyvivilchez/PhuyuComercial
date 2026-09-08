<style>
.drag-scroll {
	overflow-x: auto;
	cursor: grab;
	user-select: none;
}

.drag-scroll.active {
	cursor: grabbing;
}

.drag-scroll table {
	min-width: 1100px;
}
</style>

<div id="phuyu_form">
	<div class="card border-0 shadow-sm">
		<div class="card-body px-4 py-3">

			<!-- INFO -->
			<div class="row g-3 mb-3">

				<div class="col-md-7">
					<div class="border rounded-3 p-3 h-100">

						<h6 class="mb-2">
							<span class="badge bg-primary-subtle text-primary">
								PEDIDO: 000<?php echo $info[0]["codpedido"]; ?>
							</span>
							<span class="ms-2 text-muted">
								<?php echo $info[0]["fechapedido"]; ?>
							</span>
						</h6>

						<p class="mb-1"><strong>Comprobante:</strong>
							<?php echo $info[0]["tipo"].': '.$info[0]["seriecomprobante"].'-'.$info[0]["nrocomprobante"]; ?>
						</p>

						<p class="mb-1"><strong>Documento:</strong>
							<?php echo $info[0]["documento"]; ?>
						</p>

						<p class="mb-1"><strong>Cliente:</strong>
							<?php echo $info[0]["cliente"]; ?>
						</p>

						<p class="mb-0"><strong>Dirección:</strong>
							<?php echo $info[0]["direccion"]; ?>
						</p>

					</div>
				</div>

				<div class="col-md-5">
					<div class="border rounded-3 p-3 h-100 bg-light-subtle">

						<p class="mb-2">
							<strong>Condición pago:</strong><br>
							<span class="text-muted"><?php echo $info[0]["pago"]; ?></span>
						</p>

						<p class="mb-2">
							<strong>Estado:</strong><br>
							<span class="badge bg-success-subtle text-success">
								<?php echo $info[0]["proceso"]; ?>
							</span>
						</p>

						<p class="mb-0">
							<strong>Vendedor:</strong><br>
							<span class="text-muted"><?php echo $info[0]["vendedor"]; ?></span>
						</p>

					</div>
				</div>

			</div>

			<!-- TABLA -->
			<div class="table-responsive border rounded-3 drag-scroll">
				<table class="table table-sm table-hover align-middle mb-0">
					<thead class="table-light">
						<tr>
							<th>ID</th>
							<th>CODIGO</th>
							<th>PRODUCTO</th>
							<th>UNIDAD</th>
							<th class="text-end">CANT.</th>
							<th class="text-end">PRECIO</th>
							<th class="text-end">IGV</th>
							<th class="text-end">VALOR</th>
							<th class="text-end">SUBTOTAL</th>
							<th class="text-end">ICBPER</th>
						</tr>
					</thead>

					<tbody>
						<?php foreach ($detalle as $key => $value) { ?>
							<tr>
								<td class="text-muted"><?php echo $value["codproducto"]; ?></td>
								<td><?php echo $value["codigo"]; ?></td>
								<td class="fw-semibold"><?php echo $value["producto"]; ?></td>
								<td><?php echo $value["unidad"]; ?></td>
								<td class="text-end"><?php echo round($value["cantidad"], 2); ?></td>
								<td class="text-end">S/. <?php echo number_format($value["preciounitario"], 2); ?></td>
								<td class="text-end">S/. <?php echo number_format($value["igv"], 2); ?></td>
								<td class="text-end">S/. <?php echo number_format($value["valorventa"], 2); ?></td>
								<td class="text-end fw-semibold">S/. <?php echo number_format($value["subtotal"], 2); ?></td>
								<td class="text-end">S/. <?php echo number_format($value["icbper"], 2); ?></td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>

			<!-- TOTALES -->
			<div class="row g-2 mt-4">

				<div class="col-md-4">
					<div class="alert alert-danger text-center mb-0 py-2">
						<small class="d-block">DESCUENTO</small>
						<strong>S/. <?php echo number_format(round($info[0]["descglobal"],2),2); ?></strong>
					</div>
				</div>

				<div class="col-md-4">
					<div class="alert alert-primary text-center mb-0 py-2">
						<small class="d-block">VALOR VENTA</small>
						<strong>S/. <?php echo number_format(round($info[0]["valorventa"],2),2); ?></strong>
					</div>
				</div>

				<div class="col-md-4">
					<div class="alert alert-warning text-center mb-0 py-2">
						<small class="d-block">I.G.V</small>
						<strong>S/. <?php echo number_format(round($info[0]["igv"],2),2); ?></strong>
					</div>
				</div>

			</div>

			<!-- TOTAL -->
			<div class="alert alert-success text-center mt-3 mb-0 py-2">
				<strong style="font-size:20px">
					TOTAL PEDIDO: S/. <?php echo number_format(round($info[0]["importe"],2),2); ?>
				</strong>
			</div>

		</div>
	</div>
</div>

<script>
document.querySelectorAll('.drag-scroll').forEach(function(el) {
	let isDown = false;
	let startX;
	let scrollLeft;

	el.addEventListener('mousedown', (e) => {
		isDown = true;
		el.classList.add('active');
		startX = e.pageX - el.offsetLeft;
		scrollLeft = el.scrollLeft;
	});

	el.addEventListener('mouseleave', () => {
		isDown = false;
		el.classList.remove('active');
	});

	el.addEventListener('mouseup', () => {
		isDown = false;
		el.classList.remove('active');
	});

	el.addEventListener('mousemove', (e) => {
		if (!isDown) return;
		e.preventDefault();
		const x = e.pageX - el.offsetLeft;
		const walk = (x - startX) * 1.5;
		el.scrollLeft = scrollLeft - walk;
	});
});
</script>