<div id="phuyu_unidades">
	<div class="row">
		<div class="col-12 col-md-6">
            <h1 class="mb-0 pb-0 display-4" id="title">Productos x Unidades</h1>
            <nav class="breadcrumb-container d-inline-block" aria-label="breadcrumb">
              <ul class="breadcrumb pt-0">
                <li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
                <li class="breadcrumb-item"><a href="javascript:;">PXU</a></li>
              </ul>
            </nav>
        </div>
	</div>
	<div class="phuyu_body">
		<div class="card">
	
			<div class="card-body">
				<div class="row">
					<div class="col-sm-12 col-md-5 col-lg-4 col-xxl-2 mb-1">
	                    <div class="d-inline-block float-md-start me-1 mb-1 search-input-container w-100 shadow bg-foreground">
	                      <input class="form-control datatable-search" v-model="buscar" placeholder="BUSCAR REGISTRO . . ." />
	                      <span class="search-magnifier-icon">
	                        <i data-acorn-icon="search"></i>
	                      </span>
	                      <span class="search-delete-icon d-none">
	                        <i data-acorn-icon="close"></i>
	                      </span>
	                    </div>
	                </div>
	                <div class="col-sm-12 col-md-7 col-lg-8 col-xxl-10 text-end mb-1">
	                    <div class="d-inline-block me-0 me-sm-3 float-start float-md-none">
	                      <!-- Add Button Start -->
	                        <button type="button" class="btn btn-info btn-icon" v-on:click="cambiar_unidad()">CAMBIAR UNIDAD</button>
	                      
				    		<button type="button" class="btn btn-warning btn-icon" v-on:click="productos_almacen()">ASIGNAR ALMACENES</button>

				    		<button type="button" class="btn btn-success btn-icon" v-on:click="actualizar_stock()">ACTUALIZAR STOCK</button>
	                      <!-- Delete Button End -->
	                    </div>
	                </div>
			    </div><br>
				<div class="table-responsive lista scroll-phuyu-view" style="height:300px;overflow:auto;overflow-x:hidden;">
					<table class="table table-striped" style="font-size: 11px;">
						<thead>
							<tr>
								<th width="3%"> # </th>
								<th width="3%"> <i class="fa fa-circle-o-notch"></i> </th>
								<th width="6%">CODIGO</th>
								<th width="25%">PRODUCTO</th>
								<th width="7%">UNIDAD</th>
								<th width="6%">STOCK</th>
								<th width="7%">P.COSTO</th>
								<th width="7%" style="background:#23c6c8;color:#fff;">S/.&nbsp;COSTO</th>
								<th width="6%">P.VENTA</th>
								<th width="6%" style="background:#1ab394;color:#fff;">S/.&nbsp;VENTA</th>
								<th width="7%">P.MIN.</th>
								<th width="8%" style="background:#f8ac59;color:#fff;">S/.&nbsp;P. MIN.</th>
								<th width="7%">P.XMAYOR</th>
								<th width="8%" style="background:#f8ac59;color:#fff;">S/.&nbsp;XMAYOR</th>
								<th width="7%">P.CREDI</th>
								<th width="7%" style="background:#8b8aeb;color:#fff;">S/.&nbsp;CREDITO</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="(dato, index) in buscar_productos">
								<td><input type="hidden" class="phuyu-input-inv" v-model="dato.codproducto" readonly>{{dato.nro}}</td>
								<td> <input type="radio" name="phuyu_seleccionar" v-on:click="phuyu_marcar(dato)" style="height:15px;width:15px;"> </td>
								<td style="font-size:10px;">{{dato.codigo}}</td>
								<td style="font-size:10px;">{{dato.descripcion}}</td>
								<td>
									<input type="hidden" v-model="dato.codunidad" readonly>
									{{dato.unidad}}
								</td>
								<td>{{dato.stock}}</td>
								<td><input type="text" class="form-control _number" v-model="dato.preciocosto"></td>
								<td><input type="text" class="form-control _number" v-model="dato.costo" readonly></td>
								<td><input type="text" class="form-control _number" v-model="dato.precioventa" v-on:keyup="phuyu_calcularitem(dato)"></td>
								<td><input type="text" class="form-control _number" v-model="dato.venta" readonly></td>

								<td><input type="text" class="form-control _number" v-model="dato.preciomin"></td>
								<td><input type="text" class="form-control _number" v-model="dato.minimo" readonly></td>
								<td><input type="text" class="form-control _number" v-model="dato.precioxmayor" readonly></td>
								<td><input type="text" class="form-control _number" v-model="dato.mayor"></td>
								<td><input type="text" class="form-control _number" v-model="dato.pventacredito"></td>
								<td><input type="text" class="form-control _number" v-model="dato.credito" readonly></td>
							</tr>
						</tbody>
						<tfoot>
							<tr v-for="dato in totales">
								<td colspan="7" style="text-align:right;"> <b>TOTALES GENERALES</b> </td>
								<td><b>{{dato.costo}}</b></td> <td></td>
								<td><b>{{dato.venta}}</b></td> <td></td>
								<td><b>{{dato.minimox}}</b></td> <td></td>
								<td><b>{{dato.mayor}}</b></td> <td></td>
								<td><b>{{dato.credito}}</b></td>
							</tr>
							<tr v-for="dato in totales">
								<td colspan="7" style="text-align:right;"> <b>RESUMEN TOTAL (S/. VENTA - S/. COSTO)</b> </td>
								<td colspan="5"><b>{{dato.total}}</b></td>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		</div>
	</div>

	<div id="modal_cambiar_unidad" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header modal-phuyu-titulo">
					<h5 class="modal-title"> <b>{{campos.codigo}} - {{campos.descripcion}} | UNIDAD MEDIDA: {{campos.unidad}}</b> </h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-6 col-xs-12 text-center">
							<h5><b>UNIDAD ACTUAL</b></h5> <hr>
							<h4><span class="label label-danger">UNIDAD MEDIDA: {{campos.unidad}}</span></h4> <br>

							<ul class="list-inline widget_tally">
								<li>
									<p> <span class="month"><b>FACTOR MEDIDA</b></span> <span class="count">{{campos.factor}}</span> </p>
								</li>
                                <li>
                                  	<p> <span class="month"><b>STOCK ACTUAL</b></span> <span class="count">{{campos.stock}}</span> </p>
                                </li>
                                <li>
                                  	<p> <span class="month"><b>PRECIO COSTO</b></span> <span class="count">{{campos.preciocosto}}</span> </p>
                                </li>
                                <li>
                                  	<p> <span class="month"><b>PRECIO VENTA</b></span> <span class="count">{{campos.precioventa}}</span> </p>
                                </li>
                                <li>
                                  	<p> <span class="month"><b>PRECIO MINIMO</b></span> <span class="count">{{campos.preciomin}}</span> </p>
                                </li>
                            </ul>
						</div>
						<div class="col-md-6 col-xs-12">
							<h5 class="text-center"><b>UNIDAD NUEVA</b></h5> <hr>
							<h5>SELECCIONE NUEVA UNIDAD</h5>
							<select class="form-select" id="codunidad">
								<option value="">SELECCIONE UNIDAD</option>
								<?php 
									foreach ($unidades as $key => $value) { ?>
										<option value="<?php echo $value['codunidad'];?>"><?php echo $value["descripcion"]."(OFICIAL: ".$value["oficial"].")";?></option>
									<?php }
								?>
							</select> <br>
							<div class="alert alert-danger text-center">
								ATENCION USUARIO: AL CAMBIAR LA UNIDAD DE MEDIDA, SE REEMPLAZARA EN LAS VENTAS, COMPRAS, KARDEX E INVENTARIO
							</div>
							<button type="button" class="btn btn-primary btn-block" v-on:click="guardar_cambiar_unidad" v-bind:disabled="estado==1">CAMBIAR UNIDAD DE MEDIDA</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_almacen/unidades.js"></script>

<script> 
	var div_altura = jQuery(document).height(); var productos = div_altura - 200; $(".lista").css("height",productos+"px");
</script>