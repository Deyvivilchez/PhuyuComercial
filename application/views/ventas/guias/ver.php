<style>
	#phuyu_form .phuyu-view-block {
		border: 1px solid rgba(64, 81, 137, .12);
		border-radius: 1rem;
		padding: 1rem;
		background: #fff;
	}

	#phuyu_form .phuyu-view-title {
		display: flex;
		align-items: center;
		gap: .6rem;
		color: #405189;
		font-weight: 800;
		margin-bottom: 1rem;
	}

	#phuyu_form .phuyu-view-title i {
		width: 36px;
		height: 36px;
		border-radius: 12px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		background: rgba(64, 81, 137, .1);
	}

	#phuyu_form .phuyu-info-line {
		display: flex;
		gap: .5rem;
		margin-bottom: .45rem;
		color: #495057;
	}

	#phuyu_form .phuyu-info-line strong {
		min-width: 120px;
		color: #212529;
	}

	#phuyu_form .phuyu-table-wrapper {
		border: 1px solid #eef1f4;
		border-radius: 14px;
		overflow: auto;
	}

	#phuyu_form .phuyu-table-detalle {
		font-size: 12px;
		min-width: 620px;
		margin-bottom: 0;
	}

	#phuyu_form .phuyu-table-detalle thead th {
		background: #f3f6f9;
		font-weight: 800;
		text-transform: uppercase;
		color: #343a40;
		white-space: nowrap;
	}
</style>

<div id="phuyu_form">
	<div class="phuyu-view-block">
		<div class="phuyu-view-title">
			<i class="bi bi-truck"></i>
			<h5 class="mb-0">Detalle de guía de remisión</h5>
		</div>

		<div class="row g-3 mb-3">
			<div class="col-md-6">
				<div class="phuyu-info-line">
					<strong>Guía:</strong>
					<span><?php echo $info[0]["seriecomprobante"]."-".$info[0]["nrocomprobante"];?></span>
				</div>
				<div class="phuyu-info-line">
					<strong>Fecha guía:</strong>
					<span><?php echo $info[0]["fechaguia"];?></span>
				</div>
			</div>

			<div class="col-md-6">
				<div class="phuyu-info-line">
					<strong>Destinatario:</strong>
					<span><?php echo $info[0]["destinatario"];?></span>
				</div>
				<div class="phuyu-info-line">
					<strong>Dirección llegada:</strong>
					<span><?php echo $info[0]["direccionllegada"];?></span>
				</div>
			</div>
		</div>

		<h6 class="text-primary fw-bold mb-2">
			<i class="bi bi-box-seam me-1"></i>
			Productos trasladados
		</h6>

		<div class="phuyu-table-wrapper">
			<table class="table table-sm table-hover table-striped align-middle phuyu-table-detalle">
				<thead>
					<tr>
						<th style="width:70px;">ID</th>
						<th>Producto</th>
						<th>Unidad</th>
						<th class="text-end">Cantidad</th>
						<th class="text-end">Peso</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($detalle as $key => $value) { ?>
						<tr>
							<td class="text-muted fw-semibold"><?php echo $value["codproducto"];?></td>
							<td class="fw-semibold"><?php echo $value["producto"];?></td>
							<td><?php echo $value["unidad"];?></td>
							<td class="text-end"><?php echo round($value["cantidad"],2);?></td>
							<td class="text-end"><?php echo number_format($value["peso"],2);?></td>
						</tr>
					<?php } ?>

					<?php if (count($detalle) == 0) { ?>
						<tr>
							<td colspan="5" class="text-center text-muted py-4">
								<i class="bi bi-inbox d-block mb-1" style="font-size:28px;"></i>
								Sin productos registrados
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
