<!-- content @s -->
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="nk-block  nk-block-lg">
                    <div class="card card-bordered card-preview">
                        <!-------DATI ATTIVITA'------->
                        <div class="card-inner col-sm-12">
                            <?php if (isset($message) && $message == "EmailOk") { ?>
                                <div class="example-alert">
                                    <div class="alert alert-success alert-icon">
                                        <em class="icon ni ni-check-circle"></em> <strong>Email inviata</strong> con
                                        successo
                                    </div>
                                </div>
                                <br>
                            <?php } elseif (isset($message) && $message == "EmailKo") { ?>
                                <div class="example-alert">
                                    <div class="alert alert-danger alert-icon">
                                        <em class="icon ni ni-cross-circle"></em> <strong>Invio Fallito</strong>! Si è
                                        verificato un errore
                                    </div>
                                </div>
                                <br>
                            <?php } elseif (isset($message) && $message == "EmailEsistente") { ?>
                                <div class="example-alert">
                                    <div class="alert alert-danger alert-icon">
                                        <em class="icon ni ni-cross-circle"></em> <strong>Invio Fallito</strong>! È presente
                                        già un invito attivo
                                    </div>
                                </div>
                                <br>
                            <?php } elseif (isset($message) && $message == "insertDoc") { ?>
                                <div class="alert alert-success alert-icon">
                                    <em class="icon ni ni-check-circle"></em><strong>Documenti Inseriti
                                        Correttamente!</strong>
                                </div>
                                <br>
                            <?php } ?>
                        </div>
                        <?php if ($_SESSION['utente']['ruolo'] != 'Discente') { ?>
                            <?php
                            if (empty($discente[0]['idUser'])) { ?>
                            <form method="POST">
                                <div class="card-inner center">
                                    <div class="form-group">
                                        <input type="hidden" name="inviaemail" value="<?php echo $id ?>">
                                        <input type="submit" class="btn btn-primary" value="Invia Email">
                                    </div>
                                </div>
                            </form>
                            <hr>
                            <br>
                            <?php } ?>
                        <?php } ?>
                        <!--Inserisci i documenti mancanti per completare la registrazione-->
                        <div class="card-inner">
                            <?php if(empty($discente[0]['id_card']) || empty($discente[0]['tessera']) || empty($discente[0]['certificazioneZip']) || empty($discente[0]['cv'])){ ?>
                                <form id="file_discente" enctype="multipart/form-data" method="POST">
                                    <div class="preview-block">
                                        <div class="row gy-4">
                                            <?php if(empty($discente[0]['id_card'])){ ?>
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label class="form-label">Seleziona Carta D'Identità</label>
                                                        <div class="form-control-wrap">
                                                            <div class="form-file">
                                                                <input type="file" name="id_card" accept=".pdf" id="id_card"
                                                                    class="form-file-input" 
                                                                    value="<?php if(!empty($discente[0]['id_card'])){ echo $discente[0]['id_card'];} ?>">
                                                                <label class="form-file-label" for="id_card">Scegli
                                                                    File</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php }else{ ?>
                                                <input type="hidden" value="<?php echo $discente[0]['id_card'] ?>" name="id_card" id="id_card">
                                            <?php } ?>
                                            <?php if(empty($discente[0]['tessera'])){ ?>
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label class="form-label">Seleziona Tessera Sanitaria</label>
                                                        <div class="form-control-wrap">
                                                            <div class="form-file">
                                                                <input type="file" name="tessera" accept=".pdf" id="tessera"
                                                                    class="form-file-input"
                                                                     value="<?php if(!empty($discente[0]['tessera'])){ echo $discente[0]['tessera'];} ?>"
                                                                    >
                                                                <label class="form-file-label" for="tessera">Scegli
                                                                    File</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php } else { ?>
                                                <input type="hidden" value="<?php echo $discente[0]['tessera'] ?>" name="tessera" id="tessera">
                                            <?php } ?>
                                            <?php if(empty($discente[0]['certificazioneZip'])){ ?>
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label class="form-label">Seleziona
                                                            Certificazioni</label>
                                                        <div class="form-control-wrap">
                                                            <div class="form-file">
                                                                <input type="file" name="certificazioneZip[]" accept=".pdf"
                                                                    id="certificazioneZip" class="form-file-input" multiple>
                                                                <label class="form-file-label" for="certificazioneZip">Scegli
                                                                    File</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php }else{ ?>
                                                <input type="hidden" value="<?php echo $discente[0]['certificazioneZip'] ?>" name="certificazioneZip" id="certificazioneZip">
                                            <?php } ?>
                                            <?php if(empty($discente[0]['cv'])){ ?>
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label class="form-label">Seleziona CV</label>
                                                        <div class="form-control-wrap">
                                                            <div class="form-file">
                                                                <input type="file" name="cv" accept=".pdf" id="cv"
                                                                    class="form-file-input"                                                                                                                                        
                                                                    >
                                                                <label class="form-file-label" for="cv">Scegli
                                                                    File</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php }else{ ?>
                                                <input type="hidden" value="<?php echo $discente[0]['cv'] ?>" name="cv" id="cv">
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="center" style="margin-top: 3%;">
                                        <input type="hidden" name="action" value="insertDocumenti">
                                        <input type="submit" class="btn btn-lg btn-primary raccoltadati"
                                            value="Salva informazioni">
                                    </div>
                                </form>
                                <hr>
                            <?php } ?>
                        </div>
                        <div class="card-head card-inner center">
                            <h4 class="preview-title-lg overline-title">Dati Discente</h4>
                        </div>
                        <div class="card-inner">
                            <div class="preview-block">
                                <div class="row gy-4">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label" for="cognome">Cognome</label>
                                            <div class="form-control-wrap">
                                                <input type="text" class="form-control" id="cognome" name="cognome"
                                                    readonly value="<?php echo $discente[0]['cognome']; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label" for="percorso">Nome</label>
                                            <div class="form-control-wrap">
                                                <input type="text" class="form-control" id="nome" name="nome" readonly
                                                    value="<?php echo $discente[0]['nome']; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label" for="sesso">Sesso</label>
                                            <div class="form-control-wrap">
                                                <input type="text" class="form-control" id="sesso" name="sesso" readonly
                                                    value="<?php if ($discente[0]['sesso'] == 'M') {
                                                        echo 'Maschio';
                                                    } else {
                                                        echo 'Femmina';
                                                    } ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label" for="cf">Codice Fiscale</label>
                                            <div class="form-control-wrap">
                                                <input type="text" class="form-control" id="cf" name="cf" readonly
                                                    value="<?php echo $discente[0]['cf']; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label" for="luogoNascita">Nato a</label>
                                            <div class="form-control-wrap">
                                                <input type="text" class="form-control" id="luogoNascita"
                                                    name="luogoNascita" readonly
                                                    value="<?php echo $discente[0]['luogoNascita']; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label" for="provincia">Provincia</label>
                                            <div class="form-control-wrap">
                                                <input type="text" class="form-control" id="provincia" name="provincia"
                                                    readonly value="<?php echo $discente[0]['provincia']; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label" for="dataNascita">Data di Nascita</label>
                                            <div class="form-control-wrap">
                                                <input type="text" class="form-control" id="dataNascita"
                                                    name="dataNascita" readonly
                                                    value="<?php echo $discente[0]['dataNascita']; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label" for="cittadinanza">Cittadinanza</label>
                                            <div class="form-control-wrap">
                                                <input type="text" class="form-control" id="cittadinanza"
                                                    name="cittadinanza" readonly
                                                    value="<?php echo $discente[0]['cittadinanza']; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label" for="secondaCittadinanza">Seconda
                                                Cittadinanza</label>
                                            <div class="form-control-wrap">
                                                <input type="text" class="form-control" id="secondaCittadinanza"
                                                    name="secondaCittadinanza" readonly
                                                    value="<?php echo $discente[0]['secondaCittadinanza']; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label" for="viaResidenza">Residente in Via</label>
                                            <div class="form-control-wrap">
                                                <input type="text" class="form-control" id="viaResidenza"
                                                    name="viaResidenza" readonly
                                                    value="<?php echo $discente[0]['viaResidenza']; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label" for="localitaResidenza">Località</label>
                                            <div class="form-control-wrap">
                                                <input type="text" class="form-control" id="localitaResidenza"
                                                    name="localitaResidenza" readonly
                                                    value="<?php echo $discente[0]['localitaResidenza']; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label" for="CAPresidenza">CAP</label>
                                            <div class="form-control-wrap">
                                                <input type="text" class="form-control" id="CAPresidenza"
                                                    name="CAPresidenza" readonly
                                                    value="<?php echo $discente[0]['CAPresidenza']; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label" for="citta">Città</label>
                                            <div class="form-control-wrap">
                                                <input type="text" class="form-control" id="citta" name="citta" readonly
                                                    value="<?php echo $discente[0]['citta']; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label" for="provinciaNascita">Provincia</label>
                                            <div class="form-control-wrap">
                                                <input type="text" class="form-control" id="provinciaNascita"
                                                    name="provinciaNascita" readonly
                                                    value="<?php echo $discente[0]['provinciaNascita']; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label" for="cellulare">Cellulare</label>
                                            <div class="form-control-wrap">
                                                <input type="number" readonly class="form-control" id="cellulare"
                                                    name="cellulare" value="<?php echo $discente[0]['cellulare']; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label" for="cellulareEcampus">Cellulare E-Campus</label>
                                            <div class="form-control-wrap">
                                                <input type="number" readonly class="form-control" id="cellulareEcampus"
                                                    name="cellulareEcampus" value="<?php echo $discente[0]['cellulareEcampus']; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label" for="email">Email(Multiuniversity)</label>
                                            <div class="form-control-wrap">
                                                <input type="email" class="form-control" id="email" name="email"
                                                    readonly value="<?php echo $discente[0]['email']; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label" for="pwdMultiuniversity">Password(Multiuniversity)</label>
                                            <div class="form-control-wrap">
                                                <input type="text" class="form-control" id="pwdMultiuniversity" name="pwdMultiuniversity"
                                                    readonly value="<?php echo $discente[0]['pwdMultiuniversity']; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label" for="presso">Presso</label>
                                            <div class="form-control-wrap">
                                                <input type="text" class="form-control" id="presso" name="presso"
                                                    readonly value="<?php echo $discente[0]['presso']; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label" for="viaResidenza">Via</label>
                                            <div class="form-control-wrap">
                                                <input type="text" class="form-control" id="viaResidenza"
                                                    name="viaResidenza" readonly
                                                    value="<?php echo $discente[0]['viaResidenza']; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label" for="localitaRecapito">Località</label>
                                            <div class="form-control-wrap">
                                                <input type="text" class="form-control" id="localitaRecapito"
                                                    name="localitaRecapito" readonly
                                                    value="<?php echo $discente[0]['localitaRecapito']; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label" for="telefonoDue">Telefono</label>
                                            <div class="form-control-wrap">
                                                <input type="number" pattern="^[+0-9\s\-()]{8,20}$" class="form-control"
                                                    id="telefonoDue" name="telefonoDue" required
                                                    <?php if (isset($update) && !empty($update)) { ?>
                                                    value="<?php echo $discente['telefonoDue']; ?>" <?php } ?> readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label" for="indirizzoCorrispondente">Indirizzo
                                                Corrispondente</label>
                                            <div class="form-control-wrap">
                                                <input type="text" class="form-control" id="indirizzoCorrispondente"
                                                    name="indirizzoCorrispondente" readonly
                                                    value="<?php echo ucfirst($discente[0]['indirizzoCorrispondente']); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label" for="tipoDiploma">Diploma In</label>
                                            <div class="form-control-wrap">
                                                <input type="text" class="form-control" id="tipoDiploma"
                                                    name="tipoDiploma" readonly
                                                    value="<?php echo $discente[0]['tipoDiploma']; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label" for="tipoLaurea">Titolo Laurea</label>
                                            <div class="form-control-wrap">
                                                <input type="text" class="form-control" id="tipoLaurea"
                                                    name="tipoLaurea" readonly
                                                    value="<?php echo $discente[0]['tipoLaurea']; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label" for="titoloLaurea">Laurea In</label>
                                            <div class="form-control-wrap">
                                                <input type="text" class="form-control" id="titoloLaurea"
                                                    name="titoloLaurea" readonly
                                                    value="<?php echo $discente[0]['titoloLaurea']; ?>">
                                            </div>
                                        </div>
                                        <br>
                                    </div>
                                    <hr>
                                    <?php if($_SESSION['utente']['ruolo']!='Discente'):?>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label" for="idEcampus">ID E-campus</label>
                                            <div class="form-control-wrap">
                                                <input type="text" class="form-control" id="idEcampus" name="pwdEcampus" <?php if (isset($update) && !empty($update)) { ?>
                                                    value="<?php echo $discente['idEcampus']; ?>" <?php } ?> readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label" for="emailEcampus">Email E-campus</label>
                                            <div class="form-control-wrap">
                                                <input type="text" class="form-control" id="emailEcampus" name="emailEcampus"
                                                    readonly value="<?php echo $discente[0]['emailEcampus']; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label" for="pwdEcampus">Password E-campus</label>
                                            <div class="form-control-wrap">
                                                <input type="text" class="form-control" id="pwdEcampus" name="pwdEcampus" readonly
                                                    value="<?php echo $discente[0]['pwdEcampus']; ?>">
                                            </div>
                                        </div>
                                        <br>
                                    </div>
                                    <hr>
                                    <?php endif; ?>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label">Seleziona Carta D'Identità</label>
                                            <div class="form-control-wrap">
                                                <div class="form-file">
                                                    <?php if(isset($discente[0]['id_card']) && !empty($discente[0]['id_card'])): ?>
                                                    <a href="<?php echo $discente[0]['id_card'] . "?nocache=" . time(); ?>"
                                                        target="_blank">
                                                        Carta D'Identità
                                                    </a>
                                                    <?php else: ?>
                                                    Documento non caricato
                                                    <?php endif;?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label">Tessera Sanitaria</label>
                                            <div class="form-control-wrap">
                                                <div class="form-file">
                                                    <?php if(!empty($discente[0]['tessera'])): ?>

                                                    <a href="<?php echo $discente[0]['tessera'] . "?nocache=" . time(); ?>"
                                                        target="_blank">
                                                        Tessera Sanitaria
                                                    </a>
                                                    <?php else: ?>
                                                    Documento non caricato
                                                    <?php endif;?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label">Certificazione Zip</label>
                                            <div class="form-control-wrap">
                                                <div class="form-file">
                                                    <?php if(!empty($discente[0]['certificazioneZip']['zip'])): ?>
                                                    <a href="<?php echo $discente[0]['certificazioneZip']['zip'] . "?nocache=" . time(); ?>"
                                                        target="_blank">
                                                        File Zip
                                                    </a>
                                                    <?php else: ?>
                                                    Documento non caricato
                                                    <?php endif;?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label">CV</label>
                                            <div class="form-control-wrap">
                                                <div class="form-file">
                                                    <?php if(!empty($discente[0]['cv'])): ?>
                                                    <a href="<?php echo $discente[0]['cv'] . "?nocache=" . time(); ?>"
                                                        target="_blank">
                                                        CV
                                                    </a>
                                                    <?php else: ?>
                                                    Documento non caricato
                                                    <?php endif;?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if(isset($discente[0]['documento_unito']) && !empty($discente[0]['documento_unito'])){?>
                                        <hr>
                                        <div class="card-head card-inner center">
                                            <h4 class="preview-title-lg overline-title">File Immatricolazione</h4>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <input type="button" class="btn btn-primary" href="<?php echo $discente[0]['documento_unito'] ?>" value="Scarica File Immatricolazione">
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>