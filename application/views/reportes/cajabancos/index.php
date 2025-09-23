<div id="phuyu_datos">
	<div class="phuyu_body">
		<div class="card">
			<div class="card-body">
				<div class="row">
					<div class="col-md-12"> <h4> <b>REPORTE DE CAJA</b> </h4> </div>
				</div>
				<div class="row">
					<div class="col-md-4">
						<label>SELECCIONAR PERSONAS</label>
						<select class="form-select" id="codpersona" required>
							<option value="0">LISTA GENERAL - TODAS LAS PERSONAS</option>
						</select>
					</div>
					<div class="col-md-2"> 
						<label> <i class="fa fa-calendar"></i> CAJA DETALLADO AL</label> 
						<input type="date" class="form-control" id="fecha_detallado" value="<?php echo date('Y-m-d');?>">
					</div>
					<div class="col-md-6" style="margin-top:20px;">
						<button type="button" class="btn btn-warning btn-icon" v-on:click="caja_detallado()">
							<i data-acorn-icon="print"></i> CAJA DETALLADO
						</button>
						<button type="button" class="btn btn-success btn-icon" v-on:click="modal_conceptos()">
							<i data-acorn-icon="search"></i> TODOS LOS CONCEPTOS
						</button>
					</div>
				</div><hr>
				<div class="row">
					<div class="col-md-2"> 
						<label><i class="fa fa-calendar"></i> DESDE</label> 
						<input type="date" class="form-control" id="fecha_desde" value="<?php echo date('Y-m-d');?>">
					</div>
					<div class="col-md-2"> 
						<label><i class="fa fa-calendar"></i> HASTA</label> 
						<input type="date" class="form-control" id="fecha_hasta" value="<?php echo date('Y-m-d');?>">
					</div>
					
					<div class="col-md-1">
						<label>CAJAS</label><br>
						<input type="checkbox" style="height:25px;width:25px;" v-model="campos.caja"> 
					</div>
					<div class="col-md-1">
						<label>BANCO</label>
						<input type="checkbox" style="height:25px;width:25px;" v-model="campos.banco">
					</div>
					<div class="col-md-4" style="margin-top:20px">
						<button type="button" class="btn btn-success btn-icon" v-on:click="reporte_movimientos()">
							<i data-acorn-icon="search"></i> MOVIMIENTOS
						</button>
						<button type="button" class="btn btn-danger btn-icon" v-on:click="reporte_movimientos_anulados()">
							<i data-acorn-icon="search"></i> MOV. ANULADOS
						</button>
					</div>
					<div class="col-md-2" style="margin-top: 20px">
						<div class="dropdown">
                          <button class="btn btn-warning dropdown-toggle mb-1 btn-icon"
                            type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i data-acorn-icon="print"></i> FORMATOS
                          </button>
                          <div class="dropdown-menu">
                            <a class="dropdown-item" href="javascript:;" v-on:click="pdf_caja()">Formato PDF</a>
                            <a class="dropdown-item" href="javascript:;" v-on:click="excel_caja()">Formato Excel</a>
                          </div>
                        </div>
					</div>
				</div><br>
				<div class="col-md-12" id="phuyu_cajabancos" style="height:400px;overflow-y:auto;" >
					<table class="table table-striped" v-if="estado_detallado==1" style="font-size: 11px">
						<thead>
							<tr>
								<th width="120px">N° RECIBO</th>
								<th>CONCEPTO</th>
								<th>DOC.&nbsp;REFERENCIA</th>
								<th>RAZON SOCIAL</th>
								<th>REFERENCIA</th>
								<th>INGRESOS&nbsp;S/.</th>
								<th>EGRESOS&nbsp;S/.</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td colspan="5" align="right"> <b>SALDO ANTERIOR</b> </td>
								<td>{{saldocaja.ingresos}}</td>
								<td>{{saldocaja.egresos}}</td>
							</tr>
							<tr v-for="dato in detallado">
								<td>{{dato.seriecomprobante}} - {{dato.nrocomprobante}}</td>
								<td>{{dato.concepto}}</td>
								<td>{{dato.seriecomprobante_ref}} - {{dato.nrocomprobante_ref}}</td>
								<td>{{dato.razonsocial}}</td>
								<td>{{dato.referencia}}</td>
								<td> <b v-if="dato.tipomovimiento==1">{{dato.importe_r}}</b> </td>
								<td> <b v-if="dato.tipomovimiento==2">{{dato.importe_r}}</b> </td>
							</tr>
						</tbody>
						<tfoot>
							<tr>
								<td colspan="5" align="right"> <b>TOTALES</b> </td>
								<td>{{saldocaja.totalingresos}}</td>
								<td>{{saldocaja.totalegresos}}</td>
							</tr>
							<tr>
								<td colspan="7" align="right"> <b>SALDO (INGRESOS - EGRESOS): {{saldocaja.total}}</b> </td>
							</tr>
						</tfoot>
					</table>

					<table class="table table-striped" v-if="estado_movimientos==1" style="font-size: 11px">
						<thead>
							<tr>
								<th width="100px">FECHA</th>
								<th width="120px">N° RECIBO</th>
								<th>CONCEPTO</th>
								<th>DOC.&nbsp;REFERENCIA</th>
								<th>RAZON SOCIAL</th>
								<th>REFERENCIA</th>
								<th>INGRESOS&nbsp;S/.</th>
								<th>EGRESOS&nbsp;S/.</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="dato in movimientos">
								<td>{{dato.fechamovimiento}}</td>
								<td>{{dato.seriecomprobante}} - {{dato.nrocomprobante}}</td>
								<td>{{dato.concepto}}</td>
								<td>{{dato.seriecomprobante_ref}} - {{dato.nrocomprobante_ref}}</td>
								<td>{{dato.razonsocial}}</td>
								<td>{{dato.referencia}}</td>
								<td> <b v-if="dato.tipomovimiento==1">{{dato.importe_r}}</b> </td>
								<td> <b v-if="dato.tipomovimiento==2">{{dato.importe_r}}</b> </td>
							</tr>
						</tbody>
						<tfoot>
							<tr>
								<td colspan="6" align="right"> <b>TOTALES</b> </td>
								<td>{{saldocaja.totalingresos}}</td>
								<td>{{saldocaja.totalegresos}}</td>
							</tr>
							<tr>
								<td colspan="8" align="right"> <b>SALDO (INGRESOS - EGRESOS): {{saldocaja.total}}</b> </td>
							</tr>
						</tfoot>
					</table>
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

			<div id="modal_conceptos" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" style="font-size:30px;margin-bottom:0px;">
								<i class="fa fa-times-circle"></i> 
							</button>
							<h4 class="modal-title"> <b>CONCEPTOS DE CAJA</b> </h4>
						</div>
						<div class="modal-body">
							<div class="row">
								<div class="col-md-12">
									<table class="table table-bordered">
										<tr>
											<td style="width: 350px;"><b>MARCAR TODOS LOS CONCEPTOS</b></td>
											<td class="text-center"> <input type="checkbox" class="check-phuyu" id="marcar" v-on:change="phuyu_marcar()" style="height:20px;width:20px;" checked> </td>
										</tr>
									</table>
								</div>
							</div>	
							<div class="row">
								<div class="col-md-6" style="height:400px;overflow-y:scroll;">
									<table class="table table-bordered">
										<thead>
											<tr>
												<th>CONCEPTO INGRESO</th>
												<th>MARCAR</th>
											</tr>
										</thead>
										<tbody>

											<?php 
												foreach ($conceptosingresos as $key => $value) { ?>
													<tr>
														<td><?php echo $value["descripcion"];?></td>
														<td align="center">
															<input type="checkbox" name="conceptos" value="<?php echo $value['codconcepto'];?>" style="height:20px;width:20px;" checked>
														</td>
													</tr>
												<?php }
											?>
										</tbody>
									</table>
								</div>
								<div class="col-md-6" style="height:400px;overflow-y:scroll;">
									<table class="table table-bordered">
										<thead>
											<tr>
												<th>CONCEPTO EGRESO</th>
												<th>MARCAR</th>
											</tr>
										</thead>
										<tbody>
											<?php 
												foreach ($conceptosegresos as $key => $value) { ?>
													<tr>
														<td><?php echo $value["descripcion"];?></td>
														<td align="center">
															<input type="checkbox" name="conceptos" value="<?php echo $value['codconcepto'];?>" style="height:20px;width:20px;" checked>
														</td>
													</tr>
												<?php }
											?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script> 
	var campos = {"codpersona":0,"fecha_detallado":$("#fecharef").val(),"fecha_desde":$("#fecharef").val(),"fecha_hasta":$("#fecharef").val(),"caja":1,"banco":0,"reporte":0,"cliente":"TODAS LAS PERSONAS"};

	var pantalla = jQuery(document).height(); $("#reportes_modal").css({height: pantalla - 65});
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_reportes/cajabancos.js"> </script>
<script src="<?php echo base_url();?>phuyu/phuyu_selects.js"> </script>
