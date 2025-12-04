<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('../../config.php');
require_once('../../db.php');

// Inizializzazione del database una sola volta
$database = new Database($host, $username, $password, $db);
$database->connect();

if (isset($_POST['id']) && $_POST['id'] != '') {

    $query = "
    SELECT 
        esami.nome
    FROM 
        an_esami AS esami
    INNER JOIN 
        esami_tutor AS et ON esami.id = et.eid
    WHERE 
        et.tid = " . $_POST['id'];
    $nome = $database->query($query);

}
$database->disconnect();
?>

<div class="col-sm-12">
    <div class="form-group">
        <label class="form-label" for="default-textarea">Esami:</label>
        <div class="form-control-wrap">
            <ul>
                <li>
                    <?php foreach ($nome as $n) { ?>
                        <?php echo '- '.$n['nome'].'<br>'; ?>
                    <?php } ?>
                </li>
            </ul>
        </div>
    </div>
</div>