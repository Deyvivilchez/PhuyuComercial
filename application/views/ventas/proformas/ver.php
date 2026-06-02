<div id="phuyu_form">
	<div style="padding: 0px 20px;">
		<div class="row">
			<div class="col-md-7">
				<h6><b>PEDIDO:</b> 000<?php echo $info[0]["codproforma"];?> | <b>FECHA PEDIDO:</b> <?php echo $info[0]["fechaproforma"];?> </h6>
				<h6><b>COMPROBANTE:</b> <?php echo $info[0]["tipo"].': '.$info[0]["seriecomprobante"].'-'.$info[0]["nrocomprobante"];?></h6>
				<h6><b>DOCUMENTO:</b> <?php echo $info[0]["documento"];?></h6>
				<h6><b>CLIENTE:</b> <?php echo $info[0]["razonsocial"];?></h6>
				<h6><b>DIRECCION:</b> <?php echo $info[0]["direccion"];?></h6>
			</div>
			<div class="col-md-5">
				<h6><b>CONDICION PAGO:</b> <?php echo $info[0]["pago"];?></h6>
				<h6><b>ESTADO PROCESO:</b> <?php echo $info[0]["proceso"];?></h6>
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
				</tbody>
			</table>
		</div>
		<h4 align="center">
			<span class="label label-danger">DESC.: S/. <?php echo number_format(round($info[0]["descglobal"],2) ,2);?> </span> &nbsp;
			<span class="label label-success">VALOR VENTA: S/. <?php echo number_format(round($info[0]["valorventa"],2) ,2);?> </span> &nbsp;
			<span class="label label-warning">I.G.V: S/. <?php echo number_format(round($info[0]["igv"],2) ,2);?> </span>
		</h4>
		<div class="alert alert-success" align="center" style="padding:5px;">
			<strong style="font-size:20px">TOTAL PEDIDO: S/. <?php echo number_format(round($info[0]["importe"],2) ,2);?></strong>
		</div>
	</div>
</div>