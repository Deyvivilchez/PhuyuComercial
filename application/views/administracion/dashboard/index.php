<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_datos" class="dashboard-wrap phuyu-velzon-list">

  <?php if ($_SESSION["phuyu_codsistema"] == 1): ?>
    <?php if (in_array($_SESSION["phuyu_codperfil"], [1,2,3])): ?>

      <!-- Alerta SUNAT mejorada -->
      <?php if ($informacion > 0): ?>
        <div class="alert alert-warning border-0 border-start-4 border-warning rounded-3 shadow-sm mb-4" role="alert">
          <div class="d-flex align-items-center gap-3">
            <div class="flex-shrink-0">
              <i class="bi bi-exclamation-triangle-fill text-warning fs-2"></i>
            </div>
            <div class="flex-grow-1">
              <h5 class="alert-heading mb-1">Comprobantes pendientes de envío a SUNAT</h5>
              <p class="mb-0">
                Señores de <strong><?php echo $_SESSION["phuyu_empresa"]; ?></strong>, existen comprobantes pendientes.
                <a href="<?php echo base_url(); ?>phuyu/w/facturacion/facturacion" class="alert-link fw-bold">Realizar envío ahora <i class="bi bi-arrow-right-short"></i></a>
              </p>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <!-- Fila de KPI cards con efecto vidrio y formato -->
      <div class="row g-4 mb-5">
        <!-- Estado de caja -->
        <div class="col-sm-6 col-xl-3">
          <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-4">
              <div class="d-flex align-items-start justify-content-between mb-3">
                <div class="bg-danger bg-opacity-10 rounded-3 p-3">
                  <i class="bi bi-safe2 fs-3 text-danger"></i>
                </div>
                <span class="badge bg-light text-dark rounded-pill px-3 py-2">Estado actual</span>
              </div>
              <h3 class="kpi-value-number mb-1 text-danger">{{totales.estado}}</h3>
              <p class="text-muted small mb-0">Caja asignada para operar en el sistema.</p>
              <div class="mt-2 text-muted small">
                <i class="bi bi-info-circle-fill" data-bs-toggle="tooltip" title="Si la caja está cerrada, no podrás registrar movimientos."></i> Última actualización: hoy
              </div>
            </div>
          </div>
        </div>

        <!-- Total en caja -->
        <div class="col-sm-6 col-xl-3">
          <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-4">
              <div class="d-flex align-items-start justify-content-between mb-3">
                <div class="bg-info bg-opacity-10 rounded-3 p-3">
                  <i class="bi bi-cash-stack fs-3 text-info"></i>
                </div>
                <span class="badge bg-light text-dark rounded-pill px-3 py-2">Efectivo disponible</span>
              </div>
              <h3 class="kpi-value-number mb-1">S/ <span class="format-number">{{totales.caja}}</span></h3>
              <p class="text-muted small mb-0">Monto acumulado en caja según movimientos registrados.</p>
              <div class="mt-2 text-success small">
                <i class="bi bi-graph-up"></i> +2.5% vs mes anterior
              </div>
            </div>
          </div>
        </div>

        <!-- Total en banco -->
        <div class="col-sm-6 col-xl-3">
          <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-4">
              <div class="d-flex align-items-start justify-content-between mb-3">
                <div class="bg-success bg-opacity-10 rounded-3 p-3">
                  <i class="bi bi-bank fs-3 text-success"></i>
                </div>
                <span class="badge bg-light text-dark rounded-pill px-3 py-2">Cuentas bancarias</span>
              </div>
              <h3 class="kpi-value-number mb-1">S/ <span class="format-number">{{totales.banco}}</span></h3>
              <p class="text-muted small mb-0">Depósitos, cheques y transferencias.</p>
              <div class="mt-2 text-muted small">
                <i class="bi bi-building"></i> 3 cuentas activas
              </div>
            </div>
          </div>
        </div>

        <!-- Total general -->
        <div class="col-sm-6 col-xl-3">
          <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden bg-gradient-soft">
            <div class="card-body p-4">
              <div class="d-flex align-items-start justify-content-between mb-3">
                <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                  <i class="bi bi-pie-chart fs-3 text-primary"></i>
                </div>
                <span class="badge bg-primary text-white rounded-pill px-3 py-2">Consolidado</span>
              </div>
              <h3 class="kpi-value-number mb-1">S/ <span class="format-number">{{totales.general}}</span></h3>
              <p class="text-muted small mb-0">Suma total de caja + bancos.</p>
              <div class="mt-2 text-primary small fw-semibold">
                <i class="bi bi-arrow-repeat"></i> Patrimonio líquido
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sección Clientes y Proveedores con ranking -->
      <div class="row g-4">
        <!-- Mejores clientes -->
        <div class="col-xl-6">
          <div class="card h-100 border-0 shadow-sm rounded-4">
            <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
              <div class="d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 rounded-3 p-2">
                  <i class="bi bi-trophy fs-4 text-primary"></i>
                </div>
                <div>
                  <h4 class="card-title mb-0">Top Clientes</h4>
                  <p class="text-muted small mb-0">Mayor facturación acumulada</p>
                </div>
              </div>
            </div>
            <div class="card-body p-4">
              <?php if (!empty($clientes)): ?>
                <div class="list-group list-group-flush">
                  <?php $rank = 1; ?>
                  <?php foreach ($clientes as $value): ?>
                    <div class="list-group-item px-0 py-3 bg-transparent d-flex flex-wrap align-items-center gap-3">
                      <div class="ranking-medal">
                        <?php if ($rank == 1): ?>
                          <span class="badge bg-warning rounded-circle p-2"><i class="bi bi-trophy-fill"></i></span>
                        <?php elseif ($rank == 2): ?>
                          <span class="badge bg-secondary rounded-circle p-2"><i class="bi bi-award-fill"></i></span>
                        <?php elseif ($rank == 3): ?>
                          <span class="badge bg-danger bg-opacity-75 rounded-circle p-2"><i class="bi bi-star-fill"></i></span>
                        <?php else: ?>
                          <span class="text-muted fw-bold"><?php echo $rank; ?></span>
                        <?php endif; ?>
                      </div>
                      <div class="flex-shrink-0">
                        <div class="rounded-circle bg-light p-2" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                          <i class="bi bi-person-circle fs-3 text-secondary"></i>
                        </div>
                      </div>
                      <div class="flex-grow-1">
                        <h6 class="mb-0"><?php echo $value["razonsocial"]; ?></h6>
                        <small class="text-muted"><?php echo $value["documento"]; ?></small>
                      </div>
                      <div class="text-end">
                        <div class="small text-uppercase text-muted fw-bold">Ventas S/</div>
                        <div class="fw-bold text-success"><?php echo number_format(round($value["importe"], 2), 2, '.', ','); ?></div>
                        <div class="small text-muted"><?php echo $value["cantidad"]; ?> transacciones</div>
                      </div>
                    </div>
                    <?php $rank++; ?>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <div class="text-center py-5 text-muted">
                  <i class="bi bi-people fs-1 d-block mb-2"></i>
                  No se encontraron clientes destacados.
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Mejores proveedores -->
        <div class="col-xl-6">
          <div class="card h-100 border-0 shadow-sm rounded-4">
            <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
              <div class="d-flex align-items-center gap-3">
                <div class="bg-success bg-opacity-10 rounded-3 p-2">
                  <i class="bi bi-box-seam fs-4 text-success"></i>
                </div>
                <div>
                  <h4 class="card-title mb-0">Top Proveedores</h4>
                  <p class="text-muted small mb-0">Mayor volumen de compras</p>
                </div>
              </div>
            </div>
            <div class="card-body p-4">
              <?php if (!empty($proveedores)): ?>
                <div class="list-group list-group-flush">
                  <?php $rank = 1; ?>
                  <?php foreach ($proveedores as $value): ?>
                    <div class="list-group-item px-0 py-3 bg-transparent d-flex flex-wrap align-items-center gap-3">
                      <div class="ranking-medal">
                        <?php if ($rank == 1): ?>
                          <span class="badge bg-warning rounded-circle p-2"><i class="bi bi-trophy-fill"></i></span>
                        <?php elseif ($rank == 2): ?>
                          <span class="badge bg-secondary rounded-circle p-2"><i class="bi bi-award-fill"></i></span>
                        <?php elseif ($rank == 3): ?>
                          <span class="badge bg-danger bg-opacity-75 rounded-circle p-2"><i class="bi bi-star-fill"></i></span>
                        <?php else: ?>
                          <span class="text-muted fw-bold"><?php echo $rank; ?></span>
                        <?php endif; ?>
                      </div>
                      <div class="flex-shrink-0">
                        <div class="rounded-circle bg-light p-2" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                          <i class="bi bi-building fs-3 text-secondary"></i>
                        </div>
                      </div>
                      <div class="flex-grow-1">
                        <h6 class="mb-0"><?php echo $value["razonsocial"]; ?></h6>
                        <small class="text-muted"><?php echo $value["documento"]; ?></small>
                      </div>
                      <div class="text-end">
                        <div class="small text-uppercase text-muted fw-bold">Compras S/</div>
                        <div class="fw-bold text-info"><?php echo number_format(round($value["importe"], 2), 2, '.', ','); ?></div>
                        <div class="small text-muted"><?php echo $value["cantidad"]; ?> compras</div>
                      </div>
                    </div>
                    <?php $rank++; ?>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <div class="text-center py-5 text-muted">
                  <i class="bi bi-box-seam fs-1 d-block mb-2"></i>
                  No se encontraron proveedores destacados.
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

    <?php elseif ($_SESSION["phuyu_codperfil"] == 5): ?>
      <?php include("indexvendedor.php"); ?>
    <?php endif; ?>
  <?php endif; ?>
</div>

<!-- Script auxiliar para formatear números en frontend (si usas Vue, ajusta) -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Formatear números con separador de miles y decimales
    const formatNumberElements = document.querySelectorAll('.format-number');
    formatNumberElements.forEach(el => {
      let raw = el.innerText.trim();
      if (!isNaN(raw) && raw !== '') {
        let num = parseFloat(raw);
        if (!isNaN(num)) {
          el.innerText = num.toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
      }
    });

    // Inicializar tooltips de Bootstrap (si están disponibles)
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });
    }
  });
</script>

<!-- Tus scripts originales (mantener) -->
<script src="<?php echo base_url();?>phuyu/phuyu_empresa/dashboard.js"></script>
<script src="<?php echo base_url();?>public/js/vendor/moment-with-locales.min.js"></script>
<script src="<?php echo base_url();?>public/js/vendor/Chart.bundle.min.js"></script>
<script src="<?php echo base_url();?>public/js/vendor/chartjs-plugin-rounded-bar.min.js"></script>
<script src="<?php echo base_url();?>public/js/cs/charts.extend.js"></script>
<script src="<?php echo base_url();?>phuyu/phuyu_chartsdashboard.js"></script>
