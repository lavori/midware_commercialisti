
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
                                <div class="card-inner">
                                    <div class="card-head">
                                        <h6 class="title">Gestione Corrispettivi</h6>
                                    </div>
                                    <div class="card-inner col-md-12">
                                        <form method="GET">
                                            <div class="nk-block  nk-block-lg">
                                                <div class="row g-gs my-3">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label class="form-label">Utente</label>
                                                            <div class="form-control-wrap">
                                                                <select id="utente" name="utente" class="form-select js-select2" data-ui="sm" data-search="on">
                                                                    <option value="*">Seleziona Azienda / Utente</option>
                                                                    
                                                                    <?php 
                                                                        foreach($utenti as $utente){
                                                                            echo"<option value='T".$utente['id']."'>".$utente['nome']." ".$utente['cognome']."</option>";
                                                                        }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label class="form-label">Tipo di corrispettivo</label>
                                                            <div class="form-control-wrap">
                                                                <select id="tipo" name="tipo" class="form-select js-select2" data-ui="sm" data-search="on">
                                                                    <option value="">Seleziona Tipo</option>
                                                                    <option value="1">Contabilizzato</option>
                                                                    <option value="0">da Contabilizzare</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4" style="margin-top: 4em;">
                                                        <div class="form-group">
                                                            <div class="form-control-wrap">
                                                                <input type="submit" class="btn btn-primary" value="Invia">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="card-inner">
                                        <?php 
                                            $th=array_keys($content[0]);
                                        ?>
                                        <table class="datatable-init table">
                                            <thead>
                                                <tr>
                                                <?php 
                                                    foreach($th as $k => $v): 
                                                ?>
                                                    <th classnk-tb-col tb-col-sm"><?php echo $v; ?></th>
                                                <?php endforeach; ?>
                                                    <th class="nk-tb-col nk-tb-col-tools text-end " style="white-space: nowrap;">azioni</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach($content as $k => $v): ?>
                                                <tr>
                                                <?php foreach($v as $k2 => $v2): ?>
                                                    <td class="vm nk-tb-col tb-col-sm">
                                                    <?php
                                                        // Controlla se il valore è una data valida
                                                        echo htmlspecialchars($v2, ENT_QUOTES, 'UTF-8');
                                                    ?>
                                                    </td>
                                                <?php endforeach; ?>
                                                <td class="nk-tb-col nk-tb-col-tools">
                                                    <?php if($v['Mexal']=='non inviato'):?>
                                                    <ul class="nk-tb-actions gx-1">
                                                        <li>
                                                            <div class="drodown">
                                                                <a class="dropdown-toggle btn btn-icon btn-trigger" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                                                                <div class="dropdown-menu dropdown-menu-end">
                                                                    <ul class="link-list-opt no-bdr">
                                                                        <li>
                                                                            <a style="cursor: pointer;" onClick="apriModal('Modifica Corrispettivo','Gestione corrispettivi','corrispettivi/form_corrispettivo','<?php echo $v['id']; ?>')">
                                                                                <em class="icon ni ni-shield-star"></em>
                                                                                <span>Modifica</span>
                                                                            </a>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                    <?php endif; ?>
                                                </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="center card-footer" style="background-color: #fff; border-top:none; margin-top:1.5rem;">
                                        <a onClick="apriModal('Nuovo Corrispettivo','Aggiungi Corrispettivo','user/form_corrispettivo.php','new')" class="btn btn-dim btn-warning">Aggiungi <?php echo $h2; ?></a>
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
