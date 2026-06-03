<!DOCTYPE html>
<html lang="es" data-placement="horizontal" data-behaviour="pinned" data-layout="boxed" data-radius="rounded" data-color="light-blue" data-navcolor="light">
  <?php include("phuyu_css.php"); ?>
  <body>
    <style>
      /* ===== Rediseño total, centrado y limpio (LIGHT) ===== */
      html, body { height: 100%; }
      body { margin: 0; }

      .admin-wrap {
        min-height: 100vh;
        display: grid;
        place-items: center;
        padding: 32px 20px;
        background: linear-gradient(135deg, #f8fafc 0%, #e0f2fe 100%);
      }

      .admin-card {
        width: min(820px, 96vw);
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0,0,0,.12);
        overflow: hidden;
        display: grid;
        grid-template-columns: 1.25fr 1fr;
      }

      /* Columna izquierda: bienvenida */
      .admin-left {
        padding: 28px 28px 8px 28px;
        border-right: 1px solid #eef2f7;
        display: flex; flex-direction: column; justify-content: center;
      }
      .brand { display:flex; align-items:center; gap:14px; margin-bottom: 8px; }
      .brand img { height: 44px; width: auto; }
      .brand-title { font-weight: 800; color: #111827; font-size: 1.15rem; }

      .hello { color:#111827; font-weight:700; margin: 6px 0 2px; font-size: 1.05rem; }
      .hello b { font-weight:800; }
      .empresa { color:#1f2937; font-size: .98rem; }
      .ruc { color:#6b7280; font-size: .92rem; }

      .tip { margin-top: 16px; color:#6b7280; font-size:.9rem; }

      /* Columna derecha: formulario */
      .admin-right { padding: 28px; display:flex; flex-direction:column; justify-content:center; }
      .form-title { text-align:center; font-weight:800; color:#1f2937; margin-bottom:14px; }

      .select-block { margin-bottom: 12px; }
      .select-label { display:block; margin-bottom:6px; color:#374151; font-weight:700; font-size:.92rem; }
      .select-ctl {
        height: 48px; border-radius:12px; border:1px solid #d1d5db; padding:0 12px; background:#fff; color:#111827; width:100%;
      }
      .select-ctl:focus { outline:none; border-color:#38bdf8; box-shadow: 0 0 0 .2rem rgba(56,189,248,.15); }

      .actions { display:flex; justify-content:center; margin-top:16px; }
      .btn-main {
        height: 48px; border-radius:12px; border:1px solid transparent; padding:0 18px;
        background: linear-gradient(90deg,#38bdf8 0%, #6366f1 100%);
        color:#fff; font-weight:800; letter-spacing:.2px; display:flex; align-items:center; gap:.5rem;
        box-shadow: 0 10px 30px rgba(99,102,241,.25); transition: transform .15s ease, box-shadow .15s ease;
      }
      .btn-main:hover { transform: translateY(-1px); box-shadow: 0 14px 36px rgba(99,102,241,.35); }
      .btn-main[disabled] { opacity:.6; cursor:not-allowed; }

      /* Responsive: en móviles se apila */
      @media (max-width: 900px) {
        .admin-card { grid-template-columns: 1fr; }
        .admin-left { border-right: 0; border-bottom: 1px solid #eef2f7; }
      }
    </style>

    <div id="root" class="admin-wrap">
      <div class="admin-card">
        <!-- Columna bienvenida -->
        <div class="admin-left">
          <div class="brand">
            <img src="<?php echo base_url();?>/public/img/logo_completo.png" alt="Logo" />
            <div class="brand-title">phuyu Soft</div>
          </div>
          <div class="hello">Hola <b><?php echo $_SESSION["phuyu_usuario"];?></b> 👋</div>
          <div class="empresa">Bienvenido a <b><?php echo $_SESSION["phuyu_empresa"];?></b></div>
          <div class="ruc">RUC: <?php echo $_SESSION["phuyu_ruc"];?></div>
          <div class="tip">Selecciona tu sucursal, almacén y caja para administrar.</div>
        </div>

        <!-- Columna formulario (MISMA funcionalidad Vue + IDs y v-models) -->
        <div class="admin-right" id="phuyu_administrar">
          <form id="formulario" class="form-horizontal" v-on:submit.prevent="administrar()">
            <div class="form-title">Configura tu espacio de trabajo</div>

            <div class="select-block">
              <label class="select-label">Sucursal</label>
              <select class="select-ctl form-select form-control-lg" v-on:change="phuyu_resultados" v-model="campos.codsucursal" required>
                <option value="">Seleccione sucursal</option>
                <?php foreach ($info as $key => $value) { ?>
                  <option value="<?php echo $value["codsucursal"]?>"><?php echo $value["descripcion"]?></option>
                <?php } ?>
              </select>
            </div>

            <div class="select-block">
              <label class="select-label">Almacén</label>
              <select class="select-ctl form-select form-control-lg" v-model="campos.codalmacen" required>
                <option value="">Seleccione almacén</option>
                <option v-for="dato in almacenes" v-bind:value="dato.codalmacen">{{dato.descripcion}}</option>
              </select>
            </div>

            <div class="select-block">
              <label class="select-label">Caja</label>
              <select class="select-ctl form-select form-control-lg" v-model="campos.codcaja" required>
                <option value="">Seleccione caja</option>
                <option v-for="dato in cajas" v-bind:value="dato.codcaja">{{dato.descripcion}}</option>
              </select>
            </div>

            <div class="actions">
              <button type="submit" class="btn-main" v-bind:disabled="estado==1">
                <i data-acorn-icon="arrow-right"></i>
                <span>Administrar sucursal</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <?php include("phuyu_js.php"); ?>
    <script> var url = "<?php echo base_url();?>"; </script>
    <script src="<?php echo base_url();?>phuyu/phuyu_ministrar.js"></script>
  </body>
</html>