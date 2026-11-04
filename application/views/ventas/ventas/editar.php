<div id="phuyu_form">
	<div class="p-2">
		<h6 class="mb-1"><b>COMPROBANTE:</b> <?php echo $info[0]["seriecomprobante"]."-".$info[0]["nrocomprobante"];?></h6>
		<h6 class="mb-1"><b>CLIENTE:</b> <?php echo $info[0]["cliente"];?></h6>
		<h6 class="mb-0"><b>FECHA KARDEX:</b> <?php echo $info[0]["fechakardex"];?> <span class="text-muted">(no se modifica)</span></h6>
	</div>

	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">
		<input type="hidden" name="codregistro" v-model="campos.codregistro">

		<div class="alert alert-warning mt-3 mb-3">
			Solo se actualiza la fecha de venta del comprobante. La fecha kardex y caja se mantienen igual.
		</div>

		<div class="row form-group">
			<div class="col-md-6">
				<label>FECHA VENTA</label>
				<input type="date" class="form-control" name="fechacomprobante" id="fechacomprobante" v-model="campos.fechacomprobante" autocomplete="off" required>
			</div>
			<div class="col-md-6">
				<label>FECHA KARDEX</label>
				<input type="date" class="form-control bg-light text-muted" v-model="campos.fechakardex" disabled tabindex="-1">
			</div>
		</div>

		<div class="ln_solid"></div>
		<div class="form-group" align="center">
			<div class="alert alert-danger" v-if="sunat==1">El comprobante ya fue enviado a SUNAT. No se puede cambiar la fecha.</div>
			<button type="submit" class="btn btn-success" v-if="sunat==0" v-bind:disabled="estado==1">
				<i class="fa fa-save"></i> GUARDAR FECHA
			</button>
			<button type="button" class="btn btn-danger" v-on:click="phuyu_cerrar()">CERRAR</button>
		</div>
	</form>
</div>

<script>
	var phuyu_form = new Vue({
		el: "#phuyu_form",
		data: {
			estado: 0,
			sunat: "<?php echo $sunat;?>",
			campos: {
				codregistro: "<?php echo $info[0]["codkardex"];?>",
				fechacomprobante: "<?php echo $info[0]["fechacomprobante"];?>",
				fechakardex: "<?php echo $info[0]["fechakardex"];?>"
			}
		},
		methods: {
			phuyu_guardar: function(){
				this.estado = 1;
				this.campos.fechacomprobante = $("#fechacomprobante").val();
				this.$http.post(url+phuyu_controller+"/editar_guardar", this.campos).then(function(data){
					if (data.body==1) {
						phuyu_sistema.phuyu_alerta("FECHA ACTUALIZADA", "Se actualizo solo la fecha de venta del comprobante","info");
						phuyu_sistema.phuyu_modulo(); this.phuyu_cerrar();
					}else{
						var mensaje = data.body && data.body.mensaje ? data.body.mensaje : "No se pudo actualizar la fecha";
						phuyu_sistema.phuyu_alerta("NO SE PUEDE ACTUALIZAR", mensaje, "error");
						this.estado = 0;
					}
				}, function(){
					phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS","ERROR DE RED","error");
					this.estado = 0;
				});
			},
			phuyu_cerrar: function(){
				$(".compose").slideToggle();
			}
		}
	});
</script>
