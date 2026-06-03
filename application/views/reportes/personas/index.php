<div id="phuyu_datos">
	<div class="phuyu_body">
		<div class="card">
			<div class="card-body">
				<div class="row form-group">
					<div class="col-md-8"> <h5 style="letter-spacing:1px;"> <b>REPORTE GENERAL DE PERSONAS(CLIENTES Y PROVEEDORES)</b> </h5> </div>
				</div>
				<input type="hidden" id="sucursal" value="<?php echo $_SESSION["phuyu_codsucursal"];?>" name="">
				<div class="row form-group">
					<div class="col-md-2">
						<label>DEPARTAMENTO</label>
						<select class="form-select" id="departamento" v-model="campos.departamento" v-on:change="phuyu_provincias()">
							<option value="">TODOS</option>
							<?php 
								foreach ($departamentos as $key => $value) { ?>
									<option value="<?php echo $value["ubidepartamento"];?>"><?php echo $value["departamento"];?></option>	
								<?php }
							?>
						</select>
					</div>
					<div class="col-md-2">
						<label>PROVINCIA</label>
						<select class="form-select" id="provincia" v-model="campos.provincia" v-on:change="phuyu_distritos()">
							<option value="">TODOS</option>
						</select>
					</div>
					<div class="col-md-2">
						<label>DISTRITO</label> 
						<select class="form-select" id="codubigeo" v-model="campos.distrito" v-on:change="phuyu_opcion()">
							<option value="">TODOS</option>
						</select>
					</div>
					<div class="col-md-2">	
						<label>TIPO PERSONA</label> 
						<select class="form-select" id="ubigeo" v-model="campos.codsociotipo" v-on:change="phuyu_opcion()">
							<option value="1">CLIENTES</option>
							<option value="2">PROVEEDORES</option>
							<option value="3">CLIENTES/PROVEEDORES</option>
						</select>
					</div>
					<div class="col-md-4" style="margin-top: 1.2rem">
						<button type="button" class="btn btn-primary btn-icon" v-on:click="phuyu_opcion()"><i data-acorn-icon="search"></i> CONSULTAR</button>
						<button type="button" class="btn btn-danger btn-icon" v-on:click="pdf_personas"><i data-acorn-icon="print"></i> PDF</button>
						<button type="button" class="btn btn-success btn-icon" v-on:click="excel_personas"><i data-acorn-icon="file-text"></i> EXCEL</button>
					</div>
				</div>
				<div class="row form-group mt-4" >
    				<div class="col-md-12">
						<div class="table-responsive">
							<table class="table table-striped" style="font-size: 11px">
								<thead>
									<th>ID</th>
									<th>DOCUMENTO</th>
									<th style="width: 30%">RAZON SOCIAL</th>
									<th style="width: 35%">DIRECCION</th>
									<th>CONTACTO</th>
									<th>EMAIL</th>
									<th>UBIGEO</th>
								</thead>
								<tbody>
									<tr v-for="dato in datos">
										<td>{{dato.codpersona}}</td>
										<td>{{dato.documento}}</td>
										<td>{{dato.razonsocial}}</td>
										<td>{{dato.direccion}}</td>
										<td>{{dato.telefono}}</td>
										<td>{{dato.email}}</td>
										<td>{{dato.provincia}} - {{dato.distrito}}</td>	
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
<script src="<?php echo base_url();?>phuyu/phuyu_reportes/personas.js"> </script>
<script src="<?php echo base_url();?>phuyu/phuyu_personas_2.js"> </script>