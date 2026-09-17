<!DOCTYPE html>
<html lang="es" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable" data-theme="default" data-theme-colors="default">
<?php include("phuyu_css.php"); ?>

<body>
    <div id="layout-wrapper">
        <header id="page-topbar">
            <div class="layout-width">
                <div class="navbar-header">
                    <div class="d-flex align-items-center">
                        <div class="navbar-brand-box horizontal-logo">
                            <a href="<?php echo base_url(); ?>phuyu/w/" class="logo logo-dark">
                                <span class="logo-sm">
                                    <img src="<?php echo base_url(); ?>public/img/phuyu.png" alt="Phuyu" class="phuyu-brand-logo">
                                </span>
                                <span class="logo-lg">
                                    <img src="<?php echo base_url(); ?>public/img/phuyu.png" alt="Phuyu" class="phuyu-brand-logo">
                                </span>
                            </a>
                            <a href="<?php echo base_url(); ?>phuyu/w/" class="logo logo-light">
                                <span class="logo-sm">
                                    <img src="<?php echo base_url(); ?>public/img/phuyu.png" alt="Phuyu" class="phuyu-brand-logo">
                                </span>
                                <span class="logo-lg">
                                    <img src="<?php echo base_url(); ?>public/img/phuyu.png" alt="Phuyu" class="phuyu-brand-logo">
                                </span>
                            </a>
                        </div>

                        <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger material-shadow-none" id="topnav-hamburger-icon">
                            <span class="hamburger-icon">
                                <span></span>
                                <span></span>
                                <span></span>
                            </span>
                        </button>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <div class="phuyu-system-picker d-none d-md-block">
                            <select class="form-select" id="codsistema">
                                <?php foreach ($sistemas as $key => $value) { ?>
                                    <option value="<?php echo $value["codsistema"] ?>" <?php if ($value["codsistema"] == $_SESSION["phuyu_codsistema"]) { ?>selected<?php } ?>>
                                        <?php echo $value["descripcion"] ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="dropdown ms-sm-3 header-item topbar-user phuyu-topbar-user">
                            <button type="button" class="btn material-shadow-none" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="d-flex align-items-center">
                                    <img class="rounded-circle header-profile-user" src="<?php echo base_url(); ?>public/plantilla_phuyu/images/users/avatar-1.jpg" alt="Perfil">
                                    <span class="text-start ms-xl-2">
                                        <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text"><?php echo $_SESSION["phuyu_usuario"]; ?></span>
                                        <span class="d-none d-xl-block ms-1 fs-12 user-name-sub-text"><?php echo strtoupper($_SESSION["phuyu_perfil"]); ?></span>
                                    </span>
                                </span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <h6 class="dropdown-header">Bienvenido <?php echo $_SESSION["phuyu_usuario"]; ?></h6>
                                <a class="dropdown-item" href="<?php echo base_url(); ?>">
                                    <i class="ri-store-2-line text-muted fs-16 align-middle me-1"></i>
                                    <span class="align-middle">Sucursales</span>
                                </a>
                                <a class="dropdown-item" href="<?php echo base_url(); ?>phuyu/w/administracion/configuraciones">
                                    <i class="ri-building-line text-muted fs-16 align-middle me-1"></i>
                                    <span class="align-middle">Mi empresa</span>
                                </a>
                                <div class="dropdown-divider"></div>
                                <h6 class="dropdown-header">Cambiar módulo</h6>
                                <?php foreach ($sistemas as $value) { ?>
                                    <a
                                        class="dropdown-item d-flex align-items-center justify-content-between"
                                        href="javascript:;"
                                        onclick="phuyuCambiarSistema(<?php echo (int)$value['codsistema']; ?>)"
                                    >
                                        <span>
                                            <i class="ri-apps-2-line text-muted fs-16 align-middle me-1"></i>
                                            <span class="align-middle"><?php echo htmlspecialchars($value['descripcion'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        </span>
                                        <?php if ((int)$value['codsistema'] === (int)$_SESSION['phuyu_codsistema']) { ?>
                                            <i class="ri-check-line text-success fs-16"></i>
                                        <?php } ?>
                                    </a>
                                <?php } ?>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="javascript:;" onclick="cerrar_sesion()">
                                    <i class="ri-logout-box-r-line text-muted fs-16 align-middle me-1"></i>
                                    <span class="align-middle">Cerrar sesion</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <?php include("phuyu_menu.php"); ?>

       <div id="phuyu-vertical-overlay" class="vertical-overlay"></div>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid phuyu-page-shell">
                    <div class="row">
                        <div class="col-12">
                            <div id="phuyu_sistema" class="phuyu-module-stage"></div>
                        </div>
                    </div>
                </div>

            </div>
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <script>document.write(new Date().getFullYear())</script> © Phuyu System.
                        </div>
                        <div class="col-sm-6">
                            <div class="text-sm-end d-none d-sm-block">
                                <?php echo $_SESSION["phuyu_empresa"]; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <div id="modal_electronicos" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary-subtle">
                    <h4 class="modal-title mb-0">
                        <b style="letter-spacing: 2px;">COMPROBANTES POR ENVIAR A SUNAT</b>
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalver"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">SEGUIR EN EL SISTEMA</button>
                    <a href="<?php echo base_url(); ?>phuyu/phuyu_logout" class="btn btn-danger">CERRAR SESION DE TODAS MANERAS</a>
                </div>
            </div>
        </div>
    </div>

    <?php include("ventana.php"); ?>

    <div class="settings-buttons-container">
        <button type="button" class="btn settings-button btn-primary p-0" data-bs-toggle="offcanvas" 
        data-bs-target="#theme-settings-offcanvas" aria-controls="theme-settings-offcanvas" id="settingsButton">
            <span class="d-inline-block">
                <i class="ri-settings-3-line fs-22"></i>
            </span>
        </button>
    </div>

    <div class="compose">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">
                <b id="phuyu_tituloform">FORMULARIO REGISTRO</b>
            </h5>
             <button type="button"
                class="compose-close btn-close"
                aria-label="Close">
            </button>
        </div>
        <div class="compose-body" id="phuyu_formulario" style="font-size: 11px"></div>
    </div>

 

    <?php include("phuyu_js.php"); ?>
    <script src="<?php echo base_url(); ?>phuyu/phuyu_base.js"></script>
</body>

</html>
