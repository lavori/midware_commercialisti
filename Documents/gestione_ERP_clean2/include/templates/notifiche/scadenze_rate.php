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
                <?php if (isset($message) && $message == 'insertRata') { ?>
                    <div class="example-alert">
                        <div class="alert alert-success alert-icon">
                            <em class="icon ni ni-check-circle"></em> <strong>Rata Pagata</strong> con successo
                        </div>
                    </div>
                <?php } ?>
                <div class="card-inner">
                    <?php if ($_SESSION['utente']['ruolo'] == 'Superadmin' || $_SESSION['utente']['ruolo'] == 'Admin') { ?>
                        <form method="POST">
                            <div class="col-12">
                                <div class="form-group">
                                    <div class="input-group">
                                        <select class="custom-select js-select2 flex-grow-1" data-search="on"
                                            name="cercaDiscenteRata" required>
                                            <option value="#" selected disabled>Seleziona Discente</option>
                                            <?php
                                            foreach ($discente as $p):
                                                $selected = (isset($_POST['cercaDiscenteRata']) && $_POST['cercaDiscenteRata'] == $p['id']) ? 'selected' : '';
                                                ?>
                                                <option value="<?php echo $p['id']; ?>" <?php echo $selected; ?>>
                                                    <?php echo htmlspecialchars($p['nome'] . ' ' . $p['cognome']); ?>
                                                </option>
                                            <?php endforeach; ?>
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
                        <table class="table datatable-init-export">
                            <thead>
                                <tr>
                                    <?php foreach ($th as $v): ?>
                                        <?php
                                        switch ($v) {
                                            case "id":
                                                continue 2;
                                            case "importoRata":
                                                $label = "Importo Rata";
                                                $class = "nk-tb-col tb-col-md";
                                                break;
                                            case "data_scadenza":
                                                $label = "Data Scadenza";
                                                $class = "nk-tb-col tb-col-md";
                                                break;
                                            case "nomeDiscente":
                                                $label = "Nome Discente";
                                                $class = "nk-tb-col tb-col-md";
                                                break;
                                            case "pagato":
                                                $label = "Pagato";
                                                $class = "nk-tb-col tb-col-md";
                                                break;
                                            case "tipologia":
                                                $label = "Tipo";
                                                $class = "nk-tb-col tb-col-md";
                                                break;
                                            default:
                                                continue 2; // salta tutti gli altri campi
                                        }
                                        ?>
                                        <th class="<?php echo $class; ?>"><?php echo $label; ?></th>
                                    <?php endforeach; ?>
                                    <th class="nk-tb-col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($events as $riga): ?>
                                    <tr>
                                        <?php foreach ($riga as $colonna => $valore): ?>
                                            <?php if (in_array($colonna, ['importoRata', 'data_scadenza', 'nomeDiscente', 'pagato','tipologia'])): ?>
                                                <td>
                                                    <?php
                                                    if ($colonna === 'tipologia') {
                                                        if (!empty($valore)) {
                                                            if($valore=='e-campus'){
                                                                echo 'E-campus';
                                                            }elseif($valore=='tutoraggio'){
                                                                echo 'Tutoraggio';
                                                            }
                                                        }
                                                    } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $valore)) {
                                                        $data = DateTime::createFromFormat('Y-m-d', $valore);
                                                        echo $data ? $data->format('d/m/Y') : htmlspecialchars($valore);
                                                    } elseif (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $valore)) {
                                                        $data = DateTime::createFromFormat('d/m/Y', $valore);
                                                        echo $data ? $data->format('d/m/Y') : htmlspecialchars($valore);
                                                    } elseif (
                                                        filter_var($valore, FILTER_VALIDATE_URL) ||
                                                        (is_string($valore) && preg_match('/[\/\\\\].+\.[a-zA-Z0-9]{2,5}$/', $valore))
                                                    ) {
                                                        echo '<a href="' . htmlspecialchars($valore) . '" class="btn btn-primary" download target="_blank">Scarica</a>';
                                                    } elseif ($colonna == 'pagato') {
                                                        if ($valore == 'Non Pagato') {
                                                            echo '<span style="color:red"><em class="icon ni ni-cross"></em> ' . $valore . '</span>';
                                                        } else {
                                                            echo '<span style="color:green"><em class="icon ni ni-check"></em> ' . $valore . '</span>';
                                                        }
                                                    } else {
                                                        echo htmlspecialchars($valore);
                                                    }
                                                    ?>
                                                </td>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                        <td>
                                            <?php if ($riga['pagato'] != 'Pagato') { ?>
                                                <a class="btn btn-primary"
                                                    href="/discenti/gestione/<?php echo base64_encode($riga['id_discente']); ?>/pagamento/<?php echo base64_encode($riga['id']); ?>">
                                                    Conferma Pagamento</a>
                                            <?php } else { ?>
                                                <a class="btn btn-primary"
                                                    href="<?php echo $riga['file'] ?>" target="_blank">
                                                    Visualizza Pagamento</a>
                                            <?php } ?>
                                        </td>
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
<!-- Form unico nella pagina -->
<form id="mainForm" action="<?php echo $serp; ?>" method="POST">
    <!-- Altri campi visibili opzionali -->
</form>