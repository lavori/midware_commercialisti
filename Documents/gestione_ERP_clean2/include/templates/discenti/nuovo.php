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
<?php //echo'<pre>';print_r($pagamentiDiscente);echo'</pre>';?>
<div class="nk-block nk-block-lg">
    <div class="row g-gs my-3">
        <div class="col-md-12">
            <div class="card card-bordered card-preview">
                <div class="card-inner">
                    <div class="form-group">
                        <div class="card-head">
                            <h4 class="card-title">
                                Discente: <?= htmlspecialchars($datiDiscente['nome'] . ' ' . $datiDiscente['cognome']) ?>
                            </h4>
                        </div>
                        <div class="card-head">
                            <h6 class="card-title">
                                N. Matricola: <?= htmlspecialchars($datiDiscente['numMatricola']) ?>
                            </h6>
                        </div>
                    </div>

                    <table class="table datatable-init-export">
                        <thead>
                            <tr>
                                <?php
                                $labels = [
                                    'riferimentoTabellaProd' => 'Tipologia Prodotto',
                                    'riferimentoNomeProd' => 'Prodotto',
                                    'tipoPagamento'=> 'Tipologia Pagamento',
                                    'importoTotale' => 'Importo Totale',
                                    'linkAccordo' => ''
                                ];

                                $excluded = ['id','id_discente','diritti_segreteria','tassa_regionale','marca_bollo','acconto','residuo'];
                                $headers = array_keys($pagamentiDiscente[0]);

                                foreach ($headers as $key):
                                    if (in_array($key, $excluded)) continue;
                                    $label = $labels[$key] ?? ucfirst($key);
                                ?>
                                    <th class="nk-tb-col tb-col-md"><?= htmlspecialchars($label) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pagamentiDiscente as $pD): ?>
                                <tr>
                                    <?php foreach ($pD as $col => $val): ?>
                                        <?php if (in_array($col, $excluded)) continue; ?>
                                        <td>
                                            <?php
                                            // Formatta date
                                            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val) || preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $val)) {
                                                $data = DateTime::createFromFormat(str_contains($val, '-') ? 'Y-m-d' : 'd/m/Y', $val);
                                                echo $data ? $data->format('d/m/Y') : htmlspecialchars($val);
                                            // Importo Totale
                                            } elseif ($col=='importoTotale'){
                                                echo $val.'€';
                                            // Tipologia Pagamento
                                            }elseif($col=='tipoPagamento'){
                                                if($val=='e-campus'){
                                                    echo 'E-Campus';
                                                }else{
                                                    echo 'Tutoraggio';
                                                }
                                            } elseif ($col=='linkAccordo'){
                                                echo '<a href="'.$val.'" type="button" class="btn btn-primary" target="_blank">Visualizza Accordo</a>';
                                            // Altri valori
                                            } elseif ($val !== '') {
                                                echo $val;
                                            }
                                            ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Form unico nella pagina -->
<form id="mainForm" action="<?= htmlspecialchars($serp) ?>" method="POST">
    <!-- Altri campi visibili opzionali -->
</form>
-