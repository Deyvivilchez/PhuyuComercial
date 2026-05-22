<!DOCTYPE html>
<html lang="es">
  <?php include("phuyu_css.php"); ?>
  <body class="phuyu-admin-page">
    <style>
      html, body { min-height: 100%; margin: 0; }
      body.phuyu-admin-page {
        background:
          linear-gradient(135deg, rgba(15, 23, 42, .58), rgba(37, 99, 235, .32)),
          url('<?php echo base_url();?>public/plantilla_phuyu/images/auth-one-bg.jpg') center/cover fixed no-repeat;
        font-family: 'Nunito Sans', sans-serif;
      }
      [v-cloak] { display: none; }

      .admin-shell {
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 30px 20px;
        position: relative;
      }
      .admin-shell::before,
      .admin-shell::after {
        content: "";
        position: fixed;
        border-radius: 999px;
        pointer-events: none;
        z-index: 0;
        filter: blur(6px);
      }
      .admin-shell::before {
        width: 260px;
        height: 260px;
        top: 72px;
        left: 56px;
        background: radial-gradient(circle, rgba(59, 130, 246, .16), rgba(59, 130, 246, 0));
      }
      .admin-shell::after {
        width: 320px;
        height: 320px;
        right: 44px;
        bottom: 46px;
        background: radial-gradient(circle, rgba(79, 70, 229, .12), rgba(79, 70, 229, 0));
      }
      .admin-card {
        position: relative;
        z-index: 1;
        width: min(980px, 96vw);
        background: rgba(255,255,255,.94);
        backdrop-filter: blur(14px);
        border: 1px solid rgba(255, 255, 255, .75);
        border-radius: 30px;
        box-shadow: 0 30px 80px rgba(30, 41, 59, .14);
        overflow: hidden;
      }
      .brand-hero {
        position: relative;
        padding: 36px 28px 28px;
        text-align: center;
        background:
          radial-gradient(circle at top right, rgba(255,255,255,.12), transparent 24%),
          radial-gradient(circle at bottom left, rgba(255,255,255,.08), transparent 28%),
          linear-gradient(135deg, #23355f 0%, #405189 52%, #5c6db2 100%);
        overflow: hidden;
      }
      .brand-hero::before { content: ""; position: absolute; width: 220px; height: 220px; border-radius: 50%; background: rgba(255,255,255,.08); top: -70px; right: -50px; filter: blur(18px); }
      .brand-hero::after { content: ""; position: absolute; width: 180px; height: 180px; border-radius: 50%; background: rgba(255,255,255,.06); left: -55px; bottom: -75px; filter: blur(22px); }
      .brand-hero img { max-width: 146px; height: auto; filter: drop-shadow(0 16px 34px rgba(0,0,0,.20)); position: relative; z-index: 1; }
      .brand-hero h2 { position: relative; z-index: 1; color: #f8fafc; font-size: clamp(1.7rem, 2.2vw, 2.2rem); margin: 16px 0 0; font-weight: 800; letter-spacing: .08em; }
      .brand-hero p { position: relative; z-index: 1; margin: 10px auto 0; max-width: 560px; color: rgba(248,250,252,.84); font-size: .94rem; line-height: 1.6; }

      .welcome { padding: 22px 28px 12px; text-align: center; color: #1f2937; }
      .welcome strong { display: inline-block; font-weight: 700; }
      .welcome small { display: block; color: #5b5fe6; margin-top: 6px; }

      .status-strip { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; padding: 0 28px 16px; }
      .status-box {
        background: linear-gradient(180deg, rgba(255,255,255,.9), rgba(244,247,255,.88));
        border: 1px solid rgba(191, 219, 254, .7);
        border-radius: 18px;
        padding: 12px 15px;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.75);
      }
      .status-box small { display: block; color: #64748b; font-size: .74rem; text-transform: uppercase; letter-spacing: .12em; margin-bottom: 6px; }
      .status-box strong { display: block; color: #0f172a; font-size: .94rem; line-height: 1.35; }
      .status-box.empty strong { color: #94a3b8; font-weight: 700; }

      .steps { padding: 2px 28px 24px; }
      .wizard-header { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; margin-bottom: 22px; }
      .wizard-step {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 9px 16px;
        border-radius: 999px;
        border: 1px solid rgba(191, 219, 254, .9);
        background: rgba(248, 250, 252, .88);
        color: #475569;
        font-weight: 700;
        font-size: .9rem;
      }
      .wizard-step.active { background: #eff6ff; border-color: #c7d2fe; color: #1d4ed8; }
      .wizard-step span { display: inline-flex; width: 26px; height: 26px; border-radius: 999px; align-items: center; justify-content: center; background: #dbeafe; color: #1d4ed8; font-size: .8rem; }

      .step { margin-top: 12px; }
      .step-title { display: flex; align-items: center; gap: 10px; font-weight: 800; color: #111827; margin-bottom: 12px; font-size: 1rem; }
      .step.disabled .step-title { opacity: .55; }
      .step.disabled .cards-grid { filter: saturate(.75); }

      .cards-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 10px; }
      .card-option {
        position: relative;
        display: flex;
        gap: 10px;
        align-items: center;
        padding: 12px 14px;
        background: linear-gradient(180deg, rgba(255,255,255,.94), rgba(246,248,253,.94));
        border: 1px solid rgba(203, 213, 225, .72);
        border-radius: 18px;
        transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease, background .18s ease;
        cursor: pointer;
        min-height: 76px;
      }
      .card-option:hover { transform: translateY(-2px); box-shadow: 0 14px 30px rgba(148, 163, 184, .16); background: #ffffff; }
      .card-option.active {
        border-color: rgba(129, 140, 248, .52);
        background: linear-gradient(135deg, #f5f7ff, #eef4ff);
        box-shadow: 0 16px 30px rgba(129, 140, 248, .14);
      }
      .card-option:disabled { background: rgba(248,250,252,.78); cursor: not-allowed; opacity: .8; }
      .card-icon {
        min-width: 36px;
        min-height: 36px;
        border-radius: 12px;
        background: linear-gradient(135deg, #eef2ff, #e0e7ff);
        display: grid;
        place-items: center;
        color: #4f46e5;
        font-size: .96rem;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.75);
      }
      .card-title { display: block; font-weight: 800; color: #1e293b; margin-bottom: 4px; font-size: .92rem; }
      .card-sub { color: #64748b; font-size: .78rem; line-height: 1.3; }
      .selected-mark { position: absolute; right: 10px; top: 10px; color: #4f46e5; font-size: .96rem; }

      .empty-state,
      .loading-state { border: 1px dashed #cbd5e1; border-radius: 18px; padding: 22px 18px; text-align: center; color: #64748b; background: #f8fafc; }
      .loading-state { color: #405189; font-weight: 700; }
      .empty-state i,
      .loading-state i { display: block; font-size: 1.4rem; margin-bottom: 10px; }

      .hint {
        margin: 18px auto 0;
        max-width: 720px;
        text-align: center;
        color: #475569;
        font-size: .9rem;
        line-height: 1.7;
        padding: 12px 16px 0;
      }

      .actions { padding: 20px 28px 28px; display: flex; justify-content: center; border-top: 1px solid rgba(226, 232, 240, .78); }
      .btn-main {
        height: 52px;
        border-radius: 17px;
        border: 0;
        padding: 0 30px;
        background: linear-gradient(90deg, #334155 0%, #405189 45%, #5b67b8 100%);
        color: #ffffff;
        font-weight: 800;
        letter-spacing: .06em;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 16px 34px rgba(64, 81, 137, .24);
      }
      .btn-main:hover { transform: translateY(-1px); }
      .btn-main[disabled] { opacity: .55; cursor: not-allowed; }

      @media (max-width: 880px) {
        .admin-card { width: min(95vw, 860px); }
      }
      @media (max-width: 680px) {
        .brand-hero { padding: 28px 18px 22px; }
        .status-strip { grid-template-columns: 1fr; padding: 0 18px 18px; }
        .steps { padding: 0 18px 18px; }
        .card-option { min-height: auto; flex-direction: row; align-items: center; }
        .card-icon { min-width: 40px; min-height: 40px; }
        .actions { padding: 18px 18px 24px; }
        .btn-main { width: 100%; justify-content: center; }
      }
    </style>

    <div class="admin-shell">
      <div class="admin-card" id="phuyu_administrar" v-cloak>
        <div class="brand-hero">
          <img src="<?php echo base_url();?>/public/img/phuyu2024-blanco.png" alt="Phuyu" />
          <h2>Bienvenido</h2>
          <p>Selecciona tu sucursal, almacén y caja para empezar a administrar el sistema de forma segura y rápida.</p>
        </div>

        <div class="welcome">
          Hola <strong><?php echo $_SESSION["phuyu_usuario"];?></strong> — Espacio de <strong><?php echo $_SESSION["phuyu_empresa"];?></strong>
          <small>RUC: <?php echo $_SESSION["phuyu_ruc"];?></small>
        </div>

        <div class="status-strip">
          <div class="status-box" :class="{empty: !sucursalSeleccionada}">
            <small>Sucursal activa</small>
            <strong>{{ sucursalSeleccionada ? sucursalSeleccionada.descripcion : 'Pendiente por elegir' }}</strong>
          </div>
          <div class="status-box" :class="{empty: !almacenSeleccionado}">
            <small>Almacén activo</small>
            <strong>{{ almacenSeleccionado ? almacenSeleccionado.descripcion : 'Pendiente por elegir' }}</strong>
          </div>
          <div class="status-box" :class="{empty: !cajaSeleccionada}">
            <small>Caja activa</small>
            <strong>{{ cajaSeleccionada ? cajaSeleccionada.descripcion : 'Pendiente por elegir' }}</strong>
          </div>
        </div>

        <div class="steps">
          <div class="wizard-header" role="tablist" aria-label="Progreso de selección">
            <div class="wizard-step active"><span>1</span> Sucursal</div>
            <div class="wizard-step" :class="{'active': campos.codsucursal}"><span>2</span> Almacén</div>
            <div class="wizard-step" :class="{'active': campos.codalmacen}"><span>3</span> Caja</div>
          </div>

          <div class="step">
            <div class="step-title"><i class="bi bi-geo-alt"></i> Elige tu sucursal</div>
            <div class="cards-grid">
              <?php foreach ($info as $key => $value) { ?>
                <button type="button" class="card-option"
                        @click="campos.codsucursal='<?php echo $value["codsucursal"]?>'; campos.codalmacen=''; campos.codcaja=''; phuyu_resultados();"
                        :class="{'active': campos.codsucursal=='<?php echo $value["codsucursal"]?>'}"
                        :aria-pressed="campos.codsucursal=='<?php echo $value["codsucursal"]?>'">
                  <div class="card-icon"><i class="bi bi-buildings"></i></div>
                  <div>
                    <span class="card-title"><?php echo $value["descripcion"]?></span>
                    <span class="card-sub">Sucursal #<?php echo $value["codsucursal"]?></span>
                  </div>
                  <i v-if="campos.codsucursal=='<?php echo $value["codsucursal"]?>'" class="bi bi-check2-circle selected-mark"></i>
                </button>
              <?php } ?>
            </div>
          </div>

          <div class="step" :class="{disabled: !campos.codsucursal}">
            <div class="step-title"><i class="bi bi-box-seam"></i> Selecciona un almacén</div>
            <div v-if="estado === 1" class="loading-state">
              <i class="bi bi-hourglass-split"></i>
              Cargando almacenes y cajas de la sucursal...
            </div>
            <div v-else-if="campos.codsucursal && !almacenes.length" class="empty-state">
              <i class="bi bi-inboxes"></i>
              No hay almacenes disponibles para esta sucursal.
            </div>
            <div v-else class="cards-grid">
              <button type="button" class="card-option" v-for="dato in almacenes" :key="dato.codalmacen"
                      @click="seleccionarAlmacen(dato.codalmacen)"
                      :class="{'active': campos.codalmacen===dato.codalmacen}"
                      :disabled="!campos.codsucursal"
                      :aria-disabled="!campos.codsucursal">
                <div class="card-icon"><i class="bi bi-archive"></i></div>
                <div>
                  <span class="card-title">{{dato.descripcion}}</span>
                  <span class="card-sub">Código: {{dato.codalmacen}}</span>
                </div>
                <i v-if="campos.codalmacen===dato.codalmacen" class="bi bi-check2-circle selected-mark"></i>
              </button>
            </div>
          </div>

          <div class="step" :class="{disabled: !campos.codalmacen}">
            <div class="step-title"><i class="bi bi-cash-coin"></i> Selecciona una caja</div>
            <div v-if="estado === 1" class="loading-state">
              <i class="bi bi-hourglass-split"></i>
              Preparando cajas disponibles...
            </div>
            <div v-else-if="campos.codsucursal && !cajas.length" class="empty-state">
              <i class="bi bi-credit-card-2-front"></i>
              No hay cajas disponibles para esta sucursal.
            </div>
            <div v-else class="cards-grid">
              <button type="button" class="card-option" v-for="dato in cajas" :key="dato.codcaja"
                      @click="campos.codcaja=dato.codcaja"
                      :class="{'active': campos.codcaja===dato.codcaja}"
                      :disabled="!campos.codalmacen"
                      :aria-disabled="!campos.codalmacen">
                <div class="card-icon"><i class="bi bi-credit-card"></i></div>
                <div>
                  <span class="card-title">{{dato.descripcion}}</span>
                  <span class="card-sub">Código: {{dato.codcaja}}</span>
                </div>
                <i v-if="campos.codcaja===dato.codcaja" class="bi bi-check2-circle selected-mark"></i>
              </button>
            </div>
          </div>

          <p class="hint">{{ mensajeAyuda }}</p>
        </div>

        <div class="actions">
          <button type="button" class="btn-main" @click="administrar()" :disabled="estado==1 || estado==2 || !campos.codcaja">
            <i class="bi" :class="estado === 2 ? 'bi-arrow-repeat' : 'bi-arrow-right'"></i>
            {{ estado === 2 ? 'Ingresando...' : 'Administrar' }}
          </button>
        </div>

        <div class="d-none">
          <select v-model="campos.codsucursal" @change="phuyu_resultados">
            <option value="">Seleccione sucursal</option>
            <?php foreach ($info as $key => $value) { ?>
              <option value="<?php echo $value["codsucursal"]?>"><?php echo $value["descripcion"]?></option>
            <?php } ?>
          </select>
          <select v-model="campos.codalmacen">
            <option value="">Seleccione almacen</option>
            <option v-for="dato in almacenes" :value="dato.codalmacen">{{dato.descripcion}}</option>
          </select>
          <select v-model="campos.codcaja">
            <option value="">Seleccione caja</option>
            <option v-for="dato in cajas" :value="dato.codcaja">{{dato.descripcion}}</option>
          </select>
        </div>
      </div>
    </div>

    <?php include("phuyu_js.php"); ?>
    <script> var url = "<?php echo base_url();?>"; </script>
    <script src="<?php echo base_url();?>phuyu/phuyu_ministrar.js"></script>
  </body>
</html>
