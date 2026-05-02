<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Phuyu System</title>
    <meta name="description" content="Sistema comercial y facturacion electronica Phuyu System" />
    <meta name="author" content="Phuyu System" />
    <link rel="icon" href="<?php echo base_url(); ?>public/img/icono-phuyu.ico">

    <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />

    <script src="<?php echo base_url(); ?>public/plantilla_phuyu/js/layout.js"></script>

    <link rel="stylesheet" href="<?php echo base_url(); ?>public/plantilla_phuyu/css/bootstrap.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>public/plantilla_phuyu/css/icons.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>public/plantilla_phuyu/css/app.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>public/plantilla_phuyu/css/custom.min.css" />

    <link rel="stylesheet" href="<?php echo base_url(); ?>public/plantilla_phuyu/libs/jsvectormap/css/jsvectormap.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>public/plantilla_phuyu/libs/swiper/swiper-bundle.min.css" />

    <link rel="stylesheet" href="<?php echo base_url(); ?>public/css/vendor/select2.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>public/css/vendor/select2-bootstrap4.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>public/css/custom.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>public/css/main.css" />

    <style>
        html,
        body {
            min-height: 100%;
            font-family: 'Nunito Sans', sans-serif;
        }

        body {
            background:
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.10), transparent 28%),
                linear-gradient(180deg, #f6f9fc 0%, #eef3f8 100%);
        }

        .navbar-brand-box {
            padding: 0 1rem;
        }

        html[data-layout="vertical"] #page-topbar .navbar-header,
        html[data-layout="semibox"] #page-topbar .navbar-header {
            padding-left: calc(var(--vz-grid-gutter-width) * .5);
        }

        html[data-layout="vertical"] #page-topbar .navbar-header > .d-flex.align-items-center,
        html[data-layout="semibox"] #page-topbar .navbar-header > .d-flex.align-items-center {
            gap: .5rem;
        }

        .phuyu-brand-logo {
            max-height: 40px;
            width: auto;
        }

        .phuyu-brand-isotype {
            width: 38px;
            height: 38px;
            object-fit: contain;
            border-radius: 12px;
        }

        .phuyu-brand-full {
            max-width: 150px;
            object-fit: contain;
        }

        .app-menu .navbar-brand-box .logo-sm {
            line-height: 70px;
        }

        .app-menu .navbar-brand-box {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .app-menu .navbar-brand-box .logo {
            line-height: 70px;
            text-align: center;
            width: 100%;
        }

        html[data-sidebar-size="sm"] .app-menu .navbar-brand-box .phuyu-brand-isotype,
        html[data-sidebar-size="sm-hover"] .app-menu .navbar-brand-box .phuyu-brand-isotype {
            display: inline-block !important;
            width: 34px !important;
            height: 34px !important;
            max-height: 34px !important;
        }

        html[data-sidebar-size="sm"] .app-menu .navbar-brand-box .logo-sm,
        html[data-sidebar-size="sm-hover"] .app-menu .navbar-brand-box .logo-sm {
            display: inline-block !important;
        }

        html[data-sidebar-size="sm"] .app-menu .navbar-brand-box .logo-lg,
        html[data-sidebar-size="sm-hover"] .app-menu .navbar-brand-box .logo-lg {
            display: none !important;
        }

        .app-menu .navbar-nav .nav-link {
            text-transform: capitalize;
        }

        .app-menu .navbar-nav .nav-link.active,
        .app-menu .navbar-nav .nav-link[aria-expanded="true"] {
            color: #fff;
            background: rgba(255, 255, 255, 0.14);
        }

        .app-menu .navbar-nav .nav-link[aria-expanded="true"] i,
        .app-menu .navbar-nav .nav-link[aria-expanded="true"] span,
        .app-menu .navbar-nav .nav-link.active i,
        .app-menu .navbar-nav .nav-link.active span {
            color: #fff;
        }

        .app-menu .navbar-nav .menu-dropdown {
            transition: height 0.2s ease;
        }

        .app-menu .navbar-nav .menu-link.collapsed[aria-expanded="false"]::after {
            transform: rotate(0deg);
        }

        .app-menu .navbar-nav .menu-link[aria-expanded="true"]::after {
            transform: rotate(90deg);
        }

        #topnav-hamburger-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .phuyu-topbar-isotype {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: rgba(64, 81, 137, 0.08);
            transition: background .2s ease, transform .2s ease;
        }

        .phuyu-topbar-isotype:hover {
            background: rgba(64, 81, 137, 0.14);
            transform: translateY(-1px);
        }

        .phuyu-topbar-isotype img {
            width: 30px;
            height: 30px;
            object-fit: contain;
        }

        .vertical-overlay {
            display: none;
        }

        .vertical-overlay.active {
            display: block;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.35);
            z-index: 1001;
        }

        body.vertical-sidebar-enable {
            overflow: hidden;
        }

        .phuyu-system-picker {
            min-width: 220px;
        }

        .phuyu-system-picker .form-select {
            border-color: rgba(64, 81, 137, 0.18);
            min-height: 38px;
        }

        .phuyu-page-shell {
            min-height: calc(100vh - 70px);
        }

        .phuyu-module-stage {
            background: rgba(255, 255, 255, 0.88);
            border: 1px solid rgba(64, 81, 137, 0.10);
            border-radius: 1rem;
            box-shadow: 0 10px 35px rgba(15, 23, 42, 0.06);
            min-height: calc(100vh - 170px);
            padding: 1rem;
        }

        .phuyu-custom-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.48);
            backdrop-filter: blur(3px);
            z-index: 1060;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .phuyu-custom-modal {
            width: min(980px, 100%);
            max-height: calc(100vh - 2rem);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            background: #fff;
            border-radius: 1.2rem;
            box-shadow: 0 24px 80px rgba(15, 23, 42, 0.30);
        }

        .phuyu-custom-modal-header,
        .phuyu-custom-modal-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.25rem;
            background: #f8fafc;
        }

        .phuyu-custom-modal-header {
            border-bottom: 1px solid rgba(64, 81, 137, 0.10);
        }

        .phuyu-custom-modal-body {
            padding: 1.25rem;
            overflow: auto;
        }

        .phuyu-custom-modal-footer {
            border-top: 1px solid rgba(64, 81, 137, 0.10);
        }

        .phuyu-custom-modal .btn-close {
            box-shadow: none;
        }

        .dashboard-wrap {
            animation: fadeInUp 0.4s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dashboard-wrap .card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .dashboard-wrap .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 1rem 2rem rgba(0,0,0,0.08) !important;
        }

        .ranking-medal {
            width: 28px;
            text-align: center;
            font-weight: bold;
        }

        .dashboard-wrap .list-group-item {
            transition: background-color 0.15s ease;
        }

        .dashboard-wrap .list-group-item:hover {
            background-color: #f8fafc;
        }

        .kpi-value-number {
            font-size: calc(1.4rem + 0.6vw);
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        @media (min-width: 1200px) {
            .kpi-value-number {
                font-size: 2rem;
            }
        }

        .bg-gradient-soft {
            background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
        }

        .modal-finventa-card {
            border: 0;
            border-radius: 1.25rem;
            overflow: hidden;
            background: linear-gradient(180deg, #ffffff 0%, #f8faff 100%);
            box-shadow: 0 30px 80px rgba(15, 23, 42, 0.28);
        }

        .modal-finventa-header {
            border: 0;
            padding: 1.15rem 1.35rem;
            color: #fff;
            background: linear-gradient(135deg, #405189 0%, #5569a8 100%);
            align-items: flex-start;
        }

        .modal-finventa-header .modal-title {
            letter-spacing: .3px;
        }

        .modal-finventa-body {
            padding: 1.25rem;
            background: radial-gradient(circle at top right, rgba(64, 81, 137, 0.05), transparent 35%),
                        radial-gradient(circle at left bottom, rgba(64, 81, 137, 0.04), transparent 30%),
                        #fff;
        }

        .finventa-block {
            background: #fff;
            border: 1px solid rgba(64, 81, 137, 0.12);
            border-radius: 1rem;
            padding: 1rem;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
        }

        .finventa-section-title {
            display: flex;
            align-items: center;
            gap: .65rem;
            margin-bottom: .9rem;
            padding-bottom: .65rem;
            border-bottom: 1px dashed rgba(64, 81, 137, 0.18);
            font-weight: 700;
            color: #405189;
        }

        .finventa-section-title i {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(64, 81, 137, 0.10);
        }

        .finventa-credit {
            background: linear-gradient(135deg, rgba(64, 81, 137, 0.05), rgba(64, 81, 137, 0.01));
        }

        .finventa-payment-visual {
            min-height: 128px;
            border-radius: 1rem;
            background: linear-gradient(135deg, #405189 0%, #5569a8 100%);
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: .6rem;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.08);
        }

        .finventa-payment-visual i {
            font-size: 2rem;
            opacity: .95;
        }

        .finventa-payment-visual span {
            font-weight: 700;
            letter-spacing: .3px;
            text-align: center;
        }

        .finventa-divider {
            margin: 1rem 0;
            opacity: .18;
        }

        .finventa-table {
            border: 1px solid rgba(64, 81, 137, 0.12);
            border-radius: .9rem;
            overflow: hidden;
            background: #fff;
        }

        .finventa-table table {
            margin-bottom: 0;
        }

        .finventa-table thead th {
            background: #405189;
            color: #fff;
            font-weight: 600;
            border: 0;
            white-space: nowrap;
        }

        .finventa-table tbody td {
            vertical-align: middle;
        }

        .finventa-table .form-control,
        .finventa-table .form-select {
            min-width: 120px;
        }

        .finventa-summary {
            display: flex;
            flex-wrap: wrap;
            gap: .75rem;
            justify-content: flex-end;
            margin-top: 1rem;
        }

        .finventa-summary .btn {
            border-radius: .8rem;
            padding-inline: 1rem;
        }

        .finventa-footer {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(64, 81, 137, 0.10);
        }

        .finventa-footer .btn {
            border-radius: .9rem;
            min-height: 46px;
            font-weight: 700;
            letter-spacing: .2px;
        }

        .phuyu-topbar-user .header-profile-user {
            background: #fff;
            object-fit: cover;
        }

        .compose {
            display: none;
            position: fixed;
            top: 84px;
            right: 1rem;
            width: min(560px, calc(100vw - 2rem));
            height: calc(100vh - 100px);
            background: #fff;
            border: 1px solid rgba(64, 81, 137, 0.12);
            border-radius: 1rem;
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.18);
            z-index: 1052;
            overflow: hidden;
        }

        .compose .offcanvas-header {
            border-bottom: 1px solid rgba(64, 81, 137, 0.10);
            padding: 1rem 1.25rem;
            background: linear-gradient(135deg, #fff 0%, #f6faff 100%);
        }

        .compose .compose-body {
            height: calc(100% - 72px);
            overflow-y: auto;
            padding: 1rem;
            background: #fff;
        }

        .settings-buttons-container {
            position: fixed;
            right: 1rem;
            bottom: 1rem;
            z-index: 1051;
        }

        .settings-buttons-container .settings-button {
            width: 52px;
            height: 52px;
            border-radius: 999px;
            box-shadow: 0 10px 30px rgba(64, 81, 137, 0.28);
        }

        .overlay-spinner {
            position: relative;
            pointer-events: none;
        }

        .overlay-spinner::before {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.7);
            border-radius: inherit;
            z-index: 10;
        }

        .overlay-spinner::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 2rem;
            height: 2rem;
            margin-top: -1rem;
            margin-left: -1rem;
            border: 3px solid rgba(64, 81, 137, 0.20);
            border-top-color: var(--vz-primary);
            border-radius: 50%;
            animation: phuyu-spin .8s linear infinite;
            z-index: 11;
        }

        @keyframes phuyu-spin {
            to {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 991.98px) {
            .phuyu-system-picker {
                min-width: 180px;
            }

            .phuyu-module-stage {
                min-height: auto;
            }
        }

        @media (max-width: 991.98px) {
            .app-menu {
                transform: translateX(-100%);
                transition: transform .25s ease;
                visibility: hidden;
                z-index: 1003;
            }

            body.vertical-sidebar-enable .app-menu {
                transform: translateX(0);
                visibility: visible;
            }
        }

        @media (max-width: 767.98px) {

            .compose {
                top: 70px;
                right: 0.75rem;
                width: calc(100vw - 1.5rem);
                height: calc(100vh - 86px);
            }

            .phuyu-system-picker {
                min-width: 0;
                width: 100%;
            }
        }
    </style>
</head>
