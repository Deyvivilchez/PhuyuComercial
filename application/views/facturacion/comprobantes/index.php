<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_sunat" class="phuyu-velzon-list phuyu-cpe-velzon">
	<div class="phuyu-page-title">
		<div class="phuyu-page-icon"><i class="bi bi-receipt"></i></div>
		<div>
			<div class="text-muted small text-uppercase fw-semibold">CPE</div>
			<h4 class="mb-0 fw-bold">Comprobantes electronicos</h4>
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb mb-0 mt-1">
					<li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
					<li class="breadcrumb-item active" aria-current="page">Comprobantes</li>
				</ol>
			</nav>
		</div>
	</div>

	<div class="phuyu_body">
		<div class="card phuyu-card">
			<div class="card-body">
				<input type="hidden" id="sucursal" value="<?php echo $_SESSION['phuyu_codsucursal'];?>" name="">
				<div class="row g-3 align-items-end mb-3">
					<div class="col-12 col-md-6 col-xl-2">
						<label>Sucursales</label>
						<select class="form-select" v-model="sucursal" v-on:change="phuyu_buscar()">
							<?php 
								foreach ($sucursal as $key => $value) { ?>
								<option value="<?php echo $value["codsucursal"];?>"><?php echo $value["descripcion"];?></option>
							<?php	}
							?>
						</select>
					</div>
					<div class="col-12 col-md-3 col-xl-2">
						<label><i class="bi bi-calendar-date me-1"></i> Desde</label>
						<input type="date" class="form-control" id="fecha_desde" value="<?php echo date('Y-m-01');?>" v-on:blur="phuyu_buscar()" autocomplete="off">
					</div>
					<div class="col-12 col-md-3 col-xl-2">
						<label><i class="bi bi-calendar-check me-1"></i> Hasta</label>
						<input type="date" class="form-control" id="fecha_hasta" value="<?php echo date('Y-m-d');?>" v-on:blur="phuyu_buscar()" autocomplete="off">
					</div>
					<div class="col-12 col-md-4 col-xl-2">
						<label><i class="bi bi-file-earmark-text me-1"></i> Comprobantes</label>
						<select class="form-select" v-model="comprobantetipo" v-on:change="phuyu_buscar()">
							<option value="0">TODOS</option>	
							<?php 
								foreach ($comprobantes as $key => $value) { ?>
								<option value="<?php echo $value["codcomprobantetipo"];?>"><?php echo $value["descripcion"];?></option>
							<?php	}
							?>
						</select>
					</div>
					<div class="col-12 col-md-3 col-xl-1">
						<label>Estado</label>
						<select class="form-select" v-model="estado_sunat" v-on:change="phuyu_buscar()">
							<option value="">TODOS</option>
							<option value="0">PENDIENTES</option>
							<option value="1">ENVIADOS</option>
						</select>
					</div>
					<div class="col-12 col-md-5 col-xl-3">
						<label>Buscar</label>
						<div class="input-group">
							<input type="search" class="form-control" v-model.trim="buscar" v-on:keyup.enter="phuyu_buscar()" placeholder="RUC, cliente, serie o número" autocomplete="off">
							<button type="button" class="btn btn-primary" v-on:click="phuyu_buscar()" v-bind:disabled="cargando">
								<span v-if="cargando" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
								<i v-else class="bi bi-search me-1"></i>{{cargando ? 'Buscando' : 'Buscar'}}
							</button>
						</div>
					</div>
				</div>
				<div class="d-flex justify-content-end mb-3">
					<button type="button" class="btn btn-danger" v-on:click="phuyu_buscar_candidatos()" v-bind:disabled="cargando || corrigiendo_lote">
						<span v-if="modo_lote==='consulta'" class="spinner-border spinner-border-sm me-1"></span>
						<i v-else class="bi bi-search me-1"></i>
						{{modo_lote==='consulta' ? 'Consultando SUNAT...' : (solo_habilitados ? 'Consultar nuevamente en SUNAT' : 'Consultar candidatos en SUNAT')}}
					</button>
				</div>
				<div v-if="cargando" class="text-center py-5">
					<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Buscando...</span></div>
					<div class="text-muted mt-2">Buscando comprobantes...</div>
				</div>
				<div v-if="!cargando">
					<div v-if="datos.length==0" class="alert alert-light border text-center py-4">
						<i class="bi bi-search fs-4 d-block mb-2"></i>
						No se encontraron comprobantes con los filtros indicados.
					</div>
					<div v-else class="table-responsive">
						<table class="table table-hover table-striped align-middle" style="font-size: 11px">
							<thead>
								<tr>
									<th width="10px">TIPO</th>
									<th>Razon social cliente</th>
									<th>FECHA</th>
									<th width="10px">COMPROBANTE</th>
									<th width="10px">IMPORTE</th>
									<th>Descripcion</th>
									<th>SUNAT</th>
									<th width="10px">XML</th>
									<th width="10px">CDR</th>
									<th width="10px" class="text-center">VALIDAR</th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="dato in datos">
									<td><span class="label label-success">{{dato.tipo}}</span></td>
									<td>{{dato.documento}}-{{dato.cliente}}</td>
									<td>{{dato.fechacomprobante}}</td>
									<td>{{dato.seriecomprobante}}-{{dato.nrocomprobante}}</td>
									<td>{{dato.importe}}</td>
									<td>{{dato.descripcion_cdr}}</td>
									<td>
										<span class="label label-danger" v-if="dato.estado==0">PENDIENTE</span>
										<span class="label label-success" v-else="dato.estado!=0">ENVIADO</span>
									</td>
									<td>
										<button type="button" class="btn btn-info btn-xs btn-table" style="margin:1px;" v-on:click="phuyu_xml(dato.codkardex)"><i class="bi bi-download"></i> XML</button>
									</td>
									<td>
										<button type="button" class="btn btn-warning btn-xs btn-table" style="margin:1px;" v-on:click="phuyu_cdr(dato.codkardex)"><i class="bi bi-download"></i> CDR</button>
									</td>
									<td>
										<button type="button" class="btn btn-success btn-xs btn-table" style="margin:1px;" title="Consultar estado directamente en SUNAT" v-bind:disabled="dato.validando_sunat" v-on:click="phuyu_validar_sunat(dato)">
											<i class="bi" v-bind:class="dato.validando_sunat ? 'bi-arrow-repeat' : 'bi-shield-check'"></i>
											<span v-if="!dato.validando_sunat"> SUNAT</span>
										</button>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<?php include("application/views/phuyu/phuyu_paginacion.php");?>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="modalRespuestaSunat" tabindex="-1" aria-labelledby="modalRespuestaSunatTitulo" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="modalRespuestaSunatTitulo"><i class="bi bi-shield-check me-2"></i>Consulta oficial SUNAT</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</div>
				<div class="modal-body text-center py-4">
					<div class="text-muted mb-2" v-if="comprobante_consultado">{{comprobante_consultado.seriecomprobante}}-{{comprobante_consultado.nrocomprobante}}</div>
					<span class="badge fs-6 px-3 py-2" v-bind:class="'bg-'+respuesta_sunat.nivel">{{respuesta_sunat.mensaje}}</span>
					<p class="text-muted mb-0 mt-3" v-if="respuesta_sunat.detalle">{{respuesta_sunat.detalle}}</p>
					<div class="small text-muted mt-2">Resultado consultado directamente en el portal de SUNAT.</div>
					<button v-if="puede_reprogramar_resumen" type="button" class="btn btn-warning mt-3" v-bind:disabled="reprogramando_resumen" v-on:click="phuyu_reprogramar_resumen()">
						<span v-if="reprogramando_resumen" class="spinner-border spinner-border-sm me-1"></span>
						<i v-else class="bi bi-arrow-repeat me-1"></i>
						{{reprogramando_resumen ? 'Verificando...' : 'Habilitar para nuevo resumen'}}
					</button>
					<button v-if="puede_sincronizar_aceptado" type="button" class="btn btn-success mt-3" v-bind:disabled="sincronizando_estado" v-on:click="phuyu_sincronizar_aceptado()">
						<span v-if="sincronizando_estado" class="spinner-border spinner-border-sm me-1"></span>
						<i v-else class="bi bi-arrow-repeat me-1"></i>
						{{sincronizando_estado ? 'Verificando...' : 'Sincronizar estado aceptado'}}
					</button>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="modalCandidatosSunat" tabindex="-1" aria-labelledby="modalCandidatosSunatTitulo" aria-hidden="true">
		<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
			<div class="modal-content">
				<div class="modal-header">
					<div>
						<h5 class="modal-title" id="modalCandidatosSunatTitulo"><i class="bi bi-shield-exclamation me-2 text-danger"></i>Candidatos para nuevo resumen</h5>
						<div class="small text-muted mt-1">Rango: {{rango_candidatos.desde}} al {{rango_candidatos.hasta}} · {{rango_candidatos.dias}} días</div>
					</div>
					<div class="ms-auto me-3">
						<button v-if="candidatos_sunat.length" type="button" class="btn btn-warning" v-bind:disabled="habilitando_todos || candidatos_pendientes===0" v-on:click="phuyu_habilitar_todos()">
							<span v-if="habilitando_todos" class="spinner-border spinner-border-sm me-1"></span><i v-else class="bi bi-arrow-repeat me-1"></i>
							{{habilitando_todos ? 'Habilitando '+progreso_habilitando+' de '+candidatos_sunat.length : 'Habilitar todos'}}
						</button>
					</div>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar" v-bind:disabled="habilitando_todos"></button>
				</div>
				<div class="modal-body">
					<div class="alert alert-info py-2"><b>Consulta terminada:</b> SUNAT reportó {{candidatos_sunat.length}} comprobante(s) como <b>NO EXISTE</b>. Habilítalos individualmente.</div>
					<div v-if="candidatos_sunat.length===0" class="text-center text-muted py-5"><i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>No se encontraron candidatos en este rango.</div>
					<div v-else class="table-responsive">
						<table class="table table-hover align-middle mb-0">
							<thead><tr><th>Fecha</th><th>Comprobante</th><th>Cliente</th><th>Importe</th><th>Resultado SUNAT</th><th class="text-end">Acción</th></tr></thead>
							<tbody><tr v-for="dato in candidatos_sunat" v-bind:key="dato.codkardex">
								<td>{{dato.fechacomprobante}}</td><td><b>{{dato.seriecomprobante}}-{{dato.nrocomprobante}}</b></td><td>{{dato.documento}} - {{dato.cliente}}</td><td>{{dato.importe}}</td>
								<td><span class="badge bg-danger">NO EXISTE</span></td>
								<td class="text-end"><button type="button" class="btn btn-sm" v-bind:class="dato.habilitada ? 'btn-success' : 'btn-warning'" v-bind:disabled="dato.habilitando || dato.habilitada" v-on:click="phuyu_habilitar_candidato(dato)"><span v-if="dato.habilitando" class="spinner-border spinner-border-sm me-1"></span><i v-else class="bi me-1" v-bind:class="dato.habilitada ? 'bi-check-circle' : 'bi-arrow-repeat'"></i>{{dato.habilitada ? 'Habilitado' : (dato.habilitando ? 'Habilitando...' : 'Habilitar')}}</button></td>
							</tr></tbody>
						</table>
					</div>
				</div>
				<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal" v-bind:disabled="habilitando_todos">Cerrar</button></div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="modalProgresoSunat" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
		<div class="modal-dialog modal-sm modal-dialog-centered">
			<div class="modal-content border-0 shadow-lg">
				<div class="modal-body text-center px-4 py-5">
					<div class="position-relative d-inline-flex align-items-center justify-content-center mb-4" style="width:78px;height:78px">
						<div class="spinner-border text-danger position-absolute w-100 h-100" role="status"></div>
						<i class="bi bi-shield-check text-danger fs-2"></i>
					</div>
					<h5 class="mb-2">Consultando en SUNAT</h5>
					<p class="text-muted small mb-3">Validando oficialmente las boletas del {{rango_candidatos.desde}} al {{rango_candidatos.hasta}}.</p>
					<div v-if="total_consulta_sunat" class="mb-2"><b>{{progreso_lote}}</b> de <b>{{total_consulta_sunat}}</b> comprobantes</div>
					<div v-else class="mb-2 text-muted">Preparando comprobantes…</div>
					<div class="progress" style="height:7px"><div class="progress-bar progress-bar-striped progress-bar-animated bg-danger" v-bind:style="{width: total_consulta_sunat ? Math.round((progreso_lote/total_consulta_sunat)*100)+'%' : '100%'}"></div></div>
					<div class="small text-muted mt-3">No cierres esta ventana mientras finaliza la consulta.</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script src="<?php echo base_url();?>phuyu/phuyu_facturacion/comprobantes.js"> </script>
