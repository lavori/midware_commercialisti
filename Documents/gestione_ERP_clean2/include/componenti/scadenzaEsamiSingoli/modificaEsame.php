<?php
include('../../config.php');
require_once('../../db.php');

// Inizializzazione del database una sola volta
$database = new Database($host, $username, $password, $db);
$database->connect();

$DatiPost = explode("|", $_POST['id']);
$id = $DatiPost[0];
$dataScritto = $DatiPost[1] ?? "";
$dataOrale = $DatiPost[2] ?? "";
$te = $DatiPost[3] ?? "";

$database->disconnect();
?>
<div class="card-inner">
    <form method="POST">
        <div class="form-group">
            <label class="form-label d-block">Motivazione</label>
            <div class="form-control-wrap">
                <div class="form-check form-check-inline">
                    <input type="radio" class="form-check-input" name="motivazione" id="bocciato" value="bocciato">
                    <label class="form-check-label" for="bocciato">Bocciato</label>
                </div>
                <div class="form-check form-check-inline">
                    <input type="radio" class="form-check-input" name="motivazione" id="rinviato" value="rinviato">
                    <label class="form-check-label" for="rinviato">Rinviato</label>
                </div>
            </div>
        </div>

        <!-- Campo voto, mostrato solo se "Bocciato" -->
        <div class="form-group mt-2" id="motivazione-extra-group" style="display: none;">
            <label for="votoBocciato">Voto</label>
            <input type="number" max="17" class="form-control" name="votoBocciato" id="votoBocciato"
                placeholder="Inserisci voto (max 17)">
        </div>


        <div class="form-group mt-3">
            <input type="hidden" name="te" value="<?php echo $te; ?>">
            <input type="hidden" name="updateEsame" value="update">
            <input type="hidden" name="dataScritto" value="<?php echo $dataScritto; ?>">
            <input type="hidden" name="dataOrale" value="<?php echo $dataOrale; ?>">
            <input type="hidden" name="idEsameSingolo" value="<?php echo htmlspecialchars($id); ?>">
            <button type="submit" class="btn btn-primary">Modifica</button>
        </div>
    </form>
</div>

