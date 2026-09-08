<style>
	#phuyu_form label,
	#phuyu_form .form-label {
		font-size: 11px;
		font-weight: 800;
		color: #343a40;
		margin-bottom: 6px;
		text-transform: uppercase;
	}

	#phuyu_form .form-control,
	#phuyu_form .form-select {
		border-radius: 9px;
		min-height: 40px;
	}

	#phuyu_form .phuyu-edit-card {
		border: 1px solid rgba(64, 81, 137, .12);
		border-radius: 1rem;
		background: #fff;
		padding: 1rem;
	}

	#phuyu_form .phuyu-edit-title {
		display: flex;
		align-items: center;
		gap: .65rem;
		color: #405189;
		font-weight: 800;
		margin-bottom: 1rem;
	}

	#phuyu_form .phuyu-edit-title i {
		width: 38px;
		height: 38px;
		border-radius: 12px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		background: rgba(64, 81, 137, .1);
	}

	#phuyu_form .phuyu-btn-text-icon {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		gap: .45rem;
		border-radius: 10px;
		font-weight: 700;
	}
</style>

<div id="phuyu_form">
	<div class="phuyu-edit-card">
		<div class="phuyu-edit-title">
			<i class="bi bi-pencil-square fs-5"></i>
			<div>
				<h5 class="mb-0">Editar compra <?php echo $info[0]["seriecomprobante"]."-".$info[0]["nrocomprobante"];?></h5>
				<div class="text-muted small mt-1">
					<?php echo $info[0]["fechacomprobante"];?> · <?php echo $info[0]["razonsocial"];?>
				</div>
			</div>
		</div>

		<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">
			<input type="hidden" name="codregistro" v-model="campos.codregistro">

			<div class="row g-3">
				<div class="col-12">
					<label class="form-label">Proveedor de la compra</label>
					<select name="codproveedor" id="codproveedor" required>
						<option value="<?php echo $info[0]["codpersona"];?>"><?php echo $info[0]["razonsocial"];?></option>
					</select>
				</div>

				<div class="col-md-6">
					<label class="form-label">Fecha compra</label>
					<input type="date" class="form-control" name="fechacomprobante" id="fechacomprobante" v-model="campos.fechacomprobante" autocomplete="off" required>
				</div>

				<div class="col-md-6">
					<label class="form-label">Fecha kardex</label>
					<input type="date" class="form-control" name="fechakardex" id="fechakardex" v-model="campos.fechakardex" autocomplete="off" required>
				</div>

				<div class="col-md-6">
					<label class="form-label">Tipo comprobante</label>
					<select class="form-select" name="codcomprobantetipo" v-model="campos.codcomprobantetipo" required>
						<?php foreach ($comprobantes as $key => $value) { ?>
							<option value="<?php echo $value["codcomprobantetipo"];?>">
								<?php echo $value["descripcion"];?>
							</option>
						<?php } ?>
					</select>
				</div>

				<div class="col-md-3">
					<label class="form-label">Serie</label>
					<input type="text" class="form-control" v-model="campos.seriecomprobante">
				</div>

				<div class="col-md-3">
					<label class="form-label">Comprobante</label>
					<input type="text" class="form-control" v-model="campos.nrocomprobante">
				</div>

				<div class="col-12">
					<label class="form-label">Glosa de la compra</label>
					<input type="text" class="form-control" v-model="campos.descripcion" autocomplete="off" maxlength="250" placeholder="Referencia de la compra">
				</div>
			</div>

			<div class="border-top pt-3 mt-3 text-center">
				<button type="submit" class="btn btn-success phuyu-btn-text-icon" v-bind:disabled="estado==1">
					<i class="bi bi-save"></i>
					<span>Guardar</span>
				</button>
				<button type="button" class="btn btn-danger phuyu-btn-text-icon" v-on:click="phuyu_cerrar()">
					<i class="bi bi-x-circle"></i>
					<span>Cerrar</span>
				</button>
			</div>
		</form>
	</div>
</div>

<script>
	var phuyu_form = new Vue({
		el: "#phuyu_form",
		data: {
			estado: 0, campos: {codregistro:"<?php echo $info[0]["codkardex"];?>",codpersona:<?php echo $info[0]["codpersona"];?>,fechacomprobante: "<?php echo $info[0]["fechacomprobante"];?>",fechakardex: "<?php echo $info[0]["fechakardex"];?>",descripcion: "<?php echo $info[0]["descripcion"];?>",codcomprobantetipo:<?php echo $info[0]["codcomprobantetipo"]?>,seriecomprobante:"<?php echo $info[0]["seriecomprobante"]?>",nrocomprobante:"<?php echo $info[0]["nrocomprobante"]?>",cliente:"<?php echo $info[0]["razonsocial"];?>"}
		},
		methods: {
			phuyu_infocliente: function(codpersona){
				this.campos.codpersona = codpersona;
	        },
			phuyu_guardar: function(){
				this.estado= 1; this.campos.fechacomprobante = $("#fechacomprobante").val(); this.campos.fechakardex = $("#fechakardex").val();
				this.$http.post(url+phuyu_controller+"/editar_guardar", this.campos).then(function(data){
					if (data.body==1) {
						phuyu_sistema.phuyu_alerta("EDITADO CORRECTAMENTE", "UNA COMPRA EDITADA EN EL SISTEMA","info");
					}else{
						phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR AL REGISTRAR", "NO SE PUEDE REGISTRAR","error");
					}
					phuyu_sistema.phuyu_modulo(); this.phuyu_cerrar();
				}, function(){
					phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS","ERROR DE RED","error");
				});
			},
			phuyu_cerrar: function(){
				$(".compose").slideToggle();
			}
		}
	});
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_personas_2.js"></script>
