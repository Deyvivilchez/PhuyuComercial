<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_datos" class="phuyu-reportes-velzon phuyu-velzon-list">
	<div class="phuyu_body">
		<div class="card">
			<div class="card-body">
				<div class="row align-items-end g-2">
					<div class="col-md-4">
						<h5 class="mb-1"><b>ANALISIS DE COSTOS DE COMPRA</b></h5>
						<small class="text-muted">Historial desde compras válidas registradas en kardex.</small>
					</div>
					<div class="col-md-2">
						<label>ALMACEN</label>
						<select class="form-select" v-model="campos.codalmacen">
							<option value="0">TODOS</option>
							<?php foreach ($almacenes as $value) { ?>
								<option value="<?php echo $value["codalmacen"];?>"><?php echo $value["descripcion"];?></option>
							<?php } ?>
						</select>
					</div>
					<div class="col-md-2">
						<label>LINEA</label>
						<select class="form-select" v-model="campos.codlinea">
							<option value="0">TODAS</option>
							<?php foreach ($lineas as $value) { ?>
								<option value="<?php echo $value["codlinea"];?>"><?php echo $value["descripcion"];?></option>
							<?php } ?>
						</select>
					</div>
					<div class="col-md-2">
						<label>DESDE</label>
						<input type="date" class="form-control" id="fechadesde" value="<?php echo date('Y-m-01');?>">
					</div>
					<div class="col-md-2">
						<label>HASTA</label>
						<input type="date" class="form-control" id="fechahasta" value="<?php echo date('Y-m-d');?>">
					</div>
				</div>

				<div class="row align-items-end g-2 mt-2">
					<div class="col-md-5">
						<label>PRODUCTO</label>
						<div class="input-group">
							<input type="text" class="form-control" v-model="producto_texto" placeholder="Opcional: escriba codigo o descripcion">
							<button type="button" class="btn btn-outline-secondary" v-on:click="limpiar_producto()">Limpiar</button>
						</div>
					</div>
					<div class="col-md-2">
						<label>MAX. RESUMEN</label>
						<input type="number" class="form-control" v-model="campos.limit">
					</div>
					<div class="col-md-5 text-end">
						<button type="button" class="btn btn-primary" v-on:click="generar_resumen()">
							<i class="bi bi-search"></i> Consultar
						</button>
						<button type="button" class="btn btn-success" v-on:click="generar_historial()">
							<i class="bi bi-clock-history"></i> Ver historial
						</button>
					</div>
				</div>

				<hr>

				<div class="row g-3 mb-3">
					<div class="col-md-3">
						<div class="border rounded p-3">
							<small class="text-muted">Productos</small>
							<h4 class="mb-0">{{kpi.productos || 0}}</h4>
						</div>
					</div>
					<div class="col-md-3">
						<div class="border rounded p-3">
							<small class="text-muted">Compras</small>
							<h4 class="mb-0">{{kpi.compras || 0}}</h4>
						</div>
					</div>
					<div class="col-md-3">
						<div class="border rounded p-3">
							<small class="text-muted">Precio minimo</small>
							<h4 class="mb-0">S/ {{moneda(kpi.precio_minimo)}}</h4>
						</div>
					</div>
					<div class="col-md-3">
						<div class="border rounded p-3">
							<small class="text-muted">Precio maximo</small>
							<h4 class="mb-0">S/ {{moneda(kpi.precio_maximo)}}</h4>
						</div>
					</div>
				</div>

				<h6><b>VARIACION ULTIMA VS ANTERIOR</b></h6>
				<div class="table-responsive">
					<table class="table table-striped align-middle" style="font-size: 12px">
						<thead>
							<tr>
								<th>PRODUCTO</th>
								<th>UNIDAD</th>
								<th>FECHA ULTIMA</th>
								<th>PROVEEDOR</th>
								<th>ANTERIOR</th>
								<th>ULTIMO</th>
								<th>VAR. S/</th>
								<th>VAR. %</th>
								<th>COMPRAS</th>
								<th>DOC.</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="dato in resumen">
								<td>{{dato.producto}}</td>
								<td>{{dato.unidad}}</td>
								<td>{{dato.fechacomprobante}}</td>
								<td>{{dato.proveedor}}</td>
								<td>S/ {{moneda(dato.precio_anterior)}}</td>
								<td>S/ {{moneda(dato.ultimo_precio)}}</td>
								<td :class="clase_variacion(dato.variacion)">S/ {{moneda(dato.variacion)}}</td>
								<td :class="clase_variacion(dato.variacion_porc)">{{porcentaje(dato.variacion_porc)}}</td>
								<td>{{dato.compras_producto}}</td>
								<td>{{dato.seriecomprobante}}-{{dato.nrocomprobante}}</td>
							</tr>
							<tr v-if="resumen.length == 0">
								<td colspan="10" class="text-center text-muted py-4">
									No hay compras validas en el rango seleccionado.
								</td>
							</tr>
						</tbody>
					</table>
				</div>

				<h6 class="mt-4"><b>HISTORIAL</b></h6>
				<div class="table-responsive">
					<table class="table table-bordered align-middle" style="font-size: 12px">
						<thead>
							<tr>
								<th>FECHA</th>
								<th>PRODUCTO</th>
								<th>PROVEEDOR</th>
								<th>CANT.</th>
								<th>UNIDAD</th>
								<th>PRECIO</th>
								<th>ANTERIOR</th>
								<th>VAR. S/</th>
								<th>VAR. %</th>
								<th>DOC.</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="dato in historial">
								<td>{{dato.fechacomprobante}}</td>
								<td>{{dato.producto}}</td>
								<td>{{dato.proveedor}}</td>
								<td>{{dato.cantidad}}</td>
								<td>{{dato.unidad}}</td>
								<td>S/ {{moneda(dato.precio)}}</td>
								<td>S/ {{moneda(dato.precio_anterior)}}</td>
								<td :class="clase_variacion(dato.variacion)">S/ {{moneda(dato.variacion)}}</td>
								<td :class="clase_variacion(dato.variacion_porc)">{{porcentaje(dato.variacion_porc)}}</td>
								<td>{{dato.seriecomprobante}}-{{dato.nrocomprobante}}</td>
							</tr>
							<tr v-if="historial.length == 0">
								<td colspan="10" class="text-center text-muted py-4">
									No hay historial de costos para mostrar.
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
var campos = {
	codalmacen: 0,
	codlinea: 0,
	codproducto: 0,
	codpersona: 0,
	fechadesde: "",
	fechahasta: "",
	limit: 50
};
</script>
<script src="<?php echo base_url(); ?>phuyu/phuyu_reportes/costoscompra.js"></script>
