<!-- content @s -->
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="nk-block-head nk-block-head-sm">
                    <div class="nk-block-between">
                        <div class="nk-block-head-content">
                            <h2><?php echo $h1 ?></h2>
                            <div class="nk-block-des text-soft">
                                <h4><?php echo $h2 ?></h4><br>
                            </div>
                        </div><!-- .nk-block-head-content -->
                    </div><!-- .nk-block-between -->
                </div><!-- .nk-block-head -->
                <div id="discenti_js" style="display: none;" data-json='<?php echo $discenti_js; ?>'></div>
                <div id="pagamento_js" style="display: none;" data-json='<?php echo $pagamento_js; ?>'></div>
                <div class="nk-block">
                    <div class="row g-gs">

                        <!-- .col -->
                        <div class="col-md-7">
                            <div class="card card-bordered h-60">
                                <div class="card-inner">
                                    <h6 class="card-title fw-bold mb-2">Incassi Mensili</h6>
                                    <div class="card-inner">
                                        <canvas class="line-chart" id="filledLineChart"></canvas>
                                    </div>
                                </div>
                            </div><!-- .card -->
                        </div><!-- .col -->
                        <div class="col-md-5">
                            <div class="card card-bordered h-100">
                                <div class="card-inner">
                                    <h6 class="card-title fw-bold mb-2">Discenti Mensili</h6>
                                    <div class="card-inner">
                                        <div class="nk-ck-sm" style="margin-top: 10%;">
                                            <canvas class="pie-chart" id="pieChartData"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- .card -->
                        </div><!-- .col -->
                        <div class="col-md-12col-xxl-3">
                            <div class="card card-bordered h-100">
                                <div class="nk-ck">
                                    <canvas class="line-chart" id="solidLineChart"></canvas>
                                </div>
                            </div><!-- .card -->
                        </div><!-- .col -->
                    </div><!-- .row -->
                </div><!-- .nk-block -->
            </div>
        </div>
    </div>
</div>
<!-- Script per passare gli eventi PHP a JavaScript -->
<script src="/assets/js/bundle.js?ver=3.1.3"></script>
<script src="/assets/js/scripts.js?ver=3.1.3"></script>

<script>
    //Numero Discenti
    var jsonData = document.getElementById("discenti_js").dataset.json;
    var discenti_js = JSON.parse(jsonData);

    const jsonMensile = document.getElementById("pagamento_js").dataset.json;
    const pagamentoMensile = JSON.parse(jsonMensile);

    const labelArray = Object.keys(pagamentoMensile).map(m => {
        const [y, mth] = m.split("-");
        return new Date(y, mth - 1)
            .toLocaleString('it-IT', { month: 'long', year: 'numeric' });
    });


    const dataArray = Object.values(pagamentoMensile);

    const filledLineChart = {
        labels: labelArray,
        dataUnit: '€',
        lineTension: .4,
        datasets: [{
            label: "Totale Spesa Mensile",
            color: "#9d72ff",
            background: NioApp.hexRGB('#9d72ff', .4),
            data: dataArray
        }]
    };


    var solidLineChart = {
        labels: ["Gennaio", "Febbraio", "Marzo", "Aprile", "Maggio", "Giugno", "Luglio", "Agosto", "Settembre", "Ottobre", "Novembre", "Dicembre"],
        dataUnit: 'Discenti',
        lineTension: .4,
        legend: true,
        datasets: [{
            label: "Andamento Studenti",
            color: "#5ce0aa",
            background: 'transparent',
            data: ['']
        }]
    };

    var pieChartData = {
        labels: ["Gennaio", "Febbraio", "Marzo", "Aprile", "Maggio", "Giugno", "Luglio", "Agosto", "Settembre", "Ottobre", "Novembre", "Dicembre"],
        dataUnit: 'Discenti Iscritti',
        legend: false,
        datasets: [{
            borderColor: "#fff",
            background: [
                '#377eb8',
                '#e41a1c',
                '#4daf4a',
                '#984ea3',
                '#ff7f00',
                '#a6cee3',
                '#b2df8a',
                '#fdbf6f',
                '#cab2d6',
                '#ffff99',
                '#6a3d9a',
                '#b15928'
            ],
            data: discenti_js
        }]
    };

    function lineChart(selector, set_data) {
        var $selector = selector ? $(selector) : $('.line-chart');
        $selector.each(function () {
            var $self = $(this),
                _self_id = $self.attr('id'),
                _get_data = typeof set_data === 'undefined' ? eval(_self_id) : set_data;
            var selectCanvas = document.getElementById(_self_id).getContext("2d");
            var chart_data = [];
            for (var i = 0; i < _get_data.datasets.length; i++) {
                chart_data.push({
                    label: _get_data.datasets[i].label,
                    tension: _get_data.lineTension,
                    backgroundColor: _get_data.datasets[i].background,
                    borderWidth: 2,
                    borderColor: _get_data.datasets[i].color,
                    pointBorderColor: _get_data.datasets[i].color,
                    pointBackgroundColor: '#fff',
                    pointHoverBackgroundColor: "#fff",
                    pointHoverBorderColor: _get_data.datasets[i].color,
                    pointBorderWidth: 2,
                    pointHoverRadius: 4,
                    pointHoverBorderWidth: 2,
                    pointRadius: 4,
                    pointHitRadius: 4,
                    data: _get_data.datasets[i].data
                });
            }
            var chart = new Chart(selectCanvas, {
                type: 'line',
                data: {
                    labels: _get_data.labels,
                    datasets: chart_data
                },
                options: {
                    legend: {
                        display: _get_data.legend ? _get_data.legend : false,
                        rtl: NioApp.State.isRTL,
                        labels: {
                            boxWidth: 12,
                            padding: 20,
                            fontColor: '#6783b8'
                        }
                    },
                    maintainAspectRatio: false,
                    tooltips: {
                        enabled: true,
                        rtl: NioApp.State.isRTL,
                        callbacks: {
                            title: function title(tooltipItem, data) {
                                return data['labels'][tooltipItem[0]['index']];
                            },
                            label: function label(tooltipItem, data) {
                                return data.datasets[tooltipItem.datasetIndex]['data'][tooltipItem['index']] + ' ' + _get_data.dataUnit;
                            }
                        },
                        backgroundColor: '#eff6ff',
                        titleFontSize: 13,
                        titleFontColor: '#6783b8',
                        titleMarginBottom: 6,
                        bodyFontColor: '#9eaecf',
                        bodyFontSize: 12,
                        bodySpacing: 4,
                        yPadding: 10,
                        xPadding: 10,
                        footerMarginTop: 0,
                        displayColors: false
                    },
                    scales: {
                        yAxes: [{
                            display: true,
                            position: NioApp.State.isRTL ? "right" : "left",
                            ticks: {
                                beginAtZero: false,
                                fontSize: 12,
                                fontColor: '#9eaecf',
                                padding: 10
                            },
                            gridLines: {
                                color: NioApp.hexRGB("#526484", .2),
                                tickMarkLength: 0,
                                zeroLineColor: NioApp.hexRGB("#526484", .2)
                            }
                        }],
                        xAxes: [{
                            display: true,
                            ticks: {
                                fontSize: 12,
                                fontColor: '#9eaecf',
                                source: 'auto',
                                padding: 5,
                                reverse: NioApp.State.isRTL
                            },
                            gridLines: {
                                color: "transparent",
                                tickMarkLength: 10,
                                zeroLineColor: NioApp.hexRGB("#526484", .2),
                                offsetGridLines: true
                            }
                        }]
                    }
                }
            });
        });
    }

    function pieChart(selector, set_data) {
        var $selector = selector ? $(selector) : $('.pie-chart');
        $selector.each(function () {
            var $self = $(this),
                _self_id = $self.attr('id'),
                _get_data = typeof set_data === 'undefined' ? eval(_self_id) : set_data;
            var selectCanvas = document.getElementById(_self_id).getContext("2d");
            var chart_data = [];
            for (var i = 0; i < _get_data.datasets.length; i++) {
                chart_data.push({
                    backgroundColor: _get_data.datasets[i].background,
                    borderWidth: 2,
                    borderColor: _get_data.datasets[i].borderColor,
                    hoverBorderColor: _get_data.datasets[i].borderColor,
                    data: _get_data.datasets[i].data
                });
            }
            var chart = new Chart(selectCanvas, {
                type: 'pie',
                data: {
                    labels: _get_data.labels,
                    datasets: chart_data
                },
                options: {
                    legend: {
                        display: _get_data.legend ? _get_data.legend : false,
                        rtl: NioApp.State.isRTL,
                        labels: {
                            boxWidth: 12,
                            padding: 20,
                            fontColor: '#6783b8'
                        }
                    },
                    rotation: -.2,
                    maintainAspectRatio: false,
                    tooltips: {
                        enabled: true,
                        rtl: NioApp.State.isRTL,
                        callbacks: {
                            title: function title(tooltipItem, data) {
                                return data['labels'][tooltipItem[0]['index']];
                            },
                            label: function label(tooltipItem, data) {
                                return data.datasets[tooltipItem.datasetIndex]['data'][tooltipItem['index']] + ' ' + _get_data.dataUnit;
                            }
                        },
                        backgroundColor: '#eff6ff',
                        titleFontSize: 13,
                        titleFontColor: '#6783b8',
                        titleMarginBottom: 6,
                        bodyFontColor: '#9eaecf',
                        bodyFontSize: 12,
                        bodySpacing: 4,
                        yPadding: 10,
                        xPadding: 10,
                        footerMarginTop: 0,
                        displayColors: false
                    }
                }
            });
        });
    }

    // init line chart
    lineChart('#filledLineChart', filledLineChart);
    lineChart('#solidLineChart', solidLineChart);
    // init pie chart
    pieChart('#pieChartData', pieChartData);
</script>