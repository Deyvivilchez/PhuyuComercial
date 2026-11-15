const fs=require('fs'),vm=require('vm'),assert=require('assert');
const texto='Alcance del servicio\n• Diagnóstico y pruebas\n• Garantía\n\n'+ 'Descripción con ñ y tildes. '.repeat(100);
for (const name of ['nuevo','editar']) {
 let options;
 const context={Vue:function(o){options=o;}, $:()=>({val:()=>0,modal(){}}),document:{addEventListener(){},getElementById:()=>null},window:{},PhuyuDescripcionItem:{inicializar(screen){assert.equal(screen.item.descripcion,texto);screen.editorAbierto=true;}}};
 vm.runInNewContext(fs.readFileSync('phuyu/phuyu_proformas/'+name+'.js','utf8'),context);
 const screen={item:{},$nextTick(fn){fn.call(this);}};
 const producto={descripcion:texto};options.methods.phuyu_itemdetalle.call(screen,0,producto);
 assert.strictEqual(screen.item,producto);assert.equal(screen.editorAbierto,true);
}
let options;
const $=()=>({val:()=>0});$.each=(values,fn)=>values.forEach((v,i)=>fn.call(v,i,v));
const context={Vue:function(o){options=o;}, $,document:{addEventListener(){}},window:{},url:'/',phuyu_sistema:{phuyu_alerta(){throw Error('No debe dar error');}}};
vm.runInNewContext(fs.readFileSync('phuyu/phuyu_ventas/nuevo.js','utf8'),context);
const producto={descripcion:texto,codproducto:1,codunidad:1,producto:'Servicio',unidades:'1|UNIDAD|1|10|0|0|0|0|1',cantidad:1,cantidadcomprobante:0,preciosinigv:10,preciounitario:10,icbper:0,igv:0,stock:10,control:0,factor:1};
const screen={codproforma:0,campos:{},phuyu_seleccionar_cliente(){},phuyu_totales(){},phuyu_series(){},phuyu_validar_stock_detalle(){return true;},$http:{get(url){return {then(fn){fn.call(screen,{body:url.includes('buscarproductos')?[producto]:[{documento:'12345678',coddocumentotipo:1}]});}}}}};
options.methods.phuyu_addproforma.call(screen,{codproforma:12,codpersona:5,razonsocial:'Cliente',direccion:'Direccion',condicionpago:1});
assert.equal(screen.detalle[0].descripcion,texto);
assert(texto.length>250);
console.log('OK: crear/editar proforma y pasar a venta conservan descripcion larga, listas y saltos.');
