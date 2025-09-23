<div id="phuyu_datos">
	<div class="row">
		<div class="col-12 col-md-8">
			<input type="hidden" id="estadocaja" value="<?php echo $_SESSION['phuyu_codcontroldiario'];?>">
			<input type="hidden" id="saldarautomaticamente" value="<?php echo $automatico[0]['estado'];?>" name="">
            <h3 class="mb-0 pb-0" id="title"><span class="bg bg-danger" style="padding: .5rem;border-radius: 1rem">CAJA CERRADA</span> 
					<b><?php echo $_SESSION["phuyu_caja"];?> AL DIA <?php echo date("d / m / Y");?></h3>
        </div>
	</div>
<br>
	<div class="phuyu_body">
		<div class="card">
			<div class="card-body">
				<div class="row form-group">
					<div class="col-md-12" align="center">
		        		<button type="button" class="btn btn-success" v-on:click="phuyu_aperturar()" v-bind:disabled="estado==1">
		        			<b><i data-acorn-icon="money"></i> APERTURAR CAJA ACTUAL</b>
		        		</button>
					</div>
				</div><br>
				<div class="row form-group">
					<div class="col-md-4">
						<div class="card">
							<div class="card-body">
								<h5>REPORTE DE MOVIMIENTOS</h5>
								<div class="row form-group">
									<div class="col-md-6">	
										<label>FECHA DESDE</label>
							        	<input type="date" id="f_desde" class="form-control" autocomplete="off" value="<?php echo date('Y-m-d');?>" />
							        </div>
							        <div class="col-md-6">
										<label>FECHA HASTA</label>
							        	<input type="date" id="f_hasta" class="form-control" autocomplete="off" value="<?php echo date('Y-m-d');?>" />
							        </div>
						        </div>
						        <div class="row form-group" align="center"> <br>
			              			<button type="button" class="btn btn-warning btn-lg" v-on:click="pdf_movimientos()"><i data-acorn-icon="print"></i> IMPRIMIR MOVIMIENTOS</button>
			              		</div>
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="card">
							<div class="card-body">
								<h5>REPORTE DE ARQUEO</h5>
								<div class="row form-group">
									<div class="col-md-12">	
										<label>FECHA APERTURA</label>
							        	<input type="date" id="f_arqueo" class="form-control" autocomplete="off" value="<?php echo date('Y-m-d');?>" />
							        </div>
						        </div>
						        <div class="row form-group" align="center"> <br>
			              			<button type="button" class="btn btn-info btn-lg" v-on:click="pdf_arqueo()"><i data-acorn-icon="print"></i> IMPRIMIR ARQUEO</button>
			              		</div>
							</div>
						</div>
					</div>

					<div class="col-md-4 col-xs-12">
						<div class="animated flipInY col-md-12 col-xs-12">
							<div class="alert alert-warning" align="center" role="alert">
								<strong>SALDO CAJA <br> <i class="fa fa-dollar" style="font-size:40px;"></i> </strong>
								<h1> <b>S/. <?php echo number_format(round($saldocaja["total"],2) ,2);?> </b> </h1>
							</div>
						</div>
						<div class="animated flipInY col-md-12 col-xs-12">
							<div class="alert alert-success" align="center" role="alert">
								<strong>SALDO BANCO <br> <i class="fa fa-dollar" style="font-size:40px;"></i> </strong>
								<h1> <b>S/. <?php echo number_format(round($saldobanco["total"],2) ,2);?> </b> </h1>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="modal_reportes" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog" style="width:100%;margin:0px;">
			<div class="modal-content" align="center" style="border-radius:0px">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" style="font-size:30px;margin-bottom:0px;">
						<i class="fa fa-times-circle"></i> 
					</button>
					<h4 class="modal-title">
						<b style="letter-spacing:4px;"><?php echo $_SESSION["phuyu_empresa"]." - ".$_SESSION["phuyu_sucursal"];?> </b>
					</h4>
				</div>
				<div class="modal-body" id="reportes_modal" style="height:450px;padding:0px;">
					<iframe id="phuyu_pdf" src="" style="width:100%; height:100%; border:none;"> </iframe>
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
<script src="<?php echo base_url();?>phuyu/phuyu_caja/controlcaja.js"> </script>