<div id="phuyu_sunat">
	<div class="phuyu_body">
		<div class="card">
			<div class="card-body">
				<input type="hidden" id="sucursal" value="<?php echo $_SESSION['phuyu_codsucursal'];?>" name="">
				<div class="row form-group">
					<div class="col-md-12 col-xs-12"><h5>COMPROBANTES ELECTRÓNICOS</h5></div>
				</div>
				<div class="row form-group" >
					<div class="col-md-2">
						<label >SUCURSALES</label>
						<select class="form-select" v-model="sucursal" v-on:change="phuyu_buscar()">
							<?php 
								foreach ($sucursal as $key => $value) { ?>
								<option value="<?php echo $value["codsucursal"];?>"><?php echo $value["descripcion"];?></option>
							<?php	}
							?>
						</select>
					</div>
					<div class="col-md-2 col-xs-12">
						<label><i class="fa fa-calendar"></i> DESDE</label>
						<input type="date" class="form-control" id="fecha_desde" value="<?php echo date('Y-m-01');?>" v-on:blur="phuyu_buscar()" autocomplete="off">
					</div>
					<div class="col-md-2 col-xs-12">
						<label><i class="fa fa-calendar"></i> HASTA</label>
						<input type="date" class="form-control" id="fecha_hasta" value="<?php echo date('Y-m-d');?>" v-on:blur="phuyu_buscar()" autocomplete="off">
					</div>
					<div class="col-md-2">
						<label ><i class="fa fa-home"></i> COMPROBANTES</label>
						<select class="form-select">
							<option value="0">TODOS</option>	
							<?php 
								foreach ($comprobantes as $key => $value) { ?>
								<option value="<?php echo $value["codcomprobantetipo"];?>"><?php echo $value["descripcion"];?></option>
							<?php	}
							?>
						</select>
					</div>
					<div class="col-md-1">
						<label>ESTADO</label>
						<select class="form-select">
							<option value="">TODOS</option>
							<option value="0">PENDIENTES</option>
							<option value="1">ENVIADOS</option>
						</select>
					</div>
					<div class="col-md-3 col-xs-12">
						<label>&nbsp;</label>
				    	<input type="text" class="form-control input-sm" v-model="buscar" v-on:keyup="phuyu_buscar()" placeholder="BUSCAR REGISTRO . . .">
				    </div>
				</div>
				<div v-if="!cargando">
					<div class="table-responsive">
						<table class="table table-bordered" style="font-size: 11px">
							<thead>
								<tr>
									<th width="10px">TIPO</th>
									<th>RAZON SOCIAL CLIENTE</th>
									<th>FECHA</th>
									<th width="10px">COMPROBANTE</th>
									<th width="10px">IMPORTE</th>
									<th>DESCRIPCION</th>
									<th>SUNAT</th>
									<th width="10px">XML</th>
									<th width="10px">CDR</th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="dato in datos">
									<td> <span class="label label-success">{{dato.tipo}}</span> </td>
									<td>{{dato.documento}}-{{dato.cliente}}</td>
									<td>{{dato.fechacomprobante}}</td>
									<td>{{dato.seriecomprobante}}-{{dato.nrocomprobante}}</td>
									<td>{{dato.importe}}</td>
									<td>{{dato.descripcion_cdr}}</td>
									<td>
										<span class="label label-danger" v-if="dato.estado==0">PENDIENTE</span>
										<span class="label label-success" v-else="dato.estado!=0">ENVIADO</span>
									</td>
									<td>
										<button type="button" class="btn btn-info btn-xs btn-table" style="margin:1px;" v-on:click="phuyu_xml(dato.codkardex)"><i class="fa fa-download"></i> XML</button>
									</td>
									<td>
										<button type="button" class="btn btn-warning btn-xs btn-table" style="margin:1px;" v-on:click="phuyu_cdr(dato.codkardex)"><i class="fa fa-download"></i> CDR</button>
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
</div>
<script>
	if (typeof AcornIcons !== 'undefined') {
      new AcornIcons().replace();
    }
    if (typeof Icons !== 'undefined') {
      const icons = new Icons();
    }
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_facturacion/comprobantes.js"> </script>