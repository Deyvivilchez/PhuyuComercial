var phuyu_administrar = new Vue({
	el: "#phuyu_administrar",
	data: {phuyu_almacen : "1", phuyu_caja : "1", estado : 0,
		campos:{codsucursal:"",codcaja:"",codalmacen:""}, cajas:[], almacenes:[]
	},
	methods: {
		phuyu_noti: function(titulo,mensaje,tipo){
			new PNotify({
				title: titulo,
				text: mensaje,
				type: tipo,
				styling: 'bootstrap3'
			});
		},
		administrar: function(){
			this.$http.post(url+"phuyu/phuyu_web",this.campos).then(function(data){
				window.location.href = url+"phuyu/w";
			}, function(){
				phuyu_sistema.alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
			});
		},
		phuyu_resultados: function(){
			if(this.campos.codsucursal!=""){
				this.estado = 1;
				this.$http.post(url+"phuyu/phuyu_sucursal",this.campos).then(function(data){
					this.estado = 0;
					this.almacenes = data.body.almacenes;
					this.cajas = data.body.cajas;
					var almacenprincipal = "";
					$.each( data.body.almacenes, function( k, v ) {
						if(parseInt(v.principal)==1){
							almacenprincipal = v.codalmacen;
						}
					});
					this.campos.codalmacen = almacenprincipal;
					if(data.body.cajas.length > 0){
						this.campos.codcaja = data.body.cajas[0].codcaja
					}
				}, function(){
					this.estado = 0;
					phuyu_sistema.alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
				});
			}else{
				this.almacenes = []; this.cajas = [];
				this.campos.codalmacen = ""; this.campos.codcaja = "";
			}
		}
	},
	created: function(){
		console.log($("#vencimiento").val())
		if ($("#vencimiento").val() < 11) {
			console.log('ko')
			$("#modal_vencimiento").modal('show');
		}
	}
});