
<div id="phuyu_nuevocredito">
	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">
		<div class="phuyu_body">
			<div class="card">
				<div class="card-body">
					<input type="hidden" id="rubro" value="<?php echo $_SESSION["phuyu_rubro"]?>" name="">
					<div class="row form-group">
						<div class="col-md-8"> 
							<h5 style="letter-spacing:1px;"> <b>REGISTRO NUEVO CREDITO POR PAGAR</b> </h5> 
						</div>
					</div>
					<div class="row form-group">
						<div class="col-md-3 col-xs-12">
					    	<label>SOCIO DEL CREDITO</label>
					    	<select class="form-select" name="codpersona" v-model="campos.codpersona" required>
					    		<option value="<?php echo $persona[0]['codpersona'];?>"><?php echo 'LOTE: '.$codlote.' | '.$persona[0]['razonsocial'];?></option>
					    	</select>
					    </div>
					    <div class="col-md-2 col-xs-12">
					    	<label>AFECTA CAJA</label>
					    	<input type="checkbox" style="height:18px;width:18px;" v-model="campos.afectacaja">
					    </div>
					    <div class="col-md-2 col-xs-12">
					    	<label>FECHA</label>
					    	<input type="hidden" id="fecha" value="<?php echo date('Y-m-d');?>">
				        	<input type="date" class="form-control" id="fechacredito" v-model="campos.fechacredito" autocomplete="off" required v-on:blur="phuyu_fecha()">
					    </div>
					    <div class="col-md-2 col-xs-12">
					    	<label>FECHA INICIO</label>
				        	<input type="date" class="form-control" id="fechainicio" v-model="campos.fechainicio" autocomplete="off" required v-on:blur="phuyu_fecha()">
					    </div>
					    <div class="col-md-2 col-xs-12">
					    	<label>DIAS</label>
				        	<input type="number" class="form-control number" name="nrodias" v-model="campos.nrodias" min="1" autocomplete="off" v-on:keyup="phuyu_calcular()">
					    </div>
					    <div class="col-md-1 col-xs-12">
					    	<label>CUOTAS</label>
				        	<input type="number" class="form-control number" name="nrocuotas" v-model="campos.nrocuotas" min="1" autocomplete="off" v-on:keyup="phuyu_calcular()">
					    </div>
					</div>
					<div class="row form-group">
						<div class="col-md-2 col-xs-12">
					    	<label>CONCEPTO CREDITO</label>
					    	<select class="form-select" name="codcreditoconcepto" v-model="campos.codcreditoconcepto" required>
					    		<option value="2">CREDITOS RECIBIDOS</option>
					    	</select>
					    </div>
					    <div class="col-md-2 col-xs-12">
					    	<label>TIPO DE PAGO</label>
					    	<select class="form-select" name="codtipopago" v-model="campos.codtipopago" required>
					    		<?php 
					    			foreach ($tipopagos as $key => $value) { ?>
					    				<option value="<?php echo $value["codtipopago"];?>"><?php echo $value["descripcion"];?></option>
					    			<?php }
					    		?>
					    	</select>
					    </div>
					    <div class="col-md-2 col-xs-12">
					    	<label>IMPORTE</label>
					    	<input type="number" class="form-control number" name="importe" step="0.01" v-model="campos.importe" min="1" required style="border: 2px solid #d43f3a;" autocomplete="off" placeholder="0.00" v-on:keyup="phuyu_calcular()">
					    </div>
					    <div class="col-md-1 col-xs-12">
					    	<label>TASA %</label>
					    	<input type="number" class="form-control number" name="tasainteres" step="0.01" v-model="campos.tasainteres" min="0" required autocomplete="off" placeholder="0.00" v-on:keyup="calcular_credito()">
					    </div>
					    <div class="col-md-2 col-xs-12">
					    	<label>INTERES</label>
					    	<input type="number" class="form-control number" name="interes" v-model="campos.interes" min="0" readonly>
					    </div>
					    <div class="col-md-3">
					    	<label>DESCRIPCION CREDITO</label>
					    	<input type="text" class="form-control" name="referencia" v-model="campos.referencia" maxlength="255">
					    </div>
					</div>
					<div class="row form-group" v-show="campos.codtipopago!=1">
				    	<div class="col-md-2 col-xs-12">
					    	<label>FECHA DOC. BANCO</label>
					    	<input type="hidden" id="fechadocbanco_ref" value="<?php echo date('Y-m-d');?>">
					    	<input type="date" class="form-control" name="fechadocbanco" id="fechadocbanco" v-model="campos.fechadocbanco" autocomplete="off" required v-on:blur="phuyu_fechamovimiento()">
					    </div>
					    <div class="col-md-3 col-xs-12">
					    	<label>NRO DOCUMENTO BANCO (VOUCHER)</label>
				        	<input type="text" class="form-control" name="nrodocbanco" id="nrodocbanco" v-model="campos.nrodocbanco" placeholder="Nro documento banco" autocomplete="off">
					    </div>
				    </div>
				    <div class="row form-group slimscroll-detalle" style="height:200px;">
						<div class="col-xs-12">
							<table class="table table-striped" style="font-size: 11px;">
								<thead>
									<tr align="center" >
										<th width="10%">CUO</th>
										<th width="20%">FECHA VENCE</th>
				    					<th width="15%">N° LETRA</th>
				    					<th width="15%">COD. UNICO</th>
										<th width="15%">IMPORTE</th>
										<th width="10%">INTERES</th>
										<th width="15%">TOTAL</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="dato in cuotas">
										<th>{{dato.nrocuota}}</th>
										<th><input type="date" class="form-control" v-model="dato.fechavence" name=""></th>
				    					<td><input type="text" class="form-control" v-model="dato.nroletra" name="" maxlength="10"></td>
				    					<td><input type="text" class="form-control" v-model="dato.nrounicodepago" name=""></td>
										<th><input type="number" class="form-control" step="0.01" v-model="dato.importe" v-on:keyup="calcular_credito()" name=""></th>
										<th>{{dato.interes}}</th>
										<th>{{dato.total}}</th>
									</tr>

				    				<tr>
				    					<th colspan="6" style="text-align: right !important;">TOTAL</th>
				    					<th id="totalimportecredito">{{importetotalcredito}}</th>
				    				</tr>
								</tbody>
							</table>
						</div>
					</div>
					<br>
					<div align="center">
						<a class="btn btn-warning btn-sm"> <b>
				    		TOTAL CREDITO S/. {{campos.total}}</b> 
				    	</a>
						<button type="button" class="btn btn-warning btn-sm"> <b>IMPORTE: {{campos.importe}}</b> </button>
						<button type="button" class="btn btn-danger btn-sm"> <b>INTERES: {{campos.interes}}</b> </button>
						<button type="button" class="btn btn-success btn-sm"> <b>TOTAL: S/. {{campos.total}}</b> </button>
					</div>
					<br>
					<div class="col-md-12" align="center">
						<button type="submit" class="btn btn-success btn-lg" v-bind:disabled="estado==1"> <b>GUARDAR CREDITO</b> </button>
						<button type="button" class="btn btn-danger btn-lg" v-on:click="phuyu_cerrar()"> <b>CANCELAR</b> </button>
					</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_creditos/nuevopagar.js"> </script>
<script>
	var div_altura = jQuery(document).height(); var detalle = div_altura - 370;
	$(".slimscroll-detalle").slimScroll({position:'right',size:"5px", color:'#98a6ad',wheelStep:10,height:detalle+"px"});
</script>