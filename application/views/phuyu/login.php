<!DOCTYPE html>
<html lang="es" data-color="dark-blue" data-navcolor="dark">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>phuyu Soft | Iniciar Sesión</title>

    <!-- Base (mantengo tus rutas y libs principales) -->
    <link rel="stylesheet" href="<?php echo base_url();?>public/css/vendor/bootstrap.min.css" />
    <link rel="stylesheet" href="<?php echo base_url();?>public/css/styles.css" />
    <link rel="stylesheet" href="<?php echo base_url();?>public/css/main.css" />
    <script src="<?php echo base_url();?>public/js/base/loader.js"></script>

<style>
  html, body { height: 100%; }
  body { margin: 0; }

  /* Fondo claro degradado */
  .login-wrap {
    min-height: 100vh;
    display: grid;
    place-items: center;
    padding: 24px;
    background: linear-gradient(135deg, #f8fafc 0%, #e0f2fe 100%);
  }

  /* Card clara */
  .login-card {
    width: min(420px, 92vw);
    position: relative;
    z-index: 1;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 20px;
    box-shadow: 0 20px 50px rgba(0,0,0,.1);
    padding: 28px 24px;
    color: #111827;
  }

  .brand { text-align:center; margin-bottom: 8px; }
  .brand img { max-width: 220px; height: auto; }

  .title {
    text-align:center;
    color:#1f2937;
    font-weight:800;
    font-size:1.2rem;
    margin: 8px 0 16px;
    letter-spacing:.2px;
  }
  .subtitle {
    text-align:center;
    color:#4b5563;
    font-size:.925rem;
    margin-bottom:16px;
  }

  /* Campos */
  .field { position: relative; margin-bottom: 14px; }
  .field .input-icon {
    position:absolute; left:12px; top:50%;
    transform:translateY(-50%);
    opacity:.7; color:#6b7280;
  }
  .field .form-control {
    height: 48px;
    padding-left: 42px;
    color:#111827;
    background: #ffffff;
    border:1px solid #d1d5db;
    border-radius:12px;
  }
  .field .form-control::placeholder { color:#9ca3af; }
  .field .form-control:focus {
    outline:none;
    border-color:#38bdf8;
    box-shadow: 0 0 0 .2rem rgba(56,189,248,.15);
  }

  /* Toggle password */
  .toggle-pass {
    position:absolute; right:8px; top:50%;
    transform:translateY(-50%);
    background:transparent; border:0;
    color:#6b7280; opacity:.8; padding:6px 8px;
  }
  .toggle-pass:hover { opacity:1; }

  /* Acciones */
  .actions { display:flex; gap:10px; margin-top: 16px; }
  .btn-main {
    flex:1; height:48px; border-radius:12px;
    border:1px solid transparent;
    background: linear-gradient(90deg,#38bdf8 0%, #6366f1 100%);
    color:#ffffff; font-weight:700;
    display:flex; align-items:center; justify-content:center; gap:.5rem;
    transition: transform .15s ease, box-shadow .15s ease;
    box-shadow: 0 8px 20px rgba(99,102,241,.25);
  }
  .btn-main:hover {
    transform: translateY(-1px);
    box-shadow: 0 12px 28px rgba(99,102,241,.35);
  }
  .btn-reset {
    width: 120px; height:48px; border-radius:12px;
    background:#f3f4f6; color:#374151;
    border:1px solid #d1d5db;
  }
  .btn-reset:hover { background:#e5e7eb; }

  .links {
    display:flex; justify-content:space-between;
    margin-top:12px; font-size:.9rem;
  }
  .links a { color:#6b7280; text-decoration:none; }
  .links a:hover{ color:#111827; }
</style>

  </head>

  <body>
    <div id="root" class="login-wrap">
      <div class="login-card">
        <div class="brand">
          <img src="<?php echo base_url();?>/public/img/logo_completo.png" alt="phuyu Soft" />
        </div>
        <div class="title">Bienvenido</div>
        <div class="subtitle">Accede con tu usuario y clave</div>

        <!-- Mantengo EXACTAMENTE tus IDs y onsubmit -->
        <form id="form_login" onsubmit="return phuyu_login()" autocomplete="off">
          <div class="field">
            <i data-acorn-icon="user" class="input-icon"></i>
            <input type="text" class="form-control" id="phuyu_usuario" placeholder="USUARIO" required autofocus>
          </div>
          <div class="field">
            <i data-acorn-icon="lock-off" class="input-icon"></i>
            <input type="password" class="form-control" id="phuyu_clave" placeholder="CLAVE" required>
            <button type="button" class="toggle-pass" id="btnTogglePass" aria-label="Mostrar/Ocultar contraseña">
              <i data-acorn-icon="eye"></i>
            </button>
          </div>
          <div class="actions">
            <button type="reset" class="btn-reset">Limpiar</button>
            <button type="submit" class="btn-main" id="iniciar_sesion">
              <i data-acorn-icon="arrow-right"></i>
              <span>INGRESAR</span>
            </button>
          </div>
          <div class="links">
            <a href="#">¿Olvidaste tu contraseña?</a>
            <a href="#">Soporte</a>
          </div>
        </form>
      </div>
    </div>

    <?php include("phuyu_js.php"); ?>
    <script> var url = "<?php echo base_url();?>"; </script>
    <script src="<?php echo base_url();?>phuyu/phuyu_login.js"></script>

    <script>
      // Toggle password y refresco de íconos Acorn
      (function(){
        const input = document.getElementById('phuyu_clave');
        const btn = document.getElementById('btnTogglePass');
        if (input && btn) {
          btn.addEventListener('click', function(){
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.innerHTML = show ? '<i data-acorn-icon="eye-off"></i>' : '<i data-acorn-icon="eye"></i>';
            if (window.csicons?.replace) window.csicons.replace();
          });
        }
        if (window.csicons?.replace) window.csicons.replace();
      })();
    </script>
  </body>
</html>
