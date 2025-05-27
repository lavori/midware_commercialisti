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

<div class="nk-block nk-block-lg">
    <div class="card card-bordered card-preview">
        <?php if (isset($completato) && $completato == 'insert_ok') { ?>
            <div class="example-alert">
                <div class="alert alert-success alert-icon">
                    <em class="icon ni ni-check-circle"></em> Tassista aggiunto <strong>con successo</strong>
                </div>
            </div>
        <?php } elseif (isset($completato) && $completato == 'update_ok') { ?>
            <div class="example-alert">
                <div class="alert alert-success alert-icon">
                    <em class="icon ni ni-check-circle"></em> Tassista aggiornato <strong>con successo</strong>
                </div>
            </div>
        <?php } elseif (isset($completato) && $completato == 'delete_ok') { ?>
            <div class="example-alert">
                <div class="alert alert-success alert-icon">
                    <em class="icon ni ni-check-circle"></em> Tassista eliminato <strong>con successo</strong>
                </div>
            </div>
        <?php } ?>
        <div class="card-inner">
            <?php
            $th = array_keys($content_tabella[0]);
            ?>
            <table class="datatable-init table">
                <thead>
                    <tr>
                        <?php
                        foreach ($th as $k => $v):
                            if ($v != "id") :
                                $class = "nk-tb-col tb-col-lg";
                                // Inserisce uno spazio prima di ogni lettera maiuscola, eccetto se è la prima lettera della stringa.
                                $headerText = preg_replace('/(?<!^)([A-Z])/', ' $1', $v);
                                // Capitalizza la prima lettera della stringa risultante (es. "data Di Assunzione" diventa "Data Di Assunzione")
                                $headerText = ucfirst($headerText);
                            ?>
                            <th class="<?php echo $class; ?>"><?php echo $v; ?></th>
                        <?php endif; endforeach; ?>
                        <th class="nk-tb-col nk-tb-col-tools text-end"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($content_tabella as $k => $v): ?>
                        <tr>
                            <?php foreach ($v as $k2 => $v2): 
                                if($k2!='id'):?>
                                <td>
                                    <?php
                                    // Controlla se il valore è una data valida
                                    if (!empty($v2) && is_string($v2)) {
                                        try {
                                            // Tenta di creare un oggetto DateTime
                                            $dateObj = new DateTime($v2);
                                            // Verifica se la stringa originale era effettivamente una data/datetime nei formati comuni
                                            if ($dateObj && ($dateObj->format('Y-m-d') === $v2 || $dateObj->format('Y-m-d H:i:s') === $v2 || $dateObj->format('d/m/Y') === $v2)) {
                                                echo htmlspecialchars($dateObj->format('d/m/Y'));
                                            } else {
                                                // Se non corrisponde a un formato data atteso o strtotime darebbe un falso positivo
                                                echo htmlspecialchars($v2);
                                            }
                                        } catch (Exception $e) {
                                            // Se DateTime lancia un'eccezione, non è una data valida
                                            echo htmlspecialchars($v2);
                                        }
                                    } else {
                                        // Stampa il valore originale se non è una data
                                        echo $v2;
                                        // Stampa il valore originale se non è una stringa o è vuoto/null
                                        echo htmlspecialchars((string)$v2);
                                    }
                                    ?>
                                </td>
                            <?php endif; endforeach; ?>
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
                                                            onClick="apriModal('Modifica Tassisti','Gestione Tassisti','user/form_tassista','<?php echo $v['id']; ?>')">
                                                            <em class="icon ni ni-shield-star"></em>
                                                            <span>Info Tassista</span>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="<?php echo $serp; ?>?action=delete&id=<?php echo $v['id']; ?>"
                                                            onclick="return confermaDelete();">
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
            <br>
            <div class="center card-footer" style="background-color: #fff; border-top:none; margin-top:1.5rem;">
                <a onClick="apriModal('Nuovo Tassista','Gestione tassisti','user/form_tassista','new')"
                    class="btn btn-dim btn-warning">Aggiungi <?php echo $h2; ?></a>
            </div>
        </div>
    </div>
</div>


<!-- Form unico nella pagina -->
<form id="mainForm" action="<?php echo $serp; ?>" method="POST">
    <!-- Altri campi visibili opzionali -->
</form>



<script>
    document.addEventListener('DOMContentLoaded', function () {
        const deleteForms = document.querySelectorAll('form[action="/admin/tassisti"]');

        deleteForms.forEach(function (form) {
            form.addEventListener('submit', function (event) {
                const confirmDelete = confirm('Sei sicuro di voler eliminare questo tassista?');
                if (!confirmDelete) {
                    event.preventDefault(); // Annulla l'invio del form
                }
            });
        });
    });
</script>