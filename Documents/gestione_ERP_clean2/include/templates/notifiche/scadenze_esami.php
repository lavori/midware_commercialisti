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
                <div class="card-inner">
                    <?php if ($_SESSION['utente']['ruolo'] == 'Superadmin' || $_SESSION['utente']['ruolo'] == 'Admin') { ?>
                        <form method="POST">
                            <div class="col-12">
                                <div class="form-group">
                                    <div class="input-group">
                                        <select class="custom-select js-select2 flex-grow-1" data-search="on"
                                            name="cercaDiscenteEsame">
                                            <option value="#" selected disabled>Seleziona Discente</option>
                                            <?php foreach ($discente as $p) { ?>
                                                <option value="<?php echo $p['id']; ?>"
                                                <?php if(isset($_POST['cercaDiscenteEsame']) && $p['id'] == $_POST['cercaDiscenteEsame']){ echo 'selected';}?>
                                                >
                                                    <?php echo $p['nome'] . ' ' . $p['cognome']; ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <br>
                                    <div class="center">
                                        <button type="submit" class="btn btn-primary">Cerca</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    <?php } ?>
                    <?php if (isset($events) && !empty($events)) { ?>
                        <?php $th = array_keys($events[0]); ?>
                        <?php
                            $ordineColonne = ['codice', 'esame', 'cfu', 'data', 'nomeDiscente' , 'voto','motivazione'];
                            $th = array_values(array_filter($ordineColonne, fn($col) => in_array($col, $th)));
                        ?>

                        <table class="table datatable-init-export">
                            <thead>
                                <tr>
                                    <?php foreach ($th as $v): ?>
                                        <?php
                                        switch ($v) {
                                            case "id_esame":
                                                continue 2;
                                            case "id_discente":
                                                continue 2;
                                            case "data":
                                                $label = "Data";
                                                $class = "nk-tb-col tb-col-md";
                                                break;
                                            case "esame":
                                                $label = "Nome Esame";
                                                $class = "nk-tb-col tb-col-md";
                                                break;
                                            case "cfu":
                                                $label = "CFU";
                                                $class = "nk-tb-col tb-col-md";
                                                break;
                                            case "codice":
                                                $label = "Codice";
                                                $class = "nk-tb-col tb-col-md";
                                                break;
                                            case "nomeDiscente":
                                                $label = "Nome Discente";
                                                $class = "nk-tb-col tb-col-md";
                                                break;
                                            case "voto":
                                                $label = "Voto";
                                                $class = "nk-tb-col tb-col-md";
                                                break;
                                           case "motivazione":
                                                $label = "Esito";
                                                $class = "nk-tb-col tb-col-md";
                                                break;
                                            default:
                                                continue 2; // salta tutti gli altri campi
                                        }
                                        ?>
                                        <th class="<?php echo $class; ?>"><?php echo $label; ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($events as $riga): ?>
                                    <tr>
                                        <?php foreach ($th as $colonna): ?>
                                            <?php $valore = $riga[$colonna] ?? null; ?>
                                            <td>
                                                <?php
                                                if ($colonna === 'cv') {
                                                    if (!empty($valore)) {
                                                        echo '<a href="' . htmlspecialchars($valore) . '" class="btn btn-primary" download target="_blank">Scarica CV</a>';
                                                    }
                                                } elseif (is_string($valore) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $valore)) {
                                                    $data = DateTime::createFromFormat('Y-m-d', $valore);
                                                    echo $data ? $data->format('d/m/Y') : htmlspecialchars($valore);
                                                } elseif (is_string($valore) && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $valore)) {
                                                    $data = DateTime::createFromFormat('d/m/Y', $valore);
                                                    echo $data ? $data->format('d/m/Y') : htmlspecialchars($valore);
                                                } elseif (
                                                    filter_var($valore, FILTER_VALIDATE_URL) ||
                                                    (is_string($valore) && preg_match('/[\/\\\\].+\.[a-zA-Z0-9]{2,5}$/', $valore))
                                                ) {
                                                    echo '<a href="' . htmlspecialchars($valore) . '" class="btn btn-primary" download target="_blank">Scarica</a>';
                                                } else {
                                                    echo (is_null($valore) || $valore === '') ? '--' : htmlspecialchars($valore);
                                                }
                                                ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php } ?>
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