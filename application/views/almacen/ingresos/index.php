<style>
    #phuyu_ingresos.phuyu-almacen-list .page-title-box {
        margin-bottom: 18px;
    }
    #phuyu_ingresos.phuyu-almacen-list .page-title-box h4 {
        color: #212529;
        font-weight: 700;
        margin-bottom: 4px;
    }
    #phuyu_ingresos.phuyu-almacen-list .breadcrumb {
        margin-bottom: 0;
    }
    #phuyu_ingresos.phuyu-almacen-list .phuyu-filter-panel {
        background: #fff;
        border: 1px solid #e9ebec;
        border-radius: 8px;
        box-shadow: 0 1px 2px rgba(56, 65, 74, 0.08);
        padding: 12px;
    }
    #phuyu_ingresos.phuyu-almacen-list label {
        color: #495057;
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 6px;
        text-transform: uppercase;
    }
    #phuyu_ingresos.phuyu-almacen-list .form-control,
    #phuyu_ingresos.phuyu-almacen-list .form-select {
        border: 1px solid #d9e2ef;
        border-radius: 6px;
        box-shadow: none;
        min-height: 38px;
    }
    #phuyu_ingresos.phuyu-almacen-list .phuyu-list-card {
        border: 1px solid #e9ebec;
        border-radius: 8px;
        box-shadow: 0 1px 2px rgba(56, 65, 74, 0.08);
    }
    #phuyu_ingresos.phuyu-almacen-list .phuyu-toolbar {
        align-items: center;
        gap: 8px;
    }
    #phuyu_ingresos.phuyu-almacen-list .phuyu-toolbar .btn-icon {
        align-items: center;
        display: inline-flex;
        height: 38px;
        justify-content: center;
        width: 38px;
    }
    #phuyu_ingresos.phuyu-almacen-list .phuyu-data-table {
        font-size: 12px;
    }
    #phuyu_ingresos.phuyu-almacen-list .phuyu-data-table thead th {
        background: #f3f6f9;
        color: #495057;
        font-size: 11px;
        letter-spacing: .2px;
        text-transform: uppercase;
        white-space: nowrap;
    }
    #phuyu_ingresos.phuyu-almacen-list .modal-content {
        border: 0;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.14);
    }
    #phuyu_ingresos.phuyu-almacen-list .modal-header {
        align-items: center;
        background: #f3f6f9;
        border-bottom: 1px solid #e9ebec;
    }
    @media (max-width: 767.98px) {
        #phuyu_ingresos.phuyu-almacen-list .phuyu-toolbar {
            justify-content: flex-start !important;
        }
    }
</style>

<div id="phuyu_ingresos" class="phuyu-almacen-list">
    <div class="row g-3 align-items-end page-title-box">
        <div class="col-12 col-lg-5">
            <input type="hidden" id="formato" value="<?php echo $_SESSION['phuyu_formato'];?>">
            <h4 id="title">Administración Ingresos de Almacén</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
                    <li class="breadcrumb-item active">Ingresos</li>
                </ol>
            </nav>
        </div>
        <div class="col-12 col-lg-7">
            <div class="row g-2 phuyu-filter-panel">
                <div class="col-md-4 col-12">
                    <label><i class="bi bi-calendar3 me-1"></i>Desde</label>
                    <input type="date" class="form-control" id="fecha_desde" value="" v-on:blur="phuyu_buscar()" autocomplete="off">
                </div>
                <div class="col-md-4 col-12">
                    <label><i class="bi bi-calendar3 me-1"></i>Hasta</label>
                    <input type="date" class="form-control" id="fecha_hasta" value="<?php echo date('Y-m-d');?>" v-on:blur="phuyu_buscar()" autocomplete="off">
                </div>
                <div class="col-md-4 col-12 hidden-xs">
                    <label>Formato impresión</label>
                    <select class="form-select input-sm" v-model="formato_impresion" v-on:change="phuyu_formato()">
                        <option value="a4">A4 IMPRESION</option>
                        <option value="a5">A5 IMPRESION</option>
                        <option value="ticket">TICKET IMPRESION</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
	<div class="phuyu_body">
		<div class="card phuyu-list-card">
			<div class="card-body">
				<div class="row g-2 phuyu-toolbar mb-3">
					<div class="col-sm-12 col-md-4 col-lg-3 col-xxl-2 mb-1">
	                    <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                            <input class="form-control datatable-search border-start-0" v-model="buscar" v-on:keyup="phuyu_buscar()" placeholder="BUSCAR REGISTRO . . ." />
                        </div>
	                </div>
	                <div class="col-md-3">
				    	<select class="form-select" name="codmovimientotipo" v-model="movimiento" v-on:change="phuyu_buscar()">
					    		<option value="0">TODOS LOS MOVIMIENTOS</option>
					    		<?php
					    			foreach ($movimientos as $key => $value) { ?>
					    				<option value="<?php echo $value["codmovimientotipo"];?>">
					    					<?php echo $value["descripcion"];?>
					    				</option>
					    			<?php }
					    		?>
					    </select>
	                </div>
					<div class="col-sm-12 col-md-6 col-lg-6 col-xxl-10 text-end mb-1">
						<div class="d-flex flex-wrap justify-content-end phuyu-toolbar">

							<button type="button" class="btn btn-success btn-icon" title="Nuevo Ingreso" v-on:click="phuyu_nuevo()"> <i class="bi bi-plus-lg"></i></button>
							<button type="button" class="btn btn-info btn-icon" title="VER INGRESO" v-on:click="phuyu_ver()"> <i class="bi bi-eye"></i> </button>
						    <button type="button" class="btn btn-warning editar btn-icon" title="EDITAR INGRESO" v-on:click="phuyu_editar()"> <i class="bi bi-pencil-square"></i> </button>
						    <button type="button" class="btn btn-danger btn-icon btn-outline-icon eliminar" title="ELIMINAR INGRESO" v-on:click="phuyu_eliminar()"> <i class="bi bi-trash"></i> </button>
						    <button type="button" class="btn btn-primary btn-icon" title="IMPRIMIR INGRESO" v-on:click="phuyu_imprimir()"> <i class="bi bi-printer"></i></button>
						    <button type="button" class="btn btn-info btn-icon" title="CLONAR INGRESO" v-on:click="phuyu_clonar()"> <i class="bi bi-files"></i> </button>
						    <button type="button" class="btn btn-primary" v-on:click="phuyu_trasferencias()">
                                <i class="bi bi-arrow-left-right me-1"></i>
						        Transferencias
						    </button>
					    </div>
					</div>
				</div>
				<div class="phuyu_cargando" v-if="cargando">
					<div class="overlay-spinner"></div>
				</div>
				<div class="table-responsive">
					<table class="table table-hover align-middle mb-0 phuyu-data-table">
						<thead>
							<tr>
								<th width="12px">ID</th>
								<th>TIPO MOVIMIENTO</th>
								<th>RESPONSABLE</th>
								<th>FECHA</th>
								<th>COMPROBANTE</th>
								<th>COMPROBANTE REF.</th>
								<th>IMPORTE</th>
								<th>ESTADO</th>
								<th width="5px;"> <center> <i class="bi bi-record-circle"></i> </center> </th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="dato in datos" v-bind:class="[dato.estado==0 ? 'phuyu_anulado':'']">
								<td>{{dato.codkardex}}</td>
								<td>{{dato.tipomovimiento}}<br>
                                    <span v-if="dato.codmovimientotipo==12">Destino: {{dato.destino}}</span>
									<span v-if="dato.codmovimientotipo!=12"></span>
								</td>
								<td>{{dato.cliente}}</td>
								<td>{{dato.fechakardex}}</td>
								<td>{{dato.seriecomprobante}} - {{dato.nrocomprobante}}</td>
								<td>{{dato.tipo}} ({{dato.seriecomprobante_ref}} - {{dato.nrocomprobante_ref}})</td>
								<td>S/. {{dato.importe}}</td>
								<td>
									<span class="badge bg-danger" v-if="dato.estado==0">ANULADO</span>
									<span class="badge bg-warning text-dark" v-if="dato.estado==1">ACTIVO</span>
								</td>
								<td> <input type="radio" class="form-check-input" name="phuyu_seleccionar" v-on:click="phuyu_seleccionar(dato.codkardex,dato.estado)"> </td>
							</tr>
						</tbody>
					</table>
				</div>
				<?php include("application/views/phuyu/phuyu_paginacion.php");?>
			</div>
		</div>
	</div>

	<div id="modal_transferencias" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content" v-show="transferencias==1" align="center">
				<div class="modal-header modal-phuyu-titulo">
					<h4 class="modal-title" style="letter-spacing:1px;">
						<i data-acorn-icon="exchange"></i> <b>LISTA DE TRANFERENCIAS A ESTE ALMACEN</b> 
					</h4>
				</div>
				<div class="modal-body" style="height:270px;">
					<div class="col-md-6 col-xs-12" v-for="dato in listatransferencias">
						<div class="x_panel">
							<h4>
								<span class="label label-danger" style="color:#fff;">FECHA TRANSFERIDO: {{dato.fechakardex}}</span>
							</h4> <hr>
							<div style="text-align:left;">
								<h5>ALMACEN ORIGEN: {{dato.almacen}}</h5>
								<h5>DOCUMENTO: {{dato.seriecomprobante}} - {{dato.nrocomprobante}}</h5>
								<h5>DOCUMENTO REFERENCIA: {{dato.seriecomprobante_ref}} - {{dato.nrocomprobante_ref}}</h5>
								<h5 align="center"> 
									<span class="label label-warning">TOTAL IMPORTE TRANSFERIDO: S/. {{dato.importe}}</span>
								</h5>
								<h5 align="center"> 
									<button type="button" class="btn btn-success" v-on:click="phuyu_detalle(dato)">VER DETALLE</button> 
								</h5>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-danger" data-bs-dismiss="modal">CERRAR</button>
				</div>
			</div>

			<div class="modal-content" v-show="transferencias==0" align="center">
				<div class="modal-header"> <h4 class="modal-title"> 
					<b>LISTA DE PRODUCTOS <span class="label label-warning">{{texto_transferencia}}</span></b> </h4> 
				</div>
				<div class="modal-body" style="height:450px;">
					<form id="formulario_trans" class="form-horizontal" v-on:submit.prevent="phuyu_guardartransferencia()">
						<table class="table table-bordered" style="font-size: 11px">
							<thead>
								<tr align="center" >
									<th width="40%">PRODUCTO</th>
									<th width="20%">UNIDAD</th>
									<th width="20%">CANTIDAD</th>
									<th width="20%">SUBTOTAL</th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="(dato, index) in detalletransferencia">
									<td>
										<input type="hidden" class="phuyu-input-inv" v-model="dato.codproducto" readonly>
										{{dato.producto}} 
									</td>
									<td>
										<input type="hidden" class="phuyu-input-inv" v-model="dato.codunidad" readonly>
										{{dato.unidad}}
									</td>
									<td> 
										<input type="number" step="0.001" class="form-control number" v-model="dato.cantidad" v-on:keyup="phuyu_calcular(dato)"  min="0.001" required>
										<input type="hidden" step="0.01" class="phuyu-input-inv" v-model="dato.preciounitario" v-on:keyup="phuyu_calcular(dato)" min="0.01" > 
									</td>
									<td> 
										<input type="number" step="0.01" class="form-control number" v-model="dato.subtotal" readonly> 
									</td>
								</tr>
							</tbody>
						</table>
						<div class="modal-footer">
							<button type="submit" class="btn btn-success" v-bind:disabled="estado_envio==1">ACEPTAR TRANSFERENCIA</button>
							<button type="button" class="btn btn-danger" data-bs-dismiss="modal">CERRAR</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<div id="modal_prestamos" class="modal fade"  data-bs-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content" v-show="prestamos==0" align="center">
				<div class="modal-header modal-phuyu-titulo">
					<h4 class="modal-title" style="letter-spacing:1px;">
						<i data-acorn-icon="exchange"></i> <b>LISTA DE PRESTAMOS POR COBRAR</b> 
					</h4>
				</div>
				<div class="modal-body">
					<div class="row form-group">
						<div class="col-md-12">
							<div class="table-responsive">
								<table class="table table-bordered" style="font-size: 11px">
									<thead>
										<tr>
											<th>RAZON SOCIAL</th>
											<th>COMPROBANTE</th>
											<th>FECHA PRESTAMO</th>
											<th>SELECCIONAR</th>
										</tr>
									</thead>
									<tbody>
										<tr v-for="(dato,index) in detalle_prestamo">
											<td>{{dato.cliente}}</td>
											<td>{{dato.seriecomprobante}}-{{dato.nrocomprobante}}</td>
											<td>
												{{dato.fechakardex}}
											</td>
											<td>
												<button type="button" v-on:click="phuyu_detalleprestamo(dato)" class="btn btn-xs btn-block btn-info">ACEPTAR DEVOLUCION</button>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-danger" data-bs-dismiss="modal">CERRAR</button>
				</div>
			</div>
			<div class="modal-content" v-show="prestamos==1" align="center">
				<div class="modal-header modal-phuyu-titulo"> <h4 class="modal-title"> 
					<b>LISTA DE PRODUCTOS <span class="label label-warning">{{texto_transferencia}}</span></b> </h4> 
				</div>
				<div class="modal-body" style="height:450px;">
					<form id="formulario_pres" class="form-horizontal" v-on:submit.prevent="phuyu_guardarcobroprestamo()">
						<table class="table table-bordered" style="font-size: 10px">
							<thead>
								<tr align="center" >
									<th width="40%">PRODUCTO</th>
									<th width="20%">UNIDAD</th>
									<th width="20%">CANTIDAD</th>
									<th width="20%">SUBTOTAL</th>
									<th></th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="(dato, index) in detallecobroprestamo">
									<td>
										<input type="hidden" v-model="dato.item" name="">
										<input type="hidden" class="phuyu-input-inv" v-model="dato.codproducto" readonly>
										{{dato.producto}} 
									</td>
									<td>
										<select class="form-select number unidad" v-model="dato.codunidad" v-on:change="informacion_unidad(index,dato,this.value)" id="codunidad">
											<template v-for="(unidad, und) in dato.unidades">
												<option v-bind:value="unidad.codunidad" v-if="unidad.factor==1" selected>
													{{unidad.descripcion}}
												</option>
												<option v-bind:value="unidad.codunidad" v-if="unidad.factor!=1">
													{{unidad.descripcion}}
												</option>
											</template>
										</select>
									</td>
									<td> 
										<input type="number" step="0.001" class="form-control number" v-model="dato.cantidad" v-on:keyup="phuyu_calcularprestamo(dato)"  min="0.001" required>
										<input type="hidden" step="0.01" class="phuyu-input-inv" v-model="dato.preciounitario" v-on:keyup="phuyu_calcularprestamo(dato)" min="0.01" > 
									</td>
									<td> 
										<input type="number" step="0.01" class="form-control number" v-model="dato.subtotal" readonly> 
									</td>
									<td> 
										<button type="button" class="btn btn-danger btn-xs" style="margin-bottom:-1px;" v-on:click="phuyu_deleteitem(index,dato)">
											<b>X</b> 
										</button> 
									</td>
								</tr>
							</tbody>
						</table>
						<div class="modal-footer">
							<button type="submit" class="btn btn-success btn-icon" v-bind:disabled="estado_envio==1"><i data-acorn-icon="save"></i> ACEPTAR COBRO</button>
							<button type="button" class="btn btn-danger" v-on:click="cerrar_modalprestamo()">CANCELAR</button>
						</div>
					</form>
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
<script src="<?php echo base_url();?>phuyu/phuyu_almacen/ingresos.js"> </script>
