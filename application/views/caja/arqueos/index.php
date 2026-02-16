<div id="phuyu_datos">
	<div class="row">
		<div class="col-12 col-md-6">
            <h1 class="mb-0 pb-0 display-4" id="title">LISTA DE ARQUEOS DE CAJA</h1>
            <nav class="breadcrumb-container d-inline-block" aria-label="breadcrumb">
              <ul class="breadcrumb pt-0">
                <li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
                <li class="breadcrumb-item"><a href="javascript:;">Arqueos de Caja</a></li>
              </ul>
            </nav>
        </div>
        <div class="col-12 col-md-6 d-flex align-items-start justify-content-end" style="font-size: 11px">
			<div class="col-md-4 col-xs-12">
				<label><i class="fa fa-calendar"></i> DESDE:</label>
				<input type="date" class="form-control input-sm" id="desde" value="<?php echo date('Y-m-01');?>" v-on:change="phuyu_buscar()" autocomplete="off">
			</div>
			<div class="col-md-4 col-xs-12">
				<label><i class="fa fa-calendar"></i> HASTA:</label>
				<input type="date" class="form-control input-sm" id="hasta" value="<?php echo date('Y-m-d');?>" v-on:change="phuyu_buscar()" autocomplete="off">
			</div>
        </div>
    </div>

	<div class="phuyu_body">
		<div class="card">
			<div class="card-body">				
				<input type="hidden" id="phuyu_opcion" value="1">

				<div class="phuyu_cargando" v-if="cargando">
					<i class="fa fa-spinner fa-spin"></i> <h5>CARGANDO DATOS</h5>
				</div>

				<div v-if="!cargando">
					<div class="table-responsive">
						<table class="table table-bordered" style="font-size: 11px">
							<thead>
								<tr>
									<th width="5px;"> <center> <i class="fa fa-circle-o"></i> </center> </th>
									<th>F. APERTURA</th>
									<th>F. CIERRE</th>
									<th>CODIGO</th>
									<th>S/. TOTAL APERTURA</th>
									<th>S/. TOTAL CIERRE</th>
									<th width="10px" colspan="2">S/.ANFITRIONAS</th>
									<th width="10px">V.DIARIA</th>
									<th width="10px">BALANCE</th>
									<th width="10px">ARQUEO</th>
									<th width="10px">EXCEL</th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="dato in datos">
									<td>{{dato.codcontroldiario}}</td>
									<td>{{dato.fechaapertura}}</td>
									<td>
										<span v-if="dato.fechacierre!=null">{{dato.fechacierre}}</span>
										<span class="badge badge-danger" v-else="dato.fechacierre==''">SIN CERRAR</span>
									</td>
									<td>{{dato.codigodiario}}</td>
									<td>S/. {{dato.saldoinicialcaja}}</td>
									<td>
										<b v-if="dato.cerrado==0">S/. {{dato.cierre}}</b>
										<span class="badge badge-danger" v-else="dato.cerrado!=0">CAJA APERTURADA</span>
									</td>
									<td style="padding-top:5px;">
										<button type="button" class="btn btn-success btn-sm btn-table" v-on:click="pdf_anfitrionas(dato)">GRAL</button>
									</td>
									<td style="padding-top:5px;">
										<button type="button" class="btn btn-success btn-sm btn-table" v-on:click="pdf_anfitrionas_general(dato)">RES</button>
									</td>
									<td style="padding-top:5px;">
										<button type="button" class="btn btn-primary btn-sm btn-table" v-on:click="pdf_venta(dato)">V.DIARIA</button>
									</td>
									<td style="padding-top:5px;">
										<button type="button" class="btn btn-info btn-sm btn-table" v-on:click="pdf_balance(dato)">B.CAJA</button>
									</td>
									<td style="padding-top:5px;">
										<button type="button" class="btn btn-success btn-sm btn-table" v-on:click="pdf_arqueo_caja(dato)"><i class="fa fa-print"></i> PDF</button>
									</td>
									<td style="padding-top:5px;">
										<button type="button" class="btn btn-warning btn-sm btn-table" v-on:click="pdf_arqueo_excel(dato)"><i class="fa fa-download"></i> EXCEL</button>
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

	<div id="modal_empleados" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" style="font-size:30px;margin-bottom:0px;">
						<i class="fa fa-times-circle"></i> 
					</button>
					<h4 class="modal-title">REPORTE DE ANFITRIONAS</h4>
				</div>
				<div class="modal-body" id="modal_empleados_contenido">

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
<script src="<?php echo base_url();?>phuyu/phuyu_caja/arqueos.js"> </script>