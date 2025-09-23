<div id="phuyu_operacion">

	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">
		<input type="hidden" id="comprobante" value="<?php echo $comprobantes[0]['codcomprobantetipo'];?>">
		<input type="hidden" id="serie" value="<?php echo $sucursal[0]['seriecomprobante'];?>">
		<input type="hidden" id="formato" value="<?php echo $_SESSION['phuyu_formato'];?>">
	<div class="phuyu_body">
		<div class="card">
			<div class="card-body">
				<div class="row form-group">
					<div class="col-md-6 col-xs-12"> <h5><b class="text-danger">GUIA DE REMISION N° {{campos.nro}}</b></h5> </div>
				</div>		
				<div class="row form-group">
				    <div class="col-md-2 col-xs-12">
				    	<label>SERIE</label>
			        	<select class="form-select requeridogeneral" id="seriecomprobante" v-model="campos.seriecomprobante" v-on:change="phuyu_correlativo()">
				    		<option v-for="dato in series" v-bind:value="dato.seriecomprobante"> 
				    			{{dato.seriecomprobante}}
				    		</option>
				    	</select>
				    </div>
			    	<div class="col-md-2 col-xs-12">
				    	<label>FECHA EMISION<span class="text-danger">*</span></label>
				    	<input type="date" class="form-control requeridogeneral" id="fechaguia" name="fechaguia" value="<?php echo date('Y-m-d');?>" v-on:change="validar_general()">
				    </div>
				    <div class="col-md-2 col-xs-12">
				    	<label>FECHA TRASLADO<span class="text-danger">*</span></label>
				    	<input type="date" class="form-control" id="fechatraslado" name="fechatraslado" value="<?php echo date('Y-m-d');?>" v-on:change="validar_general()">
				    </div>
				    <div class="col-md-6 col-xs-12">
				    	<label>DESTINATARIO<span class="text-danger">*</span></label>
				    	<select class="form-select" name="codpersona" id="codpersona" required>
		    				<option value="">SELECCIONE DESTINATARIO</option>
		    			</select>
				    </div>
			    </div>
			    <div class="row form-group">
			    	<div class="col-md-3">
			    		<label>MODALIDAD DE TRASLADO<span class="text-danger">*</span></label>
			    		<select class="form-select" name="modotraslado" v-model="campos.codmodalidadtraslado" id="modotraslado" required>
			    			<option value="">SELECCIONE</option>
			    			<?php
				    			foreach ($modalidades as $key => $value) { ?>
				    				<option value="<?php echo $value["codmodalidadtraslado"];?>" codigo="<?php echo $value["oficial"]?>">
				    					<?php echo $value["modalidadtraslado"];?>
				    				</option>
				    			<?php }
				    		?>
			    		</select>
			    	</div>
			    	<div class="col-md-3">
			    		<label>MOTIVO DE TRASLADO<span class="text-danger">*</span></label>
			    		<select class="form-select" v-model="campos.codmotivotraslado" name="motivotraslado" id="motivotraslado" v-on:change="motivotraslado()" required>
			    			<option value="">SELECCIONE</option>
			    			<?php
				    			foreach ($motivos as $key => $value) { ?>
				    				<option value="<?php echo $value["codmotivotraslado"];?>" codigo="<?php echo $value["oficial"]?>">
				    					<?php echo $value["descripcion"];?>
				    				</option>
				    			<?php }
				    		?>
			    		</select>
			    	</div>
			    	<div class="col-md-6">
			    		<label>DESCRIPCION DEL MOTIVO DE TRASLADO</label>
			    		<input type="text" maxlength="120" class="form-control" v-model="campos.descripcionmotivo" name="descripcionmotivo" id="descripcionmotivo" >
			    	</div>
			    </div>
			    <div class="row form-group almacenes" style="display: none">
			    	<div class="col-md-6">
			    		<label>ALMACEN DE PARTIDA<span class="text-danger">*</span></label>
			    		<select class="form-control" v-model="campos.almacenpartida" id="almacen_principal" name="almacen_principal">
			    			<?php
				    			foreach ($almacenes_1 as $key => $value) { ?>
				    				<option value="<?php echo $value["codalmacen"];?>" >
				    					<?php echo $value["descripcion"];?>
				    				</option>
				    			<?php }
				    		?>
			    		</select>
			    	</div>
			    	<div class="col-md-6">
			    		<label>ALMACEN DE LLEGADA<span class="text-danger">*</span></label>
			    		<select class="form-select" v-model="campos.almacendestino" id="almacen_llegada" name="almacen_llegada">
			    			<?php
				    			foreach ($almacenes_2 as $key => $value) { ?>
				    				<option value="<?php echo $value["codalmacen"];?>" >
				    					<?php echo $value["descripcion"];?>
				    				</option>
				    			<?php }
				    		?>
			    		</select>
			    	</div>
			    </div>
			    <div class="row form-group">
			    	<div class="col-md-2">
			    		<label>PESO TOTAL<span class="text-danger">*</span></label>
			    		<input type="number" class="form-control" v-model="campos.peso" name="pesobultos" id="pesobultos" v-on:keyup="validar_general()" value="0" required>
			    	</div>
			    	<div class="col-md-2">
			    		<label>TOTAL DE BULTOS</label>
			    		<input type="number" class="form-control" name="totalbultos" v-model="campos.nropaquetes" id="totalbultos" required value="0">
			    	</div>
			    	<div class="col-md-2">
			    		<label>NRO CONTENEDOR<span class="text-danger">*</span></label>
			    		<input type="number" class="form-control requeridogeneral" v-model="campos.nrocontenedor" name="nrocontenedor" id="nrocontenedor" v-on:keyup="validar_general()" value="1">
			    	</div>
			    	<div class="col-md-6">
			    		<label>OBSERVACIONES</label>
			    		<input type="text" class="form-control" name="observacion" v-model="campos.observaciones" id="observacion" maxlength="150"></textarea>
			    	</div>
			    </div>
			    <div class="row form-group row_remitente" style="display: none">
			    	<div class="col-md-12">
			    		<div class="w-100">
				    		<label>REMITENTE</label>
				    		<select class="form-control" name="codremitente" id="codremitente">
			    				<option value="">SELECCIONE REMITENTE</option>
			    			</select>
			    		</div>
			    	</div>
			    </div><hr>

                <div class="row form-group">
			    	<div class="col-md-5 col-xs-10">
			    		<label>UBIGEO DE PARTIDA<span class="text-danger">*</span></label>
			    		<input type="text" class="form-control" id="ubigeopartida" name="ubigeopartida" disabled>
			    		<input type="hidden" id="idubigeopartida" v-model="campos.codubigeopartida" name="idubigeopartida">
			    	</div>
			    	<div class="col-md-1 mt-4">
						<label>&nbsp;</label>
						<button type="button" class="btn btn-info btn-icon" v-on:click="phuyu_bsubigeo()" title="BUSCAR UBIGEO"> 
							<i data-acorn-icon="search"></i>
						</button>
					</div>
					<div class="col-md-6 col-xs-10">
			    		<label>DIRECCION DE PARTIDA<span class="text-danger">*</span></label>
			    		<input type="text" class="form-control" v-model="campos.direccionpartida" id="puntopartida" name="puntopartida" maxlength="100" required>
			    	</div>
			    </div>
			    <div class="row form-group">
			    	<div class="col-md-5 col-xs-10">
			    		<label>UBIGEO DE LLEGADA<span class="text-danger">*</span></label>
			    		<input type="text" class="form-control" id="ubigeollegada" name="ubigeollegada" disabled>
			    		<input type="hidden" id="idubigeollegada" v-model="campos.codubigeollegada" name="idubigeollegada">
			    	</div>
			    	<div class="col-md-1 mt-4">
						<label>&nbsp;</label>
						<button type="button" class="btn btn-info btn-icon" v-on:click="phuyu_bsubigeollegada()" title="BUSCAR UBIGEO DE LLEGADA"> 
							<i data-acorn-icon="search"></i>
						</button>
					</div>
					<div class="col-md-6 col-xs-10">
			    		<label>DIRECCION DE LLEGADA<span class="text-danger">*</span></label>
			    		<input type="text" class="form-control" id="puntollegada" name="puntollegada" v-model="campos.direccionllegada" maxlength="100" required>
			    	</div>
			    </div><hr>
                <div class="row form-group">
					<div class="col-md-5 col-xs-10">
						<div class="w-100">
							<label>TRANSPORTISTA<span class="text-danger">*</span></label>
			    			<select class="form-control" name="codtransportista" id="codtransportista" required>
			    				<option value="">SELECCIONE TRANSPORTISTA</option>
			    			</select>
			    		</div>
					</div>
					<div class="col-md-1 mt-4">
						<label>&nbsp;</label>
						<button type="button" class="btn btn-success btn-icon" v-on:click="phuyu_addtransportista()" title="AGREGAR TRANSPORTISTA"> 
							<i data-acorn-icon="user"></i>
						</button>
					</div>
					<div class="col-md-5 col-xs-10">
						<label>CONDUCTOR<span class="text-danger">*</span></label>
		    			<select class="form-control" name="codconductor" id="codconductor" required>
		    				<option value="">SELECCIONE CONDUCTOR</option>
		    			</select>
					</div>
					<div class="col-md-1 mt-4">
						<label>&nbsp;</label>
						<button type="button" class="btn btn-success btn-icon" v-on:click="phuyu_addconductor()" title="AGREGAR CONDUCTOR"> 
							<i data-acorn-icon="user"></i>
						</button>
					</div>
				</div><hr>
				<div class="row form-group">
					<div class="col-md-6 col-xs-12">
						<label>NRO PLACA VEHICULO<span class="text-danger">*</span></label>
						<select class="form-control" name="codvehiculo" id="codvehiculo" required >
		    				<option value="">SELECCIONE VEHICULO</option>
		    			</select>
					</div>
					<div class="col-md-6 col-xs-12">
						<label>CONSTANCIA DE INSCRIPCION</label>
						<input type="text" class="form-control" id="constancia" v-model.trim="campos.constancia" autocomplete="off" maxlength="100" autocomplete="off" placeholder="Constancia" >
					</div>
				</div><hr>
			
				<div class="row form-group">
					<!--<div class="col-md-2" style="margin-top: 2rem">
						<button type="button" class="btn btn-success btn-block" v-on:click="phuyu_ventas()">BUSCAR VENTA</button>
					</div>-->
					<div class="col-md-3 btnproducto" style="margin-top: 2rem;margin-left: 2rem">
						<button type="button" class="btn btn-success btn-block" v-on:click="phuyu_item()">BUSCAR PRODUCTOS</button>
					</div>
					<div class="col-md-3 btnventa" style="margin-top: 2rem;margin-left: 2rem">
						<button type="button" class="btn btn-success btn-block" v-on:click="phuyu_itemventa()">BUSCAR VENTAS</button>
					</div>
					<div class="col-md-3 btncompra" style="margin-top: 2rem;margin-left: 2rem">
						<button type="button" class="btn btn-success btn-block" v-on:click="phuyu_itemcompra()">BUSCAR COMPRAS</button>
					</div>
				</div>
				<div class="row form-group table-responsive scroll-phuyu-view">
					<div class="col-md-7">
						<table class="table table-striped" style="font-size: 11px">
							<thead>
								<tr>
									<th width="40%">PRODUCTO</th>
									<th width="19%">UNIDAD</th>
									<th width="20%">CANTIDAD</th>
									<th width="20%">PESO UNIT.</th>
									<th width="1%"> <i class="fa fa-trash-o"></i> </th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="(dato,index) in detalle">
									<td style="font-size:10px;">{{dato.producto}}</td>
									<td>
										<select class="form-select number unidad" v-model="dato.codunidad" id="codunidad">
											<template v-for="(unidads, und) in dato.unidades">
												<option v-bind:value="unidads.codunidad" v-if="unidads.factor==1" selected>
													{{unidads.descripcion}}
												</option>
												<option v-bind:value="unidads.codunidad" v-if="unidads.factor!=1">
													{{unidads.descripcion}}
												</option>
											</template>
										</select>
									</td>
									<td>
										<input type="number" step="0.0001" class="form-control number" v-model.number="dato.cantidad" min="0.0001" required>
									</td>
									<td>
										<input type="number" step="0.0001" class="form-control number" v-model.number="dato.pesoitem" min="0" required>
									</td>
									<td> 
										<button type="button" class="btn btn-danger btn-block btn-xs" style="margin-bottom:-1px;" v-on:click="phuyu_deleteitem(index,dato)">
											<b>X</b> 
										</button>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="col-md-1"></div>
					<div class="col-md-4">
						<table class="table table-striped" style="font-size: 11px">
							<thead>
								<tr>
									<th width="99%">COMPROBANTE</th>
									<th width="1%"> <i class="fa fa-trash-o"></i> </th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="(dato,index) in detallecomprobante">
									<td style="font-size:10px;">{{dato.tipo}}: {{dato.seriecomprobante}}-{{dato.nrocomprobante}}</td>
									<td> 
										<button type="button" class="btn btn-danger btn-block btn-xs" style="margin-bottom:-1px;" v-on:click="phuyu_deleteitemcomprobante(index,dato)">
											<b>X</b> 
										</button>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div><br>
				<div class="row mb-2">
					<div class="col-md-12" align="center">
						<button type="button" class="btn btn-warning btn-lg" v-on:click="phuyu_venta()"> 
							<b> <i class="fa fa-plus-square"></i> REALIZAR NUEVA GUIA</b> 
						</button>
						<button type="button" class="btn btn-danger btn-lg" v-on:click="phuyu_atras()"> 
							CANCELAR GUIA
						</button>
						<button type="submit" class="btn btn-primary btn-lg" v-bind:disabled="estado==1"> 
							<b><i class="fa fa-save"></i> GUARDAR GUIA</b> 
						</button>
					</div>
				</div>
			</div>
		</div>
	    </div>
	</form>

<div class="modal" id="modal-ubigeo-partida" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header modal_phuyu_titulo">
        <h3 class="modal-title" id="exampleModalLabel">Seleccionar Ubigeo</h3>
      </div>
      <div class="modal-body">
        <div class="md-card-content">
            <label>Departamento</label>
            <select class="form-select" id="dep_par" v-on:change="phuyu_prov_part('pro_par')">
                <?php 
                    foreach ($departamentopartida as $key => $value) { ?>
                        <option value="<?php echo $value['ubidepartamento'];?>"><?php echo $value["departamento"];?></option>
                    <?php }
                ?>
            </select><br>
            <label>Provincia</label>
            <select class="form-select" id="pro_par" v-on:change="phuyu_dist_part('dis_par')">
            </select><br>
            <label>Distrito</label>
            <select class="form-select" id="dis_par">
            </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" style="border:1px solid #ddd;color:#000 !important" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="aceptar_ubigeo_partida" v-on:click="aceptar_ubigeo_partida()">Aceptar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="modal-ubigeo-llegada" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="exampleModalLabel">Seleccionar Ubigeo</h3>
      </div>
      <div class="modal-body">
        <div class="md-card-content">
            <label>Departamento</label>
            <select class="form-select" id="dep_lle" v-on:change="phuyu_prov_lleg('pro_lle')">
                <?php 
                    foreach ($departamentollegada as $key => $value) { ?>
                        <option value="<?php echo $value['ubidepartamento'];?>"><?php echo $value["departamento"];?></option>
                    <?php }
                ?>
            </select><br>
            <label>Provincia</label>
            <select class="form-select" id="pro_lle" v-on:change="phuyu_dist_lleg('dis_lle')">
            </select><br>
            <label>Distrito</label>
            <select class="form-select" id="dis_lle">
            </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" style="border: 1px solid #ddd; color: #000 !important" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="aceptar_ubigeo_llegada" v-on:click="aceptar_ubigeo_llegada()">Aceptar</button>
      </div>
    </div>
  </div>
</div>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_guias/nuevo.js"> </script>
<script src="<?php echo base_url();?>phuyu/phuyu_guias/selects.js"> </script>

<script>
	var pantalla = jQuery(document).height(); $("#reportes_modal").css({height: pantalla - 65});
	if (typeof AcornIcons !== 'undefined') {
      new AcornIcons().replace();
    }
    if (typeof Icons !== 'undefined') {
      const icons = new Icons();
    }
</script>