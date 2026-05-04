<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_form" class="phuyu-velzon-form">
	<form id="formulario" v-on:submit.prevent="phuyu_guardar()">
		<input type="hidden" name="codregistro" v-model="campos.codregistro">

		<div class="card phuyu-card">
			<div class="card-body">
				<div class="phuyu-form-title">
					<div class="phuyu-form-icon"><i class="bi bi-credit-card-2-front"></i></div>
					<div>
						<div class="text-muted small text-uppercase fw-semibold">Tesoreria</div>
						<h5 class="mb-0 fw-bold">Cuenta corriente</h5>
					</div>
				</div>

				<div class="row g-3">
					<div class="col-12">
						<label class="form-label">Socio de la cuenta</label>
						<select class="form-select selectpicker ajax" name="codpersona" v-model="campos.codpersona" id="codpersona" required data-live-search="true"> </select>
					</div>

					<div class="col-12">
						<label class="form-label">Banco de la cuenta</label>
						<select class="form-select" name="codbanco" v-model="campos.codbanco" required>
							<option value="">SELECCIONE BANCO</option>
							<?php foreach ($bancos as $key => $value) { ?>
								<option value="<?php echo $value['codbanco'];?>"><?php echo $value["descripcion"];?></option>
							<?php } ?>
						</select>
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Moneda</label>
						<select class="form-select" name="codmoneda" v-model="campos.codmoneda" required>
							<?php foreach ($monedas as $key => $value) { ?>
								<option value="<?php echo $value['codmoneda'];?>"><?php echo $value["descripcion"];?></option>
							<?php } ?>
						</select>
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Nro cta cte</label>
						<input type="text" name="nroctacte" v-model.trim="campos.nroctacte" class="form-control" required autocomplete="off" placeholder="Nro cuenta" maxlength="50">
					</div>

					<div class="col-12">
						<label class="form-label">Codigo interbancario (CCI)</label>
						<input type="text" name="descripcion" v-model.trim="campos.descripcion" class="form-control" autocomplete="off" placeholder="Descripcion" maxlength="50">
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

<script> var campos = {codregistro:"",codpersona: "",codbanco: "",codmoneda: "1",nroctacte: "",descripcion: ""}; </script>
<script src="<?php echo base_url();?>phuyu/phuyu_personas.js"></script>
<script src="<?php echo base_url();?>phuyu/phuyu_personas_2.js"> </script>
