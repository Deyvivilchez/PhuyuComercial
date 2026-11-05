// Ejecutar: node tests/ventas_pagos_ui.js [raiz-del-proyecto]
const fs = require('fs');
const path = require('path');
const vm = require('vm');
const assert = require('assert');
const root = process.argv[2] || path.resolve(__dirname, '..');
let options;
const attrs = {};
const context = {
    Vue: function(opts) { options = opts; return {}; },
    $: selector => ({
        val: () => 0,
        attr: (name, value) => { (attrs[selector] ||= {})[name] = value; },
        removeAttr: name => { delete (attrs[selector] ||= {})[name]; }
    }),
    document: {addEventListener() {}}, window: {}
};
vm.createContext(context);
vm.runInContext(fs.readFileSync(path.join(root, 'phuyu/phuyu_ventas/nuevo.js'), 'utf8'), context);
const screen = {totales: {importe: 6}, pagos: {codtipopago_tarjeta: 0, monto_efectivo: 6, monto_tarjeta: 0, vuelto_efectivo: 0, nrovoucher: ''}};
Object.assign(screen, options.methods);
for (const medio of [3, 4, 5]) {
    screen.pagos.codtipopago_tarjeta = medio;
    screen.phuyu_pagotarjeta();
    assert.strictEqual(screen.pagos.monto_tarjeta, 6);
    assert.strictEqual(screen.pagos.monto_efectivo, 0);
    assert.strictEqual(screen.pagos.vuelto_efectivo, 0);
}
console.log('OK: seleccionar un medio digital asigna S/ 6 y deja efectivo y vuelto en cero');
screen.pagos.monto_tarjeta = '4.20';
screen.phuyu_vuelto(1);
assert.strictEqual(screen.pagos.monto_efectivo, 1.8);
screen.pagos.monto_efectivo = 2;
screen.phuyu_vuelto(0);
assert.strictEqual(screen.pagos.vuelto_efectivo, 0.2);
console.log('OK: pago mixto y vuelto con centavos');
screen.pagos.codtipopago_tarjeta = 0;
screen.phuyu_pagotarjeta();
assert.strictEqual(screen.pagos.monto_tarjeta, 0);
assert.strictEqual(screen.pagos.monto_efectivo, 6);
assert.strictEqual(screen.pagos.vuelto_efectivo, 0);
screen.pagos.monto_efectivo = 10;
screen.phuyu_vuelto(0);
assert.strictEqual(screen.pagos.vuelto_efectivo, 4);
console.log('OK: volver a efectivo elimina el importe digital y calcula vuelto');
if (process.env.PAGO_HTML_TEST) {
    const view = fs.readFileSync(path.join(root, 'application/views/ventas/ventas/nuevo.php'), 'utf8');
    const inputs = [...view.matchAll(/<input[^>]+>/g)].map(m => m[0]);
    const cash = inputs.find(s => s.includes('v-model="pagos.monto_efectivo"'));
    const digital = inputs.find(s => s.includes('id="monto_tarjeta"'));
    const voucher = inputs.find(s => s.includes('id="nrovoucher"'));
    fs.writeFileSync(process.env.PAGO_HTML_TEST, '<!doctype html><html><body><form id="app">' + cash + digital + voucher + '</form><pre id="result"></pre><script>' + fs.readFileSync(path.join(root, 'public/js/vendor/jquery-3.5.1.min.js'), 'utf8') + '</script><script>' + fs.readFileSync(path.join(root, 'public/js/vue/vue.min.js'), 'utf8') + '</script><script>const app=new Vue({el:"#app",data:{totales:{importe:6},pagos:{codtipopago_tarjeta:0,monto_efectivo:6,monto_tarjeta:0,vuelto_efectivo:0,nrovoucher:""}},methods:{phuyu_pagotarjeta:' + options.methods.phuyu_pagotarjeta.toString() + ',phuyu_vuelto:' + options.methods.phuyu_vuelto.toString() + '}}); (async()=>{let results=[]; for(const medio of [0,3,4,5]){app.pagos.codtipopago_tarjeta=medio;app.phuyu_pagotarjeta();app.pagos.nrovoucher="QA";await app.$nextTick();if(!document.querySelector("form").checkValidity())throw Error("Formulario invalido: "+medio);if(medio&&Number(document.querySelector("input").value)!==0)throw Error("Exige efectivo");results.push("PASS formulario valido medio "+medio);}document.querySelector("#result").textContent=results.join(" | ");})().catch(e=>{document.querySelector("#result").textContent="FAIL "+e.message;});</script></body></html>');
}
