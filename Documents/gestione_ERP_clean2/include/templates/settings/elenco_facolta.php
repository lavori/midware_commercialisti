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
                    <?php if (isset($corsi[0]) && !empty($corsi[0])) { ?>

                        <?php
                        $th = array_keys($corsi[0]);
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
                                            case "facolta":
                                                $v = "Facoltà";
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
                                <?php foreach ($corsi as $k => $v): ?>
                                    <tr>
                                        <?php foreach ($v as $k2 => $v2): ?>
                                            <td>
                                                <?php
                                                // Controlla se il valore è una data valida in formato standard
                                                if (is_string($v2)) { // Il valore deve essere una stringa per tentare la formattazione
                                                    $v2_clean = trim($v2);
                                                    $formati_accettati = ['Y-m-d', 'd/m/Y', 'm/d/Y'];
                                                    $data_valida = false;

                                                    foreach ($formati_accettati as $formato) {
                                                        $data = DateTime::createFromFormat($formato, $v2_clean);
                                                        $errori = DateTime::getLastErrors();

                                                        // Se $v2_clean è una stringa che corrisponde a uno dei formati data
                                                        // e non ci sono errori di parsing...
                                                        if ($data && $errori['warning_count'] === 0 && $errori['error_count'] === 0) {
                                                            // ...allora stampa il valore formattato come data
                                                            echo $data->format('d/m/Y');
                                                            $data_valida = true;
                                                            break;
                                                        }
                                                    }

                                                    // Se $v2_clean non è stato riconosciuto come data valida...
                                                    if (!$data_valida) {
                                                        // ...allora stampa il valore originale (sanificato)
                                                        echo htmlspecialchars($v2_clean);
                                                    }
                                                } else {
                                                    // Se $v2 non è una stringa, stampa il valore originale (sanificato)
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
                                                                        onClick="apriModal('Modifica Facoltà','Gestione Facoltà','setting/form_facolta','<?php echo $v['id']; ?>')">
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
                    <div class="center card-footer" style="background-color: #fff; border-top:none;">
                        <a onClick="apriModal('Nuovo Facoltà','Gestione Facoltà','setting/form_facolta','new')"
                            class="btn btn-primary">Aggiungi Facoltà</a>
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