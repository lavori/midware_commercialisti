<?php
include('../../config.php');
require_once('../../db.php');

// Inizializzazione del database una sola volta
$database = new Database($host, $username, $password, $db);
$database->connect();

if (isset($_POST['id']) && !empty($_POST['id'])) {
    $query="SELECT 
                col.*, 
                u.user, 
                u.pwd, 
                u.nome, 
                u.cognome, 
                u.email, 
                u.ruolo AS id_ruolo, 
                r.ruolo AS nome_ruolo
            FROM 
                an_collaboratori AS col
            JOIN 
                users AS u ON col.id_user = u.id
            JOIN 
                rules AS r ON u.ruolo = r.id
            WHERE 
                col.id =".$_POST['id'];
    $utente = $database->query($query);
    $azione = "update";
    $idUser=$utente[0]['id_user'];
} else {

    $azione = "new";
}

$database->disconnect();
?>
<form id="gestione_esame" method="POST" enctype="multipart/form-data"
    class="form-validate is-alter">
    
    <div class="row gy-4">
        <div class="col-sm-6">
            <div class="form-group">
                <label class="form-label" for="nome">Nome</label>
                <div class="form-control-wrap">
                    <input type="text" class="form-control" id="nome" name="nome" required <?php if ($azione == "update") { ?>
                            value="<?php echo $utente[0]['nome']; ?>" <?php } ?>>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label class="form-label" for="cognome">Cognome</label>
                <div class="form-control-wrap">
                    <input type="text" class="form-control" id="cognome" name="cognome" required <?php if ($azione == "update") { ?>
                            value="<?php echo $utente[0]['cognome']; ?>" <?php } ?>>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <div class="form-control-wrap">
                    <input type="email" class="form-control" id="email" name="email" required <?php if ($azione == "update") { ?>
                            value="<?php echo $utente[0]['email']; ?>" <?php } ?>>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label class="form-label" for="telefono">Telefono</label>
                <div class="form-control-wrap">
                    <input type="number" class="form-control" id="telefono" name="telefono" required <?php if ($azione == "update") { ?>
                            value="<?php echo $utente[0]['telefono']; ?>" <?php } ?>>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label class="form-label" for="referente_via">Indirizzo</label>
                <div class="form-control-wrap">
                    <input type="text" class="form-control" id="referente_via" name="referente_via" required <?php if ($azione == "update") { ?>
                            value="<?php echo $utente[0]['referente_via']; ?>" <?php } ?>>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label class="form-label" for="ruolo">Tipo</label>
                <div class="form-control-wrap">
                    <div class="form-control-wrap">
                        <select class="form-select js-select2" data-search="on" id="ruolo" name="ruolo" required>
                            <?php if ($azione != 'update'): ?>
                                <option value="" disabled selected>Seleziona il tipo utente</option>
                            <?php endif; ?>
                                <option value="4" <?php echo ($azione == 'update' && $utente[0]['ruolo'] == 4) ? 'selected' : ''; ?>>Point</option>
                                <option value="5" <?php echo ($azione == 'update' && $utente[0]['ruolo'] == 5) ? 'selected' : ''; ?>>Segnalatore</option>
                                <option value="8" <?php echo ($azione == 'update' && $utente[0]['ruolo'] == 8) ? 'selected' : ''; ?>>Rivenditore</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="form-group">
                <label class="form-label" for="note">Note</label>
                <div class="form-control-wrap">
                    <textarea class="form-control" id="note" name="note" required><?php 
                        if ($azione == "update") {
                            echo htmlspecialchars(trim($utente[0]['note']));
                        } 
                    ?></textarea>
                </div>
            </div>
        </div>
    </div>
    <?php if (isset($_POST['id']) && !empty($_POST['id'])) { ?>
        <input type="hidden" name="id_user" id="id_user" value="<?php echo $idUser ?>">
        <input type="hidden" name="id" id="id" value="<?php echo $_POST['id'] ?>">
    <?php } ?>
    <input type="hidden" name="action" id="action" value="<?php echo $azione ?>">
    <div class="form-group">
        <br>
        <input type="submit" class="btn btn-lg btn-primary" value="<?php if ($azione == "update") {
            echo "Aggiorna Utente";
        } else {
            echo "Inserisci Utente";
        } ?>">
    </div>
</form>