const assert = require('assert');
const fs = require('fs');
const vm = require('vm');
let options, response, reloads = 0, ends = 0, requests = 0;
const context = {
    Vue: function(o) { options = o; }, url: '/', phuyu_controller: 'ventas/notascredito',
    phuyu_sistema: {phuyu_noti(){},phuyu_alerta(){},phuyu_inicio_guardar(){},phuyu_fin(){ends++},phuyu_modulo(){reloads++}}
};
vm.runInNewContext(fs.readFileSync('phuyu/phuyu_notas/nuevo.js','utf8'), context);
const screen = {estado:0,campos:{codkardex_ref:1,seriecomprobante:'FC01',codmotivonota:1},
    detalle:[{cantidad:1,stock:1,precio:10}],cambio:0,totales:{importe:10},
    $http:{post(){requests++;return {then(ok,fail){if(response==='network')fail.call(screen);else ok.call(screen,{body:response})}}}}};
for (response of [{estado:0,mensaje:'Revisar serie'},'network']) {
    options.methods.phuyu_guardar.call(screen);
    assert.equal(screen.estado,0);
    assert.equal(screen.detalle.length,1);
    assert.equal(reloads,0,'Un error no debe cerrar el formulario');
}
screen.estado=1;options.methods.phuyu_guardar.call(screen);assert.equal(requests,2);
screen.estado=0;screen.detalle[0].cantidad=2;options.methods.phuyu_guardar.call(screen);assert.equal(requests,2);
const item={cantidad:1,precio:8,preciooriginal:10,preciosinigv:8};
options.methods.phuyu_calcular.call({phuyu_totales(){}},item);
assert.equal(item.preciooriginal,10);
assert.equal(ends,2);
console.log('OK: conserva formulario ante errores, evita doble envio, valida cantidad y conserva precio original.');
