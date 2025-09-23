var phuyu_form = new Vue({
	el: "#phuyu_form",
	data: { estado: 0, editar:0, factor:0, campos: campos, familias:[], lineas:[], marcas:[], unidades:[], campos_1 : campos_1},
	methods: {
		phuyu_datos: function(){
			this.phuyu_extencion("almacen/familias",0); this.phuyu_extencion("almacen/lineas",0); this.phuyu_extencion("almacen/marcas",0);
		},
		phuyu_extencion: function(tabla,codigo){
			this.$http.get(url+"almacen/extenciones/lista/"+tabla).then(function(data){
				if (tabla=="almacen/familias") {
					this.familias = data.body; this.campos.codfamilia = codigo;
				}
				if (tabla=="almacen/lineas") {
					this.lineas = data.body; this.campos.codlinea = codigo;
				}
				if (tabla=="almacen/marcas") {
					this.marcas = data.body; this.campos.codmarca = codigo;
				}
			});
		},
		phuyu_activarstock: function(){
			if (this.campos.controlstock==1) {
				this.campos.controlstock = 0;
			}else{
				this.campos.controlstock = 1;
			}
		},
		phuyu_activaricbper: function(){
			if (this.campos.afectoicbper==1) {
				this.campos.afectoicbper = 0;
			}else{
				this.campos.afectoicbper = 1;
			}
		},
		phuyu_nuevo_extencion: function(tabla){
			this.$http.get(url+"almacen/extenciones/nuevo/"+tabla).then(function(data){
				$("#extencion_modal").empty().html(data.body); $("#modal_extencion").modal("show");
			});
		},

		phuyu_addunidad: function(){
			if(this.unidades.length==0){
				var factor = 1;
			}else{
				var factor = 0;
			}
			this.unidades.push({
	    		codunidad:1,
	    		factor:factor,
	    		preciocompra:0,
	    		pventapublico:0,
	    		pventamin:0,
	    		pventacredito:0,
	    		pventaxmayor:0,
	    		pventaadicional:0,
	    		codigobarra:"",
	    		eliminar : 0
	    	});
		},
		phuyu_ediunidad: function(index,unidad){
			this.campos_1.codunidad = unidad.codunidad; this.campos_1.factor = unidad.factor;
			this.campos_1.preciocompra = unidad.preciocompra; this.campos_1.pventapublico = unidad.pventapublico;
			this.campos_1.pventamin = unidad.pventamin; this.campos_1.pventacredito = unidad.pventacredito;
			this.campos_1.pventaxmayor = unidad.pventaxmayor; this.campos_1.pventaadicional = unidad.pventaadicional;
			this.campos_1.codigobarra = unidad.codigobarra;

			this.editar = 1; this.factor = unidad.factor; this.unidades.splice(index,1);
		},
		phuyu_deleteunidad: function(index,unidad){
			this.unidades.splice(index,1);
		},
		phuyu_unidades: function(unidad){
			this.$http.post(url+phuyu_controller+"/unidades", {"codregistro":phuyu_datos.registro}).then(function(data){
				this.unidades = data.body.unidades;
				if (this.campos.afectoicbper=="1") { 
					$("#afectoicbper_check").attr("checked","true");
				}else{
					$("#afectoicbper_check").removeAttr("checked");
				}
				var datos = eval(data.body.campos); 
				this.phuyu_extencion("almacen/familias",datos[0]["codfamilia"]); 
				this.phuyu_extencion("almacen/lineas",datos[0]["codlinea"]); 
				this.phuyu_extencion("almacen/marcas",datos[0]["codmarca"]);
			});
			this.editar = 1;
		},
		phuyu_checkigv: function(){
			this.campos.codafectacionigvcompra = $("#codafectoigv").val()
			this.campos.codafectacionigvventa = $("#codafectoigv").val();
		},
		phuyu_guardar: function(){
			if (this.unidades.length==0) {
				phuyu_sistema.phuyu_noti("REGISTRAR MINIMO UNA UNIDAD", "CON SUS PRECIOS DEL PRODUCTO","error"); return false;
			}

			var validad_um = 0; var factor_um = 0;
			for (var i = 0; i < this.unidades.length; i++) {
				var codunidad = this.unidades[i].codunidad;
				var factor = this.unidades[i].factor;
				var cantidad = 0; cantidadf = 0;
				for (var k = 0; k < this.unidades.length; k++) {
					if (codunidad == this.unidades[k].codunidad) {
						cantidad = cantidad + 1;
					}
					if(factor == this.unidades[k].factor){
						cantidadf = cantidadf + 1;
					}
				}
				if (parseInt(cantidad) > 1) {
					validad_um = 1; break;
				}
				if (parseInt(cantidadf) > 1) {
					factor_um = 1; break;
				}
			}
			if (validad_um == 1) {
				phuyu_sistema.phuyu_noti("Las unidades no deben repetirse", "Las unidades de medida deben ser diferentes", "error");
				return false;
			}
			if (factor_um == 1) {
				phuyu_sistema.phuyu_noti("Los factores no deben repetirse", "Los factores deben ser diferentes", "error");
				return false;
			}


		    this.campos.afectoigvcompra = 0;
		    if ($("#afectoigvcompra_check").is(":checked")) {
		    	this.campos.afectoigvcompra = 1;
		    }
		    this.campos.afectoigvventa = 0;
		    if ($("#afectoigvventa_check").is(":checked")) {
		    	this.campos.afectoigvventa = 1;
		    }
		    this.campos.afectoicbper = 0;
			if ($("#afectoicbper_check").is(":checked")) {
		    	this.campos.afectoicbper = 1;
		    }

			this.estado= 1;
			this.$http.post(url+"almacen/productos/guardar", {"campos":this.campos,"unidades":this.unidades}).then(function(data){
				if (data.body==0) {
					phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR AL REGISTRAR", "NO SE PUEDE REGISTRAR","error");
					phuyu_datos.phuyu_opcion(); this.phuyu_cerrar();
				}else{
					if (this.campos.codregistro=="") {
						phuyu_sistema.phuyu_alerta("GUARDADO CORRECTAMENTE", "UN NUEVO PRODUCTO EN EL SISTEMA","success");
					}else{
						phuyu_sistema.phuyu_alerta("EDITADO CORRECTAMENTE", "UN REGISTRO PRODUCTO EN EL SISTEMA","info");
					}

					if (phuyu_controller=="almacen/productos") {
						phuyu_datos.phuyu_opcion(); 
					}

					if (phuyu_controller=="compras/compras" || phuyu_controller=="almacen/ingresos") {
						$(".compose").removeClass("col-md-7").addClass("col-md-4");
						$("#phuyu_tituloform").text("BUSCAR PRODUCTO"); phuyu_sistema.phuyu_loader("phuyu_formulario",180); 
						this.$http.post(url+"almacen/productos/buscar/compras").then(function(data){
							$("#phuyu_formulario").empty().html(data.body);
							phuyu_sistema.phuyu_finloader("phuyu_formulario");
						});
					}else{
						this.phuyu_cerrar();
					}
				}
			}, function(){
				phuyu_sistema.phuyu_alerta("ATENCION USUARIO","ERROR DE RED (INTERNET)","error");
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			});
		},
		phuyu_codigobarra: function(e){
			if (e.keyCode == 13) {               
			    e.preventDefault(); return false;
			}
		},
		phuyu_cerrar: function(){
			$(".compose").slideToggle();
		}
	},
	created: function(){
		if (phuyu_controller=="almacen/productos") {
			if (phuyu_datos.registro!=0) {
				this.phuyu_unidades();
			}else{
				this.phuyu_datos(); this.phuyu_checkigv(); this.phuyu_addunidad();
			}
		}else{
			this.phuyu_datos(); this.phuyu_checkigv();this.phuyu_addunidad();
		}
	}
});