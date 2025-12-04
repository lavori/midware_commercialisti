<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('../../config.php');
require_once('../../db.php');

$database = new Database($host, $username, $password, $db);
$database->connect();

$nmax = $_POST['nmax'];

// Ricevi i valori delle rate e delle date esistenti
$existingProdotti = isset($_POST['existingProdotti']) ? $_POST['existingProdotti'] : [];

?>

<?php for ($i = 0; $i < $nmax; $i++) {
    $currentProdotti = isset($existingProdotti[$i]) ? htmlspecialchars($existingProdotti[$i]) : '';
    ?>
    <div class="row g-3 align-center">
        <div class="col-lg-7">
            <div class="form-control-wrap">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="basic-addon1">€</span>
                    </div>
                    <input type="number" class="form-control" name="Prodotti[]" id="importoRata_<?php echo $i; ?>"
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