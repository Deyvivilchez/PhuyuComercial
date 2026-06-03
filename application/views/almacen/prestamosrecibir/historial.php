<div id="phuyu_historial">
	<div class="phuyu_body">
		<div class="card">
			<div class="card-body">
				<input type="hidden" id="tipo" value="11">
				<div class="row form-group">
					<div class="col-md-10 col-xs-12"> 
						<h5><b>HISTORIAL DE PRESTAMOS OTORGADOS RECIBIDOS A: </b> <?php echo $info[0]["cliente"];?></h5>
					</div>
				</div>
				<div class="row form-group">
					<div class="col-md-2"></div>
					<div class="col-md-2">
						<label>FECHA INICIAL</label>
						<input type="date" class="form-control" id="fechadesde" value="<?php echo date('Y-m-01');?>" autocomplete="off">
					</div>
					<div class="col-md-2">
						<label>FECHA FIN</label>
						<input type="date" class="form-control" id="fechahasta" value="<?php echo date('Y-m-d');?>" autocomplete="off">
					</div>
					<div class="col-md-2">
						<label>FILTRO?</label>
						<select class="form-select" v-model="campos.filtro">
							<option value="1">FECHAS FILTRO (SI)</option>
							<option value="0">FECHAS FILTRO (NO)</option>
						</select>
					</div>
					<div class="col-md-4" style="margin-top: 1.2rem" align="right">
						<button type="button" class="btn btn-success btn-icon btn-block" v-on:click="phuyu_prestamos()">
							<i data-acorn-icon="search"></i> CONSULTA
						</button>
						<button type="button" class="btn btn-danger btn-icon btn-block" v-on:click="phuyu_cerrar()">
							<i data-acorn-icon="arrow-left"></i> CERRAR
						</button>
					</div>
				</div>
				<div class="table-responsive" style="height: 280px;overflow-y:auto;">
					<table class="table table-striped table-bordered" style="font-size: 11px">
						<thead>
							<tr>
								<th width="30px">ID</th>
								<th>SALIDAS</th>
								<th width="200px">FECHA DEVOL.</th>
								<th width="200px">IMPORTE</th>
								<th width="100px">VER</th>
								<th width="100px">EDITAR</th>
								<th width="100px">ANULAR</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="dato in prestamos" v-bind:class="[dato.estado==0 ? 'phuyu_anulado':'']">
								<td>000{{dato.codkardex}}</td>
								<td>{{dato.seriecomprobante}}-{{dato.nrocomprobante}}</td>
								<td>{{dato.fechakardex}}</td>
								<td>{{dato.importe}}</td>
								<td>
									<button type="button" class="btn btn-info btn-xs" v-on:click="phuyu_ver(dato.codkardex)" style="margin-bottom:2px;">
										<i data-acorn-icon="eye"></i> VER
									</button>
								</td>
								<td>
									<button type="button" class="btn btn-warning btn-xs" v-on:click="phuyu_editar(dato.codkardex)" style="margin-bottom:2px;">
										<i data-acorn-icon="edit"></i> EDITAR
									</button>
								</td>
								<td>
									<button type="button" class="btn btn-danger btn-xs" v-on:click="phuyu_eliminar(dato.codkardex,dato.codkardex_ref)" style="margin-bottom:2px;">
										<i data-acorn-icon="bin"></i> ANULAR
									</button>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div id="modal_editar" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
				<div class="modal-dialog modal-fullscreen-xxl-down">
					<div class="modal-content" style="border-radius:0px">
						<div class="modal-header modal-phuyu-titulo">
							<h4 class="modal-title">
								<b style="letter-spacing:4px;">EDITAR DEVOLUCION DE PRESTAMO</b>
							</h4>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> 
						</div>
						<div class="modal-body"  id="cuerpo">
						</div>
					</div>
				</div>
			</div>

			<div id="modal_ver" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
				<div class="modal-dialog modal-lg">
					<div class="modal-content" style="border-radius:0px">
						<div class="modal-header modal-phuyu-titulo">
							<h4 class="modal-title">
								<b style="letter-spacing:4px;">VER DEVOLUCION</b>
							</h4>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> 
						</div>
						<div class="modal-body"  id="modalver">
						</div>
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
<script src="<?php echo base_url();?>phuyu/phuyu_prestamos/historial.js"> </script>