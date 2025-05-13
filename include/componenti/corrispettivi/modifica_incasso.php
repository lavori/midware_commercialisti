<?php 
    include('../../config.php');
    require_once ('../../db.php');
     
    // Inizializzazione del database una sola volta
    $database = new Database($host, $username, $password, $db); 
    $database->connect();
    $data=$_POST['id'];
    $data_arr=explode('|',$data);

    $query="SELECT
                tassisti.Nome as Nome,
                tassisti.Cognome as Cognome,
                incassi.valore_incasso as Incasso,
                tassisti.`Licenza di guida` as Licenza,
                tassisti.Telefono as Telefono,
                tassisti.Email AS Email,
                tassisti.`Targa Taxi` as Targa,
                tassisti.`Turni di lavoro` as Turni,
                tassisti.Dimissioni as Dimissioni
            FROM
                `incassi_taxi` AS incassi
            LEFT JOIN
                tassisti ON tassisti.id = incassi.tassista_id
            WHERE
                incassi.data_incasso = '$data_arr[0]'
            AND
                incassi.tassista_id = '$data_arr[1]';"; 
    $dati_incasso=$database->query($query);
    $print=$dati_incasso[0];
    $database->disconnect();
?>
<div class="nk-block">
    <div class="row g-gs">
        <div class="col-lg-6 col-xl-6 col-xxl-6">
            <div class="card card-bordered">
                <div class="card-inner-group">
                    <div class="card-inner">
                        <div class="user-card user-card-s2">
                            <div class="user-info">
                                <div class="badge bg-light rounded-pill ucap">Tassista</div>
                                <h5><?php echo $print['Nome']." ".$print['Cognome']; ?></h5>
                                <span class="sub-text"><?php echo $print['Email']; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="card-inner card-inner-sm">
                        <ul class="btn-toolbar justify-center gx-1">
                            <li><a href="tel:<?php echo $print['Telefono']; ?>" class="btn btn-trigger btn-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo $print['Telefono']; ?>"><em class="icon ni ni-call"></em></a></li>
                            <li><a href="mailto:<?php echo $print['Email']; ?>" class="btn btn-trigger btn-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo $print['Email']; ?>"><em class="icon ni ni-mail"></em></a></li>
                        </ul>
                    </div>
                    <div class="card-inner">
                        <div class="row text-center">
                            <div class="col-12">
                                <div class="profile-stats">
                                    <span class="amount">Turni</span>
                                    <span class="sub-text">
                                        <?php
                                            $turni_serializzati = $print['Turni'];
                                            $turni_array = @unserialize($turni_serializzati); // Usa @ per sopprimere errori se la stringa non è valida
                                            if (is_array($turni_array)) {
                                                echo htmlspecialchars(implode(' | ', $turni_array), ENT_QUOTES, 'UTF-8');
                                            } else {
                                                echo 'N/D'; // O mostra la stringa originale o un messaggio di errore
                                            }
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div> 
                    <!--.card-inner -->
                    <div class="card-inner">
                        <h6 class="overline-title mb-2">Dettagli</h6>
                        <div class="row g-3">
                            <div class="col-sm-6 col-md-6 col-lg-4">
                                <span class="sub-text">Nominativo:</span>
                                <span><?php echo $print['Nome']." ".$print['Cognome']; ?></span>
                            </div>
                            <div class="col-sm-6 col-md-6 col-lg-4">
                                <span class="sub-text">Licenza:</span>
                                <span><?php echo $print['Licenza']; ?></span>
                            </div>
                            <div class="col-sm-6 col-md-6 col-lg-4">
                                <span class="sub-text">Targa Taxi:</span>
                                <span><?php echo $print['Targa']; ?></span>
                            </div>
                            <?php if($print['Dimissioni']!=''): ?>
                            <div class="col-sm-6 col-md-6 col-lg-4">
                                <span class="sub-text">Data fine rapporto:</span>
                                <span class="lead-text text-success" style="color:#FF0000;"><strong><?php echo $print['Dimissioni']; ?></strong></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div><!-- .card-inner -->
                </div>
            </div>
        </div><!-- .col -->
        <div class="col-lg-6 col-xl-6 col-xxl-6">
            <div class="card card-bordered">
                <div class="card-inner">
                    <div class="user-card user-card-s2">
                        <div class="user-info">
                            <div class="badge bg-light rounded-pill ucap">Incasso da modificare</div>
                                <h5><?php echo $data_arr[0]; ?></h5>
                            </div>
                        </div>
                    </div>
                </div><!-- .card-inner -->
                <div class="card-inner">
                <form action="/corrispettivi/taxisti" method="post">
                    <input type="hidden" name="tipo_action" value="modifica_incasso">
                    <input type="hidden" name="da" value="<?php echo $data_arr[2]; ?>">
                    <input type="hidden" name="a" value="<?php echo $data_arr[3]; ?>">
                    <input type="hidden" name="tassista_id" value="<?php echo $data_arr[1]; ?>">
                    <input type="hidden" name="data_incasso" value="<?php echo $data_arr[0]; ?>">
                    
                    <div class="form-group">
                        <label class="form-label" for="incasso">Incasso</label>
                        <div class="form-control-wrap">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">&euro;</span>
                                </div>
                                <input type="text" class="form-control" name="incasso" id="incasso" value="<?php echo $print['Incasso']; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-lg btn-primary">Modifica incasso</button>
                    </div>
                </form>
                </div><!-- .card-inner -->
            </div><!-- .card -->
        </div><!-- .col -->
    </div><!-- .row -->
</div><!-- .nk-block -->
<style>
    .modal.show .modal-dialog {
        display: block !important;
    }
    .modal-content{
        width: 800px !important;
    }
    </style>