
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

<style>
    /* Stile base per ogni scelta selezionata (il "tag") */
.select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: #f0f0f0; /* Un grigio chiaro, puoi usare un colore del tuo tema */
    border: 1px solid #d1d1d1;
    border-radius: 12px;      /* Angoli arrotondati */
    margin-right: 4px;
    margin-top: 4px;
    font-size: 1.2em;
    color: #333;
    transition: background-color 0.2s ease, border-color 0.2s ease;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice:hover {
    background-color: #e9ecef; /* Leggero cambio al passaggio del mouse */
    border-color: #adb5bd;
}

/* Stile per il testo della scelta */
.select2-container--default .select2-selection--multiple .select2-selection__choicedisplay {
    padding-right: 5px; /* Aggiunge un po' di spazio prima della X */
}

/* Stile per il pulsante di rimozione (la 'x') */
.select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: #6c757d; /* Colore della 'x' */
    font-weight: bold;
    font-size: 1.5em;
    opacity: 0.7;
    transition: opacity 0.2s ease, color 0.2s ease;
    padding: 0 3px; /* Aggiunge un po' di padding attorno alla X */
}

.select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
    color: #e85347; /* Colore più scuro o un rosso per indicare rimozione */
    opacity: 1;
    cursor: pointer;
}

/* Se il pulsante di rimozione è un <button> (come nel tuo caso),
   potresti voler resettare alcuni stili del bottone */
.select2-container--default .select2-selection--multiple .select2-selection__choice button.select2-selectionchoice__remove {
    background-color: transparent;
    border: none;
    line-height: 1; /* Allinea meglio la 'x' verticalmente */
}

</style>

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

