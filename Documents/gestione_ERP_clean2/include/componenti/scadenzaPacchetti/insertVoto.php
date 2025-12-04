<?php
$id = $_POST['id'];
$arrayDati = explode("|", $id);
$id = $arrayDati[0];
$pacchetto = $arrayDati[1] ?? "";
?>
<div class="card-inner">
    <form method="POST">
        <div class="form-group">
            <label class="form-label" for="voto">Inserisci Voto</label>
            <div class="form-control-wrap">
                <input type="number" max="30" min="18" class="form-control" name="voto">
            </div>
        </div>
        <div class="form-group">
            <input type="hidden" name="id_esame" value="<?php echo $id; ?>">
            <input type="hidden" name="pacchetto" value="<?php echo $pacchetto; ?>">
            <button type="submit" class="btn btn-primary">Inserisci</button>
        </div>
    </form>
</div>