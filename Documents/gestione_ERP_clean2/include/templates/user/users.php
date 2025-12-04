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
                <?php if (isset($message) && $message == 'ok') { ?>
                    <div class="example-alert">
                        <div class="alert alert-success alert-icon">
                            <em class="icon ni ni-check-circle"></em> <strong>Dato aggiornato</strong> con successo
                        </div>
                    </div>
                <?php } ?>
                <?php if (isset($message) && $message == 'pwd_error') { ?>
                    <div class="example-alert">
                        <div class="alert alert-danger alert-icon">
                            <em class="icon ni ni-cross-circle"></em> <strong>Errore</strong>! Le password non coincidono.
                        </div>
                    </div>
                <?php } ?>
                <div class="card-inner">
                    <?php //echo'<pre>';print_r($_SESSION['utente']['ruolo']);echo'</pre>'; ?>
                    <div class="card-head">
                        <h6 class="title">Gestione Profilo</h6>
                    </div>
                    <div class="card-inner">
                        <div class="preview-block">
                            <form id="aggiorna_user" name="aggiorna_user" method="POST">
                                <div class="row gy-6">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label" for="default-01">Nome</label>
                                            <div class="form-control-wrap">
                                                <input type="text" name="nome" id="nome" class="form-control"
                                                    value="<?php echo $utenti['nome']; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label" for="default-01">Cognome</label>
                                            <div class="form-control-wrap">
                                                <input type="text" name="cognome" id="cognome" class="form-control"
                                                    value="<?php echo $utenti['cognome']; ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label" for="default-01">Password</label>
                                            <div class="form-control-wrap">
                                                <input type="text" class="form-control" name="pwd" id="pwd">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label" for="default-01">Conferma Password</label>
                                            <div class="form-control-wrap">
                                                <input type="text" class="form-control" name="cpwd" id="cpwd">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label class="form-label" for="default-01">Email</label>
                                            <div class="form-control-wrap">
                                                <input type="email" class="form-control" name="email" id="email"
                                                    value="<?php echo $utenti['email']; ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="center" style="margin-top: 5%;">
                                    <input type="hidden" name="id_update" id="id_update"
                                        value="<?php echo $utenti['id']; ?>">
                                    <input type="submit" value="Aggiorna" class="btn btn-primary">
                                </div>
                            </form>
                        </div>
                    </div>
                </div><!-- .card-preview -->
            </div>
        </div>
    </div>
</div>
<!-- content @s -->
<!-- Form unico nella pagina -->
<form id="mainForm" action="<?php echo $serp; ?>" method="POST">
    <!-- Altri campi visibili opzionali -->
</form>