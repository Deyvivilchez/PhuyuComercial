var phuyu_datos = new Vue({
	el: "#phuyu_datos",
	data: {campos:{buscar:"", estado:0,codpersona:0,codsucursal:$("#sucursal").val(),fechadesde:$("#fechad").val(),fechahasta:$("#fechah").val()}, productos:[], datos:[], totales_productos:[] },
	methods: {
		phuyu_fecha: function(){
			this.campos.fechadesde = $("#fechadesde").val(); 
			this.campos.fechahasta = $("#fechahasta").val(); 
		},
		buscar_producto_pedidos: function(){
			phuyu_sistema.phuyu_inicio(); 
			this.$http.post(url+phuyu_controller+"/buscar_producto_pedidos",this.campos).then(function(data){
				this.productos = data.body.lista; this.totales_productos = data.body.totales; phuyu_sistema.phuyu_fin();
			});
		},
		pdf_pedidos: function() {
			window.open(url+phuyu_controller+"/pdf_pedidos?datos="+JSON.stringify(this.campos),"_blank");
		},
		pdf_pedidos_detallado: function() {
			window.open(url+phuyu_controller+"/pdf_pedidos_detallado?datos="+JSON.stringify(this.campos),"_blank");
		},
		excel_proformas: function() {
			window.open(url+phuyu_controller+"/excel_proformas?datos="+JSON.stringify(this.campos),"_blank");
		},
		excel_proformas_detallado: function() {
			window.open(url+phuyu_controller+"/excel_proformas_detallado?datos="+JSON.stringify(this.campos),"_blank");
		},
		pdf_producto_proformas: function() {
			window.open(url+phuyu_controller+"/pdf_producto_proformas?datos="+JSON.stringify(this.campos),"_blank");
		},
		excel_producto_proformas: function() {
			window.open(url+phuyu_controller+"/excel_producto_proformas?datos="+JSON.stringify(this.campos),"_blank");
		},

		buscar_cliente_pedidos: function(){
			phuyu_sistema.phuyu_inicio(); this.campos.codpersona = $("#codpersona").val();
			this.$http.post(url+phuyu_controller+"/buscar_cliente_pedidos",this.campos).then(function(data){
				this.datos = data.body; phuyu_sistema.phuyu_fin();
			});
		},
		pdf_cliente_proformas: function() {
			this.campos.codpersona = $("#codpersona").val();
			window.open(url+phuyu_controller+"/pdf_cliente_proformas?datos="+JSON.stringify(this.campos),"_blank");
		},
		excel_cliente_proformas: function() {
			this.campos.codpersona = $("#codpersona").val();
			window.open(url+phuyu_controller+"/excel_cliente_proformas?datos="+JSON.stringify(this.campos),"_blank");
		}
	},
	created: function(){
		phuyu_sistema.phuyu_fin();
	}
});