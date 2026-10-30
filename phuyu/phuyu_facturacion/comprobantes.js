function phuyu_swal(opciones){
	if (typeof Swal!=="undefined" && typeof Swal.fire==="function") return Swal.fire(opciones);
	var contenido=null;
	if (opciones.html) { contenido=document.createElement("div"); contenido.innerHTML=opciones.html; contenido.style.textAlign="left"; }
	return swal({title:opciones.title||"",text:opciones.text||"",content:contenido,icon:opciones.icon||"info",buttons:opciones.showCancelButton?[opciones.cancelButtonText||"Cancelar",opciones.confirmButtonText||"Aceptar"]:(opciones.confirmButtonText||"Aceptar"),dangerMode:opciones.icon==="warning"||opciones.icon==="error",closeOnClickOutside:false}).then(function(valor){return {isConfirmed:!!valor};});
}

var phuyu_sunat = new Vue({
	el: "#phuyu_sunat",
	data: {
		cargando: true, registro:0, buscar: "", sucursal:$("#sucursal").val(), comprobantetipo: "0", estado_sunat: "", datos: [], fechas:{"filtro":1,"desde":"","hasta":""},
		comprobante_consultado: null, respuesta_sunat: {nivel:"secondary",mensaje:"",detalle:""},
		reprogramando_resumen: false, sincronizando_estado: false, corrigiendo_lote: false, modo_lote: "", progreso_lote: 0, solo_habilitados: false,
		candidatos_sunat: [], rango_candidatos: {desde:"",hasta:"",dias:0}, total_consulta_sunat:0, habilitando_todos:false, progreso_habilitando:0,
		paginacion: {"total":0, "actual":1, "ultima":0, "desde":0, "hasta":0}, offset: 3
	},
	computed: {
		candidatos_pendientes: function(){
			return this.candidatos_sunat.filter(function(d){return !d.habilitada;}).length;
		},
		puede_reprogramar_resumen: function(){
			return this.comprobante_consultado && parseInt(this.comprobante_consultado.codcomprobantetipo)===12 && this.respuesta_sunat && this.respuesta_sunat.estado_cp==="NO EXISTE";
		},
		puede_sincronizar_aceptado: function(){
			return this.comprobante_consultado && parseInt(this.comprobante_consultado.estado)===0 && this.respuesta_sunat && this.respuesta_sunat.estado_cp==="ACEPTADO";
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
		phuyu_datos: function(){
			this.fechas.desde = $("#fecha_desde").val(); this.fechas.hasta = $("#fecha_hasta").val();
			this.cargando = true; this.registro = 0;

			return this.$http.post(url+phuyu_controller+"/lista",{"buscar":this.buscar,"fechas":this.fechas,"pagina":this.paginacion.actual,"sucursal":this.sucursal,"comprobantetipo":this.comprobantetipo,"estado_sunat":this.estado_sunat,"solo_habilitados":this.solo_habilitados?1:0}).then(function(data){
				this.datos = data.body.lista; this.paginacion = data.body.paginacion;
				this.cargando = false; phuyu_sistema.phuyu_fin();
			},function(){
				phuyu_sistema.phuyu_error(); this.cargando = false;
			});
		},
		phuyu_buscar: function(){
			this.paginacion.actual = 1; this.phuyu_datos();
		},
		phuyu_ver_habilitados: function(){
			this.comprobantetipo="12"; this.estado_sunat="";
			this.buscar=""; this.solo_habilitados=!this.solo_habilitados;
			this.phuyu_buscar();
		},
		phuyu_buscar_candidatos: function(){
			if (this.corrigiendo_lote) return;
			var vm=this, desde=$("#fecha_desde").val(), hasta=$("#fecha_hasta").val();
			var formato=function(fecha){if(!fecha)return "-";var p=fecha.split("-");return p[2]+"/"+p[1]+"/"+p[0];};
			var dias=Math.max(1,Math.round((new Date(hasta+"T00:00:00")-new Date(desde+"T00:00:00"))/86400000)+1);
			var aviso=dias>62?"<div class='alert alert-danger mt-3 mb-0 py-2'><b>Rango amplio:</b> "+dias+" días. La consulta puede tardar varios minutos.</div>":"";
			phuyu_swal({title:"Consultar candidatos en SUNAT",html:"<div class='border rounded p-3 mb-3 bg-light'><div><b>Desde:</b> "+formato(desde)+"</div><div><b>Hasta:</b> "+formato(hasta)+"</div><div><b>Periodo:</b> "+dias+" días</div><div><b>Tipo:</b> Boletas electrónicas</div></div><div class='alert alert-info py-2'><b>Solo consulta:</b> no modifica ni habilita comprobantes.</div>Se mostrarán las boletas que SUNAT responda <b>NO EXISTE</b>."+aviso,icon:"info",showCancelButton:true,confirmButtonText:"Consultar solamente",cancelButtonText:"Cancelar"}).then(function(c){
				if (!c.isConfirmed) return;
				vm.corrigiendo_lote=true; vm.modo_lote="consulta"; vm.progreso_lote=0; vm.total_consulta_sunat=0; vm.candidatos_sunat=[]; vm.rango_candidatos={desde:formato(desde),hasta:formato(hasta),dias:dias};
				var modalProgreso=new bootstrap.Modal(document.getElementById("modalProgresoSunat"),{backdrop:"static",keyboard:false}); modalProgreso.show();
				var lista=[],pagina=1;
				var procesar=function(i){
					if(i>=lista.length){
						vm.corrigiendo_lote=false; vm.modo_lote="";
						var progresoEl=document.getElementById("modalProgresoSunat");
						progresoEl.addEventListener("hidden.bs.modal",function abrirResultado(){progresoEl.removeEventListener("hidden.bs.modal",abrirResultado);var modal=new bootstrap.Modal(document.getElementById("modalCandidatosSunat"));modal.show();});
						modalProgreso.hide();
						return;
					}
					vm.progreso_lote=i+1;
					vm.$http.post(url+phuyu_controller+"/phuyu_validar_sunat/"+lista[i].codkardex,{}).then(function(r){
						if(r.body && r.body.estado_cp==="NO EXISTE") { vm.$set(lista[i],"habilitando",false); vm.$set(lista[i],"habilitada",false); vm.candidatos_sunat.push(lista[i]); }
						procesar(i+1);
					},function(){procesar(i+1);});
				};
				var cargar=function(){
					vm.fechas.desde=$("#fecha_desde").val(); vm.fechas.hasta=$("#fecha_hasta").val();
					vm.$http.post(url+phuyu_controller+"/lista",{buscar:"",fechas:vm.fechas,pagina:pagina,sucursal:vm.sucursal,comprobantetipo:"12",estado_sunat:"",solo_habilitados:0}).then(function(r){lista=lista.concat(r.body.lista||[]);if(pagina<(r.body.paginacion.ultima||1)){pagina++;cargar();}else{vm.total_consulta_sunat=lista.length;procesar(0);}},function(){vm.corrigiendo_lote=false;vm.modo_lote="";modalProgreso.hide();phuyu_sistema.phuyu_error();});
				};
				cargar();
			});
		},
		phuyu_habilitar_candidato: function(dato){
			if (dato.habilitando || dato.habilitada) return;
			this.$set(dato,"habilitando",true);
			this.$http.post(url+phuyu_controller+"/phuyu_reprogramar_resumen/"+dato.codkardex,{}).then(function(r){
				this.$set(dato,"habilitando",false);
				if (parseInt(r.body.estado)===1) { this.$set(dato,"habilitada",true); this.$set(dato,"estado",0); }
				else phuyu_swal({title:"No se pudo habilitar",text:r.body.mensaje||"SUNAT no confirmó la operación.",icon:"error"});
			},function(){ this.$set(dato,"habilitando",false); phuyu_sistema.phuyu_error(); });
		},
		phuyu_habilitar_todos: function(){
			if (this.habilitando_todos) return;
			var vm=this, pendientes=this.candidatos_sunat.filter(function(d){return !d.habilitada;});
			if (!pendientes.length) return;
			phuyu_swal({title:"¿Habilitar todos los candidatos?",html:"<div class='border rounded p-3 mb-3 bg-light'><b>Rango:</b> "+this.rango_candidatos.desde+" al "+this.rango_candidatos.hasta+"<br><b>Comprobantes:</b> "+pendientes.length+"</div><div class='alert alert-warning py-2 mb-0'>Se retirarán de sus resúmenes anteriores y quedarán pendientes para incluirlos en un nuevo resumen.</div>",icon:"warning",showCancelButton:true,confirmButtonText:"Sí, habilitar todos",cancelButtonText:"Cancelar"}).then(function(c){
				if (!c.isConfirmed) return;
				vm.habilitando_todos=true; vm.progreso_habilitando=0;
				var correctos=0, errores=0;
				var procesar=function(i){
					if (i>=pendientes.length) {
						vm.habilitando_todos=false;
						phuyu_swal({title:"Proceso terminado",html:"<div class='text-start'><b>Habilitados:</b> "+correctos+"<br><b>No habilitados:</b> "+errores+"</div>",icon:errores?"warning":"success",confirmButtonText:"Entendido"});
						return;
					}
					var dato=pendientes[i]; vm.progreso_habilitando=i+1; vm.$set(dato,"habilitando",true);
					vm.$http.post(url+phuyu_controller+"/phuyu_reprogramar_resumen/"+dato.codkardex,{}).then(function(r){
						vm.$set(dato,"habilitando",false);
						if (parseInt(r.body.estado)===1) { vm.$set(dato,"habilitada",true); vm.$set(dato,"estado",0); correctos++; } else errores++;
						procesar(i+1);
					},function(){ vm.$set(dato,"habilitando",false); errores++; procesar(i+1); });
				};
				procesar(0);
			});
		},
		phuyu_paginacion: function(pagina){
			this.paginacion.actual = pagina; this.phuyu_datos();
		},
		
		phuyu_xml: function(codkardex){
			window.open(url+phuyu_controller+"/phuyu_xml/"+codkardex,"_blank");
		},
		phuyu_cdr: function(codkardex){
			window.open(url+phuyu_controller+"/phuyu_cdr/"+codkardex,"_blank");
		},
		phuyu_validar_sunat: function(dato){
			this.$set(dato,"validando_sunat",true);
			this.$http.post(url+phuyu_controller+"/phuyu_validar_sunat/"+dato.codkardex,{}).then(function(respuesta){
				this.$set(dato,"consulta_sunat",respuesta.body);
				this.$set(dato,"validando_sunat",false);
				this.comprobante_consultado = dato;
				this.respuesta_sunat = respuesta.body;
				var modal = new bootstrap.Modal(document.getElementById("modalRespuestaSunat"));
				modal.show();
			},function(){
				this.$set(dato,"validando_sunat",false);
				phuyu_sistema.phuyu_error();
			});
		},
		phuyu_reprogramar_resumen: function(){
			if (!this.puede_reprogramar_resumen || this.reprogramando_resumen) return;
			var vm=this, numero=this.comprobante_consultado.seriecomprobante+"-"+this.comprobante_consultado.nrocomprobante;
			phuyu_swal({
				title:"¿Habilitar para nuevo resumen?",
				html:"<div class='mb-2'><span class='badge bg-danger fs-6'>NO EXISTE EN SUNAT</span></div><strong>"+numero+"</strong><p class='text-muted mt-2 mb-0'>Se retirará del resumen anterior y quedará pendiente para incluirla en un nuevo resumen.</p>",
				icon:"warning", showCancelButton:true, confirmButtonText:"Sí, habilitar", cancelButtonText:"Cancelar", confirmButtonColor:"#f7b84b", cancelButtonColor:"#74788d", reverseButtons:true, focusCancel:true
			}).then(function(confirmacion){
				if (!confirmacion.isConfirmed) return;
				vm.reprogramando_resumen=true;
				vm.$http.post(url+phuyu_controller+"/phuyu_reprogramar_resumen/"+vm.comprobante_consultado.codkardex,{}).then(function(respuesta){
					vm.reprogramando_resumen=false;
					if (parseInt(respuesta.body.estado)===1) {
						vm.respuesta_sunat={nivel:"success",mensaje:"HABILITADA PARA NUEVO RESUMEN",detalle:respuesta.body.mensaje}; vm.comprobante_consultado.estado=0;
						phuyu_swal({title:"Boleta habilitada",text:respuesta.body.mensaje,icon:"success",confirmButtonText:"Entendido",confirmButtonColor:"#0ab39c"});
					} else {
						vm.respuesta_sunat={nivel:"danger",mensaje:"NO SE PUDO HABILITAR",detalle:respuesta.body.mensaje};
						phuyu_swal({title:"No se pudo habilitar",text:respuesta.body.mensaje,icon:"error",confirmButtonText:"Cerrar"});
					}
				},function(){ vm.reprogramando_resumen=false; phuyu_sistema.phuyu_error(); });
			});
		},
		phuyu_sincronizar_aceptado: function(){
			if (!this.puede_sincronizar_aceptado || this.sincronizando_estado) return;
			this.sincronizando_estado=true;
			this.$http.post(url+phuyu_controller+"/phuyu_sincronizar_aceptado/"+this.comprobante_consultado.codkardex,{}).then(function(respuesta){
				this.sincronizando_estado=false;
				if (parseInt(respuesta.body.estado)===1) {
					this.comprobante_consultado.estado=1;
					this.comprobante_consultado.descripcion_cdr=respuesta.body.mensaje;
					this.respuesta_sunat.detalle=respuesta.body.mensaje;
				} else {
					this.respuesta_sunat={nivel:"danger",mensaje:"NO SE PUDO SINCRONIZAR",detalle:respuesta.body.mensaje};
				}
			},function(){ this.sincronizando_estado=false; phuyu_sistema.phuyu_error(); });
		},
		phuyu_corregir_visibles: function(){
			if (this.corrigiendo_lote || !this.datos.length) return;
			var vm=this, desde=$("#fecha_desde").val(), hasta=$("#fecha_hasta").val();
			var f=function(fecha){var p=fecha.split("-");return p[2]+"/"+p[1]+"/"+p[0];};
			phuyu_swal({title:"Procesar y habilitar comprobantes",html:"<div class='border rounded p-3 mb-3 bg-light'><b>Rango:</b> "+f(desde)+" al "+f(hasta)+"<br><b>Comprobantes mostrados:</b> "+this.paginacion.total+"</div><div class='alert alert-warning py-2'><b>Esta acción sí realizará cambios:</b> sincronizará los aceptados y habilitará las boletas NO EXISTE para un nuevo resumen.</div>",icon:"warning",showCancelButton:true,confirmButtonText:"Procesar y habilitar",cancelButtonText:"Cancelar",confirmButtonColor:"#f7b84b"}).then(function(confirmacion){
				if (!confirmacion.isConfirmed) return;
				vm.corrigiendo_lote=true; vm.modo_lote="proceso"; vm.progreso_lote=0;
				var lista=[], pagina=1;
				var cargar=function(){
					vm.fechas.desde=$("#fecha_desde").val(); vm.fechas.hasta=$("#fecha_hasta").val();
					vm.$http.post(url+phuyu_controller+"/lista",{buscar:vm.buscar,fechas:vm.fechas,pagina:pagina,sucursal:vm.sucursal,comprobantetipo:vm.comprobantetipo,estado_sunat:vm.estado_sunat,solo_habilitados:vm.solo_habilitados?1:0}).then(function(r){
						lista=lista.concat(r.body.lista||[]);
						if (pagina<(r.body.paginacion.ultima||1)) { pagina++; cargar(); } else procesar(0);
					},function(){ vm.corrigiendo_lote=false; vm.modo_lote=""; phuyu_sistema.phuyu_error(); });
				};
				var resumen={aceptados:0,habilitados:0,sin_cambio:0,errores:0};
			var procesar=function(indice){
				if (indice>=lista.length) {
					vm.corrigiendo_lote=false; vm.modo_lote="";
					phuyu_swal({title:"Corrección terminada",html:"<div class='text-start'><b>Aceptados sincronizados:</b> "+resumen.aceptados+"<br><b>Habilitados para nuevo resumen:</b> "+resumen.habilitados+"<br><b>Sin cambios:</b> "+resumen.sin_cambio+"<br><b>Con error:</b> "+resumen.errores+"</div>",icon:"success",confirmButtonText:"Cerrar"});
					vm.phuyu_datos(); return;
				}
				var dato=lista[indice]; vm.progreso_lote=indice+1;
				vm.$http.post(url+phuyu_controller+"/phuyu_validar_sunat/"+dato.codkardex,{}).then(function(r){
					var accion=null;
					if (r.body.estado_cp==="ACEPTADO" && parseInt(dato.estado)===0) accion="phuyu_sincronizar_aceptado";
					if (r.body.estado_cp==="NO EXISTE" && parseInt(dato.codcomprobantetipo)===12) accion="phuyu_reprogramar_resumen";
					if (!accion) { resumen.sin_cambio++; procesar(indice+1); return; }
					vm.$http.post(url+phuyu_controller+"/"+accion+"/"+dato.codkardex,{}).then(function(a){
						if (parseInt(a.body.estado)===1) { if (accion==="phuyu_sincronizar_aceptado") resumen.aceptados++; else resumen.habilitados++; }
						else resumen.errores++;
						procesar(indice+1);
					},function(){ resumen.errores++; procesar(indice+1); });
				},function(){ resumen.errores++; procesar(indice+1); });
			};
				cargar();
			});
		}
	},
	created: function(){
		this.phuyu_datos(); phuyu_sistema.phuyu_fin();
	}
});
