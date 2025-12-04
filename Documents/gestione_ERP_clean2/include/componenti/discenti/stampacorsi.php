<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('../../config.php');
require_once('../../db.php');

$database = new Database($host, $username, $password, $db);
$database->connect();

$corso_id = isset($_POST['id']) ? $_POST['id'] : 0;

if ($corso_id > 0) {
    // Recupera il nome del percorso
    $percorsoQuery = "SELECT percorso FROM an_percorsi WHERE id = " . $corso_id;
    $percorsoResult = $database->query($percorsoQuery);

    if (!empty($percorsoResult)) {
        $percorso = $percorsoResult[0]['percorso'];
        if (isset($_POST['esami'])) {
            $esamiCarriera = json_decode($_POST['esami']);
        }
        // Recupera gli esami associati al percorso
        $esamiQuery = "SELECT id, nome, cfu, anno FROM an_esami WHERE pid = $corso_id";
        $esami = $database->query($esamiQuery);

        if (!empty($esami)) {
            ?>
            <br>
            <h5>Ecco gli esami del percorso "<?php echo htmlspecialchars($percorso); ?>"</h5>
            <br>
            <div class="card card-bordered card-preview">
                <div class="card-inner">
                    <table class="datatable-init-export nowrap table" data-export-title="Export">
                        <thead>
                            <tr>
                                <th>Seleziona</th>
                                <th>Nome</th>
                                <th>Anno</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($esami as $esame) {
                                $esameId = $esame['id'];

                                if (isset($esamiCarriera)) {
                                    $checked = (is_array($esamiCarriera) && in_array($esameId, $esamiCarriera)) ? 'checked' : '';
                                }
                                ?>
                                <tr>
                                    <td>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" name="esami[]"
                                                id="check-<?php echo $esameId; ?>" value="<?php echo $esameId; ?>" <?php if (isset($esamiCarriera)) {
                                                          echo $checked;
                                                      } ?>>
                                            <label class="custom-control-label" for="check-<?php echo $esameId; ?>"></label>
                                        </div>
                                    </td>

                                    <td><?php echo $esame['nome']; ?></td>
                                    <td>
                                        <?php
                                        if ($esame['anno'] == 's') {
                                            echo "A Scelta";
                                        } else {
                                            echo $esame['anno'] . "° Anno";
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php } ?>
                            <input type="hidden" name="esamiEsistenti"
                                value='<?= isset($_POST['esami']) ? $_POST['esami'] : ""; ?>'>
                        </tbody>
                    </table>
                </div>

            </div>
            <br>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" name="tutoraggio" id="tutoraggio" value="si"
                    <?= isset($_POST['tutoraggio']) && $_POST['tutoraggio'] === 'si' ? 'checked' : '' ?>>
                <label class="custom-control-label" for="tutoraggio">Tutoraggio</label>
            </div>

            <br><br>
            <?php
        } else {
            echo "<strong>Nessun Risultato Trovato</strong>";
        }
    } else {
        echo "<strong>Percorso non trovato.</strong>";
    }
} else {
    echo "ID corso non valido.";
}

$database->disconnect();
?>