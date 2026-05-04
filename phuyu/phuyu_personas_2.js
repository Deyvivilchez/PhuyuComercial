personaselect();
proveedorselect();
socioselect();
sociorefselect();

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
        if (phuyu_controller=="compras/compras" || phuyu_controller=="compras/notascredito" || phuyu_controller=="compras/pedidos" || phuyu_controller=="compras/proformas") {
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
        if(typeof result.razonsocial != "undefined" && phuyu_operacion.campos.codpersona!= result.codpersona){
            phuyu_operacion.phuyu_infocliente();
        }
        if(typeof result.razonsocial == "undefined"){
          result.razonsocial = phuyu_operacion.campos.cliente;
        }
        //
        return result.razonsocial;
      },
    });
    phuyu_select2_velzon('#codpersona');
}

function proveedorselect(){
   var tipo = 2;
   if (phuyu_controller=="compras/compras") {
        tipo = 2;
    }else{
        tipo = 0;
    }
    jQuery('#codproveedor').select2({
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
        if(typeof result.razonsocial != "undefined" && phuyu_form.campos.codpersona!= result.codpersona){
            phuyu_form.phuyu_infocliente(result.codpersona);
        }
        if(typeof result.razonsocial == "undefined"){
          result.razonsocial = phuyu_form.campos.cliente;
        }
        //
        return result.razonsocial;
      },
    });
    phuyu_select2_velzon('#codproveedor');
}

function socioselect(){
   var tipo = 1;
    jQuery('#codsocio').select2({
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
	        if(typeof result.razonsocial != "undefined" && typeof phuyu_operacion !== "undefined" && phuyu_operacion.campos.codsocio != result.codpersona){
	            phuyu_operacion.phuyu_infosocio();
	        }
	        if(typeof result.razonsocial == "undefined"){
	          return result.text;
	        }
	        return result.razonsocial;
	      },
	    });
    phuyu_select2_velzon('#codsocio');
}

function sociorefselect(){
   var tipo = 1;
    jQuery('#codsocioreferencia').select2({
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
	        if(typeof result.razonsocial != "undefined" && typeof phuyu_operacion !== "undefined" && phuyu_operacion.campos.codsocioreferencia != result.codpersona){
	            phuyu_operacion.phuyu_infosocioref();
	        }
	        if(typeof result.razonsocial == "undefined"){
	          return result.text;
	        }
	        return result.razonsocial;
	      },
	    });
    phuyu_select2_velzon('#codsocioreferencia');
}


/*class Select2Controls {
  constructor() {
    // Initialization of the page plugins
    if (!jQuery().select2) {
      console.log('select2 is null!');
      return;
    }

    this._initSelect2Basic();
    this._initSelect2Multiple();
    this._initTags();
    this._initSearchHidden();
    this._initAjax();
    this._Proveedores();
    this._initDataApi();
    this._initTemplating();
    this._initTopLabel();
    this._initFilled();
    this._initFloatingLabel();
  }

  // Basic single select2
  _initSelect2Basic() {
    jQuery('#select2Basic').select2({placeholder: ''});
  }

  // Basic multiple select2
  _initSelect2Multiple() {
    jQuery('#select2Multiple').select2({});
  }

  // Basic select2 tags
  _initTags() {
    var tipo = 1;
    jQuery('#codsocio').select2({
      destroy : 'true',
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
        //console.log(result.razonsocial);
        if(typeof result.razonsocial != "undefined" && phuyu_operacion.campos.codsocio!= result.codpersona){
          phuyu_operacion.phuyu_infosocio();
        }
        //
        return result.razonsocial;
      },
    });
  }

  // No search field
  _initSearchHidden() {
    
var tipo = 1;
    jQuery('#codsocioreferencia').select2({
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
        //console.log(result.razonsocial);
        if(typeof result.razonsocial != "undefined" && phuyu_operacion.campos.codsocioreferencia!= result.codpersona){
          phuyu_operacion.phuyu_infosocioref();
        }
        //
        return result.razonsocial;
      },
    });
  }

  // Ajax api connection
  _initAjax() {
    var tipo = 0;
    if (phuyu_controller=="ventas/ventas" || phuyu_controller=="ventas/pedidos" 
      || phuyu_controller=="ventas/notascredito" || phuyu_controller=="ventas/proformas") {
        tipo = 1;
    }else{
        if (phuyu_controller=="compras/compras") {
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
        if(typeof result.razonsocial != "undefined" && phuyu_operacion.campos.codpersona!= result.codpersona && phuyu_controller != 'compras/compras'){
            phuyu_operacion.phuyu_infocliente();
        }
        if(typeof result.razonsocial == "undefined"){
          result.razonsocial = phuyu_operacion.campos.cliente;
        }
        //
        return result.razonsocial;
      },
    });
  }
  _Proveedores() {
        var tipo = 2;
        jQuery('#codproveedor').select2({
          destroy : 'true',
          ajax: {
            destroy: true,
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
            //console.log(result.razonsocial);
            if(typeof result.razonsocial == "undefined"){
              result.razonsocial = phuyu_form.campos.proveedor;
            }
            //
            return result.razonsocial;
          },
        });
    }

  // Using data- attributes
  _initDataApi() {
    jQuery('#selectDataApi').select2({minimumResultsForSearch: Infinity, placeholder: ''});
  }

  // Basic templating with circles
  _initTemplating() {
    jQuery('#selectTemplating').select2({
      minimumResultsForSearch: Infinity,
      placeholder: '',
      templateSelection: function formatText(item) {
        if (jQuery(item.element).val()) {
          return jQuery(
            '<div><span class="align-middle d-inline-block option-circle me-2 rounded-xl ' +
              jQuery(item.element).data('class') +
              '"></span> <span class="align-middle d-inline-block lh-1">' +
              item.text +
              '</span></div>',
          );
        }
      },
      templateResult: function formatText(item) {
        if (jQuery(item.element).val()) {
          return jQuery(
            '<div><span class="align-middle d-inline-block option-circle me-2 rounded-xl ' +
              jQuery(item.element).data('class') +
              '"></span> <span class="align-middle d-inline-block lh-1">' +
              item.text +
              '</span></div>',
          );
        }
      },
    });
  }

  // Top label input select2
  _initTopLabel() {
    jQuery('#selectTopLabel').select2({minimumResultsForSearch: Infinity, placeholder: ''});
  }

  // Filled input select2
  _initFilled() {
    jQuery('#selectFilled').select2({minimumResultsForSearch: Infinity});
  }

  // Floating label input select2
  _initFloatingLabel() {
    const _this = this;
    jQuery('#selectFloating')
      .select2({minimumResultsForSearch: Infinity, placeholder: ''})
      .on('select2:open', function (e) {
        jQuery(this).addClass('show');
      })
      .on('select2:close', function (e) {
        _this._addFullClassToSelect2(this);
        jQuery(this).removeClass('show');
      });
    this._addFullClassToSelect2(jQuery('#selectFloating'));
  }

  // Helper method for floating label Select2
  _addFullClassToSelect2(el) {
    if (jQuery(el).val() !== '' && jQuery(el).val() !== null) {
      jQuery(el).parent().find('.select2.select2-container').addClass('full');
    } else {
      jQuery(el).parent().find('.select2.select2-container').removeClass('full');
    }
  }
}*/
