<div id="phuyu_editarcredito">
	<form id="formulario" v-on:submit.prevent="phuyu_guardar()">
		<div class="phuyu_body" style="padding-top: 0px">
			<div class="card">
				<div class="card-body">	
					<input type="hidden" id="rubro" value="<?php echo $_SESSION["phuyu_rubro"]?>" name="">
		        	<input type="hidden" id="codcredito" value="<?php echo $creditos[0]["codcredito"];?>" name="">
				    <div class="row form-group">
				    	<div class="col-md-4">
                        	<label>LINEA DE CREDITO DEL CLIENTE</label>
                        	<select class="form-select" name="codlote" v-model="campos.codlote" id="codlote">
                        		<?php 
									foreach ($lotes as $key => $value) { ?>
										<option value="<?php echo $value["codlote"];?>"><?php echo 'LOTE: '.$value["codlote"].' | '.$value["descripcion"];?></option>
									<?php }
								?>
				            </select>
				        </div>
				        <div class="col-md-2" style="margin-top: 1.2rem">
				        	<button type="button" class="btn btn-success btn-icon" v-on:click="phuyu_lineascreditodirecto()"><i data-acorn-icon="plus"></i> NUEVA LINEA CREDITO</button>
				        </div>
					    <div class="col-md-2 col-xs-12">
					    	<label>FECHA</label>
					    	<input type="hidden" id="fecha" value="<?php echo date('Y-m-d');?>">
				        	<input type="date" class="form-control" id="fechacredito" v-model="campos.fechacredito" autocomplete="off" required v-on:blur="phuyu_fecha()">
					    </div>
					    <div class="col-md-2 col-xs-12">
					    	<label>FECHA INI.</label>
				        	<input type="date" class="form-control" id="fechainicio" v-model="campos.fechainicio" autocomplete="off" required v-on:blur="phuyu_fecha()">
					    </div>
					    <div class="col-md-1 col-xs-12">
					    	<label>DIAS</label>
				        	<input type="number" class="form-control number" name="nrodias" v-model="campos.nrodias" min="1" autocomplete="off" v-on:keyup="phuyu_calcular()">
					    </div>
					    <div class="col-md-1 col-xs-12">
					    	<label>CUOTAS</label>
				        	<input type="number" class="form-control number" name="nrocuotas" v-model="campos.nrocuotas" min="1" autocomplete="off" v-on:keyup="phuyu_calcular()">
					    </div>
				    </div>
				    <div class="row form-group">
					    <div class="col-md-1">
					    	<label>MONEDA</label>
					    	<select class="form-select" name="codmoneda" v-model="campos.codmoneda" v-on:change="phuyu_tipocambio()" required>
			    				<?php 
			    					foreach ($monedas as $key => $value) {?>
			    						<option value="<?php echo $value["codmoneda"];?>"><?php echo $value["simbolo"]." ".$value["descripcion"];?></option>
			    					<?php }
			    				?>
			    			</select>	
					    </div>
					    <div class="col-md-1">
					    	<label>CAMBIO</label>
					    	<input type="number" step="0.001" class="form-control number" name="tipocambio" v-model.number="campos.tipocambio" autocomplete="off" min="1" v-bind:disabled="campos.codmoneda==1" required>
					    </div>
				    	<div class="col-md-2 col-xs-12">
					    	<label>CONCEPTO CREDITO</label>
					    	<select class="form-select" name="codcreditoconcepto" v-model="campos.codcreditoconcepto" required v-bind:disabled="campos.codcreditoconcepto==3">
					    		<?php 
					    			foreach ($conceptos as $key => $value) { ?>
					    				<option value="<?php echo $value["codcreditoconcepto"];?>"><?php echo $value["descripcion"];?></option>
					    			<?php }
					    		?>
					    	</select>
					    </div>
					    <div class="col-md-2 col-xs-12">
					    	<label>N° TARJETA</label>
				        	<input type="text" class="form-control" name="nrotarjeta" v-model="campos.nrotarjeta" autocomplete="off">
					    </div>
					    <div class="col-md-2 col-xs-12">
					    	<label>TIPO DE PAGO</label>
					    	<select class="form-select" name="codtipopago" v-bind:disabled="campos.codcreditoconcepto==3" v-model="campos.codtipopago" required>
					    		<?php 
					    			foreach ($tipopagos as $key => $value) { ?>
					    				<option value="<?php echo $value["codtipopago"];?>"><?php echo $value["descripcion"];?></option>
					    			<?php }
					    		?>
					    	</select>
					    </div>
					    <div class="col-md-1 col-xs-12">
					    	<label>IMPORTE</label>
					    	<input type="number" class="form-control number" v-bind:disabled="campos.codcreditoconcepto==3" name="importe" step="0.01" v-model="campos.importe" min="1" required style="border: 2px solid #d43f3a;" autocomplete="off" placeholder="0.00" v-on:keyup="phuyu_calcular()">
					    </div>
					    <div class="col-md-1 col-xs-12">
					    	<label>TASA I. %</label>
					    	<input type="number" class="form-control number" name="tasainteres" step="0.01" v-model="campos.tasainteres" min="0" required autocomplete="off" placeholder="0.00" v-on:keyup="phuyu_calcular()">
					    </div>
					    <div class="col-md-1 col-xs-12">
					    	<label>INTERES</label>
					    	<input type="number" class="form-control number" name="interes" v-model="campos.interes" min="0" readonly>
					    </div>
					    <div class="col-md-1 col-xs-12">
					    	<label>TOTAL</label>
					    	<input type="number" class="form-control number" name="totales" v-model="campos.totales" min="0" readonly>
					    </div>
				    </div>
				    <div class="row form-group">
					    <div class="col-md-2 col-xs-12">
					    	<label>MODO CUOTAS</label>
					    	<select class="form-select" name="tipocuota" v-model="campos.tipocuota" required v-on:change="phuyu_calcular()">
					    		<option value="0">SIMPLE</option>
					    		<option value="1">FRANCES</option>
					    	</select>
					    </div>
				    	<div class="col-md-2 col-xs-12">
					    	<label>EMPLEADO DEL CREDITO</label>
					    	<select class="form-select" name="codempleado" v-model="campos.codempleado" required>
					    		<option value="0">CREDITO SIN EMPLEADO</option>
					    		<?php 
					    			foreach ($empleados as $key => $value) { ?>
					    				<option value="<?php echo $value["codpersona"];?>">
					    					<?php echo $value["razonsocial"]." (DOCUMENTO: ".$value["documento"].")";?>
					    				</option>
					    			<?php }
					    		?>
					    	</select>
					    </div>
					    <div class="col-md-3">
					    	<label>DESCRIPCION CREDITO</label>
					    	<input type="text" class="form-control" name="referencia" v-model="campos.referencia" maxlength="255">
					    </div>
					    <div class="col-md-2 col-xs-12" v-show="campos.codtipopago!=1">
					    	<label>FECHA DOC. BANCO</label>
					    	<input type="hidden" id="fechadocbanco_ref" value="<?php echo date('Y-m-d');?>">
					    	<input type="date" class="form-control" name="fechadocbanco" id="fechadocbanco" v-model="campos.fechadocbanco" autocomplete="off" required v-on:blur="phuyu_fechamovimiento()">
					    </div>
					    <div class="col-md-3 col-xs-12" v-show="campos.codtipopago!=1">
					    	<label>NRO DOCUMENTO BANCO (VOUCHER)</label>
				        	<input type="text" class="form-control" name="nrodocbanco" id="nrodocbanco" v-model="campos.nrodocbanco" placeholder="Nro documento banco" autocomplete="off">
					    </div>
				    </div>
					<div class="row form-group slimscroll-detalle" style="height:250px;">
						<div class="col-xs-12">
							<table class="table table-striped" style="font-size: 11px">
								<thead>
									<tr>
										<th width="5%">CUOTA</th>
										<th width="20%">FECHA VENCE</th>
										<th width="15%">N° LETRA</th>
										<th width="15%">NRO CODIGO</th>
										<th width="10%">IMPORTE</th>
										<th width="10%">TASA %</th>
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
										<th>{{dato.tasa}}</th>
										<th>{{dato.interes}}</th>
										<th>{{dato.total}}</th>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
					<hr>
					<div align="center">
						<button type="button" class="btn btn-warning"> <b>IMPORTE: {{campos.importe}}</b> </button>
						<button type="button" class="btn btn-danger"> <b>INTERES: {{campos.interes}}</b> </button>
						<button type="button" class="btn btn-success"> <b>TOTAL: S/. {{campos.total}}</b> </button>
						<button type="submit" class="btn btn-primary" v-bind:disabled="estado==1"> <b>GUARDAR CREDITO</b> </button>
						<button type="button" class="btn btn-danger" data-bs-dismiss="modal"> <b>CANCELAR</b> </button>
					</div>
					<br>
				</div>
			</div>
		</div>
	</form>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_creditos/editar.js"> </script>
<script>
	var div_altura = jQuery(document).height(); var detalle = div_altura - 410;
	$(".slimscroll-detalle").slimScroll({position:'right',size:"5px", color:'#98a6ad',wheelStep:10,height:detalle+"px"});
</script>