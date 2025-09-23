<div id="phuyu_form">
	<div style="padding: 0px 20px;">
		<h5 class="text-center"> <b>CONCILIACION DE CREDITOS</b> </h5>
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
				<thead>
					<?php 
						foreach ($detalle as $key => $value) { ?>
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
						<?php }
					?>
				</thead>
			</table>
		</div>
	</div>
</div>