<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<style>
	#phuyu_datos .report-shell { border: 1px solid #e9ebec; border-radius: 10px; box-shadow: 0 1px 2px rgba(56,65,74,.06); }
	#phuyu_datos .report-title { font-size: 1.05rem; font-weight: 700; margin: 0; }
	#phuyu_datos .filter-band { background: #f8fafc; border: 1px solid #edf0f2; border-radius: 8px; padding: 14px; }
	#phuyu_datos label { color: #495057; font-size: .72rem; font-weight: 700; margin-bottom: 6px; text-transform: uppercase; }
	#phuyu_datos .form-control, #phuyu_datos .form-select { border-color: #d9dee3; border-radius: 6px; min-height: 39px; }
	#phuyu_datos .action-row { display: flex; flex-wrap: wrap; gap: 8px; }
	#phuyu_datos .action-row .btn { align-items: center; display: inline-flex; gap: 6px; min-height: 39px; white-space: nowrap; }
	#phuyu_datos .results-box { border: 1px solid #edf0f2; border-radius: 8px; height: calc(100vh - 360px); min-height: 330px; overflow: auto; }
	#phuyu_datos .results-box thead th { background: #f3f6f9; position: sticky; top: 0; z-index: 1; }
</style>

<div id="phuyu_datos" class="phuyu-reportes-velzon phuyu-velzon-list">
	<div class="phuyu_body">
		<div class="card report-shell">
			<div class="card-body">
				<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
					<h5 class="report-title">REPORTE DE VENDEDORES</h5>
					<div class="action-row">
						<button type="button" class="btn btn-danger btn-sm" v-on:click="pdf_ventas_vendedor_resumen()"><i class="bi bi-printer"></i> PDF resumen</button>
						<button type="button" class="btn btn-danger btn-sm" v-on:click="pdf_ventas_vendedor()"><i class="bi bi-printer"></i> PDF detallado</button>
						<button type="button" class="btn btn-success btn-sm" v-on:click="excel_ventas_vendedor_resumen()"><i class="bi bi-file-earmark-excel"></i> Excel resumen</button>
						<button type="button" class="btn btn-success btn-sm" v-on:click="excel_ventas_vendedor()"><i class="bi bi-file-earmark-excel"></i> Excel detallado</button>
					</div>
				</div>
				<div class="filter-band mb-3">
				<div class="row g-3 align-items-end">
					<div class="col-xl-4 col-lg-5">
						<label>VENDEDOR</label>
						<select class="form-select" v-model="campos.codvendedor">
							<?php if($_SESSION["phuyu_codperfil"]!=5){ ?>
							<option value="">TODOS LOS VENDEDORES</option>
						<?php } ?>
							<?php 
								foreach ($vendedores as $key => $value) { ?>
									<option value="<?php echo $value["codpersona"];?>"><?php echo $value["razonsocial"];?></option>
								<?php }
							?>
						</select>
					</div>
					<div class="col-xl-3 col-lg-3 col-md-6">
						<label><i class="bi bi-calendar3"></i> DESDE</label>
						<input type="date" class="form-control" id="fechadesde" value="<?php echo date('Y-m-01');?>" autocomplete="off">
					</div>
					<div class="col-xl-3 col-lg-3 col-md-6">
						<label><i class="bi bi-calendar3"></i> HASTA</label>
						<input type="date" class="form-control" id="fechahasta" value="<?php echo date('Y-m-d');?>" autocomplete="off">
					</div>
					<div class="col-xl-2 col-lg-1">
						<label>Accion</label>
						<button type="button" class="btn btn-warning w-100" v-on:click="consulta_vendedores()"><i class="bi bi-search"></i> Consultar</button>
					</div>
				</div>
				</div>
				<div class="results-box">
					<div class="h-100 d-flex align-items-center justify-content-center text-muted" v-if="detalle.length==0">
						Seleccione filtros y consulte ventas de vendedores.
					</div>
					<div class="table-responsive" v-if="detalle.length>0">
						<table class="table table-striped table-hover align-middle" style="font-size: 11px">
							<thead>
								<th>#</th>
								<th>DOCUMENTO</th>
								<th>RAZON SOCIAL</th>
								<th>FECHA</th>
								<th>TIPO</th>
								<th>COMPROBANTE</th>
								<th>SUBTOTAL</th>
								<th>IGV</th>
								<th>TOTAL</th>
								<th>CONDICION</th>
							</thead>
							<tbody>
								<tr v-for="(dato,index) in detalle">
									<td>{{index+1}}</td>
									<td>{{dato.documento}}</td>
									<td>{{dato.cliente}}</td>
									<td>{{dato.fechacomprobante}}</td>
									<td>{{dato.tipo}}</td>
									<td>{{dato.seriecomprobante}}-{{dato.nrocomprobante}}</td>
									<td>{{dato.valorventa}}</td>
									<td>{{dato.igv}}</td>
									<td>{{dato.importe}}</td>
									<td>
										<span v-if="dato.condicionpago==1">CONTADO</span>
										<span v-else="dato.condicionpago==1">CREDITO</span>
									</td>
								</tr>
								<tr v-for="(dato1,index1) in totales">
									<td colspan="6" align="right" style="font-weight: 700;font-size: 12px">TOTALES</td>
									<td style="font-weight: 700;font-size: 12px">{{dato1.valorventatotal}}</td>
									<td style="font-weight: 700;font-size: 12px">{{dato1.igvtotal}}</td>
									<td style="font-weight: 700;font-size: 12px">{{dato1.totalgeneral}}</td>
								</tr>
							</tbody>
						</table>
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
<script> 
	var campos = {"codvendedor":"<?php echo $_SESSION["phuyu_codempleado"]; ?>","fechadesde":"","fechahasta":"","estado":1};

	var pantalla = jQuery(document).height(); $("#reporte_ventas").css({height: pantalla - 250});
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_reportes/ventas.js"> </script>
