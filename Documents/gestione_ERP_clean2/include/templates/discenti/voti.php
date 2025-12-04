<!-- content @s -->
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="nk-block  nk-block-lg">
                    <div class="card card-bordered card-preview">
                        <?php if (isset($message) && $message == "FileInserito") { ?>
                            <div class="example-alert">
                                <div class="alert alert-success alert-icon">
                                    <em class="icon ni ni-check-circle"></em>
                                    <strong>File Inserito</strong> con successo.
                                </div>
                            </div>
                        <?php } ?>

                        <div class="card-inner">
                            <?php if (!empty($esami)) { ?>
                                <?php if (empty($fileVoti)) { ?>
                                    <div class="form-group">
                                        <label class="form-label" for="inputMethodToggle">Metodo di Caricamento Voti:
                                            <a style="color: purple;" id="inputMethodToggle">Passa a Carica Manualmente
                                            </a>
                                        </label>
                                    </div>

                                    <div id="caricaVoti" class="text-center mt-4">
                                        <a class="btn btn-primary" name="downloadFile"
                                            href="<?php echo $Prevalutazione ?>">Scarica File</a>
                                        <p class="form-note mt-3">Scarica il file, compilalo e caricalo</p>

                                        <br>
                                        <p class="form-label">Oppure</p>
                                        <div class="row justify-content-center mt-4">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <form method="POST" enctype="multipart/form-data">
                                                        <p class="form-note mt-3">Carica File con l'attestazione della
                                                            valutazione degli esami sostenuti</p>
                                                        <div class="form-file">
                                                            <input type="file" class="form-file-input" id="FileVoti"
                                                                name="FileVoti">
                                                            <label class="form-file-label" for="FileVoti">Upload File</label>
                                                        </div>
                                                        <button type="submit" class="btn btn-primary mt-3">Invia</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } else { ?>
                                    <div class="alert alert-warning mt-3">
                                        I voti sono già stati caricati
                                    </div>
                                <?php } ?>

                                <div id="voti" class="mt-4" style="display: none;">
                                    <div class="card card-bordered card-preview">
                                        <div class="card-inner">
                                            <table class="datatable-init nk-tb-list nk-tb-ulist"
                                                data-auto-responsive="true">
                                                <thead>
                                                    <tr class="nk-tb-item nk-tb-head">
                                                        <th class="nk-tb-col tb-col-lg"><span class="sub-text">Codice
                                                                Esame</span></th>
                                                        <th class="nk-tb-col"><span class="sub-text">Nome</span></th>
                                                        <th class="nk-tb-col tb-col-lg"><span class="sub-text">CFU</span>
                                                        </th>
                                                        <th class="nk-tb-col"><span class="sub-text">Voto</span></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($esami as $e) { ?>
                                                        <tr class="nk-tb-item">
                                                            <td class="nk-tb-col tb-col-lg">
                                                                <span class="tb-lead"><?php echo $e['codice'] ?></span>
                                                            </td>
                                                            <td class="nk-tb-col">
                                                                <span class="tb-amount"><?php echo $e['nome'] ?></span>
                                                            </td>
                                                            <td class="nk-tb-col tb-col-lg">
                                                                <span><?php echo $e['cfu'] ?></span>
                                                            </td>
                                                            <td class="nk-tb-col">
                                                                <form method="POST"
                                                                    action="/include/componenti/discenti/insertVotiPrevalutazione.php"
                                                                    class="form-voti d-flex align-items-center gap-2"
                                                                    data-id-esame="<?php echo $e['id']; ?>">
                                                                    <input type="hidden" name="id_esame"
                                                                        value="<?php echo $e['id']; ?>">
                                                                    <?php if (!empty($e['voto'])) { ?>
                                                                        <input type="number" name="voto"
                                                                            class="form-control input-voto"
                                                                            value="<?php echo $e['voto']; ?>" readonly
                                                                            style="max-width: 80px;">
                                                                    <?php } else { ?>
                                                                        <input type="number" name="voto"
                                                                            class="form-control input-voto" placeholder="Voto"
                                                                            max="30" required autocomplete="off"
                                                                            style="max-width: 80px;">
                                                                        <input type="hidden" name="permesso" value="discente">
                                                                        <input type="hidden" name="id_discente"
                                                                            value="<?php echo $_SESSION['utente']['id_discente']; ?>">
                                                                        <button type="button" onclick="inviaVoti(this)"
                                                                            class="btn btn-primary btn-inserisci-voto">Inserisci</button>

                                                                    <?php } ?>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            <?php } else { ?>
                                <div class="alert alert-warning mt-3">
                                    <strong>Attenzione!</strong> Non è stato ancora assegnato un percorso di laurea.
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
<script>
    let currentInputMethod = "word";

    function toggleInputMethodDisplay() {
        const votiDiv = document.getElementById('voti');
        const fileVotiDiv = document.getElementById('caricaVoti');
        const toggleButton = document.getElementById('inputMethodToggle');

        if (votiDiv && fileVotiDiv && toggleButton) {
            if (currentInputMethod === "word") {
                fileVotiDiv.style.display = "block";
                votiDiv.style.display = "none";
                toggleButton.textContent = "Passa a Carica Manualmente";
                currentInputMethod = "manuale";
            } else {
                fileVotiDiv.style.display = "none";
                votiDiv.style.display = "block";
                toggleButton.textContent = "Passa a Carica File";
                currentInputMethod = "word";
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const toggleButton = document.getElementById('inputMethodToggle');
        if (toggleButton) {
            toggleButton.addEventListener('click', toggleInputMethodDisplay);
        }

        toggleInputMethodDisplay();
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
                console.log('Risposta dal server:', data);
                const voto = data.trim();
                if (!isNaN(voto)) {
                    votoInput.value = voto;
                    votoInput.readOnly = true; // ✅ rendo readonly il campo
                    button.style.display = 'none'; // ✅ nascondo il pulsante
                } else {
                    alert("Risposta non valida dal server: " + voto);
                }
            })
            .catch(error => {
                alert('Errore nel salvataggio del voto');
            });
    }

</script>