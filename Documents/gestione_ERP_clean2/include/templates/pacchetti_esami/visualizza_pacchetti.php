<!-- content @s -->
<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h1 class="page-title"><?= htmlspecialchars($h1) ?></h1>
            <div class="nk-block-des text-soft">
                <h2><?= htmlspecialchars($h2) ?></h2>
            </div>
        </div>
    </div>
</div>
<div class="nk-block nk-block-lg">
    <div class="row g-gs my-3">
        <div class="col-md-12">
            <div class="card card-bordered card-preview">
                <div class="card-inner">
                    <table class="table datatable-init-export">
                        <thead>
                            <tr>
                                <?php
                                    $labels = [
                                        'nomePacchetto' => 'Nome Pacchetto',
                                    ];

                                    $excluded = ['id', 'id_esami'];
                                    if (isset($pacchettiEsami) && !empty($pacchettiEsami)) {
                                        $headers = array_keys($pacchettiEsami[0]);

                                        foreach ($headers as $key):
                                            if (in_array($key, $excluded))
                                                continue;
                                            $label = $labels[$key] ?? ucfirst($key);
                                            echo '<th class="nk-tb-col tb-col-md">' . htmlspecialchars($label) . '</th>';
                                        endforeach;
                                    }
                                ?>
                                <th class="nk-tb-col tb-col-md"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pacchettiEsami as $pE): ?>
                                <tr>
                                    <?php foreach ($pE as $col => $val): ?>
                                        <?php if (in_array($col, $excluded))
                                            continue; ?>
                                        <td>
                                            <?php
                                            // Formatta date
                                            if (
                                                $val !== null && (
                                                    preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $val) ||
                                                    preg_match('/^\d{2}\/\d{2}\/\d{4}$/', (string) $val)
                                                )
                                            ) {
                                                $data = DateTime::createFromFormat(str_contains($val, '-') ? 'Y-m-d' : 'd/m/Y', $val);
                                                echo $data ? $data->format('d/m/Y') : htmlspecialchars((string) $val);
                                            } elseif ($val !== '') {
                                                echo $val;
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
                                                            <a style="cursor: pointer;"
                                                                onClick="apriModalLarge('Pacchetto: <?php echo $pE['nomePacchetto']; ?>','Gestione pacchetti','pacchettiEsame/view_esami','<?php echo $pE['id']; ?>')">
                                                                <em class="icon ni ni-eye"></em>
                                                                <span>Visualizza Esami Pacchetti</span>
                                                            </a>
                                                            <li><a href="/genera_pacchetti/update?id=<?= $pE['id']; ?>"><em
                                                                        class="icon ni ni-edit-alt"></em><span>Modifica
                                                                        Esami</span></a>
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

                </div>
            </div>
        </div>
    </div>
</div>