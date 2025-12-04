<!-- content @s -->
<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h1 class="page-title"><?php echo $h1; ?></h1>
            <div class="nk-block-des text-soft">
                <h2><?php echo $h2; ?></h2>
            </div>
        </div>
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->
<div class="nk-block  nk-block-lg">
    <div class="row g-gs my-3">
        <div class="col-md-12">
            <div class="card card-bordered card-preview">
                <?php if (isset($message) && $message == 'insert') { ?>
                    <div class="alert alert-success alert-icon">
                        <em class="icon ni ni-check-circle"></em> <strong>Dato inserito</strong> con successo
                    </div>
                <?php } ?>
                <?php if (isset($message) && $message == 'insertProdotto') { ?>
                    <div class="alert alert-success alert-icon">
                        <em class="icon ni ni-check-circle"></em> <strong>Prodotto inserito</strong> con successo
                    </div>
                <?php } ?>
                <?php if (isset($message) && $message == 'DiscentePresente') { ?>
                    <div class="alert alert-warning alert-icon">
                        <em class="icon ni ni-check-circle"></em> <strong>Attenzione! </strong> Percorso non inserito
                        perchè il discente ha già un percorso attivo, per modificare vai a
                        <a style="color: inherit; text-decoration: none !important;"
                            href="discenti/gestione/<?= $id ?>/modifica_percorso">
                            <strong>Modifica Esami</strong>
                        </a>
                    </div>
                <?php } ?>
                <?php if (isset($message) && $message == 'CorsoNonPresente') { ?>
                    <div class="alert alert-danger alert-icon">
                        <em class="icon ni ni-check-circle"></em> <strong>Attenzione! </strong> Percorso non inserito
                        perchè il corso non ha esami.
                    </div>
                <?php } ?>
                <div class="card-inner">
                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Scegli Anno di Accesso del Discente</label>
                            <div class="form-control-wrap">
                                <select class="form-select" id="annoAccessoDiscente" name="annoAccessoDiscente"
                                    data-search="on" required>
                                    <option value="" disabled selected>Seleziona...</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group" id="percorso" style="display: none;">
                            <label class="form-label">Scegli Percorso di Laurea</label>
                            <div class="form-control-wrap">
                                <select class="form-select" id="corsoSelect" name="corsoSelect" data-search="on"
                                    required>
                                    <option value="" disabled selected>Seleziona...</option>
                                    <?php foreach ($percorsi as $c) { ?>
                                        <option value="<?php echo $c['id'] ?>"
                                            <?php if(in_array($c['id'],$pid)){echo 'disabled';} ?>
                                        ><?php echo $c['percorso'] ?>  <?php if(in_array($c['id'],$pid)){echo ' (Già presente)';}?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <!-- DIV nascosto inizialmente -->
                        <div id="corsoInfo" style="display: none;">
                            <div id="corsoDettagli">

                            </div>
                        </div>

                        <input type="hidden" name="action" value="insert">
                        <button type="submit" class="btn btn-primary center">Invio</button>
                    </form>
                </div>
            </div>
        </div><!-- .card-preview -->
    </div>
</div>
<!-- Form unico nella pagina -->
<form id="mainForm" action="<?php echo $serp; ?>" method="POST">
    <!-- Altri campi visibili opzionali -->
</form>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const selectAnno = document.getElementById("annoAccessoDiscente");
        const selectCorso = document.getElementById("corsoSelect");
        const divPercorso = document.getElementById("percorso");
        const divCorsoInfo = document.getElementById("corsoInfo");
        const divCorsoDettagli = document.getElementById("corsoDettagli");

        function mostraCorsiLaurea() {
            const annopercorso = selectAnno.value;
            if (annopercorso !== '') {
                divPercorso.style.display = 'block';
            } else {
                divPercorso.style.display = 'none';
                divCorsoInfo.style.display = 'none';
                divCorsoDettagli.innerHTML = '';
            }
        }

        function caricaEsami(corsoId, annoAccesso) {
            if (!corsoId || !annoAccesso) return;

            divCorsoInfo.style.display = 'block';
            divCorsoDettagli.innerHTML = 'Caricamento...';

            fetch('./include/componenti/discenti/stampacorsi.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + encodeURIComponent(corsoId) + '&anno=' + encodeURIComponent(annoAccesso)
            })
                .then(response => response.ok ? response.text() : Promise.reject(response.status))
                .then(html => divCorsoDettagli.innerHTML = html)
                .catch(error => divCorsoDettagli.innerHTML = 'Errore nel caricamento: ' + error);
        }

        // Evento sul cambio anno accesso
        selectAnno.addEventListener('change', function () {
            mostraCorsiLaurea();
            divCorsoInfo.style.display = 'none';
            divCorsoDettagli.innerHTML = '';
            selectCorso.selectedIndex = 0; // reset selezione corso
            if ($(selectCorso).hasClass("select2-hidden-accessible")) {
                $(selectCorso).val('').trigger('change'); // reset se Select2
            }
        });

        // Inizializzazione Select2 e collegamento evento select
        if (typeof $ !== 'undefined' && $.fn.select2) {
            $(selectCorso).select2().on('select2:select', function (e) {
                const corsoId = e.params.data.id;
                const anno = selectAnno.value;
                caricaEsami(corsoId, anno);
            });
        } else {
            // Fallback normale senza select2
            selectCorso.addEventListener('change', function () {
                const corsoId = selectCorso.value;
                const anno = selectAnno.value;
                caricaEsami(corsoId, anno);
            });
        }

        mostraCorsiLaurea(); // in caso di pre-selezione
    });
</script>