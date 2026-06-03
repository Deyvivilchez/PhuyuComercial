<div id="phuyu_form">
	<div style="padding:0px 10px;">
		<h6><b>NROVENTA:</b> <?php echo $info[0]["seriecomprobante"]."-".$info[0]["nrocomprobante"];?> | <b>FECHA VENTA:</b> <?php echo $info[0]["fechacomprobante"];?> </h6>
		<h6><b>CLIENTE:</b> <?php echo $info[0]["razonsocial"];?></h6>
		<h6><b>DIRECCION:</b> <?php echo $info[0]["direccion"];?></h6>
	</div>

	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">
		<br> <input type="hidden" name="codregistro" v-model="campos.codregistro">
		<div class="row form-group">
			<div class="col-xs-12">
				<label>CLIENTE DE LA VENTA</label>
	        	<select class="form-control selectpicker ajax" name="codpersona" v-model="campos.codpersona" id="codpersona" required data-live-search="true" v-on:change="phuyu_infocliente()"> 
	        		<option value="<?php echo $info[0]["codpersona"];?>"><?php echo $info[0]["razonsocial"];?></option>
	        	</select>
			</div>
		</div>
		<div class="row form-group">
			<div class="col-xs-12">
				<label>CLIENTE PARA COMPROBANTE</label>
				<input type="text" class="form-control" id="cliente" v-model.trim="campos.cliente" autocomplete="off" maxlength="250" placeholder="Razon social del cliente . . ." required>
			</div>
		</div>
		<div class="row form-group">
			<div class="col-xs-12">
				<label> DIRECCION CLIENTE</label>
				<input type="text" class="form-control" id="direccion" v-model.trim="campos.direccion" autocomplete="off" maxlength="250" placeholder="Direccion del cliente . . ." required>
			</div>
		</div>

		<div class="row form-group">
			<div class="col-md-12">
				<label>GLOSA DE LA VENTA</label>
				<input type="text" class="form-control" v-model="campos.descripcion" autocomplete="off" maxlength="250" placeholder="Referencia de la venta . . .">
			</div>
		</div>
		<div class="row form-group">
			<div class="col-md-6">
				<label>FECHA VENTA</label>
    			<input type="text" class="form-control datepicker" name="fechacomprobante" id="fechacomprobante" v-model="campos.fechacomprobante" autocomplete="off" required>
			</div>
			<div class="col-md-6">
				<label>FECHA KARDEX</label>
    			<input type="text" class="form-control datepicker" name="fechakardex" id="fechakardex" v-model="campos.fechakardex" autocomplete="off" required>
			</div>
		</div>

		<?php
			if ($_SESSION["phuyu_rubro"]==1) { ?>
				<div class="row form-group" >
					<div class="col-md-12">
						<label>NRO PLACAS(S)</label>
						<input type="text" class="form-control" v-model="campos.nroplaca" autocomplete="off" maxlength="100" placeholder="Nro placa . . .">
					</div>
				</div>
			<?php }
		?>

		<div class="ln_solid"></div>
		<div class="form-group" align="center">
			<!-- <div class="alert alert-danger" v-if="sunat==1">EL COMPROBANTE YA FUE ENVIADO A SUNAT - NO PUEDES EDITAR LO SENTIMOS</div>
			<button type="submit" class="btn btn-success" v-if="sunat==0" v-bind:disabled="estado==1"> <i class="fa fa-save"></i> GUARDAR </button> -->

			<button type="submit" class="btn btn-success" v-bind:disabled="estado==1"> <i class="fa fa-save"></i> GUARDAR </button>
			<button type="button" class="btn btn-danger" v-on:click="phuyu_cerrar()">CERRAR</button>
		</div>
	</form>
</div>

<script>
	var phuyu_form = new Vue({
		el: "#phuyu_form",
		data: {
			estado: 0, sunat:"<?php echo $sunat;?>", campos: {codregistro:"<?php echo $info[0]["codkardex"];?>",codpersona:<?php echo $info[0]["codpersona"];?>,fechacomprobante: "<?php echo $info[0]["fechacomprobante"];?>",fechakardex: "<?php echo $info[0]["fechakardex"];?>",nroplaca: "<?php echo $info[0]["nroplaca"];?>",cliente: "<?php echo $info[0]["cliente"];?>",direccion: "<?php echo $info[0]["direccion"];?>",descripcion: "<?php echo $info[0]["descripcion"];?>"}
		},
		methods: {
			phuyu_guardar: function(){
				this.estado= 1; this.campos.fechacomprobante = $("#fechacomprobante").val(); this.campos.fechakardex = $("#fechakardex").val();
				this.$http.post(url+phuyu_controller+"/editar_guardar", this.campos).then(function(data){
					if (data.body==1) {
						phuyu_sistema.phuyu_alerta("EDITADO CORRECTAMENTE", "UNA VENTA EDITADA EN EL SISTEMA","info");
					}else{
						phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR AL REGISTRAR", "NO SE PUEDE REGISTRAR","error");
					}
					phuyu_sistema.phuyu_modulo(); this.phuyu_cerrar();
				}, function(){
					phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS","ERROR DE RED","error");
				});
			},
			phuyu_infocliente: function(){
				this.campos.cliente = $("#codpersona option:selected").text();
				this.$http.get(url+"ventas/clientes/infocliente/"+this.campos.codpersona).then(function(data){
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

<script src="<?php echo base_url();?>phuyu/phuyu_personas_2.js"> </script>