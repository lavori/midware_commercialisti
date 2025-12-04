<?php
include('../../config.php');
require_once('../../db.php');

// Inizializzazione del database una sola volta
$database = new Database($host, $username, $password, $db);
$database->connect();


if (isset($_POST['id']) && $_POST['id'] != '') {

$azione = 'update';
$id = $_POST['id'];

// Query ottimizzata con JOIN
$query = "SELECT 
                f.*, 
                a.id AS ente_id, 
                a.nome AS ente_nome
            FROM 
                an_facolta f
            LEFT JOIN 
                an_enti a ON f.eid = a.id
            WHERE 
                f.id = $id";

$result = $database->query($query);

// Estrai corso e ente in un colpo solo
$corso = $result[0]; // contiene sia info della facoltà che dell'ente

} else {
    $azione = 'new';
}

$allEnti = $database->select("an_enti", "*");

$database->disconnect();
?>

<form id="form_facolta" class="form-validate is-alter" method="POST" action="/settings/facolta">
    <?php if ($azione == 'update'): ?>
        <input type="hidden" id="id" name="id" value="<?php echo $id; ?>">
    <?php endif; ?>
        <div class="form-group">
        <label class="form-label" for="eid">Ente</label>
        <div class="form-control-wrap">
            <select class="form-select js-select2" data-search="on" id="eid" name="eid" required>
                <?php if ($azione !== 'update'): ?>
                    <option value="" disabled selected>Seleziona il tipo di corso</option>
                <?php endif; ?>

                <?php foreach ($allEnti as $v): ?>
                    <option value="<?php echo htmlspecialchars($v['id']); ?>"
                        <?php
                        if (isset($corso['eid']) && $corso['eid'] == $v['id']) {
                            echo 'selected';
                        }
                        ?>>
                        <?php echo htmlspecialchars($v['nome']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label class="form-label" for="facolta">Nome Facoltà</label>
        <div class="form-control-wrap">
            <input type="text" class="form-control" id="facolta" name="facolta" required <?php if ($azione == 'update') { ?>
                    value="<?php echo $corso['facolta']; ?>" <?php } ?>>
        </div>
    </div>
    <div class="form-group">
        <input type="hidden" id="action" name="action" value="<?php echo $azione; ?>">
        <input type="submit"
            class="btn btn-lg btn-primary raccoltadati"
            value="Salva informazioni">
    </div>
</form>