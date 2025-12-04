

    <?php
        $array_id = explode('|',$_POST['id']);
        $url='/settings/esami_master/'.$array_id[0];
        if (isset($array_id[1]) && $array_id[1] == "new") {
            $azione = "new";
        } else {
            $azione = "update";
            $id_esame = $array_id[1];
            include('../../config.php');
            require_once('../../db.php');

            // Inizializzazione del database una sola volta
            $database = new Database($host, $username, $password, $db);
            $database->connect();
            $esami = $database->select("an_esami", "*", ["id" => $id_esame]);
            $database->disconnect();
            $esame = $esami[0];
        }
    ?>
    
<form method="POST" action="<?php echo $url;?>" class="form-validate is-alter">
    <div class="form-group">
        <label class="form-label" for="codice">Codice Esame</label>
        <div class="form-control-wrap">
            <input type="text" class="form-control" id="codice" name="codice" required <?php if ($azione == "update") { ?>
                    value="<?php echo $esame['codice']; ?>" readonly <?php } ?>>
            <?php
            if ($azione == "new") {
                echo '<p style="color:red; font-size: 12px;" class="ff-italic">Attenzione, una volta inserito il codice esame non potrà essere cambiato</p>';
            }
            ?>
        </div>
    </div>
    <div class="form-group">
        <label class="form-label" for="nome">Nome Esame</label>
        <div class="form-control-wrap">
            <input type="text" class="form-control" id="nome" name="nome" required <?php if ($azione == "update") { ?>
                    value="<?php echo $esame['nome']; ?>" <?php } ?>>
        </div>
    </div>
    <div class="form-group">
        <label class="form-label" for="cfu">CFU</label>
        <div class="form-control-wrap">
            <input type="number" class="form-control" id="cfu" name="cfu" required <?php if ($azione == "update") { ?>
                    value="<?php echo $esame['cfu']; ?>" <?php } ?>>
        </div>
    </div>

    <?php if (isset($azione) && $azione=="update") { ?>
        <input type="hidden" name="id" id="id" value="<?php echo $id_esame ?>">
    <?php } ?>
    <input type="hidden" name="id_master" id="id_master" value="<?php echo $array_id[0] ?>">
    <input type="hidden" name="action" id="action" value="<?php echo $azione ?>">
    <div class="form-group">
        <br>
        <input type="submit" class="btn btn-lg btn-primary" value="<?php if ($azione == "update") {
            echo "Aggiorna Esame";
        } else {
            echo "Inserisci Esame";
        } ?>">
    </div>
</form>