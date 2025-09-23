<div id="phuyu_ventas">
	<div class="row">
		<div class="col-12 col-md-6">			
        	<input type="hidden" id="sessioncaja" value="<?php echo $_SESSION["phuyu_codcontroldiario"];?>">
			<input type="hidden" id="comprobante" value="<?php echo $comprobante;?>">
            <h1 class="mb-0 pb-0 display-4" id="title">Administracion de Chacras</h1>
            <nav class="breadcrumb-container d-inline-block" aria-label="breadcrumb">
              <ul class="breadcrumb pt-0">
                <li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
                <li class="breadcrumb-item"><a href="javascript:;">Chacras</a></li>
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
					<div class="col-sm-12 col-md-3 col-lg-3 col-xxl-2 mb-1">
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
	                <div class="col-sm-12 col-md-9 col-lg-9 col-xxl-10 text-end mb-1">
				    	<div class="d-inline-block me-0 me-sm-3 float-start float-md-none">

					    	<button type="button" class="btn btn-success btn-icon" v-on:click="phuyu_nuevo()"> <i data-acorn-icon="plus"></i> Nuevo</button>
					    	<button type="button" class="btn btn-warning btn-icon btn-icon-only" v-on:click="phuyu_editar()"> <i data-acorn-icon="edit"></i></button>
						    <button type="button" class="btn btn-danger btn-icon btn-icon-only" v-on:click="phuyu_eliminar()"> <i data-acorn-icon="bin"></i></button>
					    	<button type="button" class="btn btn-info btn-icon btn-icon-only" v-on:click="phuyu_ver()"> <i data-acorn-icon="eye"></i></button>
					    	<button type="button" class="btn btn-info btn-icon" v-on:click="phuyu_asignargasto()"> <i data-acorn-icon="plus"></i> Gastos</button>
					    	<button type="button" class="btn btn-info btn-icon" v-on:click="phuyu_salidaproductos()"> <i data-acorn-icon="plus"></i> Insumos</button>
					    	<button type="button" class="btn btn-info btn-icon" v-on:click="phuyu_ingresoproductos()"> <i data-acorn-icon="plus"></i> Produccion</button>
					    	<button type="button" class="btn btn-warning btn-icon" v-on:click="phuyu_historial()"> <i data-acorn-icon="eye"></i> Historial</button>
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
								<th>UBIGEO</th>
								<th>DIRECCION</th>
								<th>AREA(M2)</th>
								<th>FECHA</th>
								<th width="100px">TIPO POSESION</th>
								<th>ESTADO</th>
								<th width="5px;"> <center> <i class="fa fa-circle-o"></i> </center> </th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="dato in datos">
								<td>{{dato.codlote}}</td>
								<td>{{dato.departamento}} / {{dato.provincia}} / {{dato.distrito}} / {{dato.zona}}</td>
								<td>{{dato.direccion}}</td>
								<td>{{dato.area}}</td>
								<td>{{dato.fechainicio}}</td>
								<td>
									<span v-if="dato.tipoposesion==0">PROPIA</span>
									<span v-if="dato.tipoposesion==1">ALQUILADA</span>
									<span v-if="dato.tipoposesion==2">ALQUILER COMPRA</span>
								</td>
								<td>
									<span v-if="dato.estado==0">ANULADO</span>
									<span v-if="dato.estado==1">VÁLIDO</span>
								</td>
								<td v-if="dato.estado!='2'"> 
									<input type="radio" class="form-check-input" name="phuyu_seleccionar" v-on:click="phuyu_seleccionar(dato.codlote)"> 
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
	var pantalla = jQuery(document).height(); $("#reportes_modal").css({height: pantalla - 65});

	if (typeof AcornIcons !== 'undefined') {
      new AcornIcons().replace();
    }
    if (typeof Icons !== 'undefined') {
      const icons = new Icons();
    }
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_chacra/index.js"> </script>