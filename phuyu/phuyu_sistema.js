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
			swal({
				title: "GENERAR COPIA DE SEGURIDAD",
				text: "Se descargará un backup de la base de datos actual.",
				icon: "warning",
				buttons: ["CANCELAR", "SI, GENERAR"],
			}).then((willBackup) => {
				if (!willBackup) {
					return;
				}

				phuyu_sistema.phuyu_inicio_guardar("GENERANDO BACKUP . . .");
				fetch(url+"administracion/backup/database_backup", {
					method: "POST",
					credentials: "same-origin"
				}).then(function(response) {
					if (!response.ok) {
						return response.text().then(function(texto) {
							throw new Error(texto || "No se pudo generar el backup");
						});
					}

					var nombre = "backup-phuyu.backup";
					var disposition = response.headers.get("Content-Disposition");
					if (disposition) {
						var match = disposition.match(/filename="?([^"]+)"?/i);
						if (match && match[1]) {
							nombre = match[1];
						}
					}

					return response.blob().then(function(blob) {
						var enlace = document.createElement("a");
						var archivo = window.URL.createObjectURL(blob);
						enlace.href = archivo;
						enlace.download = nombre;
						document.body.appendChild(enlace);
						enlace.click();
						document.body.removeChild(enlace);
						setTimeout(function() {
							window.URL.revokeObjectURL(archivo);
						}, 1000);
					});
				}).then(function() {
					phuyu_sistema.phuyu_fin();
					swal({title: "BACKUP GENERADO", text: "La descarga de la copia de seguridad inició correctamente.", icon: "success", closeOnClickOutside: true});
				}).catch(function(error) {
					phuyu_sistema.phuyu_fin();
					swal({title: "NO SE PUDO GENERAR EL BACKUP", text: error.message.substring(0, 500), icon: "error", closeOnClickOutside: true});
				});
			});
		}
	},
	created: function(){
		phuyu_sistema.phuyu_fin()
	}
});
