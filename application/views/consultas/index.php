<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="shortcut icon" href="<?php echo base_url();?>public/img/phuyu_favicon.ico">
        <title>phuyu Perú Comercial</title>
        
        <link href="<?php echo base_url();?>public/css/bootstrap/bootstrap.css" rel="stylesheet">
        <link href="<?php echo base_url();?>public/css/font-awesome.min.css" rel="stylesheet">
        <link href="<?php echo base_url();?>public/css/nprogress.css" rel="stylesheet">
        <link href="<?php echo base_url();?>public/css/green.css" rel="stylesheet">

        <link href="<?php echo base_url();?>public/css/notify/pnotify.css" rel="stylesheet">
        <link href="<?php echo base_url();?>public/css/notify/pnotify.buttons.css" rel="stylesheet">
        <link href="<?php echo base_url();?>public/css/notify/pnotify.nonblock.css" rel="stylesheet">

        <link href="<?php echo base_url();?>public/css/select/select.min.css" rel="stylesheet">
        <link href="<?php echo base_url();?>public/css/select/ajax-select.min.css" rel="stylesheet">
        <link href="<?php echo base_url();?>public/css/switchery.min.css" rel="stylesheet">

        <link href="<?php echo base_url();?>public/css/custom.min.css" rel="stylesheet">
        <link href="<?php echo base_url();?>public/css/phuyu.css" rel="stylesheet">
    </head>
    <style type="text/css">
        .table > tbody > tr > td{
            padding: 10px 5px;
            background: #fff;
        }
    </style>
    <body class="nav-md" style="background: #fff">

        <div class="container body">
            <div class="main_container">

                <div class="top_nav" style="margin-left: 0rem;">
                    <div class="nav_menu" style="padding-left: 2rem">
                        <nav>
                            <ul class="nav navbar-nav hidden-xs">
                                
                                <li>
                                    <p style="color:#1ab394;padding-left: 10px;padding-top:4px;font-weight: 700;font-size: 22px">EMPRESA: CERAMICA SAN MARTIN SAC</p>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>

                <div class="right_col" id="phuyu_sistema" style="padding-top: 10rem;margin-left: 8rem;margin-right: 8rem">
                    <div id="phuyu_operacion">
                        <div class="phuyu_body_card">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="alert alert-info" role="alert" style="font-size: 20px;text-align:center">
                                              <strong><i class="fa fa-eye"></i> Resultados disponibles desde Abril del 2021</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label>TIPO COMPROBANTE<span class="text-danger">*</span></label>
                                            <select class="form-control" name="codcomprobantetipo" v-model="codcomprobantetipo">
                                                <option value="">--SELECCIONE--</option>
                                                <?php
                                                    foreach ($comprobantes as $key => $value) { ?>
                                                        <option value="<?php echo $value["codcomprobantetipo"];?>">
                                                            <?php echo $value["descripcion"];?>
                                                        </option>
                                                    <?php }
                                                ?>
                                            </select>
                                        </div>
                                    </div><br>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label>TIPO DOCUMENTO<span class="text-danger">*</span></label>
                                            <select class="form-control" v-on:change="phuyu_tipodocumento()" name="coddocumentotipo" v-model="coddocumentotipo">
                                                <option value="">--SELECCIONE--</option>
                                                <option value="2">D.N.I</option>
                                                <option value="3">CARNET DE EXTRANJERIA</option>
                                                <option value="4">R.U.C</option>
                                                <option value="5">PASAPORTE</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label>DOCUMENTO<span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="documento" v-model="documento" id="documento" placeholder="Numero Documento" required autocomplete="off" minlength="8" maxlength="15" ref="documento">
                                        </div>
                                    </div><br>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label><i class="fa fa-calendar"></i> DESDE</label>
                                            <input type="date" class="form-control input-sm" id="fechai" value="<?php echo date('Y-m-d');?>" autocomplete="off">
                                        </div>
                                        <div class="col-md-6">
                                            <label><i class="fa fa-calendar"></i> HASTA</label>
                                            <input type="date" class="form-control input-sm" id="fechaf" value="<?php echo date('Y-m-d');?>" autocomplete="off">
                                        </div>
                                    </div><hr>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button type="button" style="border-radius: 2rem" class="btn btn-primary btn-block" v-bind:disabled="estado==1" v-on:click="enviar_consulta()"><i class="fa fa-check"></i> ENVIAR CONSULTA</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="row">
                                        <div class="col-md-12 table-responsive" style="height:420px;overflow-y:scroll;background: #f9f9f9;padding: 2rem 4rem 4rem 4rem;margin-left: 3rem;border: 1px solid #f3f3f3">
                                            <table class="table table-striped">
                                                <thead>
                                                    <th>COMPROBANTE</th>
                                                    <th>FECHA EMISION</th>
                                                    <th>MONTO S/</th>
                                                    <th style="width: 200px">DESCARGAR ARCHIVO</th>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="(dato,index) in detalle" v-if="detalle!=''">
                                                        <td><b style="font-size:15px;">{{dato.seriecomprobante}} - {{dato.nrocomprobante}}</b></td>
                                                        <td><b style="font-size:15px;">{{dato.fechacomprobante}}</b></td>
                                                        <td><b style="font-size:15px;" v-bind:class="[dato.estado==0 ? '' : 'text-success']">S/. {{dato.importe}}</b></td>
                                                        <td>
                                                            <a v-bind:href="'facturacion/formato/consulta/'+dato.codkardex" target="_blank" class="btn btn-xs btn-danger"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> PDF</a>
                                                            <a v-bind:href="'facturacion/formato/descargarxml/'+dato.nombre_xml+'?codkardex='+dato.codkardex" target="_blank" class="btn btn-xs btn-success"><i class="fa fa-file-code-o" aria-hidden="true"></i> XML</a>
                                                            <a v-bind:href="'facturacion/formato/descargarzip?ruta='+dato.ruta_cdr+'&nombre='+dato.nombre_xml" target="_blank" v-if="dato.codcomprobantetipo!=12" class="btn btn-xs btn-warning"><i class="fa fa-file-archive-o" aria-hidden="true"></i> CDR</a>
                                                        </td>
                                                    </tr>
                                                    <tr v-if="detalle==''">
                                                        <td colspan="4">TABLA SIN RESULTADOS</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> 
            </div>
        </div>

        <script src="<?php echo base_url();?>public/js/jquery.min.js"></script>
        <script src="<?php echo base_url();?>public/js/bootstrap.js"></script>
        <script> var url = "<?php echo base_url();?>";</script>
        <script src="<?php echo base_url();?>public/js/notify/pnotify.js"></script>
        <script src="<?php echo base_url();?>public/js/notify/pnotify.buttons.js"></script>
        <script src="<?php echo base_url();?>public/js/notify/pnotify.nonblock.js"></script>
        <script src="<?php echo base_url();?>public/js/sweetalert.min.js"></script>
        <script src="<?php echo base_url();?>phuyu/phuyu_login.js"> </script>
        <script src="<?php echo base_url();?>public/js/vue/vue.js"></script>
        <script src="<?php echo base_url();?>public/js/vue/vue-resource.min.js"></script>
        <script src="<?php echo base_url();?>public/js/validacion.js"></script>

        <script>
            var sistema_url = window.location;

            phuyu_controller = sistema_url;
        </script>
        <script src="<?php echo base_url();?>phuyu/consultacomprobante.js"></script>

    </body>
</html>