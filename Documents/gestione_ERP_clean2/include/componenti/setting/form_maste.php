<?php
include('../../config.php');
require_once('../../db.php');

// Inizializzazione del database una sola volta
$database = new Database($host, $username, $password, $db);
$database->connect();


if (isset($_POST['id']) && $_POST['id'] != '') {

    $azione = 'update';
    $id = $_POST['id'];

    $query = "SELECT * FROM `an_percorsi` WHERE `id` = $id";
    $corso = $database->query($query);
    $corso = $corso[0];

    $facolta = "SELECT * FROM `an_facolta`";
    $facolta = $database->query($facolta);

    $facoltaesistente="SELECT * FROM `an_facolta` WHERE `id` = ".$corso['fid'];
    $facoltaesistente = $database->query($facoltaesistente);
    $facoltaesistente = $facoltaesistente[0];

    //print_r($corso);exit();

} else {
    $facolta = "SELECT * FROM `an_facolta`";
    $facolta = $database->query($facolta);
    $azione = 'new';
    //print_r($facolta);exit();
}
$database->disconnect();
?>

<form id="form_corso" class="form-validate is-alter" method="POST" action="/settings/percorsi">
    <?php if ($azione == 'update'): ?>
        <input type="hidden" id="id" name="id" value="<?php echo $id; ?>">
    <?php endif; ?>
    <div class="form-group">
        <label class="form-label" for="facolta">Facoltà</label>
        <div class="form-control-wrap">
           <?php
                // Determina l'ID selezionato
                if (!empty($_POST['facolta'])) {
                    $selectedId = $_POST['facolta'];
                } elseif (!empty($azione) && $azione === 'update') {
                    $selectedId = $corso['fid'] ?? null;
                } else {
                    $selectedId = $corso['facolta'] ?? null;
                }
            ?>
            <select class="form-select js-select2" data-search="on" id="facolta" name="facolta" required>
                <option value="" disabled <?= $selectedId ? '' : 'selected' ?>>Seleziona il tipo di corso</option>
                <?php foreach ($facolta as $f): 
                    $id = htmlspecialchars((string)$f['id'], ENT_QUOTES, 'UTF-8');
                    $label = htmlspecialchars((string)$f['facolta'], ENT_QUOTES, 'UTF-8');
                    $isSelected = ((string)$selectedId === (string)$f['id']) ? ' selected' : '';
                ?>
                    <option value="<?= $id ?>"<?= $isSelected ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label class="form-label" for="percorso">Nome Percorso</label>
        <div class="form-control-wrap">
            <input type="text" class="form-control" id="percorso" name="percorso" required <?php if ($azione == 'update') { ?>
                    value="<?php echo $corso['percorso']; ?>" <?php } ?>>
        </div>
    </div>
    <div class="form-group">
        <input type="hidden" id="action" name="action" value="<?php echo $azione; ?>">
        <input type="submit"
            class="btn btn-lg btn-primary"
            value="Salva informazioni">
    </div>
</form>