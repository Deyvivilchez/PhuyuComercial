<script src="<?php echo base_url();?>public/js/vendor/jquery-3.5.1.min.js"></script>
    <script src="<?php echo base_url();?>public/js/vendor/bootstrap.bundle.min.js"></script>
    <script src="<?php echo base_url();?>public/js/vendor/OverlayScrollbars.min.js"></script>
    <script src="<?php echo base_url();?>public/js/vendor/clamp.min.js"></script>

    <script src="<?php echo base_url();?>public/icon/acorn-icons.js"></script>
    <script src="<?php echo base_url();?>public/icon/acorn-icons-interface.js"></script>

    <script src="<?php echo base_url();?>public/js/cs/scrollspy.js"></script>

    <script src="<?php echo base_url();?>public/icon/acorn-icons-commerce.js"></script>

    <script src="<?php echo base_url();?>public/js/vendor/select2.full.min.js"></script>

    <script src="<?php echo base_url();?>public/js/vendor/bootstrap-notify.min.js"></script>
<script src="<?php echo base_url();?>public/js/sweetalert.min.js"></script>
    <!-- Vendor Scripts End -->

    <!-- Template Base Scripts Start -->
    <script src="<?php echo base_url();?>public/js/base/helpers.js"></script>
    <script src="<?php echo base_url();?>public/js/base/globals.js"></script>
    <script src="<?php echo base_url();?>public/js/base/nav.js"></script>

    <script src="<?php echo base_url();?>public/js/base/search.js"></script>
    <!-- Template Base Scripts End -->


    <script src="<?php echo base_url();?>public/js/common.js"></script>
    <script src="<?php echo base_url();?>public/js/scripts.js"></script>

<script src="<?php echo base_url();?>public/js/vue/vue.js"></script>
<script src="<?php echo base_url();?>public/js/vue/vue-resource.min.js"></script>
<script src="<?php echo base_url();?>public/js/validacion.js"></script>

<script>
	var url = "<?php echo base_url();?>";
	var sistema_url = window.location; sistema_url = String(sistema_url).split("/w/");
	var CURRENT_URL = window.location.href.split('#')[0].split('?')[0],
	$SIDEBAR_MENU = $('#sidebar-menu')

	if (sistema_url[1] != undefined && sistema_url[1] != "") {
		phuyu_controller = sistema_url[1];
	}else{
		phuyu_controller = "administracion/dashboard";
	}

	$('#compose, .compose-close').click(function(){
 		$('.compose').slideToggle();
 	});

 	$SIDEBAR_MENU.find('a[href="' + CURRENT_URL + '"]').parent('li').parent('ul').siblings('a').addClass('active');
 	$SIDEBAR_MENU.find('a[href="' + CURRENT_URL + '"]').addClass('active');

    $('#codsistema').on('change', function() {
        phuyu_sistema.phuyu_inicio();
        $.post(url+"phuyu/cambiarsistema/"+$("#codsistema").val()).then(function(data){
            window.location.href = url+"phuyu/w";
        }, function(){
            phuyu_sistema.alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
        });
    });

    function cerrar_sesion(){
        phuyu_sistema.phuyu_inicio();
        $.post(url+"phuyu/verificarcomprobantes").then(function(data){
            phuyu_sistema.phuyu_fin();
            if(data>0){
                $("#modal_electronicos").modal('show');
                $.post(url+"phuyu/obtenercomprobanteselectronicos").then(function(data){
                    $("#modalver").empty().html(data);
                },function(){
                    phuyu_sistema.alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
                });
            }else{
                $.get(url+"phuyu/phuyu_logout2").then(function(data){
                    window.location = url
                });
            }
            //
        }, function(){
            phuyu_sistema.alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
        });
    }
</script>