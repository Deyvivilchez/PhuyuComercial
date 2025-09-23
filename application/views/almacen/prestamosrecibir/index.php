<div id="phuyu_datos">
	<div class="row">
		<div class="col-12 col-md-6">
			<input type="hidden" id="formato" value="<?php echo $_SESSION['phuyu_formato'];?>">
            <h1 class="mb-0 pb-0 display-4" id="title">Administración Prestamos Otorgados x recibir</h1>
            <nav class="breadcrumb-container d-inline-block" aria-label="breadcrumb">
              <ul class="breadcrumb pt-0">
                <li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
                <li class="breadcrumb-item"><a href="javascript:;">Prestamos Otorgados x recibir</a></li>
              </ul>
            </nav>
        </div>
    </div>
	<div class="phuyu_body">
		<div class="card">
			<div class="card-body">
				<input type="hidden" id="phuyu_opcion" value="1">

				<div class="row">
					<div class="col-sm-12 col-md-4 col-lg-4 col-xxl-2 mb-1">
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
				    <div class="col-md-2 col-xs-12">
				    	<select class="form-select" v-model="estadodespacho" v-on:change="phuyu_buscar()">
				    		<option value="">TODOS</option>
				    		<option value="0">PENDIENTES</option>
				    		<option value="1">DEVUELTOS</option>	
				    	</select>
				    </div>
	                <div class="col-sm-12 col-md-6 col-lg-6 col-xxl-10 text-end mb-1">
	                    <div class="d-inline-block me-0 me-sm-3 float-start float-md-none">
	                    	<button type="button" class="btn btn-warning btn-icon btn-icon-end" v-on:click="phuyu_historial()"> <i data-acorn-icon="file-text"></i> HISTORIAL </button>
				    		<button type="button" class="btn btn-info btn-icon" v-on:click="phuyu_operacion(25)"> <i data-acorn-icon="exchange"></i> RECIBIR PRESTAMOS OTORGADOS</button>
		                </div>
	                </div>
			    </div>
				<div class="phuyu_cargando" v-if="cargando">
					<div class="overlay-spinner"></div>
				</div>
				<div v-if="!cargando">
					<div class="table-responsive">
						<table class="table table-striped" style="font-size: 11px">
							<thead>
								<tr>
									<th>DOCUMENTO</th>
									<th style="width: 30%">RAZON SOCIAL</th>
									<th>FECHA</th>
									<th>COMPROBANTE</th>
									<th>IMPORTE</th>
									<th>ESTADO</th>
									<th width="5px;"> <center> <i class="fa fa-circle-o"></i> </center> </th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="dato in datos">
									<td>{{dato.documento}}</td>
									<td>{{dato.cliente}}</td>
									<td>{{dato.fechakardex}}</td>
									<td>{{dato.seriecomprobante}} - {{dato.nrocomprobante}}</td>
									<td>S/. {{dato.importe}}</td>
									<td>
										<span class="alert alert-warning" v-if="dato.cantidaddevuelta!=0">PENDIENTE</span>
										<span class="alert alert-success" v-else="dato.cantidaddevuelta==dato.cantidad">DEVUELTO</span>
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
				<button type="button" class="close" data-dismiss="modal" style="font-size:30px;margin-bottom:0px;">
					<i class="fa fa-times-circle"></i> 
				</button>
				<h4 class="modal-title"> <b style="letter-spacing:2px;">BUSCAR EL KARDEX DE COMPRA O VENTA</b> </h4>
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
						<button type="submit" class="btn btn-success">BUSCAR COMPROBANTE</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">CERRAR</button>
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
											<button type="button" class="btn btn-success btn-xs" v-on:click="phuyu_seleccionar_1(dato.codkardex)"> <i class="fa fa-check"></i> </button>
										</td>
										<td>
											<span class="label label-danger" v-if="dato.codmovimientotipo==2">COMPRA</span>
											<span class="label label-warning" v-else="dato.codmovimientotipo==20">VENTA</span>
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
<script src="<?php echo base_url();?>phuyu/phuyu_prestamos/index.js"> </script>