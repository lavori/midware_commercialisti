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
                <!--Stampa messaggi di errore e di corretto inserimento-->
                <?php if (isset($message)): ?>
                    <?php switch ($message):
                        case 'insert': ?>
                            <div class="example-alert">
                                <div class="alert alert-success alert-icon">
                                    <em class="icon ni ni-check-circle"></em>
                                    <strong>Dato inserito</strong> con successo
                                </div>
                            </div>
                            <?php break;
                        case 'update': ?>
                            <div class="example-alert">
                                <div class="alert alert-success alert-icon">
                                    <em class="icon ni ni-check-circle"></em>
                                    <strong>Dato aggiornato</strong> con successo
                                </div>
                            </div>
                            <?php break;
                        case 'delete': ?>
                            <div class="example-alert">
                                <div class="alert alert-success alert-icon">
                                    <em class="icon ni ni-check-circle"></em>
                                    <strong>Dato eliminato</strong> con successo
                                </div>
                            </div>
                            <?php break;
                    endswitch; ?>
                <?php endif; ?>
                <div class="card-inner">

                    <?php $th = array_keys($content[0]); ?>
                    <table class="datatable-init table">

                        <thead>
                            <tr>
                                <?php
                                foreach ($th as $k => $v):
                                    switch ($v) {
                                            case "id_ruolo":
                                                $v = "";
                                                $class = "nk-tb-col tb-col-md";
                                                break;
                                            case "ruolo":
                                                $v = "Ruolo";
                                                $class = "nk-tb-col tb-col-md";
                                                break;
                                            case "permessi":
                                                $v = "Permessi";
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
                            <?php foreach ($content as $k => $v): ?>
                                <tr>
                                    <?php foreach ($v as $k2 => $v2): ?>
                                        <td>
                                            <?php
                                            // Controlla se il valore è una data valida
                                            if (isset($v2) && strtotime($v2)) {
                                                // Crea un oggetto DateTime dal valore e stampa nel formato desiderato
                                                $data = new DateTime($v2);
                                                echo $data->format('d/m/Y');
                                            } else {
                                                // Stampa il valore originale se non è una data
                                                if ($v2 != "")
                                                    echo $v2;
                                                else
                                                    echo "Nessun Permesso";
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
                                                                    onClick="apriModal('Modifica Ruolo','Gestione ruoli','permessi/form_ruoli','<?php echo $v['id_ruolo']; ?>')">
                                                                    <em class="icon ni ni-shield-star"></em>
                                                                    <span>Modifica</span>
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a href="<?php echo $serp; ?>?action=delete&id=<?php echo $v['id_ruolo']; ?>"
                                                                    onclick="confermaNavigazione();">
                                                                    <em class="icon ni ni-shield-star"></em>
                                                                    <span>Cancella</span>
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
                    <div class="center card-footer" style="background-color: #fff;border-top:none;">
                        <a onClick="apriModal('Nuovo Ruolo','Gestione Ruoli','permessi/form_ruoli','new')"
                            class="btn btn-primary">Aggiungi Ruolo</a>
                    </div>
                </div>
            </div><!-- .card-preview -->
        </div>

    </div>
</div>
<!-- content @s -->
<!-- Form unico nella pagina -->
<form id="mainForm" action="<?php echo $serp; ?>" method="POST">
    <!-- Altri campi visibili opzionali -->
</form>