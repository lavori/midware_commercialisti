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
                            <em class="icon ni ni-check-circle"></em> <strong>Quesito inserito </strong> con successo
                        </div>
                    </div>
                <?php } ?>
                <div class="card-inner">
                    <?php if(isset($content[0]) && !empty($content[0])){ ?>
                        <?php $th = array_keys($content[0]); ?>
                        <table class="datatable-init table">
                            <thead>
                                <tr>
                                <?php foreach ($th as $k => $v): ?>
                                    <th class="<?php echo $class; ?>"><?php echo $v; ?></th>
                                <?php endforeach; ?>
                                    <th class="nk-tb-col nk-tb-col-tools text-end"></th>
                                </tr>
                            </thead> 
                            <tbody>
                                <?php foreach ($content as $k => $v): ?>
                                    <tr>
                                    <?php foreach ($v as $k2 => $v2): ?>
                                        <td>
                                            <?php echo $v2; ?>
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
                                                                    <?php if($_SESSION['utente']['ruolo'] == 'Discente' || $_SESSION['utente']['ruolo'] == 'Tutor') : ?>
                                                                        <?php if(in_array($v['id'], $acquisti_validi)): ?>
                                                                            <a href='panieri/esame/<?php  $v['id']; ?>'>
                                                                                <em class="icon ni ni-shield-star"></em>
                                                                                <span>Visualizza Paniere</span>
                                                                            </a>
                                                                        <?php else : ?>
                                                                            <a onClick="apriModal('Acquista Paniere','Acquista Accesso','panieri/acquista_accesso','<?php echo base64_encode($v['id']); ?>')">
                                                                                <em class="icon ni ni-shield-star"></em>
                                                                                <span>Acquista accesso</span>
                                                                            </a>
                                                                        <?php endif; ?>
                                                                    <?php else : ?>
                                                                        <a href='panieri/esame/<?= $v['id']; ?>'>
                                                                            <em class="icon ni ni-shield-star"></em>
                                                                            <span>Visualizza Paniere</span>
                                                                        </a>
                                                                    <?php endif; ?>
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