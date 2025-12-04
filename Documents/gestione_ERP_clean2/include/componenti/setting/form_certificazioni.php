<?php
include('../../config.php');
require_once('../../db.php');

// Inizializzazione del database una sola volta
$database = new Database($host, $username, $password, $db);
$database->connect();


if (isset($_POST['id']) && $_POST['id'] != '') {
    $azione = 'update';
    $id = $_POST['id'];
    $certificazioni = $database->select("an_certificazioni", "*", "id= " . $id);
} else {
    $azione = 'new';
}

$allEnti = $database->select("an_enti", "*");
$database->disconnect();
?>

<form id="form_certificazioni" class="form-validate is-alter" method="POST">
    <div class="row gy-4">
        <div class="col-sm-12">
            <div class="form-group">
                <label class="form-label" for="nome">Nome Certificazione</label>
                <div class="form-control-wrap">
                    <input type="text" class="form-control" id="nome" name="nome" required <?php if ($azione == 'update') { ?>
                            value="<?php echo $certificazioni[0]['nome']; ?>" <?php } ?>>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <?php if ($azione == 'update'): ?>
                <input type="hidden" id="id" name="id" value="<?php echo $id; ?>">
            <?php endif; ?>
            <div class="form-group">
                <label class="form-label" for="id_ente">Ente</label>
                <div class="form-control-wrap">
                    <select class="form-select js-select2" data-search="on" id="id_ente" name="id_ente" required>
                        <?php if ($azione !== 'update'): ?>
                            <option value="" disabled selected>Seleziona il tipo di corso</option>
                        <?php endif; ?>

                        <?php foreach ($allEnti as $v): ?>
                            <option value="<?php echo htmlspecialchars($v['id']); ?>" <?php
                            if (isset($certificazioni[0]['id_ente']) && $certificazioni[0]['id_ente'] == $v['id']) {
                                echo 'selected';
                            }
                            ?>>
                                <?php echo htmlspecialchars($v['nome'])?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label class="form-label" for="tipoPagamento">Pagamento</label>
                <div class="form-control-wrap">
                    <select class="form-select js-select2" data-search="on" id="tipoPagamento" name="tipoPagamento" onchange="aggiornaLabelCosto()">
                        <?php if ($azione !== 'update'): ?>
                            <option value="" disabled selected>Seleziona il tipo di pagamento</option>
                        <?php endif; ?>
                        <option value="Diretto"
                            <?php if(isset($certificazioni[0]['tipoPagamento']) && $certificazioni[0]['tipoPagamento'] == 'Diretto'){ ?>selected<?php }?>>Diretto (Costo Fisso)</option>
                        <option value="Indiretto"
                            <?php if(isset($certificazioni[0]['tipoPagamento']) && $certificazioni[0]['tipoPagamento'] == 'Indiretto'){ ?>selected<?php } ?>>Indiretto (Costo Variabile)</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label class="form-label" for="<?php echo ($azione == 'update' && isset($certificazioni[0]['tipoPagamento']) && $certificazioni[0]['tipoPagamento'] == 'Indiretto') ? 'percentuale' : 'costo'; ?>">
                    <?php
                        if ($azione == 'update' && isset($certificazioni[0]['tipoPagamento'])) {
                            echo $certificazioni[0]['tipoPagamento'] == 'Indiretto' ? 'Percentuale (Costo Variabile, Es: 15)' : 'Costo (Costo Fisso, Es: 180.00)';
                        } else {
                            echo 'Costo';
                        }
                    ?>
                </label>
                <div class="form-control-wrap">
                    <div class="input-group">
                        <input type="number" class="form-control" 
                         id="<?php echo ($azione == 'update' && isset($certificazioni[0]['tipoPagamento']) && $certificazioni[0]['tipoPagamento'] == 'Indiretto') ? 'percentuale' : 'costo'; ?>"
                            name="<?php echo ($azione == 'update' && isset($certificazioni[0]['tipoPagamento']) && $certificazioni[0]['tipoPagamento'] == 'Indiretto') ? 'percentuale' : 'costo'; ?>"
                            required <?php if ($azione == 'update') { ?>
                                value="<?php if ($certificazioni[0]['tipoPagamento'] == 'Diretto') {
                                    echo number_format((float) str_replace(',', '.', $certificazioni[0]['costo']), 2, '.', '');
                                } else {
                                    echo $certificazioni[0]['percentuale'];
                                } ?>"
                            <?php } ?>>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label class="form-label" for="svolgimento">Svolgimento</label>
                <div class="form-control-wrap">
                    <select class="form-select js-select2" data-search="on" id="svolgimento" name="svolgimento">
                        <?php if ($azione !== 'update'): ?>
                            <option value="" disabled selected>Seleziona il tipo di svolgimento</option>
                        <?php endif; ?>
                        <option value="Presenza"
                            <?php if(isset($certificazioni[0]['svolgimento']) && $certificazioni[0]['svolgimento'] == 'Presenza'){ ?>selected<?php }?>>Presenza</option>
                        <option value="Da Remoto"
                            <?php if(isset($certificazioni[0]['svolgimento']) && $certificazioni[0]['svolgimento'] == 'Da Remoto'){ ?>selected<?php } ?>>Da Remoto</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label class="form-label" for="validita">Validità</label>
                <div class="form-control-wrap">
                    <input type="number" class="form-control" id="validita" name="validita" required <?php if ($azione == 'update') { ?>
                            value="<?php echo $certificazioni[0]['validita']; ?>" <?php } ?>>
                </div>
            </div>
        </div>
         <div class="col-sm-6">
            <div class="form-group">
                <label class="form-label" for="tipo">Tipo</label>
                <div class="form-control-wrap">
                    <select class="form-select js-select2" data-search="on" id="tipo" name="tipo">
                        <?php if ($azione !== 'update'): ?>
                            <option value="" disabled selected>Seleziona il tipo</option>
                        <?php endif; ?>
                        <option value="Corso"
                            <?php if(isset($certificazioni[0]['tipo']) && $certificazioni[0]['tipo'] == 'Corso'){ ?>selected<?php }?>>Corso</option>
                        <option value="Corso + Esame"
                            <?php if(isset($certificazioni[0]['tipo']) && $certificazioni[0]['tipo'] == 'Corso + Esame'){ ?>selected<?php } ?>>Corso + Esame</option>
                        <option value="Esame"
                            <?php if(isset($certificazioni[0]['tipo']) && $certificazioni[0]['tipo'] == 'Esame'){ ?>selected<?php } ?>>Esame</option>
                    </select>
                </div>
            </div>
        </div>
        <div><hr></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label class="form-label" for="piattaforma">Piattaforma</label>
                <div class="form-control-wrap">
                    <input type="text" class="form-control" id="piattaforma" name="piattaforma" <?php if ($azione == 'update') { ?>
                            value="<?php echo $certificazioni[0]['piattaforma']; ?>" <?php } ?>>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label class="form-label" for="url_piattaforma">URL Piattaforma</label>
                <div class="form-control-wrap">
                    <input type="text" class="form-control" id="url_piattaforma" name="url_piattaforma" <?php if ($azione == 'update') { ?>
                            value="<?php echo $certificazioni[0]['url_piattaforma']; ?>" <?php } ?>>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label class="form-label" for="username_piattaforma">Username Piattaforma</label>
                <div class="form-control-wrap">
                    <input type="text" class="form-control" id="username_piattaforma" name="username_piattaforma" <?php if ($azione == 'update') { ?>
                            value="<?php echo $certificazioni[0]['username_piattaforma']; ?>" <?php } ?>>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label class="form-label" for="pwd_piattaforma">Password Piattaforma</label>
                <div class="form-control-wrap">
                    <input type="text" class="form-control" id="pwd_piattaforma" name="pwd_piattaforma" <?php if ($azione == 'update') { ?>
                            value="<?php echo $certificazioni[0]['pwd_piattaforma']; ?>" <?php } ?>>
                </div>
            </div>
        </div>
    </div>
    <br>
    <div class="form-group center">
        <input type="hidden" name="id" value="<?php echo $_POST['id'];?>">
        <input type="hidden" id="action" name="action" value="<?php echo $azione; ?>">
        <button type="button" class="btn btn-lg btn-primary raccoltadati" onclick="submitCertificazione()">
            <?php echo ($azione === 'new') ? 'Inserisci' : 'Aggiorna'; ?>
        </button>
    </div>
</form>
