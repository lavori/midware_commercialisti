<?php
$action = "new";
if (isset($_GET['id'])) {
    $action = "update";
}
?>

<style>
    .vertical-divider {
        border-left: 1px solid #dbdfea;
        padding-left: 20px;
    }

    /* Rimuovi il bordo sui dispositivi mobili */
    @media (max-width: 767px) {
        .vertical-divider {
            border-left: none;
            padding-left: 0;
        }
    }
</style>
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
                <!--Stampa messaggi di errore e di corretto inserimento-->
                <?php if (isset($message)): ?>
                    <?php switch ($message):
                        case 'insert': ?>
                            <div class="example-alert">
                                <div class="alert alert-success alert-icon">
                                    <em class="icon ni ni-check-circle"></em>
                                    <strong>Dato inserito</strong> con successo
                                </div>
                            </div>
                            <?php break;
                        case 'update': ?>
                            <div class="example-alert">
                                <div class="alert alert-success alert-icon">
                                    <em class="icon ni ni-check-circle"></em>
                                    <strong>Dato aggiornato</strong> con successo
                                </div>
                            </div>
                            <?php break;
                        case 'delete': ?>
                            <div class="example-alert">
                                <div class="alert alert-success alert-icon">
                                    <em class="icon ni ni-check-circle"></em>
                                    <strong>Dato eliminato</strong> con successo
                                </div>
                            </div>
                            <?php break;
                    endswitch; ?>
                <?php endif; ?>
                <br>

                <div class="card-inner">
                    <form method="POST">
                        <div class="row g-gs">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label" for="nomePacchetto">
                                        <h5>Nome Pacchetto</h5>
                                    </label>
                                    <div class="form-control-wrap">
                                        <input type="text" class="form-control" name="nomePacchetto" id="nomePacchetto"
                                            placeholder="Nome" <?php if (isset($update)) {
                                                echo 'value="' . $pacchettoEsami['nomePacchetto'] . '"';
                                            } ?> required>
                                    </div>
                                </div>
                                <label class="form-label" for="spinnerEsami">
                                    <h6>Numero di Esami</h6>
                                </label>

                                <div class="form-control-wrap number-spinner-wrap">
                                    <button type="button"
                                        class="btn btn-icon btn-primary number-spinner-btn number-minus"
                                        data-number="minus"><em class="icon ni ni-minus"></em></button>
                                    <input type="number" id="esameSpinner" class="form-control number-spinner" min="1"
                                        value="<?php if (isset($update)) {
                                            echo count($pacchettoEsami['id_esami']);
                                        } else {
                                            echo '1';
                                        } ?>" max="10">
                                    <button type="button"
                                        class="btn btn-icon btn-primary number-spinner-btn number-plus"
                                        data-number="plus"><em class="icon ni ni-plus"></em></button>
                                </div>
                                <div class="center" style="margin-top: 10%;">
                                    <input type="submit" class="btn btn-primary" value="Salva Pacchetto">
                                </div>
                            </div><!-- col -->
                            <div class="col-12 col-md vertical-divider">
                                <div class="form-group">
                                    <label class="form-label" for="spinnerEsami">
                                        <h6>Esami</h6>
                                    </label>
                                    <div id="esamiDiv">

                                    </div>
                                </div>
                            </div><!-- col -->
                        </div><!-- .row -->
                        <input type="hidden" name="action" value="<?= $action; ?>">
                    </form>
                </div>
            </div><!-- .card-preview -->
        </div>
    </div>
</div>

<!-- Form unico nella pagina -->
<form id="mainForm" action="<?php echo $serp; ?>" method="POST">
    <!-- Altri campi visibili opzionali -->
</form>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
    $(document).ready(function () {

        //VARIABILI GLOBALI
        const LS_KEY = 'selectedExamsCache';
        const esamiDiv = $('#esamiDiv');

        const getSpinnerInput = () => {
            const $el = $('#esameSpinner');
            return $el.is('input') ? $el : $el.find('input[type="number"], input').first();
        };
        let esameSpinner = getSpinnerInput();

        // DATI PHP
        const esamiPrecaricati = <?php
        echo isset($pacchettoEsami['id_esami'])
            ? json_encode($pacchettoEsami['id_esami'])
            : '[]';
        ?>;
        const hasIdGet = <?= isset($_GET['id']) && !empty($_GET['id']) ? 'true' : 'false'; ?>;

        // STATO GLOBALE
        window.selectedExams = [...esamiPrecaricati];

        // Se la pagina è stata ricaricata manualmente → cancella cache
        if (performance.navigation.type === performance.navigation.TYPE_RELOAD) {
            localStorage.removeItem(LS_KEY);
            console.log('🔄 Reload rilevato → reset cache, uso dati originali.');
        }

        // PREVENZIONE RELOAD / DEFAULT
        $('.number-spinner-btn').attr('type', 'button');
        $(document).on('click', 'a[href="#"]', e => {
            e.preventDefault();
            e.stopPropagation();
        });
        $(document).on('keydown', '#esameSpinner input, #esameSpinner', e => {
            if (e.key === 'Enter') e.preventDefault();
        });

        // CACHE LOCALSTORAGE (solo sessione)
        const loadCache = () => {
            try {
                const cache = JSON.parse(localStorage.getItem(LS_KEY) || '[]');
                if (Array.isArray(cache) && cache.length) {
                    window.selectedExams = cache;
                    console.log('🗂️ Ripristino da cache sessione:', cache);
                }
            } catch (err) {
                console.warn('⚠️ Errore cache:', err);
            }
        };

        const persistCache = () => {
            try {
                localStorage.setItem(LS_KEY, JSON.stringify(window.selectedExams || []));
                console.log('💾 Cache aggiornata:', window.selectedExams);
            } catch (err) {
                console.warn('⚠️ localStorage non disponibile:', err);
            }
        };

        const readCurrentSelections = () => esamiDiv
            .find('select[name^="esamiSelezionati"]')
            .map(function () { return this.value || ''; })
            .get();

        // FUNZIONI DI SUPPORTO
        function controllaSelectVuote() {
            const hasEmpty = $('select[name^="esamiSelezionati"]').toArray().some(s => !s.value);
            $('.number-spinner-btn.plus').prop('disabled', hasEmpty);
        }

        //GESTIONE SPINNER
        $(document).on('click', '.number-spinner-btn', function (e) {
            e.preventDefault();
            e.stopPropagation();

            esameSpinner = getSpinnerInput();
            const $btn = $(this);
            const $inp = esameSpinner;

            const step = Number($inp.attr('step') || 1);
            const min = $inp.attr('min') ? Number($inp.attr('min')) : 0;
            const max = $inp.attr('max') ? Number($inp.attr('max')) : Infinity;
            let val = parseInt($inp.val(), 10);
            if (isNaN(val)) val = min;

            if ($btn.hasClass('plus')) val = Math.min(val + step, max);
            if ($btn.hasClass('minus')) val = Math.max(val - step, min);

            $inp.val(val).trigger('change');
        });

        // FUNZIONE PRINCIPALE
        function visualizzaEsami() {
            const nmax = parseInt(getSpinnerInput().val(), 10) || 0;
            if (!nmax) return;

            const current = readCurrentSelections();
            if (current.length > 0) window.selectedExams = current;

            if (window.selectedExams.length > nmax) {
                window.selectedExams = window.selectedExams.slice(0, nmax);
            }

            console.log('📤 Invio a cicloStampaEsami.php:', window.selectedExams);

            $.ajax({
                url: './include/componenti/pacchettiEsame/cicloStampaEsami.php',
                type: 'POST',
                data: { nmax, esamiPresenti: window.selectedExams },
                success: function (response) {
                    esamiDiv.html(response);

                    // Inizializza Select2
                    $('.js-select2').select2({
                        ajax: {
                            url: './include/componenti/pacchettiEsame/queryStampaEsami.php',
                            dataType: 'json',
                            delay: 250,
                            data: params => ({
                                q: params.term || '',
                                ids: window.selectedExams
                            }),
                            processResults: data => ({ results: data.items }),
                            cache: true
                        },
                        minimumInputLength: 2,
                        width: '100%'
                    });

                    // Ripristina selezioni precedenti
                    if (window.selectedExams.length > 0) {
                        $.ajax({
                            url: './include/componenti/pacchettiEsame/queryStampaEsami.php',
                            data: { ids: window.selectedExams },
                            dataType: 'json',
                            success: data => {
                                data.items.forEach((item, i) => {
                                    const select = $('select.js-select2').eq(i);
                                    if (!select.length) return;
                                    const option = new Option(item.text, item.id, true, true);
                                    select.append(option).trigger('change');
                                });
                            }
                        });
                    }

                    controllaSelectVuote();
                    persistCache();
                    console.log('✅ Esami aggiornati, stato:', window.selectedExams);
                },
                error: xhr => {
                    esamiDiv.html(`Errore nel caricamento dei dati. Status ${xhr.status}`);
                }
            });
        }

        // EVENTI GLOBALI
        $(document).on('change', 'select[name^="esamiSelezionati"]', () => {
            window.selectedExams = readCurrentSelections();
            persistCache();
            controllaSelectVuote();
        });

        $(document).on('change', '#esameSpinner input, #esameSpinner', () => {
            window.selectedExams = readCurrentSelections();
            persistCache();
            visualizzaEsami();
        });

        $('form').on('submit', function (e) {
            let valid = true;
            $('select[name^="esamiSelezionati"]').each(function () {
                const $sel = $(this);
                if (!$sel.val()) {
                    valid = false;
                    $sel.addClass('is-invalid');
                    $sel.next('.select2').find('.select2-selection').css('border-color', '#dc3545');
                } else {
                    $sel.removeClass('is-invalid');
                    $sel.next('.select2').find('.select2-selection').css('border-color', '');
                }
            });

            if (!valid) {
                e.preventDefault();
                alert('Attenzione: alcuni campi esame non sono compilati!');
                return false;
            }

            localStorage.removeItem(LS_KEY);
        });

        // INIZIALIZZAZIONE
        const inizializzaEsami = () => {
            const nmaxInput = getSpinnerInput();

            //Caso 1: senza ID → spinner = 1 e mostra una select vuota
            if (!hasIdGet) {
                nmaxInput.val(1);
                //console.log('🆕 Nessun ID in GET → spinner impostato a 1, mostro una select vuota.');
                visualizzaEsami();
                return;
            }

            // Caso 2: con ID → attende che lo spinner sia valorizzato dal backend
            const nmax = parseInt(nmaxInput.val(), 10);
            if (window.selectedExams.length > 0 && nmax > 0) {
                //console.log('🚀 Caricamento iniziale esami da DB...');
                visualizzaEsami();
            } else {
                //console.log('⏳ Attendo che lo spinner sia pronto...');
                const observer = new MutationObserver(() => {
                    const val = parseInt(nmaxInput.val(), 10);
                    if (!isNaN(val) && val > 0) {
                        observer.disconnect();
                        console.log('✅ Spinner pronto, carico esami...');
                        visualizzaEsami();
                    }
                });
                observer.observe(nmaxInput[0], { attributes: true, attributeFilter: ['value'] });
            }
        };

        inizializzaEsami();
    });
</script>