<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<style>
	#phuyu_datos .report-shell { border: 1px solid #e9ebec; border-radius: 10px; box-shadow: 0 1px 2px rgba(56,65,74,.06); }
	#phuyu_datos .report-title { font-size: 1.05rem; font-weight: 700; margin: 0; }
	#phuyu_datos .filter-band { background: #f8fafc; border: 1px solid #edf0f2; border-radius: 8px; padding: 14px; }
	#phuyu_datos label { color: #495057; font-size: .72rem; font-weight: 700; margin-bottom: 6px; text-transform: uppercase; }
	#phuyu_datos .form-control, #phuyu_datos .form-select { border-color: #d9dee3; border-radius: 6px; min-height: 39px; }
	#phuyu_datos .action-row { display: flex; flex-wrap: wrap; gap: 8px; }
	#phuyu_datos .action-row .btn { align-items: center; display: inline-flex; gap: 6px; min-height: 39px; white-space: nowrap; }
	#phuyu_datos .results-box { border: 1px solid #edf0f2; border-radius: 8px; height: calc(100vh - 410px); min-height: 340px; overflow: auto; }
	#phuyu_datos .results-box table { margin-bottom: 16px; }
	#phuyu_datos .results-box th { background: #f3f6f9; }
</style>

<div id="phuyu_datos" class="phuyu-reportes-velzon phuyu-velzon-list">
	<div class="phuyu_body">
		<div class="card report-shell">
			<div class="card-body">

		<input type="hidden" id="rubro" value="<?php echo $_SESSION["phuyu_rubro"];?>" name="">
				<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
					<h4 class="report-title">REPORTE GENERAL DE CREDITOS</h4>
				</div>
				<div class="filter-band mb-3">
				<div class="row g-3 align-items-end">
					<div class="col-xl-4 col-lg-6">
						<label>PERSONAS</label>
						<select class="form-select" id="codpersona" required  v-on:change="phuyu_lineascredito()">
							<option value="0">LISTA GENERAL - TODAS LAS PERSONAS</option>
						</select>
					</div>
					<div class="col-xl-2 col-lg-3 col-md-6" v-if="rubro==6">
						<label>LINEAS</label>
						<select class="form-select" name="codlote" v-model="campos.codlote" id="codlote">
							<option value="0">TODAS LAS LINEAS</option>
			</select>
					</div>
					<div class="col-xl-2 col-lg-3 col-md-6">
						<label> <i class="bi bi-calendar3"></i> FECHA</label>
						<input type="date" class="form-control" id="fecha_saldos" value="<?php echo date('Y-m-d');?>">
					</div>
					<div class="col-xl-4 col-lg-6">
						<label>Saldos</label>
						<div class="action-row">
						<button type="button" class="btn btn-warning" v-on:click="saldo_creditos()">
							<i class="bi bi-search"></i> SALDOS
						</button>
						<button type="button" class="btn btn-warning" v-on:click="saldo_creditos_actual()">
							<i class="bi bi-printer"></i> SALDOS ACTUAL
						</button>
						</div>
					</div>
				</div>
				<hr class="my-3">
				<div class="row g-3 align-items-end">
					<div class="col-xl-2 col-lg-3 col-md-6">
						<label><i class="bi bi-calendar3"></i> DESDE</label>
						<input type="date" class="form-control" id="fecha_desde" value="<?php echo date('Y-m-01');?>" v-on:blur="phuyu_vacio()">
					</div>
					<div class="col-xl-2 col-lg-3 col-md-6">
						<label><i class="bi bi-calendar3"></i> HASTA</label>
						<input type="date" class="form-control" id="fecha_hasta" value="<?php echo date('Y-m-d');?>" v-on:blur="phuyu_vacio()">
					</div>
					<div class="col-xl-4 col-lg-6">
						<label>TIPO ESTADO</label>
						<select class="form-select input-sm" id="tipo_consulta" v-model="campos.tipo_consulta" v-on:change="phuyu_vacio()">
							<option value="1">ESTADO DE CUENTA</option>
							<option value="2">ESTADO DE CUENTA DETALLADO</option>
							<option value="3">ESTADO DE CUENTA INTERES ACTUALIZADO</option>
							<option value="4">ESTADO DE CUENTA DETALLADO INTERES ACTUALIZADO</option>
						</select>
					</div>
					<div class="col-xl-2 col-lg-3 col-md-6">
						<label>ESTADO</label>
						<select class="form-select input-sm" id="estado" v-model="campos.estado" v-on:change="phuyu_vacio()">
							<option value="0">TODOS</option>
							<option value="1">PENDIENTES</option>
							<option value="2">CANCELADOS</option>
						</select>
					</div>
					<div class="col-xl-2 col-lg-3 col-md-6">
						<label>TIPO CREDITO</label>
						<select class="form-select input-sm" id="tipo" v-model="campos.tipo" v-on:change="phuyu_vacio()">
							<option value="1">POR COBRAR</option>
							<option value="2">POR PAGAR</option>
						</select>
					</div>
					<div class="col-xl-2 col-lg-3 col-md-6">
						<label>MOSTRAR</label>
						<select class="form-select input-sm" id="mostrar" v-model="campos.mostrar" v-on:change="phuyu_vacio()">
							<option value="1" v-if="campos.tipo==1">POR CLIENTE</option>
							<option value="1" v-if="campos.tipo!=1">POR PROVEEDOR</option>
							<option value="2" v-if="campos.tipo_consulta==1">POR CREDITO</option>
						</select>
					</div>
					<div class="col-xl-8 col-lg-12">
						<label>Acciones</label>
						<div class="action-row">
						<button type="button" class="btn btn-primary" v-on:click="ver_creditos()">
							<i class="bi bi-search"></i> VER REPORTE
						</button>
						<button type="button" class="btn btn-danger" v-on:click="pdf_creditos()">
							<i class="bi bi-printer"></i> PDF REPORTE
						</button>
						<button type="button" class="btn btn-success" v-on:click="excel_creditos()">
							<i class="bi bi-file-earmark-excel"></i> EXCEL
						</button>
						<button type="button" class="btn btn-warning" v-on:click="actualizar_interes()">
							<i class="bi bi-save"></i> ACTUALIZAR INTERES
						</button>
						</div>
					</div>
				</div>
				</div>
				<div class="results-box" id="phuyu_creditos">
					<div v-if="campos.tipo_consulta==1">
						<div v-if="campos.mostrar==1">
							<div v-for="dato in estado_cuenta_socios">
								<table class="table table-bordered" style="font-size: 11px">
									<tr style="background:#f2f2f2">
										<th colspan="9">
											<b v-if="campos.tipo==1">CLIENTE:</b>
											<b v-if="campos.tipo!=1">PROVEEDOR:</b> {{dato.razonsocial}}  |
											<b>DIRECCION:</b> {{dato.direccion}}
										</th>
									</tr>
									<tr>
										<th style="width:8%;"><b>FECHA</b></th>
										<th style="width:10%;"><b>LINEA</b></th>
										<th style="width:10%;"><b>COMPROBANTE</b></th>
										<th style="width:40%;"><b>DESCRIPCION</b></th>
										<th style="width:8%;"><b>CARGO</b></th>
										<th style="width:8%;"><b>INTERES</b></th>
										<th style="width:10%;"><b>TOTAL CARGO</b></th>
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
										<td style="width:8%;">{{c.fecha}}</td>
										<td style="width:10%;">{{c.linea}}</td>
										<td style="width:10%;">{{c.comprobante}}</td>
										<td style="width:40%;">{{c.referencia}}</td>
										<td style="width:8%;" align="right">{{c.cargo}} </td>
										<td style="width:8%;" align="right">{{c.interes}} </td>
										<td style="width:10%;" align="right">{{c.cargototal}}</td>
										<td style="width:8%;" align="right">{{c.abono}} </td>
										<td style="width:8%;" align="right">{{c.saldo}} </td>
									</tr>
									<tr>
										<td colspan="4" align="right"><b>TOTALES</b></td>
										<td align="right"><b>{{dato.cargo}}</b></td>
										<td align="right"><b>{{dato.total_interes}}</b></td>
										<td align="right"><b>{{dato.cargototal}}</b></td>
										<td align="right"><b>{{dato.abono}}</b></td>

										<td align="right"><b>{{dato.saldo}}</b></td>
									</tr>
								</table>
							</div>
						</div>

						<div v-if="campos.mostrar==2">
							<div v-for="dato in estado_cuenta_creditos">
								<table class="table table-bordered" style="font-size: 11px">
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
							<table class="table table-bordered" style="font-size: 11px;">
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
									<td style="width:10%;">{{c.seriecomprobante}}-{{c.nrocomprobante}}</td>
									<td style="width:20%;">{{c.descripcion}}</td>
									<td style="width:8%;">{{c.unidad}}</td>
									<td style="width:7%;">{{c.cantidad}}</td>
									<td style="width:8%;">{{c.preciounitario}}</td>
									<td style="width:7%;" align="right">{{c.cargo}} </td>
									<td style="width:8%;" align="right">{{c.interes}} </td>
									<td style="width:8%;" align="right">{{c.cargototal}} </td>
									<td style="width:8%;" align="right">{{c.abono}} </td>
									<td style="width:8%;" align="right">{{c.saldo}} </td>
								</tr>
								<tr>
									<td colspan="7" align="right"><b>TOTALES</b></td>
									<td align="right"><b>{{dato.cargo}}</b></td>
									<td align="right"><b>{{dato.totalinteres}}</b></td>
									<td align="right"><b>{{dato.cargototal}}</b></td>
									<td align="right"><b>{{dato.abono}}</b></td>
									<td align="right"><b>{{dato.saldo}}</b></td>
								</tr>
							</table>
						</div>
					</div>

					<div v-if="campos.tipo_consulta==3">
						<div v-if="campos.mostrar==1">
							<div v-for="dato in estado_cuenta_socios ">
								<table class="table table-bordered" style="font-size: 11px">
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
								<table class="table table-bordered" style="font-size: 11px">
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
							<table class="table table-bordered" style="font-size: 11px">
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
							<table class="table table-bordered" style="font-size: 11px">
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
							<table class="table table-bordered" style="font-size: 11px">
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
							<button type="button" class="close" data-bs-dismiss="modal" style="font-size:30px;margin-bottom:0px;">
								<i class="bi bi-x-circle"></i>
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
	var campos = {"codpersona":0,"fecha_desde":"","fecha_hasta":"","fecha_saldos":"","tipo_consulta":1,"tipo":1,"mostrar":1,"saldos":0,"codlote":0,"estado":0,"cliente": "LISTA GENERAL - TODAS LAS PERSONAS"};
	var pantalla = jQuery(document).height(); $("#reportes_modal").css({height: pantalla - 65});
</script>

<script src="<?php echo base_url();?>phuyu/phuyu_reportes/creditos.js"> </script>
<script src="<?php echo base_url();?>phuyu/phuyu_selects.js"> </script>
