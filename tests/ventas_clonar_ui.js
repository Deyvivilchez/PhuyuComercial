// Ejecutar: node tests/ventas_clonar_ui.js
const fs = require('fs');
const vm = require('vm');
const assert = require('assert');
const path = require('path');
let options;
let elements = {};
const context = {
    Vue: function(opts) { options = opts; },
    document: { addEventListener() {} }, window: {},
    url: '/', phuyu_controller: 'ventas/ventas', phuyu_ventas: { registro: 5 },
    phuyu_sistema: { phuyu_fin() {} },
    Option: function(text, value) { this.text = text; this.value = value; },
    $: function(selector) {
        assert(!selector.includes('select2-selection__rendered'), 'No modificar otros selectores');
        const el = elements[selector] ||= { props: {}, options: [] };
        const api = {
            prop(key, value) { el.props[key] = value; return api; },
            val(value) { el.value = value; return api; },
            append(option) { el.options.push(option); return api; },
            trigger(event) { el.event = event; return api; },
            find() { return { filter(fn) { return { length: el.options.filter(o => fn.call(o)).length }; } }; }
        };
        return api;
    }
};
vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../phuyu/phuyu_ventas/nuevo.js'), 'utf8'), context);
for (const id of [37, 2]) {
    elements = {};
    const ticks = [];
    const body = {
        socio: [{ codpersona: id, razonsocial: 'Ficha actual', documento: '12345678', coddocumentotipo: 1 }],
        campos: [{ cliente: 'Cliente del comprobante', direccion: 'Direccion original', condicionpago: 1 }],
        detalle: []
    };
    const screen = {
        campos: {}, totales: {},
        $nextTick(fn) { ticks.push(() => fn.call(screen)); },
        $http: { post() { return { then(fn) { fn.call(screen, { body }); } }; } },
        phuyu_seleccionar_cliente: options.methods.phuyu_seleccionar_cliente,
        phuyu_validar_stock_detalle() { return true; },
        phuyu_series() {}, phuyu_totales() {}
    };
    // Simular el ciclo de Vue: un v-model sin opcion coincidente dispara
    // change y pierde el codigo antes de que se ejecuten los nextTick.
    let clienteAsignado;
    Object.defineProperty(screen.campos, 'codpersona', {
        get() { return clienteAsignado; },
        set(value) {
            const select = elements['#codpersona'];
            clienteAsignado = select && select.options.some(o => o.value === String(value)) ? value : null;
        }
    });
    const clone = options.methods.phuyu_clonar;
    clone.call(screen);
    ticks.splice(0).forEach(fn => fn());
    assert.strictEqual(screen.campos.codpersona, id);
    assert.strictEqual(elements['#codpersona'].value, String(id));
    assert.strictEqual(elements['#codpersona'].options.length, 1);
    assert.strictEqual(elements['#codpersona'].event, 'change.select2');
    assert.strictEqual(elements['#acv'].props.checked, id === 2);
    assert.strictEqual(elements['#codpersona'].props.disabled, id === 2);
    assert.strictEqual(elements['#codpersona'].props.required, id !== 2);
    assert.strictEqual(screen.campos.cliente, 'Cliente del comprobante');
    assert.strictEqual(screen.campos.direccion, 'Direccion original');
    assert.strictEqual(screen.campos.nrodocumento, '12345678');
    clone.call(screen);
    ticks.splice(0).forEach(fn => fn());
    assert.strictEqual(elements['#codpersona'].options.length, 1);
}
console.log('OK: cliente seleccionado, datos originales, Clientes varios y opcion sin duplicados');

// Proforma y clonacion comparten la seleccion real del cliente.
const validate = options.methods.phuyu_validar_stock_detalle;
context.phuyu_sistema.phuyu_alerta = () => {};
const unit = {codproducto: 1, control: 1, factor: 1, cantidad: 3, stock: 4};
const pack = {codproducto: 1, control: 1, factor: 6, cantidad: 1, stock: 4 / 6};
assert.strictEqual(validate.call({stockalmacen: 1, detalle: [unit]}), true);
assert.strictEqual(validate.call({stockalmacen: 1, detalle: [unit, pack]}), false);
assert.strictEqual(unit.cantidad, 3, 'No modificar la cantidad cotizada');
assert.strictEqual(validate.call({stockalmacen: 0, detalle: [unit, pack]}), true);
assert.strictEqual(validate.call({stockalmacen: 1, detalle: [{control: 0, cantidad: 1, stock: 0}]}), true);
console.log('OK: stock agregado en unidades equivalentes, sin recortar cotizacion ni bloquear servicios');
