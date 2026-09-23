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
			this.phuyu_fecha();
			if (this.campos.fechadesde>this.campos.fechahasta) {
				phuyu_sistema.phuyu_fin();
				phuyu_sistema.phuyu_noti("LA FECHA DESDE DEBE SER MAYOR","QUE LA FECHA HASTA","error"); return false;
			}
			var datos = this.campos;
			var tipos = [];
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
		zip_tickets_ventas: function(){
			this.phuyu_fecha();
			if (this.campos.fechadesde>this.campos.fechahasta) {
				phuyu_sistema.phuyu_noti("LA FECHA DESDE DEBE SER MAYOR","QUE LA FECHA HASTA","error"); return false;
			}
			var progressId = "zip_" + Date.now() + "_" + Math.random().toString(36).substring(2, 10);
			var descargaUrl = url+phuyu_controller+"/zip_tickets_ventas?progress_id="+encodeURIComponent(progressId)+"&datos="+encodeURIComponent(JSON.stringify(this.campos));
			var progresoUrl = url+phuyu_controller+"/zip_tickets_ventas_progreso?progress_id="+encodeURIComponent(progressId);
			var xhr = new XMLHttpRequest();
			var intervalo = null;
			var actualizarProgreso = function(info){
				var actual = parseInt(info.actual || 0);
				var total = parseInt(info.total || 0);
				var porcentaje = parseFloat(info.porcentaje || 0);
				var texto = total > 0 ? (actual + " de " + total + " comprobantes procesados") : (info.mensaje || "Iniciando proceso...");
				$("#zip_tickets_progreso").text(texto);
				$("#zip_tickets_barra").css("width", Math.max(0, Math.min(100, porcentaje)) + "%");
			};
			var iniciarPolling = function(){
				$("#zip_tickets_progreso").text("Iniciando proceso...");
				$("#zip_tickets_barra").css("width", "0%");
				intervalo = window.setInterval(function(){
					$.getJSON(progresoUrl, function(info){
						actualizarProgreso(info || {});
						if (info && info.estado === "completado") {
							window.clearInterval(intervalo);
						}
					});
				}, 650);
			};
			$("#modal_zip_tickets").modal("show");
			iniciarPolling();
			xhr.open("GET", descargaUrl, true);
			xhr.responseType = "blob";
			xhr.onload = function(){
				if (intervalo) {
					window.clearInterval(intervalo);
				}
				$("#modal_zip_tickets").modal("hide");
				if (xhr.status >= 200 && xhr.status < 300) {
					var contentDisposition = xhr.getResponseHeader("Content-Disposition") || "";
					var filename = "tickets_ventas.zip";
					var match = contentDisposition.match(/filename=\"?([^\";]+)\"?/i);
					if (match && match[1]) {
						filename = match[1];
					}
					var blobUrl = window.URL.createObjectURL(xhr.response);
					var link = document.createElement("a");
					link.href = blobUrl;
					link.download = filename;
					document.body.appendChild(link);
					link.click();
					document.body.removeChild(link);
					window.URL.revokeObjectURL(blobUrl);
					phuyu_sistema.phuyu_noti("ZIP GENERADO", "La descarga de comprobantes se inicio correctamente", "success");
					return;
				}
				var reader = new FileReader();
				reader.onload = function(){
					var mensaje = String(reader.result || "No se pudo generar el ZIP.");
					try {
						var respuesta = JSON.parse(mensaje);
						if (respuesta && respuesta.mensaje) {
							mensaje = respuesta.mensaje;
						}
					} catch (e) {
						mensaje = mensaje.replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim();
					}
					phuyu_sistema.phuyu_alerta("ERROR AL GENERAR ZIP", mensaje || "No se pudo generar el ZIP.", "error");
				};
				reader.readAsText(xhr.response);
			};
			xhr.onerror = function(){
				if (intervalo) {
					window.clearInterval(intervalo);
				}
				$("#modal_zip_tickets").modal("hide");
				phuyu_sistema.phuyu_alerta("ERROR AL GENERAR ZIP", "No se pudo conectar con el servidor.", "error");
			};
			xhr.send();
		},

		phuyu_comprobantes: function(){
			this.comprobantes = []; list = this;
			$("input[name='comprobantes']:checked").each(function() {
				list.comprobantes.push({"codcomprobantetipo":$(this).val()});
	        });
		},
		pdf_reporte_ventas: function(estado){
			this.phuyu_comprobantes(); this.campos.estado = estado;
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
