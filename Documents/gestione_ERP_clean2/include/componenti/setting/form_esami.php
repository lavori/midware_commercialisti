<?php
include('../../config.php');
require_once('../../db.php');

// Inizializzazione del database una sola volta
$database = new Database($host, $username, $password, $db);
$database->connect();
$azione = "";
if (isset($_POST['id']) && !empty($_POST['id'])) {

    $esame = "SELECT * FROM an_esami WHERE id= " . $_POST['id'];
    $esame = $database->query($esame);
    $esame = $esame[0];

    $percorso = "SELECT * FROM an_percorsi WHERE 1";
    $percorso = $database->query($percorso);

    $azione = "update";
} else {
    $percorso = "SELECT * FROM an_percorsi WHERE 1";
    $percorso = $database->query($percorso);
    $azione = "new";
}

$database->disconnect();
?>
<form id="gestione_esame" method="POST" enctype="multipart/form-data" class="form-validate is-alter">
    <div class="form-group">
        <label class="form-label" for="percorso">Percorso</label>
        <div class="form-control-wrap">
            <select class="form-select js-select2" id="percorso" name="percorso" data-search="on" required>
                <?php if ($azione != 'update'): ?>
                    <option value="" disabled selected>Seleziona il tipo di corso</option>
                <?php endif; ?>

                <?php foreach ($percorso as $v): ?>
                    <option value="<?php echo htmlspecialchars($v['id']); ?>" <?php if (isset($esame['pid']) && $esame['pid'] == $v['id'])
                           echo 'selected'; ?>>
                        <?php echo htmlspecialchars($v['percorso']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    </div>
    <div class="form-group">
        <label class="form-label" for="codice">Codice Esame</label>
        <div class="form-control-wrap">
            <input type="text" class="form-control" id="codice" name="codice" required <?php if ($azione == "update") { ?>
                    value="<?php echo $esame['codice']; ?>" readonly <?php } ?>>
            <?php
            if ($azione == "new") {
                echo '<p style="color:red; font-size: 12px;" class="ff-italic">Attenzione, una volta inserito il codice esame non potrà essere cambiato</p>';
            }
            ?>

        </div>
    </div>
    <div class="form-group">
        <label class="form-label" for="nome">Nome Esame</label>
        <div class="form-control-wrap">
            <input type="text" class="form-control" id="nome" name="nome" required <?php if ($azione == "update") { ?>
                    value="<?php echo $esame['nome']; ?>" <?php } ?>>
        </div>
    </div>
    <div class="form-group">
        <label class="form-label" for="cfu">CFU</label>
        <div class="form-control-wrap">
            <input type="number" class="form-control" id="cfu" name="cfu" required <?php if ($azione == "update") { ?>
                    value="<?php echo $esame['cfu']; ?>" <?php } ?>>
        </div>
    </div>
    <div class="form-group">
        <label class="form-label" for="anno">Anno</label>
        <div class="form-control-wrap">
            <select class="form-select js-select2" id="anno" name="anno" data-search="on" required>
                <?php if ($azione == 'update') { ?>
                    <option value="<?php echo $esame['anno']; ?>">
                        <?php if ($esame['anno'] == 's') {
                            echo 'A scelta';
                        } else {
                            echo $esame['anno'] . '° Anno di Corso';
                        } ?>
                    </option>
                    <option value="1">1° Anno di Corso</option>
                    <option value="2">2° Anno di Corso</option>
                    <option value="3">3° Anno di Corso</option>
                    <option value="4">4° Anno di Corso</option>
                    <option value="5">5° Anno di Corso</option>
                    <option value="s">A scelta</option>
                <?php } else { ?>
                    <option value="" disabled selected>Seleziona il tipo di corso</option>
                    <option value="1">1° Anno di Corso</option>
                    <option value="2">2° Anno di Corso</option>
                    <option value="3">3° Anno di Corso</option>
                    <option value="4">4° Anno di Corso</option>
                    <option value="5">5° Anno di Corso</option>
                    <option value="s">A scelta</option>
                <?php } ?>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label class="form-label">Seleziona Paniere</label>
        <div class="form-control-wrap">
            <div class="form-file">
                <input type="file" accept=".pdf" name="filePaniere" id="filePaniere" class="form-file-input">
                <label class="form-file-label" for="filePaniere">Scegli File</label>
            </div>
        </div>
        <?php if (isset($esame['paniere']) && !empty($esame['paniere'])) { ?>
            <a href="<?php echo $esame['paniere'] ?>" target="_blank">Visualizza Paniere</a>
        <?php } ?>
        <input type="hidden" name="action" value="newfile_esame">
    </div>

    <?php if (isset($_POST['id']) && !empty($_POST['id'])) { ?>
        <input type="hidden" name="id" id="id" value="<?php echo $_POST['id'] ?>">
    <?php } ?>
    <input type="hidden" name="action" id="action" value="<?php echo $azione ?>">
    <div class="form-group">
        <br>
        <input type="submit" class="btn btn-lg btn-primary" value="<?php if ($azione == "update") {
            echo "Aggiorna Esame";
        } else {
            echo "Inserisci Esame";
        } ?>">
    </div>
</form>