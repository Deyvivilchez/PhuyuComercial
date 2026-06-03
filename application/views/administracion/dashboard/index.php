<div id="phuyu_datos"> 
<?php 
if($_SESSION["phuyu_codsistema"]==1){
  if($_SESSION["phuyu_codperfil"]==1 || $_SESSION["phuyu_codperfil"]==2 || $_SESSION["phuyu_codperfil"]==3){ ?>	
  <div class="row form-group">
    <div class="col-md-12">
        <?php if($informacion>0){ ?>
          <div role="alert" class="alert alert-danger" style="border: 2px solid red;"> 
            <h5>ATENCION!, Señores de la empresa <?php echo $_SESSION["phuyu_empresa"]; ?> le informamos que hay comprobantes que están pendientes de envío a SUNAT, le recomendamos realizar el envío haciendo <a href="<?php echo base_url();?>phuyu/w/facturacion/facturacion"><b>Click aquí</b></a> </h5>
          </div>
        <?php } ?>
    </div>
  </div>
	<div class="col-md-12">
		<div class="row" align="center">
      <div class="col-sm-3">
        <div class="card h-100 hover-scale-up">
          <div class="card-body pb-0">
            <div class="d-flex flex-column align-items-center mb-4">
              <div class="bg-gradient-light sw-6 sh-6 rounded-xl d-flex justify-content-center align-items-center mb-2">
                <i class="text-white" data-acorn-icon="building-small"></i>
              </div>
              <div class="cta-4 text-primary mb-1">ESTADO DE LA CAJA ASIGNADA</div>
              <div class="display-4 text-danger">{{totales.estado}}</div>
            </div>
            <p class="text-alternate mb-4">
              Le informamos que su caja está aperturada, para poder realizar cualquier tipo de movimiento.
            </p>
          </div>
        </div>
      </div>
      <div class="col-sm-3">
        <div class="card h-100 hover-scale-up">
          <div class="card-body pb-0">
            <div class="d-flex flex-column align-items-center mb-4">
              <div class="bg-gradient-light sw-6 sh-6 rounded-xl d-flex justify-content-center align-items-center mb-2">
                <i class="text-white" data-acorn-icon="building-small"></i>
              </div>
              <div class="cta-4 text-primary mb-1">TOTAL DE DINERO EN CAJA</div>
              <div class="display-4 text-danger">$ {{totales.caja}}</div>
            </div>
            <p class="text-alternate mb-4">
              El total de dinero recaudado en su caja.
            </p>
          </div>
        </div>
      </div>

      <div class="col-sm-3">
        <div class="card h-100 hover-scale-up">
          <div class="card-body pb-0">
            <div class="d-flex flex-column align-items-center mb-4">
              <div class="bg-gradient-light sw-6 sh-6 rounded-xl d-flex justify-content-center align-items-center mb-2">
                <i class="text-white" data-acorn-icon="building-small"></i>
              </div>
              <div class="cta-4 text-primary mb-1">TOTAL DE DINERO EN BANCO</div>
              <div class="display-4 text-danger">$ {{totales.banco}}</div>
            </div>
            <p class="text-alternate mb-4">
              Informacion recaudada del sistema a travez de los cheques, depositos y transferencias en su cuenta bancaria.
            </p>
          </div>
        </div>
      </div>

      <div class="col-sm-3">
        <div class="card h-100 hover-scale-up">
          <div class="card-body pb-0">
            <div class="d-flex flex-column align-items-center mb-4">
              <div class="bg-gradient-light sw-6 sh-6 rounded-xl d-flex justify-content-center align-items-center mb-2">
                <i class="text-white" data-acorn-icon="building-small"></i>
              </div>
              <div class="cta-4 text-primary mb-1">TOTAL GENERAL DE DINERO</div>
              <div class="display-4 text-danger">$ {{totales.general}}</div>
            </div>
            <p class="text-alternate mb-4">
              Informacion recaudada del sistema del dinero de caja y bancos.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-12"> <br>
    <div class="row">
      <div class="col-xl-6 mb-5">
        <section class="scroll-section" id="basicItems">
          <div class="d-flex justify-content-between">
            <h2 class="small-title"><b>MEJORES CLIENTES</b></h2>
          </div>
          <div class="scroll-out">
            <div class="scroll-by-count" data-count="4">
              <?php 
                foreach ($clientes as $key => $value) { ?>
                  <div class="card mb-2">
                    <a href="#" class="row g-0 sh-10">
                      <div class="col-auto h-100">
                        <img src="<?php echo base_url();?>public/img/profile/profile-11.webp" alt="alternate text" class="card-img card-img-horizontal sw-11" />
                      </div>
                      <div class="col">
                        <div class="card-body d-flex flex-row pt-0 pb-0 h-100 align-items-center justify-content-between">
                          <div class="d-flex flex-column justify-content-center">
                            <div><?php echo $value["razonsocial"];?></div>
                            <div><?php echo $value["documento"];?></div>
                          </div>
                          <div class="d-flex flex-row ms-3">
                            <div class="d-flex flex-column align-items-center">
                              <div class="text-muted text-small">VENTAS S/.</div>
                              <div class="text-alternate"><b class="text-success"><?php echo round($value["importe"],2);?></b></div>
                            </div>
                            <div class="d-flex flex-column align-items-center ms-3">
                              <div class="text-muted text-small">VENTAS #</div>
                              <div class="text-alternate"><b><?php echo $value["cantidad"];?></b></div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </a>
                  </div>
                <?php }
              ?>
            </div>
          </div>
        </section>
      </div>

      <div class="col-xl-6 mb-5">
        <section class="scroll-section" id="basicItems">
          <div class="d-flex justify-content-between">
            <h2 class="small-title"><b>MEJORES PROVEEDORES</b></h2>
          </div>
          <div class="scroll-out">
            <div class="scroll-by-count" data-count="4">
              <?php 
                foreach ($proveedores as $key => $value) { ?>
                  <div class="card mb-2">
                    <a href="#" class="row g-0 sh-10">
                      <div class="col-auto h-100">
                        <img src="<?php echo base_url();?>public/img/profile/profile-11.webp" alt="alternate text" class="card-img card-img-horizontal sw-11" />
                      </div>
                      <div class="col">
                        <div class="card-body d-flex flex-row pt-0 pb-0 h-100 align-items-center justify-content-between">
                          <div class="d-flex flex-column justify-content-center">
                            <div><?php echo $value["razonsocial"];?></div>
                            <div><?php echo $value["documento"];?></div>
                          </div>
                          <div class="d-flex flex-row ms-3">
                            <div class="d-flex flex-column align-items-center">
                              <div class="text-muted text-small">COMPRAS S/.</div>
                              <div class="text-alternate"><b class="text-success"><?php echo round($value["importe"],2);?></b></div>
                            </div>
                            <div class="d-flex flex-column align-items-center ms-3">
                              <div class="text-muted text-small">COMPRAS #</div>
                              <div class="text-alternate"><b><?php echo $value["cantidad"];?></b></div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </a>
                  </div>
                <?php }
              ?>
            </div>
          </div>
        </section>
      </div>

    </div>
	</div>
  <?php }
   if($_SESSION["phuyu_codperfil"]==5){ 
    include("indexvendedor.php");
   }
 }
   ?>
</div>

<!-- <script src="<?php echo base_url();?>public/js/highcharts.js"> </script> -->
<script>
  if (typeof AcornIcons !== 'undefined') {
      new AcornIcons().replace();
    }
    if (typeof Icons !== 'undefined') {
      const icons = new Icons();
    }
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_empresa/dashboard.js"> </script>

<script src="<?php echo base_url();?>public/js/vendor/moment-with-locales.min.js"></script>
<script src="<?php echo base_url();?>public/js/vendor/Chart.bundle.min.js"></script>
<script src="<?php echo base_url();?>public/js/vendor/chartjs-plugin-rounded-bar.min.js"></script>
<script src="<?php echo base_url();?>public/js/cs/charts.extend.js"></script>
<script src="<?php echo base_url();?>phuyu/phuyu_chartsdashboard.js"></script>