<!-- content @s -->
<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h1 class="page-title"><?= htmlspecialchars($h1) ?></h1>
            <div class="nk-block-des text-soft">
                <h2><?= htmlspecialchars($h2) ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="nk-block nk-block-lg">
    <div class="row g-gs my-3">
        <div class="col-md-12">
            <div class="card card-bordered card-preview">
                <div class="card-inner">
                    <div class="card-head">
                        <h6 class="card-title">
                            Seleziona il prodotto da assegnare:
                        </h6>
                    </div>

                    <select class="form-select js-select2" id="cercaProdotto">
                        <option value=" " selected disabled>Seleziona</option>
                        <option value="/discenti/gestione/<?= $id ?>/data_esame_singolo">
                            Esame Singolo
                        </option>
                        <option value="/discenti/gestione/<?= $id ?>/data_certificazioni">
                            Certificazione
                        </option>
                        <option disabled>––––––––––––––––––––</option>
                        <?php if (!empty($PacchettiEsami)): ?>
                            <optgroup label="Pacchetto Esami">
                                <?php foreach ($PacchettiEsami as $pE): ?>
                                    <option
                                        value="/discenti/gestione/<?= $id ?>/data_pacchettiEsami?pacchetto=<?= $pE['id'] ?>">
                                        <?= htmlspecialchars($pE['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php else: ?>
                            <option value="/discenti/gestione/<?= $id ?>/data_pacchettiEsami">Pacchetto Esami</option>
                        <?php endif; ?>
                        <?php if (!empty($percorsi)): ?>
                            <optgroup label="Percorso di Laurea">
                                <?php foreach ($percorsi as $percorso): ?>
                                    <option value="/discenti/gestione/<?= $id ?>/data_esami?percorso=<?= $percorso['id'] ?>">
                                        <?= htmlspecialchars($percorso['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php else: ?>
                            <option value="/discenti/gestione/<?= $id ?>/data_esami">Percorso di Laurea</option>
                        <?php endif; ?>

                        <?php if (!empty($master)): ?>
                            <optgroup label="Master">
                                <?php foreach ($master as $m): ?>
                                    <option value="/discenti/gestione/<?= $id ?>/data_master?master=<?= $m['id'] ?>">
                                        <?= htmlspecialchars($m['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php else: ?>
                            <option value="/discenti/gestione/<?= $id ?>/data_master">Master</option>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Form unico nella pagina -->
<form id="mainForm" action="<?= htmlspecialchars($serp) ?>" method="POST">
    <!-- Qui puoi inserire altri campi del form -->
</form>


<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Script per inizializzazione e redirect -->
<script>
    $(document).ready(function () {
        // Inizializzazione Select2
        $('#cercaProdotto').select2();

        // Listener su cambio selezione
        $('#cercaProdotto').on('change', function () {
            const selectedURL = $(this).val();
            if (selectedURL) {
                window.location.href = selectedURL;
            }
        });
    });
</script>