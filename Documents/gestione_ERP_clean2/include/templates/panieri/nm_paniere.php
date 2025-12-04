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
    <div class="row g-gs my-3">
        <div class="col-md-12">
            <div class="card card-bordered card-preview">
                <?php if (isset($message) && $message == 'UpdatePaniere') { ?>
                    <div class="example-alert">
                        <div class="alert alert-success alert-icon">
                            <em class="icon ni ni-check-circle"></em> <strong>Aggiornamento avvenuto</strong> con successo
                        </div>
                    </div>
                <?php } ?>
                <div class="card-inner">
                    <form id="form_quesito" class="form-validate is-alter" method="POST" enctype="multipart/form-data">
                        <!-- Esame/i -->
                         <?php
                            $esami_selezionati = [];
                            if (isset($update) && isset($panieri[0]['esami'])) {
                                // Rimuovo la virgola iniziale/finale e separo in array
                                $esami_selezionati = array_filter(explode(',', $panieri[0]['esami']));
                            }
                        ?>
                        <div class="form-group">
                            <label class="form-label" for="esami">Esame/i</label>
                            <div class="form-control-wrap">
                                <select class="form-select js-select2" id="esami" name="esami[]" multiple required>
                                    <?php foreach ($esami as $v) { ?>
                                        <option value="<?php echo htmlspecialchars($v['id']); ?>"
                                            <?php if (in_array($v['id'], $esami_selezionati)) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($v['codice'] . " - " . $v['nome']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <!-- Domanda e Immagine -->
                        <div class="row g-gs my-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="domanda">Quesito</label>
                                    <div class="form-control-wrap">
                                        <input type="text" class="form-control" id="domanda" name="domanda" required value="<?php echo isset($panieri[0]['domanda']) ? $panieri[0]['domanda'] : ''; ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="img-domanda">Immagine Quesito</label>
                                    <div class="form-control-wrap">
                                        <div class="form-file">
                                            <input type="file" class="form-file-input" id="img-domanda" name="img-domanda" accept=".jpg"                                                        
                                                <?php if(!empty($panieri[0]['img_domanda'])){echo'value="'.$panieri[0]['img_domanda'].'"';}?>>
                                            <label class="form-file-label" for="img-domanda">
                                                Seleziona immagine a corredo della domanda (Opzionale)
                                            </label>
                                            <?php
                                                if (!empty($panieri[0]['img_domanda'])){ 
                                                    echo '<a href='.$panieri[0]['img_domanda'].' target="_blank"> Visualizza immagine presente</a>';
                                                 }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tipo Quesito -->
                        <div class="form-group">
                            <label class="form-label d-block">Tipo Quesito</label>
                            <div class="form-control-wrap">
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" class="custom-control-input" id="tipo1" name="tipo" value="1" <?php echo (!isset($update) || (isset($panieri[0]['ra']) && empty($panieri[0]['ra']))) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="tipo1">Risposta Multipla</label>
                                </div>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" class="custom-control-input" id="tipo2" name="tipo" value="2" <?php echo (isset($panieri[0]['ra']) && !empty($panieri[0]['ra'])) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="tipo2">Risposta Aperta</label>
                                </div>
                            </div>
                        </div>

                        <!-- Risposte Multiple -->
                        <div class="nk-block nk-block-lg" id="risposta_multipla">
                            <div class="row g-gs my-3">
                                <?php
                                for ($i = 1; $i <= 4; $i++) {
                                    $r = 'r' . $i;
                                    $img_r = 'img_r' . $i;
                                    $rc = 'rc' . $i;
                                    ?>
                                    <div class="col-md-6">
                                        <div class="card-inner card-bordered card-preview mb-3" style="border-radius: 6px;">
                                            <div class="form-group">
                                                <label class="form-label" for="<?php echo $r; ?>">Risposta <?php echo $i; ?></label>
                                                <div class="form-control-wrap">
                                                    <input type="text" class="form-control" id="<?php echo $r; ?>" name="<?php echo $r; ?>" value="<?php echo isset($panieri[0][$r]) ? htmlspecialchars($panieri[0][$r]) : ''; ?>">
                                                    <div class="form-file mt-2">
                                                        <input type="file" accept=".jpg" class="form-file-input" id="img-<?php echo $r; ?>" name="img-<?php echo $r; ?>" 
                                                        <?php if(!empty($panieri[0][$img_r])){echo'value="'.$panieri[0][$img_r].'"';}?>>
                                                        <label class="form-file-label" for="<?php echo $img_r?>">
                                                            Seleziona immagine a corredo della domanda (Opzionale)
                                                        </label>
                                                        <?php
                                                            if (!empty($panieri[0][$img_r])){ 
                                                                echo '<a href='.$panieri[0][$img_r].' target="_blank"> Visualizza immagine presente</a>';
                                                            }
                                                        ?>
                                                    </div>
                                                    <div class="mt-2">
                                                        <input type="radio" class="form-check-input" id="rc<?php echo $i; ?>" name="rc" value="<?php echo $i; ?>" <?php echo (isset($panieri[0]['rc']) && $panieri[0]['rc'] == "$i") ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="rc<?php echo $i; ?>">Risposta Corretta</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>

                        <!-- Risposta Aperta -->
                        <div class="nk-block nk-block-lg" id="risposta_aperta">
                            <div class="card-inner card-bordered card-preview mb-3" style="border-radius: 6px;">
                                <div class="form-group">
                                    <label class="form-label" for="ra">Risposta Aperta</label>
                                    <div class="form-control-wrap">
                                        <textarea class="form-control" id="ra" name="ra"><?php echo isset($panieri[0]['ra']) ? trim($panieri[0]['ra']) : ''; ?></textarea>
                                        <div class="form-file mt-2">
                                            <input type="file" accept=".jpg" class="form-file-input" id="img-ra" name="img-ra">
                                            <label class="form-file-label" for="img-ra">
                                                            Seleziona immagine a corredo della domanda (Opzionale)
                                            </label>
                                            <?php
                                                if (!empty($panieri[0]['img-ra'])){ 
                                                    echo '<a href='.$panieri[0]['img-ra'].' target="_blank"> Visualizza immagine presente</a>';
                                                }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="form-group text-end center">
                            <?php if(isset($panieri[0]['id'])){?>
                                <input type="hidden" name="id" value="<?php echo $panieri[0]['id']; ?>">    
                            <?php } ?>
                            <input type="submit" class="btn btn-lg btn-primary" value="Salva Quesito">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- content @s -->
<!-- Form unico nella pagina -->
<form id="mainForm" action="<?php echo $serp; ?>" method="POST">
    <!-- Altri campi visibili opzionali -->
</form>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tipoRadioButtons = document.querySelectorAll('input[name="tipo"]');
        const rispostaMultiplaDiv = document.getElementById('risposta_multipla');
        const rispostaApertaDiv = document.getElementById('risposta_aperta');
        const form = document.querySelector('form');

        // Funzione per mostrare/nascondere i blocchi
        function toggleVisibility() {
            const selectedRadio = document.querySelector('input[name="tipo"]:checked');
            const isAperta = selectedRadio && selectedRadio.value === '2';

            rispostaApertaDiv.classList.toggle('d-none', !isAperta);
            rispostaMultiplaDiv.classList.toggle('d-none', isAperta);
        }

        // Aggiungi listener ai radio button
        tipoRadioButtons.forEach(radio => {
            radio.addEventListener('change', toggleVisibility);
        });

        // Inizializza la visibilità al caricamento
        toggleVisibility();

        // Validazione al submit
        form.addEventListener('submit', function (e) {
            const selectedRadio = document.querySelector('input[name="tipo"]:checked');
            const isMultipla = selectedRadio && selectedRadio.value === '1';

            if (isMultipla) {
                const checked = form.querySelector('input[name="rc"]:checked');
                if (!checked) {
                    e.preventDefault();
                    alert("Devi selezionare almeno una risposta corretta per i quesiti a risposta multipla.");
                }
            }
        });
    });


</script>