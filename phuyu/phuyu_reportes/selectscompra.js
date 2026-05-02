destinatario();
function phuyu_select2_velzon(selector){
  var $select = jQuery(selector);
  if (!$select.length) {
    return;
  }

  phuyu_select2_velzon_style();
  $select.next('.select2-container').addClass('phuyu-select2-velzon');
}

function phuyu_select2_velzon_style(){
  if (document.getElementById('phuyu-select2-velzon-style')) {
    return;
  }

  jQuery('head').append(
    '<style id="phuyu-select2-velzon-style">' +
      '.phuyu-select2-velzon.select2-container{width:100%!important;}' +
      '.phuyu-select2-velzon .select2-selection--single{display:flex!important;align-items:center!important;height:40px!important;min-height:40px!important;border:1px solid rgba(64,81,137,.16)!important;border-radius:.375rem!important;background:#fff!important;box-shadow:none!important;}' +
      '.phuyu-select2-velzon .select2-selection__rendered{line-height:40px!important;padding-left:.75rem!important;padding-right:2rem!important;font-size:.86rem!important;font-weight:600!important;color:#343a40!important;}' +
      '.phuyu-select2-velzon .select2-selection__arrow{height:40px!important;right:.25rem!important;}' +
      '.phuyu-select2-velzon.select2-container--default.select2-container--focus .select2-selection--single,.phuyu-select2-velzon.select2-container--default.select2-container--open .select2-selection--single,.phuyu-select2-velzon.select2-container--bootstrap4.select2-container--focus .select2-selection--single,.phuyu-select2-velzon.select2-container--bootstrap4.select2-container--open .select2-selection--single{border-color:#405189!important;}' +
    '</style>'
  );
}

function destinatario(){
    var tipo = 2;
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
    phuyu_select2_velzon('#codpersona');
}
