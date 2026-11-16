/* Descripciones compatibles con comprobantes/XML: nunca guardar HTML del editor. */
var PhuyuDescripcionItem = (function() {
    function desdeDelta(delta) {
        var lineas = [], linea = '', numero = 0;
        (delta.ops || []).forEach(function(op) {
            if (typeof op.insert !== 'string') return;
            var partes = op.insert.split('\n');
            partes.forEach(function(parte, i) {
                linea += parte;
                if (i === partes.length - 1) return;
                var attrs = op.attributes || {}, prefijo = '';
                if (attrs.list === 'ordered') prefijo = (++numero) + '. ';
                else { numero = 0; if (attrs.list === 'bullet') prefijo = '• '; }
                var sangria = Math.min(8, parseInt(attrs.indent, 10) || 0);
                lineas.push('  '.repeat(sangria) + prefijo + linea);
                linea = '';
            });
        });
        if (linea) lineas.push(linea);
        // Quill termina siempre en salto de linea; no quitar parrafos intermedios.
        return lineas.join('\n').replace(/\n+$/, '');
    }
    function aDelta(texto) {
        var ops = [];
        String(texto || '').split('\n').forEach(function(linea) {
            var lista = linea.match(/^(\s*)([•-]|\d+[.)])\s+(.*)$/);
            if (lista) {
                ops.push({insert: lista[3]});
                var attrs = {list: /^\d/.test(lista[2]) ? 'ordered' : 'bullet'};
                var indent = Math.min(8, Math.floor(lista[1].length / 2));
                if (indent) attrs.indent = indent;
                ops.push({insert: '\n', attributes: attrs});
            } else ops.push({insert: linea + '\n'});
        });
        return {ops: ops};
    }
    function inicializar(vm) {
            var elemento = document.getElementById('descripcion_item_editor');
            if (!elemento || typeof Quill === 'undefined') return;
            if (!vm._descripcionQuill || vm._descripcionQuill.container !== elemento) {
                
                vm._descripcionQuill = new Quill(elemento, {
                    theme: 'snow',
                    placeholder: 'Detalle del servicio, alcance, condiciones y observaciones...',
                    formats: ['list', 'indent'],
                    modules: {toolbar: [[{list:'ordered'}, {list:'bullet'}], [{indent:'-1'}, {indent:'+1'}], ['clean']]}
                });
                vm._descripcionQuill.on('text-change', function(delta, anterior, origen) {
                    if (origen === 'silent') return;
                    var texto = PhuyuDescripcionItem.desdeDelta(vm._descripcionQuill.getContents());
                    if (texto.length > PhuyuDescripcionItem.limite) {
                        vm._descripcionQuill.setContents(anterior, 'silent');
                        phuyu_sistema.phuyu_noti('DESCRIPCION DEMASIADO LARGA', 'El limite es de 5000 caracteres, incluidos los marcadores de lista.', 'warning');
                        return;
                    }
                    vm.item.descripcion = texto;
                });
            }
            vm._descripcionQuill.setContents(PhuyuDescripcionItem.aDelta(vm.item.descripcion), 'silent');
            vm.descripcion_editor_activo = true;
    }
    return {inicializar: inicializar, desdeDelta: desdeDelta, aDelta: aDelta, limite: 5000};
})();
