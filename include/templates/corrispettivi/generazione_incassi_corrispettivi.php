<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h1 class="page-title">Genera Incassi</h1>
            <div class="nk-block-des text-soft">
                <h2>Inserisci i dati per generare Incassi</h2>
            </div>
        </div>
    </div>
</div>
<div class="nk-block nk-block-lg">
    <div class="card card-bordered card-preview">
        <div class="card-inner">
            <div class="card-head">
                <h6 class="title">Dati Incassi</h6>
            </div>
            <form action="/corrispettivi/ridistribuisci-incassi" method="POST" class="form-validate">
                <input type="hidden" id="anno" name="anno" value="<?php echo date('Y'); ?>">
                <div class="row g-3">
                    <div class="col-3">
                        <div class="form-group">
                            <label class="form-label" for="azienda">Azienda</label>
                            <div class="form-control-wrap">
                                <select class="form-select" id="azienda" name="azienda" data-search="off" data-ui="lg">
                                    <option value="">Seleziona Azienda</option>
                                    <?php 
                                        foreach($aziende as $azienda):
                                            echo "<option value='".$azienda['id_azienda']."'>".$azienda['nome_azienda']." (Tax. ".$azienda['numero_tassisti'].")</option>";
                                        endforeach;
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label class="form-label" for="mese">Mese di Riferimento</label>
                            <div class="form-control-wrap">
                                <select class="form-select" id="mese" name="mese" data-search="off" data-ui="lg">
                                    <option value="1">Gennaio</option>
                                    <option value="2">Febbraio</option>
                                    <option value="3">Marzo</option>
                                    <option value="4">Aprile</option>
                                    <option value="5">Maggio</option>
                                    <option value="6">Giugno</option>
                                    <option value="7">Luglio</option>
                                    <option value="8">Agosto</option>
                                    <option value="9">Settembre</option>
                                    <option value="10">Ottobre</option>
                                    <option value="11">Novembre</option>
                                    <option value="12">Dicembre</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label class="form-label" for="incassoMensile">Incasso Mensile per tassista</label>
                            <div class="form-control-wrap">
                                <div class="form-icon form-icon-left">
                                    <em class="icon ni ni-sign-eur"></em>
                                </div>
                                <input type="number" class="form-control" id="incassoMensile" name="incassoMensile" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label class="form-label" for="submit">&nbsp;</label>
                            <div class="form-control-wrap">
                                <button type="submit" class="btn btn-primary" id="submit">Genera Incassi</button>
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
<?php if($content!= ''): ?>
<div class="nk-block nk-block-lg">
    <div class="card card-bordered card-preview">
        <div class="card-inner">
            <?php 
                $th=array_keys($content[0]);
            ?>
            <form action="/corrispettivi/invio-corrispettivi" method="POST" class="form-validate">
                <table class="datatable-init-export table">
                    <thead>
                        <tr>
                        <?php 
                            foreach($th as $k => $v): 
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
                            <td class="vm nk-tb-col tb-col-sm">
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
                                    } else {
                                        // Altrimenti, stampa il valore normalmente
                                        echo htmlspecialchars($v2, ENT_QUOTES, 'UTF-8');
                                    }
                                ?>
                            </td>
                            <?php endforeach; ?>
                            <td class="nk-tb-col nk-tb-col-tools">
                                <ul class="nk-tb-actions gx-1">
                                    <li>
                                        <div class="drodown">
                                            <a class="dropdown-toggle btn btn-icon btn-trigger" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <ul class="link-list-opt no-bdr">
                                                    <li>
                                                        <a style="cursor: pointer;" onClick="apriModal('Dettaglio Corrispettivo','Gestione corrispettivi','corrispettivi/dettaglio_corrispettivo','<?php echo $v['data']; ?>','1')">
                                                            <em class="icon ni ni-shield-star"></em>
                                                            <span>Visualizza dettagli</span>
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
        </div>
    </div>
</div>
<?php endif; ?>