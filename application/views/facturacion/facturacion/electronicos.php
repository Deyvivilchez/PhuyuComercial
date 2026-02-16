<div id="phuyu_facturacion">
	<div role="alert" class="alert alert-danger" style="border: 2px solid red;"> 
	    <h5>ATENCION!, antes de cerrar sesión por favor enviar los comprobantes que estan pendientes de envío a SUNAT, abajo le mostrará enlistado por tipo de comprobante, solo tiene que hacer clik en el <b class="text-success">BOTON ENVIAR</b> que está en cada fila. </h5>
	</div>

	<h5 class="text-success"><b>FACTURAS ACTIVAS</b></h5>
	<div class="table-responsive mb-4">
		<table class="table table-bordered" style="font-size: 11px">
			<thead>
				<tr>
					<th width="100px"><i class="fa fa-send"></i> ENVIAR</th>
					<th>DOCUMENTO</th>
					<th>RAZON SOCIAL</th>
					<th>COMPROBANTE</th>
					<th>FECHA</th>
					<th>IMPORTE</th>
					<th width="10px">ESTADO</th>
					<th width="50px">XML</th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="dato in facturas1">
					<td style="padding-top:5px;"> 
						<button type="button" class="btn btn-success btn-sm" v-bind:id="dato.codkardex" v-on:click="comprobantes_enviar(dato.codkardex,'01')">
							<i class="fa fa-send"></i> ENVIAR
						</button> 
					</td>
					<td> {{dato.documento}} </td>
					<td> {{dato.cliente}} </td>
					<td> {{dato.seriecomprobante}} - {{dato.nrocomprobante}}</td>
					<td> {{dato.fechacomprobante}} </td>
					<td> S/. {{dato.importe}} </td>
					<td>
						<b v-if="dato.estado==0" style="color:#d43f3a">PENDIENTE</b>
						<b v-if="dato.estado==3" style="color:#eea236">CON EXCEPCIONES</b> 
						<b v-if="dato.estado==4" style="color:#eea236">RECHAZADO</b> 
					</td>
					<td style="padding-top:5px;"> 
						<button type="button" class="btn btn-info btn-sm" v-on:click="comprobantes_xml(dato.codkardex,'01')">
							<i class="fa fa-cloud-download"></i> XML
						</button>
					</td>
				</tr>
			</tbody>
		</table>
	</div>

	<div class="row form-group">
		<div class="col-md-7 col-xs-12">
			<h5 class="text-danger"><b>RESUMEN DE FACTURAS ANULADAS</b></h5>
		</div>
		<div class="col-md-5 col-xs-12" align="right">
			<button type="button" class="btn btn-danger btn-sm btn-block" v-on:click="resumenes_generar(1)">
				<i class="fa fa-file-o"></i> GENERAR RESUMEN FACTURAS ANULADAS
			</button>
		</div>
	</div>
	
	<div class="table-responsive">
		<table class="table table-bordered" style="font-size: 11px">
			<thead>
				<tr>
					<th width="100px"><i class="fa fa-send"></i> ENVIAR</th>
					<th>FECHA RESUMEN</th>
					<th>PERIODO</th>
					<th>NOMBRE XML</th>
					<th>ESTADO</th>
					<th width="50px">VER</th>
					<th width="10px"><i class="fa fa-trash-o"></i></th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="dato in facturas_anuladas1">
					<td style="padding-top:5px;"> 
						<button type="button" class="btn btn-success btn-sm" v-bind:id="dato.periodo" v-on:click="resumenes_enviar(dato.codresumentipo,dato.periodo,dato.nrocorrelativo)">
							<i class="fa fa-send"></i> ENVIAR
						</button>
					</td>
					<td> {{dato.fecharesumen}} </td>
					<td> {{dato.periodo}} </td>
					<td> {{dato.nombre_xml}} </td>
					<td>
						<b v-if="dato.estado==0" style="color:#d43f3a">PENDIENTE</b>
						<b v-if="dato.estado==3" style="color:#eea236">CON EXCEPCIONES</b> 
						<b v-if="dato.estado==4" style="color:#eea236">RECHAZADO</b> 
					</td>
					<td> 
						<button type="button" class="btn btn-primary btn-sm" v-on:click="resumenes_ver(dato.codresumentipo,dato.periodo,dato.nrocorrelativo)"><i class="fa fa-file"></i> VER</button>
					</td>
					<td>
						<button type="button" class="btn btn-danger btn-sm" v-on:click="resumenes_anular(dato.codresumentipo,dato.periodo,dato.nrocorrelativo)">
							<i class="fa fa-trash-o"></i>
						</button>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<h5 class="text-success"><b>RESUMEN DE BOLETAS</b></h5>
	<div class="table-responsive">
		<table class="table table-bordered" style="font-size: 11px">
			<thead>
				<tr>
					<th width="100px"><i class="fa fa-send"></i> ENVIAR</th>
					<th width="120px">TIPO</th>
					<th>FECHA RESUMEN</th>
					<th>PERIODO</th>
					<th>NOMBRE XML</th>
					<th>ESTADO</th>
					<th width="50px">XML</th>
					<th width="50px">VER</th>
					<th width="10px"><i class="fa fa-trash-o"></i></th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="dato in resumenes_boletas1">
					<td style="padding-top:5px;"> 
						<button type="button" class="btn btn-success btn-sm" v-bind:id="dato.periodo" v-on:click="resumenes_enviar(dato.codresumentipo,dato.periodo,dato.nrocorrelativo)">
							<i class="fa fa-send"></i> ENVIAR
						</button>
					</td>
					<td>
						<span class="label label-danger" v-if="dato.codresumentipo==3">RES. BOLETAS</span>
						<span class="label label-danger" v-if="dato.codresumentipo==4">RES. BOLETAS ANULADAS</span>
					</td>
					<td> {{dato.fecharesumen}} </td>
					<td> {{dato.periodo}} </td>
					<td> {{dato.nombre_xml}} </td>
					<td>
						<b v-if="dato.estado==0" style="color:#d43f3a">PENDIENTE</b>
						<b v-if="dato.estado==3" style="color:#eea236">CON EXCEPCIONES</b> 
						<b v-if="dato.estado==4" style="color:#eea236">RECHAZADO</b> 
					</td>
					<td style="padding-top:5px;"> 
						<button type="button" class="btn btn-info btn-sm" v-on:click="resumenes_xml(dato.codresumentipo,dato.periodo,dato.nrocorrelativo)"> <i class="fa fa-cloud-download"></i> XML</button>
					</td>
					<td style="padding-top:5px;"> 
						<button type="button" class="btn btn-primary btn-sm" v-on:click="resumenes_ver(dato.codresumentipo,dato.periodo,dato.nrocorrelativo)"><i class="fa fa-file"></i> VER</button>
					</td>
					<td style="padding-top:5px;">
						<button type="button" class="btn btn-danger btn-sm" v-on:click="resumenes_anular(dato.codresumentipo,dato.periodo,dato.nrocorrelativo)">
							<b>X</b>
						</button>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<h5 class="text-success"><b>GUIAS ELECTRONICAS GENERADAS</b></h5>
	<div class="table-responsive">
		<table class="table table-bordered" style="font-size: 11px">
			<thead>
				<tr>
					<th width="100px"><i class="fa fa-send"></i> ENVIAR</th>
					<th>DOCUMENTO</th>
					<th>DESTINATARIO</th>
					<th>COMPROBANTE</th>
					<th>FECHA</th>
					<th>MOTIVO</th>
					<th width="10px">ESTADO</th>
					<th width="50px">XML</th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="dato in guias1">
					<td style="padding-top:5px;"> 
						<button type="button" class="btn btn-success btn-sm" v-bind:id="dato.codguiar" v-on:click="guias_enviar(dato.codguiar,'09')">
							<i class="fa fa-send"></i> ENVIAR
						</button> 
					</td>
					<td> {{dato.documento}} </td>
					<td> {{dato.destinatario}} </td>
					<td> {{dato.seriecomprobante}} - {{dato.nrocomprobante}}</td>
					<td> {{dato.fechaguia}} </td>
					<td> {{dato.motivo}} </td>
					<td>
						<b v-if="dato.estado==0" style="color:#d43f3a">PENDIENTE</b>
						<b v-if="dato.estado==3" style="color:#eea236">CON EXCEPCIONES</b> 
						<b v-if="dato.estado==4" style="color:#eea236">RECHAZADO</b> 
					</td>
					<td style="padding-top:5px;"> 
						<button type="button" class="btn btn-info btn-sm" v-on:click="guias_xml(dato.codguiar,'09')">
							<i class="fa fa-cloud-download"></i> XML
						</button>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<h5 class="text-success"><b>NOTAS DE CREDITOS ACTIVOS</b></h5>
	<div class="table-responsive">
		<table class="table table-bordered" style="font-size: 11px">
			<thead>
				<tr>
					<th width="100px"><i class="fa fa-send"></i> ENVIAR</th>
					<th>DOCUMENTO</th>
					<th>RAZON SOCIAL</th>
					<th>COMPROBANTE</th>
					<th>FECHA</th>
					<th>IMPORTE</th>
					<th width="10px">ESTADO</th>
					<th width="50px">XML</th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="dato in notas_creditos1">
					<td style="padding-top:5px;"> 
						<button type="button" class="btn btn-success btn-sm" v-bind:id="dato.codkardex" v-on:click="comprobantes_enviar(dato.codkardex,'07')">
							<i class="fa fa-send"></i> ENVIAR
						</button> 
					</td>
					<td> {{dato.documento}} </td>
					<td> {{dato.cliente}} </td>
					<td> {{dato.seriecomprobante}} - {{dato.nrocomprobante}}</td>
					<td> {{dato.fechacomprobante}} </td>
					<td> S/. {{dato.importe}} </td>
					<td>
						<b v-if="dato.estado==0" style="color:#d43f3a">PENDIENTE</b>
						<b v-if="dato.estado==3" style="color:#eea236">CON EXCEPCIONES</b> 
						<b v-if="dato.estado==4" style="color:#eea236">RECHAZADO</b> 
					</td>
					<td style="padding-top:5px;"> 
						<button type="button" class="btn btn-info btn-sm" v-on:click="comprobantes_xml(dato.codkardex,'01')">
							<i class="fa fa-cloud-download"></i> XML
						</button>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div id="modal_resumenes" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header modal-phuyu-titulo">
					<h4 class="modal-title" align="center"> <b style="letter-spacing:1px;">INFORMACION DEL RESUMEN</b> </h4>

					<button type="button" class="btn-close" v-on:click="phuyu_cerrar()"> </button>
				</div>

				<div class="modal-body" style="height:350px;overflow-y:auto;">
					<table class="table table-bordered" style="font-size: 11px">
						<thead>
							<tr>
								<th>RAZON SOCIAL</th>
								<th>COMPROBANTE</th>
								<th>F.COMPROBANTE</th>
								<th>F.ANULADO</th>
								<th width="100px">MOTIVO</th>
								<th>TOTAL</th>
								<th width="5px"><i class="fa fa-trash-o"></i></th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="dato in resumenes_info1">
								<td>{{dato.cliente}}</td>
								<td>{{dato.seriecomprobante}} - {{dato.nrocomprobante}}</td>
								<td>{{dato.fechacomprobante}}</td>
								<td>{{dato.fechaanulacion}}</td>
								<td>{{dato.motivobaja}}</td>
								<td>{{dato.importe}}</td>
								<td>
									<button type="button" class="btn btn-danger btn-xs" v-on:click="resumenes_eliminar_kardex(dato)">
										<b>X</b>
									</button>
								</td>
							</tr>
						</tbody>
					</table>
					<div class="text-center">
						<button type="button" class="btn btn-info" v-on:click="resumenes_siguiente_correlativo()">SIGUIENTE CORRELATIVO</button>
						<button type="button" class="btn btn-danger" v-on:click="resumenes_actualizar()">ACTUALIZAR RESUMEN</button>
						<button type="button" class="btn btn-warning" v-on:click="resumenes_quitar_ticket()">QUITAR TICKET</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_facturacion/electronicos.js"> </script>
<script>
	var pantalla = jQuery(document).height(); $("#consultas_modal").css({height: pantalla - 65}); 
	$(".panel_boletas").css({height: pantalla - 505}); $(".panel_comprobantes").css({height: pantalla - 75});
</script>