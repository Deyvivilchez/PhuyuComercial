<div id="phuyu_form">
	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">
		<br> <input type="hidden" name="codregistro" v-model="campos.codregistro">

		<div class="row form-group">
			<div class="col-md-12">
				<label>DESCRIPCION RUBRO</label>
	        	<input type="text" name="descripcion" v-model.trim="campos.descripcion" class="form-control" required autocomplete="off" placeholder="Descripcion . . ." />
			</div>
		</div>
		<div class="row form-group">
		    <div class="col-md-12 col-xs-12">
				<div class="">
					<label v-if="campos.activo==1" >
					  	UTILIZAR RUBRO EN EL SISTEMA <input type="checkbox" v-on:click="phuyu_activarrubro()" checked/>
					</label>
					<label v-else="campos.activo!=1" >
					  	UTILIZAR RUBRO EN EL SISTEMA <input type="checkbox" v-on:click="phuyu_activarrubro()"/>
					</label>
				</div>
		    </div>
	    </div>
	    <br>
	    <h5 align="center">SELECCIONE LAS SUCURSALES DONDE SE MANEJARÁ EL RUBRO</h5>

		<div class="row form-group">
			<div class="col-xs-12">
				<table class="table table-bordered table-condensed">
					<thead>
						<tr>
							<th>RUBRO</th>
							<th>PERMISO</th>
						</tr>
					</thead>
					<tbody>
						<?php 
							foreach ($sucursales as $key => $value) { ?>
								<tr>
									<td><?php echo $value["descripcion"];?></td>
									<td>
										<input type="hidden" name="sucursales[]" value="<?php echo $value["codsucursal"];?>">
										<input type="checkbox" class="form-check-input" id="sucursal_<?php echo $value["codsucursal"];?>" value="<?php echo $value["codsucursal"];?>" v-model="campos.sucursales">
									</td>
								</tr>
							<?php }
						?>
					</tbody>
				</table>
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
var campos = {codregistro:"",descripcion: "",activo:0,sucursales: []}; </script>
<script src="<?php echo base_url();?>phuyu/phuyu_form.js"></script>