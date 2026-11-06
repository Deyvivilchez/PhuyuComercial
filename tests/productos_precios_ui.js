// Ejecutar: node tests/productos_precios_ui.js
const assert = require('assert');
const fs = require('fs');
const vm = require('vm');
let opciones;
const contexto = { Vue: function (config) { opciones = config; }, phuyu_rubro: 2, campos: {}, campos_1: {},
    phuyu_sistema: { phuyu_noti() {} } };
vm.runInNewContext(fs.readFileSync('phuyu/phuyu_almacen/productos.js', 'utf8'), contexto);
function formulario(rubro, esVenta) {
    contexto.phuyu_rubro = rubro;
    const form = { campos: { es_venta: esVenta }, unidades: [] };
    Object.assign(form, opciones.methods);
    Object.defineProperty(form, 'phuyu_precios_venta_habilitados', {
        get() { return opciones.computed.phuyu_precios_venta_habilitados.call(form); }
    });
    return form;
}
for (const rubro of [2, 4]) {
    for (const esVenta of [undefined, null, 0, '0', 1, '1']) {
        assert.strictEqual(formulario(rubro, esVenta).phuyu_precios_venta_habilitados, true);
    }
}
for (const esVenta of [undefined, null, 0, '0']) {
    assert.strictEqual(formulario(3, esVenta).phuyu_precios_venta_habilitados, false);
}
for (const esVenta of [1, '1']) {
    assert.strictEqual(formulario(3, esVenta).phuyu_precios_venta_habilitados, true);
}
for (const [rubro, esVenta, ventaEsperada] of [[2, undefined, 24], [3, '0', 99], [3, '1', 24]]) {
    const form = formulario(rubro, esVenta);
    form.unidades = [
        { factor: 1, preciocompra: 1, pventapublico: 2 },
        { factor: 12, preciocompra: 99, pventapublico: 99 }
    ];
    assert.strictEqual(form.phuyu_calcular_precios_desde(0), true);
    assert.strictEqual(form.unidades[1].preciocompra, 12);
    assert.strictEqual(form.unidades[1].pventapublico, ventaEsperada);
}
const vista = fs.readFileSync('application/views/almacen/productos/nuevo.php', 'utf8');
for (const campo of ['pventapublico', 'pventamin', 'pventacredito', 'pventaxmayor', 'pventaadicional']) {
    assert(vista.split('\n').find(line => line.includes('v-model.number="uni.' + campo + '"'))
        .includes('v-bind:disabled="!phuyu_precios_venta_habilitados"'));
}
console.log('OK: precios comerciales sin clasificacion, restricciones de restaurante y calculo por unidad.');
