<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Phuyu System | Iniciar Sesion</title>
    <link rel="icon" href="<?php echo base_url(); ?>public/img/icono-phuyu.ico">

    <link rel="stylesheet" href="<?php echo base_url(); ?>public/plantilla_phuyu/css/bootstrap.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>public/plantilla_phuyu/css/app.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>public/plantilla_phuyu/css/icons.min.css" />
    <script src="<?php echo base_url(); ?>public/js/base/loader.js"></script>

    <style>
        html,
        body {
            height: 100%;
        }

        body {
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", "Helvetica Neue", Arial, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(56, 189, 248, 0.18), transparent 26%),
                radial-gradient(circle at bottom right, rgba(99, 102, 241, 0.18), transparent 24%),
                linear-gradient(135deg, #eef6ff 0%, #f8fbff 40%, #e9eefb 100%);
            overflow-x: hidden;
        }

        .login-wrap {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .login-card {
            width: min(460px, 94vw);
            position: relative;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(148, 163, 184, 0.20);
            border-radius: 24px;
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.14);
            padding: 34px 28px 28px;
            color: #111827;
            backdrop-filter: blur(12px);
        }

        .login-card::before {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: 24px;
            padding: 1px;
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.45), rgba(99, 102, 241, 0.18), rgba(255, 255, 255, 0.55));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            mask-composite: exclude;
            pointer-events: none;
        }

        .brand {
            text-align: center;
            margin-bottom: 10px;
        }

        .brand img {
            max-width: 230px;
            height: auto;
            filter: drop-shadow(0 12px 24px rgba(99, 102, 241, 0.16));
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            margin: 0 auto 14px;
            padding: .42rem .9rem;
            border-radius: 999px;
            background: rgba(56, 189, 248, 0.10);
            color: #2563eb;
            font-weight: 700;
            font-size: .82rem;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .title {
            text-align: center;
            color: #0f172a;
            font-weight: 800;
            font-size: 1.55rem;
            margin: 6px 0 10px;
            letter-spacing: -.02em;
        }

        .subtitle {
            text-align: center;
            color: #64748b;
            font-size: .98rem;
            margin-bottom: 22px;
        }

        .message-box {
            display: none;
            margin-bottom: 16px;
            padding: .85rem 1rem;
            border-radius: 14px;
            background: #fff1f2;
            border: 1px solid #fecdd3;
            color: #be123c;
            font-size: .92rem;
            font-weight: 700;
        }

        .field {
            position: relative;
            margin-bottom: 16px;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 2.75rem;
            color: #64748b;
            font-size: 1.05rem;
        }

        .field .form-control {
            height: 52px;
            padding-left: 44px;
            padding-right: 44px;
            color: #0f172a;
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid #cbd5e1;
            border-radius: 14px;
            box-shadow: none;
        }

        .field .form-control::placeholder {
            color: #94a3b8;
        }

        .field .form-control:focus {
            border-color: #38bdf8;
            box-shadow: 0 0 0 .22rem rgba(56, 189, 248, 0.14);
        }

        .toggle-pass {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            width: 36px;
            height: 36px;
            border: 0;
            border-radius: 10px;
            background: transparent;
            color: #64748b;
        }

        .toggle-pass:hover {
            background: #f8fafc;
            color: #0f172a;
        }

        .actions {
            display: flex;
            gap: 10px;
            margin-top: 18px;
        }

        .btn-main,
        .btn-reset {
            height: 52px;
            border-radius: 14px;
            font-weight: 800;
            transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
        }

        .btn-main {
            flex: 1;
            border: 0;
            background: linear-gradient(90deg, #0ea5e9 0%, #6366f1 100%);
            color: #fff;
            box-shadow: 0 12px 28px rgba(99, 102, 241, 0.24);
        }

        .btn-main:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 34px rgba(99, 102, 241, 0.28);
        }

        .btn-main:disabled {
            opacity: .72;
            cursor: not-allowed;
        }

        .btn-reset {
            width: 128px;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #334155;
        }

        .btn-reset:hover {
            background: #f8fafc;
        }

        .links {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-top: 16px;
            font-size: .92rem;
        }

        .links a {
            color: #64748b;
            text-decoration: none;
        }

        .links a:hover {
            color: #0f172a;
        }

        @media (max-width: 576px) {
            .login-card {
                padding: 28px 20px 22px;
            }

            .actions {
                flex-direction: column-reverse;
            }

            .btn-reset {
                width: 100%;
            }

            .links {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>

    <style>
        .auth-bg {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", "Helvetica Neue", Arial, sans-serif;
        }

        .auth-container {
            max-width: 1080px;
        }

        .auth-shell {
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            gap: 1.25rem;
            align-items: stretch;
        }

        .auth-aside {
            border-radius: 1.25rem;
            padding: 2rem;
            color: #ffffff;
            background:
                linear-gradient(160deg, rgba(88, 28, 135, 0.95) 0%, rgba(30, 41, 82, 0.95) 100%);
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.2);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .auth-aside-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            background: rgba(255, 255, 255, 0.16);
        }

        .auth-aside-title {
            margin: 1.2rem 0 0.25rem;
            font-size: clamp(2.1rem, 4.8vw, 3.15rem);
            line-height: 1.05;
            font-weight: 800;
            color: #ffffff;
            text-shadow: 0 4px 16px rgba(0, 0, 0, 0.28);
            letter-spacing: -0.02em;
        }

        .auth-aside-subtitle {
            margin: 0 0 0.85rem;
            font-size: clamp(1rem, 1.8vw, 1.25rem);
            line-height: 1.25;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.95);
        }

        .auth-aside-text {
            margin: 0;
            max-width: 32ch;
            color: rgba(236, 253, 245, 0.9);
            font-size: 0.97rem;
            line-height: 1.6;
        }

        .auth-aside-meta {
            margin-top: 1.3rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            font-weight: 600;
            color: #ffffff;
        }

        .auth-aside-points {
            margin: 1rem 0 0;
            padding: 0;
            list-style: none;
            display: grid;
            gap: 0.55rem;
            color: rgba(255, 255, 255, 0.96);
            font-size: 0.88rem;
        }

        .auth-aside-points li {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .auth-aside-points li i {
            color: #e0e7ff;
            font-size: 0.95rem;
        }

        .auth-aside-quote {
            margin-top: auto;
            text-align: center;
            margin-bottom: 0;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.24);
            font-size: 0.95rem;
            font-weight: 500;
            font-style: italic;
            color: #ffffff;
            letter-spacing: 0.01em;
            text-wrap: balance;
        }

        .auth-aside-quote-author {
            margin: 0.35rem 0 0;
            text-align: center;
            font-size: 0.78rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.82);
        }

        .auth-form-wrap {
            display: flex;
        }

        .glass-card {
            width: 100%;
            background: rgba(255, 255, 255, 0.93);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            box-shadow: 0 22px 50px rgba(15, 23, 42, 0.14) !important;
            animation: fadeIn 0.8s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .brand-wrap {
            width: 84px;
            height: 84px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.15);
            border: 2px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
        }

        .brand-logo {
            max-width: 58px;
            max-height: 58px;
            object-fit: contain;
            filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.08));
        }

        .custom-group .input-group-text {
            color: #cbd5e1;
            transition: all 0.3s ease;
            background: white !important;
        }

        .custom-group .form-control,
        .custom-group .input-group-text {
            min-height: 56px;
            border: 1.5px solid #e2e8f0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .custom-group .form-control:focus {
            box-shadow: none !important;
            border-color: #7c3aed !important;
            background: #f5f3ff !important;
        }

        .custom-group .form-control:focus + .input-group-text,
        .custom-group .input-group-text:has(+ .form-control:focus) {
            border-color: #7c3aed !important;
            color: #7c3aed;
            background: #faf5ff !important;
        }

        .icon-animate {
            transition: transform 0.2s ease, color 0.2s ease;
        }

        .input-animated:hover .icon-animate {
            transform: scale(1.05);
        }

        .input-wrapper {
            position: relative;
            animation: slideUp 0.6s ease-out backwards;
        }

        .input-wrapper:nth-child(1) {
            animation-delay: 0.1s;
        }

        .input-wrapper:nth-child(2) {
            animation-delay: 0.2s;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .badge-check {
            font-size: 0.85rem;
            animation: checkPulse 0.4s ease-out;
        }

        @keyframes checkPulse {
            0% {
                transform: scale(0);
            }
            50% {
                transform: scale(1.2);
            }
            100% {
                transform: scale(1);
            }
        }

        .btn-eye-animate {
            transition: all 0.2s ease;
        }

        .btn-eye-animate:hover {
            transform: scale(1.1);
            color: #7c3aed !important;
        }

        .btn-eye-animate:active {
            transform: scale(0.95);
        }

        .alert-animate {
            animation: alertSlide 0.3s ease-out;
        }

        @keyframes alertSlide {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .btn-toggle-eye {
            cursor: pointer;
        }

        .forgot-link {
            color: #7c3aed;
            font-weight: 600;
        }

        .forgot-link:hover {
            color: #6d28d9;
        }

        .btn-login {
            position: relative;
            min-height: 52px;
            font-weight: 600;
            color: #fff !important;
            background: linear-gradient(135deg, #7c3aed 0%, #1e2952 100%);
            border: none !important;
            box-shadow: 0 12px 24px rgba(124, 58, 237, 0.28);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .btn-login::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn-login:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-login:hover,
        .btn-login:focus,
        .btn-login:active {
            color: #fff !important;
            transform: translateY(-3px);
            box-shadow: 0 18px 40px rgba(124, 58, 237, 0.35);
            background: linear-gradient(135deg, #6d28d9 0%, #1e293b 100%);
        }

        .btn-login:disabled {
            opacity: 0.85;
            cursor: not-allowed;
        }

        .btn-login-animated {
            position: relative;
            z-index: 1;
        }

        .btn-login-spinner {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        .btn-login-label {
            display: inline-block;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-check-input:focus,
        .btn:focus {
            box-shadow: 0 0 0 0.2rem rgba(2, 132, 199, 0.18) !important;
        }

        .fade-enter-active,
        .fade-leave-active {
            transition: opacity 0.2s ease;
        }
        .fade-enter-from,
        .fade-leave-to {
            opacity: 0;
        }

        @media (max-width: 575.98px) {
            .auth-shell {
                grid-template-columns: 1fr;
            }

            .auth-aside {
                display: none;
            }

            .brand-wrap {
                width: 74px;
                height: 74px;
            }

            .brand-logo {
                max-width: 50px;
                max-height: 50px;
            }
        }

        @media (max-width: 991.98px) {
            .auth-shell {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body class="auth-bg min-vh-100 d-flex align-items-center py-4 py-sm-5" style="background-image: 
            radial-gradient(circle at 20% 50%, rgba(88, 28, 135, 0.35), transparent 50%),
            radial-gradient(circle at 80% 80%, rgba(124, 58, 237, 0.28), transparent 52%),
            linear-gradient(135deg, rgba(15, 23, 42, 0.72), rgba(88, 28, 135, 0.54)),
            url('<?php echo base_url(); ?>public/plantilla_phuyu/images/auth-one-bg.jpg'); 
            background-size: cover; background-position: center; background-repeat: no-repeat;">
    <div class="container auth-container">
        <div class="auth-shell">
            <section class="auth-aside">
                <div class="auth-aside-badge">Phuyu System</div>
                <h1 class="auth-aside-title">Facturación Electrónica</h1>
                <h2 class="auth-aside-subtitle">Emisión segura y cumplimiento normativo</h2>
                <p class="auth-aside-text">Simplifica. Cumple. Optimiza.</p>
                <div class="auth-aside-meta">
                    <i class="ri-star-line"></i>
                    Comprobantes electrónicos válidos
                </div>
                <ul class="auth-aside-points">
                    <li><i class="ri-file-pdf-line"></i><span>Comprobantes válidos</span></li>
                    <li><i class="ri-money-dollar-circle-line"></i><span>Ingresos y gastos</span></li>
                    <li><i class="ri-line-chart-line"></i><span>Reportes financieros</span></li>
                    <li><i class="ri-shield-check-line"></i><span>Seguridad normativa</span></li>
                </ul>
                <p class="auth-aside-quote">
                    “Gracias por ser parte de la nueva era que nos inspira Phuyu System.”
                </p>
                <p class="auth-aside-quote-author">— Phuyu Facturación</p>
            </section>

            <section class="auth-form-wrap">
                <div class="card border-0 rounded-4 shadow-lg glass-card overflow-hidden">
                    <!-- Header -->
                    <div class="card-header bg-transparent border-0 text-center pt-4 pb-2">
                        <div class="brand-wrap mx-auto mb-3">
                            <img src="<?php echo base_url(); ?>/public/img/logo_completo.png" alt="Phuyu System" class="brand-logo" />
                        </div>
                        <h4 class="mb-1 fw-bold text-dark">Iniciar sesión</h4>
                        <p class="text-muted mb-0 small">Accede de forma segura a tu panel de trabajo</p>
                    </div>

                    <!-- Body -->
                    <div class="card-body px-4 px-sm-5 pb-4 pt-3">
                        <form id="form_login" onsubmit="return phuyu_login()" autocomplete="off" novalidate>
                            <!-- Usuario -->
                            <div class="mb-3 input-wrapper">
                                <label for="phuyu_usuario" class="form-label fw-semibold d-flex align-items-center justify-content-between">
                                    Usuario
                                    <span class="badge badge-check" id="badge-usuario" style="display: none;"><i class="ri-checkbox-circle-fill text-success"></i></span>
                                </label>
                                <div class="input-group input-group-lg custom-group input-animated">
                                    <span class="input-group-text bg-white border-end-0 icon-animate">
                                        <i class="ri-user-line"></i>
                                    </span>
                                    <input
                                        id="phuyu_usuario"
                                        type="text"
                                        class="form-control border-start-0 input-field"
                                        placeholder="Ingresa tu usuario"
                                        required autofocus
                                    />
                                </div>
                                <small class="text-muted d-block mt-2">Ejemplo: usuario o correo registrado</small>
                            </div>

                            <!-- Contraseña -->
                            <div class="mb-2 input-wrapper">
                                <label for="phuyu_clave" class="form-label fw-semibold d-flex align-items-center justify-content-between">
                                    Contraseña
                                    <span class="badge badge-check" id="badge-clave" style="display: none;"><i class="ri-checkbox-circle-fill text-success"></i></span>
                                </label>
                                <div class="input-group input-group-lg custom-group input-animated">
                                    <span class="input-group-text bg-white border-end-0 icon-animate">
                                        <i class="ri-lock-2-line"></i>
                                    </span>
                                    <input
                                        id="phuyu_clave"
                                        type="password"
                                        class="form-control border-start-0 border-end-0 input-field"
                                        placeholder="Ingresa tu contraseña"
                                        required
                                    />
                                    <button
                                        class="input-group-text bg-white btn-toggle-eye border-start-0 btn-eye-animate"
                                        type="button"
                                        id="btnTogglePass"
                                        aria-label="Mostrar u ocultar contraseña"
                                    >
                                        <i class="ri-eye-line"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- extras -->
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-3 mb-3">
                                <div class="form-check">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        id="auth-remember-check"
                                    />
                                    <label class="form-check-label text-muted small" for="auth-remember-check">
                                        Recordarme
                                    </label>
                                </div>

                                <a
                                    href="javascript:void(0);"
                                    class="small text-decoration-none forgot-link"
                                >
                                    ¿Olvidaste tu contraseña?
                                </a>
                            </div>

                            <!-- alert -->
                            <div id="mensaje" class="alert py-2 small d-none alert-animate" role="alert">
                                Usuario o clave incorrectos.
                            </div>

                            <!-- botón -->
                            <div class="d-grid mt-4">
                                <button
                                    class="btn btn-lg rounded-3 btn-login btn-login-animated"
                                    type="submit"
                                    id="iniciar_sesion"
                                >
                                    <span class="btn-login-spinner" id="spinner-login" style="display: none;">
                                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                    </span>
                                    <span class="btn-login-label" id="btn-label">
                                        Iniciar sesión
                                    </span>
                                </button>
                            </div>

                            <div class="text-center mt-4">
                                <span class="text-muted small">Phuyu • Sistema de Facturación Electrónica</span>
                            </div>
                        </form>
                    </div>

                    <!-- Footer -->
                    <div class="card-footer bg-transparent border-0 text-center pb-4 pt-0">
                        <small class="text-muted">© 2026 Phuyu System</small>
                    </div>
                </div>
            </section>
        </div>
    </div>
    <script src="<?php echo base_url(); ?>public/js/vendor/jquery-3.5.1.min.js"></script>
    <script src="<?php echo base_url(); ?>public/js/vendor/bootstrap.bundle.min.js"></script>
    <script>var url = "<?php echo base_url(); ?>";</script>
    <script src="<?php echo base_url(); ?>phuyu/phuyu_login.js"></script>

    <script>
        (function () {
            const usuarioInput = document.getElementById('phuyu_usuario');
            const claveInput = document.getElementById('phuyu_clave');
            const btnToggle = document.getElementById('btnTogglePass');
            const btnLogin = document.getElementById('iniciar_sesion');
            const badgeUsuario = document.getElementById('badge-usuario');
            const badgeClave = document.getElementById('badge-clave');

            // Toggle Password
            if (claveInput && btnToggle) {
                btnToggle.addEventListener('click', function (e) {
                    e.preventDefault();
                    const show = claveInput.type === 'password';
                    claveInput.type = show ? 'text' : 'password';
                    btnToggle.innerHTML = show
                        ? '<i class="ri-eye-off-line"></i>'
                        : '<i class="ri-eye-line"></i>';
                });
            }

            // Validación en tiempo real
            if (usuarioInput) {
                usuarioInput.addEventListener('input', function () {
                    if (this.value.trim().length > 0) {
                        badgeUsuario.style.display = 'inline-block';
                    } else {
                        badgeUsuario.style.display = 'none';
                    }
                });
            }

            if (claveInput) {
                claveInput.addEventListener('input', function () {
                    if (this.value.length > 0) {
                        badgeClave.style.display = 'inline-block';
                    } else {
                        badgeClave.style.display = 'none';
                    }
                });
            }

            // Loading state del botón
            if (btnLogin) {
                const originalOnSubmit = window.phuyu_login;
                window.phuyu_login = function () {
                    if (typeof originalOnSubmit === 'function') {
                        return originalOnSubmit();
                    }

                    return false;
                };
            }

            // Efecto ripple en inputs
            document.querySelectorAll('.input-animated').forEach(input => {
                input.addEventListener('click', function () {
                    this.style.boxShadow = '0 0 0 0.5rem rgba(124, 58, 237, 0.15)';
                    setTimeout(() => {
                        this.style.boxShadow = '';
                    }, 400);
                });
            });
        })();
    </script>
</body>
</html>
