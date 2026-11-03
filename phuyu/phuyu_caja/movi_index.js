var phuyu_datos = new Vue({
	el: "#phuyu_datos",
	data: {
		cargando: true, registro:0, buscar: "", datos: [], transferencias:[],
		transferencias_pendientes: Number(document.getElementById("phuyu_datos").getAttribute("data-transferencias-pendientes")) || 0,
		campos_t:{"codmovimiento":"","codpersona":"","codcaja":0,"codtipopago":0,"importe":0,"codcomprobantetipo":0,
		"seriecomprobante":"","nrocomprobante":"","fechadocbanco":"","nrodocbanco":""},
		paginacion: {"total":0, "actual":1, "ultima":0, "desde":0, "hasta":0}, offset: 3,
		fecha_desde: "", fecha_hasta: "", codtipopago: 0, tipomovimiento: 0, vista: "actual", consulta: 0,
	},
	computed: {
		totales_pagina() {
			const campos = {pagado: "importe_pagado", entregado: "importe_entregado", vuelto: "vuelto", total: "importe_r"};
			const totales = {};
			Object.keys(campos).forEach((nombre) => {
				const centimos = this.datos.reduce((suma, dato) => suma + Math.round(Number(dato[campos[nombre]]) * 100), 0);
				totales[nombre] = centimos / 100;
			});
			return totales;
		},
		phuyu_actual: function(){
			return this.paginacion.actual;
		},
		phuyu_paginas: function(){
			if (!this.paginacion.hasta) {
				return [];
			}
			var desde = this.paginacion.actual - this.offset;
			if (desde < 1) {
				desde = 1;
			}
			var hasta = desde + (this.offset * 2);
			if (hasta >= this.paginacion.ultima) {
				hasta = this.paginacion.ultima;
			}

			var paginas = [];
			while(desde <= hasta){
				paginas.push(desde); desde++;
			}
			return paginas;
		}
	},
	methods: {
		formato_importe(importe) {
			if (importe === null || importe === undefined || importe === "" || !Number.isFinite(Number(importe))) return "—";
			return new Intl.NumberFormat("es-PE", {style: "currency", currency: "PEN"}).format(Number(importe));
		},
		cambiar_vista(vista) {
			this.vista = vista;
			this.buscar = ""; this.codtipopago = 0; this.tipomovimiento = 0;
			const elemento = document.getElementById("phuyu_datos");
			this.fecha_desde = vista === "historial" ? elemento.getAttribute("data-inicio-mes") : "";
			this.fecha_hasta = vista === "historial" ? elemento.getAttribute("data-hoy") : "";
			this.phuyu_buscar();
		},
		filtros_movimientos() {
			return {vista: this.vista, buscar: this.buscar, fecha_desde: this.fecha_desde, fecha_hasta: this.fecha_hasta, codtipopago: this.codtipopago, tipomovimiento: this.tipomovimiento};
		},
		validar_fechas(obligatorias) {
			if ((obligatorias && (!this.fecha_desde || !this.fecha_hasta)) || (this.fecha_desde && this.fecha_hasta && this.fecha_desde > this.fecha_hasta)) {
				phuyu_sistema.phuyu_noti("REVISE LAS FECHAS", "Seleccione un rango completo y ordenado de fechas.", "warning");
				return false;
			}
			return true;
		},
		exportar_detallado(formato) {
			if (!this.validar_fechas(true)) return;
			const filtros = this.filtros_movimientos();
			filtros.vista = "historial";
			filtros.desde = this.fecha_desde; filtros.hasta = this.fecha_hasta;
			filtros._exportado = Date.now();
			window.open(url + phuyu_controller + "/exportar_" + formato + "_detallado?" + new URLSearchParams(filtros).toString(), "_blank");
		},
		exportarExcelDetallado() { this.exportar_detallado("excel"); },
		exportarPdfDetallado() { this.exportar_detallado("pdf"); },

		exportarMovimientosDetallePDF() {
   			 window.open(url + phuyu_controller+ "/reporte_movimientos_detallado_pdf", "_blank");
			// exportar_movimientos_detallado_excel
			 
		},
		exportarExcelMovimientos() {
    		window.open(url + phuyu_controller + "/exportar_movimientos_detallado_excel", "_blank");
		},


		phuyu_datos: function(){
			if (!this.validar_fechas(this.vista === "historial")) return;
			this.cargando = true; this.registro = 0;
			const consulta = ++this.consulta;
			const filtros = this.filtros_movimientos();
			filtros.pagina = this.paginacion.actual;
			this.$http.post(url+phuyu_controller+"/lista", filtros).then(function(data){
				if (consulta !== this.consulta) return;
				this.datos = data.body.lista; this.paginacion = data.body.paginacion;
				this.cargando = false; phuyu_sistema.phuyu_fin();
			},function(){
				if (consulta !== this.consulta) return;
				phuyu_sistema.phuyu_alerta("NO SE PUDO CONSULTAR", "Revise los filtros e intente nuevamente.","error"); this.cargando = false;
			});
		},
		phuyu_buscar: function(){
			this.paginacion.actual = 1; this.phuyu_datos();
		},
		phuyu_paginacion: function(pagina){
			this.paginacion.actual = pagina; this.phuyu_datos();
		},
		
		phuyu_nuevo: function(){
			$(".compose").slideToggle(); phuyu_sistema.phuyu_loader("phuyu_formulario",180);
			$(".phuyu_radio").removeAttr('checked'); this.registro = 0;
			this.$http.post(url+phuyu_controller+"/nuevo").then(function(data){
				$("#phuyu_formulario").empty().html(data.body);
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			},function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); 
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			});
		},
		phuyu_seleccionar: function(registro){
			this.registro = registro;
		},
		phuyu_ver: function(){
			if (this.registro==0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UN REGISTRO", "PARA VER EN EL SISTEMA EL REGISTRO!!!","error");
			}else{
				$(".compose").slideToggle(); phuyu_sistema.phuyu_loader("phuyu_formulario",180);
				this.$http.get(url+phuyu_controller+"/ver/"+this.registro).then(function(data){
					$("#phuyu_formulario").empty().html(data.body);
					phuyu_sistema.phuyu_finloader("phuyu_formulario");
				},function(){
					phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); 
					phuyu_sistema.phuyu_finloader("phuyu_formulario");
				});
			}
		},
		phuyu_editar: function(){
			if (this.registro==0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UN REGISTRO", "PARA EDITAR EN EL SISTEMA UN REGISTRO!!!","error");
			}else{
				$(".compose").slideToggle(); phuyu_sistema.phuyu_loader("phuyu_formulario",180);
				this.$http.post(url+phuyu_controller+"/nuevo").then(function(data){
					this.$http.post(url+phuyu_controller+"/editar",{"codregistro":this.registro}).then(function(info){
						$("#phuyu_formulario").empty().html(data.body); var datos = eval(info.body);
						
						$.each(campos, function(key, value){
							campos[key] = datos[0][key];
						});
						phuyu_form.campos = campos;
						phuyu_sistema.phuyu_finloader("phuyu_formulario");
					});
				},function(){
					phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); 
					phuyu_sistema.phuyu_finloader("phuyu_formulario");
				});
			}
		},
		phuyu_eliminar: function(){
			if (this.registro==0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UN REGISTRO", "PARA ELIMINAR EN EL SISTEMA UN REGISTRO!!!","error");
			}else{
				swal({
					title: "SEGURO ELIMINAR REGISTRO ?",   
					text: "USTED ESTA POR ELIMINAR UN REGISTRO", 
					icon: "warning",
					dangerMode: true,
					buttons: ["CANCELAR", "SI, ELIMINAR"],
				}).then((willDelete) => {
					if (willDelete) {
						this.$http.post(url+phuyu_controller+"/eliminar",{"codregistro":this.registro}).then(function(data){
							if (data.body==1) {
								phuyu_sistema.phuyu_alerta("ELIMINADO CORRECTAMENTE", "UN REGISTRO ELIMINADO EN EL SISTEMA","success");
							}else{
								phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR !!!", "SE PERDIÓ LA CONEXION !!! LO SENTIMOS","error");
							}
							this.phuyu_datos();
						}, function(){
							alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
						});
					}
				});
			}
		},
		phuyu_transferencias: function(mostrar){
			this.$http.post(url+phuyu_controller+"/transferencias").then(function(data){
				this.transferencias = data.body;
				this.transferencias_pendientes = this.transferencias.length;
				if (mostrar !== false) { $("#modal_transferencias").modal("show"); }
			},function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS", "ERROR DE RED","error");
			});
		},
		phuyu_aceptar_transferencia(datos){
			this.campos_t.codmovimiento = datos.codmovimiento;
			this.campos_t.codpersona = datos.codpersona;
			this.campos_t.codcaja = datos.codcaja;
			this.campos_t.codtipopago = datos.codtipopago;
			this.campos_t.importe = datos.importe;
			this.campos_t.codcomprobantetipo = datos.codcomprobantetipo;
			this.campos_t.seriecomprobante = datos.seriecomprobante;
			this.campos_t.nrocomprobante = datos.nrocomprobante;
			this.campos_t.fechadocbanco = datos.fechadocbanco;
			this.campos_t.nrodocbanco = datos.nrodocbanco;

			$("#modal_transferencias").modal("hide");
			swal({
				title: "SEGURO ACEPTAR ESTA TRANSFERENCIA DE CAJA ?",   
				text: "", 
				icon: "warning",
				dangerMode: true,
				buttons: ["CANCELAR", "SI, ACEPTAR"],
				content: {
				    element: "input",
				    attributes: {
				      	placeholder: "ESCRIBIR UNA REFERENCIA",
				      	type: "text",
				    },
				},
			}).then((willDelete) => {
				if (willDelete) {
					this.$http.post(url+phuyu_controller+"/aceptar_transferencia",{"campos":this.campos_t,"referencia":$(".swal-content__input").val()}).then(function(data){
						const respuesta = data.body;
						if (respuesta && respuesta.exito === true) {
							phuyu_sistema.phuyu_alerta("TRANSFERENCIA ACEPTADA", respuesta.mensaje,"success");
							this.transferencias = this.transferencias.filter(function(transferencia){ return transferencia.codmovimiento != datos.codmovimiento; });
							this.transferencias_pendientes = this.transferencias.length;
							this.phuyu_transferencias(false);
							$("#modal_transferencias").modal("hide");
							this.phuyu_datos();
						}else{
							phuyu_sistema.phuyu_alerta("NO SE PUDO ACEPTAR LA TRANSFERENCIA", respuesta && respuesta.mensaje ? respuesta.mensaje : "El servidor devolvio una respuesta inesperada. Revise el estado de la transferencia antes de reintentar.","error");
						}
					}, function(error){
						const respuesta = error.body;
						const mensaje = respuesta && respuesta.mensaje ? respuesta.mensaje : (error.status === 0 ? "No se pudo conectar con el servidor. Revise su conexion y el estado de la transferencia antes de reintentar." : "El servidor no pudo procesar la transferencia. Revise su estado antes de reintentar.");
						phuyu_sistema.phuyu_alerta("NO SE PUDO ACEPTAR LA TRANSFERENCIA", mensaje,"error");
					});
				}else{
					$("#modal_transferencias").modal("show");
				}
			});
		},
		marcar: function(obj,dato,ver) {
			if(ver==0){
				obj = obj + 1
	            $('tr:eq('+obj+') td').addClass('highlightRow').parents('tr').siblings().find('td').removeClass('highlightRow')
				this.registro = dato
			}		
		}
	},
	created: function(){
		this.phuyu_datos();
	}
});
