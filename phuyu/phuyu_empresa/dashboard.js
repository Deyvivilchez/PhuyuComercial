var phuyu_datos = new Vue({
	el: "#phuyu_datos",
	data: {
		totales:{"estado":"CERRADA","caja":0,"banco":0,"general":0}
	},
	methods: {
		phuyu_totales: function(){
			this.$http.get(url+phuyu_controller+"/phuyu_totales").then(function(data){
				this.totales.estado = data.body.estado; this.totales.caja = data.body.caja;
				this.totales.banco = data.body.banco; this.totales.general = data.body.general;
				phuyu_sistema.phuyu_fin(); // phuyu_pagos();
			});
		}
	},
	created: function(){
		this.phuyu_totales();
	}
});

function phuyu_pagos(){
	$.getJSON(url+phuyu_controller+"/phuyu_pagos", function(data) {
        Highcharts.chart("phuyu_ingresos", {
		    chart: {
		        plotBackgroundColor: null, plotBorderWidth: null, plotShadow: false, type: "pie"
		    },
		    title: { text: '' },
		    tooltip: { pointFormat: '{series.name}: <b>{point.y:.1f}</b>' },
		    plotOptions: {
		        pie: {
		            allowPointSelect: true,
		            cursor: "pointer",
		            dataLabels: {
		                enabled: false
		            },
		            showInLegend: true
		        }
		    },
		    series: [{
		        name: "S/. ",
		        colorByPoint: true,
		        data: data.ingresos
		    }]
		});

		Highcharts.chart("phuyu_egresos", {
		    chart: {
		        plotBackgroundColor: null, plotBorderWidth: null, plotShadow: false, type: "pie"
		    },
		    title: { text: '' },
		    tooltip: { pointFormat: '{series.name}: <b>{point.y:.1f}</b>' },
		    plotOptions: {
		        pie: {
		            allowPointSelect: true,
		            cursor: "pointer",
		            dataLabels: {
		                enabled: false
		            },
		            showInLegend: true
		        }
		    },
		    series: [{
		        name: "S/. ",
		        colorByPoint: true,
		        data: data.egresos
		    }]
		});
    });
}