
<div id="phuyu_datos">
	<div class="phuyu_body">
		<div class="card">
			<div class="card-body">
				<div class="row form-group">
					<div class="col-md-3">
						<p style="font-size: 16px;font-weight: bold;">REPORTE VENDEDORES</p>
					</div>
					<div class="col-md-9" align="right">
						<button type="button" class="btn btn-danger btn-sm" v-on:click="pdf_ventas_vendedor_resumen()"><i data-acorn-icon="print"></i> PDF RESUMEN</button>
						<button type="button" class="btn btn-danger btn-sm" v-on:click="pdf_ventas_vendedor()">
							<i data-acorn-icon="print"></i> PDF DETALLADO
						</button>
						<button type="button" class="btn btn-danger btn-sm" v-on:click="pdf_ventas_vendedor()" >
							<i data-acorn-icon="print"></i> F2
						</button>
						<button type="button" class="btn btn-success btn-sm" v-on:click="excel_ventas_vendedor_resumen()"><i data-acorn-icon="file-text"></i> EXCEL RESUMEN</button>
						<button type="button" class="btn btn-success btn-sm" v-on:click="excel_ventas_vendedor()"><i data-acorn-icon="file-text"></i> EXCEL DETALLADO</button>
					</div>
				</div>
				<div class="row form-group">
					<div class="col-md-4">
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
					<div class="col-md-3">
						<label><i data-acorn-data="calendar"></i> DESDE</label>
						<input type="date" class="form-control" id="fechadesde" value="<?php echo date('Y-m-01');?>" autocomplete="off">
					</div>
					<div class="col-md-3">
						<label><i class="fa fa-calendar"></i> HASTA</label>
						<input type="date" class="form-control" id="fechahasta" value="<?php echo date('Y-m-d');?>" autocomplete="off">
					</div>
					<div class="col-md-2" style="margin-top: 1.2rem">
						<button type="button" class="btn btn-warning btn-icon" v-on:click="consulta_vendedores()"><i data-acorn-icon="search"></i> CONSULTAR</button>
					</div>
				</div>
				<div class="row form-group mt-4" id="consulta">
					<div class="col-md-12 text-center"><h5><b>INFORMACION GENERADA</b></h5></div>
				</div>
				<div class="row form-group">
					<div class="table-responsive">
						<table class="table table-striped" style="font-size: 11px">
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