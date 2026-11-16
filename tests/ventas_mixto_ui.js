// Ejecutar: node tests/ventas_mixto_ui.js [raiz-del-proyecto]
const fs = require('fs');
const path = require('path');
const vm = require('vm');
const assert = require('assert');
const root = process.argv[2] || path.resolve(__dirname, '..');
for (const file of ['nuevo.js', 'nuevaventa.js']) {
    let options;
    const context = {
        Vue: function(opts) { options = opts; return {}; },
        $: () => ({val: () => 0, attr() {}, removeAttr() {}}),
        document: {addEventListener() {}}, window: {},
        phuyu_sistema: {phuyu_noti() {}}
    };
    vm.createContext(context);
    vm.runInContext(fs.readFileSync(path.join(root, 'phuyu/phuyu_ventas', file), 'utf8'), context);
    const screen = {totales: {importe: 6}, pagos: {
        codtipopago_tarjeta: 9, monto_efectivo: 0, monto_tarjeta: 0, vuelto_efectivo: 0, nrovoucher: 'QA'
    }};
    Object.assign(screen, options.methods);
    for (const medio of [5, 6, 7, 9, 10]) {
        screen.pagos.codtipopago_tarjeta = medio;
        screen.phuyu_pagotarjeta();
        assert.strictEqual(screen.pagos.monto_tarjeta, 6);
        assert.strictEqual(screen.pagos.monto_efectivo, 0);
        assert.strictEqual(screen.phuyu_validar_pago_contado(), true);
    }
    // Editar el importe digital actualiza el saldo en efectivo.
    screen.pagos.monto_tarjeta = '4.20';
    screen.phuyu_vuelto(1);
    assert.strictEqual(screen.pagos.monto_efectivo, 1.8);
    screen.pagos.monto_efectivo = '2';
    screen.phuyu_vuelto(0);
    assert.strictEqual(screen.pagos.vuelto_efectivo, 0.2);
    assert.strictEqual(screen.phuyu_validar_pago_contado(), true);
    assert.strictEqual(Math.round((2 + 4.2 - screen.pagos.vuelto_efectivo) * 100), 600);
    // La suma binaria 0.10 + 0.20 no debe rechazar un total de 0.30.
    screen.totales.importe = 0.3;
    screen.pagos.monto_efectivo = '0.10';
    screen.pagos.monto_tarjeta = '0.20';
    screen.pagos.vuelto_efectivo = 100; // Al enviar se elimina un vuelto anterior.
    assert.strictEqual(screen.phuyu_validar_pago_contado(), true);
    assert.strictEqual(screen.pagos.vuelto_efectivo, 0);
    for (const [cash, digital] of [[0.09, 0.2], [0, 0], [1, 0.31], [-1, 0.2], ['abc', 0.2], [0.1, 'abc']]) {
        screen.pagos.monto_efectivo = cash;
        screen.pagos.monto_tarjeta = digital;
        assert.strictEqual(screen.phuyu_validar_pago_contado(), false, `${file}: ${cash} / ${digital}`);
    }
    screen.totales.importe = 6;
    screen.pagos.codtipopago_tarjeta = 0;
    screen.phuyu_pagotarjeta();
    assert.strictEqual(screen.pagos.monto_tarjeta, 0);
    assert.strictEqual(screen.pagos.monto_efectivo, 6);
    screen.pagos.monto_efectivo = 10;
    assert.strictEqual(screen.phuyu_validar_pago_contado(), true);
    assert.strictEqual(screen.pagos.vuelto_efectivo, 4);
    const view = fs.readFileSync(path.join(root, 'application/views/ventas/ventas', file === 'nuevo.js' ? 'nuevo.php' : 'nuevaventa.php'), 'utf8');
    assert.match(view, /v-model="pagos.monto_tarjeta"[^>]*v-on:input="phuyu_vuelto\(1\)"/);
    assert.match(view, /v-model="pagos.monto_efectivo"[^>]*v-on:input="phuyu_vuelto\(0\)"/);
    console.log(`OK ${file}: digital, mixto, centavos, vuelto, pago insuficiente e importes invalidos`);
}
