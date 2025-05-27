<?php 
    include('../../config.php');
    require_once ('../../db.php');
     
    // Inizializzazione del database una sola volta
    $database = new Database($host, $username, $password, $db); 
    $database->connect();
        $id=$_POST['id'];
        $query="SELECT `descrizione`, `email`, `corrispettivo` FROM `corrispettivo` WHERE `id` = $id";
        $corrispettivi=$database->query($query);
        $corrispettivo=$corrispettivi[0];
    $database->disconnect();
?>
<form id="corrispettivo" class="form-validate is-alter" novalidate="novalidate">
    <input type="hidden" id="id" name="id" value="<?php echo $id; ?>">
    <div class="form-group">
        <label class="form-label" for="descrizione">Descrizione</label>
        <div class="form-control-wrap">
            <input type="text" class="form-control" id="descrizione" required="" value="<?php echo $corrispettivo['descrizione']; ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="form-label" for="email">E-mail</label>
        <div class="form-control-wrap">
        <input type="text" class="form-control" id="email" required="" value="<?php echo $corrispettivo['email']; ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="form-label" for="corrispettivo">Corrispettivo</label>
        <div class="form-control-wrap">
        <input type="text" class="form-control" id="corrispettivo" required="" value="<?php echo $corrispettivo['corrispettivo']; ?>">
        </div>
    </div>
    <div class="form-group">
        <button type="button" onClick="raccoltadati('update','corrispettivo')" class="btn btn-lg btn-primary">Salva informazioni</button>
    </div>
</form>