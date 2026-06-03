<div id="phuyu_datos">
	<div class="row">
		<div class="col-12 col-md-6">
            <h1 class="mb-0 pb-0 display-4" id="title">Administracion de Vehículos</h1>
            <nav class="breadcrumb-container d-inline-block" aria-label="breadcrumb">
              <ul class="breadcrumb pt-0">
                <li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
                <li class="breadcrumb-item"><a href="javascript:;">Vehículos</a></li>
              </ul>
            </nav>
        </div>
	</div>
	<div class="phuyu_body">
		<input type="hidden" id="phuyu_opcion" value="1">
		<div class="card">
			<div class="card-body">
				<div class="row">
					<div class="col-sm-12 col-md-5 col-lg-5 col-xxl-2 mb-1">
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
		            <div class="col-sm-12 col-md-7 col-lg-7 col-xxl-10 text-end mb-1" align="right">
		                <div class="d-inline-block me-0 me-sm-3 float-start float-md-none">
		                  <!-- Add Button Start -->
		                  <button type="button" class="btn btn-success btn-icon" type="button" type="button" v-on:click="phuyu_nuevo()">
		                  	<i data-acorn-icon="plus" class="icon"></i> Nuevo
		                  </button>
		                  <!-- Add Button End -->

		                  <button
		                    class="btn btn-warning btn-icon"
		                    type="button" v-on:click="phuyu_editar()"
		                  >
		                    <i data-acorn-icon="edit"></i> Editar
		                  </button>
		                  <!-- Delete Button Start -->
		                  <button
		                    class="btn eliminar btn-danger btn-icon"
		                    type="button" v-on:click="phuyu_eliminar()"
		                  >
		                    <i data-acorn-icon="bin"></i> Eliminar
		                  </button>
		                  <!-- Delete Button End -->
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
								<th>DESCRIPCION MARCA</th>
								<th>NRO PLACA</th>
								<th>CONSTANCIA INSCRIPCION</th>
								<th width="5px;"> <center> <i class="fa fa-circle-o"></i> </center> </th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="dato in datos">
								<td>{{dato.descripcion}}</td>
								<td>{{dato.nroplaca}}</td>
								<td>{{dato.constancia}}</td>
								<td> <input type="radio" class="form-check-input" name="phuyu_seleccionar" v-on:click="phuyu_seleccionar(dato.codvehiculo)"> </td>
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
<script src="<?php echo base_url();?>phuyu/phuyu_datos.js"> </script>