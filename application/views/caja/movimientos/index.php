<div id="phuyu_datos">
	<div class="row">
		<div class="col-12 col-md-6">
			<?php 
	    		if ($_SESSION['phuyu_codcontroldiario']==0) {
	    			echo '<span class="label label-danger">CAJA CERRADA</span>';
	    		}
	    	?>
            <h1 class="mb-0 pb-0 display-4" id="title">Administracion Movimientos de Caja</h1>
            <nav class="breadcrumb-container d-inline-block" aria-label="breadcrumb">
              <ul class="breadcrumb pt-0">
                <li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
                <li class="breadcrumb-item"><a href="javascript:;">Movimientos</a></li>
              </ul>
            </nav>
        </div>
    </div>
	<div class="phuyu_body">
		<div class="card">
			<div class="card-body">
				<input type="hidden" id="phuyu_opcion" value="1">
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

					<?php 
						if ($_SESSION['phuyu_codcontroldiario']!=0) { ?>
					    	<div class="col-sm-12 col-md-7 col-lg-8  col-xxl-10 text-end mb-1">
					    		<div class="d-inline-block me-0 me-sm-3 float-start float-md-none">
							    	<button type="button" class="btn btn-icon btn-icon-end btn-success" v-on:click="phuyu_nuevo()">
								        <i data-acorn-icon="plus"></i> NUEVO
								    </button>
								    <button type="button" class="btn btn-info btn-icon btn-icon-end" v-on:click="phuyu_transferencias()">
								        <i data-acorn-icon="exchange" class="icon"></i> TRANSFERENCIAS 
								        <span class="label label-danger" style="color:#fff;"><?php echo $transferencias[0]["cantidad"];?></span>
								    </button>
								    <button type="button" class="btn btn-warning btn-icon btn-icon-end" v-on:click="phuyu_editar()">
								        <i data-acorn-icon="edit"></i> EDITAR 
								    </button>
								    <button type="button" class="btn btn-danger btn-icon btn-icon-end" v-on:click="phuyu_eliminar()">
								        <i data-acorn-icon="bin"></i> ELIMINAR
								    </button>
								</div>
						    </div>
						<?php }
					?>
	            </div>
				<div class="phuyu_cargando" v-if="cargando">
					<div class="overlay-spinner"></div>
				</div>
				<div v-if="!cargando">
					<div class="data-table-responsive-wrapper">
						<table class="table table-striped" style="font-size: 11px">
							<thead>
								<tr>
									<th width="80px">FECHA</th>
									<th width="110px">N° RECIBO</th>
									<th>CONCEPTO</th>
									<th>RAZÓN SOCIAL</th>
									<th>REFERENCIA</th>
									<th>TIPO</th>
									<th width="100px">S/ IMPORTE</th>
									<th width="5px;"> <center> <i class="fa fa-circle-o"></i> </center> </th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="(dato,index) in datos" v-bind:class="[dato.estado==0 ? 'phuyu_anulado':'']">
									<td>{{dato.fechamovimiento}}</td>
									<td>{{dato.seriecomprobante+"-"+dato.nrocomprobante}}</td>
									<td>{{dato.concepto}}</td>
									<td>{{dato.razonsocial}}</td>
									<td>{{dato.referencia}}</td>
									<td>
										<span class="label label-danger" v-if="dato.tipomovimiento==2">EGRESO</span>
										<span class="label label-warning" v-if="dato.tipomovimiento==1">INGRESO</span>
									</td>
									<td>S/. {{dato.importe_r}}</td>
									<td>
										<input type="radio" class="form-check-input" name="phuyu_seleccionar" v-on:click="phuyu_seleccionar(dato.codmovimiento)"> 
									</td>	
								</tr>
							</tbody>
						</table>
					</div>
					<?php include("application/views/phuyu/phuyu_paginacion.php");?>
				</div>
			</div>
		</div>
		<div id="modal_transferencias" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title"><b>LISTA DE TRANFERENCIAS A ESTA CAJA</b> 
						</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body" style="height:270px;overflow-y: auto;">
						<table class="table table-bordered table-condensed">
							<thead>
								<tr>
									<th width="80px">FECHA</th>
									<th width="110px">N° RECIBO</th>
									<th>CAJA</th>
									<th>CONCEPTO</th>
									<th>RAZÓN SOCIAL</th>
									<th width="100px">S/ IMPORTE</th>
									<th width="100px">ACEPTAR</th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="dato in transferencias">
									<td>{{dato.fechamovimiento}}</td>
									<td>{{dato.seriecomprobante+"-"+dato.nrocomprobante}}</td>
									<td>{{dato.caja}}</td>
									<td>{{dato.concepto}}</td>
									<td>{{dato.razonsocial}}</td>
									<td>S/. {{dato.importe_r}}</td>
									<td>
										<button type="button" class="btn btn-success btn-sm" v-on:click="phuyu_aceptar_transferencia(dato)">ACEPTAR</button>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_caja/movi_index.js"> </script>