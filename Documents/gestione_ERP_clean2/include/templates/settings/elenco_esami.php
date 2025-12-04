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
                <!--Stampa messaggi di errore e di corretto inserimento-->
                <?php if (isset($message)):?>
                    <?php switch ($message):
                        case 'insert': ?>
                            <div class="example-alert">
                                <div class="alert alert-success alert-icon">
                                    <em class="icon ni ni-check-circle"></em>
                                    <strong>Dato inserito</strong> con successo
                                </div>
                            </div>
                            <?php break;
                        case 'update': ?>
                            <div class="example-alert">
                                <div class="alert alert-success alert-icon">
                                    <em class="icon ni ni-check-circle"></em>
                                    <strong>Dato aggiornato</strong> con successo
                                </div>
                            </div>
                            <?php break;
                        case 'CSV_insert': ?>
                            <div class="example-alert">
                                <div class="alert alert-success alert-icon">
                                    <em class="icon ni ni-check-circle"></em>
                                    <strong>CSV inserito</strong> con successo
                                </div>
                            </div>
                            <?php break;
                        case 'CSV_partial_insert': ?>
                            <div class="example-alert">
                                <div class="alert alert-warning alert-icon">
                                    <em class="icon ni ni-alert-circle"></em>
                                    <strong>CSV parzialmente inserito.</strong> Alcune righe potrebbero aver causato errori o essere
                                    duplicate. Controlla i messaggi sottostanti.
                                </div>
                            </div>
                            <?php break;
                        case 'CSV_notInsert_all_failed': ?>
                            <div class="example-alert">
                                <div class="alert alert-danger alert-icon">
                                    <em class="icon ni ni-cross-circle"></em>
                                    <strong>Nessuna riga del CSV inserita.</strong> Tutte le righe hanno causato errori. Controlla i
                                    messaggi sottostanti.
                                </div>
                            </div>
                            <?php break;
                        case 'CSV_no_new_data_duplicates_or_errors':
                        case 'CSV_no_new_data': ?>
                            <div class="example-alert">
                                <div class="alert alert-info alert-icon">
                                    <em class="icon ni ni-info"></em>
                                    <strong>Nessun nuovo dato da inserire dal CSV.</strong> Le righe potrebbero essere duplicate,
                                    non valide o già presenti.
                                </div>
                            </div>
                            <?php break;
                        case 'CSV_empty_or_header_only': ?>
                            <div class="example-alert">
                                <div class="alert alert-info alert-icon">
                                    <em class="icon ni ni-info"></em>
                                    <strong>Il file CSV è vuoto o contiene solo l'intestazione.</strong>
                                </div>
                            </div>
                            <?php break;
                        case 'CSV_fopen_failed': ?>
                            <div class="example-alert">
                                <div class="alert alert-danger alert-icon">
                                    <em class="icon ni ni-cross-circle"></em>
                                    <strong>Errore:</strong> Impossibile aprire il file CSV caricato.
                                </div>
                            </div>
                            <?php break;
                        case 'CSV_invalid_extension': ?>
                            <div class="example-alert">
                                <div class="alert alert-danger alert-icon">
                                    <em class="icon ni ni-cross-circle"></em>
                                    <strong>Errore:</strong> Tipo di file non valido. Solo file .csv sono accettati.
                                </div>
                            </div>
                            <?php break;
                        // Casi per errori di upload
                        case 'CSV_file_too_large':
                        case 'CSV_upload_partial':
                        case 'CSV_no_file_sent':
                        case 'CSV_no_tmp_dir':
                        case 'CSV_cant_write':
                        case 'CSV_php_extension_error':
                        case 'CSV_upload_unknown_error':
                        case 'CSV_no_file_uploaded': ?>
                            <div class="example-alert">
                                <div class="alert alert-danger alert-icon">
                                    <em class="icon ni ni-cross-circle"></em>
                                    <strong>Errore di caricamento CSV:</strong>
                                    <?php echo htmlspecialchars(str_replace('CSV_', '', $message)); ?>
                                </div>
                            </div>
                            <?php break;
                        case 'CSV_notInsert': ?>
                            <div class="example-alert">
                                <div class="alert alert-danger alert-icon">
                                    <em class="icon ni ni-cross-circle"></em>
                                    <strong>Inserimento Fallito</strong>! Si è verificato un errore
                                </div>
                            </div>
                            <?php break;
                    endswitch; ?>
                <?php endif; ?>
                <?php if (isset($corsi_saltati) && !empty($corsi_saltati)): ?>
                    <div class="example-alert">
                        <br> <?php foreach ($corsi_saltati as $dettaglio_errore): ?>
                            <div class="alert alert-warning alert-icon">
                                <em class="icon ni ni-alert-circle"></em>
                                <?= htmlspecialchars($dettaglio_errore) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!--Sviluppo tabella-->
                <div class="card-inner">
                    <form method="POST">
                        <div class="col-12">
                            <div class="form-group">
                                <br>
                                <div class="d-flex align-items-center gap-2">
                                    <select class="form-select js-select2 flex-grow-1" name="cercaFacolta"
                                        data-search="on">
                                        <option value="#" selected disabled>Seleziona Percorso Esame</option>
                                        <?php foreach ($percorso as $p) { ?>
                                            <option value="<?php echo $p['id']; ?>">
                                                <?php echo $p['percorso']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <button type="submit" class="btn btn-primary">Cerca</button>
                                </div>
                            </div>
                        </div>
                        <br>
                    </form>
                    <?php if (isset($corsi) && !empty($corsi)) { ?>
                        <?php
                        $corsi ? $th = array_keys($corsi[0]) : "";
                        ?>
                        <?php if (isset($corsi) && !empty($corsi)) { ?>
                            <br>
                            <table class="table datatable-init-export">
                                <thead>
                                    <tr>
                                        <?php
                                        foreach ($th as $k => $v):
                                            if ($v != "id"):
                                                switch ($v) {
                                                    case "percorso":
                                                        $class = "nk-tb-col tb-col-md";
                                                        $v = "Percorso";
                                                        break;
                                                    case "cfu":
                                                        $class = "nk-tb-col tb-col-md";
                                                        $v = "CFU";
                                                        break;
                                                    case "paniere":
                                                        $class = "nk-tb-col tb-col-md";
                                                        $v = "Paniere";
                                                        break;
                                                    case "codice":
                                                        $class = "nk-tb-col tb-col-md";
                                                        $v = "Codice";
                                                        break;
                                                    case "nome":
                                                        $class = "nk-tb-col tb-col-md";
                                                        $v = "Nome";
                                                        break;
                                                    case "anno":
                                                        $class = "nk-tb-col tb-col-md";
                                                        $v = "Anno";
                                                        break;
                                                    default:
                                                        $class = "nk-tb-col tb-col-lg";
                                                        break;
                                                }
                                                ?>
                                                <th class="<?php echo $class; ?>"><?php echo $v; ?></th>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                        <th class="nk-tb-col nk-tb-col-tools text-end"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($corsi as $corso): ?>
                                        <tr>
                                            <?php foreach ($corso as $campo => $valore): ?>
                                                <?php if ($campo !== "id"): ?>
                                                    <td>
                                                        <?php
                                                        // Normalizza valori nulli
                                                        if (is_null($valore)) {
                                                            echo '';
                                                            continue;
                                                        }

                                                        // Gestione campo "anno"
                                                        if ($campo === "anno") {
                                                            echo $valore === "s" ? "A Scelta" : htmlspecialchars($valore . "° Anno");
                                                            continue;
                                                        }

                                                        // Gestione campo "paniere"
                                                        if ($campo === "paniere" && !empty($valore)) {
                                                            echo '<a href="' . htmlspecialchars($valore) . '" class="btn btn-primary" download target="_blank">Scarica</a>';
                                                            continue;
                                                        }

                                                        // Formato data YYYY-MM-DD → d/m/Y
                                                        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $valore)) {
                                                            $data = DateTime::createFromFormat('Y-m-d', $valore);
                                                            echo $data ? $data->format('d/m/Y') : htmlspecialchars($valore);
                                                            continue;
                                                        }

                                                        // Formato data d/m/Y (già corretto)
                                                        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $valore)) {
                                                            $data = DateTime::createFromFormat('d/m/Y', $valore);
                                                            echo $data ? $data->format('d/m/Y') : htmlspecialchars($valore);
                                                            continue;
                                                        }

                                                        // Link o file
                                                        if (
                                                            filter_var($valore, FILTER_VALIDATE_URL) ||
                                                            preg_match('/[\/\\\\].+\.[a-zA-Z0-9]{2,5}$/', $valore)
                                                        ) {
                                                            echo '<a href="' . htmlspecialchars($valore) . '" class="btn btn-primary" download target="_blank">Scarica</a>';
                                                            continue;
                                                        }

                                                        // Valore normale
                                                        echo htmlspecialchars($valore);
                                                        ?>
                                                    </td>
                                                <?php endif; ?>
                                            <?php endforeach; ?>

                                            <!-- Colonna azioni -->
                                            <td class="nk-tb-col nk-tb-col-tools">
                                                <ul class="nk-tb-actions gx-1">
                                                    <li>
                                                        <div class="drodown">
                                                            <a class="dropdown-toggle btn btn-icon btn-trigger"
                                                                data-bs-toggle="dropdown">
                                                                <em class="icon ni ni-more-h"></em>
                                                            </a>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                <ul class="link-list-opt no-bdr">
                                                                    <li>
                                                                        <a style="cursor: pointer;"
                                                                            onClick="apriModal('Modifica Esami','Gestione Esami','setting/form_esami','<?php echo $corso['id']; ?>')">
                                                                            <em class="icon ni ni-shield-star"></em>
                                                                            <span>Modifica</span>
                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php } ?>
                    <?php } ?>
                    <div class="form-group" style="display:none;" id="uploadDiv">
                        <hr>
                        <form method="POST" id="uploadfileForm" enctype="multipart/form-data">
                            <label class="form-label" for="fileInput">Carica CSV</label>
                            <div class="form-control-wrap">
                                <div class="form-file">
                                    <input type="file" name="file" id="fileInput" accept=".csv" class="form-file-input"
                                        required>
                                    <label class="form-file-label" for="fileInput">Scegli CSV</label>
                                </div>
                            </div>
                            <input type="hidden" name="action" value="newfile_esame">
                        </form>
                    </div>
                    <hr>

                    <div class="d-flex justify-content-between align-items-center card-footer"
                        style="background-color: #fff; border-top: none;">
                        <!-- Icona a sinistra -->
                        <a onClick="apriModalLarge('Visualizza Totale Esami', 'Gestione Esami', 'setting/visualizzaTotaleEsami')"
                            class="d-flex align-items-center" style="font-size: 1.8rem; color: #6576ff;"
                            data-bs-toggle="tooltip" data-bs-placement="top" title="Totale esami per percorso">
                            <em class="icon ni ni-info"></em>
                        </a>

                        <!-- Pulsanti centrati -->
                        <div class="d-flex align-items-center gap-2">
                            <a onClick="apriModal('Nuovo Esame','Gestione Esami','setting/form_esami','new')"
                                class="btn btn-primary">Aggiungi Esame</a>
                            <button id="buttoncsv" class="btn btn-primary" onclick="mostraUpload()">Carica CSV</button>
                        </div>

                        <!-- Spazio vuoto per allineamento simmetrico -->
                        <div style="width: 1.8rem;"></div>
                    </div>


                </div>
            </div>
        </div><!-- .card-preview -->
    </div>
</div>

<!-- Form unico nella pagina -->
<form id="mainForm" action="<?php echo $serp; ?>" method="POST">
    <!-- Altri campi visibili opzionali -->
</form>
<script>

    //Script per il funzionamento dell'upload file in settings/esami
    function mostraUpload() {
        var uploadDiv = document.getElementById('uploadDiv');
        var button = document.getElementById('buttoncsv');
        var form = document.getElementById('uploadfileForm');
        var fileInput = document.getElementById('fileInput');

        if (!fileInput) {
            alert("Campo file non trovato. Verifica che l'input abbia id='fileInput'.");
            return;
        }

        if (uploadDiv.style.display === "none" || uploadDiv.style.display === "") {
            uploadDiv.style.display = "block";
            button.textContent = "Invia CSV";
        } else {
            if (fileInput.files.length > 0) {
                form.submit();
            } else {
                alert("Seleziona un file CSV prima di inviare.");
            }
        }
    }
</script>