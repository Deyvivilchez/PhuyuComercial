<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_form" class="phuyu-velzon-form">
	<form id="formulario" v-on:submit.prevent="phuyu_guardar()">
		<input type="hidden" name="codempresa" v-model="campos.codempresa">

		<div class="card phuyu-card">
			<div class="card-body">
				<div class="phuyu-form-title">
					<div class="phuyu-form-icon"><i class="bi bi-building-gear"></i></div>
					<div>
						<div class="text-muted small text-uppercase fw-semibold">Administracion</div>
						<h5 class="mb-0 fw-bold">Configuracion tributaria</h5>
					</div>
				</div>

				<div class="row g-3">
					<div class="col-12">
						<label class="form-label">Rubro de empresa Phuyu Peru</label>
						<select class="form-select" name="rubro" v-model="campos.rubro">
							<?php foreach ($rubros as $key => $value) { ?>
								<option value="<?php echo $value["codrubro"]?>"><?php echo $value["descripcion"]?></option>
							<?php } ?>
						</select>
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Departamento</label>
						<input type="text" class="form-control" name="departamento" v-model="campos.departamento" placeholder="Departamento" autocomplete="off">
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Provincia</label>
						<input type="text" class="form-control" name="provincia" v-model="campos.provincia" placeholder="Provincia" autocomplete="off">
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Distrito</label>
						<input type="text" class="form-control" name="distrito" v-model="campos.distrito" placeholder="Distrito" autocomplete="off">
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Ubigeo</label>
						<input type="text" class="form-control" name="ubigeo" v-model="campos.ubigeo" placeholder="Ubigeo" maxlength="6" autocomplete="off">
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Email envio</label>
						<input type="email" class="form-control" name="envioemail" v-model="campos.envioemail" placeholder="Email" autocomplete="off">
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Clave email</label>
						<input type="text" class="form-control" name="claveemail" v-model="campos.claveemail" placeholder="Clave email" autocomplete="off">
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Usuario SOL</label>
						<input type="text" class="form-control" name="usuariosol" v-model.trim="campos.usuariosol" placeholder="Usuario sol" autocomplete="off">
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Clave SOL</label>
						<input type="text" class="form-control" name="clavesol" v-model="campos.clavesol" placeholder="Clave sol" autocomplete="off">
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Certificado PFX</label>
						<input type="file" class="form-control" name="certificado_pfx" accept=".pfx">
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Clave certificado</label>
						<input type="text" class="form-control" name="certificado_clave" v-model="campos.certificado_clave" placeholder="Clave certificado" autocomplete="off">
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Servicio SUNAT/OSE</label>
						<select class="form-select" name="sunatose" v-model="campos.sunatose">
							<option value="0">SERVICIO SUNAT</option>
							<option value="1">SERVICIO OSE</option>
						</select>
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Estado web service</label>
						<select class="form-select" name="serviceweb" v-model="campos.serviceweb">
							<option value="0">PRODUCCION SUNAT/OSE</option>
							<option value="1">BETA HOMOLAGACION</option>
						</select>
					</div>
				</div>

				<div class="phuyu-section-title">URLs de facturacion</div>
				<div class="row g-3">
					<div class="col-12" v-if="campos.sunatose==0">
						<label class="form-label" v-if="campos.serviceweb==0">URL web service SUNAT</label>
						<label class="form-label" v-if="campos.serviceweb!=0">URL web service SUNAT demo</label>
						<input v-if="campos.serviceweb==0" type="text" name="servicesunat" v-model.trim="campos.servicesunat" class="form-control" autocomplete="off" placeholder="Web Service SUNAT" maxlength="100">
						<input v-if="campos.serviceweb!=0" type="text" name="servicesunat_demo" v-model.trim="campos.servicesunat_demo" class="form-control" autocomplete="off" placeholder="Web Service SUNAT Demo" maxlength="100">
					</div>

					<div class="col-12" v-else="campos.sunatose!=1">
						<label class="form-label" v-if="campos.serviceweb==0">URL web service OSE</label>
						<label class="form-label" v-if="campos.serviceweb!=0">URL web service OSE demo</label>
						<input v-if="campos.serviceweb==0" type="text" name="serviceose" v-model.trim="campos.serviceose" class="form-control" autocomplete="off" placeholder="Web Service OSE" maxlength="100">
						<input v-if="campos.serviceweb!=0" type="text" name="serviceose_demo" v-model.trim="campos.serviceose_demo" class="form-control" autocomplete="off" placeholder="Web Service OSE Demo" maxlength="100">
					</div>

					<div class="col-12" v-if="campos.sunatose==0">
						<label class="form-label" v-if="campos.serviceweb==0">URL web service SUNAT guia</label>
						<label class="form-label" v-if="campos.serviceweb!=0">URL web service SUNAT guia demo</label>
						<input v-if="campos.serviceweb==0" type="text" name="servicesunatguia" v-model.trim="campos.servicesunatguia" class="form-control" autocomplete="off" placeholder="Web Service SUNAT Guia" maxlength="100">
						<input v-if="campos.serviceweb!=0" type="text" name="servicesunatguia_demo" v-model.trim="campos.servicesunatguia_demo" class="form-control" autocomplete="off" placeholder="Web Service SUNAT Guia Demo" maxlength="100">
					</div>

					<div class="col-12" v-else="campos.sunatose!=1">
						<label class="form-label" v-if="campos.serviceweb==0">URL web service OSE guia</label>
						<label class="form-label" v-if="campos.serviceweb!=0">URL web service OSE guia demo</label>
						<input v-if="campos.serviceweb==0" type="text" name="serviceoseguia" v-model.trim="campos.serviceoseguia" class="form-control" autocomplete="off" placeholder="Web Service OSE Guia" maxlength="100">
						<input v-if="campos.serviceweb!=0" type="text" name="serviceoseguia_demo" v-model.trim="campos.serviceoseguia_demo" class="form-control" autocomplete="off" placeholder="Web Service OSE Guia Demo" maxlength="100">
					</div>

					<div class="col-12" v-if="campos.sunatose==0">
						<label class="form-label" v-if="campos.serviceweb==0">URL web service SUNAT retencion</label>
						<label class="form-label" v-if="campos.serviceweb!=0">URL web service SUNAT retencion demo</label>
						<input v-if="campos.serviceweb==0" type="text" name="servicesunatretencion" v-model.trim="campos.servicesunatretencion" class="form-control" autocomplete="off" placeholder="Web Service SUNAT Retencion" maxlength="100">
						<input v-if="campos.serviceweb!=0" type="text" name="servicesunatretencion_demo" v-model.trim="campos.servicesunatretencion_demo" class="form-control" autocomplete="off" placeholder="Web Service SUNAT Retencion Demo" maxlength="100">
					</div>

					<div class="col-12" v-else="campos.sunatose!=1">
						<label class="form-label" v-if="campos.serviceweb==0">URL web service OSE retencion</label>
						<label class="form-label" v-if="campos.serviceweb!=0">URL web service OSE retencion demo</label>
						<input v-if="campos.serviceweb==0" type="text" name="serviceoseretencion" v-model.trim="campos.serviceoseretencion" class="form-control" autocomplete="off" placeholder="Web Service OSE Retencion" maxlength="100">
						<input v-if="campos.serviceweb!=0" type="text" name="serviceoseretencion_demo" v-model.trim="campos.serviceoseretencion_demo" class="form-control" autocomplete="off" placeholder="Web Service OSE Retencion Demo" maxlength="100">
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

<script>
	var campos = {
		codempresa:"<?php echo $empresa[0]["codempresa"];?>",
		rubro:"<?php echo $empresa[0]["rubro"];?>",
		departamento:"<?php echo $empresa[0]["departamento"];?>",
		provincia:"<?php echo $empresa[0]["provincia"];?>",
		distrito:"<?php echo $empresa[0]["distrito"];?>",
		ubigeo:"<?php echo $empresa[0]["ubigeo"];?>",
		usuariosol:"<?php echo $service[0]["usuariosol"];?>",
		clavesol:"<?php echo $service[0]["clavesol"];?>",
		envioemail:"<?php echo $service[0]["envioemail"];?>",
		claveemail:"<?php echo $service[0]["claveemail"];?>",
		certificado_clave:"<?php echo $service[0]["certificado_clave"];?>",
		sunatose:"<?php echo $service[0]["sunatose"];?>",
		serviceweb:"<?php echo $service[0]["serviceweb"];?>",
		servicesunat:"<?php echo $service[0]['servicesunat'];?>",
		servicesunat_demo:"<?php echo $service[0]['servicesunat_demo'];?>",
		serviceose:"<?php echo $service[0]['serviceose'];?>",
		serviceose_demo:"<?php echo $service[0]['serviceose_demo'];?>",
		servicesunatguia:"<?php echo $service[0]['servicesunatguia'];?>",
		servicesunatguia_demo:"<?php echo $service[0]['servicesunatguia_demo'];?>",
		serviceoseguia:"<?php echo $service[0]['serviceoseguia'];?>",
		serviceoseguia_demo:"<?php echo $service[0]['serviceoseguia_demo'];?>",
		servicesunatretencion:"<?php echo $service[0]['servicesunatretencion'];?>",
		servicesunatretencion_demo:"<?php echo $service[0]['servicesunatretencion_demo'];?>",
		serviceoseretencion:"<?php echo $service[0]['serviceoseretencion'];?>",
		serviceoseretencion_demo:"<?php echo $service[0]['serviceoseretencion_demo'];?>"
	};
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_empresa/editar.js"></script>
