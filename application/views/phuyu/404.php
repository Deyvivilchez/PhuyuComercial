<!-- 404 EMBEBIDO / COMPONENTE -->
<div class="ph-404-embed" role="region" aria-label="Página no encontrada">
  <style>
    /* ===== Scope solo aquí ===== */
    .ph-404-embed { 
      /* ocupa el alto disponible del contenedor donde lo insertes */
      min-height: min(72vh, 680px);
      display: grid; place-items: center; padding: 16px;
      isolation: isolate; /* evita mezclarse con overlays externos */
    }
    .ph-404-embed *{ box-sizing:border-box; font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Arial; }

    .ph-404-card{
      width: min(960px, 100%);
      background:#fff; border:1px solid #e7eef6; border-radius:16px;
      box-shadow: 0 10px 24px rgba(12,74,110,.10); overflow:hidden; position:relative; z-index:1;
    }
    .ph-404-card::before{ content:""; position:absolute; inset:0 0 auto 0; height:4px;
      background: linear-gradient(90deg,#0ea5e9,#22d3ee,#7c3aed);
    }

    .ph-404-head{ display:flex; align-items:center; gap:12px; padding:14px 18px;
      background: linear-gradient(180deg, rgba(14,165,233,.08), rgba(34,211,238,.06));
      border-bottom:1px solid #e9eef3;
    }
    .ph-404-head img{ height:28px; width:auto; }
    .ph-404-tag{ margin-left:auto; font-size:12px; font-weight:700; color:#0b1b2b;
      background:#e6f7fd; border:1px solid #cbeefb; border-radius:999px; padding:4px 10px; }

    .ph-404-body{ padding:22px 18px; display:grid; gap:20px; grid-template-columns: 1.15fr .85fr; }
    @media (max-width: 900px){ .ph-404-body{ grid-template-columns: 1fr; } }

    .ph-404-title{ margin:0 0 8px; font-size:42px; font-weight:900; color:#0b1b2b; letter-spacing:.2px; }
    .ph-404-sub{ margin:0; color:#475569; font-weight:600; }
    .ph-404-desc{ margin:10px 0 18px; color:#5b6470; }

    .ph-404-actions{ display:flex; flex-wrap:wrap; gap:10px; }
    .ph-btn{
      display:inline-flex; align-items:center; gap:8px; text-decoration:none; font-weight:700;
      padding:11px 16px; border-radius:10px; border:1px solid transparent; cursor:pointer;
      transition: background .2s, box-shadow .2s, transform .05s;
    }
    .ph-btn:active{ transform: translateY(1px) scale(.99); }
    .ph-btn-primary{ color:#fff; background:linear-gradient(90deg,#0ea5e9,#22d3ee); box-shadow:0 8px 18px rgba(14,165,233,.35); }
    .ph-btn-primary:hover{ box-shadow:0 10px 22px rgba(14,165,233,.45); }
    .ph-btn-ghost{ color:#0b1b2b; background:#f8fafc; border-color:#e7eef6; }
    .ph-btn-ghost:hover{ background:#eef6fb; }

    .ph-404-quick{ display:grid; gap:10px; grid-template-columns: repeat(3, 1fr); margin-top:12px; }
    @media (max-width:720px){ .ph-404-quick{ grid-template-columns: 1fr; } }
    .ph-q{ display:flex; align-items:center; gap:10px; padding:10px 12px; border-radius:10px;
      border:1px solid #e7eef6; background:#fff; text-decoration:none; color:#0b1b2b; font-weight:700;
      transition: background .2s, box-shadow .2s;
    }
    .ph-q:hover{ background: linear-gradient(90deg, rgba(14,165,233,.09), rgba(34,211,238,.06)); box-shadow: 0 6px 14px rgba(12,74,110,.12); }

    .ph-404-illu{ display:flex; align-items:center; justify-content:center; }
    .ph-404-illuBox{
      border:1px solid #e7eef6; border-radius:12px; padding:10px;
      background: radial-gradient(120% 100% at 0% 0%, rgba(14,165,233,.12), transparent 70%),
                  radial-gradient(120% 100% at 100% 100%, rgba(124,58,237,.12), transparent 70%),
                  #0f172a;
    }
    .ph-404-illuBox img{ display:block; height:190px; width:auto; object-fit:contain; filter: drop-shadow(0 10px 22px rgba(14,165,233,.35)); }

    .ph-404-foot{ padding:12px 18px; border-top:1px solid #e9eef3; color:#64748b; font-size:12px;
      display:flex; justify-content:space-between; align-items:center; gap:10px; }
    .ph-i{ width:18px; height:18px; }

    /* Centrado vertical extra si el contenedor padre es muy alto */
    .ph-404-embed.ph-full-height{
      min-height: calc(100vh - 140px); /* ajusta si tu header ocupa más/menos */
    }
  </style>

  <div class="ph-404-card">
    <div class="ph-404-head">
      <img src="<?php echo base_url(); ?>public/img/phuyu2024-bk.png"
           alt="Phuyu System"
           onerror="this.onerror=null;this.src='<?php echo base_url(); ?>public/img/phuyu_logo.png';">
      <span class="ph-404-tag">Phuyu · Sistema</span>
    </div>

    <div class="ph-404-body">
      <section>
        <h1 class="ph-404-title">404</h1>
        <p class="ph-404-sub">La página solicitada no existe o fue movida.</p>
        <p class="ph-404-desc">Puedes regresar al inicio, volver atrás o ir directo a un módulo.</p>

        <div class="ph-404-actions" role="group" aria-label="Acciones">
          <!-- Forzamos navegación aunque tu SPA intercepte <a> -->
          <a href="<?php echo base_url(); ?>" class="ph-btn ph-btn-primary"
             data-force-nav="<?php echo base_url(); ?>">Ir al inicio</a>

          <a href="javascript:void(0)" class="ph-btn ph-btn-ghost"
             onclick="if (history.length>1){history.back()}else{window.location.href='<?php echo base_url(); ?>'}">Volver atrás</a>
        </div>

        <!-- Atajos (ajusta rutas reales) -->
        <div class="ph-404-quick" aria-label="Módulos">
          <a class="ph-q" href="<?php echo base_url('administracion'); ?>" data-force-nav="<?php echo base_url('administracion'); ?>">Administración</a>
          <a class="ph-q" href="<?php echo base_url('ventas'); ?>" data-force-nav="<?php echo base_url('ventas'); ?>">Ventas</a>
          <a class="ph-q" href="<?php echo base_url('cpe'); ?>" data-force-nav="<?php echo base_url('cpe'); ?>">CPE</a>
        </div>
      </section>

      <aside class="ph-404-illu" aria-hidden="true">
        <div class="ph-404-illuBox">
          <img src="<?php echo base_url(); ?>public/img/404.png" alt="404">
        </div>
      </aside>
    </div>

    <div class="ph-404-foot">
      <span>© <?php echo date('Y'); ?> Phuyu System</span>
      <a href="<?php echo base_url('contacto'); ?>" data-force-nav="<?php echo base_url('contacto'); ?>" style="text-decoration:none; font-weight:700;">Soporte</a>
    </div>
  </div>

  <script>
    // Forzar navegación "hard" aunque tu SPA haga preventDefault
    (function(){
      var root = document.currentScript && document.currentScript.parentElement || document;
      root.querySelectorAll('[data-force-nav]').forEach(function(a){
        a.addEventListener('click', function(ev){
          var url = a.getAttribute('data-force-nav');
          if (!url) return;
          ev.preventDefault();
          window.location.href = url;
        });
      });
    })();
  </script>
</div>
