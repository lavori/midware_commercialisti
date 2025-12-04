<!-- content @s -->
<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h1 class="page-title"><?= $h1 ?></h1>
            <div class="nk-block-des text-soft">
                <h2><?= $h2 ?></h2>
            </div>
        </div>
    </div>
</div>
<div class="nk-block nk-block-lg">
    <div class="row g-gs my-3">
        <div class="col-md-12">
            <div class="card card-bordered card-preview">
                <?php if (isset($message) && $message == 'insertData') { ?>
                    <div class="example-alert">
                        <div class="alert alert-success alert-icon">
                            <em class="icon ni ni-check-circle"></em> <strong>Esame inserito</strong> con successo
                        </div>
                    </div>
                <?php } ?>
                <?php if (isset($message) && $message == 'error') { ?>
                    <div class="example-alert">
                        <div class="alert alert-danger alert-icon">
                            <em class="icon ni ni-cross-circle"></em> <strong>Errore!</strong> Esame non inserito.
                        </div>
                    </div>
                <?php } ?>
                <div class="card-inner">
                    <form method="POST" id="myForm">
                        <div class="row g-gs align-items-center">
                            <div class="card-head mb-3">
                            </div>
                            <?php if (!empty($esamiSingoli)) { ?>
                                <div id="accordion-2" class="accordion accordion-s3">
                                    <div class="accordion-item">
                                        <a href="#" class="accordion-head" data-bs-toggle="collapse"
                                            data-bs-target="#accordion-item-2-1">
                                            <h6 class="title">Esami Presenti Discente:</h6>
                                            <span class="accordion-icon"></span>
                                        </a>
                                        <div class="accordion-body collapse show" id="accordion-item-2-1"
                                            data-bs-parent="#accordion-2">
                                            <div class="accordion-inner">
                                                <?php foreach ($esamiSingoli as $esame): ?>
                                                    -
                                                    <b><?= htmlspecialchars(is_array($esame) ? ($esame['nome'] ?? ($esame[0]['nome'] ?? '')) : $esame, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></b><br>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <br><br><br>
                                </div>
                            <?php } else { ?>
                                <div><br></div>
                            <?php } ?>
                        </div>


                        <div class="row g-gs align-items-center mb-3">
                            <!-- INPUT TEXT + ID nascosto -->
                            <div class="col-md-12">
                                <div class="form-group mb-0">
                                    <div class="form-control-wrap">
                                        <?php
                                        // Estrai tutti gli ID degli esami già presenti decodificando il JSON
                                        $presentiIds = [];
                                        foreach ($discente_esameSingolo as $esame) {
                                            $esameData = json_decode($esame['esame_json'], true);
                                            if (!empty($esameData) && is_array($esameData)) {
                                                // Aggiungi tutti gli ID contenuti nell'array JSON
                                                $presentiIds = array_merge($presentiIds, $esameData);
                                            }
                                        }
                                        ?>
                                        <select class="form-select js-select2" name="idEsame" data-search="on" required>
                                            <option value="" disabled selected>Seleziona Esame...</option>
                                            <?php foreach ($esami as $s): ?>
                                                <?php
                                                // Controlla se l'ID dell'esame corrente è presente nella lista degli esami già associati
                                                $disabled = in_array($s['id'], $presentiIds) ? 'disabled' : '';
                                                ?>
                                                <option value="<?= htmlspecialchars($s['id'] . '||' . $s['nome']) ?>"
                                                    data-nome="<?= htmlspecialchars($s['nome']) ?>"
                                                    data-desc="<?= htmlspecialchars($s['codice']) ?>"
                                                    data-cfu="<?= htmlspecialchars($s['cfu']) ?>" <?= $disabled ?>>
                                                    <?= htmlspecialchars($s['nome']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="footer">
                            <input type="submit" class="btn btn-primary" value="Aggiungi">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<form id="mainForm" action="<?= $serp ?>" method="POST"></form>

<!-- Assicurati di aver incluso jQuery e Select2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
<!-- html2canvas per catturare il div -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<!-- jsPDF per generare il PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>


<!-- Assicurati di aver già incluso jQuery, Select2, html2canvas e jsPDF -->
<script>
    $(document).ready(function () {
        // 0) All’avvio: disabilito solo #prezzo, #prezzoTutoraggio rimane abilitatо
        $('#prezzo').prop({ disabled: true, required: false });
        $('#prezzoTutoraggio').prop({ disabled: false, required: false });
        // Assicuro che la riga sia sempre visibile
        $('#tutoraggio-row').show();

        // 1) Inizializza Select2
        const $select = $('select.js-select2').select2({
            placeholder: 'Seleziona Esame...',
            allowClear: true
        });

        // 2) Validazione al submit
        $('#myForm').on('submit', function (e) {
            if (!$select.val()) {
                e.preventDefault();
                alert('Per favore, seleziona un esame prima di inviare.');
            }
        });
    });
</script>