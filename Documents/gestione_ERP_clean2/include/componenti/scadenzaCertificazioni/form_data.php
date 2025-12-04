<?php
include('../../config.php');
require_once('../../db.php');

// Inizializzazione del database una sola volta
$database = new Database($host, $username, $password, $db);
$database->connect();
$idDataCertificazioni = $_POST['id'];

$database->disconnect();
?>
<div class="card-inner">
    <form method="POST">
        <div class="form-group">
            <label class="form-label" for="dataCertificazione">Data</label>
            <div class="form-control-wrap">
                <input type="datetime-local" class="form-control" name="dataCertificazione" id="dataCertificazione"
                    min="<?= date('Y-m-d\TH:i') ?>">
            </div>
        </div>
        <div class="form-group">
            <input type="hidden" name="idDataCertificazioni" value="<?php echo $idDataCertificazioni; ?>">
            <button type="submit" class="btn btn-primary">Fissa Data</button>
        </div>
    </form>
</div>