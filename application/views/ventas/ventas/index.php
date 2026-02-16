<style type="text/css">
	.btn-xs{
		font-size: 10px;
    	line-height: 1.5;
	}
</style>
<div id="phuyu_ventas">
	<div class="row">
		<div class="col-12 col-md-6">
            <h1 class="mb-0 pb-0 display-4" id="title">Administracion Ventas</h1>
            <nav class="breadcrumb-container d-inline-block" aria-label="breadcrumb">
              <ul class="breadcrumb pt-0">
                <li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
                <li class="breadcrumb-item"><a href="javascript:;">Ventas</a></li>
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
			<div class="card-body" >
				<input type="hidden" id="caja" value="<?php echo $caja;?>"> 
				<input type="hidden" id="almacen" value="<?php echo $almacen;?>">
				<input type="hidden" id="formato" value="<?php echo $_SESSION['phuyu_formato'];?>">
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
	                      <!-- Add Button Start -->
	                      <button type="button" class="btn btn-icon btn-success" type="button" data-bs-toggle="tooltip"
	                        data-bs-placement="top"
	                        title="Nueva venta"
	                        type="button"
	                        data-bs-delay="0" v-on:click="phuyu_nuevo()">
	                      	<i data-acorn-icon="plus" class="icon"></i> Nuevo
	                      </button>
	                      <!-- Add Button End -->

	                      <!-- Delete Button Start -->
	                      <button
	                        class="btn btn-icon eliminar btn-danger"
	                        data-bs-toggle="tooltip"
	                        data-bs-placement="top"
	                        title="Anular venta"
	                        type="button"
	                        data-bs-delay="0" v-on:click="phuyu_eliminar()"
	                      >
	                        <i data-acorn-icon="bin"></i> Anular
	                      </button>
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
	                      <!-- Delete Button End -->
	                    </div>
	                    <div class="d-inline-block">
	                      <!-- Print Button Start -->
	                      <button
	                        class="btn btn-icon btn-icon-only btn-primary"
	                        data-bs-toggle="tooltip"
	                        data-bs-placement="top"
	                        data-bs-delay="0"
	                        title="Imprimir venta"
	                        type="button" v-on:click="phuyu_imprimir()"
	                      >
	                        <i data-acorn-icon="print"></i>
	                      </button>
	                      <!-- Print Button End -->

	                      <button
	                        class="btn btn-icon restaurar btn-icon-only btn-warning"
	                        data-bs-toggle="tooltip"
	                        data-bs-placement="top"
	                        data-bs-delay="0"
	                        title="Restaurar venta"
	                        type="button" v-on:click="restaurar_venta()"
	                      >
	                        <i data-acorn-icon="recycle"></i>
	                      </button>
	                      <!-- Export Dropdown End -->

	                      <button
	                        class="btn btn-icon btn-icon-only btn-info"
	                        data-bs-toggle="tooltip"
	                        data-bs-placement="top"
	                        data-bs-delay="0"
	                        title="Clonar venta"
	                        type="button" v-on:click="phuyu_clonar()"
	                      >
	                        <i data-acorn-icon="duplicate"></i>
	                      </button>
	                      <!-- Length End -->
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
								<th>ID</th>
								<th>COMPROBANTE</th>
								<th>DOCUMENTO</th>
								<th>RAZON SOCIAL</th>
								<th>FECHA</th>
								<th width="100px">IMPORTE</th>
								<th>PAGO</th>
								<th>E. SUNAT</th>
								<th>DESCARGAR</th>
								<th width="5px;"> <center> <i class="fa fa-circle-o"></i> </center> </th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="dato in datos" v-bind:class="[dato.estado==0 ? 'phuyu_anulado':'']">
								<td>{{dato.codkardex}}</td>
								<td>{{dato.abreviatura}}: ({{dato.seriecomprobante}} - {{dato.nrocomprobante}})<em v-if="dato.referencia!=''"><br>Pedido: {{dato.referencia}}</em><em v-if="dato.referencia==''"></em></td>
								<td>{{dato.documento}}</td>
								<td>{{dato.cliente}}</td>
								<td>{{dato.fechacomprobante}} - {{dato.hora}}</td>
								<td> <b style="font-size:12px;" v-if="dato.estado!=0" class="text-success">S/. {{dato.importe}}</b> <b style="font-size:12px;" v-if="dato.estado==0">S/. {{dato.importe}}</b> </td>
								<td>
									<h5 v-if="dato.condicionpago==1"><span class="badge bg-outline-primary">CONTADO</span></h5>
									<h5 v-else="dato.condicionpago==2"><span class="badge bg-outline-warning">CREDITO</span></h5>
								</td>
								<td style="text-align: center">
									<h5 v-if="dato.estadosunat==0"><span class="badge bg-outline-danger">PENDIENTE</span></h5>
									<h5 v-if="dato.estadosunat==1"><span class="badge bg-outline-success">ENVIADO</span></h5>
								</td>
								<td>
									<div class="btn-group">
				                        <button type="button" class="btn btn-success dropdown-toggle mb-1 btn-sm" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				                          FORMATOS
				                        </button>
				                        <div class="dropdown-menu">
				                          <a class="dropdown-item" v-on:click="phuyu_docu('pdf',dato.codkardex)"><i></i> PDF</a>
				                          <a class="dropdown-item" v-on:click="phuyu_docu('xml',dato.codkardex)">XML</a>
				                          <a v-if="dato.estadosunat==1" v-on:click="phuyu_docu('cdr',dato.codkardex)" class="dropdown-item">CDR</a>
				                        </div>
				                    </div>
								</td>
								<td> 
									<input type="radio" class="form-check-input" name="phuyu_seleccionar" v-on:click="phuyu_seleccionar(dato.codkardex,dato.estado)"> 
								</td>	
							</tr>
						</tbody>
					</table>
				</div>
				<?php include("application/views/phuyu/phuyu_paginacion.php");?>

			    <div id="modal_reportes" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
					<div class="modal-dialog" style="width:100%;margin:0px;">
						<div class="modal-content" align="center" style="border-radius:0px">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" style="font-size:30px;margin-bottom:0px;">
									<i class="fa fa-times-circle"></i> 
								</button>
								<h4 class="modal-title">
									<b style="letter-spacing:4px;"><?php echo $_SESSION["phuyu_empresa"];?> </b>
								</h4>
							</div>
							<div class="modal-body" id="reportes_modal" style="height:450px;padding:0px;">
								<iframe id="phuyu_pdf" src="" style="width:100%; height:100%; border:none;"> </iframe>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	var pantalla = jQuery(document).height(); $("#reportes_modal").css({height: pantalla - 65});
	// controls.select2.js initialization
    if (typeof Select2Controls !== 'undefined') {
      const select2Controls = new Select2Controls();
    }
	if (typeof AcornIcons !== 'undefined') {
      new AcornIcons().replace();
    }
    if (typeof Icons !== 'undefined') {
      const icons = new Icons();
    }
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_ventas/index.js"> </script>