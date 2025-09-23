<div id="phuyu_ventas">
	<div class="row">
		<div class="col-12 col-md-6">
            <h1 class="mb-0 pb-0 display-4" id="title">Administracion Guías Electrónicas</h1>
            <nav class="breadcrumb-container d-inline-block" aria-label="breadcrumb">
              <ul class="breadcrumb pt-0">
                <li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
                <li class="breadcrumb-item"><a href="javascript:;">Guias Electronicas</a></li>
              </ul>
            </nav>
        </div>
        <div class="col-12 col-md-6 d-flex align-items-start justify-content-end" style="font-size: 11px">
			<div class="col-md-4 col-xs-12">
				<label><i class="fa fa-calendar"></i> DESDE:</label>
				<input type="date" class="form-control input-sm" id="fecha_desde" value="" v-on:change="phuyu_buscar()" autocomplete="off">
			</div>
			<div class="col-md-4 col-xs-12">
				<label><i class="fa fa-calendar"></i> HASTA:</label>
				<input type="date" class="form-control input-sm" id="fecha_hasta" value="<?php echo date('Y-m-d');?>" v-on:change="phuyu_buscar()" autocomplete="off">
			</div>
        </div>
    </div>
	<div class="phuyu_body">
		<div class="card">
			<div class="card-body">
				<div class="row">
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
					    	<button type="button" class="btn btn-success btn-icon" title="Nuevo registro" data-bs-toggle="tooltip" v-on:click="phuyu_nuevo()"> <i data-acorn-icon="plus" class="icon"></i> Nuevo</button>
						    <button
	                        class="btn btn-icon eliminar btn-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Anular registro" type="button" data-bs-delay="0" v-on:click="phuyu_eliminar()"> <i data-acorn-icon="bin"></i> Eliminar</button>
					    	<button
	                        class="btn btn-icon btn-info"
	                        data-bs-toggle="tooltip"
	                        data-bs-placement="top"
	                        title="Ver registro"
	                        type="button"
	                        data-bs-delay="0" v-on:click="phuyu_ver()"
	                      >
	                        <i data-acorn-icon="eye"></i> Ver
	                      </button>
	                      <button
	                        class="btn btn-icon btn-primary"
	                        data-bs-toggle="tooltip"
	                        data-bs-placement="top"
	                        data-bs-delay="0"
	                        title="Imprimir pedido"
	                        type="button" v-on:click="phuyu_imprimir()"
	                      >
	                        <i data-acorn-icon="print"></i> Imprimir
	                      </button>
					    </div>
					</div>
				</div>
				<div class="phuyu_cargando" v-if="cargando">
					<div class="overlay-spinner"></div>
				</div>
				<div class="data-table-responsive-wrapper">
					<table class="table table-striped" style="font-size: 11px">
						<thead>
							<tr>
								<th width="5px"><i class="fa fa-file-o"></i></th>
								<th>DOCUMENTO</th>
								<th>RAZON SOCIAL</th>
								<th width="80px">FECHA</th>
								<th>TIPO</th>
								<th>COMPROBANTE</th>
								<th width="200px">MOTIVO</th>
								<th>ENVIO SUNAT</th>
								<th>DESCARGAR</th>
								<th width="5px;"> <center> <i class="fa fa-circle-o"></i> </center> </th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="dato in datos" v-bind:class="[dato.estado==0 ? 'phuyu_anulado':'']">
								<td>{{dato.codguiar}}</td>
								<td>{{dato.documento}}</td>
								<td>{{dato.razonsocial}}</td>
								<td>{{dato.fechaguia}}</td>
								<td>{{dato.tipo}}</td>
								<td>{{dato.seriecomprobante}} - {{dato.nrocomprobante}}</td>
								<td>{{dato.motivo}}</td>
								<td style="text-align: center">
									<h5 v-if="dato.estadosunat==0"><span class="badge bg-outline-danger">PENDIENTE</span></h5>
									<h5 v-if="dato.estadosunat==1"><span class="badge bg-outline-success">ENVIADO</span></h5>
								</td>
								<td>
									<div class="btn-group">
										<button type="button" class="btn btn-success dropdown-toggle mb-1 btn-sm" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> FORMATOS
										</button>
										<div class="dropdown-menu">
				                          <a class="dropdown-item" v-on:click="phuyu_docu('pdf',dato.codkardex)"><i></i> PDF</a>
				                          <a class="dropdown-item" v-on:click="phuyu_docu('xml',dato.codkardex)">XML</a>
				                          <a v-if="dato.estadosunat==1" v-on:click="phuyu_docu('cdr',dato.codkardex)" class="dropdown-item">CDR</a>
				                        </div>
									</div>
								</td>
								<td> 
									<input type="radio" class="form-check-input" name="phuyu_seleccionar" v-on:click="phuyu_seleccionar(dato.codguiar)"> 
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
	var pantalla = jQuery(document).height(); $("#reportes_modal").css({height: pantalla - 65});
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_guias/index.js"> </script>