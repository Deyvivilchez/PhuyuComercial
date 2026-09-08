<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_datos" class="phuyu-velzon-list">
	<div class="phuyu-page-title">
		<div class="phuyu-page-icon"><i class="bi bi-wallet2"></i></div>
		<div>
			<div class="text-muted small text-uppercase fw-semibold">Tesoreria</div>
			<h4 class="mb-0 fw-bold">Pre cobranzas</h4>
		</div>
	</div>

	<div class="phuyu_body">
		<input type="hidden" id="phuyu_opcion" value="1">
		<div class="card phuyu-card">
			<div class="card-body">
				<div class="row g-3 align-items-end mb-3">
					<div class="col-12 col-md-4">
						<label class="form-label">Cobradores</label>
						<select class="form-select" v-model="filtro.codempleado" v-on:change="phuyu_buscar()">
							<option value="">TODOS</option>
							<?php foreach ($vendedores as $key => $value) { ?>
								<option value="<?php echo $value["codpersona"]?>"><?php echo $value["razonsocial"]?></option>
							<?php } ?>
						</select>
					</div>
					<div class="col-12 col-md-2">
						<label class="form-label">Desde</label>
						<input type="date" class="form-control" id="desde" value="<?php echo date('Y-m-01');?>" v-on:blur="phuyu_buscar()" autocomplete="off">
					</div>
					<div class="col-12 col-md-2">
						<label class="form-label">Hasta</label>
						<input type="date" class="form-control" id="hasta" value="<?php echo date('Y-m-d');?>" v-on:blur="phuyu_buscar()" autocomplete="off">
					</div>
					<div class="col-12 col-md-3">
						<button type="button" class="btn btn-success w-100" v-on:click="phuyu_buscar()">
							<i class="bi bi-search me-1"></i> Buscar precobranza
						</button>
					</div>
					<div class="col-12 col-md-1 text-center">
						<label class="form-label text-danger">Todos</label>
						<input type="checkbox" class="form-check-input d-block mx-auto" id="marcar" v-on:change="phuyu_marcar()">
					</div>
				</div>

				<div class="phuyu_cargando" v-if="cargando">
					<div class="overlay-spinner"></div>
				</div>

				<div v-if="!cargando">
					<form id="formulario" v-on:submit.prevent="phuyu_guardar()">
						<div class="text-end mb-2">
							<button type="submit" class="btn btn-primary" v-bind:disabled="estado==1">
								<i class="bi bi-save me-1"></i> Guardar cobranza
							</button>
						</div>

						<div class="phuyu-table-wrap">
							<table class="table table-hover table-striped align-middle">
								<thead>
									<tr>
										<th width="70">#</th>
										<th>Cobradores</th>
										<th>Fecha cobranza</th>
										<th>Cliente</th>
										<th>Referencia</th>
										<th>Monto cobrado</th>
										<th width="70" class="text-center">Sel.</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="(dato,index) in datos">
										<td>{{index+1}}</td>
										<td>{{dato.vendedor}}</td>
										<td>{{dato.fechamovimiento}}</td>
										<td>{{dato.razonsocial}}</td>
										<td>{{dato.comprobantereferencia}}</td>
										<td>S/. {{dato.importe}}</td>
										<td class="text-center">
											<input type="hidden" name="movimientos[]" v-bind:value="dato.codmovimiento">
											<input type="checkbox" name="checks[]" v-bind:value="dato.codmovimiento" class="form-check-input" v-model="campos.cobrado">
										</td>
									</tr>
									<tr v-if="datos.length==0">
										<td colspan="7" class="text-center text-muted py-4">
											<i class="bi bi-inbox me-1"></i> Sin precobranzas
										</td>
									</tr>
								</tbody>
								<tfoot>
									<tr>
										<th colspan="5" class="text-end">Total de cobranza</th>
										<th colspan="2"><b class="text-danger">S/. {{total}}</b></th>
									</tr>
								</tfoot>
							</table>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_caja/precobranza.js"> </script>
