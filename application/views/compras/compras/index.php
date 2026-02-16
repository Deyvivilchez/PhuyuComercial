<div id="phuyu_compras">
	<div class="row">
		<div class="col-12 col-md-6">
			<input type="hidden" id="caja" value="<?php echo $caja;?>"> <input type="hidden" id="almacen" value="<?php echo $almacen;?>">
            <h1 class="mb-0 pb-0 display-4" id="title">Administración de Compras</h1>
            <nav class="breadcrumb-container d-inline-block" aria-label="breadcrumb">
              <ul class="breadcrumb pt-0">
                <li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
                <li class="breadcrumb-item"><a href="javascript:;">Compras</a></li>
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
        </div>
    </div>
	<div class="phuyu_body">
		<div class="card">
			<div class="card-body">
				<div class="row">
					<div class="col-sm-12 col-md-6 col-lg-4 col-xxl-2 mb-1">
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
	                <div class="col-sm-12 col-md-7 col-lg-8  col-xxl-10 text-end mb-1">
			    		<div class="d-inline-block me-0 me-sm-3 float-start float-md-none">
			    			<button type="button" class="btn btn-icon btn-icon-end btn-success" v-on:click="phuyu_nuevo()"> <i data-acorn-icon="plus"></i> Nuevo </button>
						    <button type="button" class="btn btn-icon btn-icon-end btn-info" v-on:click="phuyu_ver()"> <i data-acorn-icon="eye"></i> Ver </button>
						    <button type="button" class="btn btn-icon btn-icon-end btn-warning editar" v-on:click="phuyu_editar()"> <i data-acorn-icon="edit"></i> Editar </button>
						    <button type="button" class="btn btn-icon btn-icon-end btn-primary gasto" v-on:click="phuyu_egresos()"> <i data-acorn-icon="plus"></i> Gasto </button>
						    <button type="button" class="btn btn-icon btn-icon-end btn-danger eliminar" v-on:click="phuyu_eliminar()"> <i data-acorn-icon="bin"></i> Eliminar </button>
						    <button type="button" class="btn btn-icon btn-icon-end btn-info" v-on:click="phuyu_clonar()"> <i data-acorn-icon="duplicate"></i> Clonar </button>
			    		</div>
			    	</div>
			    </div>
				<div class="phuyu_cargando" v-if="cargando">
					<div class="overlay-spinner">
					</div>
				</div>
				<div class="data-table-responsive-wrapper">
					<table class="table table-striped" style="font-size: 11px">
						<thead>
							<tr>
								<th>ID</th>
								<th>DOCUMENTO</th>
								<th>RAZON SOCIAL</th>
								<th>FECHA</th>
								<th>TIPO</th>
								<th>COMPROBANTE</th>
								<th width="130px">IMPORTE</th>
								<th>PAGO</th>
								<th width="5px;"> <center> <i class="fa fa-circle-o"></i> </center> </th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="dato in datos" v-bind:class="[dato.estado==0 ? 'phuyu_anulado':'']">
								<td>{{dato.codkardex}}</td>
								<td>{{dato.documento}}</td>
								<td>{{dato.razonsocial}}</td>
								<td>{{dato.fechacomprobante}}</td>
								<td>{{dato.tipo}}</td>
								<td>{{dato.seriecomprobante}} - {{dato.nrocomprobante}}</td>
								<td>
									<b v-if="dato.codmoneda==1" style="font-size:14px;">S/.</b> 
									<b v-if="dato.codmoneda!=1" style="font-size:14px;">$</b> 
									<b style="font-size:14px;">{{dato.importe}}</b>
								</td>
								<td>
									<span class="badge bg-primary" v-if="dato.condicionpago==1">AL CONTADO</span>
									<span class="badge bg-warning" v-else="dato.condicionpago==2">AL CREDITO</span>
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
</div>
<script>
	if (typeof AcornIcons !== 'undefined') {
      new AcornIcons().replace();
    }
    if (typeof Icons !== 'undefined') {
      const icons = new Icons();
    }
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_compras/index.js"> </script>