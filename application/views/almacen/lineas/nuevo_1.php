<div class="row" id="phuyu_form_1">
	<div class="col-md-12 col-xs-12">
		<h4><b>REGISTRAR NUEVA LINEA</b></h4><hr>
		<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar_1('almacen/lineas')">
			<input type="hidden" id="codigo_extencion" value="codlinea">
			<div class="form-group">
				<label>DESCRIPCION LINEA</label>
	        	<input type="text" name="descripcion_extencion" v-model.trim="agregar.descripcion" class="form-control" required autocomplete="off" placeholder="Descripcion . . ." maxlength="100" />
			</div>

			<h5 align="center">SELECCIONE LAS SUCURSALES DONDE SE MANEJARÁ LA LINEA</h5>

			<div class="row form-group">
				<div class="col-xs-12">
					<table class="table table-bordered table-condensed">
						<thead>
							<tr>
								<th>SUCURSAL</th>
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
											<input type="checkbox" class="form-check-input" id="sucursal_<?php echo $value["codsucursal"];?>" value="<?php echo $value["codsucursal"];?>" v-model="agregar.sucursales" name="checks[]">
										</td>
									</tr>
								<?php }
							?>
						</tbody>
					</table>
				</div>
			</div>

			<div class="form-group text-center"> <br>
				<button type="submit" class="btn btn-primary" v-bind:disabled="estado_1==1"> <i data-acorn-icon="save"></i> GUARDAR </button>
				<button type="button" class="btn btn-danger" data-bs-dismiss="modal">CERRAR</button>
			</div>
		</form>
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
<script src="<?php echo base_url();?>phuyu/phuyu_form_1.js"></script>