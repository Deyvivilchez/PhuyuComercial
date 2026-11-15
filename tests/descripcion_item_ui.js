const fs=require('fs'),vm=require('vm'),assert=require('assert');
const context={};vm.runInNewContext(fs.readFileSync('phuyu/phuyu_ventas/descripcion_item.js','utf8'),context);
const editor=context.PhuyuDescripcionItem;
for (const texto of ['Alcance del servicio\n\nCondiciones: instalación y garantía','• Instalación\n• Pruebas\n  • Entrega','1. Diagnóstico\n2. Reparación','Árbol <script>alert(1)</script> & servicio', 'a'.repeat(5000)]) {
 assert.equal(editor.desdeDelta(editor.aDelta(texto)),texto);
}
assert.equal(editor.desdeDelta({ops:[{insert:'Trabajo'},{insert:'\n',attributes:{list:'bullet'}},{insert:'Entrega'},{insert:'\n',attributes:{list:'bullet'}}]}),'• Trabajo\n• Entrega');
console.log('OK: parrafos, listas, sangria, caracteres especiales y 5000 caracteres sin HTML.');
// Integracion del editor con el item seleccionado y rechazo del exceso de texto.
let opts,notices=0;
const element={};
context.Vue=function(o){opts=o;};
context.$=()=>({val:()=>0});
context.document={getElementById:()=>element,addEventListener(){}};
context.window={};
context.phuyu_sistema={phuyu_noti(){notices++;}};
context.Quill=function(container){this.container=container;this.handlers={};this.setContents=function(d){this.contents=d;};this.getContents=function(){return this.contents;};this.on=function(e,fn){this.handlers[e]=fn;};};
vm.runInNewContext(fs.readFileSync('phuyu/phuyu_ventas/nuevo.js','utf8'),context);
const screen={item:{descripcion:'Texto anterior'}};
const init=opts.methods.phuyu_editor_descripcion;
init.call(screen);
assert.equal(screen.descripcion_editor_activo,true);
const quill=screen._descripcionQuill;
quill.contents=editor.aDelta('• Servicio con ñ\n• Garantía');
quill.handlers['text-change']({},editor.aDelta('Texto anterior'),'user');
assert.equal(screen.item.descripcion,'• Servicio con ñ\n• Garantía');
quill.contents=editor.aDelta('a'.repeat(5001));
quill.handlers['text-change']({},editor.aDelta(screen.item.descripcion),'user');
assert.equal(notices,1);assert.equal(screen.item.descripcion,'• Servicio con ñ\n• Garantía');
screen.item={descripcion:'Otro producto'};init.call(screen);
assert.equal(editor.desdeDelta(quill.contents),'Otro producto');
console.log('OK: sincroniza el item correcto, limita el pegado y carga otro producto sin perder datos.');
