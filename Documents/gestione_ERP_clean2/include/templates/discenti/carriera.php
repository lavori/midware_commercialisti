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
                <div class="card-inner">
                    <?php if(isset($esamiCarriera) && $esamiCarriera!=array()): ?>
                        <div class="card-head">
                            <h4 class="card-title">Discente:
                                <?php echo $esamiCarriera[0]['nome_discente'] . ' ' . $esamiCarriera[0]['cognome_discente']; ?>
                            </h4>
                        </div>
                        <div class="card-head">
                            <h6 class="card-title">N. Matricola:
                                <?php echo $esamiCarriera[0]['numMatricola']; ?>
                            </h6>
                        </div>
                        <br>
                        <?php if (!empty($esamiCarriera)): ?>
                            <?php $headers = array_keys($esamiCarriera[0]); ?>
                            <table class="table datatable-init-export">
                                <thead>
                                    <tr>
                                        <th class="nk-tb-col">Nome Esame</th>
                                        <th class="nk-tb-col">Data</th>
                                        <th class="nk-tb-col">Tipo Esame</th>
                                        <th class="nk-tb-col">Esito</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($esamiCarriera as $esami): ?>
                                        <tr>
                                            <?php
                                            $ordine = ['nome_esame', 'tipoEsame', 'motivazione', 'data'];
                                            foreach ($ordine as $col):
                                                $val = $esami[$col] ?? null;
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
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>

                            </table>
                        <?php endif; ?>
                    <?php endif; ?>                                
                </div>
            </div>
        </div>
    </div>
</div>
<form id="mainForm" action="<?= $serp ?>" method="POST"></form>