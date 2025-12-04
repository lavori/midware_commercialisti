<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('../../config.php');
require_once('../../db.php');

$database = new Database($host, $username, $password, $db);
$database->connect();

$nmax = $_POST['nmax'];


if (isset($_POST['idAcconto']) && !empty($_POST['idAcconto'])) {

    $idAcconto = $_POST['idAcconto'];
    $where = "id_rateScadenze = " . $idAcconto;
    $accordo = $database->select("scadenzeRate", "id, importoRata, data_scadenza", $where);
    $existingImportoRate = [];
    $existingDataRate = [];

    foreach ($accordo as $ac) {
        $existingImportoRate[] = $ac['importoRata'];
        $existingDataRate[] = $ac['data_scadenza'];
    }

}
?>

<?php for ($i = 0; $i < $nmax; $i++) {
    $currentImportoRata = isset($existingImportoRate[$i]) ? htmlspecialchars($existingImportoRate[$i]) : '';
    $currentDataRata = isset($existingDataRate[$i]) ? htmlspecialchars($existingDataRate[$i]) : '';
    ?>
    <div class="row g-3 align-center">
        <div class="col-lg-5">
            <div class="form-group">
                <div class="form-control-wrap">
                    <input type="date" class="form-control date-picker" name="dataImporto[]"
                        id="dataImporto_<?php echo $i; ?>" value="<?php echo $currentDataRata; ?>" required>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="form-control-wrap">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="basic-addon1">€</span>
                    </div>
                    <input type="number" class="form-control" name="importoRata[]" id="importoRata_<?php echo $i; ?>"
                        placeholder="Importo rata" step="0.01" value="<?php echo $currentImportoRata; ?>" required>
                </div>
            </div>
        </div>
    </div>
    <br>
<?php } ?>

<?php
$database->disconnect();
?>