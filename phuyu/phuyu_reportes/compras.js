var phuyu_datos = new Vue({
	el: "#phuyu_datos",
	data: {
		cargando:true, campos:campos, cajas:[],detalle:[],totales:[]
	},
	methods: {
		phuyu_fecha: function(){
			this.campos.fechadesde = $("#fechadesde").val(); 
			this.campos.fechahasta = $("#fechahasta").val(); 
		},
		phuyu_infocliente: function(codpersona){
			this.campos.codpersona = codpersona;
		},
		phuyu_cajas: function(){
			this.campos.cajas = [];
			if (this.campos.codsucursal==0) {
				this.campos.codcaja = 0;
			}else{
				this.$http.get(url+"caja/controlcajas/phuyu_cajas/"+this.campos.codsucursal).then(function(data){
					this.cajas = data.body;
				});
			}
		},
		ver_consulta: function(){
			this.$http.post(url+phuyu_controller+"/consulta_reporte_compras",this.campos).then(function(data){
				this.detalle = data.body.lista;
				this.totales = data.body.totalreporte;
				phuyu_sistema.phuyu_fin();
			});
		},
		pdf_compras: function(){
			// window.open(url+phuyu_controller+"/pdf_compras?datos="+JSON.stringify(this.campos), "_blank"); //
			var phuyu_url = url+phuyu_controller+"/pdf_compras?datos="+JSON.stringify(this.campos); 
            $("#phuyu_pdf").attr("src",phuyu_url); $("#modal_reportes").modal("show");
		},
		pdf_compras_detallado: function(){
			// window.open(url+phuyu_controller+"/pdf_compras?datos="+JSON.stringify(this.campos), "_blank"); //
			var phuyu_url = url+phuyu_controller+"/pdf_reporte_compras_det?datos="+JSON.stringify(this.campos); 
            $("#phuyu_pdf").attr("src",phuyu_url); $("#modal_reportes").modal("show");
		},
		mas_reportes: function(){
			phuyu_sistema.phuyu_noti("OPCION PARA GENERAR REPORTES","MAS PERSONALIZADOS","success");
		},
		excel_compras: function(){
			this.phuyu_fecha();
			window.open(url+phuyu_controller+"/excel_compras?datos="+encodeURIComponent(JSON.stringify(this.campos)),"_blank");
		},
		excel_compras_detallado: function(){
			this.phuyu_fecha();
			window.open(url+phuyu_controller+"/excel_compras?tipo='resumen'&datos="+encodeURIComponent(JSON.stringify(this.campos)),"_blank");
		},
		phuyu_comprasproveedorpdf: function(){
			$("#modal_clientes").modal('hide');
			var phuyu_url = url+phuyu_controller+"/comprasproveedorpdf?datos="+JSON.stringify(this.campos); 
            $("#phuyu_pdf").attr("src",phuyu_url); $("#modal_reportes").modal("show");
		},
		phuyu_comprasproveedorpdfdet: function(){
			$("#modal_clientes").modal('hide');
			var phuyu_url = url+phuyu_controller+"/comprasproveedorpdfdet?datos="+JSON.stringify(this.campos); 
            $("#phuyu_pdf").attr("src",phuyu_url); $("#modal_reportes").modal("show");
		},
		phuyu_comprasproveedorexcel: function(){
			$("#modal_clientes").modal('hide');
			this.phuyu_fecha();
			window.open(url+phuyu_controller+"/comprasproveedorexcel?datos="+encodeURIComponent(JSON.stringify(this.campos)),"_blank");
		}
	},
	created: function(){
		this.phuyu_cajas(); phuyu_sistema.phuyu_fin();
	}
});