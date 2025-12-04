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
                            <em class="icon ni ni-check-circle"></em> <strong>Certificazione inserita</strong> con successo
                        </div>
                    </div>
                <?php } ?>
                <?php if (isset($message) && $message == 'error') { ?>
                    <div class="example-alert">
                        <div class="alert alert-danger alert-icon">
                            <em class="icon ni ni-cross-circle"></em> <strong>Errore!</strong> Certificazione non inserita.
                        </div>
                    </div>
                <?php } ?>
                <div class="card-inner">
                    <form method="POST" id="myForm">
                        <div class="row g-gs align-items-center">
                            <div class="card-head mb-3">
                            </div>
                            <?php if (!empty($CertPresenti)) { ?>
                                <div id="accordion-2" class="accordion accordion-s3">
                                    <div class="accordion-item">
                                        <a href="#" class="accordion-head" data-bs-toggle="collapse"
                                            data-bs-target="#accordion-item-2-1">
                                            <h6 class="title">Certificazioni Presenti Discente:</h6>
                                            <span class="accordion-icon"></span>
                                        </a>
                                        <div class="accordion-body collapse show" id="accordion-item-2-1"
                                            data-bs-parent="#accordion-2">
                                            <div class="accordion-inner">
                                                <?php
                                                foreach ($CertPresenti as $cert):
                                                    echo '- <b>' . $cert['riferimentoNomeCert'] . '</b> (Tutoraggio: ' . $cert['tutoraggio'] . ')<br>';
                                                endforeach;
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <br><br><br>
                                </div>
                            <?php } else { ?>
                                <div>
                                    <br>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="row g-gs align-items-center mb-3">
                            <!-- CHECKBOX: seleziona e tutoraggio -->
                            <div class="col-md-1">
                                <div class="form-group mb-0">
                                    <ul class="custom-control-group g-3 align-center mb-0">

                                        <!-- Tutoraggio -->
                                        <li>
                                            <div
                                                class="custom-control custom-checkbox d-flex align-items-center flex-row-reverse">
                                                <input type="checkbox" class="custom-control-input" id="tutoraggio"
                                                    name="tutoraggio">
                                                <label class="custom-control-label me-2" for="tutoraggio">
                                                    Tutoraggio
                                                </label>
                                            </div>
                                        </li>

                                    </ul>
                                </div>
                            </div>
                            <!-- INPUT TEXT + ID nascosto -->
                            <div class="col-md-11">
                                <div class="form-group mb-0">
                                    <div class="form-control-wrap">
                                        <?php
                                        // Estrai tutti gli id_cert già presenti
                                        $presentiIds = array_column($CertPresenti, 'id_cert');
                                        ?>
                                        <select class="form-select js-select2" name="idCertificazione" data-search="on"
                                            required>
                                            <option value=" " disabled selected>Seleziona Certificazione...</option>
                                            <?php foreach ($certificazioni as $c):
                                                // se l’id di questa cert è in $presentiIds, disabilita l’opzione
                                                $disabled = in_array($c['id'], $presentiIds) ? 'disabled' : '';
                                                ?>
                                                <option value="<?php echo $c['id'] . '||' . $c['nome']; ?>"
                                                    data-nome="<?php echo $c['nome']; ?>"
                                                    data-desc="<?php echo $c['tipo']; ?>" data-qty="1" <?php echo $disabled; ?>>
                                                    <?php echo $c['nome']; ?>
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
        // 0) All’avvio: disabilito e rimuovo required sui campi nascosti
        $('#prezzo').prop({ disabled: true, required: false });
        $('#prezzoTutoraggio').prop({ disabled: true, required: false });

        // 1) Inizializza Select2
        const $select = $('select.js-select2').select2({
            allowClear: true
        });

        // 2) Validazione + submit
        $('#myForm').off('submit').on('submit', function (e) {
            const form = this;

            // 2.1) Se non hai selezionato, alert e mostro dropdown
            if (!$select.val()) {
                e.preventDefault();
                alert('Per favore, seleziona una certificazione prima di inviare.');
                return;
            }

        });
    });
</script>