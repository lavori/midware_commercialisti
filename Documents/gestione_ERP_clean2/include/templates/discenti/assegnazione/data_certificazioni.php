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
                            <?php if (!empty($certificazioni)): ?>
                                <?php foreach ($certificazioni as $c): ?>
                                    <div class="row g-3 align-center mb-3 border-bottom pb-3">
                                        <!-- Titolo -->
                                        <div class="col-lg-12">
                                            <h5 class="title"><?php echo htmlspecialchars($c['riferimentoNomeCert']); ?></h5>
                                        </div>

                                        <!-- Etichetta -->
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label class="form-label"
                                                    for="dataSvolgimento_<?php echo $c['id']; ?>">Data</label>
                                                <span class="form-note">Data</span>
                                            </div>
                                        </div>
                                        <!-- Data -->
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <div class="form-control-wrap">
                                                    <div class="input-group">
                                                        <?php if (empty($c['dataSvolgimento']) && empty($c['voto'])) { ?>
                                                            <a onClick="apriModal('Fissa Data Scritto','Gestione Certificazione','scadenzaCertificazioni/form_data','<?php echo $c['id'] ?>')"
                                                                class="btn btn-primary">Fissa</a>
                                                        <?php } else { ?>
                                                            <label class="form-label mb-0 ms-3">
                                                                <?php 
                                                                    echo substr($c['dataSvolgimento'], 0, -3);
                                                                ?>
                                                            </label>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Bottoni azione -->
                                        <?php if (isset($c['dataSvolgimento']) && !empty($c['dataSvolgimento'])) { ?>
                                            <?php if (!isset($c['voto']) || empty($c['voto'])) { ?>
                                                <div class="col-lg-4 d-flex align-items-center">
                                                    <form method="POST" id="formBocciato" onsubmit="return confermaInvio();">
                                                        <input type="submit" class="btn btn-primary me-2" value="Bocciato"
                                                            name="bocciatoCert" id="bocciatoCert">

                                                        <input type="hidden" name="idCertificazione" id="idCertificazione"
                                                            value="<?php echo $c['id'] ?>">
                                                    </form>
                                                    <a onClick="apriModal('Inserisci Voto','Gestione Certificazione','scadenzaCertificazioni/insertVoto','<?php echo $c['id'] ?>')"
                                                        class="btn btn-primary me-2">Superato</a>
                                                </div>
                                            <?php } ?>
                                        <?php } ?>
                                        <!-- Voto -->
                                        <div class="col-lg-3 d-flex align-items-center" style="gap:1rem;">
                                            <label class="form-label mb-0">
                                                Voto: <?php echo !empty($c['voto']) ? $c['voto'] : 'Non Inserito'; ?>
                                            </label>
                                            <?php if (!empty($c['esito'])): ?>
                                                <label class="form-label mb-0">
                                                    Esito: <?php echo $c['esito']; ?>
                                                </label>
                                            <?php endif; ?>
                                        </div>

                                    </div>
                                    <div><br></div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>
                                    Nessun esame assegnato a questo discente.<br>
                                    <strong>Vai a <a href="/discenti/gestione/<?= $id ?>/certificazione">Assegna
                                            Certificazione</a></strong>
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
    function confermaInvio() {
        return confirm('Sei sicuro di voler marcare come “Bocciato”?');
    }
</script>