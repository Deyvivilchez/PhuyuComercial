<style>
	#phuyu_form.phuyu-cliente-rapido {
		padding: .25rem .15rem 0;
	}

	#phuyu_form.phuyu-cliente-rapido .phuyu-mini-section {
		border: 1px solid rgba(64, 81, 137, .12);
		border-radius: .85rem;
		background: #fff;
		padding: .85rem;
		margin-bottom: .85rem;
	}

	#phuyu_form.phuyu-cliente-rapido .phuyu-mini-title {
		display: flex;
		align-items: center;
		gap: .5rem;
		color: #405189;
		font-size: .75rem;
		font-weight: 800;
		letter-spacing: .04em;
		text-transform: uppercase;
		margin-bottom: .8rem;
	}

	#phuyu_form.phuyu-cliente-rapido .phuyu-mini-title i {
		width: 30px;
		height: 30px;
		border-radius: 10px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		background: rgba(64, 81, 137, .1);
		color: #405189;
		font-size: 1rem;
	}

	#phuyu_form.phuyu-cliente-rapido label {
		font-size: .7rem;
		font-weight: 800;
		text-transform: uppercase;
		letter-spacing: .03em;
		color: #495057;
		margin-bottom: .35rem;
	}

	#phuyu_form.phuyu-cliente-rapido .form-control,
	#phuyu_form.phuyu-cliente-rapido .form-select {
		min-height: 41px;
		border-radius: 9px;
		border-color: rgba(64, 81, 137, .18);
	}

	#phuyu_form.phuyu-cliente-rapido .btn-consultar,
	#phuyu_form.phuyu-cliente-rapido .phuyu-action-btn {
		min-height: 41px;
		border-radius: 9px;
		font-weight: 800;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		gap: .35rem;
	}

	#phuyu_form.phuyu-cliente-rapido .phuyu-actions {
		position: sticky;
		bottom: 0;
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: .5rem;
		padding: .75rem 0 .25rem;
		background: #fff;
		border-top: 1px solid rgba(64, 81, 137, .12);
	}
</style>

<div id="phuyu_form" class="phuyu-cliente-rapido">
	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar_1()">
		<input type="hidden" name="codsociotipo" v-model="campos.codsociotipo">
		<input type="hidden" name="">

		<div class="phuyu-mini-section">
			<div class="phuyu-mini-title"><i class="bi bi-person-vcard"></i> Documento</div>
			<div class="row g-2 align-items-end">
				<div class="col-12 col-lg-5">
					<label>Tipo documento</label>
					<select class="form-select" name="coddocumentotipo" v-model="campos.coddocumentotipo" required v-on:change="phuyu_tipodocumento()" ref="coddocumentotipo">
						<option value="">SELECCIONE</option>
						<?php foreach ($tipodocumentos as $key => $value) { ?>
							<option value="<?php echo $value['coddocumentotipo'];?>"><?php echo $value["descripcion"];?></option>
						<?php } ?>
					</select>
				</div>
				<div class="col-8 col-lg-5">
					<label>Documento</label>
					<input type="text" class="form-control line-danger" name="documento" v-model="campos.documento" id="documento" placeholder="Numero" required autocomplete="off" minlength="8" maxlength="8" ref="documento">
				</div>
				<div class="col-4 col-lg-2">
					<label>&nbsp;</label>
					<button type="button" class="btn btn-primary btn-consultar w-100" v-on:click="phuyu_consultar();" title="Consultar documento">
						<i class="bi bi-search"></i>
					</button>
				</div>
			</div>
		</div>

		<div class="phuyu-mini-section">
			<div class="phuyu-mini-title"><i class="bi bi-building"></i> Datos del cliente</div>
			<div class="row g-2">
				<div class="col-12">
					<label>Razon social</label>
					<input type="text" class="form-control" name="razonsocial" v-model="campos.razonsocial" placeholder="Razon social" required autocomplete="off">
				</div>
				<div class="col-12">
					<label>Nombre comercial</label>
					<input type="text" class="form-control" name="nombrecomercial" v-model="campos.nombrecomercial" placeholder="Nombre comercial" autocomplete="off">
				</div>
				<div class="col-12">
					<label>Direccion</label>
					<input type="text" class="form-control" name="direccion" v-model="campos.direccion" placeholder="Direccion" required autocomplete="off">
				</div>
			</div>
		</div>

		<div class="phuyu-mini-section">
			<div class="phuyu-mini-title"><i class="bi bi-telephone"></i> Contacto</div>
			<div class="row g-2">
				<div class="col-12">
					<label>Email</label>
					<input type="text" class="form-control" name="email" v-model="campos.email" placeholder="Email" autocomplete="off">
				</div>
				<div class="col-12 col-md-6">
					<label>Telf./Cel.</label>
					<input type="number" class="form-control" name="telefono" v-model="campos.telefono" placeholder="Telf./Cel." autocomplete="off">
				</div>
				<div class="col-12 col-md-6">
					<label>Sexo / Empresa</label>
					<select class="form-select" name="sexo" v-model="campos.sexo" required>
						<option value="">SELECCIONE</option>
						<option value="M">MASCULINO</option>
						<option value="F">FEMENINO</option>
						<option value="E">EMPRESA</option>
					</select>
				</div>
			</div>
		</div>

		<div class="phuyu-mini-section" v-if="campos.rubro==5">
			<div class="phuyu-mini-title"><i class="bi bi-geo-alt"></i> Ubicacion</div>
			<div class="row g-2">
				<div class="col-12">
					<label>Departamento</label>
					<select class="form-select" name="departamento" v-model="campos.departamento" required v-on:change="phuyu_provincias()">
						<option value="">SELECCIONE</option>
						<?php foreach ($departamentos as $key => $value) { ?>
							<option value="<?php echo $value['ubidepartamento'];?>"><?php echo $value["departamento"];?></option>
						<?php } ?>
					</select>
				</div>
				<div class="col-12 col-md-6">
					<label>Provincia</label>
					<select class="form-select" name="provincia" v-model="campos.provincia" id="provincia" required v-on:change="phuyu_distritos()">
						<option value="">SELECCIONE</option>
					</select>
				</div>
				<div class="col-12 col-md-6">
					<label>Distrito</label>
					<select class="form-select" name="codubigeo" v-model="campos.codubigeo" id="codubigeo" required v-on:change="phuyu_zonas()">
						<option value="">SELECCIONE</option>
					</select>
				</div>
				<div class="col-12">
					<label>Zona</label>
					<select class="form-select" name="codzona" v-model="campos.codzona" id="codzona" required>
						<option value="">SELECCIONE</option>
					</select>
				</div>
			</div>
		</div>

		<div class="phuyu-actions">
			<button type="button" class="btn btn-light phuyu-action-btn" v-on:click="phuyu_cerrar()">
				<i class="bi bi-x-lg"></i> Cerrar
			</button>
			<button type="submit" class="btn btn-success phuyu-action-btn" v-bind:disabled="estado==1">
				<i class="bi bi-check2-circle"></i> Guardar
			</button>
		</div>
	</form>
</div>

<script> var campos = {codsociotipo: "1",
coddocumentotipo: "2",documento: "",razonsocial: "",nombrecomercial:"",
direccion: "",email: "",telefono: "",sexo: "M",departamento: 0,provincia: 0,codubigeo: 0,codpatrocinador:0,
codzona:0,rubro:<?php echo $_SESSION["phuyu_rubro"];?>}; 

	if (typeof AcornIcons !== 'undefined') {
      new AcornIcons().replace();
    }
    if (typeof Icons !== 'undefined') {
      const icons = new Icons();
    }

</script>
<script src="<?php echo base_url();?>phuyu/phuyu_personas_1.js"></script>
