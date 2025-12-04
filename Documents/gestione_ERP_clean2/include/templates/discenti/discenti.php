<!-- content @s -->
<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h1 class="page-title"><?= $h1 ?></h1>
            <div class="nk-block-des text-soft">
                <h2><?= $h2 ?></h2>
            </div>
        </div>
    </div>
</div>
<?php //echo'<pre>';print_r($discenti);echo'</pre>'; ?>
<div class="nk-block nk-block-lg">
    <div class="row g-gs my-3">
        <div class="col-md-12">
            <div class="card card-bordered card-preview">
                <?php
                $messages = [
                    'insert' => 'Dato inserito',
                    'update' => 'Dato aggiornato',
                    'ImportoCorretto' => 'Rate inserite'
                ];
                if (isset($message) && isset($messages[$message])): ?>
                    <div class="example-alert">
                        <div class="alert alert-success alert-icon">
                            <em class="icon ni ni-check-circle"></em>
                            <strong><?= $messages[$message] ?></strong> con successo
                        </div>
                    </div>
                <?php endif; ?>

                <div class="card-inner">
                    <?php if (!empty($discenti)): ?>
                        <?php $headers = array_keys($discenti[0]); ?>
                        <table class="table datatable-init-export">
                            <thead>
                                <tr>
                                    <?php foreach ($headers as $key): ?>
                                        <?php
                                        $labels = [
                                            'id' => '',
                                            'nome' => 'Nome',
                                            'cognome' => 'Cognome',
                                            'email' => 'Email',
                                            'telefono' => 'Telefono'
                                        ];
                                        if (in_array($key, ['esami', 'fileVoti', 'carriera', 'pagamento', 'EsamiFatti']))
                                            continue;
                                        $label = $labels[$key] ?? ucfirst($key);
                                        ?>
                                        <th class="nk-tb-col tb-col-md"><?= $label ?></th>
                                    <?php endforeach; ?>
                                    <th class="nk-tb-col nk-tb-col-tools text-end"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($discenti as $discente): ?>
                                    <tr>
                                        <?php foreach ($discente as $col => $val): ?>
                                            <?php if (in_array($col, ['esami', 'fileVoti', 'carriera', 'pagamento', 'EsamiFatti']))
                                                continue; ?>
                                            <td>
                                                <?php
                                                if ($col === 'cv' && !empty($val)) {
                                                    echo '<a href="' . htmlspecialchars($val) . '" class="btn btn-primary" download target="_blank">Scarica CV</a>';
                                                } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val) || preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $val)) {
                                                    $data = DateTime::createFromFormat(str_contains($val, '-') ? 'Y-m-d' : 'd/m/Y', $val);
                                                    echo $data ? $data->format('d/m/Y') : htmlspecialchars($val);
                                                } elseif (filter_var($val, FILTER_VALIDATE_URL) || preg_match('/[\/\\\\].+\.[a-zA-Z0-9]{2,5}$/', $val)) {
                                                    echo '<a href="' . htmlspecialchars($val) . '" class="btn btn-primary" download target="_blank">Scarica</a>';
                                                } elseif ($val !== '') {
                                                    echo htmlspecialchars($val);
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
                                                                <li><a
                                                                        href="/discenti/gestione/<?= base64_encode($discente['id']) ?>/update"><em
                                                                            class="icon ni ni-edit-alt"></em><span>Modifica</span></a>
                                                                </li>
                                                                <li><a href="/discenti/<?= base64_encode($discente['id']) ?>"><em
                                                                            class="icon ni ni-eye"></em><span>Visualizza
                                                                            Discente</span></a></li>
                                                                <?php if (!empty($discente['fileVoti'])): ?>
                                                                    <li><a href="<?= htmlspecialchars($discente['fileVoti']) ?>"
                                                                            download><em class="icon ni ni-download"></em><span>File
                                                                                Voti</span></a></li>
                                                                <?php endif; ?>
                                                                <li><a
                                                                        href="/discenti/gestione/<?= base64_encode($discente['id']) ?>/collegamento"><em
                                                                            class="icon ni ni-link"></em><span>Collegamento
                                                                            E-Campus</span></a></li>
                                                                <li><a
                                                                        href="/discenti/gestione/<?= base64_encode($discente['id']) ?>/nuovo_prodotto">
                                                                        <em class="icon ni ni-plus"></em><span>Aggiungi Prodotto</span></a></li>
                                                                <li><a
                                                                        href="/discenti/gestione/<?= base64_encode($discente['id']) ?>/accordo"><em
                                                                            class="icon ni ni-coin-alt"></em><span>Accordo
                                                                            Economico</span></a></li>
                                                                <?php if ($discente['EsamiFatti'] === 'si' && $discente['carriera'] === 'ok'): ?>
                                                                    <li><a
                                                                            href="/discenti/gestione/<?= base64_encode($discente['id']) ?>/carriera"><em
                                                                                class="icon ni ni-book"></em><span>Visualizza
                                                                                Carriera Esami</span></a></li>
                                                                <?php endif; ?>
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
                    <?php endif; ?>
                    <div class="center card-footer" style="background-color: #fff; border-top:none;">
                        <a href="/discenti/gestione/new" class="btn btn-primary">Aggiungi Discenti</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<form id="mainForm" action="<?= $serp ?>" method="POST"></form>