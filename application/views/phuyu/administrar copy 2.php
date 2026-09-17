<!DOCTYPE html>
<html lang="es">
  <?php include("phuyu_css.php"); ?>
  <body>
    <style>
      /* ===== PROPUESTA NUEVA: Wizard de 3 pasos con cards e iconos ===== */
      html, body { height: 100%; margin:0; }
      [v-cloak] { display: none; }
      body {
        background:
          radial-gradient(circle at top left, rgba(56, 189, 248, .16), transparent 24%),
          radial-gradient(circle at bottom right, rgba(99, 102, 241, .14), transparent 26%),
          linear-gradient(135deg,#f7fbff 0%, #eef6ff 46%, #f3f6ff 100%);
        font-family: 'Nunito Sans', sans-serif;
      }

      .admin-shell { min-height:100vh; display:flex; align-items:center; justify-content:center; padding:28px 16px; }
      .admin-card {
        width:min(1080px,96vw);
        background:linear-gradient(180deg,#ffffff 0%, #fbfdff 100%);
        border:1px solid #dbe6f4;
        border-radius:24px;
        box-shadow:0 26px 75px rgba(37, 99, 235, .10), 0 14px 30px rgba(15, 23, 42, .06);
        overflow:hidden;
      }

      /* Hero superior con logo Phuyu (imponente pero sutil) */
      .brand-hero{
        position:relative;
        padding:30px 18px 20px;
        text-align:center;
        background:
          radial-gradient(circle at 18% 24%, rgba(255,255,255,.42), transparent 18%),
          radial-gradient(circle at 82% 20%, rgba(255,255,255,.16), transparent 16%),
          linear-gradient(100deg,#0ea5e9 0%,#2563eb 48%,#4f46e5 100%);
      }
      .brand-hero::before{
        content:"";
        position:absolute;
        top:-34px;
        right:90px;
        width:170px;
        height:170px;
        border-radius:50%;
        border:1px solid rgba(255,255,255,.16);
      }
      .brand-hero::after{ content:""; position:absolute; inset:auto 0 -36% 0; margin:auto; width:420px; height:240px; filter:blur(40px); opacity:.35; background:radial-gradient(50% 50% at 50% 50%, #ffffff 0%, rgba(255,255,255,0) 70%); pointer-events:none; }
      .brand-hero img{ max-width:260px; height:auto; filter: drop-shadow(0 10px 24px rgba(0,0,0,.22)); }
      .brand-hero h2{ color:#fff; font-weight:800; margin:12px 0 0; letter-spacing:.2px; }

      /* Bloque de bienvenida */
      .welcome{
        text-align:center;
        color:#1f2937;
        padding:18px 18px 2px;
        background:linear-gradient(180deg, rgba(255,255,255,.55), rgba(240,247,255,.65));
      }
      .welcome strong, .welcome b{ color:#1d4ed8; }
      .welcome small{ color:#64748b; }

      /* Pasos */
      .steps{ padding: 10px 24px 26px; }
      .step{ margin-top: 16px; }
      .step-title{
        display:flex;
        align-items:center;
        gap:10px;
        font-weight:800;
        color:#111827;
        margin-bottom:12px;
        padding-left:4px;
      }
      .step-title i{
        width:32px;
        height:32px;
        border-radius:10px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        background:linear-gradient(135deg,#dbeafe,#e0e7ff);
        color:#2563eb;
        box-shadow:0 8px 18px rgba(37,99,235,.10);
      }
      .step.disabled { opacity:.5; pointer-events:none; }

      .cards-grid{ display:grid; grid-template-columns: repeat(auto-fit,minmax(220px,1fr)); gap:16px; }
      .card-option{
        position:relative;
        background:linear-gradient(180deg,#ffffff 0%, #f8fbff 100%);
        border:1px solid #dbe7f5;
        border-radius:18px;
        padding:18px 15px;
        text-align:left;
        cursor:pointer;
        transition:all .16s ease;
        box-shadow:0 12px 26px rgba(15,23,42,.05);
      }
      .card-option:hover{
        transform:translateY(-2px);
        border-color:#93c5fd;
        box-shadow:0 18px 36px rgba(37,99,235,.12);
      }
      .card-option.active{
        border-color:#3b82f6;
        background:linear-gradient(135deg,#eff6ff 0%, #eef2ff 100%);
        box-shadow:0 18px 38px rgba(59,130,246,.16);
      }
      .card-icon{
        width:42px;
        height:42px;
        border-radius:13px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        font-size:1.15rem;
        line-height:1;
        color:#2563eb;
        margin-right:12px;
        background:linear-gradient(135deg,#dbeafe,#e0f2fe);
        box-shadow:inset 0 1px 0 rgba(255,255,255,.7);
      }
      .card-title{ font-weight:800; color:#111827; display:block; }
      .card-sub{ color:#64748b; font-size:.9rem; }
      .selected-mark{
        position:absolute;
        right:12px;
        top:12px;
        color:#2563eb;
        font-size:1.25rem;
        background:#fff;
        border-radius:50%;
      }

      /* Acciones */
      .actions{ padding: 8px 24px 26px; display:flex; justify-content:center; }
      .btn-main{
        height:54px;
        border-radius:15px;
        border:0;
        padding:0 28px;
        background:linear-gradient(90deg,#0ea5e9 0%, #2563eb 52%, #4f46e5 100%);
        color:#fff;
        font-weight:800;
        letter-spacing:.2px;
        display:inline-flex;
        align-items:center;
        gap:.55rem;
        box-shadow:0 14px 32px rgba(59,102,241,.28);
      }
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
