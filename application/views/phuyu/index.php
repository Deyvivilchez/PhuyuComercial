<!DOCTYPE html>
<html lang="en"
  data-placement="vertical"
  data-behaviour="pinned"
  data-layout="boxed"
  data-radius="rounded"
  data-color="light-blue"
  data-navcolor="light">
<?php include("phuyu_css.php"); ?>

<body>
  <div id="root">
    <div id="nav" class="nav-container d-flex" data-vertical-unpinned="1200" data-vertical-mobile="1200" data-disable-pinning="true">
      <div class="nav-content d-flex">
        <!-- Logo Start -->
        <div class="logo position-relative" style="padding:12px; text-align:center;">
          <a href="<?php echo base_url(); ?>phuyu/w/" style="display:inline-block;">
            <img
              src="<?php echo base_url(); ?>public/img/phuyu.png"
              alt="Phuyu"
              style="display:block; max-width:140px; width:100%; height:auto; object-fit:contain;" />
          </a>
        </div>


        <div class="user-container d-flex">
          <a href="#" class="d-flex user position-relative" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <!-- <img class="profile" alt="profile" src="<?php echo base_url(); ?>public/img/profile/profile-9.webp" /> -->
            <img class="profile" alt="profile" src="<?php echo base_url(); ?>public/img/profile/icon-persona.png" />
            <div class="name"><b class="text-info"><?php echo strtoupper($_SESSION["phuyu_perfil"]); ?></b></div>
          </a>
          <div class="dropdown-menu dropdown-menu-end user-menu wide">
            <div class="row mb-3 ms-0 me-0">
              <div class="col-12 ps-1 mb-2">
                <div class="text-medium text-primary">Opciones de Sistema</div>
              </div>
              <div class="col-12 ps-1 pe-1">
                <ul class="list-unstyled">
                  <li>
                    <a href="<?php echo base_url(); ?>"><i data-acorn-icon="wallet"></i> Sucursales</a>
                  </li>
                  <li>
                    <a href="<?php echo base_url(); ?>phuyu/w/administracion/configuraciones"><i data-acorn-icon="content"></i> Mi empresa</a>
                  </li>
                  <li>
                    <!--<a href="<?php echo base_url(); ?>phuyu/phuyu_logout"><i data-acorn-icon="logout"></i> Cerrar Sesion</a>-->
                    <a href="javascript:;" onclick="cerrar_sesion()"><i data-acorn-icon="logout"></i> Cerrar Sesion</a>
                  </li>
                </ul>
              </div>
            </div>
          </div>
          <div class="filled w-100" style="padding-left: 1rem">
            <select class="form-select" id="codsistema" style="border:2px solid #1fa9e8">
              <?php
              foreach ($sistemas as $key => $value) { ?>
                <option value="<?php echo $value["codsistema"] ?>" <?php if ($value["codsistema"] == $_SESSION["phuyu_codsistema"]) { ?> selected <?php } ?>><?php echo $value["descripcion"] ?></option>
              <?php }
              ?>
            </select>
          </div>
        </div>
        <?php include("phuyu_menu.php"); ?>
        <div class="mobile-buttons-container">
          <!-- Scrollspy Mobile Button Start -->
          <a href="#" id="scrollSpyButton" class="spy-button" data-bs-toggle="dropdown">
            <i data-acorn-icon="menu-dropdown"></i>
          </a>
          <!-- Scrollspy Mobile Button End -->

          <!-- Scrollspy Mobile Dropdown Start -->
          <div class="dropdown-menu dropdown-menu-end" id="scrollSpyDropdown"></div>
          <!-- Scrollspy Mobile Dropdown End -->

          <!-- Menu Button Start -->
          <a href="#" id="mobileMenuButton" class="menu-button">
            <i data-acorn-icon="menu"></i>
          </a>
          <!-- Menu Button End -->
        </div>
        <!-- Mobile Buttons End -->
      </div>
      <div class="nav-shadow"></div>
    </div>
    <main>
      <div class="container ">
        <div class="justify-content-center">
          <div class="" id="phuyu_sistema"> </div>
        </div>
      </div>
      <div id="modal_electronicos" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
          <div class="modal-content" style="border-radius:0px">
            <div class="modal-header modal-phuyu-titulo">
              <h4 class="modal-title">
                <b style="letter-spacing:4px;">COMPROBANTES POR ENVIAR A SUNAT</b>
              </h4>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalver">
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-white" data-bs-dismiss="modal">SEGUIR EN EL SISTEMA</button>
              <a href="<?php echo base_url(); ?>phuyu/phuyu_logout" class="btn btn-danger"><i data-acorn-icon="logout"></i> CERRAR SESION DE TODAS MANERAS</a>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
  <?php include("ventana.php"); ?>
  <div class="settings-buttons-container">
    <button type="button" class="btn settings-button btn-primary p-0" data-bs-toggle="modal" data-bs-target="#settings" id="settingsButton">
      <span class="d-inline-block no-delay" data-bs-delay="0" data-bs-offset="0,3" data-bs-toggle="tooltip" data-bs-placement="left" title="Settings">
        <i data-acorn-icon="paint-roller" class="position-relative"></i>
      </span>
    </button>
  </div>
  <div class="compose col-md-4 col-xs-12" style="overflow-y: auto;">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title">
        <b id="phuyu_tituloform"> FORMULARIO REGISTRO</b>
      </h5>
      <button type="button" class="close compose-close btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close">
      </button>
    </div>
    <div class="compose-body" id="phuyu_formulario" style="font-size: 11px"> </div>
  </div>

  <?php include("phuyu_js.php"); ?>
  <script src="<?php echo base_url(); ?>phuyu/phuyu_base.js"></script>
</body>

</html>