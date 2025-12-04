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

    //Recupera i dati del tutor
    $query = "SELECT * FROM `an_tutor` WHERE `id` = $id";
    $tutor = $database->query($query);
    $tutor = $tutor[0];

    //Printa tutti gli esami
    $query = "SELECT * FROM `an_esami` WHERE 1";
    $esami = $database->query($query);

    // Recupera gli esami associati al tutor
    $query = "SELECT eid FROM `esami_tutor` WHERE `tid` = $id";
    $esamiesistenti = $database->query($query);
    
    $id_selezionati = []; // inizializza array vuoto

    foreach ($esamiesistenti as $v) {
        $id_selezionati[] = $v['eid']; // aggiungi ogni eid all'array
    }

} else {
    $query = "SELECT * FROM `an_esami` WHERE 1";
    $esami = $database->query($query);

    $azione = 'new';
}
$database->disconnect();
?>

<form id="form_tutor" enctype="multipart/form-data" method="POST" action="/settings/gestione">
    <div class="form-group">
        <label class="form-label" for="percorso">Nome</label>
        <div class="form-control-wrap">
            <input type="text" class="form-control" id="nome" name="nome" required <?php if ($azione == 'update') { ?>
                    value="<?php echo $tutor['nome']; ?>" <?php } ?>>
        </div>
    </div>

    <div class="form-group">
        <label class="form-label" for="cognome">Cognome</label>
        <div class="form-control-wrap">
            <input type="text" class="form-control" id="cognome" name="cognome" required <?php if ($azione == 'update') { ?>
                    value="<?php echo $tutor['cognome']; ?>" <?php } ?>>
        </div>
    </div>
        <div class="form-group">
        <label class="form-label" for="cf">CF</label>
        <div class="form-control-wrap">
            <input type="text" class="form-control" id="cf" name="cf" required <?php if ($azione == 'update') { ?>
                    value="<?php echo $tutor['cf']; ?>" <?php } ?>>
        </div>
    </div>
    <div class="form-group">
        <label class="form-label" for="email">Email</label>
        <div class="form-control-wrap">
            <input type="email" class="form-control" id="email" name="email" required <?php if ($azione == 'update') { ?>
                    value="<?php echo $tutor['email']; ?>" <?php } ?>>
        </div>
    </div>
        <div class="form-group">
        <label class="form-label" for="telefono">Telefono</label>
        <div class="form-control-wrap">
            <input type="number" class="form-control" id="telefono" name="telefono" required <?php if ($azione == 'update') { ?>
                    value="<?php echo $tutor['telefono']; ?>" <?php } ?>>
        </div>
    </div>

    <div class="form-group">
        <label class="form-label">Esame</label>
        <div class="form-control-wrap">
            <select class="form-select js-select2" data-search="on" id="id_esami" name="id_esami[]" multiple required>
                <?php foreach ($esami as $v) { ?>
                    <option value="<?php echo htmlspecialchars($v['id']); ?>" <?php echo ($azione === 'update' && in_array($v['id'], $id_selezionati)) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($v['nome']); ?>
                    </option>
                <?php } ?>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label class="form-label">Seleziona CV</label>
        <div class="form-control-wrap">
            <div class="form-file">
                <input type="file" name="filecv" accept=".pdf" id="filecv" class="form-file-input" <?php if($azione=="update"){ ?>
                    value="<?php echo $tutor['cv']; ?>" <?php }else{ ?> required <?php }?>>
                <label class="form-file-label" for="filecv">Scegli File</label>
            </div>
        </div>
        <?php if ($azione == 'update') { ?>
            <cite style="color:red;">Attenzione! Se aggiorni il file non sarà possibile recuperarlo</cite>
            <br>
            <?php if (!empty($tutor['cv'])){?>
                <a href="<?php echo $tutor['cv'] . "?nocache=".time(); ?>" target="_blank">CV Esistente</a>
            <?php } ?>
        <?php } ?>
        
    </div>

    <div class="form-group">
        <input type="hidden" id="action" name="action" value="<?php echo $azione; ?>">
        <?php if($azione=="update"){ ?>
            <input type="hidden" id="id" name="id" value="<?php echo $tutor['id'] ?>">
        <?php } ?>
        <input type="submit"
            class="btn btn-lg btn-primary raccoltadati"
            value="Salva informazioni">
    </div>
</form>