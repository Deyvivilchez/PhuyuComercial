personaselect();
function personaselect(){
   var tipo = 0;
    if (phuyu_controller=="ventas/ventas" || phuyu_controller=="ventas/pedidos" 
      || phuyu_controller=="ventas/notascredito" || phuyu_controller=="ventas/proformas") {
        tipo = 1;
    }else{
        if (phuyu_controller=="compras/compras" || phuyu_controller=="compras/notascredito") {
            tipo = 2;
        }else{
            tipo = 0;
        }
    }
    jQuery('#codpersona').select2({
      destroy: 'true',
      ajax: {
        url: url+'ventas/clientes/buscar',
        dataType: 'json',
        delay: 250,
        data: function (params) {
          return {
            search: {value: params.term, tipo:tipo},
            page: params.page
          };
        },
        processResults: function (data, page) {
          return {
            results: data.data,
          };
        },
        cache: true,
      },
      placeholder: 'Search',
      escapeMarkup: function (markup) {
        return markup;
      },
      minimumInputLength: 1,
      templateResult: function formatResult(result) {
        if (result.loading) return result.text;
        var markup = '<div class="clearfix"><div>' + result.documento + '</div>';
        if (result.razonsocial) {
          markup += '<div class="text-muted">' + result.razonsocial + '</div>';
        }
        return markup;
      },
      templateSelection: function formatResultSelection(result) {
        console.log(result.razonsocial);
        if(typeof result.razonsocial != "undefined" && phuyu_datos.campos.codpersona!= result.codpersona && phuyu_controller != 'compras/compras'){
            phuyu_datos.phuyu_infocliente(result.codpersona);
        }
        if(typeof result.razonsocial == "undefined"){
          result.razonsocial = phuyu_datos.campos.cliente;
        }
        //
        return result.razonsocial;
      },
    });
}