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
    $pacchettoEsami=$database->select("an_pacchettiEsame","*","id= ".$id)[0];
    if(!empty($pacchettoEsami)){
        $arrayIdEsami=json_decode($pacchettoEsami['id_esami']);
        $nomeEsami= [];
        foreach($arrayIdEsami as $a){
            $nome=$database->select("an_esami","nome","id= ".$a);
            $nomeEsami[]=$nome[0]['nome'];
        }
    }
}

$database->disconnect();
?>


<div>
    <?php
        foreach($nomeEsami as $s){ 
            echo '-<b>'.$s.'</b><br>';
        } 
    ?>
</div>