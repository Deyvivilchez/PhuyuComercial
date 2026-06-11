<style>
	#phuyu_form.phuyu-producto-detalle {
		color: #212529;
		padding: 1rem;
	}
	#phuyu_form.phuyu-producto-detalle .phuyu-detail-card {
		border: 1px solid #e9ebec;
		border-radius: 8px;
		box-shadow: 0 1px 2px rgba(56, 65, 74, 0.08);
	}
	#phuyu_form.phuyu-producto-detalle .phuyu-detail-header {
		align-items: center;
		background: #f3f6f9;
		border: 1px solid #e9ebec;
		border-radius: 8px;
		display: flex;
		gap: 12px;
		margin-bottom: 16px;
		padding: 14px 16px;
	}
	#phuyu_form.phuyu-producto-detalle .phuyu-detail-icon {
		align-items: center;
		background: rgba(64, 81, 137, 0.12);
		border-radius: 8px;
		color: #405189;
		display: inline-flex;
		font-size: 22px;
		height: 44px;
		justify-content: center;
		width: 44px;
	}
	#phuyu_form.phuyu-producto-detalle label {
		color: #878a99;
		font-size: 11px;
		font-weight: 700;
		margin-bottom: 6px;
		text-transform: uppercase;
	}
	#phuyu_form.phuyu-producto-detalle .form-control {
		border: 1px solid #d9e2ef;
		border-radius: 6px;
		box-shadow: none;
	}
	#phuyu_form.phuyu-producto-detalle .table thead th {
		background: #f3f6f9;
		color: #495057;
		font-size: 11px;
		text-transform: uppercase;
		white-space: nowrap;
	}
</style>

<div id="phuyu_form" class="phuyu-producto-detalle">
	<div class="card phuyu-detail-card">
		<div class="card-body">
			<div class="phuyu-detail-header">
				<div class="phuyu-detail-icon">
					<i class="bi bi-box-seam"></i>
				</div>
				<div>
					<p class="text-muted text-uppercase mb-1">Detalle del producto</p>
					<h5 class="mb-0"><?php echo $info[0]["descripcion"];?></h5>
				</div>
			</div>

			<div class="row g-3">
				<div class="col-md-3">
					<label>CODIGO</label>
					<input type="text" class="form-control" disabled value="<?php echo $info[0]["codigo"];?>" />
				</div>
				<div class="col-md-3">
					<label>TIPO PRODUCTO</label>
					<input type="text" class="form-control" disabled value="<?php echo $info[0]["tipo"];?>" name="">
				</div>
				<div class="col-md-3">
					<label>FAMILIA</label>
					<input type="text" class="form-control" disabled value="<?php echo $info[0]["familia"];?>" />
				</div>
				<div class="col-md-3">
					<label>LINEA</label>
					<input type="text" class="form-control" disabled value="<?php echo $info[0]["linea"];?>" />
				</div>
				<div class="col-md-3">
					<label>MARCA</label>
					<input type="text" class="form-control" disabled value="<?php echo $info[0]["marca"];?>" />
				</div>
				<div class="col-md-3">
					<label>COMISION (%)</label>
					<input type="text" class="form-control" disabled value="<?php echo $info[0]["comisionvendedor"]?>">
				</div>
				<div class="col-md-12">
					<label>CARACTERISTICAS DEL PRODUCTO</label>
					<textarea class="form-control" disabled rows="3"><?php echo $info[0]["caracteristicas"];?></textarea>
				</div>
			</div>

			<hr>
			<h5 class="text-center mb-3">Unidades de medida / precios</h5>

			<div class="table-responsive">
				<table class="table table-hover align-middle mb-0">
					<thead>
						<tr>
							<th>UNIDAD</th>
							<th>FACTOR</th>
							<th>STOCK</th>
							<th>P.COMPRA</th>
							<th>P.VENTA</th>
							<th>P.MINIMO</th>
							<th>P.CREDITO</th>
							<th>P.MAYOR</th>
							<th>P.OTROS</th>
							<th>C.BARRA</th>
							<th>ESTADO</th>
						</tr>
					</thead>
					<tbody>
						<?php
							foreach ($unidades as $key => $value) {
								$anulado = ""; if ($value["estado"]!=1) {$anulado = "phuyu_anulado";} ?>

								<tr class="<?php echo $anulado;?>">
									<td><b><?php echo $value["unidad"];?></b></td>
									<td><?php echo $value["factor"];?></td>
									<td><span class="badge bg-warning text-dark"><?php echo $value["stock"];?></span></td>
									<td><?php echo number_format($value["preciocompra"],3);?></td>
									<td><?php echo number_format($value["pventapublico"],3);?></td>
									<td><?php echo number_format($value["pventamin"],3);?></td>
									<td><?php echo number_format($value["pventacredito"],3);?></td>
									<td><?php echo number_format($value["pventaxmayor"],3);?></td>
									<td><?php echo number_format($value["pventaadicional"],3);?></td>
									<td><?php echo $value["codigobarra"]?></td>
									<td>
										<?php
											if ($value["estado"]==1) { ?>
												<span class="badge bg-success">ACTIVO</span>
											<?php }else{ ?>
												<span class="badge bg-danger">ANULADO</span>
											<?php }
										?>
									</td>
								</tr>
							<?php }
						?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
