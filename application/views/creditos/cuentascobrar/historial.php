<div id="phuyu_historial">
	<div class="phuyu_body">
		<div class="card">
			<div class="card-body">
				<input type="hidden" id="tipo" value="1">
				<div class="row">
					<div class="col-md-10 col-xs-12"> 
						<h5><b>HISTORIAL DE CREDITOS POR COBRAR: </b> <?php echo $persona[0]["razonsocial"];?></h5>
					</div>
				</div>
				<div class="row">
					<div class="col-md-2">
						<label>FECHA INICIAL</label>
						<input type="date" class="form-control" id="fechadesde" value="<?php echo date('Y-m-01');?>" autocomplete="off">
					</div>
					<div class="col-md-2">
						<label>FECHA FIN</label>
						<input type="date" class="form-control" id="fechahasta" value="<?php echo date('Y-m-d');?>" autocomplete="off">
					</div>
					<div class="col-md-2"> 
						<label>ESTADO</label>
						<select class="form-select" v-model="campos.estado">
							<option value="">TODOS LOS CREDITOS</option>
							<option value="0">ANULADOS</option>
							<option value="1">PENDIENTES</option>
							<option value="2">COBRADOS</option>
						</select>
					</div>
					<div class="col-md-2">
						<label>FILTRO?</label>
						<select class="form-select" v-model="campos.filtro">
							<option value="1">FECHAS FILTRO (SI)</option>
							<option value="0">FECHAS FILTRO (NO)</option>
						</select>
					</div>
					<div class="col-md-4" style="margin-top: 1.2rem">
						<button type="button" class="btn btn-success btn-icon btn-block" v-on:click="phuyu_creditos()">
							<i data-acorn-icon="search"></i> CREDITOS
						</button>
						<button type="button" class="btn btn-danger btn-icon btn-block" v-on:click="phuyu_cerrar()">
							<i data-acorn-icon="arrow-left"></i> CERRAR
						</button>
					</div>
				</div>
				<div class="table-responsive" style="height: 180px;overflow-y:auto;">
					<table class="table table-striped" style="font-size: 11px">
						<thead>
							<tr>
								<th width="10px">CREDITO</th>
								<th>COMPROBANTE REF.</th>
								<th>F.CREDITO</th>
								<th>N° TARJETA</th>
								<th>N° CUOTAS</th>
								<th>IMPORTE</th>
								<th>TASA</th>
								<th>INTERES</th>
								<th>TOTAL</th>
								<th>COBRADO</th>
								<th>SALDO</th>
								<th width="10px">ESTADO</th>
								<th width="10px">EDITAR</th>
								<th width="10px">ANULAR</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="dato in creditos" v-bind:class="[dato.estado==0 ? 'phuyu_anulado':'']">
								<td>000{{dato.codcredito}}</td>
								<td>{{dato.comprobantereferencia}}</td>
								<td>{{dato.fechacredito}}</td>
								<td>{{dato.nrotarjeta}}</td>
								<td>0{{dato.nrocuotas}}</td>
								<td>{{dato.importe}}</td>
								<td>{{dato.tasainteres}}</td>
								<td>{{dato.interes}}</td>
								<td>{{dato.total}}</td>
								<td><b style="font-size:15px;">{{dato.cobrado}}</b></td>
								<td><b style="font-size:15px;">{{dato.saldo}}</b></td>
								<td>
									<span class="label label-danger" v-if="dato.estado==0">ANULADO</span>
									<span class="label label-warning" v-if="dato.estado==1">PENDIENTE</span>
									<span class="label label-success" v-if="dato.estado==2">COBRADO</span>
								</td>
								<td>
									<button type="button" class="btn btn-warning btn-xs" v-on:click="phuyu_editar(dato.codcredito)" style="margin-bottom:2px;">
										<i class="fa fa-edit"></i> EDITAR
									</button>
								</td>
								<td>
									<button type="button" class="btn btn-danger btn-xs" v-on:click="phuyu_eliminar(dato.codcredito)" style="margin-bottom:2px;">
										<i class="fa fa-trash-o"></i> ANULAR
									</button>
								</td>
							</tr>
							<tr v-for="dato in totales">
								<td colspan="4" style="text-align:right;"><b style="font-size:15px;">TOTALES</b></td>
								<td><b style="font-size:15px;">{{dato.importe}}</b></td> <td></td>
								<td><b style="font-size:15px;">{{dato.interes}}</b></td>
								<td><b style="font-size:15px;">{{dato.total}}</b></td>
								<td><b style="font-size:15px;">{{dato.cobrado}}</b></td>
								<td><b style="font-size:15px;">{{dato.saldo}}</b></td> <td colspan="2"></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div> <br>

			<div class="card-body" style="margin-top:5px;">
				<div class="row">
					<div class="col-md-4"> <label class="p-5">HISTORIAL DE COBRANZAS</label> </div>
					<div class="col-md-2">
						<label>FECHA INICIO</label>
						<input type="date" class="form-control" id="fechadesde_c" value="<?php echo date('Y-m-01');?>" autocomplete="off">
					</div>
					<div class="col-md-2">
						<label>FECHA FIN</label>
						<input type="date" class="form-control" id="fechahasta_c" value="<?php echo date('Y-m-d');?>" autocomplete="off">
					</div>
					<div class="col-md-2" style="margin-top: 1.2rem">
						<button type="button" class="btn btn-success btn-icon btn-block" v-on:click="phuyu_pagos_cobros()">
							<i data-acorn-icon="search"></i> COBRANZAS
						</button>
					</div>
				</div>
				<div class="table-responsive" style="height:calc(100vh - 75vh); overflow-y:auto;">
					<table class="table table-striped" style="font-size: 11px">
						<thead>
							<tr>
								<th width="10px">IMPRIMIR</th>
								<th>CUOTAS COBRADAS (CREDITO | NRO CUOTA | AMORTIZACION)</th>
								<th width="70px">FECHA</th>
								<th width="10px">IMPORTE</th>
								<th width="10px">EDITAR</th>
								<th width="10px">ANULAR</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="dato in pagos_cobros">
								<td>
									<button type="button" class="btn btn-warning btn-xs" v-on:click="phuyu_imprimir_recibo(dato.codmovimiento,1)" style="margin-bottom:2px;">
										<i class="fa fa-print"></i> RECIBO
									</button>
								</td>
								<td>
									<span v-for="d in dato.cuotas">
										<b>CREDITO: 000{{d.codcredito}}</b> | CUOTA: {{d.nrocuota}} | AMORTIZADO: <b>{{d.importe}}</b> &nbsp; | &nbsp;
									</span>
								</td>
								<td>{{dato.fechamovimiento}}</td>
								<td>{{dato.importe}}</td>
								<td>
									<button type="button" class="btn btn-warning btn-xs" v-on:click="phuyu_editarfecha_pagocobro(dato.codmovimiento,'COBRO',dato.fechamovimiento)" style="margin-bottom:2px;">
										<i class="fa fa-edit"></i> EDITAR FECHA
									</button>
								</td>
								<td>
									<button type="button" class="btn btn-danger btn-xs" v-on:click="phuyu_anular_pagocobro(dato.codmovimiento,'COBRO')" style="margin-bottom:2px;">
										<i class="fa fa-trash-o"></i> ANULAR
									</button>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>

			<div style="display:none;">
				<div id="imprimir_recibo"></div>
			</div>
			<div id="modal_editar" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
				<div class="modal-dialog modal-fullscreen-xxl-down">
					<div class="modal-content" style="border-radius:0px">
						<div class="modal-header modal-phuyu-titulo">
							<h4 class="modal-title">
								<b style="letter-spacing:4px;">EDITAR CREDITO</b>
							</h4>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> 
						</div>
						<div class="modal-body"  id="cuerpo">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	if (typeof AcornIcons !== 'undefined') {
      new AcornIcons().replace();
    }
    if (typeof Icons !== 'undefined') {
      const icons = new Icons();
    }
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_creditos/historial.js"> </script>