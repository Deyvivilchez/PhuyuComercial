<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<section id="phuyu_index" class="phuyu-velzon-list">
	<div class="phuyu-page-title">
		<div class="phuyu-page-icon"><i class="bi bi-shield-lock"></i></div>
		<div>
			<div class="text-muted small text-uppercase fw-semibold">Administracion</div>
			<h4 class="mb-0 fw-bold">Sistema</h4>
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb mb-0 mt-1">
					<li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
					<li class="breadcrumb-item active" aria-current="page">Sistema</li>
				</ol>
			</nav>
		</div>
	</div>

	<div class="phuyu_body">
		<div class="row g-3">
			<div class="col-12 col-lg-6">
				<div class="card phuyu-card h-100">
					<div class="card-body text-center p-4">
						<div class="phuyu-page-icon mx-auto mb-3"><i class="bi bi-database"></i></div>
						<h5 class="fw-bold">Limpiar base de datos Phuyu</h5>
						<p class="text-muted small mb-4">Ejecuta la limpieza configurada para el sistema actual.</p>
						<button type="button" class="btn btn-danger" v-on:click="phuyu_limpiarbd()">
							<i class="bi bi-arrow-clockwise me-1"></i> Limpiar base de datos
						</button>
					</div>
				</div>
			</div>

			<div class="col-12 col-lg-6">
				<div class="card phuyu-card h-100">
					<div class="card-body text-center p-4">
						<div class="phuyu-page-icon mx-auto mb-3"><i class="bi bi-archive"></i></div>
						<h5 class="fw-bold">Copia de seguridad</h5>
						<p class="text-muted small mb-4">Genera un backup de la informacion del sistema.</p>
						<button type="button" class="btn btn-warning" v-on:click="phuyu_backup()">
							<i class="bi bi-cloud-download me-1"></i> Sacar backup
						</button>
					</div>
				</div>
			</div>
		</div>

		<div class="modal fade" id="credimax_reportes" tabindex="-1" role="dialog">
			<div class="modal-dialog modal-fullscreen-sm-down" style="margin:0px;">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title">Reporte de creditos - <?php echo $_SESSION["credimax_empresa"];?></h4>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
					</div>
					<div class="modal-body" id="reportes_credimax" style="height:300px;padding:0px 0px 5px 0px">
						<iframe id="credimax_pdf" src="" style="width:100%; height:100%; border:none;"> </iframe>
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
