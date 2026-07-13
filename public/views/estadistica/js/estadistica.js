$(function () {

    (function init() {
        loadChartsBarras2();
        loadChartsBarras();
        loadChartsTorta();
    })();

    /**------------Torta---------------- */
    function loadChartsTorta() {

        $.ajax({
            type: 'GET',
            url: `/api/pedido/cantidadProductosVendidos`,
            dataType: 'json',
            data: {},
            beforeSend: function () {
                $('.spinner-chart').toggleClass('d-none');
            },
        }).fail((jqXHR) => {
            flashy.error(jqXHR.responseJSON.message);
        }).done(response => {
            loadTortaProductoMasVendido(response.data);
        }).always(() => {
            $('.spinner-chart').toggleClass('d-none');
        });
    }

    function loadTortaProductoMasVendido(data) {
        var options = {
        series: data.series,
    
        chart: {
            width: 500,
            type: 'pie',
    
            toolbar: {
                show: true,
                tools: {
                    download: true,
                    selection: false,
                    zoom: false,
                    zoomin: false,
                    zoomout: false,
                    pan: false,
                    reset: false
                }
            }
        },
    
        labels: data.labels,
    
        legend: {
            show: false,
        },
        plotOptions: {
            pie: {
                dataLabels: {
                    external: {
                    show: true,
                    },
                },
            },
        },
        responsive: [{
            breakpoint: 480,
                options: {
                    chart: {
                        width: 320
                    }
                }
            }]
        };
    
        var chart = new ApexCharts(document.querySelector("#chart"), options);
        chart.render();
    }

    /**------------Barra-------------- */

    function loadChartsBarras() {

        $.ajax({
            type: 'GET',
            url: `/api/pedido/cantidadVentasSemana`,
            dataType: 'json',
            data: {},
            beforeSend: function () {
                $('.spinner-chart2').toggleClass('d-none');
            },
        }).fail((jqXHR) => {
            flashy.error(jqXHR.responseJSON.message);
        }).done(response => {
            loadBarrasVentasSemana(response.data);
        }).always(() => {
            $('.spinner-chart2').toggleClass('d-none');
        });
    }

    function loadBarrasVentasSemana(data){

        var options = {
            chart: {
                type: 'bar',
                height: 300
            },
            series: [{
                name: 'Cantidad',
                data: data.series, //[44, 55, 57, 56, 61, 58, 70]
            }],
            xaxis: {
                categories: data.labels //['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo']
            }
        };
    
        var chart = new ApexCharts(
            document.querySelector("#chart2"),
            options
        );
    
        chart.render();
    }

    

    /**------------Barra2---------------- */

    
    function loadChartsBarras2(){
        $.ajax({
            type: 'GET',
            url: `/api/pedido/cantidadProductosVendidosSemana`,
            dataType: 'json',
            data: {},
            beforeSend: function () {
                $('.spinner-chart3').toggleClass('d-none');
            },
        }).fail((jqXHR) => {
            flashy.error(jqXHR.responseJSON.message);
        }).done(response => {
            loadBarrasProductosVendidosSemana(response.data);
            loadRanking(response.data);
        }).always(() => {
            $('.spinner-chart3').toggleClass('d-none');
        });
    }

    function loadBarrasProductosVendidosSemana(data){
        var options = {
            series: data.series, 
            chart: {
                type: 'bar',
                height: 350,
                width: '100%',
            },
            plotOptions: {
                bar: {
                horizontal: false,
                columnWidth: '55%',
                borderRadius: 5,
                borderRadiusApplication: 'end',
                },
            },
            dataLabels: {
                enabled: false,
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent'],
            },
            xaxis: {
                categories: data.labels,
            },
            yaxis: {
                title: {
                text: 'Cantidad',
                },
            },
            fill: {
                opacity: 1,
            },
            tooltip: {
                y: {
                formatter: function (val) {
                    return val
                },
                },
            },
        }

        var chart = new ApexCharts(document.querySelector('#chart3'), options)
        chart.render()
    }

    function loadRanking(data){
        const $ranking = $("#rankingProductos");

        $ranking.empty();

        $.each(data.ranking, function (index, producto) {

            let icono = "";

            switch (index) {
                case 0:
                    icono = '<i class="bi bi-1-circle-fill"></i>';
                    break;

                case 1:
                    icono = '<i class="bi bi-2-circle"></i>';
                    break;

                case 2:
                    icono = '<i class="bi bi-3-circle"></i>';
                    break;

                default:
                    icono = "";
            }

            $ranking.append(`
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>${icono} ${producto.nombre}</span>
                    <strong>${producto.cantidad}</strong>
                </li>
            `);
        });
    }

});