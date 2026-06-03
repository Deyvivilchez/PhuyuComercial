var phuyu_datos = new Vue({
	el: "#phuyu_datos",
	data: {
		cargando:true, campos:campos, cajas:[], almacenes:[], comprobantes: [],tipoconsulta:1,detalle:[],totales:[]
	},
	methods: {
		modal_clientes: function(){
			$("#modal_clientes").modal('show');
			this.$http.post(url+phuyu_controller+"/form_cliente").then(function(data){
				$("#cuerpoclientes").empty().html(data.body);
			});
		},
		phuyu_infocliente: function(codpersona){
			this.campos.codpersona = codpersona;
		},
		phuyu_fecha: function(){
			this.campos.fechadesde = $("#fechadesde").val(); 
			this.campos.fechahasta = $("#fechahasta").val(); 
		},
		phuyu_cajas: function(){
			if(phuyu_controller=="reportes/vendedores"){
				return false;
			}
			this.campos.cajas = [];
			if (this.campos.codsucursal==0) {
				this.campos.codcaja = 0; this.ver_grafico();
			}else{
				this.$http.get(url+"caja/controlcajas/phuyu_cajas/"+this.campos.codsucursal).then(function(data){
					this.cajas = data.body;
				});
				this.$http.get(url+"caja/controlcajas/phuyu_almacenes/"+this.campos.codsucursal).then(function(data){
					this.almacenes = data.body;
				});
			}
		},
		ver_consulta: function(){
			this.tipoconsulta=2;
			phuyu_sistema.phuyu_inicio();
			this.phuyu_comprobantes();
			if (this.comprobantes.length==0) {
				phuyu_sistema.phuyu_noti("DEBE SELECCIONAR UN TIPO DE COMPROBANTE","PARA EL REPORTE DE VENTAS","error"); return false;
			}
			this.phuyu_fecha();
			if (this.campos.fechadesde>this.campos.fechahasta) {
				phuyu_sistema.phuyu_noti("LA FECHA DESDE DEBE SER MAYOR","QUE LA FECHA HASTA","error"); return false;
			}
			var datos = this.campos;
			var tipos = this.comprobantes;
			this.$http.post(url+phuyu_controller+"/consulta_reporte_ventas",{datos,tipos}).then(function(data){
				this.detalle = data.body.lista;
				this.totales = data.body.totalreporte;
				phuyu_sistema.phuyu_fin();
			});
		},
		mas_reportes: function(){
			$("#fechadesde_mas").val($("#fechadesde").val()); 
			$("#fechahasta_mas").val($("#fechahasta").val()); 

			$("#modal_reportes").modal("show");
		},

		pdf_productos_vendidos: function(){
			this.phuyu_fecha();
			window.open(url+phuyu_controller+"/pdf_productos_vendidos?datos="+encodeURIComponent(JSON.stringify(this.campos)),"_blank");
		},
		excel_productos_vendidos: function(){
			this.phuyu_fecha();
			window.open(url+phuyu_controller+"/excel_productos_vendidos?datos="+encodeURIComponent(JSON.stringify(this.campos)),"_blank");
		},
		consulta_vendedores: function(){
			phuyu_sistema.phuyu_inicio();
			this.phuyu_fecha();
			this.$http.post(url+phuyu_controller+"/consulta_reporte_vendedores",this.campos).then(function(data){
				this.detalle = data.body.lista;
				this.totales = data.body.totalreporte;
				phuyu_sistema.phuyu_fin();
			});
		},
		pdf_ventas_vendedor: function(){
			this.phuyu_fecha();
			window.open(url+phuyu_controller+"/pdf_ventas_vendedor?datos="+encodeURIComponent(JSON.stringify(this.campos)),"_blank");
		},
		pdf_ventas_vendedor_resumen: function(){
			this.phuyu_fecha();
			window.open(url+phuyu_controller+"/pdf_ventas_vendedor?tipo='resumen'&datos="+encodeURIComponent(JSON.stringify(this.campos)),"_blank");
		},
		excel_ventas_vendedor: function(){
			this.phuyu_fecha();
			window.open(url+phuyu_controller+"/excel_ventas_vendedor?tipo='resumen'&datos="+encodeURIComponent(JSON.stringify(this.campos)),"_blank");
		},
		excel_ventas_vendedor_resumen: function(){
			this.phuyu_fecha();
			window.open(url+phuyu_controller+"/excel_ventas_vendedor?datos="+encodeURIComponent(JSON.stringify(this.campos)),"_blank");
		},
		pdf_ventas_cliente: function(){
			this.phuyu_fecha();
			window.open(url+phuyu_controller+"/pdf_ventas_cliente?datos="+encodeURIComponent(JSON.stringify(this.campos)),"_blank");
		},
		pdf_ventas_cliente_detallado: function(){
			this.phuyu_fecha();
			window.open(url+phuyu_controller+"/pdf_ventas_cliente_detallado?datos="+encodeURIComponent(JSON.stringify(this.campos)),"_blank");
		},
		excel_ventas_cliente: function(){
			this.phuyu_fecha();
			window.open(url+phuyu_controller+"/excel_ventas_cliente?datos="+encodeURIComponent(JSON.stringify(this.campos)),"_blank");
		},
		excel_ventas_cliente_detallado: function(){
			this.phuyu_fecha();
			window.open(url+phuyu_controller+"/excel_ventas_cliente?tipo='detalle'&datos="+encodeURIComponent(JSON.stringify(this.campos)),"_blank");
		},

		phuyu_comprobantes: function(){
			this.comprobantes = []; list = this;
			$("input[name='comprobantes']:checked").each(function() {
				list.comprobantes.push({"codcomprobantetipo":$(this).val()});
	        });
		},
		pdf_reporte_ventas: function(estado){
			this.phuyu_comprobantes(); this.campos.estado = estado;
			console.log(this.comprobantes)
			if (this.comprobantes.length==0) {
				phuyu_sistema.phuyu_noti("DEBE SELECCIONAR UN TIPO DE COMPROBANTE","PARA EL REPORTE DE VENTAS","error"); return false;
			}
			this.campos.fechadesde = $("#fechadesde_mas").val(); 
			this.campos.fechahasta = $("#fechahasta_mas").val(); 

			var datos = "datos="+encodeURIComponent(JSON.stringify(this.campos))+"&tipos="+JSON.stringify(this.comprobantes);
			window.open(url+phuyu_controller+"/pdf_reporte_ventas?"+datos,"_blank");
		},
		pdf_reporte_ventas_det: function(estado){
			this.phuyu_comprobantes(); this.campos.estado = estado;
			if (this.comprobantes.length==0) {
				phuyu_sistema.phuyu_noti("DEBE SELECCIONAR UN TIPO DE COMPROBANTE","PARA EL REPORTE DE VENTAS DETALLADO","error"); return false;
			}
			this.campos.fechadesde = $("#fechadesde_mas").val(); 
			this.campos.fechahasta = $("#fechahasta_mas").val(); 

			var datos = "datos="+encodeURIComponent(JSON.stringify(this.campos))+"&tipos="+JSON.stringify(this.comprobantes);
			window.open(url+phuyu_controller+"/pdf_reporte_ventas_det?"+datos,"_blank");
		},
		pdf_contable_ventas: function(){
			this.phuyu_comprobantes();
			if (this.comprobantes.length==0) {
				phuyu_sistema.phuyu_noti("DEBE SELECCIONAR UN TIPO DE COMPROBANTE","PARA EL REPORTE DE VENTAS","error"); return false;
			}
			this.campos.fechadesde = $("#fechadesde_mas").val(); 
			this.campos.fechahasta = $("#fechahasta_mas").val(); 

			var datos = "datos="+encodeURIComponent(JSON.stringify(this.campos))+"&tipos="+JSON.stringify(this.comprobantes);
			window.open(url+phuyu_controller+"/pdf_contable_ventas?"+datos,"_blank");
		},
		excel_contable_ventas: function(){
			this.phuyu_comprobantes();
			if (this.comprobantes.length==0) {
				phuyu_sistema.phuyu_noti("DEBE SELECCIONAR UN TIPO DE COMPROBANTE","PARA EL REPORTE DE VENTAS","error"); return false;
			}
			this.campos.fechadesde = $("#fechadesde_mas").val(); 
			this.campos.fechahasta = $("#fechahasta_mas").val(); 

			var datos = "datos="+encodeURIComponent(JSON.stringify(this.campos))+"&tipos="+JSON.stringify(this.comprobantes);
			window.open(url+phuyu_controller+"/excel_contable_ventas?"+datos,"_blank");
		},
		phuyu_productospendientespedido: function(tipo){
			this.phuyu_fecha();
			var datos = "datos="+encodeURIComponent(JSON.stringify(this.campos));
			window.open(url+phuyu_controller+"/"+tipo+"_productospedidos?"+datos,"_blank");
		}
	},
	created: function(){
		this.phuyu_cajas(); phuyu_sistema.phuyu_fin();
	}
});