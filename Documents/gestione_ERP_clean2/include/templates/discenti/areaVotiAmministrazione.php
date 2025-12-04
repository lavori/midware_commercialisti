<!-- content @s -->
<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h1 class="page-title"><?php echo $h1; ?></h1>
            <div class="nk-block-des text-soft">
                <h2><?php echo $h2; ?></h2>
            </div>
        </div>
    </div></div><div class="nk-block  nk-block-lg">
    <div class="row g-gs my-3">
        <div class="col-md-12">
            <div class="card card-bordered card-preview">
                <?php
                $preselectedDiscenteId = null; // Initialize
                if (isset($_SESSION['id_discente']) && !empty($_SESSION['id_discente'])) {
                    $preselectedDiscenteId = $_SESSION['id_discente'];
                    unset($_SESSION['id_discente']);
                }
                ?>
                <?php if (isset($message) && $message == 'votoInserito') { ?>
                    <div class="example-alert">
                        <div class="alert alert-success alert-icon">
                            <em class="icon ni ni-check-circle"></em> <strong>Dato inserito</strong> con successo
                        </div>
                    </div>
                <?php } ?>
                <?php if (isset($message) && $message == 'FileInserito') { ?>
                    <div class="example-alert">
                        <div class="alert alert-success alert-icon">
                            <em class="icon ni ni-check-circle"></em> <strong>File inserito</strong> con successo
                        </div>
                    </div>
                <?php } ?>
                <?php if (isset($message) && $message == 'FileGiàPresente') { ?>
                    <div class="example-alert">
                        <div class="alert alert-danger alert-icon">
                            <em class="icon ni ni-cross-circle"></em> <strong>Errore!</strong> File già presente.
                        </div>
                    </div>
                <?php } ?>
                <?php if (isset($message) && $message == 'votoModificato') { ?>
                    <div class="example-alert">
                        <div class="alert alert-success alert-icon">
                            <em class="icon ni ni-check-circle"></em> <strong>Dato aggiornato</strong> con successo
                        </div>
                    </div>
                <?php } ?>
                <div class="card-inner">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label class="form-label">Scegli Discente</label>
                            <div class="form-control-wrap">
                                <select class="form-select" id="corsoSelect" name="corsoSelect" data-search="on" required>
                                    <option value="" disabled selected>Seleziona...</option>
                                    <?php foreach ($discenti as $c) { ?>
                                        <option value="<?php echo $c['id']; ?>"
                                            <?php echo ($preselectedDiscenteId == $c['id']) ? 'selected' : ''; ?>>
                                            <?php echo $c['nome'] . ' ' . $c['cognome']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        
                            <div class="form-group" id="inputMethodToggle" style="display: none; color: purple;">
                                <a href="javascript:void(0);"><b>Carica File</b></a>
                            </div>
                        
                        <div id="corsoInfo" style="display: none;">
                            <div id="corsoDettagli">
                                <!-- contenuto dinamico AJAX -->
                            </div>
                        </div>

                        <div id="caricaVoti" style="display: none;">
                            <div class="text-center mt-4">
                                <a class="btn btn-primary" name="downloadFile"
                                    href="<?php echo $Prevalutazione ?>">Scarica File</a>
                                <p class="form-note mt-3">Scarica il file, compilalo e caricalo</p>

                                <br>
                                <p class="form-label">Oppure</p>
                                <div class="row justify-content-center mt-4">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <p class="form-note mt-3">Carica File con l'attestazione della
                                                valutazione degli esami sostenuti</p>
                                            <div class="form-file">
                                                <input type="file" class="form-file-input" id="FileVoti"
                                                    name="FileVoti">
                                                <label class="form-file-label" for="FileVoti">Upload File</label>
                                            </div>
                                            <button type="submit" class="btn btn-primary mt-3">Invia</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>

document.addEventListener('DOMContentLoaded', function () {
    let currentInputMethod = 'manuale';
    const corsoSelect = document.getElementById('corsoSelect');
    const corsoInfoDiv = document.getElementById('corsoInfo');
    const corsoDettagliDiv = document.getElementById('corsoDettagli');
    const fileVotiDiv = document.getElementById('caricaVoti');
    const toggleButton = document.getElementById('inputMethodToggle');

    // Nascondi tutte le sezioni inizialmente
    corsoInfoDiv.style.display = 'none';
    fileVotiDiv.style.display = 'none';

    // Inizializza Select2 se disponibile
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $(corsoSelect).select2().on('select2:select', function (e) {
            const idDiscente = e.params.data.id;
            if (idDiscente) {
                mostraCorsoInfo(idDiscente);
                toggleButton.style.display = 'inline'; // Mostra toggle
            }
        });
    } else {
        corsoSelect.addEventListener('change', function () {
            const idDiscente = this.value;
            if (idDiscente) {
                mostraCorsoInfo(idDiscente);
                toggleButton.style.display = 'inline';
            } else {
                corsoInfoDiv.style.display = 'none';
                fileVotiDiv.style.display = 'none';
                toggleButton.style.display = 'none';
            }
        });
    }

    function mostraCorsoInfo(idDiscente) {
        corsoInfoDiv.style.display = 'block';
        fileVotiDiv.style.display = 'none';
        toggleButton.textContent = "Carica File";
        currentInputMethod = 'manuale';
        corsoDettagliDiv.innerHTML = 'Caricamento...';

        fetch('./include/componenti/discenti/stampaVotiAmministrazione.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + encodeURIComponent(idDiscente),
        })
        .then(response => response.ok ? response.text() : Promise.reject(response.status))
        .then(html => corsoDettagliDiv.innerHTML = html)
        .catch(error => corsoDettagliDiv.innerHTML = 'Errore nel caricamento dei dati. ' + error);
    }

    toggleButton.addEventListener('click', function () {
        const isManuale = (currentInputMethod === 'manuale');
        corsoInfoDiv.style.display = isManuale ? 'none' : 'block';
        fileVotiDiv.style.display = isManuale ? 'block' : 'none';
        toggleButton.textContent = isManuale ? 'Carica Manualmente' : 'Carica File';
        currentInputMethod = isManuale ? 'word' : 'manuale';
    });
});


function inviaVoti(button) {
    const form = button.closest('form');
    const formData = new FormData(form);
    const action = form.getAttribute('action');
    const votoInput = form.querySelector('input[name="voto"]');

    fetch(action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        console.log('Risposta dal server:', data); // 🔍 controllo
        const voto = data.trim();
        if (!isNaN(voto)) {
            votoInput.value = voto;

            // ✅ Cambia il testo del pulsante in "Modifica"
            button.textContent = "Modifica";

            // ✅ Cambia il name da "insertVoto" a "updateVoto" se esiste
            const insertInput = form.querySelector('input[name="insertVoto"]');
            if (insertInput) {
                insertInput.setAttribute("name", "updateVoto");
                insertInput.setAttribute("value", "Modifica");
            }
        } else {
            alert("Risposta non valida dal server: " + voto);
        }
    })
    .catch(error => {
        alert('Errore nel salvataggio del voto');
    });
}



</script>

