<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('../../config.php');
require_once('../../db.php');

$database = new Database($host, $username, $password, $db);
$database->connect();

$tabella = $_POST['tabella'];

$query = "SELECT * FROM `$tabella`";
$tipologiaDiscente = $database->query($query);

switch ($tabella){
    case 'an_percorsi':
        $name = "percorso";
        foreach ($tipologiaDiscente as &$t) {
            $t['nome'] = $t['percorso'];
        }
        break;

    case 'an_esami':
        $name = "esame";
        break;

    case 'an_master':
        $name = "master";
        break;

    case 'an_certificazioni':
        $name = "certificazione";
        break;
}

?>
<div class="card card-bordered card-preview">
    <div class="card-inner">
        <table class="datatable-init-export nowrap table" data-export-title="Export">
            <thead>
                <tr>
                    <th>Seleziona</th>
                    <th>Nome</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tipologiaDiscente as $esame) { ?>
                    <tr>
                        <td>
                            <div class="custom-control custom-radio">
                                <input type="radio" class="custom-control-input" name="<?php echo $name; ?>"
                                    id="check-<?php echo $esame['id']; ?>" value="<?php echo $esame['id']; ?>">
                                <label class="custom-control-label" for="check-<?php echo $esame['id']; ?>"></label>
                            </div>
                        </td>
                        <td><?php echo $esame['nome']; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<br>
<?php
$database->disconnect();
?>