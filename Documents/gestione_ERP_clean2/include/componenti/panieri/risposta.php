<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('../../config.php');
require_once('../../db.php');

// Inizializzazione del database una sola volta
$database = new Database($host, $username, $password, $db);
$database->connect();


$id = $_POST['id'];
$query = "SELECT * FROM `panieri` WHERE id= " . $id;
$esami = $database->query($query);
//print_r($esami);

$database->disconnect();

?>


<div class="nk-block nk-block-lg">
    <div class="card card-bordered card-preview">
        <div class="card-inner">
            <h3 class="card-title"><?php echo $esami[0]['domanda']; ?></h3>
            <?php if ($esami[0]['ra'] != "" && $esami[0]['ra'] != "NULL"): ?>
                <h4 class="card-text">Risposta Corretta: <?php  echo $esami[0]['ra']; ?></h4>
            <?php else: ?>
                <p class="card-text"><?php echo $esami[0]['r1']; ?></p>
                <p class="card-text"><?php echo $esami[0]['r2']; ?></p>
                <p class="card-text"><?php echo $esami[0]['r3']; ?></p>
                <p class="card-text"><?php echo $esami[0]['r4']; ?></p>
                <h4 class="card-text">Risposta Corretta: <?php  $campo="r".$esami[0]['rc']; echo $esami[0][$campo]; ?></h4>
            <?php endif; ?>
        </div>
    </div>
</div>