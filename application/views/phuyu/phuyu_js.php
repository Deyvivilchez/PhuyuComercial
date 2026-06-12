<script src="<?php echo base_url(); ?>public/js/vendor/jquery-3.5.1.min.js"></script>
<script src="<?php echo base_url(); ?>public/plantilla_phuyu/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo base_url(); ?>public/plantilla_phuyu/libs/simplebar/simplebar.min.js"></script>
<script src="<?php echo base_url(); ?>public/plantilla_phuyu/libs/node-waves/waves.min.js"></script>
<script src="<?php echo base_url(); ?>public/plantilla_phuyu/libs/feather-icons/feather.min.js"></script>

<script src="<?php echo base_url(); ?>public/plantilla_phuyu/js/plugins.js"></script>
<script src="<?php echo base_url(); ?>public/plantilla_phuyu/js/app.js"></script>
<!-- <script src="<?php echo base_url(); ?>public/plantilla_phuyu/libs/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="<?php echo base_url(); ?>public/plantilla_phuyu/libs/flatpickr/flatpickr.min.js"></script> -->


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

    var url = "<?php echo base_url(); ?>";
    var sistema_url = window.location;
    sistema_url = String(sistema_url).split("/w/");
    var CURRENT_URL = phuyuNormalizeMenuUrl(window.location.href);
    var $SIDEBAR_MENU = $('#navbar-nav');

    function phuyuNormalizeMenuUrl(rawUrl) {
        var parser = document.createElement('a');
        parser.href = rawUrl || '';

        var path = parser.pathname || '';
        path = path.replace(/\/+$/, '');

        return (parser.origin || (parser.protocol + '//' + parser.host)) + path;
    }

    if (sistema_url[1] != undefined && sistema_url[1] != "") {
        phuyu_controller = sistema_url[1];
    } else {
        phuyu_controller = "administracion/dashboard";
    }

    var phuyuSesionConfig = {
        cerrar: <?php echo isset($_SESSION["phuyu_sesion_cerrar_inactividad"]) ? (int)$_SESSION["phuyu_sesion_cerrar_inactividad"] : 0; ?>,
        minutos: <?php echo isset($_SESSION["phuyu_sesion_tiempo_minutos"]) ? (int)$_SESSION["phuyu_sesion_tiempo_minutos"] : 120; ?>,
        aviso: <?php echo isset($_SESSION["phuyu_sesion_mostrar_aviso"]) ? (int)$_SESSION["phuyu_sesion_mostrar_aviso"] : 1; ?>,
        minutosAviso: <?php echo isset($_SESSION["phuyu_sesion_minutos_aviso"]) ? (int)$_SESSION["phuyu_sesion_minutos_aviso"] : 5; ?>
    };

    (function iniciarControlSesion() {
        if (phuyuSesionConfig.cerrar != 1) {
            return;
        }

        var temporizadorAviso = null;
        var temporizadorCierre = null;
        var ultimoPing = 0;
        var avisoMostrado = false;

        function segundos(valor) {
            return Math.max(1, parseInt(valor || 1, 10)) * 1000;
        }

        function cerrarPorInactividad() {
            $.get(url + "phuyu/phuyu_logout2").always(function () {
                window.location = url;
            });
        }

        function programarTemporizadores() {
            clearTimeout(temporizadorAviso);
            clearTimeout(temporizadorCierre);
            avisoMostrado = false;

            var totalMs = segundos(phuyuSesionConfig.minutos * 60);
            var avisoMs = segundos(phuyuSesionConfig.minutosAviso * 60);
            var esperaAviso = Math.max(1000, totalMs - avisoMs);

            if (phuyuSesionConfig.aviso == 1 && phuyuSesionConfig.minutosAviso < phuyuSesionConfig.minutos) {
                temporizadorAviso = setTimeout(mostrarAviso, esperaAviso);
            }

            temporizadorCierre = setTimeout(cerrarPorInactividad, totalMs);
        }

        function actualizarConfiguracion(data) {
            if (!data || data.estado != 1) {
                return;
            }
            phuyuSesionConfig.cerrar = parseInt(data.cerrar_inactividad || phuyuSesionConfig.cerrar, 10);
            phuyuSesionConfig.minutos = parseInt(data.tiempo_inactividad_minutos || phuyuSesionConfig.minutos, 10);
            phuyuSesionConfig.aviso = parseInt(data.mostrar_aviso || phuyuSesionConfig.aviso, 10);
            phuyuSesionConfig.minutosAviso = parseInt(data.minutos_aviso || phuyuSesionConfig.minutosAviso, 10);
        }

        function pingSesion(forzar) {
            var ahora = Date.now();
            if (!forzar && (ahora - ultimoPing) < 60000) {
                programarTemporizadores();
                return;
            }
            ultimoPing = ahora;

            $.get(url + "phuyu/phuyu_ping_sesion").then(function (data) {
                actualizarConfiguracion(data);
                if (phuyuSesionConfig.cerrar == 1) {
                    programarTemporizadores();
                }
            }, cerrarPorInactividad);
        }

        function mostrarAviso() {
            if (avisoMostrado || phuyuSesionConfig.aviso != 1) {
                return;
            }
            avisoMostrado = true;
            swal({
                title: "Sesion por expirar",
                text: "Tu sesion se cerrara por inactividad. Deseas continuar?",
                icon: "warning",
                buttons: ["Cerrar sesion", "Continuar"],
                dangerMode: true
            }).then(function (continuar) {
                if (continuar) {
                    pingSesion(true);
                } else {
                    cerrarPorInactividad();
                }
            });
        }

        ["click", "keydown", "mousemove", "scroll", "touchstart"].forEach(function (evento) {
            document.addEventListener(evento, function () {
                pingSesion(false);
            }, { passive: true });
        });

        programarTemporizadores();
    })();

    $('#compose, .compose-close').click(function () {
        $('.compose').slideToggle();
    });

    function phuyuOpenActiveSidebarLink() {
        if (!$SIDEBAR_MENU.length) {
            return;
        }

        var $currentLink = $();
        var bestMatchLength = 0;

        $SIDEBAR_MENU.find('a.nav-link[href]').each(function () {
            var href = this.getAttribute('href');

            if (!href || href.charAt(0) === '#') {
                return;
            }

            var normalizedHref = phuyuNormalizeMenuUrl(this.href);
            var isCurrent = normalizedHref === CURRENT_URL || CURRENT_URL.indexOf(normalizedHref + '/') === 0;

            if (isCurrent && normalizedHref.length > bestMatchLength) {
                $currentLink = $(this);
                bestMatchLength = normalizedHref.length;
            }
        });

        if (!$currentLink.length) {
            return;
        }

        $SIDEBAR_MENU.find('a.nav-link').removeClass('active');
        $SIDEBAR_MENU.find('.collapse.menu-dropdown').removeClass('show')
            .prev('a.menu-link')
            .addClass('collapsed')
            .attr('aria-expanded', 'false');

        $currentLink.addClass('active');
        $currentLink.parents('.collapse.menu-dropdown').addClass('show');
        $currentLink.parents('.collapse.menu-dropdown').prev('a.menu-link')
            .addClass('active')
            .removeClass('collapsed')
            .attr('aria-expanded', 'true');
    }

    phuyuOpenActiveSidebarLink();

    function phuyuToggleMobileSidebar(forceOpen) {
        var shouldOpen = typeof forceOpen === 'boolean'
            ? forceOpen
            : !document.body.classList.contains('vertical-sidebar-enable');

        if (window.innerWidth < 992) {
            document.documentElement.setAttribute('data-sidebar-size', 'lg');
        }

        document.body.classList.toggle('vertical-sidebar-enable', shouldOpen);

        var overlay = document.getElementById('phuyu-vertical-overlay');
        if (overlay) {
            overlay.classList.toggle('active', shouldOpen);
        }
    }

    function phuyuToggleDesktopSidebar() {
        var html = document.documentElement;
        var current = html.getAttribute('data-sidebar-size') || 'lg';
        var compactSizes = ['sm', 'sm-hover', 'sm-hover-active'];
        var next = compactSizes.indexOf(current) !== -1 ? 'lg' : 'sm';
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
        $SIDEBAR_MENU.find('.nav-item.phuyu-compact-open').each(function () {
            var $submenu = $(this).children('.collapse.menu-dropdown');
            if ($except && $submenu.length && $submenu[0] === $except[0]) {
                return;
            }

            $(this).removeClass('phuyu-compact-open');
        });
    }

    function phuyuBindLayoutFallback() {
        var $hamburger = $('#topnav-hamburger-icon');
        var $overlay = $('#phuyu-vertical-overlay');
        var $verticalHover = $('#vertical-hover');
        var $appMenu = $('#phuyu-navbar-menu');
        var $navbarNav = $('#navbar-nav');

        if (!$hamburger.length || !$navbarNav.length) {
            return;
        }

        function syncHamburgerState() {
            var isMobileMenuOpen = document.body.classList.contains('vertical-sidebar-enable');
            var isCompactDesktop = ['sm', 'sm-hover', 'sm-hover-active'].indexOf(document.documentElement.getAttribute('data-sidebar-size')) !== -1;
            var shouldLookOpen = window.innerWidth < 992 ? isMobileMenuOpen : isCompactDesktop;

            $hamburger.find('.hamburger-icon').toggleClass('open', shouldLookOpen);
            $overlay.toggleClass('active', window.innerWidth < 992 && isMobileMenuOpen);
        }

        function normalizeDesktopSidebarState() {
            var html = document.documentElement;

            if (window.innerWidth < 992) {
                return;
            }

            document.body.classList.remove('vertical-sidebar-enable');
            $overlay.removeClass('active');

            var storedSidebarSize = sessionStorage.getItem('data-sidebar-size');
            if (storedSidebarSize && ['lg', 'sm', 'md'].indexOf(storedSidebarSize) !== -1) {
                html.setAttribute('data-sidebar-size', storedSidebarSize);
            } else if (['sm-hover', 'sm-hover-active'].indexOf(html.getAttribute('data-sidebar-size')) !== -1) {
                html.setAttribute('data-sidebar-size', 'sm');
                sessionStorage.setItem('data-sidebar-size', 'sm');
            }
        }

        normalizeDesktopSidebarState();

        function handleHamburgerClick(e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            if (window.innerWidth < 992) {
                phuyuToggleMobileSidebar();
            } else {
                phuyuToggleDesktopSidebar();
            }

            syncHamburgerState();
        }

        $hamburger.off('click.phuyuFallback');
        if (!$hamburger[0].phuyuFallbackBound) {
            $hamburger[0].addEventListener('click', handleHamburgerClick, true);
            $hamburger[0].phuyuFallbackBound = true;
        }

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
            var next;

            if (current === 'sm-hover') {
                next = 'sm-hover-active';
            } else if (current === 'sm-hover-active') {
                next = 'sm-hover';
            } else {
                next = 'sm-hover';
            }

            html.setAttribute('data-sidebar-size', next);
            sessionStorage.setItem('data-sidebar-size', next);
            syncHamburgerState();
        });

        function isDesktopCompactSidebar() {
            var sidebarSize = document.documentElement.getAttribute('data-sidebar-size') || 'lg';
            return window.innerWidth >= 992 && sidebarSize === 'sm';
        }

        function phuyuOpenSubmenu($trigger, forceOpen) {
            var targetSelector = $trigger.attr('href');
            var $target = $(targetSelector);

            if (!$target.length) {
                return;
            }

            var $navItem = $trigger.closest('.nav-item');
            var isOpen = isDesktopCompactSidebar()
                ? $navItem.hasClass('phuyu-compact-open')
                : $target.hasClass('show');
            var shouldOpen = typeof forceOpen === 'boolean' ? forceOpen : !isOpen;

            phuyuCloseAllSubmenus(shouldOpen ? $target : null);

            $trigger.toggleClass('collapsed', !shouldOpen);
            $trigger.attr('aria-expanded', shouldOpen ? 'true' : 'false');
            $target.toggleClass('show', shouldOpen);
            $navItem.toggleClass('phuyu-compact-open', shouldOpen && isDesktopCompactSidebar());
        }

        $navbarNav.find('a[data-phuyu-menu-toggle="true"]').off('mouseenter.phuyuFallback').on('mouseenter.phuyuFallback', function () {
            if (!isDesktopCompactSidebar()) {
                return;
            }

            phuyuOpenSubmenu($(this), true);
            syncHamburgerState();
        });

        $appMenu.off('mouseleave.phuyuFallback').on('mouseleave.phuyuFallback', function () {
            if (isDesktopCompactSidebar()) {
                phuyuCloseAllSubmenus();
            }
        });

        $navbarNav.find('a[data-phuyu-menu-toggle="true"]').off('click.phuyuFallback').on('click.phuyuFallback', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            phuyuOpenSubmenu($(this));
            syncHamburgerState();
        });

        $navbarNav.find('a.nav-link:not([data-phuyu-menu-toggle="true"])').off('click.phuyuFallback').on('click.phuyuFallback', function () {
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
                normalizeDesktopSidebarState();
            }

            syncHamburgerState();
        });

        syncHamburgerState();
	        window.phuyuLayoutReady = true;
	    }

	    function phuyuBindBootstrapLegacyBridge() {
	        if (!window.bootstrap || !window.jQuery) {
	            return;
	        }

            if (!$.fn.modal && bootstrap.Modal) {
                $.fn.modal = function (option) {
                    return this.each(function () {
                        var instance = bootstrap.Modal.getOrCreateInstance(this, typeof option === 'object' ? option : {});

                        if (typeof option === 'string' && typeof instance[option] === 'function') {
                            instance[option]();
                        } else if (option === undefined || typeof option === 'object') {
                            instance.show();
                        }
                    });
                };
            }

            if (!$.fn.dropdown && bootstrap.Dropdown) {
                $.fn.dropdown = function (option) {
                    return this.each(function () {
                        var instance = bootstrap.Dropdown.getOrCreateInstance(this);

                        if (typeof option === 'string' && typeof instance[option] === 'function') {
                            instance[option]();
                        } else if (option === undefined || option === 'toggle') {
                            instance.toggle();
                        }
                    });
                };
            }

            if (!$.fn.tooltip && bootstrap.Tooltip) {
                $.fn.tooltip = function (option) {
                    return this.each(function () {
                        var instance = bootstrap.Tooltip.getOrCreateInstance(this, typeof option === 'object' ? option : {});

                        if (typeof option === 'string' && typeof instance[option] === 'function') {
                            instance[option]();
                        }
                    });
                };
            }

	        $(document).off('click.phuyuLegacyModalDismiss').on('click.phuyuLegacyModalDismiss', '[data-dismiss="modal"]', function (e) {
	            e.preventDefault();
	            var modal = this.closest('.modal');
	            if (modal) {
	                bootstrap.Modal.getOrCreateInstance(modal).hide();
	            }
	        });

	        $(document).off('click.phuyuLegacyModalToggle').on('click.phuyuLegacyModalToggle', '[data-toggle="modal"]', function (e) {
	            var selector = this.getAttribute('data-target') || this.getAttribute('href');
	            if (!selector || selector === '#') {
	                return;
	            }

	            var modal = document.querySelector(selector);
	            if (modal) {
	                e.preventDefault();
	                bootstrap.Modal.getOrCreateInstance(modal).show();
	            }
	        });

	        $(document).off('click.phuyuLegacyDropdownToggle').on('click.phuyuLegacyDropdownToggle', '[data-toggle="dropdown"]', function (e) {
	            e.preventDefault();
	            bootstrap.Dropdown.getOrCreateInstance(this).toggle();
	        });

	        $('[data-toggle="tooltip"]').each(function () {
	            bootstrap.Tooltip.getOrCreateInstance(this);
	        });
	    }

	    $(function () {
	        phuyuBindLayoutFallback();
	        phuyuBindBootstrapLegacyBridge();
	    });

    function phuyuCambiarSistema(codsistema) {
        codsistema = parseInt(codsistema, 10);
        if (!codsistema) {
            return;
        }

        phuyu_sistema.phuyu_inicio();
        $.post(url + "phuyu/cambiarsistema/" + codsistema).then(function (data) {
            window.location.href = url + "phuyu/w";
        }, function () {
            phuyu_sistema.phuyu_fin();
            phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED", "error");
        });
    }

    $('#codsistema').on('change', function () {
        phuyuCambiarSistema($(this).val());
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
	                    phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED", "error");
	                });
            } else {
                $.get(url + "phuyu/phuyu_logout2").then(function (data) {
                    window.location = url;
                });
            }
	        }, function () {
	            phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED", "error");
	        });
	    }
</script>

<script src="<?php echo base_url(); ?>public/js/vendor/select2.full.min.js"></script>
<script src="<?php echo base_url(); ?>public/js/vendor/bootstrap-notify.min.js"></script>
<script src="<?php echo base_url(); ?>public/js/sweetalert.min.js"></script>
<script src="<?php echo base_url(); ?>public/js/vue/vue.js"></script>
<script src="<?php echo base_url(); ?>public/js/vue/vue-resource.min.js"></script>
<script src="<?php echo base_url(); ?>public/js/validacion.js"></script>
