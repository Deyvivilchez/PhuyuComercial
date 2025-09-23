<div id="phuyu_datos">
	<div class="row">
		<div class="col-12 col-md-6">
            <h1 class="mb-0 pb-0 display-4" id="title">Inventarios</h1>
            <nav class="breadcrumb-container d-inline-block" aria-label="breadcrumb">
              <ul class="breadcrumb pt-0">
                <li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
              </ul>
            </nav>
        </div>
	</div>
	<div class="phuyu_body">
		<div class="card">
			<div class="card-body">
				<input type="hidden" id="phuyu_opcion" value="1">
				<div class="row form-group">
					<div class="col-sm-12 col-md-5 col-lg-4 col-xxl-2 mb-1">
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
	                <div class="col-sm-12 col-md-7 col-lg-8 col-xxl-10 text-end mb-1">
	                    <div class="d-inline-block me-0 me-sm-3 float-start float-md-none">
	                    	<button type="button" class="btn btn-success btn-icon" v-on:click="phuyu_nuevo()"> <i data-acorn-icon="plus"></i> NUEVO INVENTARIO </button>

	    					<button type="button" class="btn btn-primary" v-on:click="phuyu_abrirmodal()"> <i class="fa fa-database"></i> SUBIR ARCHIVO </button>
	                    </div>
	                </div>
			    </div>
				<div>
					<div class="row form-group">
						<div class="table-responsive">
							<table class="table table-striped" style="font-size: 11px">
								<thead>
									<th>TIPO</th>
									<th>SUCURSAL</th>
									<th>ALMACEN</th>
									<th>F. APERTURA</th>
									<th>F. CIERRE</th>
									<th>IMPORTE</th>
									<th></th>	
								</thead>
								<tbody>
									<tr v-for="dato in datos">
										<td>{{dato.descripcion}}</td>
										<td>{{dato.sucursal}}</td>
										<td>{{dato.almacen}}</td>
										<td>{{dato.fechaapertura}}</td>
										<td>
											<span v-if="dato.estado==1" class="badge rounded-pill bg-outline-success" style="font-size: 12px">ABIERTO</span> 
						        			<span  v-if="dato.estado==0" class="badge rounded-pill bg-outline-danger" style="font-size: 12px">{{dato.fechaapertura}} (CERRADO)</span>
										</td>
										<td>
											S/. {{dato.importe_r}}
										</td>
										<td>
											<button type="button" v-if="dato.estado==1" class="btn btn-success btn-sm" v-on:click="phuyu_inventario(dato.codinventario)">INVENTARIO</button>
											<button type="button" v-if="dato.estado==1" class="btn btn-info btn-sm" v-on:click="phuyu_verinventario(dato.codinventario)">VER</button>
									        <button type="button" v-if="dato.estado==1" class="btn btn-danger btn-sm" v-on:click="phuyu_cerrarinventario(dato.codinventario)">
									        	CERRAR INVENTARIO
									        </button>
									        <button type="button" v-if="dato.estado!=1" class="btn btn-primary btn-sm" v-on:click="phuyu_reabririnventario(dato.codinventario)"><i class="fa fa-edit"></i> REABRIR INVENTARIO</button>
									        <button type="button" v-if="dato.estado!=1" class="btn btn-info btn-sm" v-on:click="phuyu_verinventario(dato.codinventario)">VER</button>
									        <button type="button" v-if="dato.estado!=1" class="btn btn-warning btn-sm" v-on:click="phuyu_editarinventario(dato.codinventario)"><i class="fa fa-edit"></i> EDITAR</button>
										</td>	
									</tr>	
								</tbody>
							</table>
						</div>
					</div>

					<?php include("application/views/phuyu/phuyu_paginacion.php");?>
				</div>
			</div>
		</div>
	</div>

	<div id="editar_inventario" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content" align="center">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" style="font-size:30px;margin-bottom:0px;">
						<i class="fa fa-times-circle"></i> 
					</button>
					<h4 class="modal-title"><b>EDITAR INVENTARIO 000{{editar.codinventario}}</b></h4>
				</div>
				<div class="modal-body">
					<button type="button" class="btn btn-success" v-on:click="phuyu_masproductos()">ACTUALIZAR NUEVOS PRODUCTOS REGISTRADOS</button>
					<form class="form-horizontal" v-on:submit.prevent="phuyu_guardar_editar()">
						<div class="form-group">
							<h5><b>BUSCAR PRODUCTO</b></h5>
							<select class="form-control selectpicker ajax" name="codproducto" id="codproducto" required data-live-search="true" v-on:change="phuyu_unidades()">
			    				<option value="">SELECCIONE PRODUCTO</option>
			    			</select>
						</div>
						<div class="table-responsive">
							<table class="table table-bordered">
								<thead>
									<tr>
										<th>PRODUCTO</th>
										<th>UNIDAD</th>
										<th>CANTIDAD</th>
										<th>S/. COSTO</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="(dato, index) in editardetalle">
										<td>{{dato.descripcion}}</td>
										<td>{{dato.unidad}}</td>
										<td>
											<input type="number" class="form-control input-sm" step="0.01" v-model="dato.cantidad" required>
										</td>
										<td>{{dato.preciocosto}}</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="form-group text-center">
							<button type="submit" class="btn btn-success"><i class="fa fa-save"></i> GUARDAR EDITAR</button>
							<button type="button" class="btn btn-danger" data-dismiss="modal">CANCELAR</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<div id="modal_subir" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content" align="center">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" style="font-size:30px;margin-bottom:0px;">
						<i class="fa fa-times-circle"></i> 
					</button>
					<h4 class="modal-title"><b>SUBIR ARCHIVO CSV</b></h4>
				</div>
				<div class="modal-body">
					<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar_archivo()">
						<div class="form-group">
							<span class="foto">
								<input type="file" name="archivo" id="foto" class="upload" />
							</span>
							<label for="foto"> <span><i class="fa fa-upload"></i> CARGAR ARCHIVO CSV</span> </label>
						</div>
						<div class="form-group text-center">
							<button type="submit" class="btn btn-success"><i class="fa fa-save"></i> GUARDAR INVENTARIO</button>
							<button type="button" class="btn btn-danger" data-dismiss="modal">CANCELAR</button>
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
<script src="<?php echo base_url();?>phuyu/phuyu_inventarios/index.js"> </script>
<script src="<?php echo base_url();?>phuyu/phuyu_almacen/buscar.js"> </script>