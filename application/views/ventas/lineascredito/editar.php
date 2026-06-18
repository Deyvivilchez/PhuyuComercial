<style>
	#phuyu_form label,
	#phuyu_form .form-label {
		font-size: 11px;
		font-weight: 700;
		color: #343a40;
		margin-bottom: 6px;
		text-transform: uppercase;
	}

	#phuyu_form .form-control,
	#phuyu_form .form-select {
		border-radius: 9px;
		min-height: 38px;
		font-size: 12px;
	}

	#phuyu_form .phuyu-edit-card {
		border: 0;
		border-radius: 1rem;
		box-shadow: 0 10px 35px rgba(15, 23, 42, .06);
	}

	#phuyu_form .phuyu-title {
		display: flex;
		align-items: center;
		gap: .6rem;
		color: #405189;
		font-weight: 800;
		margin-bottom: 1.25rem;
	}

	#phuyu_form .phuyu-title i {
		width: 38px;
		height: 38px;
		border-radius: 12px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		background: rgba(64, 81, 137, .10);
	}

	#phuyu_form .phuyu-block,
	#phuyu_form .phuyu-cliente-row {
		border: 1px solid rgba(64, 81, 137, .12);
		border-radius: 1rem;
		padding: 1rem;
		background: #fff;
	}

	#phuyu_form .phuyu-block-soft {
		background: #f8fafc;
	}

	#phuyu_form .btn-add-cliente {
		height: 38px;
		border-radius: 8px;
		min-width: 58px;
	}

	#phuyu_form .phuyu-input-icon {
		position: relative;
	}

	#phuyu_form .phuyu-input-icon > i {
		position: absolute;
		left: 12px;
		top: 50%;
		transform: translateY(-50%);
		color: #8492a6;
		font-size: 13px;
		z-index: 2;
	}

	#phuyu_form .phuyu-input-icon .form-control {
		padding-left: 34px;
	}

	#phuyu_form .phuyu-actions {
		display: flex;
		justify-content: flex-end;
		flex-wrap: wrap;
		gap: .5rem;
	}

	#phuyu_form .phuyu-btn-text-icon {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		gap: .45rem;
		font-weight: 700;
		border-radius: 10px;
	}

	#phuyu_form .select2-container,
	#phuyu_form .bootstrap-select {
		width: 100% !important;
	}

	#phuyu_form .select2-container--default .select2-selection--single,
	#phuyu_form .select2-container--bootstrap4 .select2-selection--single {
		height: 38px !important;
		border-radius: 8px !important;
		border: 1px solid #ced4da !important;
		display: flex !important;
		align-items: center !important;
		background-color: #fff !important;
	}

	#phuyu_form .select2-container--default .select2-selection--single .select2-selection__rendered,
	#phuyu_form .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
		line-height: 38px !important;
		padding-left: 12px !important;
		padding-right: 30px !important;
		font-size: 12px !important;
		font-weight: 600 !important;
		color: #495057 !important;
	}

	#phuyu_form .select2-container--default .select2-selection--single .select2-selection__arrow,
	#phuyu_form .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
		height: 38px !important;
	}
</style>

<div id="phuyu_form">
	<div class="card phuyu-edit-card">
		<div class="card-body p-4">
			<div class="phuyu-title">
				<i class="bi bi-credit-card-2-front fs-5"></i>
				<h4 class="mb-0">Editar línea de crédito</h4>
			</div>

			<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">
				<input type="hidden" name="codregistro" v-model="campos.codregistro">

				<div class="phuyu-block phuyu-block-soft mb-3">
					<div class="row g-3 align-items-end">
						<div class="col-lg-6 col-md-8">
							<label class="form-label">Socio de la línea</label>
							<select class="form-control selectpicker ajax" name="codpersona" v-model="campos.codpersona" id="codpersona" required data-live-search="true" v-on:change="phuyu_infocliente()">
								<option value="<?php echo $info[0]["codpersona"];?>"><?php echo $info[0]["razonsocial"];?></option>
							</select>
						</div>

						<div class="col-lg-1 col-md-4">
							<label class="form-label d-none d-lg-block">&nbsp;</label>
							<button type="button"
								class="btn btn-primary btn-add-cliente w-100 d-flex align-items-center justify-content-center"
								title="Agregar cliente"
								onclick="if(typeof phuyu_addcliente === 'function'){ phuyu_addcliente(); }">
								<i class="bi bi-person-plus fs-5"></i>
							</button>
						</div>

						<div class="col-lg-5 col-md-12">
							<label class="form-label">Venta relacionada</label>
							<div class="input-group">
								<span class="input-group-text bg-light">
									<i class="bi bi-receipt text-primary"></i>
								</span>
								<input type="text" class="form-control fw-bold" value="<?php echo $info[0]["seriecomprobante"]."-".$info[0]["nrocomprobante"];?>" readonly>
							</div>
						</div>
					</div>
				</div>

				<div class="phuyu-cliente-row mb-3">
					<div class="row g-3 align-items-end">
						<div class="col-lg-5 col-md-12">
							<label class="form-label">Cliente para comprobante</label>
							<div class="phuyu-input-icon">
								<i class="bi bi-person"></i>
								<input type="text" class="form-control" id="cliente" v-model.trim="campos.cliente" autocomplete="off" maxlength="250" placeholder="Razón social del cliente..." required>
							</div>
						</div>

						<div class="col-lg-7 col-md-12">
							<label class="form-label">Dirección cliente</label>
							<div class="phuyu-input-icon">
								<i class="bi bi-geo-alt"></i>
								<input type="text" class="form-control" id="direccion" v-model.trim="campos.direccion" autocomplete="off" maxlength="250" placeholder="Dirección del cliente..." required>
							</div>
						</div>

						<div class="col-12">
							<label class="form-label">Glosa de la venta</label>
							<div class="phuyu-input-icon">
								<i class="bi bi-card-text"></i>
								<input type="text" class="form-control" v-model="campos.descripcion" autocomplete="off" maxlength="250" placeholder="Referencia de la venta...">
							</div>
						</div>

						<div class="col-md-6">
							<label class="form-label">Fecha venta</label>
							<div class="phuyu-input-icon">
								<i class="bi bi-calendar-check"></i>
								<input type="text" class="form-control datepicker" name="fechacomprobante" id="fechacomprobante" v-model="campos.fechacomprobante" autocomplete="off" required>
							</div>
						</div>

						<div class="col-md-6">
							<label class="form-label">Fecha kardex</label>
							<div class="phuyu-input-icon">
								<i class="bi bi-calendar2-week"></i>
								<input type="text" class="form-control datepicker" name="fechakardex" id="fechakardex" v-model="campos.fechakardex" autocomplete="off" required>
							</div>
						</div>

						<?php if ($_SESSION["phuyu_rubro"]==1) { ?>
							<div class="col-12">
								<label class="form-label">Nro placa(s)</label>
								<div class="phuyu-input-icon">
									<i class="bi bi-truck"></i>
									<input type="text" class="form-control" v-model="campos.nroplaca" autocomplete="off" maxlength="100" placeholder="Nro placa...">
								</div>
							</div>
						<?php } ?>
					</div>
				</div>

				<div class="border-top pt-3 mt-3">
					<div class="phuyu-actions">
						<button type="submit" class="btn btn-success phuyu-btn-text-icon" v-bind:disabled="estado==1">
							<i class="bi bi-save"></i>
							<span>Guardar</span>
						</button>

						<button type="button" class="btn btn-danger phuyu-btn-text-icon" v-on:click="phuyu_cerrar()">
							<i class="bi bi-x-circle"></i>
							<span>Cerrar</span>
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>

<script>
	var phuyu_form = new Vue({
		el: "#phuyu_form",
		data: {
			estado: 0,
			sunat:"<?php echo $sunat;?>",
			campos: {
				codregistro:"<?php echo $info[0]["codkardex"];?>",
				codpersona:<?php echo $info[0]["codpersona"];?>,
				fechacomprobante: "<?php echo $info[0]["fechacomprobante"];?>",
				fechakardex: "<?php echo $info[0]["fechakardex"];?>",
				nroplaca: "<?php echo $info[0]["nroplaca"];?>",
				cliente: "<?php echo $info[0]["cliente"];?>",
				direccion: "<?php echo $info[0]["direccion"];?>",
				descripcion: "<?php echo $info[0]["descripcion"];?>"
			}
		},
		methods: {
			phuyu_guardar: function(){
				this.estado = 1;
				this.campos.fechacomprobante = $("#fechacomprobante").val();
				this.campos.fechakardex = $("#fechakardex").val();

				this.$http.post(url + phuyu_controller + "/editar_guardar", this.campos).then(function(data){
					if (data.body == 1) {
						phuyu_sistema.phuyu_alerta("EDITADO CORRECTAMENTE", "UNA VENTA EDITADA EN EL SISTEMA", "info");
					}else{
						phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR AL REGISTRAR", "NO SE PUEDE REGISTRAR", "error");
					}
					phuyu_sistema.phuyu_modulo();
					this.phuyu_cerrar();
				}, function(){
					phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS", "ERROR DE RED", "error");
					this.estado = 0;
				});
			},

			phuyu_infocliente: function(){
				this.campos.cliente = $("#codpersona option:selected").text();
				this.$http.get(url + "ventas/clientes/infocliente/" + this.campos.codpersona).then(function(data){
					this.campos.direccion = data.body[0].direccion;
				});
	        },

			phuyu_cerrar: function(){
				$(".compose").slideToggle();
			}
		}
	});

	$(".datepicker").datetimepicker({format: 'YYYY-MM-DD'});
</script>

<script src="<?php echo base_url();?>phuyu/phuyu_personas_2.js"></script>