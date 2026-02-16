<div id="phuyu_cobranza">
	<input type="hidden" id="rubro" value="<?php echo $_SESSION["phuyu_rubro"]?>" name="">
	<input type="hidden" id="codlote" value="<?php echo $codlote;?>" name="">
	<div class="row phuyu_header_title">
		<div class="col-md-3"> 
			<h5 style="letter-spacing:1px;"> <b>COBRANZA DEL CREDITO</b> </h5> 
		</div>	
	</div>

	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">
		<div class="phuyu_body">
			<div class="card">
				<div class="card-body">
					<div class="row form-group">
						<div class="col-md-3">
					    	<label>SOCIO DEL CREDITO</label>
					    	<select class="form-select" name="codpersona" v-model="campos.codpersona" required>
					    		<option value="<?php echo $persona[0]['codpersona'];?>"><?php echo 'LOTE: '.$codlote.' | '.$persona[0]['razonsocial'];?></option>
					    	</select>
					    </div>
					    <div class="col-md-2 col-xs-12">
					    	<label>CONCEPTO CREDITO</label>
					    	<select class="form-select" name="codconcepto" v-model="campos.codconcepto" required>
					    		<option value="19">COBRANZA DE CUOTAS</option>
					    	</select>
					    </div>
					    <div class="col-md-2 col-xs-12">
					    	<label>TIPO DE PAGO</label>
					    	<select class="form-select" name="codtipopago" v-model="campos.codtipopago" v-on:change="phuyu_vuelto()" required>
					    		<?php 
					    			foreach ($tipopagos as $key => $value) { ?>
					    				<option value="<?php echo $value["codtipopago"];?>"><?php echo $value["descripcion"];?></option>
					    			<?php }
					    		?>
					    	</select>
					    </div>
					    <div class="col-md-2 col-xs-6">
							<label>MONEDA</label>
			    			<select class="form-select" name="codmoneda" v-model="campos.codmoneda" v-on:change="phuyu_tipocambio()" required>
			    				<?php 
			    					foreach ($monedas as $key => $value) {?>
			    						<option value="<?php echo $value["codmoneda"];?>"><?php echo $value["simbolo"]." ".$value["descripcion"];?></option>
			    					<?php }
			    				?>
			    			</select>
						</div>
						<div class="col-md-1 col-xs-6">
							<label>CAMBIO</label>
			    			<input type="number" step="0.001" class="form-control number" name="tipocambio" v-model.number="campos.tipocambio" autocomplete="off" min="1" v-bind:disabled="campos.codmoneda==1" required>
						</div>
		    			<div class="col-md-2">
		    				<label>FECHA COBRO</label>
		    				<input type="date" class="form-control" id="fechamovimiento" value="<?php echo date('Y-m-d');?>" name="">
		    			</div>
					</div>
		    		<div class="row form-group" v-show="campos.codtipopago!=1">
		    			<div class="col-md-1">
		    				<label>ID</label>
		    				<input type="text" class="form-control" v-model="campos.codctacte" readonly>
		    			</div>
		    			<div class="col-md-2">
		    				<label>BANCO</label>
		    				<input type="text" class="form-control" v-model="campos.banco" readonly>
		    			</div>
	    				<div class="col-md-3">
		    				<label>CUENTA CORRIENTE</label>
		    				<input type="text" class="form-control" v-model="campos.nroctacte" readonly>
		    			</div>
		    			<div class="col-md-2">
		    				<label>MONEDA</label>
		    				<input type="text" class="form-control" v-model="campos.moneda" name="" readonly>
		    			</div>
		    			<div class="col-md-3">
		    				<label>CCI</label>
		    				<input type="text" class="form-control" v-model="campos.descripcioncci" name="" readonly>
		    			</div>
		    			<div class="col-md-1" style="margin-top: 1.3rem;">
		    				<label>&nbsp;</label>
		    				<button type="button" class="btn btn-primary btn-icon" v-on:click="buscar_ccte"><i data-acorn-icon="search"></i></button>
		    			</div>
		    		</div>
				    <div class="row form-group">
	    				<div class="col-md-5">
		    				<label>DESCRIPCION COBRANZA</label>
		    				<input type="text" class="form-control" v-model="campos.descripcion" required maxlength="150">
		    			</div>
		    			<div class="col-md-2 col-xs-12" v-show="campos.codtipopago!=1">
					    	<label>FECHA DOC. BANCO</label>
					    	<input type="date" class="form-control" name="fechadocbanco" id="fechadocbanco" autocomplete="off" required value="<?php echo date('Y-m-d');?>">
					    </div>
					    <div class="col-md-3 col-xs-12" v-show="campos.codtipopago!=1">
					    	<label>NRO DOCUMENTO BANCO</label>
				        	<input type="text" class="form-control" name="nrodocbanco" id="nrodocbanco" v-model="campos.nrodocbanco" placeholder="Nro documento banco" autocomplete="off">
					    </div>
	    				<div class="col-md-2" v-show="campos.codtipopago!=1">
		    				<label style="font-size:15px;">S/. IMPORTE</label>
		    				<input type="number" step="0.01" class="form-control number phuyu-money-success" v-model="campos.importe" required placeholder="S/. 0.00">
		    			</div>
		    			<div class="col-md-3" v-show="campos.codtipopago==1"></div>
		    			<div class="col-md-2" v-show="campos.codtipopago==1">
		    				<label style="font-size:13px;">S/. RECIBIDO</label>
		    				<input type="number" step="0.01" class="form-control number phuyu-money-success" v-model="campos.importe" min="1" required v-on:keyup="phuyu_vuelto()" placeholder="S/. 0.00">
		    			</div>
		    			<div class="col-md-2" v-show="campos.codtipopago==1">
		    				<label style="font-size:13px;">VUELTO</label>
		    				<input type="number" class="form-control phuyu-money-error" v-model="campos.vuelto" readonly>
		    			</div>
		    		</div>
					<div class="row form-group">
						<div class="col-md-12 col-xs-12">
							<div class="cuotas scroll-phuyu-view" style="height:200px;overflow:auto;overflow-x:hidden;">
								<div class="table-responsive">
									<table class="table table-bordered" style="font-size: 11px">
										<thead>
											<tr align="center" >
												<th width="3%">N°.C</th>
												<th width="5%">CUOTA</th>
												<th width="10%">LINEA</th>
												<th width="12%">TOTAL</th>
												<th width="12%">COBRADO</th>
												<th width="13%">SALDO</th>
												<th width="10%">COMPROBANTE</th>
												<th width="15%">F.&nbsp;&nbsp;INICIO</th>
												<th width="15%">F.&nbsp;VENCE</th>
												<th width="5%"> </th>
											</tr>
										</thead>
										<tbody>
											<tr v-for="(dato,index) in cuotas">
												<td>000{{dato.codcredito}}</td>
												<td>000{{dato.nrocuota}} </td>
												<td>{{dato.linea}}</td>
												<td>{{dato.total}} </td>
												<td>{{dato.cobrado}} </td>
												<td>{{dato.saldo}} </td>
												<td>{{dato.comprobante}}</td>
												<td>{{dato.fecha}} </td>
												<td>{{dato.fechavence}} </td>
												<td> 
													<input type="checkbox" class="form-check-input" v-bind:id="index" v-on:click="phuyu_cobrar(index,dato)"> 
												</td>
											</tr>
										</tbody>
										<tfoot>
											<tr v-for="(dato, index) in totalcuotas">
												<td colspan="3" align="right"><b>TOTALES</b></td>
												<td>{{dato.total}}</td>
												<td>{{dato.cobrado}}</td>
												<td>{{dato.saldo}}</td>
												<td colspan="4"></td>
											</tr>
										</tfoot>
									</table>
								</div>
							</div>

							<div style="border-bottom:2px solid #f3f3f3;margin-bottom:10px;" align="center"> <b>CUOTAS A COBRAR</b> </div>

							<div class="cobros" style="height:200px;">
								<table class="table table-bordered" style="font-size: 11px">
									<thead>
										<tr align="center" >
											<th width="15%">CREDITO</th>
											<th width="20%">CUOTA</th>
											<th width="20%">TOTAL</th>
											<th width="20%">SALDO</th>
											<th width="20%">COBRAR</th>
											<th width="5%"> </th>
										</tr>
									</thead>
									<tbody>
										<tr v-for="(dato,index) in cuotascobrar">
											<td>000{{dato.codcredito}}</td>
											<td>000{{dato.nrocuota}} </td>
											<td> <b>S/. {{dato.total}}</b> </td>
											<td style="color:#d43f3a"> <b>S/. {{dato.saldo}}</b> </td>
											<td> 
												<input type="number" step="0.01" class="form-control number" v-model.number="dato.cobrar" v-on:keyup="phuyu_calcular(dato)" v-bind:max="dato.importe" required style="border:2px solid #13a89e;">
											</td>
											<td>
												<button type="button"  style="margin-top:2px;" class="btn btn-danger btn-xs" v-on:click="phuyu_anularcuota(index,dato)">
													<b>X</b>
												</button>
											</td>
										</tr>
									</tbody>
									<tfoot>
										<tr>
											<td colspan="4" style="font-size: 14px;text-align: right;"><b>Total a Cobrar</b></td>
											<td style="text-align: right;"><b style="font-size: 14px;color:red;">S/. {{campos.total}}</b></td>
											<td></td>
										</tr>
									</tfoot>
								</table>
							</div>
						</div>
				    </div>
				    <div class="row form-group">
						<div class="col-md-12" align="right">
							<button type="submit" class="btn btn-primary btn-icon" v-bind:disabled="estado==1"> <b>GUARDAR COBRANZA</b> </button>
							<button type="button" class="btn btn-danger btn-icon" v-on:click="phuyu_cerrar()"> <b>CANCELAR</b> </button>
						</div>
					</div>
				    <div id="modal_ccte" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
						<div class="modal-dialog modal-lg">
							<div class="modal-content">
								<div class="modal-header modal-phuyu-titulo">
									<h5 class="modal-title"> <b style="letter-spacing:1px;" id="phuyu_tituloform">BUSCAR CUENTAS CORRIENTES DEL SOCIO</b> </h5> 
									<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> 
								</div>
								<div class="modal-body" id="cuerpo" style="height: 450px">
									
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
	    </div>
	</form>
</div>

<script>
	if (typeof AcornIcons !== 'undefined') {
      new AcornIcons().replace();
    }
    if (typeof Icons !== 'undefined') {
      const icons = new Icons();
    }
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_creditos/cobranza.js"> </script>
<script>

	var div_altura = jQuery(document).height(); var cobros = div_altura - 420;
	$(".cuotas").slimScroll({position:'right',size:"5px", color:'#98a6ad',wheelStep:10,height:"250px"});
	$(".cobros").slimScroll({position:'right',size:"5px", color:'#98a6ad',wheelStep:10,height:cobros+"px"});
</script>