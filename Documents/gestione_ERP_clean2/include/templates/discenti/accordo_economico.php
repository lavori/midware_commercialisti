<?php
// === Raggruppa i prodotti per tabella ===
$gruppi = [];

foreach ($RisultatoProdotto as $p) {
    $value = explode('||', $p);
    $nomeprodotto = $value[0] ?? '';
    $tabella = $value[1] ?? 'Altro';
    $isTutoraggio = isset($value[2]) && $value[2] === 'tutoraggio';

    $gruppi[$tabella][] = [
        'value' => $p,
        'nome' => $nomeprodotto,
        'tutoraggio' => $isTutoraggio,
    ];
}
// === Etichette leggibili per ciascun gruppo ===
$etichette = [
    'an_percorsi' => 'Percorsi Formativi',
    'an_esami' => 'Esami',
    'an_pacchettiEsame' => 'Pacchetti Esame',
    'an_certificazioni' => 'Certificazioni',
    'an_master' => 'Master',
    'Altro' => 'Altro',
];

$isTutoraggioView = false;
$isEcampusView = false;
if (isset($view) && isset($datiDiscente['pagamento']['tipoPagamento'])) {
    $tipoPagamento = $datiDiscente['pagamento']['tipoPagamento'];
    $isTutoraggioView = ($tipoPagamento === 'tutoraggio');
    $isEcampusView = ($tipoPagamento === 'e-campus');
    $min = 0;
}

?>

<!-- content @s -->
<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h1 class="page-title"><?php echo $h1; ?></h1>
            <div class="nk-block-des text-soft">
                <h2><?php echo $h2; ?></h2>
            </div>
        </div>
    </div>
</div>
<div class="nk-block  nk-block-lg">
    <div class="row g-gs my-3">
        <div class="col-md-12">
            <div class="card card-bordered card-preview">
                <?php if (isset($message) && $message == 'UpdateOK') { ?>
                    <div>
                        <div class="alert alert-success alert-icon">
                            <em class="icon ni ni-check-circle"></em> <strong>Dato aggiornato</strong> con successo.
                        </div>
                    </div>
                <?php } ?>
                <div class="card-inner">
                    <div class="form-group">
                        <div class="card-head">
                            <h4 class="card-title">Discente:
                                <?php echo $datiDiscente['nome'] . ' ' . $datiDiscente['cognome']; ?>
                            </h4>
                        </div>
                        <div class="card-head">
                            <h6 class="card-title">N. Matricola:
                                <?php echo $datiDiscente['numMatricola']; ?>
                            </h6>
                        </div>
                        <form method="POST">
                            <div class="gy-3">
                                <div class="row g-3 align-center">
                                    <div class="col-lg-5">
                                        <div class="form-group">
                                            <label class="form-label" for="prodotto">Prodotto</label>
                                            <?php if (!isset($view)) { ?>
                                                <span class="form-note">Nei prodotti in cui non è specificato il
                                                    "tutoraggio"<br>l’incasso sarà interamente destinato all’ente.</span>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="col-lg-7">
                                        <div class="form-group">
                                            <div class="form-control-wrap">
                                                <select class="form-control js-select2" name="prodotto[]" id="prodotto"
                                                    multiple required>
                                                    <option value="" disabled>Seleziona uno o più prodotti</option>
                                                    <?php foreach ($gruppi as $tabella => $prodotti): ?>
                                                        <optgroup
                                                            label="<?= htmlspecialchars($etichette[$tabella] ?? ucfirst($tabella)) ?>">
                                                            <?php foreach ($prodotti as $prodotto): ?>
                                                                <?php
                                                                $selected = in_array($prodotto['nome'], $datiDiscente['prodotto_presente'] ?? []) ? 'selected' : '';
                                                                ?>
                                                                <option value="<?= htmlspecialchars($prodotto['value']) ?>"
                                                                    <?= $selected ?>>
                                                                    <?= htmlspecialchars($prodotto['nome']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </optgroup>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--Specifica il pagamento per cosa viene fatto-->
                                <div id="campiEcampus"
                                    style="<?php echo (isset($view)) ? 'display: block;' : 'display: none;'; ?>">
                                    <!-- Diritti Segreteria -->
                                    <div class="row g-3 align-center">
                                        <div class="col-lg-5">
                                            <div class="form-group">
                                                <label class="form-label" for="diritti_segreteria">Diritti
                                                    Segreteria</label>
                                                <?php if (!isset($view)) { ?>
                                                    <span class="form-note">Inserisci l'importo dei diritti della
                                                        segreteria.</span>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="col-lg-7">
                                            <div class="form-group">
                                                <div class="form-control-wrap">
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">€</span>
                                                        </div>
                                                        <input type="number" class="form-control"
                                                            name="diritti_segreteria" id="diritti_segreteria"
                                                            step="0.01"
                                                            value="<?= $datiDiscente['pagamento']['diritti_segreteria'] ?? "" ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                    <!-- Tassa Regionale -->
                                    <div class="row g-3 align-center">
                                        <div class="col-lg-5">
                                            <div class="form-group">
                                                <label class="form-label" for="tassa_regionale">Tassa
                                                    Regionale</label>
                                                <?php if (!isset($view)) { ?>
                                                    <span class="form-note">Inserisci l'importo della tassa
                                                        regionale.</span>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="col-lg-7">
                                            <div class="form-group">
                                                <div class="form-control-wrap">
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">€</span>
                                                        </div>
                                                        <input type="number" class="form-control" name="tassa_regionale"
                                                            id="tassa_regionale" step="0.01"
                                                            value="<?= $datiDiscente['pagamento']['tassa_regionale'] ?? "" ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                    <!-- Marca da bollo -->
                                    <div class="row g-3 align-center">
                                        <div class="col-lg-5">
                                            <div class="form-group">
                                                <label class="form-label" for="marca_bollo">Marca da bollo</label>
                                                <?php if (!isset($view)) { ?>
                                                    <span class="form-note">Inserisci l'importo della marca da
                                                        bollo.</span>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="col-lg-7">
                                            <div class="form-group">
                                                <div class="form-control-wrap">
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">€</span>
                                                        </div>
                                                        <input type="number" class="form-control" name="marca_bollo"
                                                            id="marca_bollo" step="0.01"
                                                            value="<?= $datiDiscente['pagamento']['marca_bollo'] ?? "" ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Campi comuni che appaiono dopo la selezione -->
                                <div id="campiComuni"
                                    style="<?php echo isset($view) ? 'display: block;' : 'display: none;'; ?>">
                                    <div class="row g-3 align-center">
                                        <div class="col-lg-5">
                                            <div class="form-group">
                                                <label class="form-label" for="importoTotale"
                                                    id="labelImportoTotale"><?php $isTutoraggioView ? 'Importo Totale Tutoraggio' : 'Importo Totale prodotto'; ?></label>
                                                <?php if (!isset($view)) { ?>
                                                    <span class="form-note">Inserisci l'importo totale del prodotto del
                                                        discente.</span>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="col-lg-7">
                                            <div class="form-group">
                                                <div class="form-control-wrap">
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1">€</span>
                                                        </div>
                                                        <input type="number" class="form-control" name="importoTotale"
                                                            id="importoTotale" step="0.01" <?php if (isset($view)): ?>
                                                                value="<?php echo $datiDiscente['pagamento']['importoTotale']; ?>"
                                                            <?php endif; ?> required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="row g-3 align-center">
                                        <div class="col-lg-5">
                                            <div class="form-group">
                                                <label class="form-label" for="acconto">Acconto Prodotto</label>
                                                <?php if (!isset($view)) { ?>
                                                    <span class="form-note">Inserisci l'acconto del prodotto del
                                                        discente.</span>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="col-lg-7">
                                            <div class="form-group">
                                                <div class="form-control-wrap">
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1">€</span>
                                                        </div>
                                                        <input type="number" class="form-control" name="acconto"
                                                            id="acconto" step="0.01" <?php if (isset($view)): ?>
                                                                value="<?php echo $datiDiscente['pagamento']['acconto']; ?>"
                                                            <?php endif; ?> required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="row g-3 align-center">
                                        <div class="col-lg-5">
                                            <div class="form-group">
                                                <label class="form-label">Numero Rate</label>
                                                <?php if (!isset($view)) { ?>
                                                    <span class="form-note">Inserisci il numero totale delle rate.</span>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="col-lg-7">
                                            <div class="form-group">
                                                <div class="form-control-wrap number-spinner-wrap">
                                                    <button type="button"
                                                        class="btn btn-icon btn-primary number-spinner-btn number-minus"
                                                        data-number="minus"><em class="icon ni ni-minus"></em></button>
                                                    <input type="number" id="rate" class="form-control number-spinner"
                                                        min="1" value="<?=  isset ($datiDiscente['rata']) ? count($datiDiscente['rata']) : "1"?>" max="10">
                                                    <button type="button"
                                                        class="btn btn-icon btn-primary number-spinner-btn number-plus"
                                                        data-number="plus"><em class="icon ni ni-plus"></em></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                    <?php if (isset($view)) { ?>
                                        <div class="col-lg-5">
                                            <div class="form-group">
                                                <label class="form-label" for="rate">Rate</label>
                                            </div>
                                        </div>
                                        <div id="corsoDettagli">
                                        </div>
                                    <?php } else { ?>
                                        <div class="col-sm-12" id="corsoInfo">
                                            <div id="corsoDettagli">
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div> <!-- Chiusura campiComuni -->
                                <div class="row g-3">
                                    <div class="col-lg-7 offset-lg-5">
                                        <div class="form-group mt-2">
                                            <?php if (isset($datiDiscente['prodotto_scelto'])) { ?>
                                                <input type="hidden" name="prodottoScelto"
                                                    value="<?php echo $datiDiscente['prodotto_scelto']; ?>">
                                            <?php } ?>
                                            <input type="hidden" value="<?= isset($idAccordo) ?? $idAccordo ?>"
                                                name="idAccordo">

                                            <button type="submit"
                                                class="btn btn-primary"><?= isset($view) ? "Modifica" : "Invia" ?></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
    $(function () {
        // ===============================
        // ELEMENTI DOM
        // ===============================
        const idAcconto = <?= !empty($datiDiscente['pagamento']['id']) ? $datiDiscente['pagamento']['id'] : 'null'; ?>;
        const isView = <?= isset($view) && $view ? 'true' : 'false'; ?>;

        const $form = $('form').first(),
            $rateSpinner = $('#rate'),
            $corsoDettagli = $('#corsoDettagli'),
            $importoTotale = $('#importoTotale'),
            $acconto = $('#acconto'),
            $prodotto = $('#prodotto'),
            $campiComuni = $('#campiComuni'),
            $campiEcampus = $('#campiEcampus'),
            $labelImportoTotale = $('#labelImportoTotale');

        // ===============================
        // FUNZIONI DI SUPPORTO
        // ===============================
        const toggleCampiEcampus = (show, label, isTutoraggioOnly = false) => {
            const $inputs = $campiEcampus.find('input');
            $campiEcampus.toggle(show);
            $inputs.prop('required', show);
            if (isTutoraggioOnly) $inputs.val('');
            $labelImportoTotale.text(label);
        };

        const getProdottoInfo = val => {
            const [nome = '', tabella = '', tipo = ''] = val.split('||').map(s => s.toLowerCase());
            return { nome, tabella, tipo };
        };

        const gestisciVisibilitaForm = () => {
            const selected = $prodotto.val() || [];
            const numProdotti = selected.length;
            $campiComuni.hide();
            toggleCampiEcampus(false, 'Importo Totale Prodotto');
            if (!numProdotti) return;

            $campiComuni.show();
            if (numProdotti > 1) {
                toggleCampiEcampus(true, 'Importo Totale Prodotti + Tutoraggio');
                return;
            }

            const { tabella, tipo } = getProdottoInfo(selected[0]);
            if (tipo === 'tutoraggio') {
                toggleCampiEcampus(false, 'Importo Totale Tutoraggio', true);
            } else if (['an_percorsi', 'an_master'].includes(tabella)) {
                toggleCampiEcampus(true, 'Importo Totale Prodotto (con eCampus)');
            } else {
                toggleCampiEcampus(true, 'Importo Totale Prodotto');
            }
        };

        $prodotto.on('change', gestisciVisibilitaForm);
        gestisciVisibilitaForm();

        // ===============================
        // VALIDAZIONE IMPORTI
        // ===============================
        let isValid = false;

        const validateAmounts = () => {
            // Se siamo in view e l'utente non ha ancora toccato nulla, non colorare nulla
            if (isView && !hasUserChanged) {
                console.log("🟡 Modalità VIEW: salto validazione iniziale");
                return;
            }

            const importoTotale = parseFloat($importoTotale.val()) || 0;
            const acconto = parseFloat($acconto.val()) || 0;
            const $rate = $('input[name^="importoRata"]');
            const $inputs = $importoTotale.add($acconto).add($rate);

            $inputs.removeClass('is-valid is-invalid border-success border-danger');

            if (!importoTotale || !$rate.length) return;

            const sommaRate = $rate.toArray().reduce((sum, el) => sum + (parseFloat($(el).val()) || 0), 0);
            const totaleVersato = acconto + sommaRate;
            const differenza = Math.abs(totaleVersato - importoTotale);

            if (differenza < 0.01) {
                $inputs.addClass('is-valid border-success');
                isValid = true;
            } else {
                $inputs.addClass('is-invalid border-danger');
                isValid = false;
            }
        };

        //Listener che abilita la validazione solo dopo una modifica
        $importoTotale.add($acconto).on('input', function () {
            hasUserChanged = true;
            validateAmounts();
        });

        // Live su acconto/importoTotale
        $importoTotale.add($acconto).on('input', validateAmounts);


        // ===============================
        // BLOCCO RATE (AJAX)
        // ===============================
        const viewRateFields = () => {
            const nmax = parseInt($rateSpinner.val(), 10);
            const min = parseInt($rateSpinner.attr('min')) || 1;
            const max = parseInt($rateSpinner.attr('max')) || 10;

            if (!nmax || isNaN(nmax) || nmax < min || nmax > max) {
                $corsoDettagli.empty();
                return;
            }

            console.log("Invio dati AJAX:", { nmax, idAcconto });

            $.post('./include/componenti/discenti/cicloStampaRate.php', { nmax, idAcconto })
                .done(response => {
                    $corsoDettagli.html(response);
                    $importoTotale.add($acconto).on('input', validateAmounts);
                    $corsoDettagli.on('input', 'input[name^="importoRata"]', function () {
                        console.log(`📩 Rata modificata in $corsoDettagli → ricalcolo`);
                        validateAmounts();
                    });
                    validateAmounts();
                })
                .fail(err => {
                    $corsoDettagli.html(`Errore nel caricamento delle rate.<br>Status: ${err.status}`);
                    console.error('Errore AJAX:', err.status, err.statusText);
                });
        };

        $rateSpinner.on('change', viewRateFields);
        $('.number-spinner-btn').on('click', () => setTimeout(viewRateFields, 50));

        if (parseInt($rateSpinner.val(), 10) > 0) viewRateFields();

        // ===============================
        // VALIDAZIONE FINALE SU SUBMIT
        // ===============================
        $form.on('submit', e => {
            validateAmounts();
            $campiEcampus.find('input:disabled').prop('disabled', false);
            if (!isValid) {
                e.preventDefault();
                alert("L'importo totale non corrisponde alla somma di acconto e rate.");
            }
        });
    });
</script>