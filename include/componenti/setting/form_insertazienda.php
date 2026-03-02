<?php

include('../../config.php');
require_once('../../db.php');

// Inizializzazione del database una sola volta
$database = new Database($host, $username, $password, $db);
$database->connect();

?>

<form class="form-validate is-alter" method="POST" action="/settings/aziende">
    <div class="form-group">
        <label class="form-label" for="descrizione">Ragione Sociale</label>
        <div class="form-control-wrap">
            <input type="text" class="form-control" name="ragione_sociale" id="ragione_sociale" required>
        </div>
    </div>
    <div class="form-group">
        <label class="form-label" for="email">Dominio API</label>
        <div class="form-control-wrap">
            <input type="text" class="form-control" name="dominioAPI" id="dominioAPI" required>
        </div>
    </div>
    <div class="form-group">
        <input type="hidden" name="action" value="insert">
        <input type="submit" value="Inserisci" class="btn btn-lg btn-primary">
    </div>
</form>