

<div class="app-menu navbar-menu" id="phuyu-navbar-menu">
<div class="navbar-brand-box">
    <a href="<?php echo base_url('phuyu/w/'); ?>" class="logo logo-dark">
        <span class="logo-sm">
            <img src="<?php echo base_url('public/img/phuyu2024-blanco.png'); ?>" alt="Phuyu" class="phuyu-brand-logo phuyu-brand-isotype">
        </span>
        <span class="logo-lg">
            <img src="<?php echo base_url('public/img/phuyu2024-blanco.png'); ?>" alt="Phuyu" class="phuyu-brand-logo phuyu-brand-full">
        </span>
    </a>
    <a href="<?php echo base_url('phuyu/w/'); ?>" class="logo logo-light">
        <span class="logo-sm">
            <img src="<?php echo base_url('public/img/phuyu2024-blanco.png'); ?>" alt="Phuyu" class="phuyu-brand-logo phuyu-brand-isotype">
        </span>
        <span class="logo-lg">
            <img src="<?php echo base_url('public/img/phuyu2024-blanco.png'); ?>" alt="Phuyu" class="phuyu-brand-logo phuyu-brand-full">
        </span>
    </a>
    <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
        <i class="ri-record-circle-line"></i>
    </button>
</div>

    <div id="scrollbar">
        <div class="container-fluid">
            <div id="two-column-menu"></div>

            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span>Menu</span></li>

                <?php
                $i = 0;
                foreach ($phuyu_modulos as $key => $value) {
                    $i++;
                    if (count($value["submodulos"]) > 0) {
                        $collapse_id = "sidebarModulo" . $i;
                        $iconClass = !empty($value["icono"]) ? trim($value["icono"]) : "ri-layout-grid-line";
                ?>
                        <li class="nav-item">
                            <a class="nav-link menu-link collapsed" href="#<?php echo $collapse_id; ?>" data-phuyu-menu-toggle="true" role="button" aria-expanded="false" aria-controls="<?php echo $collapse_id; ?>">
                                <i class="<?php echo htmlspecialchars($iconClass, ENT_QUOTES, 'UTF-8'); ?>"></i>
                                <span><?php echo $value["descripcion"]; ?></span>
                            </a>
                            <div class="collapse menu-dropdown" id="<?php echo $collapse_id; ?>">
                                <ul class="nav nav-sm flex-column">
                                    <?php foreach ($value["submodulos"] as $val) { ?>
                                        <li class="nav-item">
                                            <a href="<?php echo base_url() . 'phuyu/w/' . $val["url"]; ?>" class="nav-link">
                                                <?php echo $val["descripcion"]; ?>
                                            </a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </li>
                <?php
                    }
                }
                ?>
            </ul>
        </div>
    </div>

    <div class="sidebar-background"></div>
</div>
