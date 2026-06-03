<div id="phuyu_datos">
	<div class="phuyu_body">
		<div class="card">
			<div class="card-body">
				<input type="hidden" id="rubro" value="<?php echo $_SESSION["phuyu_rubro"];?>" name="">
				<div class="row form-group">
					<div class="col-md-12"> <h4> <b><button type="button" class="btn btn-danger btn-xs" v-on:click="phuyu_atras()"><i data-acorn-icon="arrow-left"></i></button> REPORTE DE HISTORIAL</b></h4> </div>
				</div>
				<div class="row form-group">
					<div class="col-md-2">
						<label><i class="fa fa-calendar"></i> DESDE</label>
						<input type="date" class="form-control" id="fecha_desde" value="<?php echo date('Y-m-01');?>" v-on:blur="phuyu_vacio()">
					</div>
					<div class="col-md-2">
						<label><i class="fa fa-calendar"></i> HASTA</label>
						<input type="date" class="form-control" id="fecha_hasta" value="<?php echo date('Y-m-d');?>" v-on:blur="phuyu_vacio()">
					</div>
					<div class="col-md-2">
						<label>ESTADO DE CUENTA</label>
						<select class="form-select" id="tipo_consulta" v-model="campos.tipo_consulta" v-on:change="phuyu_vacio()">
							<option value="1">GENERAL</option>
							<option value="2">DETALLADO</option>
						</select>
					</div>
					<div class="col-md-2">
						<label>TIPO</label>
						<select class="form-select" id="tipo" v-model="campos.tipo" v-on:change="phuyu_vacio()">
							<option value="1">PRODUCCION</option>
							<option value="2">GASTOS</option>
						</select>
					</div>
					<div class="col-md-4" style="margin-top: 1.2rem">
						<button type="button" class="btn btn-primary btn-icon" v-on:click="ver_creditos()">
							<i data-acorn-icon="search"></i> Consultar
						</button>
						<button type="button" class="btn btn-danger btn-icon" v-on:click="pdf_creditos()">
							<i data-acorn-icon="print"></i> Pdf
						</button>
						<button type="button" class="btn btn-success btn-icon" v-on:click="excel_creditos()">
							<i data-acorn-icon="file-text"></i> Excel
						</button>
					</div>
				</div>
				<div class="col-md-12" id="phuyu_creditos" style="height:350px;overflow-y:auto;font-size: 11px">
					<div v-if="campos.tipo_consulta==1">
						<div v-if="campos.mostrar==1">
							<div v-for="dato in estado_cuenta_socios">
								<table class="table table-striped" style="font-size: 11px;">
									<tr style="background:#f2f2f2">
										<th colspan="9">
											<b v-if="campos.tipo==1">CLIENTE:</b> 
											<b v-if="campos.tipo!=1">PROVEEDOR:</b> {{dato.razonsocial}} |
											<b>DIRECCION:</b> {{dato.direccion}}
										</th>
									</tr>
									<tr>
										<th style="width:8%;"><b>FECHA</b></th>
										<th style="width:10%;"><b>LINEA</b></th>
										<th style="width:10%;"><b>COMPROBANTE</b></th>
										<th style="width:40%;"><b>DESCRIPCION</b></th>
										<th style="width:8%;"><b>IMPORTE</b></th>
									</tr>
									<tr v-for="c in dato.movimientos">
										<td style="width:8%;">{{c.fecha}}</td>
										<td style="width:10%;">{{c.linea}}</td>
										<td style="width:10%;">{{c.comprobante}}</td>
										<td style="width:40%;">{{c.referencia}}</td>
										<td style="width:10%;" align="right">{{c.cargototal}}</td>
									</tr>
									<tr>
										<td colspan="4" align="right"><b>TOTALES</b></td>
										<td align="right"><b>{{dato.cargototal}}</b></td>
									</tr>
								</table>
							</div>
						</div>

						<div v-if="campos.mostrar==2">
							<div v-for="dato in estado_cuenta_creditos">
								<table class="table table-bordered">
									<tr style="background:#f2f2f2;">
										<th colspan="8">
											<b v-if="campos.tipo==1">CLIENTE:</b> 
											<b v-if="campos.tipo!=1">PROVEEDOR:</b> {{dato.razonsocial}} |
											<b>DIRECCION:</b> {{dato.direccion}}
										</th>
									</tr>
									<tr>
										<th style="width:10%;"><b>FECHA</b></th>
										<th style="width:10%;"><b>COMPROBANTE</b></th>
										<th style="width:40%;"><b>DESCRIPCION</b></th>
										<th style="width:10%"><b>IMPORTE</b></th>
										<th style="width:10%"><b>INTERES</b></th>
										<th style="width:10%"><b>TOTAL</b></th>
										<th style="width:10%"><b v-if="campos.tipo==1">COBRANZA</b> <b v-if="campos.tipo!=1">PAGO</b></th>
										<th style="width:10%"><b>SALDO</b></th>
									</tr>
									<tr v-for="c in dato.creditos">
										<td style="width:10%;">{{c.fecha}}</td>
										<td style="width:10%;">{{c.comprobante}}</td>
										<td style="width:40%;">{{c.referencia}}</td>
										<td style="width:10%;" align="right">{{c.importe}} </td>
										<td style="width:10%;" align="right">{{c.interes}} </td>
										<td style="width:10%;" align="right"><b>{{c.total}}</b></td>
										<td style="width:10%;" align="right">{{c.cobranza}} </td>
										<td style="width:10%;" align="right"><b>{{c.saldo}}</b></td>
									</tr>
								</table>
							</div>
						</div>
					</div>

					<div v-if="campos.tipo_consulta==2">
						<div v-for="dato in estado_cuenta_detallado">
							<table class="table table-striped" style="font-size: 11px">
								<tr style="background:#f2f2f2">
									<th colspan="12">
										<b v-if="campos.tipo==1">CLIENTE:</b> 
										<b v-if="campos.tipo!=1">PROVEEDOR:</b> {{dato.razonsocial}} |
										<b>DIRECCION:</b> {{dato.direccion}}
									</th>
								</tr>
								<tr>
									<th style="width:7%;"><b>FECHA</b></th>
									<th style="width:7%;"><b>LINEA</b></th>
									<th style="width:10%;"><b>COMPROBANTE</b></th>
									<th style="width:20%;"><b>DESCRIPCION</b></th>
									<th style="width:7%;"><b>UNIDAD</b></th>
									<th style="width:7%;"><b>CANTIDAD</b></th>
									<th style="width:7%;"><b>P.UNITARIO</b></th>
									<th style="width:7%;"><b>IMPORTE</b></th>
								</tr>
								<tr v-for="c in dato.movimientos">
									<td style="width:8%;">{{c.fechacomprobante}}</td>
									<td style="width:8%;">{{c.linea}}</td>
									<td style="width:10%;">{{c.seriecomprobante}}-{{c.nrocomprobante}}</td>
									<td style="width:20%;">{{c.descripcion}}</td>
									<td style="width:8%;">{{c.unidad}}</td>
									<td style="width:7%;">{{c.cantidad}}</td>
									<td style="width:8%;">{{c.preciounitario}}</td>
									<td style="width:7%;" align="right">{{c.cargototal}} </td>
								</tr>
								<tr>
									<td colspan="7" align="right"><b>TOTALES</b></td>
									<td align="right"><b>{{dato.cargototal}}</b></td>
								</tr>
							</table>
						</div>
					</div>

					<div v-if="campos.tipo_consulta==3">
						<div v-if="campos.mostrar==1">
							<div v-for="dato in estado_cuenta_socios ">
								<table class="table table-bordered">
									<tr style="background:#f2f2f2">
										<th colspan="9">
											<b v-if="campos.tipo==1">CLIENTE:</b> 
											<b v-if="campos.tipo!=1">PROVEEDOR:</b> {{dato.razonsocial}} |
											<b>DIRECCION:</b> {{dato.direccion}}
										</th>
									</tr>
									<tr>
										<th style="width:10%;"><b>FECHA</b></th>
										<th style="width:8%;"><b>LINEA</b></th>
										<th style="width:10%;"><b>COMPROBANTE</b></th>
										<th style="width:50%;"><b>DESCRIPCION</b></th>
										<th style="width:8%;"><b>CARGO</b></th>
										<th style="width:8%;">INTERES</th>
										<th style="width:10%;"><b>CARGO TOTAL</b></th>
										<th style="width:8%;"><b>ABONO</b></th>
										<th style="width:8%;"><b>SALDO</b></th>
									</tr>
									<tr>
										<td colspan="4" align="right"><b>SALDO ANTERIOR</b></td>
										<td align="right">{{dato.importeanterior}}</td>
										<td align="right">{{dato.interesanterior}}</td>
										<td align="right">{{dato.totalanterior}}</td>
										<td align="right">{{dato.pagadoanterior}}</td>
										<td align="right">{{dato.anterior}}</td>
									</tr>
									<tr v-for="c in dato.movimientos">
										<td style="width:10%;">{{c.fecha}}</td>
										<td style="width:10%;">{{c.linea}}</td>
										<td style="width:10%;">{{c.comprobante}}</td>
										<td style="width:50%;">{{c.referencia}}</td>
										<td style="width:10%;" align="right">{{c.cargo}} </td>
										<td style="width:10%;" align="right">{{c.interesactual}}</td>
										<td style="width:10%;" align="right">{{c.cargototal}} </td>
										<td style="width:10%;" align="right">{{c.abono}} </td>
										
										<!-- <td style="width:10%;" align="right">{{c.saldo}} </td> -->
										<td style="width:10%;" align="right">
										
										{{c.saldo}}
										</td>
									</tr>
									<tr>
										<td colspan="4" align="right"><b>TOTALES</b></td>
										<td align="right"><b>{{dato.cargo}}</b></td>
										<td align="right"><b>{{dato.totalinteresactual}}</b></td>
										<td align="right"><b>{{dato.cargototal}}</b></td>
										<td align="right"><b>{{dato.abono}}</b></td>
										<td align="right"><b>
										
										{{dato.saldoit}}
										</b></td>
										
										<!-- <td align="right"><b>{{dato.saldo}}</b></td> -->
									</tr>
								</table>
							</div>
						</div>

						<div v-if="campos.mostrar==2">
							<div v-for="dato in estado_cuenta_creditos_interes_actualizado">
								<table class="table table-bordered">
									<tr style="background:#f2f2f2;">
										<th colspan="8">
											<b v-if="campos.tipo==1">CLIENTE:</b> 
											<b v-if="campos.tipo!=1">PROVEEDOR:</b> {{dato.razonsocial}} |
											<b>DIRECCION:</b> {{dato.direccion}}
										</th>
									</tr>
									<tr>
										<th style="width:10%;"><b>FECHA</b></th>
										<th style="width:10%;"><b>COMPROBANTE</b></th>
										<th style="width:40%;"><b>DESCRIPCION</b></th>
										<th style="width:10%"><b>IMPORTE</b></th>
										<th style="width:10%"><b>INTERES</b></th>
										<th style="width:10%"><b>TOTAL</b></th>
										<th style="width:10%"><b v-if="campos.tipo==1">COBRANZA</b> <b v-if="campos.tipo!=1">PAGO</b></th>
										<th style="width:10%"><b>SALDO</b></th>
									</tr>
									<tr v-for="c in dato.creditos">
										<td style="width:10%;">{{c.fecha}}</td>
										<td style="width:10%;">{{c.comprobante}}</td>
										<td style="width:40%;">{{c.referencia}}</td>
										<td style="width:10%;" align="right">{{c.importe}} </td>
										<td style="width:10%;" align="right">{{c.interes}} </td>
										<td style="width:10%;" align="right"><b>{{c.total}}</b></td>
										<td style="width:10%;" align="right">{{c.cobranza}} </td>
										<td style="width:10%;" align="right"><b>{{c.saldo}}</b></td>
									</tr>
								</table>
							</div>
						</div>
					</div>

					<div v-if="campos.tipo_consulta==4">
						<div v-for="dato in estado_cuenta_detallado_interes_actualizado">
							<table class="table table-bordered">
								<tr style="background:#f2f2f2">
									<th colspan="12">
										<b v-if="campos.tipo==1">CLIENTE:</b> 
										<b v-if="campos.tipo!=1">PROVEEDOR:</b> {{dato.razonsocial}} |
										<b>DIRECCION:</b> {{dato.direccion}}
									</th>
								</tr>
								<tr>
									<th style="width:8%;"><b>FECHA</b></th>
									<th style="width:7%;"><b>LINEA</b></th>
									<th style="width:12%;"><b>COMPROBANTE</b></th>
									<th style="width:25%;"><b>DESCRIPCION</b></th>
									<th style="width:7%;"><b>UNIDAD</b></th>
									<th style="width:7%;"><b>CANTIDAD</b></th>
									<th style="width:7%;"><b>P.UNITARIO</b></th>
									<th style="width:7%;"><b>CARGO</b></th>
									<th style="width:7%;"><b>INTERES</b></th>
									<th style="width:7%;"><b>CARGO TOTAL</b></th>
									<th style="width:7%;"><b>ABONO</b></th>
									<th style="width:7%;"><b>SALDO</b></th>
								</tr>
								<tr>
									<td colspan="7" align="right"><b>SALDO ANTERIOR</b></td>
									<td align="right">{{dato.importeanterior}}</td>
									<td align="right">{{dato.interesanterior}}</td>
									<td align="right">{{dato.totalanterior}}</td>
									<td align="right">{{dato.pagadoanterior}}</td>
									<td align="right">{{dato.anterior}}</td>
								</tr>
								<tr v-for="c in dato.movimientos">
									<td style="width:8%;">{{c.fechacomprobante}}</td>
									<td style="width:8%;">{{c.linea}}</td>
									<td style="width:12%;">{{c.seriecomprobante}}-{{c.nrocomprobante}}</td>
									<td style="width:25%;">{{c.descripcion}}</td>
									<td style="width:8%;">{{c.unidad}}</td>
									<td style="width:7%;">{{c.cantidad}}</td>
									<td style="width:8%;">{{c.preciounitario}}</td>
									<td style="width:8%;" align="right">{{c.cargo}} </td>
									<td style="width:8%;" align="right">{{c.interesactual}} </td>
									<td style="width:8%;" align="right">{{c.cargototaldet}} </td>
									<td style="width:8%;" align="right">{{c.abono}} </td>
									<td style="width:8%;" align="right">{{c.saldo}} </td>
								</tr>
								<tr>
									<td colspan="7" align="right"><b>TOTALES</b></td>
									<td align="right"><b>{{dato.cargo}}</b></td>
									<td align="right"><b>{{dato.totalinteresactual}}</b></td>
									<td align="right"><b>{{dato.cargototal}}</b></td>
									<td align="right"><b>{{dato.abono}}</b></td>
									<td align="right"><b>{{dato.saldo}}</b></td>
								</tr>
							</table>
						</div>
					</div>

					<div v-if="this.campos.saldos==1">
						<div v-for="dato in saldos">
							<table class="table table-bordered">
								<tr style="background:#f2f2f2">
									<th colspan="8">
										<b v-if="campos.tipo==1">CLIENTE:</b> 
										<b v-if="campos.tipo!=1">PROVEEDOR:</b> {{dato.razonsocial}}  |
										<b>DIRECCION:</b> {{dato.direccion}}
									</th>
								</tr>

								<tr><!-- SALDOS -->
									<th style="width:8%;">RECIBO</th>
									<th style="width:7%;">FECHA CREDITO</th>
									<th style="width:7%;">FECHA VENCE</th>
									<th style="width:12%;">ESTADO</th>
									<th style="width:20%;">DESCRIPCION</th>
									<th style="width:8%;" align="right">IMPORTE</th>
									<th style="width:8%;" align="right">INTERES</th>
									<th style="width:8%;" align="right">TOTAL</th>
									<th style="width:8%"><b v-if="campos.tipo==1">COBRANZA</b> <b v-if="campos.tipo!=1">PAGO</b></th>
									<th style="width:8%;" align="right">SALDO</th>
								</tr>
								<tr v-for="c in dato.creditos">
									<td style="width:8%;">{{c.seriecomprobante_ref}} - {{c.nrocomprobante_ref}}</td>
									<td style="width:7%;">{{c.fechacredito}}</td>
									<td style="width:7%;">{{c.fechavencimiento}}</td>
									<td style="width:12%;">{{c.estado}}</td>
									<td style="width:20%;">{{c.referencia}}</td>
									<td style="width:8%;" align="right">{{c.importe}} </td>
									<td style="width:8%;" align="right">{{c.interes}} </td>
									<td style="width:8%;" align="right">{{c.total}} </td>
									<td style="width:8%;" align="right">{{c.importepagado}} </td>
									<td style="width:8%;" align="right">{{c.saldo}} </td>
								</tr>
								<tr>
									<td colspan="5" align="right"><b>TOTALES</b></td>
									<td align="right"><b>{{dato.importe}}</b></td>
									<td align="right"><b>{{dato.interes}}</b></td>
									<td align="right"><b>{{dato.total}}</b></td>
									<td align="right"><b>{{dato.totalimportepagado}}</b></td>
									<td align="right"><b>{{dato.saldo}}</b></td>
								</tr>
							</table>
						</div>
					</div>
					<div v-if="this.campos.saldos==2">
						<div v-for="dato in saldos_actual">
							<table class="table table-bordered">
								<tr style="background:#f2f2f2">
									<th colspan="8">
										<b v-if="campos.tipo==1">CLIENTE:</b> 
										<b v-if="campos.tipo!=1">PROVEEDOR:</b> {{dato.razonsocial}}  |
										<b>DIRECCION:</b> {{dato.direccion}}
									</th>
								</tr>

								<tr><!-- SALDOS -->
									<th style="width:8%;">RECIBO</th>
									<th style="width:7%;">FECHA CREDITO</th>
									<th style="width:7%;">FECHA VENCE</th>
									<th style="width:12%;">ESTADO</th>
									<th style="width:20%;">DESCRIPCION</th>
									<th style="width:8%;" align="right">IMPORTE</th>
									<th style="width:8%;" align="right">INTERES</th>
									<th style="width:8%;" align="right">TOTAL</th>
									<th style="width:8%"><b v-if="campos.tipo==1">COBRANZA</b> <b v-if="campos.tipo!=1">PAGO</b></th>
									<th style="width:8%;" align="right">SALDO</th>
								</tr>
								<tr v-for="c in dato.creditos">
									<td style="width:8%;">{{c.seriecomprobante_ref}} - {{c.nrocomprobante_ref}}</td>
									<td style="width:7%;">{{c.fechacredito}}</td>
									<td style="width:7%;">{{c.fechavencimiento}}</td>
									<td style="width:12%;">{{c.estado}}</td>
									<td style="width:20%;">{{c.referencia}}</td>
									<td style="width:8%;" align="right">{{c.importe}} </td>
									<td style="width:8%;" align="right">{{c.interesactual}} </td>
									<td style="width:8%;" align="right">{{c.totalactual}} </td>
									<td style="width:8%;" align="right">{{c.importepagado}} </td>
									<td style="width:8%;" align="right">{{c.saldoactual}} </td>
								</tr>
								<tr>
									<td colspan="5" align="right"><b>TOTALES</b></td>
									<td align="right"><b>{{dato.importe}}</b></td>
									<td align="right"><b>{{dato.totalinteresactual}}</b></td>
									<td align="right"><b>{{dato.importemastotalinteres}}</b></td>
									<td align="right"><b>{{dato.totalimportepagado}}</b></td>
									<td align="right"><b>{{dato.totalsaldoactual}}</b></td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>

			<div id="modal_reportes" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
				<div class="modal-dialog" style="width:100%;margin:0px;">
					<div class="modal-content" align="center" style="border-radius:0px">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" style="font-size:30px;margin-bottom:0px;">
								<i class="fa fa-times-circle"></i> 
							</button>
							<h4 class="modal-title">
								<b style="letter-spacing:4px;"><?php echo $_SESSION["phuyu_empresa"];?> </b>
							</h4>
						</div>
						<div class="modal-body" id="reportes_modal" style="height:450px;padding:0px;">
							<iframe id="phuyu_pdf" src="" style="width:100%; height:100%; border:none;"> </iframe>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	var campos = {"codpersona":0,"fecha_desde":"","fecha_hasta":"","fecha_saldos":"","tipo_consulta":1,"tipo":1,"mostrar":1,"saldos":0,"codlote":0,"estado":3};
	var pantalla = jQuery(document).height(); $("#reportes_modal").css({height: pantalla - 65});
	if (typeof AcornIcons !== 'undefined') {
      new AcornIcons().replace();
    }
    if (typeof Icons !== 'undefined') {
      const icons = new Icons();
    }
</script>

<script src="<?php echo base_url();?>phuyu/phuyu_chacra/creditos.js"> </script>
<script>
	var pantalla = jQuery(document).height(); $("#phuyu_creditos").css({height: pantalla - 300});
</script>