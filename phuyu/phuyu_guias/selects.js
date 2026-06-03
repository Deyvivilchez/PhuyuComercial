destinatario();
remitente();
transportista();
conductor();
vehiculo();

function destinatario(){
   var tipo = 0;
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
        if(typeof result.razonsocial != "undefined" && phuyu_operacion.campos.codpersona!= result.codpersona && phuyu_controller != 'compras/compras'){
            phuyu_operacion.phuyu_infodestinatario(result.codpersona,result.razonsocial,result.coddocumentotipo);
        }
        if(typeof result.razonsocial == "undefined"){
          result.razonsocial = phuyu_operacion.campos.destinatario;
        }
        //
        return result.razonsocial;
      },
    });
}

function remitente(){
   var tipo = 0;
    jQuery('#codremitente').select2({
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
        if(typeof result.razonsocial != "undefined" && phuyu_operacion.campos.codremitente!= result.codpersona && phuyu_controller != 'compras/compras'){
            phuyu_operacion.phuyu_inforemitente(result.codpersona,result.razonsocial,result.coddocumentotipo,result.documento);
        }
        if(typeof result.razonsocial == "undefined"){
          result.razonsocial = phuyu_operacion.campos.cliente;
        }
        //
        return result.razonsocial;
      },
    });
}

function transportista(){
   var tipo = 0;
    jQuery('#codtransportista').select2({
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
        if(typeof result.razonsocial != "undefined" && phuyu_operacion.campos.codtransportista!= result.codpersona && phuyu_controller != 'compras/compras'){
            phuyu_operacion.phuyu_infotransportista(result.codpersona,result.razonsocial,result.coddocumentotipo,result.documento);
        }
        if(typeof result.razonsocial == "undefined"){
          result.razonsocial = phuyu_operacion.campos.cliente;
        }
        //
        return result.razonsocial;
      },
    });
}

function conductor(){
    jQuery('#codconductor').select2({
      destroy: 'true',
      ajax: {
        url: url+'ventas/clientes/buscarconductor',
        dataType: 'json',
        delay: 250,
        data: function (params) {
          return {
            search: {value: params.term},
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
        //console.log(result.razonsocial);
        if(typeof result.razonsocial != "undefined" && phuyu_operacion.campos.codconductor!= result.codpersona && phuyu_controller != 'compras/compras'){
            phuyu_operacion.phuyu_infoconductor(result.codpersona,result.razonsocial,result.licenciadeconducir,result.documento,result.coddocumentotipo);
        }
        if(typeof result.razonsocial == "undefined"){
          result.razonsocial = phuyu_operacion.campos.cliente;
        }
        //
        return result.razonsocial;
      },
    });
}

function vehiculo(){
    jQuery('#codvehiculo').select2({
      destroy: 'true',
      ajax: {
        url: url+'administracion/vehiculos/buscar',
        dataType: 'json',
        delay: 250,
        data: function (params) {
          return {
            search: {value: params.term},
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
        var markup = '<div class="clearfix"><div>' + result.nroplaca + '</div>';
        return markup;
      },
      templateSelection: function formatResultSelection(result) {
        console.log(result.nroplaca);
        if(typeof result.nroplaca != "undefined" && phuyu_operacion.campos.codvehiculo!= result.codvehiculo && phuyu_controller != 'compras/compras'){
            phuyu_operacion.phuyu_infovehiculo(result.codvehiculo,result.nroplaca);
        }
        if(typeof result.nroplaca == "undefined"){
          result.nroplaca = phuyu_operacion.campos.cliente;
        }
        //
        return result.nroplaca;
      },
    });
}