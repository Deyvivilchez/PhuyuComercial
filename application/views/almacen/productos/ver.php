<div id="phuyu_form" style="padding: 1rem;">
	<h4 class="text-center"> <b><?php echo $info[0]["descripcion"];?></b> </h4>
	<div class="row form-group">
		<div class="col-md-3">
			<label>CODIGO </label>
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
    </div>
    <div class="row form-group">
    	<div class="col-md-3">
			<label>MARCA</label>
        	<input type="text" class="form-control" disabled value="<?php echo $info[0]["marca"];?>" />
		</div>
		<div class="col-md-3">
			<label>COMISION (%)</label>
			<input type="text" class="form-control" disabled value="<?php echo $info[0]["comisionvendedor"]?>">
		</div>
    </div>
    <div class="row form-group">
    	<div class="col-md-12">
			<label>CARACTERISTICAS DEL PRODUCTO</label>
        	<textarea class="form-control" disabled value="" rows="3"><?php echo $info[0]["caracteristicas"];?></textarea>
		</div>
    </div>

	<div class="form-group">
		<hr>
		<h5 class="text-center"> <b>UNIDADES DE MEDIDA / PRECIOS</b> </h5>

		<div class="table-responsive">
			<table class="table table-bordered table-condensed">
				<thead >
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
				<tbody style="font-size:13px;">
					<?php 
						foreach ($unidades as $key => $value) {
							$anulado = ""; if ($value["estado"]!=1) {$anulado = "phuyu_anulado";} ?>

							<tr class="<?php echo $anulado;?>">
								<td><b><?php echo $value["unidad"];?></b></td>
								<td><?php echo $value["factor"];?></td>
								<td><span class="label label-warning"><?php echo $value["stock"];?></span></td>
								<td><?php echo number_format($value["preciocompra"],3);?></td>
								<td><?php echo number_format($value["pventapublico"],3);?></td>
								<td><?php echo number_format($value["pventamin"],3);?></td>
								<td><?php echo number_format($value["pventacredito"],3);?></td>
								<td><?php echo number_format($value["pventaxmayor"],3);?></td>
								<td><?php echo number_format($value["pventaadicional"],3);?></td>
								<td><?php echo $value["codigobarra"]?></td>
								<td style="padding: 5px;">
									<?php 
										if ($value["estado"]==1) { ?>
											<span class="label label-success">ACTIVO</span>
										<?php }else{ ?>
											<span class="label label-danger">ANULADO</span>
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