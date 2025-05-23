<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h1 class="page-title">Ricerca Incassi / Corrispettivi taxisti</h1>
            <div class="nk-block-des text-soft">
                <h2>Inserisci i dati di ricerca</h2>
            </div>
        </div>
    </div>
</div>
<div class="nk-block nk-block-lg">
    <div class="card card-bordered card-preview">
        <div class="card-inner">
            <div class="card-head">
                <h6 class="title">Dati di ricerca</h6>
            </div>
            <form action="/corrispettivi/taxisti" method="POST" class="form-validate">
                <input type="hidden" id="tipo_action" name="tipo_action" value="ricerca">
                <div class="row g-3">
                    <div class="col-4">
                        <div class="form-group">
                            <label class="form-label" for="mese">Taxista</label>
                            <div class="form-control-wrap">
                                <select class="form-select" id="taxista" name="taxista" data-search="on" data-ui="lg">
                                    <option value="0">Tutti i Taxisti</option>
                                    <?php foreach($tassisti as $taxi): ?>
                                    <option value="<?php echo $taxi['id']; ?>"><?php echo $taxi['Nome']." ".$taxi['Cognome']; if ($taxi['Dimissioni'] != "") echo " (".$taxi['Dimissioni'].")"; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            <label class="form-label">Date</label>
                            <div class="form-control-wrap">
                                <div class="input-daterange date-picker-range input-group">
                                    <input type="text" name="da" class="form-control date-picker" data-date-format="dd/mm/yyyy" />
                                    <div class="input-group-addon">A</div>
                                    <input type="text" name="a" class="form-control date-picker" data-date-format="dd/mm/yyyy" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-4" id="tipo_div">
                        <div class="form-group" >
                            <label class="form-label">Tipo</label>
                            <div class="form-control-wrap">
                                <div class="custom-control-lg custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="tipo" value="1" name="tipo">
                                    <label class="custom-control-label" for="tipo">Non Contabilizzato</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-8" id='spacer'></div>
                    <div class="col-4">
                        <div class="form-group">
                            <label class="form-label" for="submit">&nbsp;</label>
                            <div class="form-control-wrap">
                                <button type="submit" class="btn btn-primary" id="submit">Ricerca corrispettivi</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <?php if($alert!= ''): ?>
            <div class="card-inner" id="alert">
                <div class="alerts">
                    <div class="gy-4">
                        
                        <div class="example-alert">
                            <div class="alert alert-fill alert-<?php echo $tipo_alert;?> alert-icon">
                                <em class="icon ni ni-check-circle"></em> <strong><?php echo $alert; ?></strong>. 
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
            <script>
                setTimeout(function() {
                var alertDiv = document.getElementById('alert');
                if (alertDiv) {
                    alertDiv.style.display = 'none';
                }
                }, 5000); // 5000 milliseconds = 5 seconds
            </script>
        <?php endif; ?>
    </div>
</div>
<?php if($content!= '' && $content!= array()): ?>
    <?php if(isset($_POST['tipo_action']) && ($_POST['tipo_action']=="ricerca" || $_POST['tipo_action']=="modifica_incasso" || $_POST['tipo_action']=="modifica_corrispettivi")): ?>
        <div class="nk-block nk-block-lg">
            <div class="card card-bordered card-preview">
                <div class="card-inner">
                    <?php $selectedTaxistaId = $_POST['taxista'] ?? '0'; ?>
                    <h6 class="title mb-3">Parametri Utilizzati per la Ricerca<?php if($selectedTaxistaId == '0') echo " Corrispettivi"; else echo " Incassi"; ?></h6>
                    <ul class="list list-sm list-checked">
                        <li>
                            <strong>Taxista:</strong>
                            <?php
                                if ($selectedTaxistaId === '0') {
                                    echo "Tutti i Taxisti";
                                } else {
                                    $found = false;
                                    foreach ($tassisti as $taxi) {
                                        if ($taxi['id'] == $selectedTaxistaId) {
                                            echo htmlspecialchars($taxi['Nome'] . " " . $taxi['Cognome'], ENT_QUOTES, 'UTF-8');
                                            $found = true;
                                            break;
                                        }
                                    }
                                    if (!$found) echo "ID: " . htmlspecialchars($selectedTaxistaId, ENT_QUOTES, 'UTF-8'); // Fallback if name not found
                                }
                            ?>
                        </li>
                        <?php if (!empty($_POST['da']) || !empty($_POST['a'])): ?>
                        <li><strong>Periodo:</strong> Dal <?php echo htmlspecialchars($_POST['da'] ?? 'N/D', ENT_QUOTES, 'UTF-8'); ?> al <?php echo htmlspecialchars($_POST['a'] ?? 'N/D', ENT_QUOTES, 'UTF-8'); ?></li>
                        <?php endif; ?>
                        <?php if ($selectedTaxistaId === '0'): // Mostra il tipo solo se si cercano i corrispettivi aggregati ?>
                        <li><strong>Stato:</strong> <?php echo isset($_POST['tipo']) ? 'Contabilizzato' : 'Non Contabilizzato'; ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>
<div class="nk-block nk-block-lg">
    <div class="card card-bordered card-preview">
        <div class="card-inner">
            <?php 
                $th=array_keys($content[0]);
                //echo "<pre>"; print_r($th); echo "</pre>";
            ?>
            <!-- solo se sono corrispettivi -->
            <form action="/corrispettivi/invio-corrispettivi" method="POST" class="form-validate">
                <input type="hidden" id="tipo" name="tipo" value="taxi">
            <!-- solo se sono corrispettivi -->
                <table class="datatable-init-export table">
                    <thead>
                        <tr>
                            <?php if($ricerca=="corrispettivi_taxisti"): ?>
                            <th class="nk-tb-col nk-tb-col-check">
                                <!-- Checkbox Seleziona Tutti -->
                                <div class="custom-control custom-control-sm custom-checkbox notext">
                                    <input type="checkbox" class="custom-control-input" id="selectAllCheckbox">
                                    <label class="custom-control-label" for="selectAllCheckbox">Contabilizzazione</label>
                                </div>
                            </th>
                            <?php endif; ?>
                        <?php 
                            foreach($th as $k => $v):
                                // Salta la colonna 'contabilizzato'
                                if ($v === 'contabilizzato' || $v==='n_registrazione') continue;
                                // Modifica la stringa prima della stampa
                                $stringa_modificata = str_replace('_', ' ', $v); 
                                $stringa_modificata = ucwords($stringa_modificata); 
                        ?>
                            <th class="nk-tb-col tb-col-sm"><?php echo htmlspecialchars($stringa_modificata, ENT_QUOTES, 'UTF-8'); ?></th>
                        <?php endforeach; ?>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($content as $k => $v): ?>
                        <tr>
                            <?php foreach($v as $k2 => $v2): ?>
                                <?php if ($k2==="n_registrazione")  continue; ?>
                            <!-- Cella Checkbox per riga -->
                            <?php 
                                if($ricerca=="corrispettivi_taxisti" && $k2 === 'contabilizzato'): 
                                    $data_obj_for_sort = new DateTime($v['data']); 
                                    $dataOrderAttr1 = ' data-order="' . $data_obj_for_sort->format('Ymd') . '"'; 
                            ?>
                            <td class="nk-tb-col nk-tb-col-check" <?php echo $dataOrderAttr1; ?>>
                                <?php  if ($k2 === 'contabilizzato' && $v2==0): ?>
                                <div class="custom-control custom-control-sm custom-checkbox notext">
                                    <input type="checkbox" class="custom-control-input rowCheckbox" id="corrispettivo<?php echo $k; ?>" name="corrispettivo[]" value="<?php echo $v['data']; ?>|<?php echo $v['valore_corrispettivo']; ?>">
                                    <label class="custom-control-label" for="corrispettivo<?php echo $k; ?>"></label>
                                </div>
                                <?php else: ?>
                                <h6><mark>Reg.N. <?php echo $v['n_registrazione']; ?></mark></h6>    
                                <?php endif; ?>
                            </td>
                            <?php 
                                else:
                                    // Prepara l'attributo data-order se è la colonna data
                                    $dataOrderAttr = '';
                                    if ($k2 == "data") {
                                        $data_obj_for_sort = new DateTime($v2); // $v2 è nel formato YYYY-MM-DD dal DB
                                        $dataOrderAttr = ' data-order="' . $data_obj_for_sort->format('Ymd') . '"'; // Formato YYYYMMDD per ordinamento
                                    }
                            ?>
                            <td class="vm nk-tb-col tb-col-sm" <?php echo $dataOrderAttr; ?>>
                                <?php
                                    if ($k2 == "data") {
                                        // Formatta la data come gg/mm/aaaa
                                        $data_obj = new DateTime($v2);
                                        echo $data_obj->format('d/m/Y');
                                    } elseif ($k2 == "giorno_settimana") {
                                        // Traduce il giorno della settimana in italiano
                                        $giorni_italiani = [
                                            "Monday" => "Lunedì",
                                            "Tuesday" => "Martedì",
                                            "Wednesday" => "Mercoledì",
                                            "Thursday" => "Giovedì",
                                            "Friday" => "Venerdì",
                                            "Saturday" => "Sabato",
                                            "Sunday" => "Domenica"
                                        ];
                                        echo $giorni_italiani[$v2] ?? $v2; // Usa l'operatore null coalesce per gestire giorni non tradotti
                                    } elseif (strpos($k2, "valore") !== false) {
                                        // Se il nome del campo contiene "valore", aggiungi il simbolo dell'euro
                                        echo "€ " . htmlspecialchars($v2, ENT_QUOTES, 'UTF-8');
                                    } elseif (strpos($k2, "incasso_contabilizzato") !== false) {
                                        // Se il nome del campo contiene "incasso_contabilizzato", interpreta 0 come non contabilizzato e 1 come contabilizzato
                                        if($v2==0){
                                            echo "Non contabilizzato";
                                        } else {
                                            echo "Contabilizzato"; 
                                        }
                                    } else {
                                        // Altrimenti, stampa il valore normalmente
                                        echo htmlspecialchars($v2, ENT_QUOTES, 'UTF-8');
                                    }
                                ?>
                            </td>
                            <?php 
                                endif; 
                            ?>
                            <?php endforeach; ?>
                            <td class="nk-tb-col nk-tb-col-tools">
                                <ul class="nk-tb-actions gx-1">
                                    <li>
                                        <div class="drodown">
                                            <a class="dropdown-toggle btn btn-icon btn-trigger" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <?php if($ricerca=='incassi_taxisti'): ?>
                                                <ul class="link-list-opt no-bdr">
                                                    <?php if ($v['incasso_contabilizzato']==0):?>
                                                    <li>
                                                        <a style="cursor: pointer;" onClick="apriModal('Modifica Incasso','Gestione corrispettivi','corrispettivi/modifica_incasso','<?php echo $v['data']."|".$_POST['taxista']."|".$_POST['da']."|".$_POST['a']; ?>','0')">
                                                            <em class="icon ni ni-shield-star"></em>
                                                            <span>Modifica incasso</span>
                                                        </a>
                                                    </li>
                                                    <?php else: ?>
                                                    <li>
                                                        <em class="icon ni ni-shield-star"></em>
                                                        <span>Incasso contabilizzato</span>
                                                    </li>
                                                    <?php endif; ?>
                                                </ul>
                                                <?php else: ?>
                                                <ul class="link-list-opt no-bdr">
                                                    <li>
                                                        <a style="cursor: pointer;" onClick="apriModal('Dettaglio Corrispettivo','Gestione corrispettivi','corrispettivi/dettaglio_corrispettivo','<?php echo $v['data']; ?>','1')">
                                                            <em class="icon ni ni-shield-star"></em>
                                                            <span>Visualizza dettagli</span>
                                                        </a>
                                                    </li>
                                                    <?php 
                                                        if($v['contabilizzato']==0): 
                                                            if(isset($_POST) && isset($_POST['da']))$da = $_POST['da']; else $da='';
                                                            if(isset($_POST) && isset($_POST['a']))$a = $_POST['a']; else $a='';
                                                    ?>
                                                    <li>
                                                        <a style="cursor: pointer;" onClick="apriModal('Modifica Corrispettivo','Gestione corrispettivi','corrispettivi/modifica_corrispettivo','<?php echo $v['data']."|".$da."|".$a; ?>','0')">
                                                            <em class="icon ni ni-edit"></em>
                                                            <span>Modifica Corrispettivo</span>
                                                        </a>
                                                    </li>
                                                    <?php 
                                                        endif; 
                                                    ?>
                                                </ul>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <!-- solo se sono corrispettivi -->
                <div class="row g-3">
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label" for="submit">&nbsp;</label>
                            <div class="form-control-wrap">
                                <button type="submit" class="btn btn-primary" id="submit" width="100%">Invia Corrispettivi</button>
                            </div>  
                        </div>
                    </div>
                </div>
            </form>
            <!-- solo se sono corrispettivi -->
        </div>
    </div>
</div>

<!-- Script per gestione Checkbox con DataTables -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Seleziona la tabella (potrebbe essere necessario adattare il selettore se hai più tabelle)
    const table = document.querySelector('.datatable-init-export');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');

    if (table && selectAllCheckbox) {
        // Funzione per aggiornare lo stato della checkbox "Seleziona Tutti"
        function updateSelectAllCheckbox() {
            const rowCheckboxes = table.querySelectorAll('tbody .rowCheckbox');
            const checkedCount = table.querySelectorAll('tbody .rowCheckbox:checked').length;
            const totalCheckboxes = rowCheckboxes.length;

            selectAllCheckbox.checked = (checkedCount > 0 && checkedCount === totalCheckboxes);
            // Gestisce lo stato indeterminato se alcuni ma non tutti sono selezionati
            selectAllCheckbox.indeterminate = (checkedCount > 0 && checkedCount < totalCheckboxes);
        }

        // Evento per la checkbox "Seleziona Tutti"
        selectAllCheckbox.addEventListener('change', function() {
            const rowCheckboxes = table.querySelectorAll('tbody .rowCheckbox');
            rowCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Evento delegato sul tbody per le checkbox delle righe (più efficiente con DataTables)
        table.querySelector('tbody').addEventListener('change', function(event) {
            if (event.target.classList.contains('rowCheckbox')) {
                updateSelectAllCheckbox();
            }
        });
    }

    // Gestione cambio label per lo switch "Contabilizzato"
    const tipoSwitch = document.getElementById('tipo');
    const tipoLabel = document.querySelector('label[for="tipo"]');

    if (tipoSwitch && tipoLabel) {
        // Funzione per aggiornare il testo della label
        function updateTipoLabel() {
            if (tipoSwitch.checked) {
                tipoLabel.textContent = 'Contabilizzato';
            } else {
                tipoLabel.textContent = 'Non Contabilizzato';
            }
        }

        // Imposta lo stato iniziale al caricamento della pagina
        updateTipoLabel();
        // Aggiungi l'event listener per il cambio di stato
        tipoSwitch.addEventListener('change', updateTipoLabel);
    }

    // Gestione cambio label per il bottone di submit ricerca
    const taxistaSelect = document.getElementById('taxista');
    const submitButton = document.getElementById('submit'); // Assicurati che il bottone abbia questo ID

    if (taxistaSelect && submitButton) {
        // Funzione per aggiornare il testo del bottone
        function updateSubmitButtonLabel() {
            if (taxistaSelect.value === '0') {
                submitButton.textContent = 'Ricerca corrispettivi';
                document.getElementById('tipo_div').style.display = 'block';
                document.getElementById('spacer').style.display = 'block';
            } else {
                submitButton.textContent = 'Ricerca incassi';
                document.getElementById('tipo_div').style.display = 'none';
                document.getElementById('spacer').style.display = 'none';   
            }
        }

        // Imposta lo stato iniziale al caricamento della pagina
        updateSubmitButtonLabel();
        // Aggiungi l'event listener per il cambio di stato del select
        taxistaSelect.addEventListener('change', updateSubmitButtonLabel);
    }

});

</script>
<?php endif; ?>