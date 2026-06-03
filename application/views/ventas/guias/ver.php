<div id="phuyu_form">
	<div style="padding: 0px 20px;">
		<h6><b>GUIA REMISION:</b> <?php echo $info[0]["seriecomprobante"]."-".$info[0]["nrocomprobante"];?> | <b>FECHA GUIA:</b> <?php echo $info[0]["fechaguia"];?> | <b>KARDEX:</b> <?php echo $info[0]["fechaguia"];?> </h6>
		<h6><b>DESTINATARIO:</b> <?php echo $info[0]["destinatario"];?></h6>
		<h6><b>DIRECCION LLEGADA:</b> <?php echo $info[0]["direccionllegada"];?></h6>
		<h6><b>FECHA GUIA:</b> <?php echo $info[0]["fechaguia"];?></h6>

		<h5 class="text-center"> <b>DETALLE DE LA VENTA</b> </h5>
		<div class="table-responsive">
			<table class="table table-bordered">
				<thead>
					<tr>
						<th width="5px">ID</th>
						<th>PRODUCTO</th>
						<th>UNIDAD</th>
						<th>CANT.</th>
						<th>PESO</th>
					</tr>
				</thead>
				<thead>
					<?php 
						foreach ($detalle as $key => $value) { ?>
							<tr>
								<td><?php echo $value["codproducto"];?></td>
								<td><?php echo $value["producto"];?></td>
								<td><?php echo $value["unidad"];?></td>
								<td><?php echo round($value["cantidad"],2);?></td>
								<td><?php echo number_format($value["peso"],2);?></td>
							</tr>
						<?php }
					?>
				</thead>
			</table>
		</div>
	</div>
</div>