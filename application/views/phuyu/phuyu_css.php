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

        .app-menu {
            height: 100vh;
            overflow: hidden;
        }

        .app-menu #scrollbar {
            height: calc(100vh - 70px);
            max-height: calc(100vh - 70px);
            overflow-y: auto;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: transparent transparent;
            transition: scrollbar-color .2s ease;
        }

        .app-menu:hover #scrollbar,
        .app-menu #scrollbar:focus-within {
            scrollbar-color: rgba(255, 255, 255, .34) transparent;
        }

        .app-menu #scrollbar .container-fluid {
            padding-bottom: 1.75rem;
        }

        .app-menu #scrollbar::-webkit-scrollbar {
            width: 8px;
        }

        .app-menu #scrollbar::-webkit-scrollbar-thumb {
            background: transparent;
            border: 2px solid transparent;
            border-radius: 999px;
            background-clip: padding-box;
            min-height: 42px;
            transition: background .2s ease;
        }

        .app-menu:hover #scrollbar::-webkit-scrollbar-thumb,
        .app-menu #scrollbar:focus-within::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, .34);
            background-clip: padding-box;
        }

        .app-menu #scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, .48);
            background-clip: padding-box;
        }

        .app-menu #scrollbar::-webkit-scrollbar-corner {
            background: transparent;
        }

        .app-menu #scrollbar::-webkit-scrollbar-button {
            width: 0;
            height: 0;
            display: none;
        }

        .app-menu #scrollbar::-webkit-scrollbar-track,
        .app-menu #scrollbar::-webkit-scrollbar-track-piece {
            background: transparent;
            border: 0;
        }

        html[data-sidebar-size="sm"] .app-menu #scrollbar::-webkit-scrollbar,
        html[data-sidebar-size="sm-hover"] .app-menu #scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        html[data-sidebar-size="sm"] .app-menu:hover #scrollbar::-webkit-scrollbar-thumb,
        html[data-sidebar-size="sm-hover"] .app-menu:hover #scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, .28);
            background-clip: padding-box;
        }

        html[data-sidebar-size="sm"] .app-menu #scrollbar,
        html[data-sidebar-size="sm-hover"] .app-menu #scrollbar {
            scrollbar-width: none;
        }

        html[data-sidebar-size="sm"] .app-menu:hover #scrollbar,
        html[data-sidebar-size="sm-hover"] .app-menu:hover #scrollbar {
            scrollbar-width: thin;
        }

        html[data-layout-mode="light"] .app-menu:hover #scrollbar,
        html[data-layout-mode="light"] .app-menu #scrollbar:focus-within {
            scrollbar-color: rgba(64, 81, 137, .28) transparent;
        }

        html[data-layout-mode="light"] .app-menu:hover #scrollbar::-webkit-scrollbar-thumb,
        html[data-layout-mode="light"] .app-menu #scrollbar:focus-within::-webkit-scrollbar-thumb {
            background: rgba(64, 81, 137, .28);
            background-clip: padding-box;
        }

        html[data-layout-mode="light"] .app-menu #scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(64, 81, 137, .42);
            border-radius: 999px;
            background-clip: padding-box;
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
            position: sticky;
            top: 0;
            z-index: 3;
            border-bottom: 1px solid rgba(64, 81, 137, 0.10);
            padding: 1rem 1.25rem;
            background: linear-gradient(135deg, #fff 0%, #f6faff 100%);
        }

        .compose .compose-close {
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 999px;
            background-color: #eef2f7;
            opacity: 1;
        }

        .compose .compose-close:hover {
            background-color: #e2e8f0;
            opacity: 1;
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
            width: 9px;
            height: 9px;
            margin-top: -11px;
            margin-left: -11px;
            border: 0;
            border-radius: 3px;
            background: #4d4de0;
            box-shadow: 13px 0 0 #6aa5e8, 0 13px 0 #9ec4f5, 13px 13px 0 #05056d;
            animation: phuyu-mini-loader 1s ease-in-out infinite;
            z-index: 11;
        }

        @keyframes phuyu-mini-loader {
            0% {
                transform: rotate(0deg) scale(1);
            }

            50% {
                transform: rotate(180deg) scale(1.08);
            }

            100% {
                transform: rotate(360deg) scale(1);
            }
        }

        .phuyu-mini-brand-loader {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 50px;
            border-radius: 7px;
            background: #4d4de0;
            box-shadow: 28px 0 0 #6aa5e8, 0 28px 0 #9ec4f5, 28px 28px 0 #05056d;
            animation: phuyu-mini-loader 1.2s ease-in-out infinite;
        }

        .phuyu-table-loading {
            display: flex;
            min-height: 92px;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: .85rem;
            color: #1d1d75;
            font-size: .78rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .phuyu-table-wrapper-loading {
            min-height: 220px;
        }

        .phuyu-table-wrapper-loading.overlay-spinner::before {
            background: rgba(255, 255, 255, 0.82);
        }

        .phuyu_cargando {
            display: flex;
            min-height: 180px;
            align-items: center;
            justify-content: center;
            width: 100%;
            border: 1px solid rgba(77, 77, 224, 0.08);
            border-radius: 12px;
            background: rgba(248, 250, 252, 0.72);
        }

        .phuyu_cargando .overlay-spinner {
            width: 22px;
            height: 22px;
            display: inline-block;
        }

        .phuyu_cargando .overlay-spinner::before {
            display: none;
        }

        .phuyu_cargando .overlay-spinner::after {
            top: 0;
            left: 0;
            margin: 0;
        }

        .phuyu_cargando + .table-responsive {
            display: none;
        }

        .phuyu-system-loading {
            position: relative;
            min-height: 260px;
        }

        .phuyu-system-loader {
            position: fixed;
            inset: 0;
            z-index: 2050;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background: linear-gradient(135deg, rgba(244, 246, 251, 0.94), rgba(255, 255, 255, 0.98));
            border-radius: 0;
            backdrop-filter: blur(3px);
        }

        .phuyu-system-loader-inner {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.35rem;
            width: min(320px, calc(100vw - 2rem));
            text-align: center;
        }

        .phuyu-system-spinner {
            position: relative;
            width: 118px;
            height: 118px;
            animation: phuyu-system-rotate 6s linear infinite;
        }

        .phuyu-system-ring {
            position: absolute;
            inset: -16px;
            border: 3px dashed rgba(77, 77, 224, 0.28);
            border-radius: 50%;
            animation: phuyu-system-rotate-reverse 8s linear infinite;
        }

        .phuyu-system-square {
            position: absolute;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(5, 5, 109, 0.10);
            animation: phuyu-system-pulse 1.8s infinite ease-in-out;
        }

        .phuyu-system-square-a {
            top: 0;
            left: 0;
            background: #4d4de0;
            animation-delay: 0s;
        }

        .phuyu-system-square-b {
            top: 0;
            right: 0;
            background: #6aa5e8;
            animation-delay: .3s;
        }

        .phuyu-system-square-c {
            bottom: 0;
            left: 0;
            background: #9ec4f5;
            animation-delay: .6s;
        }

        .phuyu-system-square-d {
            right: 0;
            bottom: 0;
            background: #05056d;
            animation-delay: .9s;
        }

        .phuyu-system-logo {
            width: 205px;
            max-width: 80%;
            object-fit: contain;
            animation: phuyu-system-float 3s ease-in-out infinite;
        }

        .phuyu-system-bar {
            position: relative;
            width: min(260px, 78vw);
            height: 9px;
            overflow: hidden;
            border-radius: 20px;
            background: #dfe5f0;
        }

        .phuyu-system-bar::before {
            content: "";
            position: absolute;
            left: -40%;
            width: 40%;
            height: 100%;
            border-radius: 20px;
            background: linear-gradient(90deg, #4d4de0, #6aa5e8);
            animation: phuyu-system-loading 2s infinite ease-in-out;
        }

        .phuyu-system-text {
            color: #1d1d75;
            font-size: .95rem;
            font-weight: 600;
            line-height: 1.35;
            letter-spacing: 0;
            animation: phuyu-system-blink 1.5s infinite;
        }

        @keyframes phuyu-system-rotate {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes phuyu-system-rotate-reverse {
            from {
                transform: rotate(360deg);
            }

            to {
                transform: rotate(0deg);
            }
        }

        @keyframes phuyu-system-pulse {
            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.18);
            }
        }

        @keyframes phuyu-system-loading {
            0% {
                left: -40%;
            }

            100% {
                left: 100%;
            }
        }

        @keyframes phuyu-system-float {
            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-8px);
            }
        }

        @keyframes phuyu-system-blink {
            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: .5;
            }
        }

        @media (max-width: 575.98px) {
            .phuyu-system-spinner {
                width: 108px;
                height: 108px;
            }

            .phuyu-system-ring {
                inset: -14px;
            }

            .phuyu-system-square {
                width: 38px;
                height: 38px;
                border-radius: 11px;
            }

            .phuyu-system-logo {
                width: 180px;
            }
        }

        .col-xs-1,
        .col-xs-2,
        .col-xs-3,
        .col-xs-4,
        .col-xs-5,
        .col-xs-6,
        .col-xs-7,
        .col-xs-8,
        .col-xs-9,
        .col-xs-10,
        .col-xs-11,
        .col-xs-12 {
            flex: 0 0 auto;
            padding-right: calc(var(--vz-gutter-x, 1.5rem) * .5);
            padding-left: calc(var(--vz-gutter-x, 1.5rem) * .5);
        }

        .col-xs-1 { width: 8.33333333%; }
        .col-xs-2 { width: 16.66666667%; }
        .col-xs-3 { width: 25%; }
        .col-xs-4 { width: 33.33333333%; }
        .col-xs-5 { width: 41.66666667%; }
        .col-xs-6 { width: 50%; }
        .col-xs-7 { width: 58.33333333%; }
        .col-xs-8 { width: 66.66666667%; }
        .col-xs-9 { width: 75%; }
        .col-xs-10 { width: 83.33333333%; }
        .col-xs-11 { width: 91.66666667%; }
        .col-xs-12 { width: 100%; }

        .pull-right { float: right !important; }
        .pull-left { float: left !important; }

        .btn-default {
            --vz-btn-color: #212529;
            --vz-btn-bg: #fff;
            --vz-btn-border-color: #d9dde3;
            --vz-btn-hover-color: #212529;
            --vz-btn-hover-bg: #f3f6f9;
            --vz-btn-hover-border-color: #cbd3dc;
            --vz-btn-focus-shadow-rgb: 64, 81, 137;
            --vz-btn-active-color: #212529;
            --vz-btn-active-bg: #e9edf2;
            --vz-btn-active-border-color: #cbd3dc;
        }

        .input-group-addon {
            display: flex;
            align-items: center;
            padding: .47rem .75rem;
            font-size: .8125rem;
            font-weight: 600;
            color: #495057;
            text-align: center;
            white-space: nowrap;
            background-color: #f3f6f9;
            border: 1px solid #ced4da;
        }

        .input-group .input-group-addon:first-child {
            border-right: 0;
            border-radius: .25rem 0 0 .25rem;
        }

        .input-group .input-group-addon:last-child {
            border-left: 0;
            border-radius: 0 .25rem .25rem 0;
        }

        .x_panel {
            background: #fff;
            border: 1px solid rgba(64, 81, 137, .12);
            border-radius: .9rem;
            box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
            margin-bottom: 1rem;
            padding: 1rem;
        }

        .x_title {
            border-bottom: 1px solid rgba(64, 81, 137, .12);
            margin-bottom: 1rem;
            padding-bottom: .75rem;
        }

        .x_content {
            width: 100%;
        }

        .ln_solid {
            border-top: 1px solid rgba(64, 81, 137, .12);
            margin: 1rem 0;
        }

        .phuyu-module-stage .modal-content {
            border: 0;
            border-radius: .95rem;
            box-shadow: 0 24px 80px rgba(15, 23, 42, .24);
            overflow: hidden;
        }

        .phuyu-module-stage .modal-header {
            align-items: center;
            gap: .75rem;
            background: #f8fafc;
            border-bottom: 1px solid rgba(64, 81, 137, .10);
            padding: 1rem 1.25rem;
        }

        .phuyu-module-stage .modal-title {
            color: #1f2937;
            flex: 1 1 auto;
            font-size: 1rem;
            font-weight: 800;
            line-height: 1.25;
            margin: 0;
            order: 1;
        }

        .phuyu-module-stage .modal-body {
            color: #334155;
        }

        .phuyu-module-stage .modal-footer {
            background: #f8fafc;
            border-top: 1px solid rgba(64, 81, 137, .10);
        }

        .phuyu-module-stage .modal .btn {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            justify-content: center;
            min-height: 34px;
            white-space: normal;
        }

        .phuyu-module-stage .modal .btn-close {
            flex: 0 0 auto;
            margin-left: auto;
            order: 2;
            opacity: .75;
        }

        .phuyu-module-stage .modal-header > .close:not(.btn-close) {
            flex: 0 0 auto;
            margin-left: auto;
            order: 2;
        }

        .phuyu-module-stage .modal-header > .close:not(.btn-close) + .modal-title {
            order: 1;
        }

        .phuyu-module-stage .modal .btn-close:hover {
            opacity: 1;
        }

        .phuyu-report-action-grid {
            display: grid;
            gap: .65rem;
        }

        .phuyu-report-action-grid .btn {
            min-height: 42px;
            font-weight: 700;
            text-align: center;
        }

        .phuyu-report-checklist {
            max-height: 280px;
            overflow-y: auto;
            border: 1px solid rgba(64, 81, 137, .10);
            border-radius: .75rem;
        }

        .phuyu-report-checklist .table {
            margin-bottom: 0;
        }

        .phuyu-report-checklist .form-check-input {
            width: 1.15rem;
            height: 1.15rem;
            cursor: pointer;
        }

        .close:not(.btn-close) {
            background: transparent;
            border: 0;
            color: #495057;
            cursor: pointer;
            font-size: 1.25rem;
            line-height: 1;
            opacity: .75;
        }

        .close:not(.btn-close):hover {
            opacity: 1;
        }

        .close.btn-close,
        .btn-close.close {
            background: transparent var(--bs-btn-close-bg) center / 1em auto no-repeat;
            border: 0;
            opacity: .75;
        }

        .close.btn-close:hover,
        .btn-close.close:hover {
            opacity: 1;
        }

        .modal-header.modal-phuyu-titulo .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
            opacity: .95;
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

        /* Ensure hover toggle button is visible in compact sidebar state */
        html[data-sidebar-size="sm"] .btn-vertical-sm-hover {
            display: inline-block !important;
        }
    </style>
</head>
