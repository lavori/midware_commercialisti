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
                <!--Sviluppo tabella-->
                <div class="card-inner">
                    <form method="POST">
                        <div class="col-12">
                            <div class="form-group">
                                <br>
                                <div class="d-flex align-items-center gap-2">
                                    <select class="form-select js-select2 flex-grow-1" name="cercaMaster" data-search="on">
                                        <option value="#" selected disabled>Seleziona Master</option>
                                        <?php foreach ($master as $p) { ?>
                                            <option value="<?php echo $p['id']; ?>">
                                                <?php echo $p['nome']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <br>
                    </form>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center card-footer" style="background-color: #fff; border-top: none;">
                        <!-- Pulsanti centrati -->
                        <div class="d-flex align-items-center gap-2">
                            <a onClick="apriModal('Nuovo Esame Master','Gestione Esami Master','setting/form_esamiMaster','new')" class="btn btn-primary">Aggiungi Esame</a>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- .card-preview -->
    </div>
</div>
</div>
<!-- Form unico nella pagina -->
<form id="mainForm" action="<?php echo $serp; ?>" method="POST">
    <!-- Altri campi visibili opzionali -->
</form>

