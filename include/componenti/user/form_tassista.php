<?php
include('../../config.php');
require_once('../../db.php');

// Inizializzazione del database una sola volta
$database = new Database($host, $username, $password, $db);
$database->connect();
$giorni = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];

$aziende= $database->select('aziende','id','ragione_sociale');
//query("SELECT * FROM aziende");

if(isset($_POST['id']) && !empty($_POST['id'])){
    $tassisti = "SELECT * FROM tassisti WHERE id= " . $_POST['id'];
    $tassisti = $database->query($tassisti);
    $tassisti = $tassisti[0];
    $giorni_selezionati = unserialize($tassisti['TurniDiLavoro']);
    $azione = "update";
}else{
     $azione = "insert";
}

$database->disconnect();
?>
<form id="tassista" action="/users/tassisti" method="POST" class="form-validate is-alter" novalidate="novalidate">
    <div class="form-group">
        <label class="form-label" for="nome">Azienda</label>
        <div class="form-control-wrap">
            <div class="form-control-select">
                <select class="form-control" id="Azienda" name="Azienda" required>
                    <option value="">Seleziona l'azienda</option>
                    <?php 
                    foreach ($aziende as $azienda) {
                        if($azione=="update"){
                            if($azienda['id'] == $tassisti['AziendaId']){
                                echo "<option value='".$azienda['id']."' selected>".$azienda['ragione_sociale']."</option>";
                            }else{
                                echo "<option value='".$azienda['id']."'>".$azienda['ragione_sociale']."</option>";
                            }
                        }else{
                            echo "<option value='".$azienda['id']."'>".$azienda['ragione_sociale']."</option>";
                        }
                    }
                    ?>
                </select>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label class="form-label" for="nome">Nome</label>
        <div class="form-control-wrap">
            <input type="text" class="form-control" id="nome" name="nome" required
            <?php if($azione=="update"){ ?> value="<?php echo $tassisti['Nome']; ?>" <?php } ?>>
        </div>
    </div>
    <div class="form-group">
        <label class="form-label" for="cognome">Cognome</label>
        <div class="form-control-wrap">
            <input type="text" class="form-control" id="cognome" name="cognome" required
            <?php if($azione=="update"){ ?> value="<?php echo $tassisti['Cognome']; ?>" <?php } ?>>
        </div>
    </div>
    <div class="form-group">
        <label class="form-label" for="licenza">Licenza</label>
        <div class="form-control-wrap">
            <input type="text" class="form-control" id="licenza" name="licenza" required
            <?php if($azione=="update"){ ?> value="<?php echo $tassisti['LicenzaDiGuida']; ?>" <?php } ?>>
        </div>
    </div>
    <div class="form-group">
        <label class="form-label" for="tel">Telefono</label>
        <div class="form-control-wrap">
            <input type="number" class="form-control" id="tel" name="tel" required
            <?php if($azione=="update"){ ?> value="<?php echo $tassisti['Telefono']; ?>" <?php } ?>>
        </div>
    </div>
    <div class="form-group">
        <label class="form-label" for="email">Email</label>
        <div class="form-control-wrap">
            <input type="email" class="form-control" id="email" name="email" required
            <?php if($azione=="update"){ ?> value="<?php echo $tassisti['Email']; ?>" <?php } ?>>
        </div>
    </div>
    <div class="form-group">
        <label class="form-label" for="targa">Targa</label>
        <div class="form-control-wrap">
            <input type="text" class="form-control" id="targa" name="targa" required
            <?php if($azione=="update"){ ?> value="<?php echo $tassisti['TargaTaxi']; ?>" <?php } ?>>
        </div>
    </div>
    <div class="form-group">
        <label class="form-label">Turni</label>
        <ul class="custom-control-group g-7 align-center">
            <?php foreach ($giorni as $g) { ?>
                <li>
                    <div class="custom-control custom-control-sm custom-checkbox checked">
                        <input type="checkbox" class="custom-control-input" id="<?php echo $g; ?>" name="giorni[]" 
                            value="<?php echo $g; ?>" 
                            <?php if($azione=="update"){ ?>
                                <?php if (in_array($g, $giorni_selezionati)) echo 'checked'; ?>
                            <?php } ?>
                            >
                        <label class="custom-control-label" for="<?php echo $g; ?>"><?php echo $g; ?></label>
                    </div>
                </li>
            <?php } ?>
        </ul>
    </div>
    <div class="form-group">
        <label class="form-label">Data</label>
        <div class="form-control-wrap">
            <input type="text" name="data" id="data" class="form-control date-picker-alt" data-date-format="dd-mm-yyyy"
            <?php
            if ($azione == "update") {
                $dataAssunzioneVisualizzata = '';
                if (!empty($tassisti['DataAssunzione'])) {
                    $dataOggetto = DateTime::createFromFormat('Y-m-d', $tassisti['DataAssunzione']);
                    if ($dataOggetto) $dataAssunzioneVisualizzata = $dataOggetto->format('d-m-Y');
                }
            ?> value="<?php echo $dataAssunzioneVisualizzata; ?>" <?php } ?>>
        </div>
    </div>
    <?php if(isset($_POST['id']) && !empty($_POST['id'])){ ?>
        <input type="hidden" name="id"  id="id" value="<?php echo $_POST['id'] ?>" >
    <?php } ?>
    <div class="form-group">
        <button type="button" onClick="raccoltadati_controllo('<?php echo $azione; ?>','tassista',['nome','cognome','licenza','tel','email','targa','data'])"
            class="btn btn-lg btn-primary"><?php if($azione=="update"){ ?>Aggiorna informazioni <?php }else{ ?> Inserisci Tassista<?php } ?></button>
    </div>
</form>