<div id="phuyu_inventario">
	<div class="phuyu_header">
		<div class="row phuyu_header_title">
			<div class="col-md-3 col-xs-12" style="padding-top:5px;"> <h5>PRODUCTOS DEL INVENTARIO</h5> </div>

			<div class="col-md-3 col-xs-12" style="padding-top:5px;">
				<input type="text" class="form-control" v-model="buscar" placeholder="BUSCAR PRODUCTO . . .">
			</div>
			<div class="col-md-2 col-xs-12" style="padding-top:5px;">
				<select class="form-control" id="codlinea" v-on:change="phuyu_productos()">
					<option value="">TODAS LINEAS</option>
					<?php
		    			foreach ($lineas as $key => $value) { ?>
		    				<option value="<?php echo $value["codlinea"];?>">
		    					<?php echo $value["descripcion"];?>
		    				</option>
		    			<?php }
		    		?>
				</select>
			</div>
			<div class="col-md-4 col-xs-12" style="padding-top:5px;text-align:right;">
				<button type="button" class="btn btn-success" v-on:click="phuyu_masproductos()">CARGAR PRODUCTOS</button>
				<button type="button" class="btn btn-warning" v-on:click="phuyu_nuevoproducto()">NUEVO PRODUCTO</button>
			</div>
		</div>
	</div> <br>

	<div class="phuyu_body_row">
		<input type="hidden" name="codregistro" v-model="campos.codregistro">
		<div class="table-responsive scroll-phuyu-view" style="height:calc(100vh - 220px);padding:0px; overflow:auto;">
			<table class="table table-bordered">
				<thead>
					<tr>
						<th width="5px">#</th>
						<th width="5px">ID</th>
						<th width="10px">CODIGO</th>
						<th>PRODUCTO</th>
						<th width="10px">UNIDAD</th>
						<th>MARCA</th>
						<th width="10%">CANTIDAD</th>
						<th width="10%">P.&nbsp;COSTO</th>
						<th width="10%">P.&nbsp;VENTA</th>
						<th width="10%">IMPORTE</th>
						<th width="5px"><i class="fa fa-trash-o"></i></th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="(dato, index) in buscar_productos">
						<td>{{index + 1}}</td>
						<td>{{dato.codproducto}}</td>
						<td> <input type="text" class="phuyu-input-inv" v-model="dato.codigo" readonly> </td>
						<td> <input type="text" class="phuyu-input-inv" v-model="dato.descripcion" readonly> </td>
						<td>
							<input type="hidden" class="phuyu-input-inv" v-model="dato.codunidad" readonly>
							<input type="text" class="phuyu-input-inv" v-model="dato.unidad" readonly>
						</td>
						<td width="10px">{{dato.marca}}</td>
						<td> 
							<input type="number" step="0.001" class="phuyu-input-inv" v-model="dato.cantidad" v-on:keyup="phuyu_calcular(dato)"> 
						</td>
						<td> 
							<input type="number" step="0.01" class="phuyu-input-inv" v-model="dato.preciocosto" v-on:keyup="phuyu_calcular(dato)">
						</td>
						<td> <input type="number" step="0.01" class="phuyu-input-inv" v-model="dato.precioventa"> </td>
						<td> <input type="number" class="phuyu-input-inv" v-model="dato.importe" readonly> </td>
						<td>
							<button type="button" class="btn btn-danger btn-xs" v-on:click="phuyu_itemquitar(index, dato)"><i class="fa fa-trash-o"></i></button>
						</td>
					</tr>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="8"> <center> <b>TOTAL COSTO (S/. IMPORTE VALORIZADO)</b> </center> </td>
						<td> <input type="number" class="phuyu-input-inv" v-model="campos.importe" readonly> </td>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
	<div class="text-center"> <br>
		<button type="button" class="btn btn-success" v-on:click="phuyu_guardar()" v-bind:disabled="estado==1">GUARDAR CAMBIOS</button>
		<button type="button" class="btn btn-danger" v-on:click="phuyu_cerrar()">CERRAR</button>
		<button type="button" class="btn btn-warning" v-on:click="phuyu_actualizarprecios()" v-bind:disabled="estado==1">ACTUALIZAR PRECIOS EN PRODUCTOS</button>
	</div>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_inventarios/inventario.js"></script>