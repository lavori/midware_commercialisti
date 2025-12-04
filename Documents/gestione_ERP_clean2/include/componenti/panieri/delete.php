<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('../../config.php');
require_once('../../db.php');

// Inizializzazione del database una sola volta
$database = new Database($host, $username, $password, $db);
$database->connect();


$id = $_POST['id'];
$table='panieri';
$where = "id=".$id;

/*$query = "DELETE FROM $table WHERE $where";
echo $query; exit();*/
$esami = $database->delete($table, $where);
//print_r($esami); 
 
$database->disconnect();

?>


<div class="nk-block nk-block-lg">
    <div class="card card-bordered card-preview">
        <div class="card-inner">
            <h3 class="card-title">Domanda Cancellata</h3>
        </div>
    </div>
</div>