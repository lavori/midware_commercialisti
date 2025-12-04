<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('../../config.php');
require_once('../../db.php');

$database = new Database($host, $username, $password, $db);
$database->connect();

$nmax = isset($_POST['nmax']) ? (int)$_POST['nmax'] : 0;
$esamiPresenti = isset($_POST['esamiPresenti']) ? $_POST['esamiPresenti'] : [];

for ($i = 0; $i < $nmax; $i++) {
    $idSelezionato = isset($esamiPresenti[$i]) ? $esamiPresenti[$i] : '';
    $nomeSelezionato = isset($esamiSelezionati[$idSelezionato]) ? $esamiSelezionati[$idSelezionato] : '';

    echo '<div class="col-lg-12" style="margin-top:2px;">';
    echo '<div class="form-control-wrap"><div class="input-group">';
    echo '<select name="esamiSelezionati[]" class="form-select js-select2" data-searchon required>';

    // Se non c'è selezione, mostra placeholder
    if ($idSelezionato === '' || $nomeSelezionato === '') {
        echo '<option value="" disabled selected>Seleziona un esame...</option>';
    } else {
        // Mostra solo l'opzione selezionata (Select2 la manterrà visibile)
        echo '<option value="' . htmlspecialchars($idSelezionato) . '" selected>' . htmlspecialchars($nomeSelezionato) . '</option>';
    }

    echo '</select>';
    echo '</div></div></div><br>';
}

$database->disconnect();
?>
