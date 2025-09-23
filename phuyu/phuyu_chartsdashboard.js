charventasvendedor();
charvendedor();
function _initEvents() {
  this._roundedBarChart && this._roundedBarChart.destroy();
  charvendedor();
}
function charvendedor() {
  var datos = $("#estado").val();
  if (document.getElementById('roundedBarChart')) {
    $.getJSON(url+phuyu_controller+"/ver_pedidos?datos="+datos, function(data) {
      const barChart = document.getElementById('roundedBarChart').getContext('2d');
      this._roundedBarChart = new Chart(barChart, {
        type: 'bar',
        options: {
          cornerRadius: parseInt(Globals.borderRadiusMd),
          plugins: {
            crosshair: false,
            datalabels: {display: false},
          },
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            yAxes: [
              {
                gridLines: {
                  display: true,
                  lineWidth: 1,
                  color: Globals.separatorLight,
                  drawBorder: false,
                },
                ticks: {
                  beginAtZero: true,
                  stepSize: data.rango,
                  min: 0,
                  max: data.maximo,
                  padding: 20,
                },
              },
            ],
            xAxes: [
              {
                gridLines: {display: false},
              },
            ],
          },
          legend: {
            position: 'bottom',
            labels: ChartsExtend.LegendLabels(),
          },
          tooltips: ChartsExtend.ChartTooltip(),
        },
        data: {
          labels: ['Enero', 'Febrero', 'Marzo', 'Abril','Mayo','Junio','Julio'
          ,'Agosto','Setiembre','Octubre','Noviembre','Diciembre'],
          datasets: [
            {
              label: 'Vendidos',
              borderColor: Globals.primary,
              backgroundColor: 'rgba(' + Globals.primaryrgb + ',0.1)',
              data: data.totalesc,
              borderWidth: 2,
            },
            {
              label: 'Pendientes',
              borderColor: Globals.secondary,
              backgroundColor: 'rgba(231,30,30,0.54)',
              data: data.totalesp,
              borderWidth: 1,
            },
          ],
        },
      });
    });
  }
}

function charventasvendedor() {
  if (document.getElementById('roundedBarChart1')) {
    $.getJSON(url+phuyu_controller+"/ver_pedidosventas", function(data) {
      const barChart = document.getElementById('roundedBarChart1').getContext('2d');
      this._roundedBarChart1 = new Chart(barChart, {
        type: 'bar',
        options: {
          cornerRadius: parseInt(Globals.borderRadiusMd),
          plugins: {
            crosshair: false,
            datalabels: {display: false},
          },
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            yAxes: [
              {
                gridLines: {
                  display: true,
                  lineWidth: 1,
                  color: Globals.separatorLight,
                  drawBorder: false,
                },
                ticks: {
                  beginAtZero: true,
                  stepSize: data.rango,
                  min: 0,
                  max: data.maximo,
                  padding: 20,
                },
              },
            ],
            xAxes: [
              {
                gridLines: {display: false},
              },
            ],
          },
          legend: {
            position: 'bottom',
            labels: ChartsExtend.LegendLabels(),
          },
          tooltips: ChartsExtend.ChartTooltip(),
        },
        data: {
          labels: ['Enero', 'Febrero', 'Marzo', 'Abril','Mayo','Junio','Julio'
          ,'Agosto','Setiembre','Octubre','Noviembre','Diciembre'],
          datasets: [
            {
              label: 'Vendidos',
              borderColor: Globals.primary,
              backgroundColor: 'rgba(' + Globals.primaryrgb + ',0.1)',
              data: data.totalesc,
              borderWidth: 2,
            }
          ],
        },
      });
    });
  }
}