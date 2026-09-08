
<div class="ph-404-embed" role="region" aria-label="Página no encontrada" style="min-height: 70vh; display: flex; align-items: center; justify-content: center; padding: 1rem;">
  <div class="card shadow-lg border-0 rounded-4 overflow-hidden" style="max-width: 1000px; width: 100%;">
    <!-- Header con logo -->
    <div class="card-header bg-light d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div class="d-flex align-items-center gap-3">
        <img src="<?php echo base_url('public/img/phuyu2024-bk.png'); ?>"
             alt="Phuyu System"
             style="height: 32px; width: auto;"
             onerror="this.onerror=null; this.src='<?php echo base_url('public/img/phuyu_logo.png'); ?>'">
        <span class="badge bg-info bg-opacity-25 text-info px-3 py-2 rounded-pill">Phuyu · Sistema</span>
      </div>
    </div>

    <!-- Cuerpo principal -->
    <div class="card-body p-4 p-lg-5">
      <div class="row g-4 align-items-center">
        <!-- Columna izquierda: texto y acciones -->
        <div class="col-lg-7">
          <h1 class="display-1 fw-bold text-primary mb-2">404</h1>
          <h2 class="h4 text-dark fw-semibold">La página solicitada no existe o fue movida.</h2>
          <p class="text-muted mt-3 mb-4">
            Puedes regresar al inicio, volver atrás o ir directo a un módulo.
          </p>
          <div class="d-flex flex-wrap gap-3 mb-4">
            <a href="<?php echo base_url(); ?>" class="btn btn-primary" data-force-nav>
              <i class="bi bi-house-door me-1"></i> Ir al inicio
            </a>
            <button type="button" class="btn btn-outline-secondary" id="backButton">
              <i class="bi bi-arrow-left me-1"></i> Volver atrás
            </button>
          </div>
          <div class="row g-2">
            <div class="col-md-4 col-12">
              <a href="<?php echo base_url('administracion'); ?>" class="btn btn-outline-light w-100 border text-start" data-force-nav>
                <i class="bi bi-speedometer2 me-1 text-primary"></i> Administración
              </a>
            </div>
            <div class="col-md-4 col-12">
              <a href="<?php echo base_url('ventas'); ?>" class="btn btn-outline-light w-100 border text-start" data-force-nav>
                <i class="bi bi-cart me-1 text-success"></i> Ventas
              </a>
            </div>
            <div class="col-md-4 col-12">
              <a href="<?php echo base_url('cpe'); ?>" class="btn btn-outline-light w-100 border text-start" data-force-nav>
                <i class="bi bi-file-earmark-text me-1 text-info"></i> CPE
              </a>
            </div>
          </div>
        </div>

        <!-- Columna derecha: ilustración -->
        <div class="col-lg-5 text-center">
          <div class="bg-dark bg-opacity-10 rounded-4 p-3 d-inline-block">
            <img src="<?php echo base_url('public/img/404.png'); ?>"
                 alt="Ilustración 404"
                 style="max-height: 200px; width: auto;"
                 onerror="this.onerror=null; this.src='<?php echo base_url('public/img/placeholder.png'); ?>'">
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="card-footer bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
      <small class="text-muted">© <?php echo date('Y'); ?> Phuyu System</small>
      <a href="<?php echo base_url('contacto'); ?>" class="text-decoration-none fw-semibold" data-force-nav>
        <i class="bi bi-headset me-1"></i> Soporte
      </a>
    </div>
  </div>
</div>

<script>
  (function() {
    // Manejo de navegación forzada (evita conflictos con SPA)
    document.querySelectorAll('[data-force-nav]').forEach(function(el) {
      el.addEventListener('click', function(e) {
        var url = el.getAttribute('href');
        if (url && url !== 'javascript:void(0)') {
          e.preventDefault();
          window.location.href = url;
        }
      });
    });

    // Botón "Volver atrás" con fallback a inicio
    var backBtn = document.getElementById('backButton');
    if (backBtn) {
      backBtn.addEventListener('click', function() {
        if (history.length > 1) {
          history.back();
        } else {
          window.location.href = '<?php echo base_url(); ?>';
        }
      });
    }
  })();
</script>