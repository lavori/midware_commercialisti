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
                <!--Stampa messaggi di errore e di corretto inserimento-->
                <?php if (isset($message)): ?>
                <?php endif; ?>
                <!--Sviluppo tabella-->
                <div class="card-inner">
                    <?php if (isset($certificazioni) && !empty($certificazioni)) { ?>
                        <?php
                        $th = array_keys($certificazioni[0]);
                        ?>
                        <table class="table datatable-init-export">
                            <thead>
                                <tr>
                                    <th class="nk-tb-col">Nome Certificazione</th>
                                    <th class="nk-tb-col">Ente</th>
                                    <th class="nk-tb-col">Costo</th>
                                    <th class="nk-tb-col">Svolgimento</th>
                                    <th class="nk-tb-col">Validità</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($certificazioni as $u): ?>
                                    <tr>
                                        <?php
                                        $ordine = ['nome', 'nome_ente', 'costo', 'svolgimento', 'validita'];
                                        foreach ($ordine as $col):
                                            $val = $u[$col] ?? null;

                                            ?>
                                            <td>
                                                <?php
                                                if ($col === 'cv' && !empty($val)) {
                                                    echo '<a href="' . htmlspecialchars($val) . '" class="btn btn-primary" download target="_blank">Scarica CV</a>';
                                                } elseif ($col === 'validita') {
                                                    if ($val >= 2) {
                                                        echo $val . " anni";
                                                    } else {
                                                        echo $val . " anno";
                                                    }
                                                } elseif ($col === 'costo') {
                                                    echo $val . " " . $u['tipo_ente'];
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
                                                                        onClick="apriModalLarge('Modifica Certificazioni','Gestione Certificazioni','setting/form_certificazioni','<?php echo $u['id']; ?>')">
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
                        <a onClick="apriModalLarge('Nuova Certificazione','Gestione Certificazioni','setting/form_certificazioni','new')"
                            class="btn btn-primary">Aggiungi Certificazione</a>
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
<script>

    function submitCertificazione() {
        const form = document.getElementById('form_certificazioni');
        const piattaforma = document.getElementById('piattaforma')?.value.trim();
        const url = document.getElementById('url_piattaforma')?.value.trim();
        const user = document.getElementById('username_piattaforma')?.value.trim();
        const pwd = document.getElementById('pwd_piattaforma')?.value.trim();

        const unoCompilato = piattaforma !== '' || url !== '' || user !== '' || pwd !== '';

        if (unoCompilato && (piattaforma === '' || url === '' || user === '' || pwd === '')) {
            alert('Se compili uno dei campi piattaforma, devi compilare anche gli altri.');
            return;
        }

        // Puoi anche aggiungere eventuali validazioni aggiuntive qui

        // Invio sicuro del form
        form.submit();
    }

    function aggiornaLabelCosto() {
        const selectPagamento = document.getElementById('tipoPagamento');
        const labelCosto = document.querySelector('label[for="costo"], label[for="percentuale"]');
        const input = document.querySelector('#costo, #percentuale');
        if (!selectPagamento || !labelCosto || !input) return;

        if (selectPagamento.value === 'Indiretto') {
            labelCosto.textContent = 'Percentuale (Costo Variabile, Es: 15)';
            labelCosto.setAttribute('for', 'percentuale');
            input.setAttribute('id', 'percentuale');
            input.setAttribute('name', 'percentuale');
        } else {
            labelCosto.textContent = 'Costo (Costo Fisso, Es: 180.00)';
            labelCosto.setAttribute('for', 'costo');
            input.setAttribute('id', 'costo');
            input.setAttribute('name', 'costo');
        }
    }

</script>