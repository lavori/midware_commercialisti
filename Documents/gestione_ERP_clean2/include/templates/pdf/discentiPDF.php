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
            <?php //echo'<pre>';print_r($anDiscenti);echo'</pre>'; ?>
            <div class="card card-bordered card-preview">
                <div class="card-inner">
                    <?php if(isset($anDiscenti) && !empty($anDiscenti)){ ?>
                        <?php
                            $th = array_keys($anDiscenti[0]);
                        ?>
                        <table class="table datatable-init">
                            <thead>
                                <tr>
                                    <?php foreach ($th as $k => $v):
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
                                            case "documento_unito":
                                                $v = "Documento Unito";
                                                $class = "nk-tb-col tb-col-md";
                                                break;
                                            default:
                                                $class = "nk-tb-col tb-col-lg";
                                                break;
                                        }
                                        ?>
                                        <?php if ($v != 'id_discente') { // Escludi la colonna "esami" ?>
                                        <th class="<?php echo $class; ?>"><?php echo $v; ?></th>
                                        <?php } ?>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($anDiscenti as $k => $v): ?>
                                    <tr>
                                        <?php foreach ($v as $k2 => $v2): ?>
                                            <?php if ($k2 != 'id_discente') { ?>
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
                                                elseif ($v2 != "") {
                                                    echo htmlspecialchars($v2);
                                                }
                                                ?>
                                            </td>
                                            <?php } ?>
                                        <?php endforeach; ?>
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