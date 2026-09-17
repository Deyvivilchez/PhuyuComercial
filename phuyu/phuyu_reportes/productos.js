var phuyu_datos = new Vue({
	el: "#phuyu_datos",
	data: {
		total:0,registro:0, campos:campos, datos:[], existencias:[], existencias_a:[], recoger:[], compraventas:[], consultar:{"precios":0,"stock":0,"kardex":0},
		filtro:{"codalmacen":"","codproducto":"","codunidad":"","fechadesde":"","fechahasta":"","fecha":"","operacion":0,"codmoneda":0}, descripcion:""
	},
	methods: {
		phuyu_fecha: function(){
			this.campos.fecha = $("#fecha").val();
			this.campos.fechad = $("#fechad").val();
		},
		phuyu_kardex: function(producto){
			this.filtro.codalmacen = this.campos.codalmacen;
			this.filtro.codproducto = producto.codproducto;
			this.filtro.codunidad = producto.codunidad;
			this.filtro.fechadesde = $("#fechadesde_k").val();
			this.filtro.fechahasta = $("#fechahasta_k").val();

			this.$http.post(url+phuyu_controller+"/phuyu_kardex",this.filtro).then(function(data){
				this.existencias = data.body.existencias; this.existencias_a = data.body.existencias_a; 
				$("#producto_kardex").text(producto.codigo+' - '+producto.descripcion+" | "+producto.unidad); 
				$("#modal_kardex").modal('show');
			});
		},
		phuyu_kardex_1: function(){
			this.filtro.fechadesde = $("#fechadesde_k").val();
			this.filtro.fechahasta = $("#fechahasta_k").val();

			this.$http.post(url+phuyu_controller+"/phuyu_kardex",this.filtro).then(function(data){
				this.existencias = data.body.existencias; this.existencias_a = data.body.existencias_a;
			});
		},
		phuyu_cambiar_fecha: function(dato){
			$("#producto_kardex_fecha").text("NRO DEL COMPROBANTE "+dato.seriecomprobante+"-"+dato.nrocomprobante);
			$("#c_fechakardex").val(dato.fechakardex); $("#c_fechacomprobante").val(dato.fechacomprobante); $("#c_codkardex").val(dato.codkardex);
			$("#modal_kardex_fecha").modal("show");
		},
		phuyu_cambiar_fecha_1: function(){
			this.$http.post(url+phuyu_controller+"/cambiar_fecha",{"codkardex":$("#c_codkardex").val(),"fechakardex":$("#c_fechakardex").val(),"fechacomprobante":$("#c_fechacomprobante").val()}).then(function(data){
				this.phuyu_kardex_1(); $("#modal_kardex_fecha").modal("hide");
			});
		},
		phuyu_kardex_pdf: function(){
			this.filtro.fechadesde = $("#fechadesde_k").val();
			this.filtro.fechahasta = $("#fechahasta_k").val();

			window.open(url+phuyu_controller+"/kardexproducto_pdf?datos="+JSON.stringify(this.filtro),"_blank");
		},
		phuyu_kardex_excel: function(){
			this.filtro.fechadesde = $("#fechadesde_k").val();
			this.filtro.fechahasta = $("#fechahasta_k").val();

			window.open(url+phuyu_controller+"/kardexproducto_excel?datos="+JSON.stringify(this.filtro),"_blank");
		},

		buscar_productos:function(){
			this.consultar.precios = 1; phuyu_sistema.phuyu_inicio(); 
			this.$http.post(url+phuyu_controller+"/buscar_productos",this.campos).then(function(data){
				this.datos = data.body; phuyu_sistema.phuyu_fin();
				if (typeof AcornIcons !== 'undefined') {
			      new AcornIcons().replace();
			    }
			    if (typeof Icons !== 'undefined') {
			      const icons = new Icons();
			    }
			});
		},
		generar_utilidades:function(){
			phuyu_sistema.phuyu_inicio(); 
			this.$http.post(url+phuyu_controller+"/generar_utilidad",{'codproducto': $("#codproducto").val(),'fechai':$("#fechadesde").val(),'fechaf':$("#fechahasta").val(),'codalmacen':this.campos.codalmacen}).then(function(data){
				this.datos = data.body; 
				this.total = data.body[0]['total'];phuyu_sistema.phuyu_fin();
			});
		},
		pdf_kardexproductos: function(){
			var phuyu_url = url+phuyu_controller+"/pdf_kardexproductos?datos="+JSON.stringify(this.campos); 
			//$("#phuyu_pdf").attr("src",phuyu_url); $("#modal_reportes").modal("show");
			window.open(phuyu_url,"_blank");
		},
		excel_kardexproductos: function(){
			window.open(url+phuyu_controller+"/excel_kardexproductos?datos="+JSON.stringify(this.campos),"_blank");
		},
		pdf_precios: function(){
			var phuyu_url = url+phuyu_controller+"/pdf_precios?datos="+JSON.stringify(this.campos); 
			//$("#phuyu_pdf").attr("src",phuyu_url); $("#modal_reportes").modal("show");
			window.open(phuyu_url,'_blank');
		},
		pdf_precios_stock: function(){
			var phuyu_url = url+phuyu_controller+"/pdf_precios_stock?datos="+JSON.stringify(this.campos); 
			//$("#phuyu_pdf").attr("src",phuyu_url); $("#modal_reportes").modal("show");
			window.open(phuyu_url,'_blank');
		},
		excel_precios: function(){
			window.open(url+phuyu_controller+"/excel_precios?datos="+JSON.stringify(this.campos),"_blank");
		},
		stock_general_excel: function(){
			window.open(url+phuyu_controller+"/stock_general_excel?datos="+JSON.stringify(this.campos),"_blank");
		},

		compras_producto: function(){
			if (this.registro==0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UN REGISTRO", "SELECCIONAR UN PRODUCTO PARA VER LAS COMPRAS REALIZADAS!!!","error");
			}else{
				this.filtro.operacion = 2;
				this.filtro.fechadesde = $("#fechadesde_cv").val();
				this.filtro.fechahasta = $("#fechahasta_cv").val();
				this.filtro.codmoneda = $("#codmoneda").val();
				
				this.$http.post(url+phuyu_controller+"/phuyu_compraventas",this.filtro).then(function(data){
					this.compraventas = data.body;
					$("#producto_compraventa").text("LISTA DE COMPRAS | "+this.codigo+' - '+this.descripcion); 
					$("#modal_comprasventas").modal('show');
				});
			}
		},
		ventas_producto: function(){
			if (this.registro==0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UN REGISTRO", "SELECCIONAR UN PRODUCTO PARA VER LAS VENTAS REALIZADAS!!!","error");
			}else{
				this.filtro.operacion = 20;
				this.filtro.fechadesde = $("#fechadesde_cv").val();
				this.filtro.fechahasta = $("#fechahasta_cv").val();
				this.filtro.codmoneda = $("#codmoneda").val();

				this.$http.post(url+phuyu_controller+"/phuyu_compraventas",this.filtro).then(function(data){
					this.compraventas = data.body;
					$("#producto_compraventa").text("LISTA DE VENTAS | "+this.codigo+' - '+this.descripcion); 
					$("#modal_comprasventas").modal('show');
				});
			}
		},
		phuyu_compraventas: function() {
			this.filtro.fechadesde = $("#fechadesde_cv").val();
			this.filtro.fechahasta = $("#fechahasta_cv").val();
			this.filtro.codmoneda = $("#codmoneda").val();

			this.$http.post(url+phuyu_controller+"/phuyu_compraventas",this.filtro).then(function(data){
				this.compraventas = data.body;
				$("#producto_compraventa").text(producto.descripcion+" | "+producto.unidad); 
				$("#modal_comprasventas").modal('show');
			});
		},
		phuyu_seleccionar: function(producto){
			this.registro = producto.codproducto; this.codigo = producto.codigo; this.descripcion = producto.descripcion+" | "+producto.unidad;
			this.filtro.codalmacen = this.campos.codalmacen;
			this.filtro.codproducto = producto.codproducto;
			this.filtro.codunidad = producto.codunidad;
		},

		phuyu_recoger: function(producto, operacion){
			this.filtro.codalmacen = this.campos.codalmacen;
			this.filtro.codproducto = producto.codproducto;
			this.filtro.codunidad = producto.codunidad;
			this.filtro.operacion = operacion;

			this.$http.post(url+phuyu_controller+"/phuyu_recoger",this.filtro).then(function(data){
				this.recoger = data.body;
				$("#producto_recoger").text(producto.descripcion+" | "+producto.unidad); 
				$("#modal_recoger").modal('show');
			});
		},
		stock_general: function(){
			var phuyu_url = url+phuyu_controller+"/stock_general?datos="+JSON.stringify(this.campos); 
			//$("#phuyu_pdf").attr("src",phuyu_url); $("#modal_reportes").modal("show");
			window.open(phuyu_url,'_blank');
		},
		stock_valorizado: function(){
			var phuyu_url = url+phuyu_controller+"/stock_valorizado?datos="+JSON.stringify(this.campos); 
			window.open(phuyu_url,'_blank')
			//$("#phuyu_pdf").attr("src",phuyu_url); $("#modal_reportes").modal("show");
		},
		stock_valorizado_excel: function(){
			var phuyu_url = url+phuyu_controller+"/stock_valorizado_excel?datos="+JSON.stringify(this.campos); 
			window.open(phuyu_url,"_blank")
			//$("#phuyu_pdf").attr("src",phuyu_url); $("#modal_reportes").modal("show");
		}
	},
	created: function(){
		phuyu_sistema.phuyu_fin();
	}
});