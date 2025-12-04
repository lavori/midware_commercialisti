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
                    <?php if (isset($enti) && !empty($enti)) { ?>
                        <?php
                        $th = array_keys($enti[0]);
                        ?>
                        <table class="table datatable-init-export">
                            <thead>
                                <tr>
                                    <?php foreach ($th as $v):
                                        if ($v === 'tipo')
                                            continue; // ❌ salta "tipo"
                                
                                        switch ($v) {
                                            case "id":
                                                $label = "";
                                                $class = "nk-tb-col tb-col-md";
                                                break;
                                            case "data_scadenza":
                                                $label = "Data Scadenza";
                                                $class = "nk-tb-col tb-col-lg";
                                                break;
                                            case "nome":
                                                $label = "Nome";
                                                $class = "nk-tb-col tb-col-lg";
                                                break;
                                            default:
                                                $label = ucfirst($v);
                                                $class = "nk-tb-col tb-col-lg";
                                                break;
                                        } ?>
                                        <th class="<?php echo $class; ?>"><?php echo $label; ?></th>
                                    <?php endforeach; ?>
                                    <th class="nk-tb-col nk-tb-col-tools text-end"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($enti as $riga): ?>
                                    <tr>
                                        <?php foreach ($riga as $k2 => $v2): ?>
                                            <?php if ($k2 === 'tipo')
                                                continue; // ❌ non stampare colonna "tipo" ?>

                                            <td>
                                                <?php
                                                // 👇 Se siamo nel campo 'perc_costo' e c'è anche 'tipo'
                                                if ($k2 === 'perc_costo') {
                                                    $tipo = $riga['tipo'] ?? '';
                                                    $simbolo = ($tipo === 'percentuale') ? '%' : '€';
                                                    echo htmlspecialchars($v2) . ' ' . $simbolo;
                                                    continue;
                                                }

                                                // Scarica CV
                                                if ($k2 === 'cv' && !empty($v2)) {
                                                    echo '<a href="' . htmlspecialchars($v2) . '" class="btn btn-primary" download target="_blank">Scarica CV</a>';
                                                }
                                                // Data in formato YYYY-MM-DD
                                                elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $v2)) {
                                                    $data = DateTime::createFromFormat('Y-m-d', $v2);
                                                    echo $data ? $data->format('d/m/Y') : htmlspecialchars($v2);
                                                }
                                                // Data in formato DD/MM/YYYY
                                                elseif (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $v2)) {
                                                    $data = DateTime::createFromFormat('d/m/Y', $v2);
                                                    echo $data ? $data->format('d/m/Y') : htmlspecialchars($v2);
                                                }
                                                // URL o file
                                                elseif (
                                                    filter_var($v2, FILTER_VALIDATE_URL) ||
                                                    (is_string($v2) && preg_match('/[\/\\\\].+\.[a-zA-Z0-9]{2,5}$/', $v2))
                                                ) {
                                                    echo '<a href="' . htmlspecialchars($v2) . '" class="btn btn-primary" download target="_blank">Scarica</a>';
                                                }
                                                // Default
                                                else {
                                                    echo htmlspecialchars($v2);
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
                                                                        onClick="apriModalLarge('Modifica Enti','Gestione Enti','enti/form_enti','<?php echo $riga['id']; ?>')">
                                                                        <em class="icon ni ni-shield-star"></em>
                                                                        <span>Modifica Enti</span>
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

                    <div class="center card-footer" style="background-color: #fff; border-top:none;">
                        <a onClick="apriModalLarge('Nuovo Enti','Gestione Enti','enti/form_enti','new')"
                            class="btn btn-primary">Aggiungi Enti</a>
                    </div>
                </div>
            </div>
        </div><!-- .card-preview -->
    </div>
</div>
<!-- Form unico nella pagina -->
<form id="mainForm" action="<?php echo $serp; ?>" method="POST">
    <!-- Altri campi visibili opzionali -->
</form>