var phuyu_datos = new Vue({
	el: "#phuyu_datos",
	data: {
		cargando:true, campos:campos, total:0, almacenes:[], comprobantes: [],datos:[],
		filtro:{
			"tipo":1,"estado":1,"formato":1,"desde":"","hasta":""
		},detalleprestamo:[]
	},
	methods: {
		phuyu_modalprestamos: function(){
			$("#modal_prestamos").modal('show');
			this.phuyu_listaprestamos();
		},
		phuyu_listaprestamos: function(){
			this.filtro.desde = $("#fechai").val();
			this.filtro.hasta = $("#fechaf").val();
			this.$http.post(url+phuyu_controller+"/phuyu_listaprestamos",this.filtro).then(function(data){
				this.detalleprestamo = data.body;
			});
		},
		pdf_reporte_prestamo: function(){
			this.filtro.desde = $("#fechai").val();
			this.filtro.hasta = $("#fechaf").val();

			var datos = "datos="+encodeURIComponent(JSON.stringify(this.filtro));
			window.open(url+phuyu_controller+"/pdf_reporte_prestamo?"+datos,"_blank");
		},
		excel_reporte_prestamo: function(){
			this.filtro.desde = $("#fechai").val();
			this.filtro.hasta = $("#fechaf").val();

			var datos = "datos="+encodeURIComponent(JSON.stringify(this.filtro));
			window.open(url+phuyu_controller+"/excel_reporte_prestamo?"+datos,"_blank");
		},
		phuyu_fecha: function(){
			this.campos.fechadesde = $("#fechadesde").val(); 
			this.campos.fechahasta = $("#fechahasta").val(); 
		},
		phuyu_movimientos: function(){
			this.$http.get(url+"administracion/almacenes/phuyu_tipomovimiento/"+this.campos.tipo).then(function(data){
				$("#codmovimientotipo").empty().html(data.body);
			});		
		},
		phuyu_almacenes: function(){
			this.$http.get(url+"caja/controlcajas/phuyu_almacenes/"+this.campos.codsucursal).then(function(data){
				this.almacenes = data.body; 
			});
		},
		generar_reporte: function(){
			this.phuyu_fecha();
			if (this.campos.fechadesde>this.campos.fechahasta) {
				phuyu_sistema.phuyu_noti("LA FECHA DESDE DEBE SER MAYOR","QUE LA FECHA HASTA","error"); return false;
			}
			phuyu_sistema.phuyu_inicio(); 
			this.$http.post(url+phuyu_controller+"/generar_reporte",this.campos).then(function(data){
				this.datos = data.body.almacen; 
				this.total = data.body.total;phuyu_sistema.phuyu_fin();
			});
		},
		mas_reportes: function(){
			$("#fechadesde_mas").val($("#fechadesde").val()); 
			$("#fechahasta_mas").val($("#fechahasta").val()); 

			$("#modal_reportes").modal("show");
		},

		generar_pdf: function(){
			this.phuyu_fecha();
			window.open(url+phuyu_controller+"/pdf_reporte?datos="+encodeURIComponent(JSON.stringify(this.campos)),"_blank");
		},
		generar_excel: function(){
			this.phuyu_fecha();
			window.open(url+phuyu_controller+"/excel_reporte?datos="+encodeURIComponent(JSON.stringify(this.campos)),"_blank");
		}
	},
	created: function(){
		phuyu_sistema.phuyu_fin();
	}
});