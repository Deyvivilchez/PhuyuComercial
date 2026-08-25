<style>
    #phuyu_datos.phuyu-productos-list .page-title-box {
        margin-bottom: 18px;
    }
    #phuyu_datos.phuyu-productos-list .page-title-box h4 {
        color: #212529;
        font-weight: 700;
        margin-bottom: 4px;
    }
    #phuyu_datos.phuyu-productos-list .breadcrumb {
        margin-bottom: 0;
    }
    #phuyu_datos.phuyu-productos-list .phuyu-list-card {
        border: 1px solid #e9ebec;
        border-radius: 8px;
        box-shadow: 0 1px 2px rgba(56, 65, 74, 0.08);
    }
    #phuyu_datos.phuyu-productos-list .phuyu-toolbar {
        align-items: center;
        gap: 8px;
    }
    #phuyu_datos.phuyu-productos-list .form-control {
        border: 1px solid #d9e2ef;
        border-radius: 6px;
        box-shadow: none;
        min-height: 38px;
    }
    #phuyu_datos.phuyu-productos-list .phuyu-data-table {
        font-size: 12px;
    }
    #phuyu_datos.phuyu-productos-list .phuyu-data-table thead th {
        background: #f3f6f9;
        color: #495057;
        font-size: 11px;
        letter-spacing: .2px;
        text-transform: uppercase;
        white-space: nowrap;
    }
</style>

<div id="phuyu_datos" class="phuyu-productos-list">
	<div class="row page-title-box">
		<div class="col-12">
            <h4 id="title">Administración de Productos</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
                    <li class="breadcrumb-item active">Productos</li>
                </ol>
            </nav>
        </div>
	</div>
	<div class="phuyu_body">
		<input type="hidden" id="phuyu_opcion" value="1">
		<div class="card phuyu-list-card">
			<div class="card-body">
				<div class="row g-2 phuyu-toolbar mb-3">
					<div class="col-sm-12 col-md-5 col-lg-4 col-xxl-2 mb-1">
	                    <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                            <input class="form-control datatable-search border-start-0" v-model="buscar" v-on:keyup="phuyu_buscar()" placeholder="BUSCAR REGISTRO . . ." />
                        </div>
	                </div>
	                <div class="col-sm-12 col-md-7 col-lg-8 col-xxl-10 text-end mb-1">
	                    <div class="d-flex flex-wrap justify-content-end phuyu-toolbar">
							<button type="button" class="btn btn-success" data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="Nuevo registro"
                            data-bs-delay="0" v-on:click="phuyu_nuevo()">
								<i class="bi bi-plus-lg me-1"></i> Nuevo
							</button>

							<button type="button" class="btn btn-info" v-on:click="phuyu_ver()"> <i class="bi bi-eye me-1"></i> Ver </button>

	                      <button
	                        class="btn btn-warning"
	                        data-bs-toggle="tooltip"
	                        data-bs-placement="top"
	                        title="Editar registro"
	                        type="button"
	                        data-bs-delay="0" v-on:click="phuyu_editar()"
	                      >
	                        <i class="bi bi-pencil-square me-1"></i> Editar
	                      </button>

							<button type="button" class="btn btn-info" v-on:click="phuyu_operacion()"> <i class="bi bi-upload me-1"></i> Extra</button>
							<button type="button" class="btn btn-primary" v-on:click="phuyu_migrar_stock()"> <i class="bi bi-file-earmark-spreadsheet me-1"></i> Migrar/Actualizar Stock</button>
							<button type="button" class="btn btn-dark" v-on:click="phuyu_duplicados()"> <i class="bi bi-intersect me-1"></i> Duplicados</button>
	                      <button
	                        class="btn eliminar btn-danger"
	                        data-bs-toggle="tooltip"
	                        data-bs-placement="top"
	                        title="Eliminar registro"
	                        type="button"
	                        data-bs-delay="0" v-on:click="phuyu_eliminar()"
	                      >
	                        <i class="bi bi-trash me-1"></i> Eliminar
	                      </button>
	                    </div>
	                  </div>
			    </div>
				<div class="border rounded p-3 mb-3 bg-light" v-if="mostrarDuplicados">
					<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
						<div>
							<h5 class="mb-1">Productos duplicados</h5>
							<small class="text-muted">Se detectan por codigo de barra, codigo interno y descripcion exacta. Al unir se conserva el menor ID y los demas quedan deshabilitados.</small>
						</div>
						<div class="d-flex flex-wrap gap-2">
							<div class="input-group input-group-sm" style="width:260px">
								<span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
								<input type="text" class="form-control" v-model="filtroDuplicado" placeholder="Filtrar por ID, codigo o producto">
							</div>
							<select class="form-select form-select-sm" style="width:180px" v-model="tipoDuplicado" v-on:change="phuyu_cargar_duplicados()">
								<option value="todos">Todos</option>
								<option value="barra">Codigo de barra</option>
								<option value="codigo">Codigo interno</option>
								<option value="descripcion">Descripcion exacta</option>
							</select>
							<button type="button" class="btn btn-sm btn-outline-secondary" v-on:click="phuyu_cargar_duplicados()"><i class="bi bi-arrow-clockwise me-1"></i> Actualizar</button>
							<button type="button" class="btn btn-sm btn-outline-dark" v-on:click="mostrarDuplicados=false"><i class="bi bi-x-lg me-1"></i> Cerrar</button>
						</div>
					</div>
					<div class="table-responsive">
						<table class="table table-sm table-hover align-middle mb-0 phuyu-data-table">
							<thead>
								<tr>
									<th>Tipo</th>
									<th>Valor repetido</th>
									<th>Cant.</th>
									<th>Productos involucrados</th>
									<th width="90">Accion</th>
								</tr>
							</thead>
							<tbody>
								<tr v-if="cargandoDuplicados">
									<td colspan="5" class="text-center text-muted py-4">Buscando duplicados...</td>
								</tr>
								<tr v-if="!cargandoDuplicados && duplicados.length==0">
									<td colspan="5" class="text-center text-muted py-4">No se encontraron duplicados activos.</td>
								</tr>
								<tr v-if="!cargandoDuplicados && duplicados.length>0 && duplicadosFiltrados.length==0">
									<td colspan="5" class="text-center text-muted py-4">No hay coincidencias con ese filtro.</td>
								</tr>
								<tr v-for="grupo in duplicadosFiltrados">
									<td><span class="badge bg-secondary">{{grupo.tipo_nombre}}</span></td>
									<td>{{grupo.valor}}</td>
									<td>{{grupo.cantidad}}</td>
									<td>
										<div v-for="producto in grupo.productos" class="border-bottom py-1">
											<div><strong>ID:</strong> #{{producto.codproducto}} <strong>Codigo:</strong> {{producto.codigo || 'SIN CODIGO'}} <strong>Barra:</strong> {{producto.codigobarra || 'SIN BARRA'}}</div>
											<div>{{producto.descripcion}} <span class="text-muted">Stock: {{producto.stock}}</span></div>
										</div>
									</td>
									<td>
										<button type="button" class="btn btn-sm btn-warning" v-on:click="phuyu_unir_duplicado(grupo)">Unir</button>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<div class="phuyu_cargando" v-if="cargando">
					<div class="overlay-spinner">
					</div>
				</div>
				<div class="data-table-responsive-wrapper">
					<table class="table table-hover align-middle mb-0 phuyu-data-table">
						<thead>
							<tr>
								<th>ID</th>
								<th>CODIGO</th>
								<th>PRODUCTO</th>
								<th>UNIDAD</th>
								<th>MARCA</th>
								<th>PRECIO COSTO</th>
								<th>PRECIO VENTA</th>
								<th>STOCK</th>
								<th width="5px;"> <center> <i class="bi bi-record-circle"></i> </center> </th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="(dato,index) in datos" v-bind:class="[dato.estado==0 ? 'phuyu_anulado':'']">
								<td>{{dato.codproducto}}</td>
								<td>{{dato.codigo}}</td>
								<td>{{dato.descripcion}}</td>
								<td>{{dato.unidad}}</td>
                                <td>{{dato.marca}}</td>
								<td>S/. {{dato.costo}}</td>
								<td>S/. {{dato.precio}}</td>
								<td>{{dato.stock}}</td>
								<td> 
									<input type="radio" class=" form-check-input" name="phuyu_seleccionar" v-on:click="phuyu_seleccionar(dato.codproducto,dato.estado)"> 
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
<script>
	if (typeof AcornIcons !== 'undefined') {
      new AcornIcons().replace();
    }
    if (typeof Icons !== 'undefined') {
      const icons = new Icons();
    }
</script>

<script src="<?php echo base_url();?>phuyu/phuyu_almacen/productos_index.js"> </script>
