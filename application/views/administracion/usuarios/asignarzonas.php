<div id="phuyu_form" style="padding: 0px 20px;">
	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">
		<input type="hidden" name="codusuario" v-model="campos.codusuario">
		<h6 align="center">ZONAS DEL USUARIO</h6>
		<div class="form-group">
			<table class="table table-bordered table-condensed">
				<thead>
					<tr>
						<th>ZONA</th>
						<th>PERMISO</th>
					</tr>
				</thead>
				<tbody>
					<?php 
						foreach ($zonas as $key => $value) { ?>
							<tr>
								<td><?php echo $value["descripcion"];?></td>
								<td>
									<input type="hidden" name="zonas[]" value="<?php echo $value["codzona"];?>">
									<input type="checkbox" class="form-check-input" id="zona_<?php echo $value["codzona"];?>" value="<?php echo $value["codzona"];?>" v-model="zonas">
								</td>
							</tr>
						<?php }
					?>
				</tbody>
			</table>
		</div>

		<div class="form-group text-center"> <br>
			<button type="submit" class="btn btn-success" v-bind:disabled="estado==1"> <i class="fa fa-save"></i> GUARDAR</button>
			<button type="button" class="btn btn-danger" v-on:click="phuyu_cerrar()"> <i class="fa fa-undo"></i> CERRAR</button>
		</div>
	</form>
</div>

<script> 
	var permisos = [];
	$('input[name^="modulos"]').each(function() {
	    permisos.push($(this).val());
	});
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_asignarzonas.js"></script>