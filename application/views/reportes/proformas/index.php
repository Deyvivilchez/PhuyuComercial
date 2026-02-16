<div id="phuyu_datos">
	<div class="phuyu_body">
		<div class="card">
			<div class="card-header">
				<div class="row">
					<div class="col-md-8"> <h5 style="letter-spacing:1px;"> <b>REPORTE GENERAL DE PROFORMAS</b> </h5> </div>
				</div>
				<input type="hidden" id="sucursal" value="<?php echo $_SESSION["phuyu_codsucursal"];?>" name="">
				<div class="row">
					<div class="col-md-4">
						<div class="input-group m-b-5">
							<span class="input-group-addon">
								<i class="fa fa-home"></i> SUCURSALES
							</span> 
							<select class="form-control" v-model="campos.codsucursal">
								<option value="0">TODAS SUCURSALES</option>
								<?php 
									foreach ($sucursales as $key => $value) { ?>
										<option value="<?php echo $value["codsucursal"];?>"><?php echo $value["descripcion"];?></option>	
									<?php }
								?>
							</select>
						</div>
						<input type="hidden" id="fecharef" value="<?php echo date("Y-m-d");?>">
					</div>
					<div class="col-md-3">
						<div class="input-group m-b-5">
							<span class="input-group-addon">
								<i class="fa fa-calendar"></i> DESDE
							</span>
							<input type="hidden" id="fechad" value="<?php echo date("Y-m-01");?>">
							<input type="text" class="form-control datepicker" id="fechadesde" v-model="campos.fechadesde" v-on:blur="phuyu_fecha()">
						</div>
					</div>
					<div class="col-md-3">
						<div class="input-group m-b-5">
							<span class="input-group-addon">
								<i class="fa fa-calendar"></i> HASTA
							</span>
							<input type="hidden" id="fechah" value="<?php echo date("Y-m-d");?>">
							<input type="text" class="form-control datepicker" id="fechahasta" v-model="campos.fechahasta" v-on:blur="phuyu_fecha()">
						</div>
					</div>
					<div class="col-md-2">
						<div class="input-group m-b-5">
							<span class="input-group-addon">
								<i class="fa fa-circle"></i>
							</span> 
							<select class="form-control" v-model="campos.estado">
								<option value="0">TODOS</option>
								<option value="1">PENDIENTES</option>
								<option value="2">CANJEADOS</option>
								<option value="3">ANULADOS</option>
							</select>
						</div>
					</div>
				</div>
			</div>
			<div class="card-body">
				<div class="row" style="margin-top: -20px">
    				<div class="col-md-6">
						<div class="card credimax_cart">
							<div class="card-body"><br>
								<p align="center" style="font-size: 20px;">REPORTE GENERAL PDF</p>
								<div align="center">
									<button type="button" class="btn btn-danger btn-sm" v-on:click="pdf_pedidos()"><i class="fa fa-print"></i> PROFORMAS PDF</button>
									<button type="button" class="btn btn-danger btn-sm" v-on:click="pdf_pedidos_detallado()"><i class="fa fa-print"></i> PROFORMAS DETALLADO PDF</button>
								</div>
								<br>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="card credimax_cart">
							<div class="card-body"><br>
								<p align="center" style="font-size: 20px;">REPORTE GENERAL EXCEL</p>
								<div align="center">
									<button type="button" class="btn btn-success btn-sm" v-on:click="excel_proformas()"><i class="fa fa-file-excel-o"></i> PROFORMAS EXCEL</button>
									<button type="button" class="btn btn-success btn-sm" v-on:click="excel_proformas_detallado()"><i class="fa fa-file-excel-o"></i> PROFORMAS DETALLADO EXCEL</button>
								</div>
								<br>
							</div>
						</div>
					</div>
    			</div>
    			<div class="row">
    				<div class="col-md-6">
						<div class="card credimax_cart">
							<div class="card-body"><br>
								<p align="center" style="font-size: 20px;">REPORTE POR PRODUCTO</p>
								<div class="center">
									<input type="text" class="form-control" v-model="campos.buscar" placeholder="BUSCAR PRODUCTOS">
								</div>
								<br>
								<div align="center">
									<button type="button" class="btn btn-danger" v-on:click="pdf_producto_proformas()">
										<i class="fa fa-print"></i> PDF
									</button>
									<button type="button" class="btn btn-success" v-on:click="excel_producto_proformas()">
										<i class="fa fa-cloud"></i> EXCEL
									</button>
								</div>
								<br>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="card credimax_cart">
							<div class="card-body"><br>
								<p align="center" style="font-size: 20px;">REPORTE POR CLIENTE</p>
								<div class="center">
									<select class="form-control selectpicker ajax" id="codpersona" required data-live-search="true">
										<option value="0">LISTA GENERAL - TODAS LAS PERSONAS</option>
									</select>
								</div><br>
								<div align="center">
									<button type="button" class="btn btn-danger" v-on:click="pdf_cliente_proformas()">
										<i class="fa fa-print"></i> PDF
									</button>
									<button type="button" class="btn btn-success" v-on:click="excel_cliente_proformas()">
										<i class="fa fa-cloud"></i> EXCEL
									</button>
								</div>
								<br>
							</div>
						</div>
					</div>
    			</div>
    			<br><br>	
			</div>
		</div>
	</div>
</div>
<script src="<?php echo base_url();?>phuyu/phuyu_reportes/proformas.js"> </script>
<script src="<?php echo base_url();?>phuyu/phuyu_personas_2.js"> </script>
<script> $(".datepicker").datetimepicker({format: 'YYYY-MM-DD'}); </script>