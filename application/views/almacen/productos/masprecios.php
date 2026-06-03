<style type="text/css">
	.selects{
		min-height: 8px;
		padding: 0.35rem 1.25rem 0.375rem 0.5rem;
	}
	.texto{
		min-height: 5px;
		padding-left: .4rem;
		padding-right: .3rem;
		padding-top: 0.18rem;
		padding-bottom: 0.18rem;
		font-size: .72rem !important;
		border-radius: .2rem;
	}
	.labeles{
		font-size: 0.63rem !important;
		padding-left: 0.3rem !important;
	}
</style>
<div id="phuyu_precios">
	<form id="formularioprecios" class="form-horizontal" v-on:submit.prevent="phuyu_guardarprecios()">
		<div class="row form-group" style="margin-bottom: .1rem !important;">
			<div class="col-xs-12">
				<div class="row form-group">
					<input type="hidden" id="codproducto" value="<?php echo $productosunidades[0]["codproducto"];?>" name="">
					<input type="hidden" id="codunidad" value="<?php echo $productosunidades[0]["codunidad"];?>" name="">
					<label class="col-sm-1 pt-2" style="text-align: right;">ALMACEN</label>
					<div class="col-sm-2">
						<select class="form-select selects" v-model="campos.codalmacen">
							<?php 
		    					foreach ($almacenes as $key => $value) {?>
		    						<option value="<?php echo $value["codalmacen"];?>"><?php echo $value["descripcion"];?></option>
		    					<?php }
		    				?>
						</select>
					</div>
					<label class="col-sm-1 pt-2" style="text-align: right;">MONEDA</label>
					<div class="col-sm-1">
						<input type="text" class="form-control texto" value="<?php echo $moneda; ?>" disabled name="">
					</div>
					<label class="col-sm-1 pt-2" style="text-align: right;">T. CAMBIO</label>
					<div class="col-sm-1">
						<input type="text" class="form-control texto" value="<?php echo $tipocambio;?>" disabled name="">
					</div>
					<div class="col-sm-5">
						<button type="submit" v-bind:disabled="estado==1" class="btn btn-primary btn-sm">GUARDAR</button>
						<button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal"> <b>CANCELAR</b> </button>
					</div>
				</div>
			</div>
		</div>

		<div class="card">
			<div class="card-body mt-1" style="padding:.7rem !important;">
				<h6 style="background-color: #c13d3d;padding: 0.1rem;text-align: center;color: #fff;font-weight: 700;">PRECIOS ACTUALES</h6>
				<div class="row">
					<div class="col-md-3" style="border-right: 1px solid #bfbebe;">
						<h6><b>PRECIOS COMPRA</b></h6>	
						<div class="row form-group" style="">
							<label class="col-sm-4 pt-2 labeles" style="text-align: right;">P. Compra</label>
							<div class="col-sm-8">
								<input type="number" class="form-control texto" name="" value="<?php echo $productosunidades[0]["preciocompra"];?>" disabled>
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-4 pt-2 labeles" style="text-align: right;">Igv Compra</label>
							<div class="col-sm-8">
								<input type="number" class="form-control texto" name="" value="<?php echo $productosunidades[0]["igvcompra"];?>" disabled>
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-4 pt-2 labeles" style="text-align: right;">Flete y Otr.</label>
							<div class="col-sm-8">
								<input type="number" class="form-control texto" name="" value="<?php echo $productosunidades[0]["fletecompra"];?>" disabled>
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-4 pt-2 labeles" style="text-align: right;">P. Costo</label>
							<div class="col-sm-8">
								<input type="number" class="form-control texto" name="" value="<?php echo $productosunidades[0]["preciocosto"];?>" disabled>
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-4 pt-2 labeles" style="text-align: right;">P.Costo+I.R</label>
							<div class="col-sm-8">
								<input type="number" class="form-control texto" name="" value="<?php echo $productosunidades[0]["preciocosto"];?>" disabled>
							</div>
						</div>
					</div>
					<div class="col-md-3" style="border-right: 1px solid #bfbebe;">
						<h6><b>P. VENTA PUBLICO</b></h6>	
						<div class="row form-group">
							<label class="col-sm-4 pt-2 labeles" style="text-align: right;">S/. Utilidad</label>
							<div class="col-sm-8">
								<input type="number" class="form-control texto" name="" value="<?php echo $productosunidades[0]["utilidad"];?>" disabled>
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-4 pt-2 labeles" style="text-align: right;">% Utilidad</label>
							<div class="col-sm-8">
								<input type="number" class="form-control texto" name="" value="<?php echo $productosunidades[0]["utilidadporc"];?>" disabled>
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-4 pt-2 labeles" style="text-align: right;">P. Venta</label>
							<div class="col-sm-8">
								<input type="number" class="form-control texto" name="" value="<?php echo $productosunidades[0]["pventapublico"];?>" disabled>
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-4 pt-2 labeles" style="text-align: right;">Igv Venta</label>
							<div class="col-sm-8">
								<input type="number" class="form-control texto" name="" value="<?php echo $productosunidades[0]["igvventa"];?>" disabled>
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-4 pt-2 labeles" style="text-align: right;">PVP</label>
							<div class="col-sm-8">
								<input type="number" class="form-control texto" name="" value="<?php echo $productosunidades[0]["pigvventapublico"];?>" disabled>
							</div>
						</div>
					</div>
					<div class="col-md-2" style="border-right: 1px solid #bfbebe;">
						<h6><b>P. VENTA MINIMO</b></h6>	
						<div class="row form-group">
							<label class="col-sm-5 pt-2 labeles" style="text-align: right;">S/. Utilidad</label>
							<div class="col-sm-7">
								<input type="number" class="form-control texto" name="" value="<?php echo $productosunidades[0]["pventaminutilidad"];?>" disabled>
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-5 pt-2 labeles" style="text-align: right;">% Utilidad</label>
							<div class="col-sm-7">
								<input type="number" class="form-control texto" name="" value="<?php echo $productosunidades[0]["pventaminutilidadporc"];?>" disabled>
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-5 pt-2 labeles" style="text-align: right;">P. Venta</label>
							<div class="col-sm-7">
								<input type="number" class="form-control texto" name="" value="<?php echo $productosunidades[0]["pventamin"];?>" disabled>
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-5 pt-2 labeles" style="text-align: right;">Igv Venta</label>
							<div class="col-sm-7">
								<input type="number" class="form-control texto" name="" value="<?php echo $productosunidades[0]["igvminimo"];?>" disabled>
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-5 pt-2 labeles" style="text-align: right;">PVP</label>
							<div class="col-sm-7">
								<input type="number" class="form-control texto" name="" value="<?php echo $productosunidades[0]["pventaminigv"];?>" disabled>
							</div>
						</div>
					</div>
					<div class="col-md-2" style="border-right: 1px solid #bfbebe;">
						<h6><b>P. V. X MAYOR</b></h6>	
						<div class="row form-group">
							<label class="col-sm-5 pt-2 labeles" style="text-align: right;">S/. Utilidad</label>
							<div class="col-sm-7">
								<input type="number" class="form-control texto" name="" value="<?php echo $productosunidades[0]["pventaxmayorutilidad"];?>" disabled>
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-5 pt-2 labeles" style="text-align: right;">% Utilidad</label>
							<div class="col-sm-7">
								<input type="number" class="form-control texto" name="" value="<?php echo $productosunidades[0]["pventaxmayorutilidadporc"];?>" disabled>
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-5 pt-2 labeles" style="text-align: right;">P. Venta</label>
							<div class="col-sm-7">
								<input type="number" class="form-control texto" name="" value="<?php echo $productosunidades[0]["pventaxmayor"];?>" disabled>
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-5 pt-2 labeles" style="text-align: right;">Igv Venta</label>
							<div class="col-sm-7">
								<input type="number" class="form-control texto" name="" value="<?php echo $productosunidades[0]["igvxmayor"];?>" disabled>
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-5 pt-2 labeles" style="text-align: right;">PVP</label>
							<div class="col-sm-7">
								<input type="number" class="form-control texto" name="" value="<?php echo $productosunidades[0]["pventaxmayorigv"];?>" disabled>
							</div>
						</div>
					</div>
					<div class="col-md-2">
						<h6><b>P. VENTA CREDITO</b></h6>	
						<div class="row form-group">
							<label class="col-sm-5 pt-2 labeles" style="text-align: right;">S/. Utilidad</label>
							<div class="col-sm-7">
								<input type="number" class="form-control texto" name="" value="<?php echo $productosunidades[0]["pventacreditoutilidad"];?>" disabled>
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-5 pt-2 labeles" style="text-align: right;">% Utilidad</label>
							<div class="col-sm-7">
								<input type="number" class="form-control texto" name="" value="<?php echo $productosunidades[0]["pventacreditoutilidadporc"];?>" disabled>
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-5 pt-2 labeles" style="text-align: right;">P. Venta</label>
							<div class="col-sm-7">
								<input type="number" class="form-control texto" name="" value="<?php echo $productosunidades[0]["pventacredito"];?>" disabled>
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-5 pt-2 labeles" style="text-align: right;">Igv Venta</label>
							<div class="col-sm-7">
								<input type="number" class="form-control texto" name="" value="<?php echo $productosunidades[0]["igvcredito"];?>" disabled>
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-5 pt-2 labeles" style="text-align: right;">PVP</label>
							<div class="col-sm-7">
								<input type="number" class="form-control texto" name="" value="<?php echo $productosunidades[0]["pventacreditoigv"];?>" disabled>
							</div>
						</div>
					</div>					
				</div>
			</div>
		</div>
		<div class="card mt-1">
			<div class="card-body" style="padding:.7rem !important;">
				<h6 style="background-color: #c13d3d;padding: 0.1rem;text-align: center;color: #fff;font-weight: 700;">PRECIOS MODIFICADOS</h6>
				<div class="row">
					<div class="col-md-3" style="border-right: 1px solid #bfbebe;">
						<h6><b>PRECIOS COMPRA</b></h6>	
						<div class="row form-group" style="">
							<label class="col-sm-4 pt-2 labeles" style="text-align: right;">P. Compra</label>
							<div class="col-sm-8">
								<input type="number" step="0.0001" class="form-control texto" name="" v-model="precios.preciocompra" v-on:keyup="phuyu_calcular(1,1)">
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-4 pt-2 labeles" style="text-align: right;">Igv Compra</label>
							<div class="col-sm-8">
								<input type="number" step="0.0001" class="form-control texto" name="" v-model="precios.igvcompra" v-on:keyup="phuyu_calcular(1,1)">
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-4 pt-2 labeles" style="text-align: right;">Flete y Otr.</label>
							<div class="col-sm-8">
								<input type="number" step="0.0001" class="form-control texto" name="" v-model="precios.fletecompra" v-on:keyup="phuyu_calcular(1,1)">
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-4 pt-2 labeles" style="text-align: right;">P. Costo</label>
							<div class="col-sm-8">
								<input type="number" step="0.0001" class="form-control texto" name="" v-model="precios.preciocosto"v-on:keyup="phuyu_calcular(1,2)">
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-4 pt-2 labeles" style="text-align: right;">P.Costo+I.R</label>
							<div class="col-sm-8">
								<input type="number" step="0.0001" class="form-control texto" name="" v-model="precios.preciocosto"v-on:keyup="phuyu_calcular(1,2)">
							</div>
						</div>
					</div>
					<div class="col-md-3" style="border-right: 1px solid #bfbebe;">
						<h6><b>P. VENTA PUBLICO</b></h6>	
						<div class="row form-group">
							<label class="col-sm-4 pt-2 labeles" style="text-align: right;">S/. Utilidad</label>
							<div class="col-sm-8">
								<input type="number" step="0.0001" class="form-control texto" name="" v-model="precios.utilidad" v-on:keyup="phuyu_calcular(2,1)">
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-4 pt-2 labeles" style="text-align: right;">% Utilidad</label>
							<div class="col-sm-8">
								<input type="number" step="0.0001" class="form-control texto" name="" v-model="precios.utilidadporc" v-model="precios.utilidad" v-on:keyup="phuyu_calcular(2,2)">
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-4 pt-2 labeles" style="text-align: right;">P. Venta</label>
							<div class="col-sm-8">
								<input type="number" step="0.0001" class="form-control texto" name="" v-model="precios.pventapublico" v-on:keyup="phuyu_calcular(2,3)">
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-4 pt-2 labeles" style="text-align: right;">Igv Venta</label>
							<div class="col-sm-8">
								<input type="number" step="0.0001" class="form-control texto" name="" v-model="precios.igvventa">
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-4 pt-2 labeles" style="text-align: right;">PVP</label>
							<div class="col-sm-8">
								<input type="number" step="0.0001" class="form-control texto" name="" v-model="precios.pigvventapublico" v-on:keyup="phuyu_calcular(2,4)">
							</div>
						</div>
					</div>
					<div class="col-md-2" style="border-right: 1px solid #bfbebe;">
						<h6><b>P. VENTA MINIMO</b></h6>	
						<div class="row form-group">
							<label class="col-sm-5 pt-2 labeles" style="text-align: right;">S/. Utilidad</label>
							<div class="col-sm-7">
								<input type="number" step="0.0001" class="form-control texto" name="" v-model="precios.pventaminutilidad" v-on:keyup="phuyu_calcular(3,1)">
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-5 pt-2 labeles" style="text-align: right;">% Utilidad</label>
							<div class="col-sm-7">
								<input type="number" step="0.0001" class="form-control texto" name="" v-model="precios.pventaminutilidadporc" v-on:keyup="phuyu_calcular(3,2)">
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-5 pt-2 labeles" style="text-align: right;">P. Venta</label>
							<div class="col-sm-7">
								<input type="number" step="0.0001" class="form-control texto" name="" v-model="precios.pventamin" v-on:keyup="phuyu_calcular(3,3)">
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-5 pt-2 labeles" style="text-align: right;">Igv Venta</label>
							<div class="col-sm-7">
								<input type="number" step="0.0001" class="form-control texto" name="" v-model="precios.igvminimo">
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-5 pt-2 labeles" style="text-align: right;">PVP</label>
							<div class="col-sm-7">
								<input type="number" step="0.0001" class="form-control texto" name="" v-model="precios.pventaminigv" v-on:keyup="phuyu_calcular(3,4)">
							</div>
						</div>
					</div>
					<div class="col-md-2" style="border-right: 1px solid #bfbebe;">
						<h6><b>P. V. X MAYOR</b></h6>	
						<div class="row form-group">
							<label class="col-sm-5 pt-2 labeles" style="text-align: right;">S/. Utilidad</label>
							<div class="col-sm-7">
								<input type="number" step="0.0001" class="form-control texto" name="" v-model="precios.pventaxmayorutilidad" v-on:keyup="phuyu_calcular(4,1)">
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-5 pt-2 labeles" style="text-align: right;">% Utilidad</label>
							<div class="col-sm-7">
								<input type="number" step="0.0001" class="form-control texto" name="" v-model="precios.pventaxmayorutilidadporc" v-on:keyup="phuyu_calcular(4,2)">
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-5 pt-2 labeles" style="text-align: right;">P. Venta</label>
							<div class="col-sm-7">
								<input type="number" step="0.0001" class="form-control texto" name="" v-model="precios.pventaxmayor" v-on:keyup="phuyu_calcular(4,3)">
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-5 pt-2 labeles" style="text-align: right;">Igv Venta</label>
							<div class="col-sm-7">
								<input type="number" step="0.0001" class="form-control texto" name="" v-model="precios.igvxmayor">
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-5 pt-2 labeles" style="text-align: right;">PVP</label>
							<div class="col-sm-7">
								<input type="number" step="0.0001" class="form-control texto" name="" v-model="precios.pventaxmayorigv" v-on:keyup="phuyu_calcular(4,4)">
							</div>
						</div>
					</div>
					<div class="col-md-2">
						<h6><b>P. VENTA CREDITO</b></h6>	
						<div class="row form-group">
							<label class="col-sm-5 pt-2 labeles" style="text-align: right;">S/. Utilidad</label>
							<div class="col-sm-7">
								<input type="number" step="0.0001" class="form-control texto" name="" v-model="precios.pventacreditoutilidad" v-on:keyup="phuyu_calcular(5,1)">
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-5 pt-2 labeles" style="text-align: right;">% Utilidad</label>
							<div class="col-sm-7">
								<input type="number" step="0.0001" class="form-control texto" name="" v-model="precios.pventacreditoutilidadporc" v-on:keyup="phuyu_calcular(5,2)">
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-5 pt-2 labeles" style="text-align: right;">P. Venta</label>
							<div class="col-sm-7">
								<input type="number" step="0.0001" class="form-control texto" name="" v-model="precios.pventacredito" v-on:keyup="phuyu_calcular(5,3)">
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-5 pt-2 labeles" style="text-align: right;">Igv Venta</label>
							<div class="col-sm-7">
								<input type="number" step="0.0001" class="form-control texto" name="" v-model="precios.igvcredito">
							</div>
						</div>
						<div class="row form-group">
							<label class="col-sm-5 pt-2 labeles" style="text-align: right;">PVP</label>
							<div class="col-sm-7">
								<input type="number" step="0.0001" class="form-control texto" name="" v-model="precios.pventacreditoigv" v-on:keyup="phuyu_calcular(5,4)">
							</div>
						</div>
					</div>					
				</div>
			</div>
		</div>
	</form>
</div>

<script>
	var phuyu_precios = new Vue({
		el: "#phuyu_precios",
		data: { estado :0,
			campos:{
                codalmacen:"<?php echo $_SESSION['phuyu_codalmacen'];?>",codproducto: $("#codproducto").val(), codunidad:$("#codunidad").val(),tipocambio:"<?php echo $tipocambio;?>"
			},
			precios:{
				preciocompra:"<?php echo $precio; ?>",igvcompra:"<?php echo $igv;?>",fletecompra:0,preciocosto:0,utilidad:0, utilidadporc:0,igvventa:0,pventapublico:0,pigvventapublico:0,pventamin:0,pventaminutilidad:0,pventaminutilidadporc:0,pventaminigv:0,igvminimo:0,pventaxmayor:0,pventaxmayorutilidad:0,pventaxmayorutilidadporc:0,pventaxmayorigv:0,igvxmayor:0,pventacredito:0,pventacreditoutilidad:0,pventacreditoutilidadporc:0,pventacreditoigv:0,igvcredito:0
			}
		},
		methods: {
			phuyu_otros: function(){				
				//PRECIO MINIMO
				this.precios.pventamin = this.precios.pventapublico;
				this.precios.pventaminutilidadporc = this.precios.utilidadporc;
				this.precios.pventaminutilidad = parseFloat(this.precios.utilidad).toFixed(4)
				this.precios.pventaminigv = this.precios.pigvventapublico;
				this.precios.igvminimo = this.precios.igvventa;
				//PRECIO X MAYOR
				this.precios.pventaxmayor = this.precios.pventapublico;
				this.precios.pventaxmayorutilidadporc = this.precios.utilidadporc;
				this.precios.pventaxmayorutilidad = parseFloat(this.precios.utilidad).toFixed(4)
				this.precios.pventaxmayorigv = this.precios.pigvventapublico;
				this.precios.igvxmayor = this.precios.igvventa;
				//PRECIO CREDITO
				this.precios.pventacredito = this.precios.pventapublico;
				this.precios.pventacreditoutilidadporc = this.precios.utilidadporc;
				this.precios.pventacreditoutilidad = parseFloat(this.precios.utilidad).toFixed(4)
				this.precios.pventacreditoigv = this.precios.pigvventapublico;
				this.precios.igvcredito = this.precios.igvventa;
			},
			phuyu_compras: function(subtipo){
				if(subtipo==1){
					this.precios.preciocosto = (parseFloat(this.precios.preciocompra) + parseFloat(this.precios.igvcompra) + parseFloat(this.precios.fletecompra)).toFixed(4);
				}else{
					this.precios.preciocompra = (parseFloat(this.precios.preciocosto) - parseFloat(this.precios.igvcompra) - parseFloat(this.precios.fletecompra)).toFixed(4);
				}
				//VENTA AL PUBLICO
				this.precios.pventapublico = (parseFloat(this.precios.preciocosto)+parseFloat(this.precios.utilidad)).toFixed(4);

				this.precios.utilidadporc = (parseFloat(this.precios.utilidad)*100/parseFloat(this.precios.preciocosto)).toFixed(4);

				this.precios.pigvventapublico = (parseFloat(this.precios.pventapublico) + parseFloat(this.precios.igvventa)).toFixed(4);
				this.phuyu_ventaminimo(1);
				this.phuyu_ventaxmayor(1);
				this.phuyu_ventacredito(1);
			},
			phuyu_ventapublico: function(subtipo){
				if(subtipo==1){
					this.precios.pventapublico = (parseFloat(this.precios.preciocosto)+parseFloat(this.precios.utilidad)).toFixed(4);

					this.precios.utilidadporc = (parseFloat(this.precios.utilidad)*100/parseFloat(this.precios.preciocosto)).toFixed(4);

					this.precios.pigvventapublico = (parseFloat(this.precios.pventapublico) + parseFloat(this.precios.igvventa)).toFixed(4);
					this.phuyu_otros();
				}
				else if(subtipo==2){
					this.precios.utilidad = (parseFloat(this.precios.preciocosto)*parseFloat(this.precios.utilidadporc)/100).toFixed(4);
					this.precios.pventapublico = (parseFloat(this.precios.preciocosto)+parseFloat(this.precios.utilidad)).toFixed(4);
					this.precios.pigvventapublico = (parseFloat(this.precios.pventapublico) + parseFloat(this.precios.igvventa)).toFixed(4);
					this.phuyu_otros();
				}else if(subtipo==3){
					this.precios.utilidad = (parseFloat(this.precios.pventapublico)-parseFloat(this.precios.preciocosto)).toFixed(4);
					this.precios.utilidadporc = (parseFloat(this.precios.utilidad)*100/parseFloat(this.precios.preciocosto)).toFixed(4);
					this.precios.pigvventapublico = (parseFloat(this.precios.pventapublico) + parseFloat(this.precios.igvventa)).toFixed(4);
					this.phuyu_otros();
				}else{
					this.precios.pventapublico = this.precios.pigvventapublico;
					this.precios.utilidad = (parseFloat(this.precios.pventapublico)-parseFloat(this.precios.preciocosto)).toFixed(4);
					this.precios.utilidadporc = (parseFloat(this.precios.utilidad)*100/parseFloat(this.precios.preciocosto)).toFixed(4);
					this.phuyu_otros();
				}
			},
			phuyu_ventaminimo: function(subtipo){
				if(subtipo==1){
					this.precios.pventamin = (parseFloat(this.precios.preciocosto)+parseFloat(this.precios.pventaminutilidad)).toFixed(4);
					this.precios.pventaminutilidadporc = (parseFloat(this.precios.pventaminutilidad)*100/parseFloat(this.precios.preciocosto)).toFixed(4);
					this.precios.pventaminigv = (parseFloat(this.precios.pventamin) + parseFloat(this.precios.igvminimo)).toFixed(4);
				}else if(subtipo==2){
					this.precios.pventaminutilidad = (parseFloat(this.precios.preciocosto)*parseFloat(this.precios.pventaminutilidadporc)/100).toFixed(4);
					this.precios.pventamin = (parseFloat(this.precios.preciocosto)+parseFloat(this.precios.pventaminutilidad)).toFixed(4);
					this.precios.pventaminigv = (parseFloat(this.precios.pventamin) + parseFloat(this.precios.igvminimo)).toFixed(4);
				}else if(subtipo==3){
					this.precios.pventaminutilidad = (parseFloat(this.precios.pventamin)-parseFloat(this.precios.preciocosto)).toFixed(4);
					this.precios.pventaminutilidadporc = (parseFloat(this.precios.pventaminutilidad)*100/parseFloat(this.precios.preciocosto)).toFixed(4);
					this.precios.pventaminigv = (parseFloat(this.precios.pventamin) + parseFloat(this.precios.igvminimo)).toFixed(4);
				}else{
					this.precios.pventamin = (parseFloat(this.precios.pventaminigv) - parseFloat(this.precios.igvminimo)).toFixed(4);
					this.precios.pventaminutilidad = (parseFloat(this.precios.pventamin)-parseFloat(this.precios.preciocosto)).toFixed(4);
					this.precios.pventaminutilidadporc = (parseFloat(this.precios.pventaminutilidad)*100/parseFloat(this.precios.preciocosto)).toFixed(4);
				}
			},
			phuyu_ventaxmayor: function(subtipo){
				if(subtipo==1){
					this.precios.pventaxmayor = (parseFloat(this.precios.preciocosto)+parseFloat(this.precios.pventaxmayorutilidad)).toFixed(4);

					this.precios.pventaxmayorutilidadporc = (parseFloat(this.precios.pventaxmayorutilidad)*100/parseFloat(this.precios.preciocosto)).toFixed(4);

					this.precios.pventaxmayorigv = (parseFloat(this.precios.pventaxmayor) + parseFloat(this.precios.igvxmayor)).toFixed(4);
				}else if(subtipo==2){
					this.precios.pventaxmayorutilidad = (parseFloat(this.precios.preciocosto)*parseFloat(this.precios.pventaxmayorutilidadporc)/100).toFixed(4);
					this.precios.pventaxmayor = (parseFloat(this.precios.preciocosto)+parseFloat(this.precios.pventaxmayorutilidad)).toFixed(4);
					this.precios.pventaxmayorigv = (parseFloat(this.precios.pventaxmayor) + parseFloat(this.precios.igvxmayor)).toFixed(4);
				}else if(subtipo==3){
					this.precios.pventaxmayorutilidad = (parseFloat(this.precios.pventaxmayor)-parseFloat(this.precios.preciocosto)).toFixed(4);
					this.precios.pventaxmayorutilidadporc = (parseFloat(this.precios.pventaxmayorutilidad)*100/parseFloat(this.precios.preciocosto)).toFixed(4);
					this.precios.pventaxmayorigv = (parseFloat(this.precios.pventaxmayor) + parseFloat(this.precios.igvxmayor)).toFixed(4);
				}else{
					this.precios.pventaxmayor = (parseFloat(this.precios.pventaxmayorigv) - parseFloat(this.precios.igvxmayor)).toFixed(4);
					this.precios.pventaxmayorutilidad = (parseFloat(this.precios.pventaxmayor)-parseFloat(this.precios.preciocosto)).toFixed(4);
					this.precios.pventaxmayorutilidadporc = (parseFloat(this.precios.pventaxmayorutilidad)*100/parseFloat(this.precios.preciocosto)).toFixed(4);
				}
			},
			phuyu_ventacredito: function(subtipo){
				if(subtipo==1){
					this.precios.pventacredito = (parseFloat(this.precios.preciocosto)+parseFloat(this.precios.pventacreditoutilidad)).toFixed(4);

					this.precios.pventacreditoutilidadporc = (parseFloat(this.precios.pventacreditoutilidad)*100/parseFloat(this.precios.preciocosto)).toFixed(4);

					this.precios.pventacreditoigv = (parseFloat(this.precios.pventacredito) + parseFloat(this.precios.igvcredito)).toFixed(4);
				}else if(subtipo==2){
					this.precios.pventacreditoutilidad = (parseFloat(this.precios.preciocosto)*parseFloat(this.precios.pventacreditoutilidadporc)/100).toFixed(4);
					this.precios.pventacredito = (parseFloat(this.precios.preciocosto)+parseFloat(this.precios.pventacreditoutilidad)).toFixed(4);
					this.precios.pventacreditoigv = (parseFloat(this.precios.pventacredito) + parseFloat(this.precios.igvcredito)).toFixed(4);
				}else if(subtipo==3){
					this.precios.pventacreditoutilidad = (parseFloat(this.precios.pventacredito)-parseFloat(this.precios.preciocosto)).toFixed(4);
					this.precios.pventacreditoutilidadporc = (parseFloat(this.precios.pventacreditoutilidad)*100/parseFloat(this.precios.preciocosto)).toFixed(4);
					this.precios.pventacreditoigv = (parseFloat(this.precios.pventacredito) + parseFloat(this.precios.igvcredito)).toFixed(4);
				}else{
					this.precios.pventacredito = (parseFloat(this.precios.pventacreditoigv) - parseFloat(this.precios.igvcredito)).toFixed(4);
					this.precios.pventacreditoutilidad = (parseFloat(this.precios.pventacredito)-parseFloat(this.precios.preciocosto)).toFixed(4);
					this.precios.pventacreditoutilidadporc = (parseFloat(this.precios.pventacreditoutilidad)*100/parseFloat(this.precios.preciocosto)).toFixed(4);
				}
			},
			phuyu_calcular: function(tipo,subtipo){
				if(tipo==1){
					this.phuyu_compras(subtipo);
				}else if(tipo==2){
					this.phuyu_ventapublico(subtipo);
				}
				else if(tipo==3){
					this.phuyu_ventaminimo(subtipo);
				}
				else if(tipo==4){
					this.phuyu_ventaxmayor(subtipo);
				}else{
					this.phuyu_ventacredito(subtipo);
				}
			},
			phuyu_guardarprecios: function(){
				this.estado = 1;
				this.$http.post(url+"almacen/productos/modificarprecios",{"campos":this.campos,"precios":this.precios}).then(function(data){
					if(data.body==1){
						phuyu_sistema.phuyu_noti("LOS PRECIOS SE ACTUALIZARON CORRECTAMENTE","success");
						$("#modal_masprecios").modal('hide');
					}
				});
			}
		},
		created: function(){
			this.phuyu_compras(1)
		}
	});
</script>

<script> 
    if (typeof AcornIcons !== 'undefined') {
      new AcornIcons().replace();
    }
    if (typeof Icons !== 'undefined') {
      const icons = new Icons();
    }
 function removerColumna(index){
 	$('.projects tr:eq('+index+') td').removeClass("columna");
} 
</script>