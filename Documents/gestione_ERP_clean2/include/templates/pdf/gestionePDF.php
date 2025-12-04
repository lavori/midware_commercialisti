<?php //echo'<pre>';print_r($esami);echo'</pre>';exit(); ?>
<!-- content @s -->
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="nk-block  nk-block-lg">
                    <h2><?php echo $h2; ?></h2><br>
                    <div class="card card-bordered card-preview">
                        <?php if (isset($message) && $message == 'FileInserito') { ?>
                            <div class="example-alert">
                                <div class="alert alert-success alert-icon">
                                    <em class="icon ni ni-check-circle"></em> <strong>File Creato</strong> e inviato con
                                    successo
                                </div>
                            </div>
                        <?php } ?>
                        <?php if (isset($message) && $message == "FileGiaInserito") { ?>
                            <div class="example-alert">
                                <div class="alert alert-warning alert-icon">
                                    <em class="icon ni ni-alert-circle"></em><strong>Il discente</strong>
                                    ha già inviato una domanda di iscrizione.
                                </div>
                            </div>
                        <?php } ?>
                        <?php if (isset($message) && $message == "errorOpenPDF") { ?>
                            <div class="example-alert">
                                <div class="alert alert-danger alert-icon">
                                    <em class="icon ni ni-cross-circle"></em> <strong>Errore </strong> di apertura del file .zip
                                </div>
                            </div>
                        <?php } ?>
                        <?php if (isset($message) && $message == "errorSavePDF") { ?>
                            <div class="example-alert">
                                <div class="alert alert-danger alert-icon">
                                    <em class="icon ni ni-cross-circle"></em> <strong>Errore</strong>di salvataggio del file pdf.
                                </div>
                            </div>
                        <?php } ?>
                        <?php if (isset($message) && $message == "PDFnoncompatibile") { ?>
                            <div class="example-alert">
                                <div class="alert alert-danger alert-icon">
                                    <em class="icon ni ni-cross-circle"></em> <strong>Errore</strong> di compatibilità del pdf all'interno dello zip.
                                </div>
                            </div>
                        <?php } ?>
                        <!--Inserisci i documenti mancanti la creazionde del PDF-->
                        <div class="card-inner">
                            <br>
                            <form enctype="multipart/form-data" method="POST">
                                <div class="preview-block">
                                    <div class="row gy-4">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label class="form-label">Seleziona Discente</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2" data-search="on"
                                                        name="id_discente" required>
                                                        <option value="" disabled selected>Seleziona...</option>
                                                        <?php foreach ($discente as $d): ?>
                                                            <option value="<?= htmlspecialchars($d['id']) ?>">
                                                                <?= htmlspecialchars($d['nome'] . ' ' . $d['cognome']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="form-label">Seleziona Domanda Di Ammissione</label>
                                                <div class="form-control-wrap">
                                                    <div class="form-file">
                                                        <input type="file" name="domanda_ammissione" accept=".pdf"
                                                            id="domanda_ammissione" class="form-file-input" required>
                                                        <label class="form-file-label" for="domanda_ammissione">Scegli
                                                            File</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="form-label">Seleziona Autocertificazione</label>
                                                <div class="form-control-wrap">
                                                    <div class="form-file">
                                                        <input type="file" name="autocertificazione" accept=".pdf"
                                                            id="autocertificazione" class="form-file-input" required>
                                                        <label class="form-file-label" for="autocertificazione">Scegli
                                                            File</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="form-label">Seleziona Autenticazione Fotografica</label>
                                                <div class="form-control-wrap">
                                                    <div class="form-file">
                                                        <input type="file" name="autenticazioneFoto" accept=".pdf"
                                                            id="autenticazioneFoto" class="form-file-input">
                                                        <label class="form-file-label" for="autenticazioneFoto">Scegli
                                                            File</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="form-label">Seleziona Contratto dello Studente</label>
                                                <div class="form-control-wrap">
                                                    <div class="form-file">
                                                        <input type="file" name="contrattoStudente" accept=".pdf"
                                                            id="contrattoStudente" class="form-file-input" required>
                                                        <label class="form-file-label" for="contrattoStudente">Scegli
                                                            File</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="form-label">Seleziona Documento D'Identità</label>
                                                <div class="form-control-wrap">
                                                    <div class="form-file">
                                                        <input type="file" name="docIdentita" accept=".pdf"
                                                            id="docIdentita" class="form-file-input" required>
                                                        <label class="form-file-label" for="docIdentita">Scegli
                                                            File</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="form-label">Seleziona Regolamento Economico</label>
                                                <div class="form-control-wrap">
                                                    <div class="form-file">
                                                        <input type="file" name="regolamentoEco" accept=".pdf"
                                                            id="regolamentoEco" class="form-file-input" required>
                                                        <label class="form-file-label" for="regolamentoEco">Scegli
                                                            File</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="form-label">Piano Di Studi</label>
                                                <div class="form-control-wrap">
                                                    <div class="form-file">
                                                        <input type="file" name="pianoStudi" accept=".pdf"
                                                            id="pianoStudi" class="form-file-input">
                                                        <label class="form-file-label" for="pianoStudi">Scegli
                                                            File</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="form-label">Seleziona Certificazione Carriera
                                                    (E-campus)</label>
                                                <div class="form-control-wrap">
                                                    <div class="form-file">
                                                        <input type="file" name="certCarriera" accept=".pdf"
                                                            id="certCarriera" class="form-file-input" required>
                                                        <label class="form-file-label" for="certCarriera">Scegli
                                                            File</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="form-label">Seleziona CV</label>
                                                <div class="form-control-wrap">
                                                    <div class="form-file">
                                                        <input type="file" name="cv" accept=".pdf" id="cv"
                                                            class="form-file-input" required>
                                                        <label class="form-file-label" for="cv">Scegli
                                                            File</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="form-label">Seleziona Attestati</label>
                                                <div class="form-control-wrap">
                                                    <div class="form-file">
                                                        <input type="file" name="attestati" accept=".zip" id="attestati"
                                                            class="form-file-input" required>
                                                        <label class="form-file-label" for="attestati">Scegli
                                                            File</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label class="form-label">Seleziona Prime Ricevute di Pagamento</label>
                                                <div class="form-control-wrap">
                                                    <div class="form-file">
                                                        <input type="file" name="ricevutePagamento" accept=".zip"
                                                            id="ricevutePagamento" class="form-file-input" required>
                                                        <label class="form-file-label" for="ricevutePagamento">Scegli
                                                            File</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="center" style="margin-top: 3%;">
                                    <input type="submit" class="btn btn-primary" value="Salva informazioni">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>