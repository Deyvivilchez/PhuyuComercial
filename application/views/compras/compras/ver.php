<div id="phuyu_operacion">
	<div style="padding: 0px 20px;">
		<div class="row">
			<div class="col-md-7">
				<h6><b>COMPRA:</b> 000<?php echo $info[0]["codkardex"];?> | <b>F.COMPRA:</b> <?php echo $info[0]["fechacomprobante"];?> | <b>KARDEX:</b> <?php echo $info[0]["fechakardex"];?> </h6>
				<h6><b>COMPROBANTE:</b> <?php echo $info[0]["tipo"].': '.$info[0]["seriecomprobante"].'-'.$info[0]["nrocomprobante"];?></h6>
				<h6><b>DOCUMENTO:</b> <?php echo $info[0]["documento"];?></h6>
				<h6><b>PROVEEDOR:</b> <?php echo $info[0]["razonsocial"];?></h6>
				<h6><b>NOMBRE COMERCIAL:</b> <?php echo $info[0]["nombrecomercial"];?></h6>
				<h6><b>DIRECCION:</b> <?php echo $info[0]["direccion"];?></h6>
			</div>
			<div class="col-md-5">
				<h6><b>CONDICION PAGO:</b> <?php echo $info[0]["pago"];?></h6>
				<h6><b>MOVIMIENTO:</b> <?php echo $info[0]["movimiento"];?></h6>
			</div>
		</div>

		<?php 
			if ($info[0]["codmoneda"]!=1) {
				$simbolo = "$"; ?>
				<h6>
					<b>MONEDA:</b> DOLAR | 
					<b>TIPO CAMBIO: <?php echo $info[0]["tipocambio"];?></b>
				</h6>
			<?php }else{
				$simbolo = "S/.";
			}
		?>

		<h5 class="text-center"> <b>DETALLE DE LA COMPRA</b> </h5>
		<table class="table table-bordered">
			<thead>
				<tr>
					<th width="5px">ID</th>
					<th>CODIGO</th>
					<th>PRODUCTO</th>
					<th>UNIDAD</th>
					<th>CANTIDAD</th>
					<th>PRECIO</th>
					<th>SUBTOTAL</th>
					<th></th>
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
							<td><?php echo round($value["cantidad"],3);?></td>
							<td><?php echo round($value["preciounitario"],4);?></td>
							<td><?php echo round($value["subtotal"],2);?></td>
							<td><button type="button" class="btn btn-xs btn-success" v-on:click="phuyu_masprecios(<?php echo $value["codproducto"].','."'".$value["producto"]."'".','.$value["preciosinigv"].','.$value["codunidad"].','.$value["igv"].','.$info[0]["tipocambio"].','.$info[0]["codmoneda"]; ?>)"> <i data-acorn-icon="eye"></i> </button></td>
						</tr>
					<?php }
				?>
			</thead>
		</table>
		<!--<button type="button" class="btn btn-success btn-block" v-on:click="phuyu_valorizar_precios(<?php echo $info[0]["codkardex"];?>,'<?php echo $info[0]["fechakardex"];?>')"><i class="fa fa-cog"></i> VALORIZAR PRECIOS</button> -->

		<table class="table table-bordered">
			<?php
				if (count($otros)>0) {
					echo '<h5 class="text-center"> <b>DETALLE OTROS GASTOS DE LA COMPRA</b> </h5>';
				}
				foreach ($otros as $key => $value) { ?>
					<tr>
						<td><?php echo $value["razonsocial"];?></td>
						<td><b><?php echo number_format($value["importe"],2);?></b></td>
					</tr>
				<?php }
			?>
		</table>

		<h5 class="text-center"> <b>DETALLE DE LOS PAGOS</b> </h5>
		<table class="table table-bordered">
			<thead>
				<tr>
					<th>TIPO</th>
					<th>ENTREGADO</th>
					<th>IMPORTE</th>
					<th>VUELTO</th>
					<th>NRO DOC</th>
				</tr>
			</thead>
			<thead>
				<?php 
					foreach ($pagos as $key => $value) { ?>
						<tr>
							<td><?php echo $value["tipopago"];?></td>
							<td><?php echo round($value["importeentregado"],2);?></td>
							<td><?php echo round($value["importe"],2);?></td>
							<td><?php echo round($value["vuelto"],2);?></td>
							<td><?php echo $value["nrodocbanco"];?></td>
						</tr>
					<?php }
				?>
			</thead>
		</table>

		<h4 class="text-center">
			<span class="label label-danger">DESCUENTOS: <?php echo $simbolo." ".number_format(round($info[0]["descuentos"],2) ,2);?> </span> &nbsp;
			<span class="label label-success">VALOR COMPRA: <?php echo $simbolo." ".number_format(round($info[0]["valorventa"],2) ,2);?> </span> &nbsp;
			<span class="label label-warning">I.G.V: <?php echo $simbolo." ".number_format(round($info[0]["igv"],2) ,2);?> </span> &nbsp;
			<span class="label label-warning">ICBPER: <?php echo $simbolo." ".number_format(round($info[0]["icbper"],2) ,2);?> </span>
		</h4>
		<h4 class="text-center">
			<span class="label label-info">FLETE: <?php echo $simbolo." ".number_format(round($info[0]["flete"],2) ,2);?> </span> &nbsp;
			<span class="label label-primary">GASTOS: <?php echo $simbolo." ".number_format(round($info[0]["gastos"],2) ,2);?> </span>
		</h4> <br>
		<div class="alert alert-success" align="center" style="padding:5px;">
			<strong style="font-size:25px">TOTAL COMPRA: <?php echo $simbolo." ".number_format(round($info[0]["importe"],2) ,2);?></strong>
		</div>
	</div>
	<div id="modal_masprecios" data-bs-backdrop="static" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-xl">
			<div class="modal-content">
				<div class="modal-header" style="padding:.4rem;">
					<h4 class="modal-title"> <b style="letter-spacing:0.5px;">ACTUALIZACION DE PRECIOS | <span id="descripcionproducto"></span> </b> </h4> 
				</div>
				<div class="modal-body" style="padding: .4rem">					
					<div class="phuyu_cargando" v-if="cargando">
						<div class="overlay-spinner">
						</div>
					</div>
					<div id="cuerpomasprecios">
						
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	if (typeof AcornIcons !== 'undefined') {
      new AcornIcons().replace();
    }
    if (typeof Icons !== 'undefined') {
      const icons = new Icons();
    }
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_compras/ver.js"> </script>