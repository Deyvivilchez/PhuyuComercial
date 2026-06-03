<!DOCTYPE html>
<html lang="es">
  <?php include("phuyu_css.php"); ?>
  <body>
    <style>
      /* ===== PROPUESTA NUEVA: Wizard de 3 pasos con cards e iconos ===== */
      html, body { height: 100%; margin:0; }
      [v-cloak] { display: none; }
      body { background: linear-gradient(135deg,#f8fafc 0%, #e0f2fe 100%); font-family: 'Nunito Sans', sans-serif; }

      .admin-shell { min-height:100vh; display:flex; align-items:center; justify-content:center; padding:28px 16px; }
      .admin-card { width:min(1080px,96vw); background:#fff; border:1px solid #e5e7eb; border-radius:22px; box-shadow:0 24px 70px rgba(0,0,0,.12); overflow:hidden; }

      /* Hero superior con logo Phuyu (imponente pero sutil) */
      .brand-hero{ position:relative; padding:28px 16px 18px; text-align:center; background:linear-gradient(90deg,#38bdf8 0%,#6366f1 100%); }
      .brand-hero::after{ content:""; position:absolute; inset:auto 0 -36% 0; margin:auto; width:420px; height:240px; filter:blur(40px); opacity:.35; background:radial-gradient(50% 50% at 50% 50%, #ffffff 0%, rgba(255,255,255,0) 70%); pointer-events:none; }
      .brand-hero img{ max-width:260px; height:auto; filter: drop-shadow(0 10px 24px rgba(0,0,0,.25)); }
      .brand-hero h2{ color:#fff; font-weight:800; margin:10px 0 0; }

      /* Bloque de bienvenida */
      .welcome{ text-align:center; color:#1f2937; padding:12px 16px 4px; }
      .welcome small{ color:#6b7280; }

      /* Pasos */
      .steps{ padding: 10px 24px 26px; }
      .step{ margin-top: 16px; }
      .step-title{ display:flex; align-items:center; gap:10px; font-weight:800; color:#111827; margin-bottom:12px; }
      .step.disabled { opacity:.5; pointer-events:none; }

      .cards-grid{ display:grid; grid-template-columns: repeat(auto-fit,minmax(200px,1fr)); gap:16px; }
      .card-option{ position:relative; background:#f9fafb; border:2px solid transparent; border-radius:16px; padding:18px 14px; text-align:left; cursor:pointer; transition:all .16s ease; }
      .card-option:hover{ background:#fff; box-shadow:0 10px 30px rgba(99,102,241,.15); }
      .card-option.active{ border-color:#6366f1; background:#eef2ff; }
      .card-icon{ font-size:1.6rem; line-height:1; color:#38bdf8; margin-right:10px; }
      .card-title{ font-weight:800; color:#111827; display:block; }
      .card-sub{ color:#6b7280; font-size:.9rem; }
      .selected-mark{ position:absolute; right:10px; top:10px; color:#6366f1; font-size:1.25rem; }

      /* Acciones */
      .actions{ padding: 8px 24px 26px; display:flex; justify-content:center; }
      .btn-main{ height:52px; border-radius:14px; border:0; padding:0 26px; background:linear-gradient(90deg,#38bdf8 0%, #6366f1 100%); color:#fff; font-weight:800; letter-spacing:.2px; display:inline-flex; align-items:center; gap:.5rem; box-shadow:0 12px 30px rgba(99,102,241,.3); }
      .btn-main[disabled]{ opacity:.6; cursor:not-allowed; }

      /* Responsive */
      @media (max-width: 720px) {
        .brand-hero img{ max-width:200px; }
      }
    </style>

    <div class="admin-shell">
      <div class="admin-card" id="phuyu_administrar" v-cloak>
        <!-- Hero con logo -->
        <div class="brand-hero">
          <img src="<?php echo base_url();?>/public/img/logo_completo.png" alt="Phuyu" />
          <h2>Bienvenido</h2>
        </div>

        <!-- Bienvenida contextual -->
        <div class="welcome">
          Hola <b><?php echo $_SESSION["phuyu_usuario"];?></b> — Espacio de <b><?php echo $_SESSION["phuyu_empresa"];?></b><br>
          <small>RUC: <?php echo $_SESSION["phuyu_ruc"];?></small>
        </div>

        <!-- PASO 1: Sucursal (cards PHP) -->
        <div class="steps">
          <div class="step">
            <div class="step-title"><i class="bi bi-geo-alt"></i> Elige tu sucursal</div>
            <div class="cards-grid">
              <?php foreach ($info as $key => $value) { ?>
                <button type="button" class="card-option"
                        @click="campos.codsucursal='<?php echo $value["codsucursal"]?>'; campos.codalmacen=''; campos.codcaja=''; phuyu_resultados();"
                        :class="{'active': campos.codsucursal=='<?php echo $value["codsucursal"]?>'}">
                  <div style="display:flex; align-items:flex-start;">
                    <i class="bi bi-buildings card-icon"></i>
                    <div>
                      <span class="card-title"><?php echo $value["descripcion"]?></span>
                      <span class="card-sub">Sucursal #<?php echo $value["codsucursal"]?></span>
                    </div>
                  </div>
                  <i v-if="campos.codsucursal=='<?php echo $value["codsucursal"]?>'" class="bi bi-check2-circle selected-mark"></i>
                </button>
              <?php } ?>
            </div>
          </div>

          <!-- PASO 2: Almacén (cards Vue) -->
          <div class="step" :class="{disabled: !campos.codsucursal}">
            <div class="step-title"><i class="bi bi-box-seam"></i> Selecciona un almacén</div>
            <div class="cards-grid">
              <button type="button" class="card-option" v-for="dato in almacenes" :key="dato.codalmacen"
                      @click="campos.codalmacen=dato.codalmacen; campos.codcaja='';"
                      :class="{'active': campos.codalmacen===dato.codalmacen}">
                <div style="display:flex; align-items:flex-start;">
                  <i class="bi bi-archive card-icon"></i>
                  <div>
                    <span class="card-title">{{dato.descripcion}}</span>
                    <span class="card-sub">Código: {{dato.codalmacen}}</span>
                  </div>
                </div>
                <i v-if="campos.codalmacen===dato.codalmacen" class="bi bi-check2-circle selected-mark"></i>
              </button>
            </div>
          </div>

          <!-- PASO 3: Caja (cards Vue) -->
          <div class="step" :class="{disabled: !campos.codalmacen}">
            <div class="step-title"><i class="bi bi-cash-coin"></i> Selecciona una caja</div>
            <div class="cards-grid">
              <button type="button" class="card-option" v-for="dato in cajas" :key="dato.codcaja"
                      @click="campos.codcaja=dato.codcaja"
                      :class="{'active': campos.codcaja===dato.codcaja}">
                <div style="display:flex; align-items:flex-start;">
                  <i class="bi bi-credit-card card-icon"></i>
                  <div>
                    <span class="card-title">{{dato.descripcion}}</span>
                    <span class="card-sub">Código: {{dato.codcaja}}</span>
                  </div>
                </div>
                <i v-if="campos.codcaja===dato.codcaja" class="bi bi-check2-circle selected-mark"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Acción principal -->
        <div class="actions">
          <button type="button" class="btn-main" @click="administrar()" :disabled="estado==1 || !campos.codcaja">
            <i class="bi bi-arrow-right"></i> Administrar
          </button>
        </div>

        <!-- Fallback: selects ocultos para compatibilidad (opcional) -->
        <select class="d-none" v-model="campos.codsucursal" @change="phuyu_resultados">
          <option value="">Seleccione sucursal</option>
          <?php foreach ($info as $key => $value) { ?>
            <option value="<?php echo $value["codsucursal"]?>"><?php echo $value["descripcion"]?></option>
          <?php } ?>
        </select>
        <select class="d-none" v-model="campos.codalmacen">
          <option value="">Seleccione almacen</option>
          <option v-for="dato in almacenes" :value="dato.codalmacen">{{dato.descripcion}}</option>
        </select>
        <select class="d-none" v-model="campos.codcaja">
          <option value="">Seleccione caja</option>
          <option v-for="dato in cajas" :value="dato.codcaja">{{dato.descripcion}}</option>
        </select>
      </div>
    </div>

    <?php include("phuyu_js.php"); ?>
    <script> var url = "<?php echo base_url();?>"; </script>
    <script src="<?php echo base_url();?>phuyu/phuyu_ministrar.js"></script>
  </body>
</html>
