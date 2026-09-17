<div id="phuyu_form">
	<div style="padding: 0px 20px;">
		<h6><b>NROPROFORMA:</b> 000<?php echo $info[0]["codproforma"];?></h6>
		<h6><b>CLIENTE:</b> <?php echo $info[0]["razonsocial"];?></h6>
		<h6><b>DIRECCION:</b> <?php echo $info[0]["direccion"];?></h6>
		<h6><b>FECHA PROFORMA:</b> <?php echo $info[0]["fechaproforma"];?></h6>

		<div class="table-responsive">
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>DESCRIPCION</th>
						<th width="10px">UNIDAD</th>
						<th width="10px">PRECIO</th>
						<th width="10px">CANTIDAD</th>
						<th width="10px">ATENDIDO</th>
						<th width="10px">PENDIENTE</th>
					</tr>
				</thead>
				<tbody>
					<?php 
						foreach ($detalle as $key => $value) { ?>
							<tr>
								<td><?php echo $value["producto"];?></td>
								<td><?php echo $value["unidad"];?></td>
								<td><?php echo number_format($value["preciounitario"],2);?></td>
								<td><?php echo round($value["cantidad"],2);?></td>
								<td><?php echo round($value["atendido"],2);?></td>
								<td><?php echo round($value["falta"],2);?></td>
							</tr>
						<?php }
					?>
				</tbody>
				<tfoot>
					<?php 
						foreach ($totales as $key => $value) { ?>
							<tr>
								<td colspan="3" align="right"><b>TOTALES</b></td>
								<td><?php echo round($value["cantidad"],2);?></td>
								<td><?php echo round($value["atendido"],2);?></td>
								<td><?php echo round($value["cantidad"] - $value["atendido"],2);?></td>
							</tr>
						<?php }
					?>
				</tfoot>
			</table>
		</div>
		
		<div class="alert alert-success" align="center" style="padding:5px;">
			<strong style="font-size:20px">TOTAL PROFORMA: S/. <?php echo number_format(round($info[0]["importe"],2) ,2);?></strong>
		</div>
	</div>
</div>