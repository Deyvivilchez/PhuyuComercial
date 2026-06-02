<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_form" class="phuyu-velzon-form phuyu-creditos-velzon">
	<div class="card phuyu-card">
		<div class="card-body">
			<div class="phuyu-form-title">
				<span class="phuyu-form-icon"><i class="bi bi-arrow-left-right"></i></span>
				<div>
					<h5 class="mb-1">Conciliación de créditos</h5>
					<p class="text-muted mb-0">Detalle de productos asociados al crédito.</p>
				</div>
			</div>

			<div class="table-responsive">
				<table class="table table-bordered">
					<thead>
						<tr>
							<th width="5px">ID</th>
							<th>CODIGO</th>
							<th>PRODUCTO</th>
							<th>UNIDAD</th>
							<th>CANT.</th>
							<th>PRECIO</th>
							<th>IGV</th>
							<th>VALORVENTA</th>
							<th>SUBTOTAL</th>
							<th>ICBPER</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($detalle as $key => $value) { ?>
							<tr>
								<td><?php echo $value["codproducto"];?></td>
								<td><?php echo $value["codigo"];?></td>
								<td><?php echo $value["producto"];?></td>
								<td><?php echo $value["unidad"];?></td>
								<td><?php echo round($value["cantidad"],2);?></td>
								<td><?php echo number_format($value["preciounitario"],2);?></td>
								<td><?php echo number_format($value["igv"],2);?></td>
								<td><?php echo number_format($value["valorventa"],2);?></td>
								<td><?php echo number_format($value["subtotal"],2);?></td>
								<td><?php echo number_format($value["icbper"],2);?></td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
