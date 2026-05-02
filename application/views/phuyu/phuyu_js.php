<script src="<?php echo base_url(); ?>public/js/vendor/jquery-3.5.1.min.js"></script>
<script src="<?php echo base_url(); ?>public/plantilla_phuyu/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo base_url(); ?>public/plantilla_phuyu/libs/simplebar/simplebar.min.js"></script>
<script src="<?php echo base_url(); ?>public/plantilla_phuyu/libs/node-waves/waves.min.js"></script>
<script src="<?php echo base_url(); ?>public/plantilla_phuyu/libs/feather-icons/feather.min.js"></script>

<script src="<?php echo base_url(); ?>public/js/vendor/select2.full.min.js"></script>
<script src="<?php echo base_url(); ?>public/js/vendor/bootstrap-notify.min.js"></script>
<script src="<?php echo base_url(); ?>public/js/sweetalert.min.js"></script>

<script src="<?php echo base_url(); ?>public/js/vue/vue.js"></script>
<script src="<?php echo base_url(); ?>public/js/vue/vue-resource.min.js"></script>
<script src="<?php echo base_url(); ?>public/js/validacion.js"></script>

<script>
    window.phuyuLayoutReady = false;

    if (!sessionStorage.getItem("defaultAttribute")) {
        var htmlAttributes = {};
        Array.prototype.forEach.call(document.documentElement.attributes, function (attr) {
            if (attr && attr.name) {
                htmlAttributes[attr.name] = attr.value;
            }
        });

        sessionStorage.setItem("defaultAttribute", JSON.stringify(htmlAttributes));

        [
            "data-layout",
            "data-sidebar-size",
            "data-bs-theme",
            "data-layout-width",
            "data-sidebar",
            "data-sidebar-image",
            "data-layout-direction",
            "data-layout-position",
            "data-layout-style",
            "data-topbar",
            "data-preloader",
            "data-body-image",
            "data-theme",
            "data-theme-colors"
        ].forEach(function (key) {
            var value = document.documentElement.getAttribute(key);
            if (value !== null) {
                sessionStorage.setItem(key, value);
            }
        });
    }

</script>

<script src="<?php echo base_url(); ?>public/plantilla_phuyu/js/plugins.js"></script>
<script src="<?php echo base_url(); ?>public/plantilla_phuyu/js/app.js"></script>
<!-- <script src="<?php echo base_url(); ?>public/plantilla_phuyu/libs/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="<?php echo base_url(); ?>public/plantilla_phuyu/libs/flatpickr/flatpickr.min.js"></script> -->


<script>
    var url = "<?php echo base_url(); ?>";
    var sistema_url = window.location;
    sistema_url = String(sistema_url).split("/w/");
    var CURRENT_URL = window.location.href.split('#')[0].split('?')[0];
    var $SIDEBAR_MENU = $('#navbar-nav');

    if (sistema_url[1] != undefined && sistema_url[1] != "") {
        phuyu_controller = sistema_url[1];
    } else {
        phuyu_controller = "administracion/dashboard";
    }

    $('#compose, .compose-close').click(function () {
        $('.compose').slideToggle();
    });

    if ($SIDEBAR_MENU.length) {
        var $currentLink = $SIDEBAR_MENU.find('a[href="' + CURRENT_URL + '"]');

        if ($currentLink.length) {
            $currentLink.addClass('active');
            $currentLink.parents('.collapse').addClass('show');
            $currentLink.parents('.collapse').prev('a').addClass('active');
            $currentLink.parents('.collapse').prev('a').attr('aria-expanded', 'true');
        }
    }

    function phuyuToggleMobileSidebar(forceOpen) {
        var shouldOpen = typeof forceOpen === 'boolean'
            ? forceOpen
            : !document.body.classList.contains('vertical-sidebar-enable');

        document.body.classList.toggle('vertical-sidebar-enable', shouldOpen);

        var overlay = document.getElementById('phuyu-vertical-overlay');
        if (overlay) {
            overlay.classList.toggle('active', shouldOpen);
        }
    }

    function phuyuToggleDesktopSidebar() {
        var html = document.documentElement;
        var current = html.getAttribute('data-sidebar-size') || 'lg';
        var next = current === 'sm' ? 'lg' : 'sm';
        html.setAttribute('data-sidebar-size', next);
        sessionStorage.setItem('data-sidebar-size', next);
    }

    function phuyuCloseAllSubmenus($except) {
        $SIDEBAR_MENU.find('.collapse.menu-dropdown.show').each(function () {
            if ($except && this === $except[0]) {
                return;
            }

            $(this).removeClass('show');
            $(this).prev('a.menu-link').addClass('collapsed').attr('aria-expanded', 'false');
        });
    }

    function phuyuBindLayoutFallback() {
        var $hamburger = $('#topnav-hamburger-icon');
        var $overlay = $('#phuyu-vertical-overlay');
        var $verticalHover = $('#vertical-hover');

        function syncHamburgerState() {
            var isMobileMenuOpen = document.body.classList.contains('vertical-sidebar-enable');
            var isCompactDesktop = ['sm', 'sm-hover', 'sm-hover-active'].indexOf(document.documentElement.getAttribute('data-sidebar-size')) !== -1;
            var shouldLookOpen = window.innerWidth < 992 ? isMobileMenuOpen : isCompactDesktop;

            $hamburger.find('.hamburger-icon').toggleClass('open', shouldLookOpen);
        }

        $hamburger.off('click.phuyuFallback').on('click.phuyuFallback', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            if (window.innerWidth < 992) {
                phuyuToggleMobileSidebar();
            } else {
                phuyuToggleDesktopSidebar();
            }

            syncHamburgerState();
        });

        $overlay.off('click.phuyuFallback').on('click.phuyuFallback', function () {
            phuyuToggleMobileSidebar(false);
            phuyuCloseAllSubmenus();
            syncHamburgerState();
        });

        $verticalHover.off('click.phuyuFallback').on('click.phuyuFallback', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            var html = document.documentElement;
            var current = html.getAttribute('data-sidebar-size') || 'lg';
            var next = current === 'sm-hover' || current === 'sm-hover-active' ? 'lg' : 'sm-hover';

            html.setAttribute('data-sidebar-size', next);
            sessionStorage.setItem('data-sidebar-size', next);
            syncHamburgerState();
        });

        $SIDEBAR_MENU.find('a[data-phuyu-menu-toggle="true"]').off('click.phuyuFallback').on('click.phuyuFallback', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            var $trigger = $(this);
            var targetSelector = $trigger.attr('href');
            var $target = $(targetSelector);

            if (!$target.length) {
                return;
            }

            var isOpen = $target.hasClass('show');
            phuyuCloseAllSubmenus(isOpen ? null : $target);

            $trigger.toggleClass('collapsed', isOpen);
            $trigger.attr('aria-expanded', isOpen ? 'false' : 'true');
            $target.toggleClass('show', !isOpen);
        });

        $SIDEBAR_MENU.find('a.nav-link:not([data-phuyu-menu-toggle="true"])').off('click.phuyuFallback').on('click.phuyuFallback', function () {
            if (window.innerWidth < 992) {
                phuyuToggleMobileSidebar(false);
                phuyuCloseAllSubmenus();
                syncHamburgerState();
            }
        });

        $(document).off('click.phuyuSidebarOutside').on('click.phuyuSidebarOutside', function (e) {
            if ($(e.target).closest('#phuyu-navbar-menu, #topnav-hamburger-icon').length) {
                return;
            }

            phuyuCloseAllSubmenus();

            if (window.innerWidth < 992 && document.body.classList.contains('vertical-sidebar-enable')) {
                phuyuToggleMobileSidebar(false);
                syncHamburgerState();
            }
        });

        $(document).off('keydown.phuyuSidebarOutside').on('keydown.phuyuSidebarOutside', function (e) {
            if (e.key === 'Escape') {
                phuyuCloseAllSubmenus();
                phuyuToggleMobileSidebar(false);
                syncHamburgerState();
            }
        });

        $(window).off('resize.phuyuFallback').on('resize.phuyuFallback', function () {
            if (window.innerWidth >= 992) {
                phuyuToggleMobileSidebar(false);
            }

            syncHamburgerState();
        });

        syncHamburgerState();
        window.phuyuLayoutReady = true;
    }

    $(function () {
        phuyuBindLayoutFallback();
    });

    $('#codsistema').on('change', function () {
        phuyu_sistema.phuyu_inicio();
        $.post(url + "phuyu/cambiarsistema/" + $("#codsistema").val()).then(function (data) {
            window.location.href = url + "phuyu/w";
        }, function () {
            phuyu_sistema.alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED", "error");
        });
    });

    function cerrar_sesion() {
        phuyu_sistema.phuyu_inicio();
        $.post(url + "phuyu/verificarcomprobantes").then(function (data) {
            phuyu_sistema.phuyu_fin();
            if (data > 0) {
                $("#modal_electronicos").modal('show');
                $.post(url + "phuyu/obtenercomprobanteselectronicos").then(function (data) {
                    $("#modalver").empty().html(data);
                }, function () {
                    phuyu_sistema.alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED", "error");
                });
            } else {
                $.get(url + "phuyu/phuyu_logout2").then(function (data) {
                    window.location = url;
                });
            }
        }, function () {
            phuyu_sistema.alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED", "error");
        });
    }
</script>
