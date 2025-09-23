<div id="phuyu_form">
	<table class="table table-bordered">
		<tbody>
			<tr>
				<td style="width: 350px;"><b>MARCAR TODAS LAS LINEAS</b></td>
				<td class="text-center"> <input type="checkbox" class="form-check-input" id="marcar" v-on:change="phuyu_marcar()"> </td>
			</tr>
		</tbody>
	</table>
	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardarlineas()">
		<input type="hidden" name="codsucursal" v-model="campos.codsucursal">
		<div class="row form-group">
			<div class="col-xs-12">		
				<h5 align="center">LINEAS DE LA SUCURSAL</h5>

				<div class="row form-group">
					<div class="col-xs-12">
						<table class="table table-bordered table-condensed">
							<thead>
								<tr>
									<th>LINEA</th>
									<th>PERMISO</th>
								</tr>
							</thead>
							<tbody>
								<?php 
									foreach ($lineas as $key => $value) { ?>
										<tr>
											<td><?php echo $value["descripcion"];?></td>
											<td>
												<input type="hidden" name="lineas[]" value="<?php echo $value["codlinea"];?>">
												<input type="checkbox" class="form-check-input" id="linea_<?php echo $value["codlinea"];?>" value="<?php echo $value["codlinea"];?>" v-model="campos.lineas">
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
		<div class="ln_solid"></div>
		<div class="form-group" align="center">
			<button type="submit" class="btn btn-success" v-bind:disabled="estado==1"> <i class="fa fa-save"></i> GUARDAR </button>
			<button type="button" class="btn btn-danger" v-on:click="phuyu_cerrar()">CERRAR</button>
		</div>
	</form>
</div>
<script> 
var campos = {codsucursal:"",lineas: []}; </script>
<script src="<?php echo base_url();?>phuyu/phuyu_form.js"></script>