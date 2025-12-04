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
                <div class="card-inner">
                    <form id="form_discente" enctype="multipart/form-data" method="POST">
                        <div class="preview-block">
                            <br>
                            <div class="row gy-4">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label" for="cognome">Cognome</label>
                                        <div class="form-control-wrap">
                                            <input type="text" class="form-control" id="cognome" name="cognome" required
                                                <?php if (isset($update) && !empty($update)) { ?>
                                                value="<?php echo $discente['cognome']; ?>" <?php } ?>>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label" for="percorso">Nome</label>
                                        <div class="form-control-wrap">
                                            <input type="text" class="form-control" id="nome" name="nome" required <?php if (isset($update) && !empty($update)) { ?>
                                                value="<?php echo $discente['nome']; ?>" <?php } ?>>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label" for="sesso">Sesso</label>
                                        <div class="form-control-wrap">
                                            <select class="form-control js-select2" id="sesso" name="sesso" required>
                                                        <?php if (isset($_GET['new']) && $_GET['new']=='true') { ?>
                                                    <option value="" disabled selected>Seleziona...</option>
                                                        <?php } ?>
                                                <option value="M" <?php if (isset($discente['sesso']) && $discente['sesso'] == 'M')
                                                    echo 'selected'; ?>>Maschio</option>
                                                <option value="F" <?php if (isset($discente['sesso']) && $discente['sesso'] == 'F')
                                                    echo 'selected'; ?>>Femmina</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label" for="cf">Codice Fiscale</label>
                                        <div class="form-control-wrap">
                                            <input type="text" class="form-control" id="cf" name="cf" required <?php if (isset($update) && !empty($update)) { ?>
                                                value="<?php echo $discente['cf']; ?>" <?php } ?>>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label" for="luogoNascita">Nato A</label>
                                        <div class="form-control-wrap">
                                            <input type="text" class="form-control" id="luogoNascita" name="luogoNascita" required <?php if (isset($update) && !empty($update)) { ?>
                                                value="<?php echo $discente['luogoNascita']; ?>" <?php } ?>>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label" for="provinciaNascita">Provincia</label>
                                        <div class="form-control-wrap">
                                            <input type="text" class="form-control" id="provinciaNascita" name="provinciaNascita" required <?php if (isset($update) && !empty($update)) { ?>
                                                value="<?php echo $discente['provinciaNascita']; ?>" <?php } ?>>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label" for="dataNascita">Data Nascita</label>
                                        <div class="form-control-wrap">
                                            <input type="text" class="form-control date-picker" id="dataNascita" name="dataNascita" required <?php if (isset($update) && !empty($update)) { ?>
                                                value="<?php echo $discente['dataNascita']; ?>" <?php } ?>>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label" for="cittadinanza">Cittadinanza</label>
                                        <div class="form-control-wrap">
                                            <input type="text" class="form-control" id="cittadinanza" name="cittadinanza" required <?php if (isset($update) && !empty($update)) { ?>
                                                value="<?php echo $discente['cittadinanza']; ?>" <?php } ?>>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label" for="secondaCittadinanza">Seconda Cittadinanza</label>
                                        <div class="form-control-wrap">
                                            <input type="text" class="form-control" id="secondaCittadinanza" name="secondaCittadinanza" <?php if (isset($update) && !empty($update)) { ?>
                                                value="<?php echo $discente['secondaCittadinanza']; ?>" <?php } ?>>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label" for="viaResidenza">Residente in Via</label>
                                        <div class="form-control-wrap">
                                            <input type="text" class="form-control" id="viaResidenza" name="viaResidenza" required <?php if (isset($update) && !empty($update)) { ?>
                                                value="<?php echo $discente['viaResidenza']; ?>" <?php } ?>>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label" for="localitaResidenza">Località</label>
                                        <div class="form-control-wrap">
                                            <input type="text" class="form-control" id="localitaResidenza" name="localitaResidenza" required <?php if (isset($update) && !empty($update)) { ?>
                                                value="<?php echo $discente['localitaResidenza']; ?>" <?php } ?>>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label" for="CAPresidenza">CAP</label>
                                        <div class="form-control-wrap">
                                            <input type="number" class="form-control" id="CAPresidenza" name="CAPresidenza" required <?php if (isset($update) && !empty($update)) { ?>
                                                value="<?php echo $discente['CAPresidenza']; ?>" <?php } ?>>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label" for="citta">Città</label>
                                        <div class="form-control-wrap">
                                            <input type="text" class="form-control" id="citta" name="citta" required <?php if (isset($update) && !empty($update)) { ?>
                                                value="<?php echo $discente['citta']; ?>" <?php } ?>>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label" for="provincia">Provincia</label>
                                        <div class="form-control-wrap">
                                            <input type="text" class="form-control" id="provincia" name="provincia"
                                                <?php if (isset($update) && !empty($update)) { ?>
                                                value="<?php echo $discente['provincia']; ?>" <?php } ?>>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label" for="emailPersonale">Email Personale</label>
                                        <div class="form-control-wrap">
                                            <input type="email" pattern="^[+0-9\s\-()]{8,20}$" class="form-control"
                                                id="emailPersonale" name="emailPersonale" <?php if (isset($update) && !empty($update)) { ?>
                                                value="<?php echo $discente['emailPersonale']; ?>" <?php } ?>>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label" for="pwdPersonale">Password Personale</label>
                                        <div class="form-control-wrap">
                                            <input type="text" class="form-control"
                                                id="pwdPersonale" name="pwdPersonale" <?php if (isset($update) && !empty($update)) { ?>
                                                value="<?php echo $discente['pwdPersonale']; ?>" <?php } ?>>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label" for="cellulareEcampus">Cellulare E-Campus</label>
                                        <div class="form-control-wrap">
                                            <input type="number" pattern="^[+0-9\s\-()]{8,20}$" class="form-control"
                                                id="cellulareEcampus" name="cellulareEcampus" <?php if (isset($update) && !empty($update)) { ?>
                                                value="<?php echo $discente['cellulareEcampus']; ?>" <?php } ?>>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label" for="cellulare">Cellulare Personale</label>
                                        <div class="form-control-wrap">
                                            <input type="number" pattern="^[+0-9\s\-()]{8,20}$" class="form-control"
                                                id="cellulare" name="cellulare" required <?php if (isset($update) && !empty($update)) { ?>
                                                value="<?php echo $discente['cellulare']; ?>" <?php } ?>>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label" for="email">Email (Multiuniversity)</label>
                                        <div class="form-control-wrap">
                                            <input type="email" class="form-control" id="email" name="email" required <?php if (isset($update) && !empty($update)) { ?>
                                                value="<?php echo $discente['email']; ?>" <?php } ?>>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label" for="pwdMultiuniversity">Password (Multiuniversity)</label>
                                        <div class="form-control-wrap">
                                            <input type="text" class="form-control" id="pwdMultiuniversity" name="pwdMultiuniversity"
                                                <?php if (isset($update) && !empty($update)) { ?>
                                                value="<?php echo $discente['pwdMultiuniversity']; ?>" <?php } ?>>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label" for="presso">Presso</label>
                                        <div class="form-control-wrap">
                                            <input type="text" class="form-control" id="presso" name="presso"
                                                <?php if (isset($update) && !empty($update)) { ?>
                                                value="<?php echo $discente['presso']; ?>" <?php } ?>>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label" for="viaRecapito">Via</label>
                                        <div class="form-control-wrap">
                                            <input type="text" class="form-control" id="viaRecapito" name="viaRecapito"
                                                <?php if (isset($update) && !empty($update)) { ?>
                                                value="<?php echo $discente['viaRecapito']; ?>" <?php } ?>>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label" for="localitaRecapito">Località</label>
                                        <div class="form-control-wrap">
                                            <input type="text" class="form-control" id="localitaRecapito" name="localitaRecapito"
                                                <?php if (isset($update) && !empty($update)) { ?>
                                                value="<?php echo $discente['localitaRecapito']; ?>" <?php } ?>>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label" for="telefonoDue">Telefono</label>
                                        <div class="form-control-wrap">
                                            <input type="number" pattern="^[+0-9\s\-()]{8,20}$" class="form-control"
                                                id="telefonoDue" name="telefonoDue" <?php if (isset($update) && !empty($update)) { ?>
                                                value="<?php echo $discente['telefonoDue']; ?>" <?php } ?>>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label" for="indirizzoCorrispondente">Indirizzo Corrispondente</label>
                                        <div class="form-control-wrap">
                                            <select class="form-control js-select2" id="indirizzoCorrispondente" name="indirizzoCorrispondente" required>
                                                        <?php if (isset($_GET['new']) && $_GET['new']=='true') { ?>
                                                    <option value="" disabled selected>Seleziona...</option>
                                                        <?php } ?>
                                                <option value="residenza" <?php if (isset($discente['indirizzoCorrispondente']) && $discente['indirizzoCorrispondente'] == 'residenza')
                                                    echo 'selected'; ?>>Residenza</option>
                                                <option value="residenza" <?php if (isset($discente['indirizzoCorrispondente']) && $discente['indirizzoCorrispondente'] == 'residenza')
                                                    echo 'selected'; ?>>Recapito</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label" for="tipoDiploma">Diploma in</label>
                                        <div class="form-control-wrap">
                                            <input type="text" class="form-control" id="tipoDiploma" name="tipoDiploma"
                                                required <?php if (isset($update) && !empty($update)) { ?>
                                                value="<?php echo $discente['tipoDiploma']; ?>" <?php } ?>>
                                        </div>
                                    </div>
                                </div>                        
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label" for="tipoLaurea">Titolo Laurea</label>
                                        <div class="form-control-wrap">
                                            <select class="form-control js-select2" data-search="on" id="tipoLaurea" name="tipoLaurea">
                                                <?php if (!isset($update)) { ?>
                                                    <option value="1" selected>Seleziona...</option>
                                                <?php } ?>
                                                <option value="Laurea (Laurea 1° Livello)" <?php if (isset($discente['tipoLaurea']) && $discente['tipoLaurea'] == 'Laurea (Laurea 1° Livello)')
                                                    echo 'selected'; ?>>Laurea (Laurea 1° Livello)</option>
                                                <option value="Laurea Magistrale" <?php if (isset($discente['tipoLaurea']) && $discente['tipoLaurea'] == 'Laurea Magistrale')
                                                    echo 'selected'; ?>>Laurea Magistrale</option>
                                                    <option value="Laurea Specialistica " <?php if (isset($discente['tipoLaurea']) && $discente['tipoLaurea'] == 'Laurea Specialistica ')
                                                    echo 'selected'; ?>>Laurea Specialistica </option>
                                                <option value="Diploma Universitario" <?php if (isset($discente['tipoLaurea']) && $discente['tipoLaurea'] == 'Diploma Universitario')
                                                    echo 'selected'; ?>>Diploma Universitario</option>
                                                    <option value="Laurea vecchio ordinamento " <?php if (isset($discente['tipoLaurea']) && $discente['tipoLaurea'] == 'Laurea vecchio ordinamento ')
                                                    echo 'selected'; ?>>Laurea vecchio ordinamento </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label" for="titoloLaurea">Laurea In</label>
                                        <div class="form-control-wrap">
                                            <input type="text" class="form-control" id="titoloLaurea" name="titoloLaurea"
                                                 <?php if (isset($update) && !empty($update)) { ?>
                                                value="<?php echo $discente['titoloLaurea']; ?>" <?php } ?>>
                                        </div>
                                    </div>
                                    <br>
                                </div>
                                <hr>
                                <?php if(isset($update)){ ?>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label" for="numMatricola">N. Matricola</label>
                                            <div class="form-control-wrap">
                                                <input type="text" class="form-control" id="numMatricola" name="numMatricola" <?php if (isset($update) && !empty($update)) { ?>
                                                        value="<?php echo $discente['numMatricola']; ?>" <?php } ?>>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label" for="idEcampus">ID E-campus</label>
                                        <div class="form-control-wrap">
                                            <input type="text" class="form-control" id="idEcampus" name="pwdEcampus" <?php if (isset($update) && !empty($update)) { ?>
                                                value="<?php echo $discente['idEcampus']; ?>" <?php } ?>>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label" for="emailEcampus">Email E-campus</label>
                                        <div class="form-control-wrap">
                                            <input type="email" class="form-control" id="emailEcampus" name="emailEcampus" <?php if (isset($update) && !empty($update)) { ?>
                                                    value="<?php echo $discente['emailEcampus']; ?>" <?php } ?>>
                                    </div>
                                </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label" for="pwdEcampus">Password E-campus</label>
                                        <div class="form-control-wrap">
                                            <input type="text" class="form-control" id="pwdEcampus" name="pwdEcampus" <?php if (isset($update) && !empty($update)) { ?>
                                                value="<?php echo $discente['pwdEcampus']; ?>" <?php } ?>>
                                        </div>
                                    </div>
                                </div>
                                <?php if($_SESSION['utente']['ruolo']!='Discente' || $_SESSION['utente']['ruolo']!='Tutor'){ ?>
                                    <?php if($_SESSION['utente']['ruolo']=='Superadmin' || $_SESSION['utente']['ruolo']=='Admin'){ ?>
                                        <div><hr></div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="form-label">Procacciatore</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2" name="referenteDiscente" data-search="on" required>
                                                        <option value="">Seleziona referente...</option>
                                                        <?php foreach ($gruppi as $nome_ruolo => $utenti_gruppo): ?>
                                                            <optgroup label="<?php echo htmlspecialchars($nome_ruolo); ?>">
                                                                <?php foreach ($utenti_gruppo as $utente): ?>
                                                                   <option value="<?php echo $utente['id']; ?>" 
                                                                        <?php echo (isset($update) && $discente['referenteDiscente'] == $utente['id']) ? 'selected' : ''; ?>>
                                                                        <?php echo htmlspecialchars($utente['nome'] . ' ' . $utente['cognome']); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </optgroup>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="form-label" for="contributo">Referente Interno</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2" name="referenteInterno" data-search="on" required>
                                                        <option value="">Seleziona referente...</option>
                                                        <?php foreach ($referente_int as $utente): ?>
                                                            <option value="<?php echo $utente['id']; ?>" 
                                                                <?php echo (isset($update) && $discente['referenteInt'] == $utente['id']) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($utente['nome'] . ' ' . $utente['cognome']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    <?php }elseif($_SESSION['utente']['ruolo']=='Point' || $_SESSION['utente']['ruolo']=='Segnalatore' || $_SESSION['utente']['ruolo']=='Rivenditore'){ ?>
                                        <input type="hidden" name="referenteDiscente" value="<?php echo $_SESSION['utente']['id']; ?>">
                                    <?php } ?>
                                <?php } ?>
                                <div><hr></div>
                                <?php if(!isset($update)){ ?>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label class="form-label">Tipologia Discente</label>
                                            <div class="form-control-wrap">
                                                <select class="form-select js-select2" id="tipologiaDiscente" name="tipologiaDiscente" data-search="on" required>
                                                        <option value="" disabled selected>Seleziona...</option>
                                                        <option value="an_percorsi"<?php if (isset($discente['tipologiaDiscente']) && $discente['tipologiaDiscente'] == 'an_percorsi')
                                                            echo 'an_percorsi'; ?>>Percorso</option>
                                                        <option value="an_esami" <?php if (isset($discente['tipologiaDiscente']) && $discente['tipologiaDiscente'] == 'an_esami')
                                                            echo 'an_esami'; ?>>Esame Singolo</option>
                                                        <option value="an_master" disabled <?php if (isset($discente['tipologiaDiscente']) && $discente['tipologiaDiscente'] == 'an_master')
                                                            echo 'an_master'; ?>>Master</option>
                                                        <option value="an_certificazioni" <?php if (isset($discente['tipologiaDiscente']) && $discente['tipologiaDiscente'] == 'an_certificazioni')
                                                            echo 'an_certificazioni'; ?>>Certificazione</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                <?php }else{ ?>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label class="form-label" for="carriera">Tipologia Discente</label>
                                            <div class="form-control-wrap">
                                                <input type="text" class="form-control" id="carriera" name="carriera" <?php if (isset($update) && !empty($update)) { ?>
                                                    value="<?php echo $discente['carriera']; ?>" <?php } ?> readonly>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                                <input type="hidden"
                                    value="<?php if(isset($discente['tutoraggio']) && !empty($discente['tutoraggio'])){ echo $discente['tutoraggio'];}else{ echo 'no';} ?>" 
                                    name="tutoraggio" id="tutoraggio">
                                <div class="center">
                                    <input type="hidden" id="action" name="action" value="<?php 
                                            if(isset($update) && !empty($update)) {
                                                echo 'update';
                                            } else {
                                                echo 'new';
                                            }
                                        ?>">
                                    <?php if (isset($update) && !empty($update)) { ?>
                                        <input type="hidden" id="id" name="id" value="<?php echo $discente['id'] ?>">
                                    <?php } ?>
                                    <input type="submit" class="btn btn-lg btn-primary raccoltadati"
                                        value="Salva informazioni">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div><!-- .card-preview -->
    </div>
</div>