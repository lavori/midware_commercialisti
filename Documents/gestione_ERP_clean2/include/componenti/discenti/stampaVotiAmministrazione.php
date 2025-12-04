<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include('../../config.php');
require_once('../../db.php');

$database = new Database($host, $username, $password, $db);
$database->connect();

$idDiscente = isset($_POST['id']) ? $_POST['id'] : 0;

if ($idDiscente > 0) {
    //Recupera il nome del discente
    $query = "SELECT nome, cognome FROM an_discenti WHERE id = " . $idDiscente;
    $discente = $database->query($query);

    // Recupera il nome dell'esame
    $query = "SELECT esame_json, fileVoti FROM discente_esami WHERE did = " . $idDiscente;
    $esamejson = $database->query($query);

    if (!empty($esamejson) && $esamejson[0]['fileVoti'] == NULL) {
        $_SESSION['mostraCaricaFile'] = 'si';
        $esami = json_decode($esamejson[0]['esame_json'], true);
        // Recupera gli esami associati al percorso
        foreach ($esami as $s) {
            $esamiQuery = "SELECT id, codice,nome, cfu, anno FROM an_esami WHERE id = " . $s;
            $esami = $database->query($esamiQuery);
            if (!empty($esami)) {
                $votoquery = "SELECT voto FROM votiDiscenteEsami WHERE id_esame = " . $s;
                $voto = $database->query($votoquery);
                if (!empty($voto)) {
                    $esami[0]['voto'] = $voto[0]['voto'];
                }
            }
            $totaleEsami[] = $esami[0];
        }


        if (!empty($totaleEsami)) {
            ?>
            <br>
            <h5>Ecco gli esami del discente <?php echo $discente[0]['nome'] . ' ' . $discente[0]['cognome']; ?></h5>
            <br>
            <div class="card card-bordered card-preview">
                <div class="card-inner">
                    <table class="datatable-init nk-tb-list nk-tb-ulist" data-auto-responsive="true">
                        <thead>
                            <tr class="nk-tb-item nk-tb-head">
                                <th class="nk-tb-col tb-col-lg">
                                    <span class="sub-text">Codice Esame</span>
                                </th>
                                <th class="nk-tb-col">
                                    <span class="sub-text">Nome</span>
                                </th>
                                <th class="nk-tb-col tb-col-lg">
                                    <span class="sub-text">CFU</span>
                                </th>
                                <th class="nk-tb-col">
                                    <span class="sub-text">Voto</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($totaleEsami as $e) { ?>
                                <tr class="nk-tb-item">
                                    <td class="nk-tb-col tb-col-lg">
                                        <div class="user-card">
                                            <div class="user-info">
                                                <span class="tb-lead">
                                                    <?php echo $e['codice'] ?>
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="nk-tb-col">
                                        <span class="tb-amount">
                                            <?php echo $e['nome'] ?>
                                        </span>
                                    </td>
                                    <td class="nk-tb-col tb-col-lg">
                                        <span>
                                            <?php echo $e['cfu'] ?>
                                        </span>
                                    </td>
                                    <td class="nk-tb-col">
                                        <form method="POST" class="form-voti"
                                            action="/include/componenti/discenti/insertVotiPrevalutazione.php">
                                            <input type="hidden" name="id_esame" value="<?php echo $e['id']; ?>">
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="hidden" name="id_discente" value="<?php echo $idDiscente; ?>">
                                                <?php if (isset($e['voto']) && !empty($e['voto'])) { ?>
                                                    <input type="hidden" name="id_discente" value="<?php echo $idDiscente; ?>">
                                                    <input type="hidden" name="updateVoto" value="Modifica">
                                                    <input type="hidden" name="id_esame" value="<?php echo $e['id']; ?>">
                                                    <input type="number" name="voto" class="form-control" value="<?php echo $e['voto']; ?>"
                                                        style="max-width: 30%;" max="30">
                                                    <button type="button" class="btn btn-primary"
                                                        onclick="inviaVoti(this)">Modifica</button>
                                                <?php } else { ?>
                                                    <input type="hidden" name="insertVoto" value="Inserisci">
                                                    <input type="hidden" name="id_discente" value="<?php echo $idDiscente; ?>">
                                                    <input type="hidden" name="id_esame" value="<?php echo $e['id']; ?>">
                                                    <input type="number" name="voto" class="form-control" placeholder="Voto" max="30"
                                                        autocomplete="off" style="max-width: 30%;">
                                                    <button type="button" class="btn btn-primary"
                                                        onclick="inviaVoti(this)">Inserisci</button>
                                                <?php } ?>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php
        } else {
            echo "<strong>Nessun Esame Presente</strong>";
        }
    } elseif (!empty($esamejson) && $esamejson[0]['fileVoti'] != NULL) {
        echo "<strong>I voti sono già stati caricati.</strong>";
    } else {
        echo "<strong>Nessun Percorso di Laurea Presente.</strong>";
    }
} else {
    echo "ID non valido.";
}

$database->disconnect();
?>