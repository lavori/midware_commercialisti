<h2 style="text-align: center;">Corrispettivi</h2>
<div class="card card-bordered card-preview">
    <div class="card-inner">
        <div class="table-responsive">
            <?php if ($content!=array()){?>
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">id</th>
                        <th class="descrizione" scope="col">Descrizione</th>
                        <th scope="col">Valore</th>
                        <th scope="col">Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        $tot=0; 
                        foreach ($content as $a){ 
                            $tot+=$a['corrispettivo']; 
                            $ids_array[]=$a['id'];
                    ?>
                    <tr>
                        <td><?php echo $a['id']; ?></td>    
                        <td class="descrizione"><?php echo $a['descrizione']; ?></td>
                        <td><?php echo $a['corrispettivo']; ?></td>
                        <td>
                            <?php 
                                // Crea un oggetto DateTime dalla data originale
                                $data = new DateTime($a['data']);
                                // Formatta la data nel formato desiderato
                                echo $data->format('d/m/y - H:i:s');
                            ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            <div class="button-container" style="text-align: center;">
                <?php $ids=implode(',',$ids_array); ?>
                <a class="btn btn-ricerca" href="?ids=<?php echo $ids; ?>">Invia i corrispettivi per un totale di <strong>&euro; <?php echo number_format($tot, 2, '.', ''); ?></strong></a>
            </div>
            <?php } else {?>
            <h3 style="text-align: center; color:#cc0000;">Nessun Corrispettivo da inviare</h2>
            <?php } ?>
        </div>
    </div>
</div>