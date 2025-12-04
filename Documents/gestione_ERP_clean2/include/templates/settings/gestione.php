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
                    <?php if (isset($tutor) && !empty($tutor)) { ?>
                        <?php
                        $th = array_keys($tutor[0]);
                        ?>
                        <table class="table datatable-init-export">
                            <thead>
                                <tr>
                                    <?php
                                    foreach ($th as $k => $v):
                                        switch ($v) {
                                            case "id":
                                                $v = "";
                                                $class = "nk-tb-col tb-col-md";
                                                break;
                                            case "nome":
                                                $v = "Nome";
                                                $class = "nk-tb-col tb-col-md";
                                                break;
                                            case "cognome":
                                                $v = "Cognome";
                                                $class = "nk-tb-col tb-col-md";
                                                break;
                                            case "email":
                                                $v = "Email";
                                                $class = "nk-tb-col tb-col-md";
                                                break;
                                            case "telefono":
                                                $v = "Telefono";
                                                $class = "nk-tb-col tb-col-md";
                                                break;
                                            case "cv":
                                                $v = "CV";
                                                $class = "nk-tb-col tb-col-md";
                                                break;
                                            default:
                                                $class = "nk-tb-col tb-col-lg";
                                                break;
                                        }
                                        ?>
                                        <th class="<?php echo $class; ?>"><?php echo $v; ?></th>
                                    <?php endforeach; ?>
                                    <th class="nk-tb-col nk-tb-col-tools text-end"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tutor as $k => $v): ?>
                                    <tr>
                                        <?php foreach ($v as $k2 => $v2): ?>
                                            <td>
                                                <?php
                                                // Se è il campo "paniere"
                                                if ($k2 === 'cv') {
                                                    if (!empty($v2)) {
                                                        echo '<a href="' . htmlspecialchars($v2) . '" class="btn btn-primary" download target="_blank">Scarica CV</a>';
                                                    }
                                                }
                                                // Se è una data formato YYYY-MM-DD
                                                elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $v2)) {
                                                    $data = DateTime::createFromFormat('Y-m-d', $v2);
                                                    echo $data ? $data->format('d/m/Y') : htmlspecialchars($v2);
                                                }
                                                // Se è una data formato DD/MM/YYYY
                                                elseif (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $v2)) {
                                                    $data = DateTime::createFromFormat('d/m/Y', $v2);
                                                    echo $data ? $data->format('d/m/Y') : htmlspecialchars($v2);
                                                }
                                                // Se è un URL o un percorso file (con estensione)
                                                elseif (
                                                    filter_var($v2, FILTER_VALIDATE_URL) ||
                                                    (is_string($v2) && preg_match('/[\/\\\\].+\.[a-zA-Z0-9]{2,5}$/', $v2))
                                                ) {
                                                    echo '<a href="' . htmlspecialchars($v2) . '" class="btn btn-primary" download target="_blank">Scarica</a>';
                                                }
                                                // Altrimenti, stampa normalmente
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
                                                            data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                                                        <div class="dropdown-menu dropdown-menu-end">
                                                            <ul class="link-list-opt no-bdr">
                                                                <li>
                                                                    <a style="cursor: pointer;"
                                                                        onClick="apriModal('Modifica Tutor','Gestione Tutor','setting/form_tutor','<?php echo $v['id']; ?>')">
                                                                        <em class="icon ni ni-shield-star"></em>
                                                                        <span>Modifica</span>
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a style="cursor: pointer;"
                                                                        onClick="apriModal('Visualizza Esami','Gestione Tutor','setting/tutor_esami','<?php echo $v['id']; ?>')">
                                                                        <em class="icon ni ni-shield-star"></em>
                                                                        <span>Visualizza Esami</span>
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
                        <a onClick="apriModal('Nuovo Tutor','Gestione Tutor','setting/form_tutor','new')"
                            class="btn btn-primary">Aggiungi Tutor</a>
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