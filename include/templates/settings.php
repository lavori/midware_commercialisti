
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

                    <?php foreach($content as $key => $value): ?>
                        <div class="col-md-<?php echo $value[0]; ?>">
                            <div class="card card-bordered card-preview">
                                <div class="card-inner">
                                    <div class="card-head">
                                        <h6 class="title"><?php echo $key; ?></h6>
                                    </div>
                                    <div class="card-inner">
                                        <?php 
                                            $righe=$value[1];
                                            $th=array_keys($righe[0]);
                                        ?>
                                        <table class="datatable-init table">
                                            <thead>
                                                <tr>
                                                <?php foreach($th as $k => $v): if($v!='sub'): ?>
                                                    <th><?php echo $v; ?></th>
                                                <?php endif; endforeach; ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach($righe as $k => $v): ?>
                                                <tr>
                                                <?php foreach($v as $k2 => $v2):  if($k2!='sub'):?>
                                                    <td>
                                                    <?php
                                                        // Controlla se il valore è una data valida
                                                        if (!empty($v2) && is_string($v2)) {
                                                            try {
                                                                // Prova a creare un oggetto DateTime con il formato ISO 8601 (formato più comune)
                                                                $data = new DateTime($v2);
                                                        
                                                                // Se la data è valida, formattala nel modo desiderato
                                                                echo $data->format('d/m/Y');
                                                            } catch (Exception $e) {
                                                                // Se non è una data valida, stampa il valore originale
                                                                echo htmlspecialchars($v2, ENT_QUOTES, 'UTF-8');
                                                            }
                                                        } else {
                                                            // Se $v2 è vuoto o non è una stringa, stampalo direttamente
                                                            if (!empty($v2)) {
                                                                echo htmlspecialchars((string) $v2, ENT_QUOTES, 'UTF-8');
                                                            } else {
                                                                echo ''; // Output vuoto per valori nulli o non validi
                                                            }
                                                        }
                                                        
                                                    ?>
                                                    </td>
                                                <?php endif; endforeach; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="center card-footer" style="background-color: #fff; border-top:none; margin-top:1.5rem;">
                                        <a href="<?php echo $value[2]; ?>" class="btn btn-dim btn-warning">Gestisci <?php echo $key; ?></a>
                                    </div>
                                </div>
                            </div><!-- .card-preview -->
                        </div>
                    <?php endforeach; ?>

                    </div>


                </div>
                






