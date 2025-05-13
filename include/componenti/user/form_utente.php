<?php
include('../../config.php');
require_once('../../db.php');

// Inizializzazione del database una sola volta
$database = new Database($host, $username, $password, $db);
$database->connect();

$aziende = $database->select('aziende', 'id,ragione_sociale');

$ruoli = $database->select('rules', 'id,ruolo');
if (isset($_POST['id']) && $_POST['id'] != '') {
    $azione = 'update';
    $id = $_POST['id'];
    $query = "SELECT `user`, `nome`, `cognome`, `email`, `ruolo`,  `id_aziende` ,`status` FROM `users` WHERE `id` = $id";
    $utenti = $database->query($query);
    $utente = $utenti[0];

    if (isset($utente['id_aziende'])) {
        // Se è una stringa tipo "1,2,3", trasformala in array
        $id_aziende = array_map('intval', explode(',', $utente['id_aziende']));
    } else {
        $id_aziende = [];
    }

} else {
    $azione = 'new';
    $utente = array('user' => '', 'nome' => '', 'cognome' => '', 'email' => '', 'ruolo' => '', 'status' => '');
    $id_aziende = []; // <- AGGIUNTA QUI
}
$database->disconnect();
?>
<form id="user" class="form-validate is-alter" novalidate="novalidate">
    <?php if ($azione == 'update'): ?>
        <input type="hidden" id="id" name="id" value="<?php echo $id; ?>">
    <?php endif; ?>
    <div class="form-group">
        <label class="form-label" for="user">Username</label>
        <div class="form-control-wrap">
            <input type="text" class="form-control" id="user" required="" value="<?php echo $utente['user']; ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="form-label" for="pwd">Password</label>
        <div class="form-control-wrap">
            <input type="text" class="form-control" id="pwd" required="" value="">
        </div>
    </div>
    <div class="form-group">
        <label class="form-label" for="pwd2">Conferma Password</label>
        <div class="form-control-wrap">
            <input type="text" class="form-control" id="pwd2" required="" value="">
        </div>
    </div>
    <div class="form-group">
        <label class="form-label" for="ruolo">Ruolo</label>
        <div class="form-control-wrap">
            <select class="form-select js-select2" data-placeholder="Seleziona Ruolo" id="ruolo">
                <?php foreach ($ruoli as $ruolo): ?>
                    <option value="<?php echo $ruolo['id']; ?>" <?php if ($utente['ruolo'] == $ruolo['id'])
                           echo 'selected'; ?>><?php echo $ruolo['ruolo']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label class="form-label" for="nome">Nome</label>
        <div class="form-control-wrap">
            <input type="text" class="form-control" id="nome" required="" value="<?php echo $utente['nome']; ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="form-label" for="cognome">Cognome</label>
        <div class="form-control-wrap">
            <input type="text" class="form-control" id="cognome" required="" value="<?php echo $utente['cognome']; ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="form-label" for="email">Email</label>
        <div class="form-control-wrap">
            <input type="text" class="form-control" id="email" required="" value="<?php echo $utente['email']; ?>">
        </div>
    </div>
    <div class="form-group" data-select2-id="29">
        <label class="form-label">Aziende</label>
        <div class="form-control-wrap" data-select2-id="28">
            <select class="form-select js-select2" name="id_aziende[]" multiple
                data-placeholder="Seleziona opzioni multiple">
                <?php
                    foreach ($aziende as $azienda) {
                        $selected = in_array($azienda['id'], $id_aziende) ? ' selected="selected"' : '';
                        echo '<option value="' . htmlspecialchars($azienda['id']) . '"' . $selected . '>' . htmlspecialchars($azienda['ragione_sociale']) . '</option>';
                    }
                ?>
            </select>

        </div>
    </div>
    <div class="form-group">
        <label class="form-label" for="status">Stato</label>
        <div class="form-control-wrap">
            <select class="form-select js-select2" name="id_aziende[]" multiple
                data-placeholder="Seleziona opzioni multiple">
                <?php
                foreach ($aziende as $azienda) {
                    $selected = in_array($azienda['id'], $id_aziende) ? ' selected="selected"' : '';
                    echo '<option value="' . htmlspecialchars($azienda['id']) . '"' . $selected . '>' . htmlspecialchars($azienda['ragione_sociale']) . '</option>';
                }
                ?>
            </select>
        </div>
    </div>
    <div class="form-group">
        <button type="button" onClick="raccoltadati('<?php echo $azione; ?>','user')"
            class="btn btn-lg btn-primary">Salva informazioni</button>
    </div>
</form>