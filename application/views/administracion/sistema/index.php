<section class="wrapper" id="phuyu_index">
	<div class="phuyu_body">
		<div class="card">
			<div class="card-body">
				<div class="row">
					<div class="col-md-6">
						<div class="card credimax_cart">
							<div class="card-body"><br>
								<p align="center"> <b style="font-size:17px;">LIMPIAR BASE DE DATOS phuyu</b> </p>
								<div class="row">
									<div class="col-md-12" align="center">
										<i class="fa fa-database" style="font-size:94px;color:#13a89e;"></i>
									</div>
								</div>

								<div align="center"> <br>
									<button type="button" class="btn btn-success btn-icon" v-on:click="phuyu_limpiarbd()"> 
										<i class="fa fa-refresh" style="font-size:18px"></i> LIMPIAR BASE DE DATOS
									</button>
								</div>
								<br>
							</div>
						</div>
					</div>

					<div class="col-md-6">
						<div class="card credimax_cart">
							<div class="card-body"><br>
								<p align="center"> <b style="font-size:17px;">SACAR COPIA DE SEGURIDAD (BACKUP) </b> </p>
								<div class="row">
									<div class="col-md-12" align="center">
										<i class="fa fa-archive" style="font-size:94px;color:#13a89e;"></i>
									</div>
								</div>

								<div align="center"> <br>
									<button type="button" class="btn btn-warning btn-icon" v-on:click="phuyu_backup()"> 
										<i class="fa fa-refresh" style="font-size:18px"></i> SACAR BACKUP
									</button>
								</div><br>
							</div>
						</div>
					</div>
				</div> <br> <br>

				<div class="modal fade " id="credimax_reportes" tabindex="-1" role="dialog">
				    <div class="modal-dialog" style="margin:0px;">
			            <div class="modal-content" style="width:1000px;">
			                <div class="modal-header">
			                    <h4 class="modal-title"> REPORTE DE CREDITOS - <?php echo $_SESSION["credimax_empresa"];?></h4>
			                    <button type="button" class="close" data-dismiss="modal"> <i class="fa fa-times-circle" style="color:#fff"></i> </button>
			                </div>
			                <div class="modal-body" id="reportes_credimax" style="height:300px;padding:0px 0px 5px 0px">
								<iframe id="credimax_pdf" src="" style="width:100%; height:100%; border:none;"> </iframe>
							</div>
			            </div>
				    </div>
				</div>
			</div>
		</div>
	</div>

</section>

<script src="<?php echo base_url();?>phuyu/phuyu_sistema.js"></script>
<script>
	var credimax_pantalla_ancho = jQuery(document).width(); $(".modal-content").css("width",credimax_pantalla_ancho+"px");
	var credimax_pantalla_alto = jQuery(document).height() - 50; $(".modal-body").css("height",credimax_pantalla_alto+"px");

	$(".datepicker1").datetimepicker({format:'YYYY-MM-DD'});
</script>