<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div class="phuyu-velzon-list">
	<div class="phuyu-page-title">
		<span class="phuyu-page-icon"><i class="bi bi-bar-chart-line"></i></span>
		<div>
			<h4 class="mb-1">Dashboard vendedor</h4>
			<p class="text-muted mb-0">Seguimiento mensual de pedidos y ventas.</p>
		</div>
	</div>

	<div class="row g-3">
		<div class="col-12 col-xl-6">
			<section class="card phuyu-card h-100" id="roundedBarChartTitle">
				<div class="card-header bg-transparent border-0 pb-0">
					<h5 class="card-title mb-0">
						<i class="bi bi-clipboard-data text-primary me-1"></i> Gráfica de pedidos por mes
					</h5>
				</div>
				<div class="card-body">
					<div class="sh-40">
						<canvas id="roundedBarChart"></canvas>
					</div>
				</div>
			</section>
		</div>

		<div class="col-12 col-xl-6">
			<section class="card phuyu-card h-100" id="roundedBarChartTitle1">
				<div class="card-header bg-transparent border-0 pb-0">
					<h5 class="card-title mb-0">
						<i class="bi bi-currency-dollar text-success me-1"></i> Gráfica de ventas por mes
					</h5>
				</div>
				<div class="card-body">
					<div class="sh-40">
						<canvas id="roundedBarChart1"></canvas>
					</div>
				</div>
			</section>
		</div>
	</div>
</div>
