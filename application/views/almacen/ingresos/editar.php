<div id="phuyu_form">
	<div style="padding:0px 10px;">
		<h6><b>NROINGRESO:</b> <?php echo $info[0]["seriecomprobante"]."-".$info[0]["nrocomprobante"];?> | <b>FECHA KARDEX:</b> <?php echo $info[0]["fechakardex"];?> </h6>
		<h6><b>FECHA COMPROBANTE:</b> <?php echo $info[0]["fechacomprobante"];?></h6>
	</div>

	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">
		<br> <input type="hidden" name="codregistro" v-model="campos.codregistro">
		<div class="row form-group">
			<div class="col-md-12">
				<div class="w-100">
			    	<label>RESPONSABLE</label>
			    	<select id="codproveedor" name="codproveedor">
					</select>
				</div>
		    </div>
		</div>
		<div class="row form-group">
			<div class="col-xs-12">
				<label>TIPO MOVIMIENTO</label>
	        	<select class="form-control" name="codmovimientotipo" v-model="campos.codmovimientotipo" required>
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
				<label>DESCRIPCION DEL INGRESO</label>
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

		<div class="ln_solid"></div>
		<div class="form-group" align="center">
			<button type="submit" class="btn btn-success" v-bind:disabled="estado==1"> <i class="fa fa-save"></i> GUARDAR </button>
			<button type="button" class="btn btn-danger" v-on:click="phuyu_cerrar()">CERRAR</button>
		</div>
	</form>
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
						phuyu_sistema.phuyu_alerta("EDITADO CORRECTAMENTE", "UN INGRESO EDITADO EN EL SISTEMA","info");
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

<script src="<?php echo base_url();?>phuyu/phuyu_personas_2.js"> </script>