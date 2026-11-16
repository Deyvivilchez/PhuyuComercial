(function () {
    "use strict";
    window.phuyu_abrir_soporte = function (evento, enlace) {
        // Conservar abrir en otra pestaña mediante Ctrl/Cmd/clic central.
        if (evento.ctrlKey || evento.metaKey || evento.shiftKey || evento.altKey || evento.button > 0) return true;
        evento.preventDefault();
        const contenido = document.createElement("div");
        contenido.className = "phuyu-support-dialog";
        const indicacion = document.createElement("p");
        indicacion.textContent = "Copia este mensaje y pégalo en el grupo de soporte.";
        const mensaje = document.createElement("textarea");
        mensaje.className = "form-control";
        mensaje.rows = 3;
        mensaje.readOnly = true;
        mensaje.setAttribute("aria-label", "Mensaje para soporte");
        mensaje.value = "Hola, Phuyu System. Necesito ayuda con el sistema.";
        const acciones = document.createElement("div");
        acciones.className = "d-flex flex-wrap justify-content-center gap-2 mt-3";
        const copiar = document.createElement("button");
        copiar.type = "button";
        copiar.className = "btn btn-primary";
        copiar.textContent = "Copiar mensaje";
        const abrir = document.createElement("a");
        abrir.className = "btn btn-success";
        abrir.href = enlace.href;
        abrir.target = "_blank";
        abrir.rel = "noopener noreferrer";
        abrir.textContent = "Abrir grupo en WhatsApp";
        const estado = document.createElement("p");
        estado.className = "text-muted small mt-3 mb-0";
        estado.setAttribute("role", "status");
        copiar.addEventListener("click", async function () {
            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(mensaje.value);
                } else {
                    mensaje.focus(); mensaje.select();
                    if (!document.execCommand("copy")) throw new Error("No se pudo copiar");
                }
                copiar.textContent = "Mensaje copiado";
                estado.textContent = "Ahora abre el grupo, pega el mensaje y envíalo.";
            } catch (error) {
                mensaje.focus(); mensaje.select();
                estado.textContent = "Copia el texto seleccionado y pégalo en el grupo.";
            }
        });
        acciones.append(copiar, abrir);
        contenido.append(indicacion, mensaje, acciones, estado);
        swal({title: "Soporte Phuyu System", content: contenido, button: "Cerrar"});
        return false;
    };
})();
