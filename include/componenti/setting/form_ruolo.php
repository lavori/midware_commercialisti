
<?php 
    include('../../config.php');
    require_once ('../../db.php');
     
    // Inizializzazione del database una sola volta
    $database = new Database($host, $username, $password, $db); 
    $database->connect();
    $permessi=$database->select('permessi','id,permesso');
    if(isset($_POST['id']) && $_POST['id']!=''){
        $azione='update';
        $id=$_POST['id'];
        $ruolo=$database->select('rules','*',"id=$id");
        $nomeruolo=$ruolo[0]['ruolo'];
        $permessi_concessi=explode(',',$ruolo[0]['permessi']);
        $extra_concessi=explode(',',$ruolo[0]['extra']);
    } else {
        $azione='new';
        $nomeruolo='';
        $permessi_concessi=array();
        $extra_concessi=array();
    }
    $database->disconnect();
?>
<form id="ruolo" class="form-validate is-alter" novalidate="novalidate">
<?php if ($azione=='update'):?>
    <input type="hidden" id="id" name="id" value="<?php echo $id; ?>">
<?php endif; ?>
    <div class="form-group">
        <label class="form-label" for="nomeruolo">Ruolo</label>
        <div class="form-control-wrap">
            <input type="text" class="form-control" id="nomeruolo" required="" value="<?php echo $nomeruolo; ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="form-label" for="permessi">Permessi</label>
        <div class="form-control-wrap">
            <select class="form-select js-select2" multiple="multiple" data-placeholder="Seleziona Permessi" id="permessi">
                <?php foreach($permessi as $permesso):?>
                <option value="<?php echo $permesso['id']; ?>" <?php if(in_array($permesso['id'], $permessi_concessi)) echo 'selected'; ?>><?php echo $permesso['permesso']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label class="form-label" for="extra">Permessi Extra</label>
        <div class="form-control-wrap">
            <select class="form-select js-select2" multiple="multiple" data-placeholder="Seleziona Permessi" id="extra">
                <?php foreach($permessi as $permesso):?>
                <option value="<?php echo $permesso['id']; ?>" <?php if(in_array($permesso['id'], $extra_concessi)) echo 'selected'; ?>><?php echo $permesso['permesso']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    
    <div class="form-group">
        <button type="button" onClick="raccoltadati('<?php echo $azione; ?>','ruolo')" class="btn btn-lg btn-primary">Salva informazioni</button>
    </div>
</form>

