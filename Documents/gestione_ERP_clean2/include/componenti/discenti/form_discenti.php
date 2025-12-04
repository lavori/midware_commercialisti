<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('../../config.php');
require_once('../../db.php');

// Inizializzazione del database una sola volta
$database = new Database($host, $username, $password, $db);
$database->connect();


if (isset($_POST['id']) && $_POST['id'] != '') {

    $azione = 'update';
    $id = $_POST['id'];

    //Recupera i dati del discente

    $discente=$database->select("an_discenti","*","id= ".$_POST['id']);
    $discente=$discente[0];

} else {

    $azione = 'new';
}
$database->disconnect();
?>

<form id="form_discente" enctype="multipart/form-data" method="POST">
    <div class="preview-block">
        <div class="row gy-4">
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="form-label" for="percorso">Nome</label>
                    <div class="form-control-wrap">
                        <input type="text" class="form-control" id="nome" name="nome" required <?php if ($azione == 'update') { ?>
                                value="<?php echo $discente['nome']; ?>" <?php } ?>>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="form-label" for="cognome">Cognome</label>
                    <div class="form-control-wrap">
                        <input type="text" class="form-control" id="cognome" name="cognome" required <?php if ($azione == 'update') { ?>
                                value="<?php echo $discente['cognome']; ?>" <?php } ?>>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="form-label" for="sesso">Sesso</label>
                    <div class="form-control-select">
                        <select class="form-control" id="sesso" name="sesso" required>
                            <?php if ($azione == "new") { ?>
                                <option value="" disabled selected>Seleziona...</option>
                            <?php } ?>
                            <option value="M" <?php if (isset($discente['sesso']) && $discente['sesso'] == 'M') echo 'selected'; ?>>Maschio</option>
                            <option value="F" <?php if (isset($discente['sesso']) && $discente['sesso'] == 'F') echo 'selected'; ?>>Femmina</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label class="form-label" for="dataNascita">Data di Nascita</label>
                    <div class="form-control-wrap">
                        <input type="date" class="form-control" id="dataNascita" name="dataNascita" required <?php if ($azione == 'update') { ?>
                                value="<?php echo $discente['dataNascita']; ?>" <?php } ?>>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label class="form-label" for="luogoNascita">Luogo di Nascita</label>
                    <div class="form-control-wrap">
                        <input type="text" class="form-control" id="luogoNascita" name="luogoNascita" required <?php if ($azione == 'update') { ?>
                                value="<?php echo $discente['luogoNascita']; ?>" <?php } ?>>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label class="form-label" for="provinciaNascita">Provincia di Nascita (Sigla)</label>
                    <div class="form-control-wrap">
                        <input type="text" class="form-control" id="provinciaNascita" name="provinciaNascita" required
                            maxlength="2" <?php if ($azione == 'update') { ?>
                                value="<?php echo $discente['provinciaNascita']; ?>" <?php } ?>>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="form-label" for="cf">Codice Fiscale</label>
                    <div class="form-control-wrap">
                        <input type="text" class="form-control" id="cf" name="cf" required <?php if ($azione == 'update') { ?>
                                value="<?php echo $discente['cf']; ?>" <?php } ?>>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="form-label" for="telefono">Telefono</label>
                    <div class="form-control-wrap">
                        <input type="number" pattern="^[+0-9\s\-()]{8,20}$"  class="form-control" id="telefono" name="telefono" <?php if ($azione == 'update') { ?>
                                value="<?php echo $discente['telefono']; ?>" <?php } ?>>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="form-label" for="cellulare">Cellulare</label>
                    <div class="form-control-wrap">
                        <input type="number" pattern="^[+0-9\s\-()]{8,20}$"  class="form-control" id="cellulare" name="cellulare" required <?php if ($azione == 'update') { ?>
                                value="<?php echo $discente['cellulare']; ?>" <?php } ?>>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <div class="form-control-wrap">
                        <input type="email" class="form-control" id="email" name="email" required <?php if ($azione == 'update') { ?>
                                value="<?php echo $discente['email']; ?>" <?php } ?>>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="form-label" for="email">Città</label>
                    <div class="form-control-wrap">
                        <input type="text" class="form-control" id="citta" name="citta" required <?php if ($azione == 'update') { ?>
                                value="<?php echo $discente['citta']; ?>" <?php } ?>>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="form-label" for="provincia">Provincia</label>
                    <div class="form-control-wrap">
                        <input type="text" class="form-control" id="provincia" name="provincia" required <?php if ($azione == 'update') { ?>
                                value="<?php echo $discente['provincia']; ?>" <?php } ?>>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="form-label" for="indirizzo_via">Indirizzo</label>
                    <div class="form-control-wrap">
                        <input type="text" class="form-control" id="indirizzo_via" name="indirizzo_via" required <?php if ($azione == 'update') { ?>
                                value="<?php echo $discente['indirizzoCorrispondente']; ?>" <?php } ?>>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="form-label" for="email_s">Email Sender</label>
                    <div class="form-control-wrap">
                        <input type="text" class="form-control" id="email_s" name="email_s" <?php if ($azione == 'update') { ?>
                                value="<?php echo $discente['email_s']; ?>" <?php } ?>>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="form-label" for="pwd">Password</label>
                    <div class="form-control-wrap">
                        <input type="text" class="form-control" id="pwd" name="pwd" <?php if ($azione == 'update') { ?>
                                value="<?php echo $discente['pwd']; ?>" <?php } ?>>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="form-label" for="smtp">SMTP</label>
                    <div class="form-control-wrap">
                        <input type="text" class="form-control" id="smtp" name="smtp" <?php if ($azione == 'update') { ?>
                                value="<?php echo $discente['smtp']; ?>" <?php } else {?> value="smpts.aruba.it" <?php } ?>>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="form-label" for="porta">Porta</label>
                    <div class="form-control-wrap">
                        <input type="number" class="form-control" id="porta" name="porta" <?php if ($azione == 'update') { ?>
                                value="<?php echo $discente['porta']; ?>" <?php } else {?> value="465" <?php } ?>>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="form-label">Seleziona Carta D'Identità</label>
                    <div class="form-control-wrap">
                        <div class="form-file">
                            <input type="file" name="id_card" accept=".pdf" id="id_card" class="form-file-input">
                            <label class="form-file-label" for="id_card">Scegli File</label>
                        </div>
                    </div>
                    <?php if($azione=='update'){ ?> <a href="<?php echo $discente['id_card']."?nocache=".time(); ?>" target="_blank">Visualizza Documento</a> <?php } ?>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="form-label">Seleziona Tessera Sanitaria</label>
                    <div class="form-control-wrap">
                        <div class="form-file">
                            <input type="file" name="tessera" accept=".pdf" id="tessera" class="form-file-input">
                            <label class="form-file-label" for="tessera">Scegli File</label>
                        </div>
                    </div>
                    <?php if($azione=='update' && !empty($discente['tessera'])){ ?> <a href="<?php echo $discente['tessera']."?nocache=".time(); ?>" target="_blank">Visualizza Documento</a> <?php } ?>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="form-label">Seleziona Diploma/Certificazione</label>
                    <div class="form-control-wrap">
                        <div class="form-file">
                            <input type="file" name="diploma" accept=".pdf" id="diploma" class="form-file-input">
                            <label class="form-file-label" for="diploma">Scegli File</label>
                        </div>
                    </div>
                    <?php if($azione=='update' && !empty($discente['diploma'])){ ?> <a href="<?php echo $discente['diploma'] ?>" target="_blank">Visualizza Documento</a> <?php } ?>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="form-label">Seleziona Certificazione Inglese</label>
                    <div class="form-control-wrap">
                        <div class="form-file">
                            <input type="file" name="cert_inglese" accept=".pdf" id="cert_inglese" class="form-file-input">
                            <label class="form-file-label" for="cert_inglese">Scegli File</label>
                        </div>
                    </div>
                    <?php if($azione=='update' && !empty($discente['cert_inglese'])){ ?> <a href="<?php echo $discente['cert_inglese']."?nocache=".time(); ?>" target="_blank">Visualizza Documento</a> <?php } ?>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="form-label">Seleziona Certificazione Informatica</label>
                    <div class="form-control-wrap">
                        <div class="form-file">
                            <input type="file" name="cert_informatica" accept=".pdf" id="cert_informatica" class="form-file-input">
                            <label class="form-file-label" for="cert_informatica">Scegli File</label>
                        </div>
                    </div>
                    <?php if($azione=='update' && !empty($discente['cert_informatica'])){ ?> <a href="<?php echo $discente['cert_informatica']."?nocache=".time(); ?>" target="_blank">Visualizza Documento</a> <?php } ?>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="form-label">Seleziona CV</label>
                    <div class="form-control-wrap">
                        <div class="form-file">
                            <input type="file" name="cv" accept=".pdf" id="cv" class="form-file-input">
                            <label class="form-file-label" for="cv">Scegli File</label>
                        </div>
                    </div>
                    <?php if($azione=='update' && !empty($discente['cv'])){ ?> <a href="<?php echo $discente['cv']."?nocache=".time(); ?>" target="_blank">Visualizza Documento</a> <?php } ?>
                </div>
            </div>
            <?php if($azione=='update'){ ?>
                <div class="center">
                    <p style="color: red;">Attenzione! Se aggiorni il file non sarà possibile recuperarlo</p>
                </div>
            <?php } ?>
        </div>
    </div>
    <div class="center" style="margin-top: 3%;">
        <input type="hidden" id="action" name="action" value="<?php echo $azione; ?>">
        <?php if($azione=="update"){ ?>
            <input type="hidden" id="id" name="id" value="<?php echo $discente['id'] ?>">
        <?php } ?>
        <input type="submit"
            class="btn btn-lg btn-primary raccoltadati"
            value="Salva informazioni">
    </div>
</form>