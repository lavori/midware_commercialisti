<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h1 class="page-title"><?= htmlspecialchars($h1 ?? 'Reportistica Prodotti') ?></h1>
            <div class="nk-block-des text-soft">
                <h2><?= htmlspecialchars($h2 ?? '') ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- Ricerca -->
<div class="nk-block nk-block-lg" id="reportistica-root">
    <div class="card card-bordered card-preview">
        <div class="card-inner">
            <form method="get" action="<?= htmlspecialchars($serp) ?>" class="row g-2 align-items-end mb-2">
                <div class="col-md-10">
                    <label class="form-label">Seleziona Prodotto</label>
                    <select name="did" class="form-select js-select2" data-search="on">
                        <option value="Seleziona" selected disabled>Seleziona</option>
                        <option value="percorsiLaurea">Percorso di laurea</option>
                        <option value="master">Master</option>
                        <option value="esamiSingoli">Esame singolo</option>
                        <option value="certificazioni">Certificazioni</option>
                        <option value="tutoraggio">Tutoraggio</option>
                    </select>
                </div>
                <div class="col-auto"><button class="btn btn-primary">Cerca</button></div>
            </form>
        </div>
    </div>
</div>
<div id="TotaleIncassi" style="display: none;" data-json='<?php echo $TotaleIncassiJson; ?>'></div>

<div class="row g-gs">
    <div class="col-lg-6 col-xxl-6">
        <div class="card card-bordered">
            <div class="card-inner">
            </div>
        </div><!-- .card -->
    </div><!-- .col -->
    <div class="col-md-6 col-lg-5 col-xxl-6">
        <div class="card card-bordered">
            <div class="card-inner">
                <div class="nk-ck-sm" style="height: 25%;">
                    <canvas class="pie-chart" id="pieChartData"></canvas>
                </div>
            </div>
        </div><!-- .card -->
    </div><!-- .col -->
</div>

<script src="./assets/js/bundle.js?ver=3.1.3"></script>
<script>
    (function () {
        if (typeof Chart === 'undefined') { console.error('Chart.js non è caricato.'); return; }
        // Dati
        const holder = document.getElementById('TotaleIncassi');
        let data = [];
        try { data = JSON.parse(holder?.dataset?.json || '[]').map(Number); } catch (_) { data = []; }
        const labels = ["Percorso di Laurea", "Master", "Esame Singolo", "Certificazione", "Tutoraggio"];
        if (data.length > labels.length) data = data.slice(0, labels.length);
        if (data.length < labels.length) data = data.concat(new Array(labels.length - data.length).fill(0));
        const bg = ['#9d72ff', '#8051c6', '#6b3f99', '#543079', '#422461'].slice(0, labels.length);
        const canvas = document.getElementById('pieChartData');
        if (!canvas) return;
        // Opzioni compatibili v2/v3+
        const major = parseInt((Chart.version || '2').split('.')[0], 10);
        const optionsBase = {
            responsive: true,
            maintainAspectRatio: false,
            rotation: -0.2,
            layout: { padding: { bottom: 0 } } // spazio extra per la legenda
        };
        let options = {};
        if (major >= 3) {
            options = Object.assign({}, optionsBase, {
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        align: 'center',
                        labels: {
                            padding: 14,
                            boxWidth: 12,
                            usePointStyle: true,
                            pointStyle: 'rectRounded'
                        }
                    },
                    tooltip: {
                        enabled: true,
                        callbacks: {
                            // Modificato per includere anche la label
                            label: (ctx) => `${ctx.label}: ${ctx.parsed}€`
                        }
                    }
                }
            });
        } else {
            // Chart.js v2
            options = Object.assign({}, optionsBase, {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        padding: 14,
                        boxWidth: 12,
                        usePointStyle: true
                    }
                },
                tooltips: {
                    enabled: true,
                    callbacks: {
                        // Modificato per includere anche la label
                        label: function (tooltipItem, dataObj) {
                            const label = dataObj.labels[tooltipItem.index];
                            const value = dataObj.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                            return `${label}: ${value}€`;
                        }
                    }
                }
            });
        }
        new Chart(canvas.getContext('2d'), {
            type: 'pie',
            data: {
                labels,
                datasets: [{
                    data,
                    backgroundColor: bg,
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options
        });
    })();

</script>