personaselect();
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
    var $codpersona = jQuery('#codpersona');
    if ($codpersona.data('select2')) {
      $codpersona.select2('destroy');
    }

    $codpersona.select2({
      destroy: 'true',
      theme: 'bootstrap4',
      width: '100%',
      dropdownParent: $('#phuyu_movimiento'),
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
      placeholder: 'Buscar socio...',
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
        if(typeof result.razonsocial != "undefined"){
          phuyu_movimiento.campos.codpersona = result.codpersona || result.id;
          phuyu_movimiento.campos.cliente = result.razonsocial;
        }
        if(typeof result.razonsocial == "undefined"){
          result.razonsocial = result.text || phuyu_movimiento.campos.cliente || "Buscar socio...";
        }
        return result.razonsocial;
      },
    });

    $codpersona.off('select2:select.phuyuCaja').on('select2:select.phuyuCaja', function (e) {
      var result = e.params.data || {};
      phuyu_movimiento.campos.codpersona = result.codpersona || result.id || $codpersona.val();
      phuyu_movimiento.campos.cliente = result.razonsocial || result.text || "";
    });

    phuyu_select2_velzon('#codpersona');
}
