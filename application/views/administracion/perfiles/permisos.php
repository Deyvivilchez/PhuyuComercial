<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_form" class="phuyu-velzon-form">
	<div class="card phuyu-card">
		<div class="card-body">
			<div class="phuyu-form-title">
				<div class="phuyu-form-icon"><i class="bi bi-key"></i></div>
				<div>
					<div class="text-muted small text-uppercase fw-semibold">Administracion</div>
					<h5 class="mb-0 fw-bold">Permisos del perfil</h5>
				</div>
			</div>

			<div class="alert alert-light border d-flex align-items-center justify-content-between gap-3">
				<strong>Marcar todos los modulos</strong>
				<input type="checkbox" class="form-check-input" id="marcar" v-on:change="phuyu_marcar()">
			</div>

			<form id="formulario" v-on:submit.prevent="phuyu_guardar()">
				<input type="hidden" name="codperfil" v-model="campos.codperfil">
				<?php foreach ($modulos as $value) { ?>
					<div class="phuyu-section-title">
						<i class="<?php echo $value["icono"]; ?>"></i> Modulo <?php echo $value["descripcion"]; ?>
					</div>

					<div class="phuyu-table-wrap mb-3">
						<table class="table table-hover align-middle">
							<thead>
								<tr>
									<th>Sub modulo</th>
									<th width="110" class="text-center">Ver</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($value["submodulos"] as $val) {
									foreach ($permisos as $v) {
										if ($v["codmodulo"] == $val["codmodulo"]) {
											echo '<input type="hidden" name="modulos[]" value="'.$v["codmodulo"].'">';
											break;
										}
									} ?>
									<tr>
										<td><?php echo $val["descripcion"]; ?></td>
										<td class="text-center">
											<input type="checkbox" class="form-check-input" id="modulos_<?php echo $val["codmodulo"];?>" value="<?php echo $val["codmodulo"];?>" v-model="campos.modulos">
											<input type="hidden" name="lista[]" value="<?php echo $val["codmodulo"];?>">
										</td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				<?php } ?>

				<div class="phuyu-form-actions">
					<button type="submit" class="btn btn-primary" v-bind:disabled="estado==1">
						<i class="bi bi-save me-1"></i> Guardar
					</button>
					<button type="button" class="btn btn-light" v-on:click="phuyu_cerrar()">
						<i class="bi bi-x-circle me-1"></i> Cerrar
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script>
	var permisos = [];
	$('input[name^="modulos"]').each(function() {
		permisos.push($(this).val());
	});
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_permisos.js"></script>
