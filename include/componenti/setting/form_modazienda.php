
<?php

include('../../config.php');
require_once ('../../db.php');
 
// Inizializzazione del database una sola volta
$database = new Database($host, $username, $password, $db); 
$database->connect();

$aziende=$database->select("aziende","*","id= ".$_POST['id']);

?>

<form class="form-validate is-alter" method="POST" action="/settings/aziende">
    <div class="form-group">
        <label class="form-label" for="dominioAPI">Ragione Sociale</label>
        <div class="form-control-wrap">
            <input type="text" class="form-control" name="ragione_sociale" id="ragione_sociale" value="<?php echo $aziende[0]['ragione_sociale'] ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="form-label" for="dominioAPI">Dominio API</label>
        <div class="form-control-wrap">
        <input type="text" class="form-control" name="dominioAPI" id="dominioAPI" value="<?php echo $aziende[0]['CodiceAPI'] ?>">
        </div>
    </div>
    <div class="form-group">
        <input type="hidden" name="aggiorna_id" value="<?php echo $_POST['id'] ?>">
        <input type="submit" value="Aggiorna" class="btn btn-lg btn-primary">   
    </div>
</form>