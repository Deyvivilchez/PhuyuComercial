<style>
    #phuyu_datos.phuyu-inventarios-list .page-title-box {
        margin-bottom: 18px;
    }
    #phuyu_datos.phuyu-inventarios-list .page-title-box h4 {
        color: #212529;
        font-weight: 700;
        margin-bottom: 4px;
    }
    #phuyu_datos.phuyu-inventarios-list .breadcrumb {
        margin-bottom: 0;
    }
    #phuyu_datos.phuyu-inventarios-list .phuyu-list-card {
        border: 1px solid #e9ebec;
        border-radius: 8px;
        box-shadow: 0 1px 2px rgba(56, 65, 74, 0.08);
    }
    #phuyu_datos.phuyu-inventarios-list .phuyu-toolbar {
        align-items: center;
        gap: 8px;
    }
    #phuyu_datos.phuyu-inventarios-list .form-control,
    #phuyu_datos.phuyu-inventarios-list .form-select {
        border: 1px solid #d9e2ef;
        border-radius: 6px;
        box-shadow: none;
        min-height: 38px;
    }
    #phuyu_datos.phuyu-inventarios-list label {
        color: #495057;
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 6px;
        text-transform: uppercase;
    }
    #phuyu_datos.phuyu-inventarios-list .phuyu-data-table {
        font-size: 12px;
    }
    #phuyu_datos.phuyu-inventarios-list .phuyu-data-table thead th {
        background: #f3f6f9;
        color: #495057;
        font-size: 11px;
        text-transform: uppercase;
        white-space: nowrap;
    }
    #phuyu_datos.phuyu-inventarios-list .modal-content {
        border: 0;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.14);
    }
    #phuyu_datos.phuyu-inventarios-list .modal-header {
        background: #f3f6f9;
        border-bottom: 1px solid #e9ebec;
    }
</style>

<div id="phuyu_datos" class="phuyu-inventarios-list">
	<div class="row page-title-box">
		<div class="col-12">
            <h4 id="title">Inventarios</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
                    <li class="breadcrumb-item active">Inventarios</li>
                </ol>
            </nav>
        </div>
	</div>
	<div class="phuyu_body">
		<div class="card phuyu-list-card">
			<div class="card-body">
				<input type="hidden" id="phuyu_opcion" value="1">
				<div class="row g-2 phuyu-toolbar mb-3">
					<div class="col-sm-12 col-md-5 col-lg-4 col-xxl-2 mb-1">
	                    <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                            <input class="form-control datatable-search border-start-0" v-model="buscar" v-on:keyup="phuyu_buscar()" placeholder="BUSCAR REGISTRO . . ." />
                        </div>
	                </div>
	                <div class="col-sm-12 col-md-7 col-lg-8 col-xxl-10 text-end mb-1">
	                    <div class="d-flex flex-wrap justify-content-end phuyu-toolbar">
							<button type="button" class="btn btn-success" v-on:click="phuyu_nuevo()"> <i class="bi bi-plus-lg me-1"></i> Nuevo inventario </button>

							<button type="button" class="btn btn-primary" v-on:click="phuyu_abrirmodal()"> <i class="bi bi-database-up me-1"></i> Subir archivo </button>
	                    </div>
	                </div>
			    </div>
				<div>
					<div class="row form-group">
						<div class="table-responsive">
							<table class="table table-hover align-middle mb-0 phuyu-data-table">
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
											<span v-if="dato.estado==1" class="badge bg-success">ABIERTO</span>
											<span v-if="dato.estado==0" class="badge bg-danger">{{dato.fechaapertura}} (CERRADO)</span>
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
									        <button type="button" v-if="dato.estado!=1" class="btn btn-primary btn-sm" v-on:click="phuyu_reabririnventario(dato.codinventario)"><i class="bi bi-arrow-clockwise me-1"></i> REABRIR INVENTARIO</button>
									        <button type="button" v-if="dato.estado!=1" class="btn btn-info btn-sm" v-on:click="phuyu_verinventario(dato.codinventario)">VER</button>
									        <button type="button" v-if="dato.estado!=1" class="btn btn-warning btn-sm" v-on:click="phuyu_editarinventario(dato.codinventario)"><i class="bi bi-pencil-square me-1"></i> EDITAR</button>
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
					<h5 class="modal-title">Editar inventario 000{{editar.codinventario}}</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
							<button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i> Guardar editar</button>
							<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
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
					<h5 class="modal-title">Subir archivo CSV</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body">
					<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar_archivo()">
						<div class="form-group">
							<span class="foto">
								<input type="file" name="archivo" id="foto" class="upload" />
							</span>
							<label for="foto"> <span><i class="bi bi-upload me-1"></i> Cargar archivo CSV</span> </label>
						</div>
						<div class="form-group text-center">
							<button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i> Guardar inventario</button>
							<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
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
