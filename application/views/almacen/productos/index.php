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
