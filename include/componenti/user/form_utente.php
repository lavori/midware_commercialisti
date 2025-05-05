
<?php 
    include('../../config.php');
    require_once ('../../db.php');
     
    // Inizializzazione del database una sola volta
    $database = new Database($host, $username, $password, $db); 
    $database->connect();
    $ruoli=$database->select('rules','id,ruolo');
    if(isset($_POST['id']) && $_POST['id']!=''){
        $azione='update';
        $id=$_POST['id'];
        $query="SELECT `user`, `nome`, `cognome`, `email`, `ruolo`, `status` FROM `users` WHERE `id` = $id";
        $utenti=$database->query($query);
        $utente=$utenti[0];
    } else {
        $azione='new';
        $utente=array('user'=>'','nome'=>'','cognome'=>'','email'=>'','ruolo'=>'','status'=>'');
    }
    $database->disconnect();
?>
<form id="user" class="form-validate is-alter" novalidate="novalidate">
<?php if ($azione=='update'):?>
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
                <?php foreach($ruoli as $ruolo):?>
                <option value="<?php echo $ruolo['id']; ?>" <?php if($utente['ruolo']==$ruolo['id']) echo 'selected'; ?>><?php echo $ruolo['ruolo']; ?></option>
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
        <label class="form-label" for="email">email</label>
        <div class="form-control-wrap">
        <input type="text" class="form-control" id="email" required="" value="<?php echo $utente['email']; ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="form-label" for="status">Stato</label>
        <div class="form-control-wrap">
            <select class="form-select js-select2" data-placeholder="Stato" id="status">
                <option value="attivo" <?php if($utente['status']=="attivo") echo 'selected'; ?>>Attivo</option>
                <option value="disattivo" <?php if($utente['status']=="disattivo") echo 'selected'; ?>>Disattivo</option>
            </select>
        </div>
    </div>
    <div class="form-group">
        <button type="button" onClick="raccoltadati('<?php echo $azione; ?>','user')" class="btn btn-lg btn-primary">Salva informazioni</button>
    </div>
</form>

