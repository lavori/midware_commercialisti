<?php

include('../../config.php');
require_once('../../db.php');

// Inizializzazione del database una sola volta
$database = new Database($host, $username, $password, $db);
$database->connect();

?>

<form class="form-validate is-alter" method="POST" action="/users/tassisti">
    <div class="form-group">
        <label class="form-label" for="descrizione">Nome</label>
        <div class="form-control-wrap">
            <input type="text" class="form-control" name="nome" id="nome" required>
        </div>
    </div>
    <div class="form-group">
        <label class="form-label" for="email">Cognome</label>
        <div class="form-control-wrap">
            <input type="text" class="form-control" name="cognome" id="cognome" required>
        </div>
    </div>
    <div class="form-group">
        <label class="form-label" for="corrispettivo">Codice Fiscale</label>
        <div class="form-control-wrap">
            <input type="text" class="form-control" id="cf" name="cf" required>
        </div>
    </div>
    <div class="form-group">
        <label class="form-label">Associato</label>
        <div class="form-control-wrap">
            <select class="form-select" name="associato" required>
                <option value="si">si</option>
                <option value="no">no</option>
            </select>
        </div>
    </div>
    <div class="form-group">
        <input type="hidden" name="insert" value="insert">
        <input type="submit" value="Inserisci" class="btn btn-lg btn-primary">
    </div>
</form>