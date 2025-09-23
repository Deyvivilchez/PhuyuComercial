<div id="phuyu_notas">
	<div class="row">
		<div class="col-12 col-md-6">
			<input type="hidden" id="formato" value="<?php echo $_SESSION['phuyu_formatonotacredito'];?>">
			<input type="hidden" id="perfil" value="<?php echo $_SESSION["phuyu_codperfil"];?>" name="">
            <h1 class="mb-0 pb-0 display-4" id="title">Administracion Notas de Crédito</h1>
            <nav class="breadcrumb-container d-inline-block" aria-label="breadcrumb">
              <ul class="breadcrumb pt-0">
                <li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
                <li class="breadcrumb-item"><a href="javascript:;">Nota de crédito</a></li>
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
					    	<button type="button" class="btn btn-success btn-icon" title="Nueva nota" data-bs-toggle="tooltip" v-on:click="phuyu_nuevo()"> <i data-acorn-icon="plus" class="icon"></i> Nuevo</button>
						    <button
	                        class="btn btn-icon eliminar btn-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Anular nota credito" type="button" data-bs-delay="0" v-on:click="phuyu_eliminar()"> <i data-acorn-icon="bin"></i> Eliminar</button>
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
	                        title="Imprimir registro"
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
								<th>ID</th>
								<th width="10px">DOCUMENTO</th>
								<th>RAZON SOCIAL</th>
								<th width="90px">FECHA</th>
								<th>TIPO</th>
								<th width="10px">COMPROBANTE</th>
								<th width="10px">C.REFERENCIA</th>
								<th width="10px">IMPORTE</th>
								<th>DESCRIPCION</th>
								<th width="5px;"> <center> <i class="fa fa-circle-o"></i> </center> </th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="dato in datos" v-bind:class="[dato.estado==0 ? 'phuyu_anulado':'']">
								<td>{{dato.codkardex}}</td>
								<td>{{dato.documento}}</td>
								<td>{{dato.cliente}}</td>
								<td>{{dato.fechacomprobante}}</td>
								<td>{{dato.tipo}}</td>
								<td>{{dato.seriecomprobante}}-{{dato.nrocomprobante}}</td>
								<td>{{dato.seriecomprobante_ref}}-{{dato.nrocomprobante_ref}}</td>
								<td>S/&nbsp;{{dato.importe}}</td>
								<td>{{dato.descripcion}}</td>
								<td> 
									<input type="radio" class="form-check-input" name="phuyu_seleccionar" v-on:click="phuyu_seleccionar(dato.codkardex,dato.estado)"> 
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				<?php include("application/views/phuyu/phuyu_paginacion.php");?>
			</div>

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

<script>
	if (typeof AcornIcons !== 'undefined') {
      new AcornIcons().replace();
    }
    if (typeof Icons !== 'undefined') {
      const icons = new Icons();
    }
	var pantalla = jQuery(document).height(); $("#reportes_modal").css({height: pantalla - 65});
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_notas/index.js"> </script>