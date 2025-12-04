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
<div class="nk-block nk-block-lg">
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
                <div class="card-inner">
                    <?php if (isset($utenti) && $utenti != array()): ?>
                        <?php if (!empty($utenti)): ?>
                            <?php $headers = array_keys($utenti[0]); ?>
                            <table class="table datatable-init-export">
                                <thead>
                                    <tr>
                                        <th class="nk-tb-col">Nome</th>
                                        <th class="nk-tb-col">Cognome</th>
                                        <th class="nk-tb-col">Email</th>
                                        <th class="nk-tb-col">Telefono</th>
                                        <th class="nk-tb-col">Ruolo</th>
                                        <th class="nk-tb-col"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($utenti as $u): ?>
                                        <tr>
                                            <?php
                                            $ordine = ['nome', 'cognome', 'email', 'telefono', 'ruolo'];
                                            foreach ($ordine as $col):
                                                $val = $u[$col] ?? null;
                                                ?>
                                                <td>
                                                    <?php
                                                    if ($col === 'cv' && !empty($val)) {
                                                        echo '<a href="' . htmlspecialchars($val) . '" class="btn btn-primary" download target="_blank">Scarica CV</a>';
                                                    } elseif (
                                                        is_string($val) &&
                                                        (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val) || preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $val))
                                                    ) {
                                                        $formato = (strpos($val, '-') !== false) ? 'Y-m-d' : 'd/m/Y';
                                                        $data = DateTime::createFromFormat($formato, $val);
                                                        echo $data ? $data->format('d/m/Y') : htmlspecialchars($val);
                                                    } elseif (
                                                        is_string($val) &&
                                                        (filter_var($val, FILTER_VALIDATE_URL) || preg_match('/[\/\\\\].+\.[a-zA-Z0-9]{2,5}$/', $val))
                                                    ) {
                                                        echo '<a href="' . htmlspecialchars($val) . '" class="btn btn-primary" download target="_blank">Scarica</a>';
                                                    } elseif (!is_null($val) && trim((string) $val) !== '') {
                                                        echo htmlspecialchars($val);
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </td>
                                            <?php endforeach; ?>
                                            <td class="nk-tb-col nk-tb-col-tools">
                                                <ul class="nk-tb-actions gx-1">
                                                    <li>
                                                        <div class="drodown">
                                                            <a class="dropdown-toggle btn btn-icon btn-trigger"
                                                                data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                <ul class="link-list-opt no-bdr">
                                                                    <li>
                                                                        <a style="cursor: pointer;"
                                                                            onClick="apriModalLarge('Modifica Utente','Gestione Utente','setting/form_utente','<?php echo $u['id']; ?>')">
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
                        <?php endif; ?>
                    <?php endif; ?>
                    <div class="center card-footer" style="background-color: #fff; border-top:none;">
                        <a onClick="apriModalLarge('Nuovo Utente','Gestione Utente','setting/form_utente','new')"
                            class="btn btn-primary">Aggiungi Utente</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<form id="mainForm" action="<?= $serp ?>" method="POST"></form>