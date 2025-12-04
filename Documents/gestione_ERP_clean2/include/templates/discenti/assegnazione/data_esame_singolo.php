<!-- content @s -->
<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h1 class="page-title"><?php echo $h1; ?></h1>
            <div class="nk-block-des text-soft">
            </div>
        </div>
    </div>
</div>
<div class="nk-block  nk-block-lg">
    <div class="row g-gs my-3">
        <div class="col-md-12">
            <div class="card card-bordered card-preview">
                <?php if (isset($message) && $message == 'insertData') { ?>
                    <div class="example-alert">
                        <div class="alert alert-success alert-icon">
                            <em class="icon ni ni-check-circle"></em> <strong>Data esame salvata</strong> con successo
                        </div>
                    </div>
                <?php } ?>
                <?php if (isset($message) && $message == 'updateData') { ?>
                    <div class="example-alert">
                        <div class="alert alert-success alert-icon">
                            <em class="icon ni ni-check-circle"></em> <strong>Modifica avvenuta</strong> con successo
                        </div>
                    </div>
                <?php } ?>
                <?php if (isset($message) && $message == 'insertVoto') { ?>
                    <div class="example-alert">
                        <div class="alert alert-success alert-icon">
                            <em class="icon ni ni-check-circle"></em> <strong>Voto inserito</strong> con successo
                        </div>
                    </div>
                <?php } ?>
                <div class="card-inner">
                    <h5>
                        <p>Discente: <?php echo $discente[0]['nome'] . ' ' . $discente[0]['cognome']; ?></p>
                        <p>N. Matricola: <?php echo $discente[0]['numMatricola']; ?></p>
                    </h5>
                    <br>
                    <div class="border-bottom"></div>
                    <br>
                    <div class="form-group">
                        <div class="gy-3">
                            <?php if (!empty($esami)):
                                $TE = ''; ?>
                                <?php foreach ($esami as $exam): ?>
                                    <div class="row g-3 align-center mb-3 border-bottom pb-3">
                                        <!-- Titolo -->
                                        <div class="col-lg-12">
                                            <h5 class="title"><?php echo htmlspecialchars($exam['nome']); ?>
                                            </h5>
                                        </div>

                                        <!-- Etichetta -->
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label class="form-label" for="dataScritto_<?php echo $exam['id']; ?>">Data
                                                    Scritto</label>
                                                <span class="form-note">Data Esame Scritto</span>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="dataOrale_<?php echo $exam['id']; ?>">Data
                                                    Orale</label>
                                                <span class="form-note">Data Esame Orale</span>
                                            </div>
                                        </div>
                                        <!-- Data -->
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <div class="form-control-wrap">
                                                    <div class="input-group">
                                                        <?php if (empty($exam['data_scritto']) && empty($exam['voto'])) { ?>
                                                            <a onClick="apriModal('Fissa Data Scritto','Gestione Esame','scadenzaEsamiSingoli/form_data','<?php echo $exam['id'] . '|' . 'scritto'; ?>')"
                                                                class="btn btn-primary">Fissa</a>
                                                        <?php } else { ?>
                                                            <label class="form-label mb-0 ms-3">
                                                                <?php
                                                                echo substr($exam['data_scritto'], 0, -3);
                                                                $TE = 'S';
                                                                ?>
                                                            </label>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="form-control-wrap">
                                                    <div class="input-group">
                                                        <?php if (empty($exam['voto']) && isset($exam['data_scritto']) && !empty($exam['data_scritto'])) { ?>
                                                            <?php if (empty($exam['data_orale'])) { ?>
                                                                <a onClick="apriModal('Fissa Data Orale','Gestione Esame','scadenzaEsamiSingoli/form_data','<?php echo $exam['id'] . '|orale|' . $exam['data_scritto']; ?>')"
                                                                    class="btn btn-primary">Fissa</a>
                                                            <?php } else { ?>
                                                                <label class="form-label mb-0 ms-3">
                                                                    <?php
                                                                        echo substr($exam['data_orale'], 0, -3);
                                                                        $TE = 'O';
                                                                    ?>
                                                                </label>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Bttoni azione -->
                                        <?php if (isset($exam['data_scritto']) && !empty($exam['data_scritto'])) { ?>
                                            <?php if (!isset($exam['voto']) || empty($exam['voto'])) { ?>
                                                <div class="col-lg-4 d-flex align-items-center">
                                                    <a onClick="apriModal('Modifica','Gestione Esame','scadenzaEsamiSingoli/modificaEsame','<?php echo $exam['id'] . '|' . $exam['data_scritto'] . '|' . $exam['data_orale'] . '|' . $TE; ?>')"
                                                        class="btn btn-primary me-2">Modifica</a>
                                                    <a onClick="apriModal('Inserisci Voto','Gestione Esame','scadenzaEsamiSingoli/insertVoto','<?php echo $exam['id'] ?>')"
                                                        class="btn btn-primary me-2">Superato</a>
                                                </div>
                                            <?php } ?>
                                        <?php } ?>
                                        <!-- Voto -->
                                        <div class="col-lg-3 d-flex align-items-center">
                                            <label class="form-label mb-0 ms-3">
                                                Voto: <?php echo !empty($exam['voto']) ? $exam['voto'] : 'Non Inserito'; ?>
                                            </label>
                                            <?php if (!empty($exam['esito'])) { ?>
                                                <label class="form-label mb-0 ms-3">
                                                    Esito:
                                                    <?php echo $exam['esito']; ?>
                                                </label>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div><br></div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>
                                    Nessun esame assegnato a questo discente.<br>
                                    <strong>Vai a <a href="/discenti/gestione/<?= $id ?>/esame_singolo">Assegna Esame
                                            Singolo</a></strong>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Form unico nella pagina -->
<form id="mainForm" action="<?php echo $serp; ?>" method="POST">
    <!-- Altri campi visibili opzionali -->
</form>

<script>
    // Ascolta il caricamento della modale
    document.addEventListener('click', function () {
        // Delay per assicurarti che il DOM sia stato aggiornato
        setTimeout(function () {
            const bocciatoRadio = document.getElementById('bocciato');
            const rinviatoRadio = document.getElementById('rinviato');
            const motivazioneExtraGroup = document.getElementById('motivazione-extra-group');

            if (bocciatoRadio && rinviatoRadio && motivazioneExtraGroup) {
                function toggleMotivazioneExtra() {
                    if (bocciatoRadio.checked) {
                        motivazioneExtraGroup.style.display = 'block';
                    } else {
                        motivazioneExtraGroup.style.display = 'none';
                    }
                }

                bocciatoRadio.addEventListener('change', toggleMotivazioneExtra);
                rinviatoRadio.addEventListener('change', toggleMotivazioneExtra);

                // Esegui al caricamento per impostare lo stato iniziale
                toggleMotivazioneExtra();
            }
        }, 300); // Attendi il caricamento DOM della modale
    });
</script>