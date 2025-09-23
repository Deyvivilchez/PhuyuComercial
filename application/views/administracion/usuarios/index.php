<div id="phuyu_datos">
	<div class="row">
		<div class="col-12 col-md-6">
            <h1 class="mb-0 pb-0 display-4" id="title">Administracion Usuarios</h1>
            <nav class="breadcrumb-container d-inline-block" aria-label="breadcrumb">
              <ul class="breadcrumb pt-0">
                <li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
                <li class="breadcrumb-item"><a href="javascript:;">Usuarios</a></li>
              </ul>
            </nav>
        </div>
	</div>
	<div class="phuyu_body">
        <div class="card">
			<div class="card-body">
				<input type="hidden" id="phuyu_opcion" value="1">
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
	            	<div class="col-sm-12 col-md-2 col-lg-2 col-xxl-2 mb-1">
				    	<select class="form-select" id="sucursal_search" name="sucursal_search" v-on:change="phuyu_buscar()">
				    		<?php 
			    			foreach ($sucursales as $key => $value) { 
			    				$selected = '';
		                        if($_SESSION["credimax_codsucursal"] == $value["codsucursal"])
		                        	$selected = 'selected';
			    			?>
			    				<option value="<?php echo $value["codsucursal"];?>" <?php echo $selected; ?> >
			    					<?php echo $value["descripcion"];?>
			    				</option>
			    			<?php } ?>
				    	</select>
	            	</div>
		            <div class="col-sm-12 col-md-7 col-lg-6 col-xxl-10 text-end mb-1" align="right">
		                <div class="d-inline-block me-0 me-sm-3 float-start float-md-none">
		                  <!-- Add Button Start -->
		                  <button type="button" class="btn btn-success btn-icon" type="button" data-bs-toggle="tooltip"
		                    data-bs-placement="top"
		                    title="Nuevo registro"
		                    type="button"
		                    data-bs-delay="0" v-on:click="phuyu_nuevo()">
		                  	<i data-acorn-icon="plus" class="icon"></i> Nuevo
		                  </button>
		                  <!-- Add Button End -->

		                  <button
		                    class="btn btn-warning btn-icon"
		                    data-bs-toggle="tooltip"
		                    data-bs-placement="top"
		                    title="Editar registro"
		                    type="button"
		                    data-bs-delay="0" v-on:click="phuyu_editar()"
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
		                  <button type="button" class="btn btn-warning btn-icon" v-on:click="phuyu_asignarzona()">
			            	<i data-acorn-icon="plus"></i> ASIGNAR ZONAS
			              </button>
		                  <!-- Delete Button End -->
		                </div>
		            </div>
			    </div>
				<div class="phuyu_cargando" v-if="cargando">
					<div class="overlay-spinner"></div>
				</div>
				<div v-if="!cargando">
					<div class="row form-group">
						<div class="table-responsive">
                            <table class="table table-striped" style="font-size: 11px">
                                <thead>
                                	<th>ID</th>
                                	<th>RAZON SOCIAL</th>
                                	<th>USUARIO</th>
                                	<th>SUCURSALES</th>
                                	<th>PERFIL</th>
                                	<th></th>
                                </thead>
                                <tbody>
                                	<tr v-for="dato in datos">
                                		<td>{{dato.codusuario}}</td>
                                		<td>{{dato.razonsocial}}</td>
                                		<td>{{dato.usuario}}</td>
                                		<td>
                                		 <template v-for="s in dato.sucursales">
                                		 	<span class="label label-danger">{{s.sucursal}} </span>&nbsp;
                                		 </template>	
                                		</td>
                                		<td>{{dato.perfil}}</td>
                                		<td><input type="radio" class="form-check-input" name="phuyu_seleccionar" v-on:click="phuyu_seleccionar(dato.codusuario)"></td>
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