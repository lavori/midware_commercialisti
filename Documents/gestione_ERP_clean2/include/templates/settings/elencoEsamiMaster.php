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
                <?php if (isset($message)): ?>
                    <?php switch ($message): 
                              case 'insert': ?>
                            <div class="example-alert">
                                <div class="alert alert-success alert-icon">
                                    <em class="icon ni ni-check-circle"></em>
                                    <strong>Dato inserito</strong> con successo
                                </div>
                            </div>
                        <?php break; ?>
                        <?php case 'update': ?>
                            <div class="example-alert">
                                <div class="alert alert-success alert-icon">
                                    <em class="icon ni ni-check-circle"></em>
                                    <strong>Dato aggiornato</strong> con successo
                                </div>
                            </div>
                        <?php break; ?>
                    <?php endswitch; ?>
                <?php endif; ?>
                <!--Sviluppo tabella-->
                <div class="card-inner">
                    <form method="POST">
                        <div class="col-12">
                            <div class="form-group">
                                <br>
                                <div class="d-flex align-items-center gap-2">
                                    <select class="form-select js-select2 flex-grow-1" name="cercaMaster" data-search="on">
                                        <option value="#" selected disabled>Seleziona Master</option>
                                        <?php foreach ($master as $p) { ?>
                                            <option value="<?php echo $p['id']; ?>">
                                                <?php echo $p['nome']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
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
                            <?php if (isset($corsi) && !empty($corsi)) {
                                // Prepara le intestazioni della tabella per un codice più pulito
                                $tableHeaders = [
                                    'codice' => ['label' => 'Codice', 'class' => 'nk-tb-col tb-col-md'],
                                    'nome' => ['label' => 'Nome Esame', 'class' => 'nk-tb-col tb-col-md'],
                                    'cfu' => ['label' => 'CFU', 'class' => 'nk-tb-col tb-col-md'],
                                    'nome_master' => ['label' => 'Master', 'class' => 'nk-tb-col tb-col-md']
                                ];
                                ?>
                                <br>
                                <table class="table datatable-init-export">
                                    <thead>
                                        <tr>
                                            <?php foreach ($tableHeaders as $field => $header): ?>
                                                <th class="<?= $header['class'] ?>">
                                                    <?= $header['label'] ?>
                                                </th>
                                            <?php endforeach; ?>
                                            <th class="nk-tb-col nk-tb-col-tools text-end"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($corsi as $corso): ?>
                                            <tr>
                                                <?php foreach ($tableHeaders as $field => $header):
                                                    $valore = $corso[$field] ?? null; ?>
                                                    <td>
                                                        <?php
                                                        // Formattazione dei valori in linea
                                                        if (is_null($valore) || $valore === '') {
                                                            echo '';
                                                        } elseif ($field === 'anno') {
                                                            echo $valore === 's' ? 'A Scelta' : htmlspecialchars($valore . '° Anno');
                                                        } elseif ($field === 'paniere') {
                                                            if (!empty($valore)) {
                                                                echo '<a href="' . htmlspecialchars($valore) . '" class="btn btn-primary" download target="_blank">Scarica</a>';
                                                            }
                                                        } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $valore)) {
                                                            $data = DateTime::createFromFormat('Y-m-d', $valore);
                                                            echo $data ? $data->format('d/m/Y') : htmlspecialchars($valore);
                                                        } elseif (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $valore)) {
                                                            echo htmlspecialchars($valore);
                                                        } elseif (filter_var($valore, FILTER_VALIDATE_URL) || preg_match('/[\/\\\\].+\.[a-zA-Z0-9]{2,5}$/', $valore)) {
                                                            echo '<a href="' . htmlspecialchars($valore) . '" class="btn btn-primary" download target="_blank">Scarica</a>';
                                                        } else {
                                                            echo htmlspecialchars($valore);
                                                        }
                                                        ?>
                                                    </td>
                                                <?php endforeach; ?>
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
                                                                                onClick="apriModal('Modifica Esami Master','Gestione Esami Master','setting/form_esamiMaster','<?php echo $id_master;?>|<?php echo htmlspecialchars($corso['id']); ?>')">
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
                    <?php } ?>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center card-footer"
                        style="background-color: #fff; border-top: none;">
                        <!-- Icona a sinistra -->
                        <a onClick="apriModalLarge('Visualizza Totale Esami Master', 'Gestione Esami', 'setting/visualizzaTotaleEsamiMaster')"
                            class="d-flex align-items-center" style="font-size: 1.8rem; color: #6576ff;"
                            data-bs-toggle="tooltip" data-bs-placement="top" title="Totale esami per master">
                            <em class="icon ni ni-info"></em>
                        </a>

                        <!-- Pulsanti centrati -->
                        <div class="d-flex align-items-center gap-2">
                            <a onClick="apriModal('Nuovo Esame Master','Gestione Esami Master','setting/form_esamiMaster','<?php echo $id_master;?>|new')"
                                class="btn btn-primary">Aggiungi Esame
                            </a>
                        </div>
                        <!-- Spazio vuoto per allineamento simmetrico -->
                        <div style="width: 1.9rem;"></div>
                    </div>
                </div>
            </div>
        </div><!-- .card-preview -->
    </div>
</div>
</div>
<!-- Form unico nella pagina -->
<form id="mainForm" action="<?php echo $serp; ?>" method="POST">
    <!-- Altri campi visibili opzionali -->
</form>

