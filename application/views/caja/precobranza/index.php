<div id="phuyu_datos">
	<div class="row">
		<div class="col-md-8 col-xs-12"><h2 class="mb-1 display-4" ><b>LISTA DE PRE COBRANZAS</b></h2> </div>
	</div>
	<div class="phuyu_body">
		<div class="card">
			<div class="card-body">
				<input type="hidden" id="phuyu_opcion" value="1">
				<div class="row">
			    	<div class="col-md-4">
			    		<label>COBRADORES</label>
			    		<select class="form-select" v-model="filtro.codempleado" v-on:change="phuyu_buscar()">
			    			<option value="">TODOS</option>
			    			<?php
			    			foreach ($vendedores as $key => $value) { ?>
			    				<option value="<?php echo $value["codpersona"]?>"><?php echo $value["razonsocial"]?></option>
			    			<?php }
			    			?>
			    		</select>	
			    	</div>
					<div class="col-md-2 col-xs-12">
						<label><i class="fa fa-calendar"></i> DESDE</label>
						<input type="date" class="form-control" id="desde" value="<?php echo date('Y-m-01');?>" v-on:blur="phuyu_buscar()" autocomplete="off">
					</div>
					<div class="col-md-2 col-xs-12">
						<label><i class="fa fa-calendar"></i> HASTA</label>
						<input type="date" class="form-control" id="hasta" value="<?php echo date('Y-m-d');?>" v-on:blur="phuyu_buscar()" autocomplete="off">
					</div>
			    	<div class="col-md-3">
					    <button type="button" class="btn btn-success btn-block btn-icon" style="margin-top: 1.2rem" v-on:click="phuyu_buscar()"> <i data-acorn-icon="search"></i> BUSCAR PRECOBRANZA  </button>
				    </div>
				    <div class="col-md-1">
				    	<label class="text-danger">MARCAR TODOS</label>
				    	<input type="checkbox" class="form-check-input" id="marcar" v-on:change="phuyu_marcar()">
				    </div>
			    </div>
				<div class="phuyu_cargando" v-if="cargando">
					<i class="fa fa-spinner fa-spin"></i> <h5>CARGANDO DATOS</h5>
				</div>

				<div v-if="!cargando">
					<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">
						<div class="col-md-12 mb-2" align="right">
						    <button type="submit" class="btn btn-primary btn-icon"  v-bind:disabled="estado==1" style="margin-top: 2rem" > <i data-acorn-icon="save"></i> GUARDAR COBRANZA </button>
					    </div>
					    <div class="col-md-12">
							<div class="table-responsive">
								<table class="table table-striped" style="font-size: 11px">
									<thead>
										<tr>
											<th width="5px;"> <center> <i class="fa fa-circle-o"></i> </center> </th>
											<th>COBRADORES</th>
											<th>FECHA COBRANZA</th>
											<th>CLIENTE</th>
											<th>REFERENCIA</th>
											<th>MONTO COBRADO</th>
											<th width="10px"></th>
										</tr>
									</thead>
									<tbody>
										<tr v-for="(dato,index) in datos">
											<td>{{index+1}}</td>
											<td>{{dato.vendedor}}</td>
											<td>{{dato.fechamovimiento}}</td>
											<td>
												{{dato.razonsocial}}
											</td>
											<td>{{dato.comprobantereferencia}}</td>
											<td>S/. {{dato.importe}}</td>
											<td>
												<input type="hidden" name="movimientos[]" v-bind:value="dato.codmovimiento">
												<input type="checkbox" name="checks[]" v-bind:value="dato.codmovimiento" class="form-check-input" v-model="campos.cobrado">
											</td>
										</tr>
									</tbody>
									<tfoot>
										<tr>
											<td colspan="5" style="text-align: right;"><b>TOTAL DE COBRANZA</b></td>
											<td><b class="text-danger" style="font-size: 14px">S/. {{total}}</b></td>
										</tr>
									</tfoot>
								</table>
							</div>
						</div>
					</form>
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
<script src="<?php echo base_url();?>phuyu/phuyu_caja/precobranza.js"> </script>