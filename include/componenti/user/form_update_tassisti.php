
<?php

include('../../config.php');
require_once ('../../db.php');
 
// Inizializzazione del database una sola volta
$database = new Database($host, $username, $password, $db); 
$database->connect();

$tassisti=$database->select("tassisti","*","id= ".$_POST['id']);

?>

<form class="form-validate is-alter" method="POST" action="/users/tassisti">
    <div class="form-group">
        <label class="form-label" for="descrizione">Nome</label>
        <div class="form-control-wrap">
            <input type="text" class="form-control" name="nome" id="nome" value="<?php echo $tassisti[0]['nome'] ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="form-label" for="email">Cognome</label>
        <div class="form-control-wrap">
        <input type="text" class="form-control" name="cognome" id="cognome" value="<?php echo $tassisti[0]['cognome'] ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="form-label" for="corrispettivo">Codice Fiscale</label>
        <div class="form-control-wrap">
        <input type="text" class="form-control" id="cf" name="cf" value="<?php echo $tassisti[0]['cf'] ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="form-label">Associato</label>
        <div class="form-control-wrap">
            <select class="form-select" name="associato">
                <option value="<?php echo $tassisti[0]['associato'] ?>"><?php echo $tassisti[0]['associato'] ?></option>
                <option value="si">si</option>
                <option value="no">no</option>
            </select>
        </div>
    </div>
    <div class="form-group">
        <input type="hidden" name="aggiorna_id" value="<?php echo $_POST['id'] ?>">
        <input type="submit" value="Aggiorna" class="btn btn-lg btn-primary">   
    </div>
</form>