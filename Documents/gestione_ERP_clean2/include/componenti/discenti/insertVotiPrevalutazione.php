<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('../../config.php');
require_once('../../db.php');

$database = new Database($host, $username, $password, $db);
$database->connect();

$voto = null;

if (isset($_POST['permesso'])) {
    $data = array(
        'did' => $_POST['id_discente'],
        'id_esame' => $_POST['id_esame'],
        'voto' => $_POST['voto']
    );
    $database->insert('votiDiscenteEsami', $data);
    $voto = $_POST['voto'];
} else {
    if (isset($_POST['insertVoto']) && !empty($_POST['insertVoto'])) {
        if (isset($_POST['id_esame']) && !empty($_POST['id_esame'])) {
            $data = array(
                'did' => $_POST['id_discente'],
                'id_esame' => $_POST['id_esame'],
                'voto' => $_POST['voto']
            );
            $database->insert('votiDiscenteEsami', $data);
            $voto = $_POST['voto'];
        }
    } else {
        if (isset($_POST['id_esame']) && !empty($_POST['id_esame'])) {
            $data = array(
                'did' => $_POST['id_discente'],
                'id_esame' => $_POST['id_esame'],
                'voto' => $_POST['voto']
            );
            $where = "id_esame= " . $_POST['id_esame'] . " AND did= " . $_POST['id_discente'];
            $database->update('votiDiscenteEsami', $data, $where);
            $voto = $_POST['voto'];
        }
    }
}

// ✅ Risposta finale pulita
if (!is_null($voto)) {
    echo $voto;
}
exit;


?>