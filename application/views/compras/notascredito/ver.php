<div id="phuyu_form" class="phuyu-nota-ver">
	<div class="phuyu-ver-card">
		<div class="phuyu-ver-header">
			<div class="phuyu-ver-icon"><i class="bi bi-receipt-cutoff"></i></div>
			<div>
				<div class="text-muted small text-uppercase fw-semibold">Nota de credito</div>
				<h5 class="mb-0 fw-bold">NC 000<?php echo $info[0]["codkardex"];?></h5>
			</div>
		</div>

		<div class="row g-3 mb-3">
			<div class="col-md-6">
				<div class="phuyu-info-item">
					<span>Fecha nota</span>
					<strong><?php echo $info[0]["fechacomprobante"];?></strong>
				</div>
			</div>
			<div class="col-md-6">
				<div class="phuyu-info-item">
					<span>Proveedor</span>
					<strong><?php echo $info[0]["cliente"];?></strong>
				</div>
			</div>
			<div class="col-12">
				<div class="phuyu-info-item">
					<span>Direccion</span>
					<strong><?php echo $info[0]["direccion"];?></strong>
				</div>
			</div>
			<div class="col-md-6">
				<div class="phuyu-info-item">
					<span>Comprobante</span>
					<strong><?php echo $info[0]["seriecomprobante"]."-".$info[0]["nrocomprobante"];?></strong>
				</div>
			</div>
			<div class="col-md-6">
				<div class="phuyu-info-item">
					<span>Comprobante referencia</span>
					<strong><?php echo $info[0]["seriecomprobante_ref"]."-".$info[0]["nrocomprobante_ref"];?></strong>
				</div>
			</div>
		</div>

		<div class="phuyu-section-title">
			<i class="bi bi-list-check"></i>
			<span>Detalle de la nota de credito</span>
		</div>
		<div class="table-responsive phuyu-table-wrap">
			<table class="table table-hover align-middle mb-0">
				<thead>
					<tr>
						<th>ID</th>
						<th>Producto</th>
						<th>Unidad</th>
						<th class="text-end">Cantidad</th>
						<th class="text-end">Precio</th>
						<th class="text-end">Subtotal</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($detalle as $value) { ?>
						<tr>
							<td class="text-muted fw-semibold"><?php echo $value["codproducto"];?></td>
							<td><?php echo $value["producto"];?></td>
							<td><?php echo $value["unidad"];?></td>
							<td class="text-end"><?php echo round($value["cantidad"],2);?></td>
							<td class="text-end"><?php echo round($value["preciounitario"],2);?></td>
							<td class="text-end fw-semibold"><?php echo round($value["subtotal"],2);?></td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>

		<div class="phuyu-total-box">
			<span>Total nota credito</span>
			<strong>S/ <?php echo round($info[0]["importe"],2);?></strong>
		</div>
	</div>
</div>

<style>
	#phuyu_form.phuyu-nota-ver {
		padding: .25rem;
	}

	#phuyu_form .phuyu-ver-card {
		background: #fff;
		border: 1px solid rgba(64, 81, 137, .12);
		border-radius: .9rem;
		padding: 1.25rem;
		box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
	}

	#phuyu_form .phuyu-ver-header {
		display: flex;
		align-items: center;
		gap: .75rem;
		padding-bottom: 1rem;
		margin-bottom: 1rem;
		border-bottom: 1px solid rgba(64, 81, 137, .10);
	}

	#phuyu_form .phuyu-ver-icon {
		width: 44px;
		height: 44px;
		border-radius: 12px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		background: rgba(64, 81, 137, .10);
		color: #405189;
		font-size: 1.25rem;
	}

	#phuyu_form .phuyu-info-item {
		border: 1px solid rgba(64, 81, 137, .10);
		border-radius: .7rem;
		padding: .75rem;
		background: #f8fafc;
		min-height: 64px;
	}

	#phuyu_form .phuyu-info-item span {
		display: block;
		font-size: .72rem;
		font-weight: 800;
		text-transform: uppercase;
		color: #6c757d;
		margin-bottom: .25rem;
	}

	#phuyu_form .phuyu-info-item strong {
		color: #212529;
	}

	#phuyu_form .phuyu-section-title {
		display: flex;
		align-items: center;
		gap: .55rem;
		margin-bottom: .75rem;
		font-weight: 700;
		color: #405189;
	}

	#phuyu_form .phuyu-section-title i {
		width: 32px;
		height: 32px;
		border-radius: 10px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		background: rgba(64, 81, 137, .10);
	}

	#phuyu_form .phuyu-table-wrap {
		border: 1px solid rgba(64, 81, 137, .10);
		border-radius: .75rem;
		overflow: hidden;
	}

	#phuyu_form table thead th {
		background: #f8fafc;
		font-size: .74rem;
		font-weight: 800;
		text-transform: uppercase;
		white-space: nowrap;
	}

	#phuyu_form .phuyu-total-box {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 1rem;
		margin-top: 1rem;
		padding: 1rem;
		border-radius: .8rem;
		background: rgba(10, 179, 156, .10);
		color: #087f70;
	}

	#phuyu_form .phuyu-total-box span {
		font-weight: 800;
		text-transform: uppercase;
	}

	#phuyu_form .phuyu-total-box strong {
		font-size: 1.35rem;
	}
</style>
