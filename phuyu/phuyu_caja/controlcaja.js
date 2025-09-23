var phuyu_datos = new Vue({
	el: "#phuyu_datos",
	data: {estado:0,cargando: true},
	methods: {
		phuyu_controlcaja: function(){
			phuyu_sistema.phuyu_fin();
		},
		phuyu_aperturar: function(){
            var saldar_automaticamente = $("#saldarautomaticamente").val()

            if(saldar_automaticamente == 1){
                swal({
                    title: "SEGURO APERTURAR CAJA ?",   
                    text: "", 
                    icon: "warning",
                    dangerMode: true,
                    buttons: ["CANCELAR", "SI, APERTURAR CAJA"],
                    content: {
                        element: "input",
                        attributes: {
                            placeholder: "INGRESAR EL MONTO DE APERTURA",
                            type: "text",
                        },
                    },
                }).then((willDelete) => {
                    if (willDelete){
                        if($(".swal-content__input").val() == ""){
                            $(".swal-content__input").focus()
                            return;
                        }
                        this.estado = 1; phuyu_sistema.phuyu_inicio_guardar("APERTURANDO CAJA . . .");
                        this.$http.post(url+phuyu_controller+"/phuyu_aperturar",{"monto_apertura":$(".swal-content__input").val()}).then(function(data){
                            if (data.body==1) {
                                phuyu_sistema.phuyu_alerta("CAJA APERTURADA CORRECTAMENTE", "CAJA INICIADA","success");
                            }else{
                                phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR", "NO SE PUEDE APERTURAR CAJA","error");
                            }
                            phuyu_sistema.phuyu_fin(); phuyu_sistema.phuyu_modulo();
                        }, function(){
                            phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR", "NO SE PUEDE APERTURAR CAJA","error"); phuyu_sistema.phuyu_fin();
                        });
                    }
                });
            }else{
                swal({
                    title: "SEGURO APERTURAR CAJA ?",   
                    text: "", 
                    icon: "warning",
                    dangerMode: true,
                    buttons: ["CANCELAR", "SI, APERTURAR CAJA"],
                }).then((willDelete) => {
                    if (willDelete){
                        this.estado = 1; phuyu_sistema.phuyu_inicio_guardar("APERTURANDO CAJA . . .");
                        this.$http.post(url+phuyu_controller+"/phuyu_aperturar").then(function(data){
                            if (data.body==1) {
                                phuyu_sistema.phuyu_alerta("CAJA APERTURADA CORRECTAMENTE", "CAJA INICIADA","success");
                            }else{
                                phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR", "NO SE PUEDE APERTURAR CAJA","error");
                            }
                            phuyu_sistema.phuyu_fin(); phuyu_sistema.phuyu_modulo();
                        }, function(){
                            phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR", "NO SE PUEDE APERTURAR CAJA","error"); phuyu_sistema.phuyu_fin();
                        });
                    }
                });
            }
		},
		phuyu_cerrarcaja: function(){
            phuyu_sistema.phuyu_inicio();
            this.$http.post(url+phuyu_controller).then(function(data){
                $("#phuyu_sistema").empty().html(data.body);
                swal({
                    title: "CERRAR CAJA CON "+$("#saldo_actual").text(),   
                    text: "SEGURO DESEA CERRAR LA CAJA APERTURADA ?", 
                    icon: "warning",
                    dangerMode: true,
                    buttons: ["CANCELAR", "SI, CERRAR CAJA"],
                }).then((willDelete) => {
                    if (willDelete){
                        this.$http.post(url+phuyu_controller+"/phuyu_cerrar").then(function(data){
                            if (data=="e") {
                                phuyu_sistema.phuyu_alerta("ESTIMADO USUARIO, DEBE INGRESAR NUEVAMENTE","SU SESION A VENCIDO EN EL SISTEMA","error");
                            }else{
                                if (data.body==1) {
                                    phuyu_sistema.phuyu_alerta("CAJA CERRADA CORRECTAMENTE", "CAJA CERRADA","success"); phuyu_sistema.phuyu_modulo();
                                }else{
                                    phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR", "NO SE PUEDE CERRAR CAJA","error");
                                }
                            }
                        }, function(){
                            phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR", "NO SE PUEDE CERRAR CAJA","error");
                        });
                    }
                });
            },function(){
                this.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); this.phuyu_fin();
            });
		},

        // REPORTES PDF DE CAJA //
        pdf_movimientos: function(){
            var phuyu_url = url+phuyu_controller+"/pdf_movimientos/"+$("#f_desde").val()+"/"+$("#f_hasta").val();
            window.open(phuyu_url,"_blank");
            //$("#phuyu_pdf").attr("src",phuyu_url); $("#modal_reportes").modal("show");
        },
        pdf_arqueo: function(){
            var phuyu_url = url+phuyu_controller+"/pdf_arqueo/"+$("#f_arqueo").val();
            window.open(phuyu_url,"_blank");
            //$("#phuyu_pdf").attr("src",phuyu_url); $("#modal_reportes").modal("show");
        },
        pdf_arqueo_caja: function(){
            var phuyu_url = url+phuyu_controller+"/pdf_arqueo_caja/"+$("#estadocaja").val();
            window.open(phuyu_url,"_blank");
            //$("#phuyu_pdf").attr("src",phuyu_url); $("#modal_reportes").modal("show");
        },
        pdf_arqueo_excel: function(){
            var phuyu_url = url+phuyu_controller+"/pdf_arqueo_excel/"+$("#estadocaja").val();
            window.open(phuyu_url,"_blank");
        }
	},
	created: function(){
		this.phuyu_controlcaja();
	}
});

$(document).ready(function () {
    if ($("#estadocaja").val()==0) {
        phuyu_graficocaja();
    }
});

function phuyu_graficocaja(){
    $.getJSON(url+phuyu_controller+"/phuyu_graficocaja", function(data) {
        Highcharts.chart("phuyu_graficocaja", {
            chart: { type: "column" },
            title: { text: "GRAFICOS DE CAJA" },
            subtitle: { text: 'Sistema comercial: webphuyu.com' },
            xAxis: {
                categories: ["CAJA","BANCOS"],
                crosshair: true
            },
            yAxis: {
                min: 0,
                title: {text: "SOLES (S/.)"}
            },
            tooltip: {
                headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                    '<td style="padding:0"><b>S/. {point.y:.1f}</b></td></tr>',
                footerFormat: '</table>',
                shared: true,
                useHTML: true
            },
            plotOptions: {
                column: {
                    pointPadding: 0.2,
                    borderWidth: 0
                }
            },
            series: [{
                name: "INGRESOS",
                data: data.ingresos
            }, {
                name: "EGRESOS",
                data: data.egresos
            }]
        });
    });
}