<?php
include('../../config.php');
require_once('../../db.php');

// Inizializzazione del database una sola volta
$database = new Database($host, $username, $password, $db);
$database->connect();
$id=$_POST['id'];
$database->disconnect();
?>
<div class="card-inner">
    <form method="POST">
        <div class="form-group">
            <label class="form-label" for="voto">Inserisci Voto</label>
            <div class="form-control-wrap">
                <input type="number" max="30" min="18" class="form-control" name="voto">
            </div>
        </div>
        <div class="form-group">
            <input type="hidden" name="idEsameSingolo" value="<?php echo $id;?>">
            <button type="submit" class="btn btn-primary">Inserisci</button>
        </div>
    </form>
</div>