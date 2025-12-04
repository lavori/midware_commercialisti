<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('../../config.php');
require_once('../../db.php');

// Inizializzazione del database una sola volta
$database = new Database($host, $username, $password, $db);
$database->connect();
$query = "SELECT 
            p.id AS master_id, 
            p.nome, 
            COUNT(e.id) AS numero_esami 
          FROM an_master p 
          LEFT JOIN an_esamiMaster e ON e.mid = p.id 
          GROUP BY p.id, p.nome 
          ORDER BY p.nome";
$esami = $database->query($query);

$database->disconnect();

?>
<div class="row g-3">
    <?php
    // Dividi l'elenco a metà
    $metà = ceil(count($esami) / 2);
    $colonne = array_chunk($esami, $metà);
    ?>

    <?php foreach ($colonne as $col) { ?>
        <div class="col-md-6">
            <div class="card card-bordered card-preview h-100">
                <div class="card-inner">
                    <table class="datatable-init table">
                        <thead>
                            <tr>
                                <th>Nome Master</th>
                                <th>Numero Totale</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($col as $v) { ?>
                                <tr>
                                    <td><?php echo $v['nome']; ?></td>
                                    <td><?php echo $v['numero_esami']; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php } ?>
</div>