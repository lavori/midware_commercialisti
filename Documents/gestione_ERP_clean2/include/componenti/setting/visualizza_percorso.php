<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('../../config.php');
require_once('../../db.php');

// Inizializzazione del database una sola volta
$database = new Database($host, $username, $password, $db);
$database->connect();


$id = $_POST['id'];
$query = "SELECT * FROM `an_esami` WHERE pid= " . $id;
$esami = $database->query($query);
//print_r($esami);

$database->disconnect();

?>

<div style="text-align: center;">
    <form id="downloadPercorso" method="POST">
        <input type="hidden" name="PercorsoEsame" value="<?php echo $id; ?>">
        <button type="submit" class="btn btn-primary">Download Percorso</button>
    </form>
</div>
<br>

<div class="nk-block nk-block-lg">
    <div class="card card-bordered card-preview">
        <div class="card-inner">
            <table class="datatable-init table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>CFU</th>
                        <th>Anno</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($esami as $v) { ?>
                            <tr>
                                <td><?php echo $v['nome']; ?></td>
                                <td><?php echo $v['cfu']; ?></td>
                                <td>
                                    <?php if ($v['anno'] == 's') { ?>
                                        A scelta
                                    <?php } else {
                                        echo $v['anno'] . "° Anno";
                                    } ?>
                                </td>
                            </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>