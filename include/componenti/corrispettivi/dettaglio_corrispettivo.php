<?php 
    include('../../config.php');
    require_once ('../../db.php');
     
    // Inizializzazione del database una sola volta
    $database = new Database($host, $username, $password, $db); 
    $database->connect();
    $data=$_POST['id'];

    $query="SELECT
                tassisti.Nome as Nome,
                tassisti.Cognome as Cognome,
                incassi.valore_incasso as Incasso,
                tassisti.`Licenza di guida` as Licenza,
                CONCAT(tassisti.Telefono, ' | ', tassisti.Email) AS Contatti,
                tassisti.`Targa Taxi` as Targa
            FROM
                `incassi_taxi` AS incassi
            LEFT JOIN
                tassisti ON tassisti.id = incassi.tassista_id
            WHERE
                `data_incasso` = '$data';";
    $incassi=$database->query($query);
    $th = array_keys($incassi[0]);
    $database->disconnect();
?>
 <table class="datatable-init-export table" style="width: 100%;">
    <thead>
        <tr>
        <?php 
            foreach($th as $k => $v): 
                // Modifica la stringa prima della stampa
                $stringa_modificata = str_replace('_', ' ', $v); 
                $stringa_modificata = ucwords($stringa_modificata); 
        ?>
            <th class="nk-tb-col tb-col-sm"><?php echo htmlspecialchars($stringa_modificata, ENT_QUOTES, 'UTF-8'); ?></th>
        <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
    <?php foreach($incassi as $k => $v): ?>
        <tr>
            <?php foreach($v as $k2 => $v2): ?>
            <td class="vm nk-tb-col tb-col-sm">
                <?php
                    if ($k2 == "Contatti") {
                        // separa telefono da email
                        $contatti = explode(" | ", $v2);
                        echo'<ul class="nk-tb-actions"><li class="nk-tb-action">';
                        echo'<a href="mailto:'.$contatti[1].'" class="btn btn-trigger btn-icon" data-bs-toggle="tooltip" data-bs-placement="top" aria-label="'.$contatti[1].'" data-bs-original-title="E-mail">
                            <em class="icon ni ni-mail-fill"></em>
                        </a>';
                        echo'</li><li class="nk-tb-action">';
                        echo'<a href="tel:'.$contatti[0].'" class="btn btn-trigger btn-icon" data-bs-toggle="tooltip" data-bs-placement="top" aria-label="'.$contatti[0].'" data-bs-original-title="Telefono">
                            <em class="icon ni ni-call-fill"></em>
                        </a>';
                        echo'</li></ul>';
                    } elseif (strpos($k2, "Incasso") !== false) {
                        // Se il nome del campo contiene "valore", aggiungi il simbolo dell'euro
                        echo "€ " . htmlspecialchars($v2, ENT_QUOTES, 'UTF-8');
                    } else {
                        // Altrimenti, stampa il valore normalmente
                        echo htmlspecialchars($v2, ENT_QUOTES, 'UTF-8');
                    }
                ?>
            </td>
            <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<style>
    .nk-tb-actions{
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
    }
    .modal.show .modal-dialog {
        display: block !important;
    }
    .modal-content{
        width: 800px !important;
        display: table !important;
    }
    </style>