var phuyu_datos = new Vue({
	el: "#phuyu_datos",
	data: {
		cargando: true, campos:campos, estado_detallado: 0, estado_movimientos:0, conceptos:[],
		saldocaja:{"ingresos":"","egresos":"","totalingresos":0,"totalegresos":0,"total":0}, detallado:[], movimientos:[]
	},
	methods: {
		phuyu_infocliente: function(codpersona){
			this.campos.codpersona = codpersona
		},
		phuyu_fecha: function(){
			this.campos.codpersona = $("#codpersona").val();
			this.campos.fecha_desde = $("#fecha_desde").val();
			this.campos.fecha_hasta = $("#fecha_hasta").val();
			this.campos.fecha_detallado = $("#fecha_detallado").val();
		},
		phuyu_conceptos: function(){
			this.conceptos = []; list = this;
			$("input[name='conceptos']:checked").each(function() {
				list.conceptos.push({"codconcepto":$(this).val()});
	        });
		},
		caja_detallado: function(){
			this.phuyu_conceptos();
			if (this.conceptos.length==0) {
				phuyu_sistema.phuyu_noti("DEBE SELECCIONAR AL MENOS UN CONCEPTO","PARA EL REPORTE DE CAJA","error"); return false;
			}
			phuyu_sistema.phuyu_inicio(); this.phuyu_fecha(); this.estado_detallado = 1; this.estado_movimientos = 0;
			this.$http.post(url+phuyu_controller+"/caja_detallado",{"campos":this.campos,"conceptos":this.conceptos}).then(function(data){
				this.detallado = data.body.lista; phuyu_sistema.phuyu_fin();
				this.saldocaja.ingresos = data.body.ingresos; this.saldocaja.egresos = data.body.egresos;
				this.saldocaja.totalingresos = data.body.totalingresos; this.saldocaja.totalegresos = data.body.totalegresos;
				this.saldocaja.total = data.body.total;
			});
		},
		modal_conceptos: function(){
			$("#modal_conceptos").modal("show");
		},
		phuyu_marcar: function(){
			if ($("#marcar").is(":checked")) {
				$('input[name^="conceptos"]').prop("checked", true);
		    }else{
		    	$('input[name^="conceptos"]').prop("checked", false);
		    }
		},
		reporte_movimientos: function(){
			this.phuyu_conceptos();
			if (this.conceptos.length==0) {
				phuyu_sistema.phuyu_noti("DEBE SELECCIONAR AL MENOS UN CONCEPTO","PARA EL REPORTE DE MOVIMIENTOS","error"); return false;
			}
			phuyu_sistema.phuyu_inicio(); this.phuyu_fecha(); this.estado_detallado = 0; this.estado_movimientos = 1;
			this.$http.post(url+phuyu_controller+"/reporte_movimientos",{"campos":this.campos,"conceptos":this.conceptos}).then(function(data){
				this.movimientos = data.body.lista; phuyu_sistema.phuyu_fin();
				this.saldocaja.totalingresos = data.body.ingresos; this.saldocaja.totalegresos = data.body.egresos;
				this.saldocaja.total = data.body.total;
			});
		},
		reporte_movimientos_anulados: function(){
			this.phuyu_conceptos();
			if (this.conceptos.length==0) {
				phuyu_sistema.phuyu_noti("DEBE SELECCIONAR AL MENOS UN CONCEPTO","PARA EL REPORTE DE MOVIMIENTOS ANULADOS","error"); return false;
			}
			phuyu_sistema.phuyu_inicio(); this.phuyu_fecha(); this.estado_detallado = 0; this.estado_movimientos = 1;
			this.$http.post(url+phuyu_controller+"/reporte_movimientos_anulados",{"campos":this.campos,"conceptos":this.conceptos}).then(function(data){
				this.movimientos = data.body.lista; phuyu_sistema.phuyu_fin();
				this.saldocaja.totalingresos = data.body.ingresos; this.saldocaja.totalegresos = data.body.egresos;
				this.saldocaja.total = data.body.total;
			});
		},
		pdf_caja: function(){
			if (this.estado_detallado==0 && this.estado_movimientos==0) {
				phuyu_sistema.phuyu_noti("SELECCIONA UN REPORTE","CAJA DETALLADO O REPORTE DE MOVIMIENTOS","error");
			}else{
				if (this.estado_detallado==1) {
					this.campos.reporte = 1;
				}else{
					this.campos.reporte = 2;
				}
				var phuyu_url = url+phuyu_controller+"/pdf_caja?datos="+JSON.stringify(this.campos); 
            	$("#phuyu_pdf").attr("src",phuyu_url); $("#modal_reportes").modal("show");
			}
		},
		excel_caja(){
			window.open(url+phuyu_controller+"/excel_caja?datos="+JSON.stringify(this.campos),"_blank");
		}
	},
	created: function(){
		phuyu_sistema.phuyu_fin();
	}
});