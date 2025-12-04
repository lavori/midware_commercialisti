<?php
include('../../config.php');
require_once('../../db.php');

// Inizializzazione del database una sola volta
$database = new Database($host, $username, $password, $db);
$database->connect();


if (isset($_POST['id']) && $_POST['id'] != '') {

    $azione = 'update';
    $id = $_POST['id'];

    $query = "SELECT * FROM `rules` WHERE `id` = $id";
    $rules = $database->query($query);
    $rules = $rules[0];

    $query = "SELECT id, permesso FROM permessi WHERE 1";
    $permessi = $database->query($query);
    
    $id_selezionati = [];
    if ($azione === 'update' && !empty($rules['id_permesso'])) {
        $id_selezionati = explode(',', $rules['id_permesso']);
    }

} else {
    $query = "SELECT id, permesso FROM `permessi` WHERE 1";
    $permessi = $database->query($query);
    $azione = 'new';
}


$database->disconnect();
?>

<form id="form_ruolo" class="form-validate is-alter" method="POST" action="/ruoli">
    <?php if ($azione == 'update'): ?>
        <input type="hidden" id="id" name="id" value="<?php echo $id; ?>">
    <?php endif; ?>
    <div class="form-group">
        <label class="form-label" for="ruolo">Ruolo</label>
        <div class="form-control-wrap">
            <input type="text" class="form-control" id="ruolo" name="ruolo" required <?php if ($azione == 'update') { ?>
                    value="<?php echo $rules['ruolo']; ?>" <?php } ?>>
        </div>
    </div>
    <div class="form-group">
        <label class="form-label">Permesso</label>
        <div class="form-control-wrap">
            <select class="form-select js-select2" id="permessi" name="permessi[]" multiple required>
                <?php foreach ($permessi as $v) { ?>
                    <option value="<?php echo htmlspecialchars($v['id']); ?>" <?php echo ($azione === 'update' && in_array($v['id'], $id_selezionati)) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($v['permesso']); ?>
                    </option>
                <?php } ?>
            </select>
        </div>
    </div>



    <div class="form-group">
        <input type="hidden" id="action" name="action" value="<?php echo $azione; ?>">
        <input type="submit" class="btn btn-lg btn-primary" value="Salva informazioni">
    </div>
</form>