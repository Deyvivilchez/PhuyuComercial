<style>
	#phuyu_form.phuyu-almacen-form {
		color: #212529;
	}
	#phuyu_form.phuyu-almacen-form .phuyu-edit-card {
		border: 1px solid #e9ebec;
		border-radius: 8px;
		box-shadow: 0 1px 2px rgba(56, 65, 74, 0.08);
		overflow: hidden;
	}
	#phuyu_form.phuyu-almacen-form .phuyu-form-header {
		align-items: center;
		background: #f3f6f9;
		border: 1px solid #e9ebec;
		border-radius: 8px;
		display: flex;
		gap: 12px;
		margin-bottom: 18px;
		padding: 14px 16px;
	}
	#phuyu_form.phuyu-almacen-form .phuyu-form-icon {
		align-items: center;
		background: rgba(240, 101, 72, 0.12);
		border-radius: 8px;
		color: #f06548;
		display: inline-flex;
		font-size: 22px;
		height: 44px;
		justify-content: center;
		width: 44px;
	}
	#phuyu_form.phuyu-almacen-form .phuyu-form-meta {
		display: flex;
		flex-wrap: wrap;
		gap: 8px;
		margin-left: auto;
	}
	#phuyu_form.phuyu-almacen-form .phuyu-form-meta .badge {
		border: 1px solid #d9e2ef;
		color: #495057;
		font-size: 12px;
		font-weight: 600;
		padding: 7px 10px;
	}
	#phuyu_form.phuyu-almacen-form label {
		color: #495057;
		font-size: 12px;
		font-weight: 700;
		letter-spacing: .2px;
		margin-bottom: 7px;
		text-transform: uppercase;
	}
	#phuyu_form.phuyu-almacen-form .form-group {
		margin-bottom: 15px;
	}
	#phuyu_form.phuyu-almacen-form .form-control,
	#phuyu_form.phuyu-almacen-form .form-select,
	#phuyu_form.phuyu-almacen-form select {
		border: 1px solid #d9e2ef;
		border-radius: 6px;
		box-shadow: none;
		min-height: 38px;
		width: 100%;
	}
	#phuyu_form.phuyu-almacen-form .select2-container {
		width: 100% !important;
	}
	#phuyu_form.phuyu-almacen-form .select2-container--default .select2-selection--single {
		border: 1px solid #d9e2ef;
		border-radius: 6px;
		min-height: 38px;
	}
	#phuyu_form.phuyu-almacen-form .select2-container--default .select2-selection--single .select2-selection__rendered {
		line-height: 36px;
	}
	#phuyu_form.phuyu-almacen-form .select2-container--default .select2-selection--single .select2-selection__arrow {
		height: 36px;
	}
	#phuyu_form.phuyu-almacen-form .phuyu-form-actions {
		border-top: 1px solid #e9ebec;
		display: flex;
		flex-wrap: wrap;
		gap: 8px;
		justify-content: center;
		margin-top: 6px;
		padding-top: 18px;
	}
	@media (max-width: 767.98px) {
		#phuyu_form.phuyu-almacen-form .phuyu-form-header {
			align-items: flex-start;
			flex-direction: column;
		}
		#phuyu_form.phuyu-almacen-form .phuyu-form-meta {
			margin-left: 0;
		}
	}
</style>

<div id="phuyu_form" class="phuyu-almacen-form">
	<div class="card phuyu-edit-card">
		<div class="card-body">
			<div class="phuyu-form-header">
				<div class="phuyu-form-icon">
					<i class="bi bi-box-arrow-up"></i>
				</div>
				<div>
					<p class="text-muted text-uppercase mb-1">Editar salida</p>
					<h5 class="mb-0">NROSALIDA: <?php echo $info[0]["seriecomprobante"]."-".$info[0]["nrocomprobante"];?></h5>
				</div>
				<div class="phuyu-form-meta">
					<span class="badge bg-white">Fecha comprobante: <?php echo $info[0]["fechacomprobante"];?></span>
					<span class="badge bg-white">Fecha kardex: <?php echo $info[0]["fechakardex"];?></span>
				</div>
			</div>

			<form id="formulario" v-on:submit.prevent="phuyu_guardar()">
				<input type="hidden" name="codregistro" v-model="campos.codregistro">
				<div class="row form-group">
					<div class="col-md-12">
						<label>RESPONSABLE</label>
							<select name="codproveedor" id="codproveedor" required>
								<option value="<?php echo $info[0]["codpersona"];?>"><?php echo $info[0]["cliente"];?></option>
							</select>
					</div>
				</div>
				<div class="row form-group">
					<div class="col-md-12">
						<label>TIPO MOVIMIENTO</label>
							<select class="form-select" name="codmovimientotipo" v-model="campos.codmovimientotipo" required>
								<?php
									foreach ($movimientos as $key => $value) { ?>
										<option value="<?php echo $value["codmovimientotipo"];?>"><?php echo $value["descripcion"];?></option>
									<?php }
								?>
							</select>
					</div>
				</div>

				<div class="row form-group">
					<div class="col-md-12">
						<label>DESCRIPCION DE LA SALIDA</label>
						<input type="text" class="form-control" v-model="campos.descripcion" autocomplete="off" maxlength="250" placeholder="Referencia de la venta . . .">
					</div>
				</div>
				<div class="row form-group">
					<div class="col-md-6">
						<label>FECHA COMPROBANTE</label>
							<input type="date" class="form-control" name="fechacomprobante" id="fechacomprobante" v-model="campos.fechacomprobante" autocomplete="off" required>
					</div>
					<div class="col-md-6">
						<label>FECHA KARDEX</label>
							<input type="date" class="form-control" name="fechakardex" id="fechakardex" v-model="campos.fechakardex" autocomplete="off" required>
					</div>
				</div>

				<div class="phuyu-form-actions">
					<button type="submit" class="btn btn-success" v-bind:disabled="estado==1">
						<i class="bi bi-save me-1"></i> Guardar
					</button>
					<button type="button" class="btn btn-light" v-on:click="phuyu_cerrar()">
						<i class="bi bi-x-lg me-1"></i> Cerrar
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script>
	var phuyu_form = new Vue({
		el: "#phuyu_form",
		data: {
			estado: 0, campos: {codregistro:"<?php echo $info[0]["codkardex"];?>",codpersona:<?php echo $info[0]["codpersona"];?>,codmovimientotipo:<?php echo $info[0]["codmovimientotipo"];?>,fechacomprobante: "<?php echo $info[0]["fechacomprobante"];?>",fechakardex: "<?php echo $info[0]["fechakardex"];?>",descripcion: "<?php echo $info[0]["descripcion"];?>",cliente:"<?php echo $info[0]["cliente"];?>"}
		},
		methods: {
			phuyu_infocliente: function(codpersona){
				this.campos.codpersona = codpersona;
			},
			phuyu_guardar: function(){
				this.estado= 1; this.campos.fechacomprobante = $("#fechacomprobante").val(); this.campos.fechakardex = $("#fechakardex").val();
				this.$http.post(url+phuyu_controller+"/editar_guardar", this.campos).then(function(data){
					if (data.body==1) {
						phuyu_sistema.phuyu_alerta("EDITADO CORRECTAMENTE", "UNA SALIDA EDITADO EN EL SISTEMA","info");
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

	$(".datepicker").datetimepicker({format: 'YYYY-MM-DD'});
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_personas_2.js"> </script>
