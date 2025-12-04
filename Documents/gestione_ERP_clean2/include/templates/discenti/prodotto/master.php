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
                <?php if (isset($message) && $message == 'insertProdotto') { ?>
                    <div class="example-alert">
                        <div class="alert alert-success alert-icon">
                            <em class="icon ni ni-check-circle"></em> <strong>Prodotto inserito</strong> con successo
                        </div>
                    </div>
                <?php } ?>
                <?php if (isset($message) && $message == 'DiscentePresente') { ?>
                    <div class="example-alert">
                        <div class="alert alert-warning alert-icon">
                            <em class="icon ni ni-check-circle"></em> <strong>Attenzione! </strong> master non inserito
                            perchè il discente ha già un percorso attivo, per modificare vai a
                            <a style="color: inherit; text-decoration: none !important;"
                                href="discenti/gestione/<?= $id ?>/modifica_percorso">
                                <strong>Modifica Esami</strong>
                            </a>
                        </div>
                    </div>
                <?php } ?>
                <div class="card-inner">
                    <form method="POST">
                        <div class="row g-gs align-items-center">
                            <div class="card-head mb-3">
                            </div>
                            <?php if (!empty($risultatoPerMaster)) { ?>
                                <div id="accordion-2" class="accordion accordion-s3">
                                    <div class="accordion-item">
                                        <a href="#" class="accordion-head" data-bs-toggle="collapse"
                                            data-bs-target="#accordion-item-2-1">
                                            <h6 class="title">Certificazioni Presenti Discente:</h6>
                                            <span class="accordion-icon"></span>
                                        </a>
                                        <div class="accordion-body collapse show" id="accordion-item-2-1"
                                            data-bs-parent="#accordion-2">
                                            <div class="accordion-inner">
                                                <?php foreach ($risultatoPerMaster as $cert): ?>
                                                <?php echo '- <b>' . $cert['master']['nome'] . '</b> (Tutoraggio: ' . (($cert['tutoraggio'] ?? 'no') === 'si' ? 'si' : 'no') . ')<br>'; ?>
                                                <?php endforeach; ?>

                                            </div>
                                        </div>
                                    </div>
                                    <br><br><br>
                                </div>
                            <?php } else { ?>
                                <div>
                                    <br>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="row g-gs align-items-center mb-3">
                            <!-- CHECKBOX: seleziona e tutoraggio -->
                            <div class="col-md-1">
                                <div class="form-group mb-0">
                                    <ul class="custom-control-group g-3 align-center mb-0">

                                        <!-- Tutoraggio -->
                                        <li>
                                            <div
                                                class="custom-control custom-checkbox d-flex align-items-center flex-row-reverse">
                                                <input type="checkbox" class="custom-control-input" id="tutoraggio"
                                                    name="tutoraggio">
                                                <label class="custom-control-label me-2" for="tutoraggio">
                                                    Tutoraggio
                                                </label>
                                            </div>
                                        </li>

                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-11">
                                <div class="form-group mb-0">
                                    <div class="form-control-wrap">
                                        <select class="form-select js-select2" id="masterSelect" name="masterSelect" data-search="on"
                                            required>
                                            <option value=" " disabled selected>Seleziona...</option>
                                            <?php foreach ($tuttiIMaster as $c) { ?>
                                                <option value="<?php echo $c['id'] ?>"
                                                    <?php if(in_array($c['id'],$mid)){echo 'disabled';} ?>
                                                ><?php echo $c['nome'] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="mostraEsami"></div>                      

                        <input type="hidden" name="action" value="insert">
                        <div class="footer">
                            <button type="submit" class="btn btn-primary">Invio</button>
                        </div>
                    </form>
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
document.addEventListener('DOMContentLoaded', function () {
  const masterSelect = document.getElementById('masterSelect');
  const mostraEsami  = document.getElementById('mostraEsami');

  // ⬇️ Metti qui il path del tuo componente PHP
  const ENDPOINT = './include/componenti/master/stampaEsamiMaster.php';

  if (!masterSelect || !mostraEsami) return;

  function hasValidValue(v) {
    return typeof v === 'string' && v.trim() !== '';
  }

  async function loadEsami(masterId) {
    if (!hasValidValue(masterId)) {
      mostraEsami.style.display = 'none';
      mostraEsami.innerHTML = '';
      return;
    }

    mostraEsami.style.display = 'block';
    masterSelect.disabled = true;

    const body = new URLSearchParams({
      masterId: masterId,
    });

    try {
      const res = await fetch(ENDPOINT, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString(),
        cache: 'no-store'
      });
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const html = await res.text();
      mostraEsami.innerHTML = html;
    } catch (err) {
      mostraEsami.innerHTML = '<div class="alert alert-danger">Errore nel caricamento: ' + (err.message || err) + '</div>';
    } finally {
      masterSelect.disabled = false;
    }
  }

  function bindSelect() {
    // Usa evento di Select2 se presente, altrimenti change nativo
    if (window.jQuery && typeof jQuery.fn.select2 === 'function' &&
        jQuery(masterSelect).hasClass('js-select2')) {
      jQuery(masterSelect).on('select2:select', function (e) {
        const id = String(e.params?.data?.id || '');
        loadEsami(id);
      });
    } else {
      masterSelect.addEventListener('change', function () {
        loadEsami(masterSelect.value);
      });
    }
  }


  bindSelect();

  // Stato iniziale (gestisce pre-selezione)
  if (hasValidValue(masterSelect.value)) {
    loadEsami(masterSelect.value);
  } else {
    mostraEsami.style.display = 'none';
    mostraEsami.innerHTML = '';
  }
});
</script>

