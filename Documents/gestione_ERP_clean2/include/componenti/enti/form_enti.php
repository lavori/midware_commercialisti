<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('../../config.php');
require_once('../../db.php');

// Inizializzazione del database una sola volta
$database = new Database($host, $username, $password, $db);
$database->connect();


if (isset($_POST['id']) && $_POST['id'] != '') {
    $id = $_POST['id'];
    $enti = "SELECT * FROM an_enti WHERE id= " . $id;
    $enti = $database->query($enti);
    $enti = $enti[0];
    $azione = 'update';
} else {
    $azione = 'new';
}
$database->disconnect();
?>

<form id="form_tutor" enctype="multipart/form-data" method="POST">
    <div class="card-inner">
        <div class="row g-gs">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label" for="nome">Nome</label>
                    <div class="form-control-wrap">
                        <input type="text" class="form-control" id="nome" name="nome" 
                        <?php if ($azione == 'update') { ?> value="<?php echo $enti['nome']; ?>" <?php }?>
                            required>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label" for="data_scadenza">Data Scadenza</label>
                    <div class="form-control-wrap">
                        <input type="text" class="form-control date-picker" data-date-format="yyyy-mm-dd" id="data_scadenza" name="data_scadenza" 
                        <?php if ($azione == 'update') { ?> value="<?php echo $enti['data_scadenza']; ?>" <?php } ?>
                        required>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="form-label">Seleziona Listino</label>
                    <div class="form-control-wrap">
                        <div class="form-file">
                            <input type="file" name="listino" accept=".pdf" id="listino"
                                class="form-file-input" <?php if ($azione == 'new') { ?> <?php } ?>>
                            <label class="form-file-label" for="listino">Scegli File</label>
                        </div>
                    </div>
                    <?php if ($azione == 'update' && !empty($enti['listino'])) { ?> 
                        <a href="<?php echo $enti['listino'] . "?nocache=" . time(); ?>"
                            target="_blank">Visualizza Documento</a> 
                    <?php } elseif ($azione == 'update' && empty($enti['listino'])) { ?>
                        <p style="color:red;"><i>Nessun documento caricato</i></p>
                    <?php } ?>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="form-label">Seleziona Convenzione</label>
                    <div class="form-control-wrap">
                        <div class="form-file">
                            <input type="file" name="convenzione" accept=".pdf" id="convenzione" class="form-file-input" <?php if ($azione == 'new') { ?> <?php } ?>>
                            <label class="form-file-label" for="convenzione">Scegli File</label>
                        </div>
                    </div>
                    <?php if ($azione == 'update' && !empty($enti['convenzione'])) { ?> 
                        <a href="<?php echo $enti['convenzione'] . "?nocache=" . time(); ?>" target="_blank">
                            Visualizza Documento</a> 
                        <?php } elseif ($azione == 'update' && empty($enti['convenzione'])) { ?>
                        <p style="color:red;"><i>Nessun documento caricato</i></p>
                    <?php } ?>
                </div>
            </div>
            <div class="col-md-12 center">
                <div class="form-group" style="margin-top: 3%;">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <input type="hidden" name="action" value="<?php echo $azione; ?>">
                    <button type="submit" class="btn btn-lg btn-primary">Salva Informazioni</button>
                </div>
            </div>
        </div>
    </div>
</form>