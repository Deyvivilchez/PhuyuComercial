const fs = require('fs');
const vm = require('vm');
const assert = require('assert');
for (const filename of ['nuevo.js','nuevaventa.js']) {
    let options, alerts = 0;
    const context = {
        Vue: function (opts) { options = opts; return {}; },
        $: function () { return {val: () => 0}; },
        document: {addEventListener: () => {}},
        window: {},
        phuyu_sistema: {phuyu_alerta: () => { alerts++; }}
    };
    vm.createContext(context);
    vm.runInContext(fs.readFileSync(process.argv[2] + '/phuyu/phuyu_ventas/' + filename,'utf8'), context);
    const methods = options.methods;
    const product = {codproducto:31,codunidad:138,controlstock:1,stock:1,stockdisponible:0,factor:1,descripcion:'COCA COLA',unidad:'UNIDAD'};
    const screen = {stockalmacen:1,detalle:[]};
    assert.strictEqual(methods.phuyu_additem.call(screen,product,4),false);
    assert.strictEqual(screen.detalle.length,0);
    assert.strictEqual(alerts,1);
    const current = {codproducto:31,control:1,factor:1,stock:1,cantidad:2,unidad:'UNIDAD'};
    screen.detalle = [current];
    methods.phuyu_limitar_disponible.call(screen,current);
    assert.strictEqual(current.cantidad,1);
    const pack = {codproducto:31,control:1,factor:12,stock:1,cantidad:1,unidad:'PAQUETE'};
    current.stock=12;current.cantidad=1;
    screen.detalle = [current,pack];
    methods.phuyu_limitar_disponible.call(screen,pack);
    assert(pack.cantidad < 1);
    console.log('OK '+filename+': bloquea reservado y limita cantidades/unidades equivalentes');
}
