<?php
include('../../config.php');
require_once('../../db.php');

// Inizializzazione del database una sola volta
$database = new Database($host, $username, $password, $db);
$database->connect();

$DatiPost = explode("|", $_POST['id']);
$id = $DatiPost[0];
$pacchetto = $DatiPost[1] ?? "";
$tipoEsame = $DatiPost[2] ?? "";
//Data che mi servirà nella route
$dataScritto = $DatiPost[3] ?? ""; 
$database->disconnect();
?>
<div class="card-inner">
    <form method="POST">
        <div class="form-group">
            <label class="form-label" for="dataEsame">Data</label>
            <div class="form-control-wrap">
                <input type="datetime-local" class="form-control" name="dataEsame" id="dataEsame"
                    min="<?= date('Y-m-d\TH:i') ?>">
            </div>
        </div>
        <div class="form-group">
            <input type="hidden" name="dataScritto" value="<?php echo $dataScritto; ?>">
            <input type="hidden" name="tipoEsame" value="<?php echo $tipoEsame; ?>">
            <input type="hidden" name="id_esame" value="<?php echo $id; ?>">
            <input type="hidden" name="pacchetto" value="<?php echo $pacchetto; ?>">
            <button type="submit" class="btn btn-primary">Fissa Data</button>
        </div>
    </form>
</div>