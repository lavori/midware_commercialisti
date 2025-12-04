<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('../../config.php');
require_once('../../db.php');

$database = new Database($host, $username, $password, $db);
$database->connect();
$master_id = isset($_POST['masterId']) ? $_POST['masterId'] : 0;
if (!empty($master_id)) {
    // Recupera gli esami associati al percorso
    $query = "SELECT nome FROM an_esamiMaster WHERE mid = $master_id";
    $esami = $database->query($query);
    if (!empty($esami)) {
        ?>
        <br>
        <br>
        <div class="card-inner">
            <strong>Esami Presenti:<br></strong>
            <?php
            foreach ($esami as $e) {
                echo '- ' . $e['nome'] . '<br>';
            }
            ?>
        </div>
        <br>
        <?php
    } else {
        echo "<strong>Nessun Risultato Trovato</strong>";
    }
} else {
    echo "<strong>Master non trovato.</strong>";
}

$database->disconnect();
?>