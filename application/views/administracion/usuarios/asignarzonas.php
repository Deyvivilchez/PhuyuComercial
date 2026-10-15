<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_form" class="phuyu-velzon-form">
	<form id="formulario" v-on:submit.prevent="phuyu_guardar()">
		<input type="hidden" name="codusuario" v-model="campos.codusuario">

		<div class="card phuyu-card">
			<div class="card-body">
				<div class="phuyu-form-title">
					<div class="phuyu-form-icon"><i class="bi bi-geo-alt"></i></div>
					<div>
						<div class="text-muted small text-uppercase fw-semibold">Administracion</div>
						<h5 class="mb-0 fw-bold">Zonas del usuario</h5>
					</div>
				</div>

				<div class="phuyu-table-wrap">
					<table class="table table-hover align-middle">
						<thead>
							<tr>
								<th>Zona</th>
								<th width="110" class="text-center">Permiso</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($zonas as $key => $value) { ?>
								<tr>
									<td><?php echo $value["descripcion"];?></td>
									<td class="text-center">
										<input type="hidden" name="zonas[]" value="<?php echo $value["codzona"];?>">
										<input type="checkbox" class="form-check-input" id="zona_<?php echo $value["codzona"];?>" value="<?php echo $value["codzona"];?>" v-model="zonas">
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>

				<div class="phuyu-form-actions">
					<button type="submit" class="btn btn-primary" v-bind:disabled="estado==1">
						<i class="bi bi-save me-1"></i> Guardar
					</button>
					<button type="button" class="btn btn-light" v-on:click="phuyu_cerrar()">
						<i class="bi bi-arrow-left me-1"></i> Cerrar
					</button>
				</div>
			</div>
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
