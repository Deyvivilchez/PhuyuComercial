var phuyu_sistemabd = new Vue({
	el: "#phuyu_index",
	data: {phuyu_almacen : "1", phuyu_caja : "1", tipo_backup: "backup"},
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
					$.ajax({
						url: url+"administracion/dashboard/vaciabd",
						method: "POST",
						dataType: "json",
						timeout: 600000
					}).done(function(data){
						if(data && parseInt(data.estado) === 1){
							swal({title: "VALORES RESTAURADOS CORRECTAMENTE", text: data.mensaje || "LA BASE DE DATOS ESTÁ LIMPIA", icon: "success", closeOnClickOutside: true });
						}else{
							swal({title: "NO SE PUDO LIMPIAR LA BASE", text: (data && data.mensaje) ? data.mensaje : "El servidor no confirmo la limpieza.", icon: "error", closeOnClickOutside: true });
						}
					}).fail(function(xhr, estado){
						var mensaje = estado === "timeout"
							? "La limpieza tardo demasiado y se cancelo la espera."
							: phuyu_limpia_error_backup(xhr.responseText) || "Error de red o servidor.";
						swal({title: "NO SE PUDO LIMPIAR LA BASE", text: mensaje.substring(0, 500), icon: "error", closeOnClickOutside: true });
					}).always(function(){
						phuyu_sistema.phuyu_fin();
					});
				}
			});
		},
		phuyu_backup: function(){
			var tipo = this.tipo_backup || "backup";
			var descripcion = tipo === "sql" ? "SQL plano (.sql)" : "backup PostgreSQL (.backup)";
			swal({
				title: "GENERAR COPIA DE SEGURIDAD",
				text: "Se descargará un " + descripcion + " de la base de datos actual.",
				icon: "warning",
				buttons: ["CANCELAR", "SI, GENERAR"],
			}).then((willBackup) => {
				if (!willBackup) {
					return;
				}

				phuyu_sistema.phuyu_inicio_guardar("GENERANDO BACKUP . . .");
				var controlador = window.AbortController ? new AbortController() : null;
				var timeoutBackup = setTimeout(function() {
					if (controlador) {
						controlador.abort();
					}
				}, 600000);
				fetch(url+"administracion/backup/database_backup", {
					method: "POST",
					credentials: "same-origin",
					headers: {"Content-Type": "application/json"},
					body: JSON.stringify({tipo: tipo}),
					signal: controlador ? controlador.signal : undefined
				}).then(function(response) {
					if (!response.ok) {
						return response.text().then(function(texto) {
							throw new Error(phuyu_limpia_error_backup(texto) || "No se pudo generar el backup");
						});
					}

					var nombre = tipo === "sql" ? "backup-phuyu.sql" : "backup-phuyu.backup";
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
					clearTimeout(timeoutBackup);
					phuyu_sistema.phuyu_fin();
					swal({title: "BACKUP GENERADO", text: "La descarga de la copia de seguridad inició correctamente.", icon: "success", closeOnClickOutside: true});
				}).catch(function(error) {
					clearTimeout(timeoutBackup);
					phuyu_sistema.phuyu_fin();
					var mensaje = error && error.name === "AbortError"
						? "El backup tardo demasiado y se cancelo la espera. Si Apache quedo ocupado, reinicia XAMPP antes de intentar otra vez."
						: error.message.substring(0, 500);
					swal({title: "NO SE PUDO GENERAR EL BACKUP", text: mensaje, icon: "error", closeOnClickOutside: true});
				});
			});
		}
	},
	created: function(){
		phuyu_sistema.phuyu_fin()
	}
});

function phuyu_limpia_error_backup(texto) {
	var contenedor = document.createElement("div");
	contenedor.innerHTML = texto || "";
	return (contenedor.textContent || contenedor.innerText || texto || "").replace(/\s+/g, " ").trim();
}
