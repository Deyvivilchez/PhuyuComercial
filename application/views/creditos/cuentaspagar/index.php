<div id="phuyu_creditos">
	<div class="row">
		<div class="col-12 col-md-6">
			<input type="hidden" id="sessioncaja" value="<?php echo $_SESSION["phuyu_codcontroldiario"];?>"> 
            <h1 class="mb-0 pb-0 display-4" id="title"><span class="badge bg-danger" v-if="sessioncaja==0">La Caja no esta Aperturada</span> Cuentas por Pagar</h1>
            <nav class="breadcrumb-container d-inline-block" aria-label="breadcrumb">
              <ul class="breadcrumb pt-0">
                <li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
                <li class="breadcrumb-item"><a href="javascript:;">Cuentas por Pagar</a></li>
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
	                <div class="col-sm-12 col-md-7 col-lg-8  col-xxl-10 text-end mb-1">
			    		<div class="d-inline-block me-0 me-sm-3 float-start float-md-none">
					    	<button type="button" class="btn btn-icon btn-icon-end btn-success" v-on:click="phuyu_nuevo()">
						        <i data-acorn-icon="plus"></i> NUEVO CREDITO
						    </button>
						    <button type="button" class="btn btn-info btn-icon btn-icon-end" v-on:click="phuyu_cobranza()">
						        <i data-acorn-icon="money"></i> PAGAR 
						    </button>
						    <button type="button" class="btn btn-warning btn-icon btn-icon-end" v-on:click="phuyu_historial()"> <i data-acorn-icon="file"></i> HISTORIAL </button>
						    <button type="button" class="btn btn-success btn-icon btn-icon-end" v-on:click="phuyu_persona()"> <i data-acorn-icon="plus"></i> NUEVO PROVEEDOR</button>
						</div>
				    </div>
				</div>
				<div class="phuyu_cargando" v-if="cargando">
					<div class="overlay-spinner"></div>
				</div>
				<div v-if="!cargando">
					<div class="data-table-responsive-wrapper">
					<table class="table table-striped" style="font-size: 11px">
						<thead>
							<tr>
								<th>LOTE</th>
								<th>DOCUMENTO</th>
								<th>CLIENTE</th>
								<th>DIRECCION</th>
								<th>TELEFONO</th>
								<th>PENDIENTES</th>
								<th width="5px;"> <center> <i class="fa fa-circle-o"></i> </center> </th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="(dato,index) in datos">
								<td>{{dato.codlote}}</td>
								<td>{{dato.documento}}</td>
								<td>{{dato.razonsocial}}</td>
								<td>{{dato.direccion}}</td>
								<td>{{dato.telefonos}}</td>
								<td class="text-center">{{dato.creditos}}</td>
								<td> <input type="radio" class="form-check-input" name="phuyu_seleccionar" v-on:click="phuyu_seleccionar(dato.codpersona,dato.codlote)" v-if="sessioncaja==1"> </td>
							</tr>
						</tbody>
					</table>
				</div>
					<?php include("application/views/phuyu/phuyu_paginacion.php");?>
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
<script src="<?php echo base_url();?>phuyu/phuyu_creditos/cuentaspagar.js"> </script>