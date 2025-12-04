<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('../../config.php');
require_once('../../db.php');

header('Content-Type: application/json; charset=utf-8');

$database = new Database($host, $username, $password, $db);
$database->connect();

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$existingIds = isset($_GET['ids']) ? $_GET['ids'] : [];

if (!is_array($existingIds)) {
    $existingIds = json_decode($existingIds, true) ?: [];
}

$items = [];

// --- CASO 1: ricerca testuale
if ($q !== '') {
    $qEscaped = $database->escapeString($q);
    $query = "SELECT id, nome FROM an_esami WHERE nome LIKE '%$qEscaped%' ORDER BY nome LIMIT 30";
}

// --- CASO 2: preload di ID già esistenti
elseif (!empty($existingIds)) {
    $idsString = implode(',', array_map('intval', $existingIds));
    $query = "SELECT id, nome FROM an_esami WHERE id IN ($idsString) ORDER BY FIELD(id, $idsString)";
}

// --- CASO 3: nessun input → non caricare nulla
else {
    $query = "SELECT id, nome FROM an_esami ORDER BY nome LIMIT 0";
}

$results = $database->query($query);
foreach ($results as $row) {
    $items[] = [
        'id' => $row['id'],
        'text' => $row['nome']
    ];
}

echo json_encode(['items' => $items]);
$database->disconnect();
?>
