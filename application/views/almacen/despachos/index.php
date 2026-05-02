<style>
    #phuyu_datos.phuyu-despachos-list .page-title-box {
        margin-bottom: 18px;
    }
    #phuyu_datos.phuyu-despachos-list .page-title-box h4 {
        color: #212529;
        font-weight: 700;
        margin-bottom: 4px;
    }
    #phuyu_datos.phuyu-despachos-list .breadcrumb {
        margin-bottom: 0;
    }
    #phuyu_datos.phuyu-despachos-list .phuyu-list-card {
        border: 1px solid #e9ebec;
        border-radius: 8px;
        box-shadow: 0 1px 2px rgba(56, 65, 74, 0.08);
    }
    #phuyu_datos.phuyu-despachos-list .phuyu-toolbar {
        align-items: center;
        gap: 8px;
    }
    #phuyu_datos.phuyu-despachos-list .form-control,
    #phuyu_datos.phuyu-despachos-list .form-select {
        border: 1px solid #d9e2ef;
        border-radius: 6px;
        box-shadow: none;
        min-height: 38px;
    }
    #phuyu_datos.phuyu-despachos-list label {
        color: #495057;
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 6px;
        text-transform: uppercase;
    }
    #phuyu_datos.phuyu-despachos-list .phuyu-data-table {
        font-size: 12px;
    }
    #phuyu_datos.phuyu-despachos-list .phuyu-data-table thead th {
        background: #f3f6f9;
        color: #495057;
        font-size: 11px;
        letter-spacing: .2px;
        text-transform: uppercase;
        white-space: nowrap;
    }
    #phuyu_datos.phuyu-despachos-list .modal-content {
        border: 0;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.14);
    }
    #phuyu_datos.phuyu-despachos-list .modal-header {
        background: #f3f6f9;
        border-bottom: 1px solid #e9ebec;
    }
</style>

<div id="phuyu_datos" class="phuyu-despachos-list">
	<div class="row page-title-box">
		<div class="col-12">
			<input type="hidden" id="formato" value="<?php echo $_SESSION['phuyu_formato'];?>">
            <h4 id="title">Administración de Despachos Venta</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
                    <li class="breadcrumb-item active">Despachos Venta</li>
                </ol>
            </nav>
        </div>
    </div>
	<div class="phuyu_body">
		<div class="card phuyu-list-card">
			<div class="card-body">
				<input type="hidden" id="phuyu_opcion" value="1">

				<div class="row g-2 phuyu-toolbar mb-3">
					<div class="col-sm-12 col-md-4 col-lg-4 col-xxl-2 mb-1">
	                    <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                            <input class="form-control datatable-search border-start-0" v-model="buscar" v-on:keyup="phuyu_buscar()" placeholder="BUSCAR REGISTRO . . ." />
                        </div>
	                </div>
				    <div class="col-md-2 col-xs-12">
				    	<select class="form-select" v-model="estadodespacho" v-on:change="phuyu_buscar()">
				    		<option value="">TODOS</option>
				    		<option value="0">PENDIENTES</option>
				    		<option value="1">DESPACHADOS</option>	
				    	</select>
				    </div>
	                <div class="col-sm-12 col-md-6 col-lg-6 col-xxl-10 text-end mb-1">
	                    <div class="d-flex flex-wrap justify-content-end phuyu-toolbar">
							<button type="button" class="btn btn-info" v-on:click="phuyu_operacion(20)"> <i class="bi bi-truck me-1"></i> DESPACHAR VENTA </button>
		                </div>
	                </div>
			    </div>
				<div class="phuyu_cargando" v-if="cargando">
					<div class="overlay-spinner"></div>
				</div>
				<div v-if="!cargando">
					<div class="table-responsive">
						<table class="table table-hover align-middle mb-0 phuyu-data-table">
							<thead>
								<tr>
									<th>OPERACION</th>
									<th>DOCUMENTO</th>
									<th>RAZON SOCIAL</th>
									<th>FECHA</th>
									<th>TIPO</th>
									<th>COMPROBANTE</th>
									<th>IMPORTE</th>
									<th>ESTADO</th>
									<th width="5px;"> <center> <i class="bi bi-record-circle"></i> </center> </th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="dato in datos">
									<td>
										<span class="badge bg-danger" v-if="dato.codmovimientotipo==2">COMPRA</span>
										<span class="badge bg-warning text-dark" v-else="dato.codmovimientotipo==20">VENTA</span>
									</td>
									<td>{{dato.documento}}</td>
									<td>{{dato.razonsocial}}</td>
									<td>{{dato.fechakardex}}</td>
									<td>{{dato.tipo}}</td>
									<td>{{dato.seriecomprobante}} - {{dato.nrocomprobante}}</td>
									<td>S/. {{dato.importe}}</td>
									<td>
										<span class="badge bg-warning text-dark" v-if="dato.retirar==0">PENDIENTE</span>
										<span class="badge bg-success" v-else="dato.retirar==1">DESPACHADO</span>
									</td>
									<td> <input type="radio" v-if="dato.estado!=0" class="form-check-input" name="phuyu_seleccionar" v-on:click="phuyu_seleccionar(dato.codkardex,dato.codmovimientotipo)"> </td>
								</tr>
							</tbody>
						</table>
					</div> <hr>

					<?php include("application/views/phuyu/phuyu_paginacion.php");?>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="modal_buscarkardex" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Buscar el kardex de compra o venta</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body">
				<form id="formulario_filtro" v-on:submit.prevent="phuyu_filtrar()">
					<div class="row form-group">
						<div class="col-xs-12">
							<label>CLIENTE DE LA VENTA O PROVEEDOR DE LA COMPRA</label>
			    			<select class="form-control selectpicker ajax" name="codpersona" v-model="filtro.codpersona" id="codpersona" required data-live-search="true"> </select>
						</div>
					</div>
					<div class="row form-group">
						<div class="col-md-6 col-xs-12">
							<label>SERIE COMPROBANTE</label>
			    			<input type="text" class="form-control" name="seriecomprobante" v-model.trim="filtro.seriecomprobante" required maxlength="4" autocomplete="off">
						</div>
						<div class="col-md-6 col-xs-12">
							<label>NRO COMPROBANTE</label>
			    			<input type="text" class="form-control" name="seriecomprobante" v-model.trim="filtro.nrocomprobante" required maxlength="10" autocomplete="off">
						</div>
					</div>

					<div class="form-group" align="center">
						<button type="submit" class="btn btn-success"><i class="bi bi-search me-1"></i> Buscar comprobante</button>
						<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
					</div>

					<div class="row form-group">
						<div class="col-xs-12">
							<table class="table table-bordered">
								<thead>
									<tr>
										<th> </th>
										<th>OPER</th>
										<th>FECHA</th>
										<th>TIPO</th>
										<th>COMPROBANTE</th>
										<th>IMPORTE</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="dato in filtros">
										<td style="padding-bottom:9px !important;">
											<button type="button" class="btn btn-success btn-xs" v-on:click="phuyu_seleccionar_1(dato.codkardex)"> <i class="bi bi-check-lg"></i> </button>
										</td>
										<td>
											<span class="badge bg-danger" v-if="dato.codmovimientotipo==2">COMPRA</span>
											<span class="badge bg-warning text-dark" v-else="dato.codmovimientotipo==20">VENTA</span>
										</td>
										<td>{{dato.fechakardex}}</td>
										<td>{{dato.tipo}}</td>
										<td>{{dato.seriecomprobante}} - {{dato.nrocomprobante}}</td>
										<td>S/. {{dato.importe}}</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</form>
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
<script src="<?php echo base_url();?>phuyu/phuyu_despachos/index.js"> </script>
