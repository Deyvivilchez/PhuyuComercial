<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_form" class="phuyu-velzon-form">
	<form id="formulario" v-on:submit.prevent="phuyu_guardar()">
		<input type="hidden" name="codregistro" v-model="campos.codregistro">

		<div class="card phuyu-card">
			<div class="card-body">
				<div class="phuyu-form-title">
					<div class="phuyu-form-icon"><i class="bi bi-currency-exchange"></i></div>
					<div>
						<div class="text-muted small text-uppercase fw-semibold">Tesoreria</div>
						<h5 class="mb-0 fw-bold">Tipo de cambio</h5>
					</div>
				</div>

				<div class="row g-3">
					<div class="col-12 col-md-6">
						<label class="form-label">Moneda</label>
						<select class="form-select" name="codmoneda" v-model="campos.codmoneda" required>
							<?php foreach ($monedas as $key => $value) { ?>
								<option value="<?php echo $value['codmoneda'];?>"><?php echo $value["descripcion"];?></option>
							<?php } ?>
						</select>
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Fecha</label>
						<input type="hidden" id="fecha_ref" value="<?php echo date('Y-m-d');?>">
						<input type="text" class="form-control datepicker" name="fecha" id="fecha" v-model="campos.fecha" autocomplete="off" required v-on:blur="phuyu_fecha()">
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Cambio compra</label>
						<input type="number" step="0.01" name="compra" v-model.number="campos.compra" class="form-control" required autocomplete="off">
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Cambio venta</label>
						<input type="number" step="0.01" name="venta" v-model.number="campos.venta" class="form-control" required autocomplete="off">
					</div>
				</div>

				<div class="phuyu-form-actions">
					<button type="submit" class="btn btn-primary" v-bind:disabled="estado==1">
						<i class="bi bi-save me-1"></i> Guardar
					</button>
					<button type="button" class="btn btn-light" v-on:click="phuyu_cerrar()">
						<i class="bi bi-x-circle me-1"></i> Cerrar
					</button>
				</div>
			</div>
		</div>
	</form>
</div>

<script> var campos = {codregistro:"",codmoneda: "2",fecha:$("#fecha_ref").val(),compra: "",venta: ""}; </script>
<script src="<?php echo base_url();?>phuyu/phuyu_caja/tipocambios.js"></script>
<script>
	$(".datepicker").datetimepicker({format: 'YYYY-MM-DD'});
</script>
