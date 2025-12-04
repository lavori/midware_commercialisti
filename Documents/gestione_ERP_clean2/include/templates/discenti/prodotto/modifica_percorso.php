<!-- content @s -->
<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h1 class="page-title"><?php echo $h1; ?></h1>
            <div class="nk-block-des text-soft">
                <h2><?php echo $h2; ?></h2>
            </div>
        </div>
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->
<div class="nk-block  nk-block-lg">
    <div class="row g-gs my-3">
        <div class="col-md-12">
            <div class="card card-bordered card-preview">
                <?php if (isset($message) && $message == 'insert') { ?>
                    <div class="example-alert">
                        <div class="alert alert-success alert-icon">
                            <em class="icon ni ni-check-circle"></em> <strong>Dato inserito</strong> con successo
                        </div>
                    </div>
                <?php } ?>
                <?php if (isset($message) && $message == 'update') { ?>
                    <div class="example-alert">
                        <div class="alert alert-success alert-icon">
                            <em class="icon ni ni-check-circle"></em> <strong>Dato aggiornato</strong> con successo
                        </div>
                    </div>
                <?php } ?>
                <?php if (isset($message) && $message == 'errorCarriera') { ?>
                    <div class="example-alert">
                        <div class="alert alert-danger alert-icon">
                            <em class="icon ni ni-check-circle"></em> <strong>Attenzione</strong> non puoi inserire un percorso in quest'area,
                            <a href="/discenti/gestione/<?php echo $id ?>/assegna_percorso">clicca qui</a>
                        </div>
                    </div>
                <?php } ?>
                <div class="card-inner">
                    <?php if(!empty($NomePercorsi)){ ?>
                        <div>
                            <label class="form-label">Percorsi Di Laurea Presenti:</label>
                            <?php foreach ($NomePercorsi as $c) { ?>
                                <p>- <b><?php echo $c['percorso']?></b></p>
                            <?php } ?>
                            <br>
                        </div>

                        <form method="POST">
                            <div class="form-group" id="percorso" style="display: none;">
                                <label class="form-label">Scegli Percorso di Laurea</label>
                                <div class="form-control-wrap">
                                    <select class="form-select" id="corsoSelect" name="corsoSelect" data-search="on"
                                        required>
                                        <option value="" disabled selected>Seleziona...</option>
                                        <?php foreach ($percorsi as $c) { ?>
                                            <option value="<?php echo $c['id'] ?>"
                                            <?php if(isset($controllo[0]['carriera']['id_percorso']) && $controllo[0]['carriera']['id_percorso'] == $c['id']){ 
                                                echo 'selected';
                                            } ?>>
                                            <?php echo $c['percorso'] ?>
                                        </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <!-- div nascosto inizialmente -->
                            <div id="corsoInfo">
                                <div id="corsoDettagli">

                                </div>
                            </div>
                            <br>
                            <input type="hidden" name="action" value="update">
                            <button type="submit" class="btn btn-primary center">Modifica</button>
                        </form>
                    <?php }else{ ?>
                        <div class="example-alert">
                            <div class="alert alert-warning alert-icon">
                                <em class="icon ni ni-alert-circle"></em> <strong>Non sono presenti percorsi di laurea</strong>. 
                                <a href="/discenti/gestione/<?php echo $id ?>/assegna_percorso" class="alert-link">Registrali qui</a>
                            </div>
                        </div>
                    <?php } ?> 
                </div>
            </div>
        </div><!-- .card-preview -->
    </div>
</div>
<!-- Form unico nella pagina -->
<form id="mainForm" action="<?php echo $serp; ?>" method="POST">
    <!-- Altri campi visibili opzionali -->
</form>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const selectCorso     = document.getElementById("corsoSelect");
    const divPercorso     = document.getElementById("percorso");
    const divCorsoInfo    = document.getElementById("corsoInfo");
    const divCorsoDettagli= document.getElementById("corsoDettagli");

    // 1) esporta qui tutoraggio da PHP in JS
    const tutoraggio      = <?php echo json_encode($controllo[0]['tutoraggio'] ?? ''); ?>;
    const carriera        = <?php echo json_encode($controllo[0]['carriera'] ?? []); ?>;
    const esamiCarriera   = <?php echo !empty($AllEsami) ? $AllEsami : '[]'; ?>;

    function mostraCorsiLaurea() {
      divPercorso.style.display = 'block';
    }

    function getEsamiSelezionatiAttuali() {
      const checked = document.querySelectorAll('input[name="esami[]"]:checked');
      return Array.from(checked).map(cb => cb.value.toString());
    }

    function caricaEsami(corsoId) {
      if (!corsoId) return;

      divCorsoInfo.style.display = 'block';
      divCorsoDettagli.innerHTML = 'Caricamento...';

      const formData = new URLSearchParams();
      formData.append('id', corsoId);
      formData.append('esami', JSON.stringify(esamiCarriera));

      // 2) aggiungi tutoraggio al payload
      formData.append('tutoraggio', tutoraggio);

      fetch('./include/componenti/discenti/stampacorsi.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData
      })
      .then(response => response.text())
      .then(html => {
        divCorsoDettagli.innerHTML = html;
      })
      .catch(error => {
        divCorsoDettagli.innerHTML = 'Errore nel caricamento: ' + error;
      });
    }

    // init select2 o change nativo
    if (typeof $ !== 'undefined' && $.fn.select2) {
      $(selectCorso).select2().on('select2:select', function (e) {
        caricaEsami(e.params.data.id);
      });
    } else {
      selectCorso.addEventListener('change', function () {
        caricaEsami(this.value);
      });
    }

    mostraCorsiLaurea();

    // se esiste già un percorso scelto, pre-selezionalo e ricarica
    if (carriera && carriera.id_percorso && esamiCarriera.length > 0) {
      const opt = [...selectCorso.options].find(o => o.value == carriera.id_percorso);
      if (opt) opt.selected = true;
      caricaEsami(carriera.id_percorso);
    }
  });
</script>

