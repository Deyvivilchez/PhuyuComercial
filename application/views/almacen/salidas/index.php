<div id="phuyu_salidas">
	<div class="row">
		<div class="col-12 col-md-6">
			<input type="hidden" id="formato" value="<?php echo $_SESSION['phuyu_formato'];?>">
            <h1 class="mb-0 pb-0 display-4" id="title">Administración Salidas de Almacén</h1>
            <nav class="breadcrumb-container d-inline-block" aria-label="breadcrumb">
              <ul class="breadcrumb pt-0">
                <li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
                <li class="breadcrumb-item"><a href="javascript:;">Salidas</a></li>
              </ul>
            </nav>
        </div>
        <div class="col-12 col-md-6 d-flex align-items-start justify-content-end" style="font-size: 11px">
			<div class="col-md-4 col-xs-12">
				<label><i class="fa fa-calendar"></i> DESDE:</label>
				<input type="date" class="form-control" id="fecha_desde" value="" v-on:blur="phuyu_buscar()" autocomplete="off">
			</div>
			<div class="col-md-4 col-xs-12">
				<label><i class="fa fa-calendar"></i> HASTA:</label>
				<input type="date" class="form-control" id="fecha_hasta" value="<?php echo date('Y-m-d');?>" v-on:blur="phuyu_buscar()" autocomplete="off">
			</div>
			<div class="col-md-4 col-xs-12 hidden-xs">
				<label>FORMATO IMPRESION:</label>
				<select class="form-select input-sm" v-model="formato_impresion" v-on:change="phuyu_formato()">
					<option value="a4">A4 IMPRESION</option>
	        		<option value="a5">A5 IMPRESION</option>
	        		<option value="ticket">TICKET IMPRESION</option>
				</select>
			</div>
        </div>
    </div>
	<div class="phuyu_body">
		<div class="card">
			<div class="card-body">
				<div class="row">
					<div class="col-sm-12 col-md-4 col-lg-3 col-xxl-2 mb-1">
	                    <div class="d-inline-block float-md-start me-1 mb-1 search-input-container w-100 shadow bg-foreground">
	                      <input class="form-control datatable-search" v-model="buscar" v-on:keyup="phuyu_buscar()" placeholder="BUSCAR REGISTRO . . ." />
	                      <span class="search-magnifier-icon">
	                        <i data-acorn-icon="search"></i>
	                      </span>
	                      <span class="search-delete-icon d-none">
	                        <i data-acorn-icon="close"></i>
	                      </span>
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
				    	<div class="d-inline-block me-0 me-sm-3 float-start float-md-none">
					    	<button type="button" class="btn btn-success btn-icon" title="Nuevo Ingreso" v-on:click="phuyu_nuevo()"> <i data-acorn-icon="plus"></i></button>
					    	<button type="button" class="btn btn-info btn-icon" title="VER INGRESO" v-on:click="phuyu_ver()"> <i data-acorn-icon="eye"></i> </button>
						    <button type="button" class="btn btn-warning editar btn-icon" title="EDITAR INGRESO" v-on:click="phuyu_editar()"> <i data-acorn-icon="edit"></i> </button>
						    <button type="button" class="btn btn-danger btn-icon btn-outline-icon eliminar" title="ELIMINAR INGRESO" v-on:click="phuyu_eliminar()"> <i data-acorn-icon="bin"></i> </button>
						    <button type="button" class="btn btn-primary btn-icon" title="IMPRIMIR INGRESO" v-on:click="phuyu_imprimir()"> <i data-acorn-icon="print"></i></button>
						    <button type="button" class="btn btn-info btn-icon" title="CLONAR INGRESO" v-on:click="phuyu_clonar()"> <i data-acorn-icon="duplicate"></i> </button>
						    <button type="button" class="btn btn-success btn-icon guia" v-on:click="phuyu_asignarguia()"> <i data-acorn-icon="plus"></i> Asignar Guía </button>
					    </div>
					</div>
				</div>
				<div class="phuyu_cargando" v-if="cargando">
					<div class="overlay-spinner"></div>
				</div>
				<div class="table-responsive">
					<table class="table table-striped" style="font-size: 11px">
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
								<th width="5px;"> <center> <i class="fa fa-circle-o"></i> </center> </th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="dato in datos" v-bind:class="[dato.estado==0 ? 'phuyu_anulado':'']">
								<td>{{dato.codkardex}}</td>	
								<td>{{dato.tipomovimiento}}<br>
                                    <span v-if="dato.codmovimientotipo==30">Destino: {{dato.destino}}</span>
									<span v-if="dato.codmovimientotipo!=30"></span>
								</td>
								<td>{{dato.cliente}}</td>
								<td>{{dato.fechakardex}}</td>
								<td>{{dato.seriecomprobante}} - {{dato.nrocomprobante}}</td>
								<td>{{dato.tipo}} ({{dato.seriecomprobante_ref}} - {{dato.nrocomprobante_ref}})</td>
								<td>S/. {{dato.importe}}</td>
								<td>
									<span class="label label-danger" v-if="dato.estado==0">ANULADO</span>
									<span class="label label-warning" v-if="dato.estado==1">ACTIVO</span>
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
	<div id="modal_guia" class="modal fade" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content" style="margin-top: -20px">
				<div class="modal-header modal-phuyu-titulo"> 
					<h4 class="modal-title"> <b style="letter-spacing:1px;">RELLENAR DATOS DE LA GUIA</b> </h4>
					<button type="button" class="btn-close" data-bs-dismiss="modal" style="font-size:30px;margin-bottom:0px;">
					</button> 
				</div>
				<div class="modal-body" id="cuerpo">

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
						<b style="letter-spacing:4px;"><?php echo $_SESSION["phuyu_empresa"]." - ".$_SESSION["phuyu_sucursal"];?> </b>
					</h4>
				</div>
				<div class="modal-body" id="reportes_modal" style="height:650px;padding:0px;">
					<iframe id="phuyu_pdf" src="" style="width:100%; height:100%; border:none;"> </iframe>
				</div>
			</div>
		</div>
	</div>
	<div id="modal_prestamos" class="modal fade"  data-bs-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content" v-show="prestamos==0" align="center">
				<div class="modal-header modal-phuyu-titulo">
					<h4 class="modal-title" style="letter-spacing:1px;">
						<i data-acorn-icon="exchange"></i> <b>LISTA DE PRESTAMOS POR DEVOLVER</b> 
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
												<button type="button" v-on:click="phuyu_detalleprestamo(dato)" class="btn btn-xs btn-block btn-info">DEVOLVER PRESTAMOS</button>
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
							<button type="submit" class="btn btn-success btn-icon" v-bind:disabled="estado_envio==1"><i data-acorn-icon="save"></i> ACEPTAR SALIDA</button>
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
<script src="<?php echo base_url();?>phuyu/phuyu_almacen/salidas.js"> </script>