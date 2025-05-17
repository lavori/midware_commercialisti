<?php 
    include('../../config.php');
    require_once ('../../db.php');
     
    // Inizializzazione del database una sola volta
    $database = new Database($host, $username, $password, $db); 
    $database->connect();
    $data=$_POST['id'];
    $data_arr=explode('|',$data);

    $query = "SELECT 
                ct.contabilizzato, 
                ct.data, 
                ct.giorno_settimana, 
                ct.valore_corrispettivo,  
                COUNT(DISTINCT it.tassista_id) AS numero_tassisti
              FROM corrispettivi_taxi ct
              LEFT JOIN incassi_taxi it ON ct.data = it.data_incasso
              WHERE ct.data='".$data_arr[0]."'"; 
    $dati_corrispettivo=$database->query($query);
    $print=$dati_corrispettivo[0];
    $database->disconnect();
    $giorni_italiani = [
        "Monday" => "Lunedì",
        "Tuesday" => "Martedì",
        "Wednesday" => "Mercoledì",
        "Thursday" => "Giovedì",
        "Friday" => "Venerdì",
        "Saturday" => "Sabato",
        "Sunday" => "Domenica"
    ];
    $gg=$print['giorno_settimana'];
    $giorno=$giorni_italiani[$gg];
    $data_obj = new DateTime($print['data']);
    $data_it= $data_obj->format('d/m/Y');
?>
<div class="nk-block">
    <div class="row g-gs">
        <div class="col-lg-6 col-xl-6 col-xxl-6">
            <div class="card card-bordered">
                <div class="card-inner-group">
                    <div class="card-inner">
                        <div class="user-card user-card-s2">
                            <div class="user-info">
                                <div class="badge bg-light rounded-pill ucap">Corrispettivo di</div>
                                <h5><?php echo $giorno." - ".$data_it; ?></h5>
                                <span class="sub-text">Incasso generato da &nbsp;<strong><?php echo $print['numero_tassisti']; ?></strong>&nbsp; taxisti</span>
                            </div>
                        </div>
                    </div>
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
                    <input type="hidden" name="tipo_action" value="modifica_corrispettivo">
                    <input type="hidden" name="da" value="<?php echo $data_arr[1]; ?>">
                    <input type="hidden" name="a" value="<?php echo $data_arr[2]; ?>">
                    <input type="hidden" name="tassista_id" value="0">
                    <input type="hidden" name="data_corrispettivo" value="<?php echo $data_arr[0]; ?>">
                    
                    <div class="form-group">
                        <label class="form-label" for="corrispettivo">Corrispettivo</label>
                        <div class="form-control-wrap">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">&euro;</span>
                                </div>
                                <input type="text" class="form-control" name="corrispettivo" id="corrispettivo" value="<?php echo $print['valore_corrispettivo']; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-lg btn-primary">Modifica corrispettivo</button>
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