var phuyu_sistemabd = new Vue({
	el: "#phuyu_index",
	data: {phuyu_almacen : "1", phuyu_caja : "1"},
	methods: {
		phuyu_limpiarbd: function(){
			swal({
				title: "USTED ESTÁ POR LIMPIAR TODA LA BASE DE DATOS",   
				text: "ESTA SEGURO DE REALIZAR ESTA OPERACION?", 
				icon: "warning",
				dangerMode: true,
				buttons: ["CANCELAR", "SI, LIMPIAR"],
			}).then((willDelete) => {
				if (willDelete) {
					phuyu_sistema.phuyu_inicio_guardar("LIMPIANDO LA BASE DE DATOS . . .");
					$.post(url+"administracion/dashboard/vaciabd").then(function(data){
						if(data==1){
							swal({title: "VALORES RESTAURADOS CORRECTAMENTE", text: "LA BASE DE DATOS ESTÁ LIMPIA", icon: "success", closeOnClickOutside: true });							
							phuyu_sistema.phuyu_fin();
						}
					});
				}
			});
		},
		phuyu_backup: function(){
			$.post(url+"administracion/backup/database_backup").then(function(data){
				if(data==1){
					swal({title: "VALORES RESTAURADOS CORRECTAMENTE", text: "LA BASE DE DATOS ESTÁ LIMPIA", icon: "success", closeOnClickOutside: true });							
					phuyu_sistema.phuyu_fin();
				}
			});
		}
	},
	created: function(){
		phuyu_sistema.phuyu_fin()
	}
});