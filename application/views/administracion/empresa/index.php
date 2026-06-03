<div id="phuyu_datos">
	<div class="phuyu_body">
		<div class="card">
			<div class="card-body">
				<div class="row form-group">
					<div class="col-md-7">
						<div class="row form-group">
							<div class="col-md-12">
								<img class="img-responsive" src="<?php echo base_url();?>public/img/empresa/<?php echo $info[0]['foto']?>" style="height:250px;">
							</div>
						</div>
						<div class="row form-group">
							<div class="col-md-12">
								<input type="hidden" id="codempresa" value="<?php echo $_SESSION["phuyu_codempresa"];?>">
								<h4> <b><?php echo $info[0]["nombrecomercial"];?></b> </h4>
							</div>
						</div>
						<div class="row form-group">
							<div class="col-md-12">
								<b>RUC: <?php echo $info[0]["documento"];?></b>
							</div>
						</div>
						<div class="row form-group">
							<div class="col-md-12">
								<?php echo $info[0]["direccion"];?>
							</div>
						</div>
						<div class="row form-group">
							<div class="col-md-12">
								<ul class="list-unstyled user_data">
									<li> <i class="fa fa-google-plus"></i> EMAIL: <?php echo $info[0]["email"];?> </li>
									<li> <i class="fa fa-phone"></i> TELF./CEL.: <?php echo $info[0]["telefono"];?> </li>
									<li class="m-top-xs">
										<i class="fa fa-external-link"></i> UBIGEO: 
										<?php echo $empresa[0]["departamento"]."-".$empresa[0]["provincia"]."-".$empresa[0]["distrito"]." (".$empresa[0]["ubigeo"].")";?>
									</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="col-md-1"></div>
					<div class="col-md-4">
						<div class="row form-group">
							 <div class="col-md-12">
							 	<h5> <b class="text-success">DATOS DE LA FACTURACION</b> </h5>
									<ul class="list-unstyled user_data">
										<li> <i class="fa fa-user-o"></i> USUARIO SOL: <?php echo $service[0]["usuariosol"];?> </li>
										<li> <i class="fa fa-user-o"></i> CLAVE SOL: <?php echo $service[0]["clavesol"];?> </li>
										<li> <i class="fa fa-google-plus"></i> EMAIL ENVIO: <?php echo $service[0]["envioemail"];?> </li>
										<li> <i class="fa fa-key"></i> EMAIL CLAVE: <?php echo $service[0]["claveemail"];?> </li>
										<li> <i class="fa fa-lock"></i> CLAVE CERTIFICADO: <?php echo $service[0]["certificado_clave"];?> </li>
										<li>
											<a download="<?php echo $service[0]['certificado_pfx'];?>" href="<?php echo base_url();?>sunat/certificado/<?php echo $service[0]['certificado_pfx'];?>" style="color:#337ab7"> 
												<b><i class="fa fa-cloud-download"></i> DESCARGAR CERTIFICADO</b>
											</a>
										</li>
									</ul>
							 </div>
						</div><br>
						<div class="row form-group">
							<div class="col-md-12">
								<h5 class="text-danger"> <b>ARCHIVOS PEM: <?php echo $pen;?></b> </h5>

								<?php 
									if ($service[0]["sunatose"]==0) { ?>
										<span class="label label-success">SERVICIO: SUNAT</span> <br>
									<?php }else{ ?>
										<span class="label label-success">SERVICIO: OSE</span> <br>
									<?php }
								?>

								<?php 
									if ($service[0]["serviceweb"]==0) { ?>
										<span class="label label-primary">ESTADO: PRODUCCION</span> 
									<?php }else{ ?>
										<span class="label label-primary">ESTADO: BETA HOMOLOGACION</span> 
									<?php }
								?>
							</div>
						</div>	
					</div>	
				</div>
				<div class="row form-group">
					<div class="col-md-12">
				    	<div class="phuyu_body_row" align="center">
							 <br> <br>

							<button type="button" class="btn btn-success" v-on:click="phuyu_editar()">
								<i class="fa fa-edit"></i> CONFIGURAR FACTURACIÓN <span class="hidden-xs">ELECTRONICA - EMPRESA</span>
							</button>
							<button type="button" class="btn btn-warning" v-on:click="phuyu_copia()">
								<i class="fa fa-database"></i> <span class="hidden-xs">GENERAR</span> COPIA SEGURIDAD
							</button>
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
<script src="<?php echo base_url();?>phuyu/phuyu_empresa/index.js"> </script>