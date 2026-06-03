<div id="phuyu_form">
	<div style="padding: 0px 20px;">
		<div class="row">
			<div class="col-md-10">
				<h6><b>SALIDA:</b> 000<?php echo $info[0]["codkardex"];?> | <b>FECHA DE DEVOLUCION:</b> <?php echo $info[0]["fechakardex"];?> </h6>
				<h6><b>COMPROBANTE:</b> <?php echo 'SALIDA : '.$info[0]["seriecomprobante"].'-'.$info[0]["nrocomprobante"];?></h6>
				<h6><b>DOCUMENTO:</b> <?php echo $info[0]["documento"];?></h6>
				<h6><b>SOCIO:</b> <?php echo $info[0]["cliente"];?></h6>
			</div>
		</div>
		<h5 class="text-center"> <b>DETALLE DE LA DEVOLUCION</b> </h5>
		<div class="table-responsive">
			<table class="table table-bordered" style="font-size: 11px">
				<thead>
					<tr>
						<th width="5px">ID</th>
						<th>CODIGO</th>
						<th>PRODUCTO</th>
						<th>UNIDAD</th>
						<th>CANTIDAD DEVUELTA</th>
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
							</tr>
						<?php }
					?>
				</thead>
			</table>
		</div>
	</div>
</div>