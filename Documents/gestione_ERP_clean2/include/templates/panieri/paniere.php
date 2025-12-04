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
                    <?php $th = array_keys($panieri); ?>
                    <table class="datatable-init table">
                        <thead>
                            <tr>
                                <th class="<?php echo $class; ?>">Quesito</th>
                                <th class="nk-tb-col nk-tb-col-tools text-end"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($panieri as $k => $v): ?>
                                <tr id="dom_<?php echo $v['id']; ?>">
                                    <td>
                                        <strong><?php echo $v['domanda']; ?></strong><br>
                                        <?php if ($v['r1'] != "" && $v['r1'] != "NULL" && $v['r2'] != '' && $v['r2'] != "NULL" && $v['r3'] != "" && $v['r3'] != "NULL" && $v['r4'] != "" && $v['r4'] != "NULL"): ?>
                                            <ul>
                                                <ol><strong>1:</strong> <?php echo $v['r1']; ?></ol>
                                                <ol><strong>2:</strong> <?php echo $v['r2']; ?></ol>
                                                <ol><strong>3:</strong> <?php echo $v['r3']; ?></ol>
                                                <ol><strong>4:</strong> <?php echo $v['r4']; ?></ol>
                                            </ul>
                                        <?php endif; ?>
                                    </td>
                                    <td class="nk-tb-col nk-tb-col-tools">
                                        <ul class="nk-tb-actions gx-1">
                                            <li>
                                                <div class="drodown">
                                                    <a class="dropdown-toggle btn btn-icon btn-trigger"
                                                        data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <ul class="link-list-opt no-bdr">
                                                            <li>
                                                                <a style="cursor: pointer;"
                                                                    href="/panieri/esame/<?= $v['id']; ?>/update">
                                                                    <em class="icon ni ni-shield-star"></em>
                                                                    <span>Modifica</span>
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a
                                                                    onClick="apriModal('Risposta','Risposta Corretta','panieri/risposta','<?php echo $v['id']; ?>')">
                                                                    <em class="icon ni ni-shield-star"></em>
                                                                    <span>Visualizza Risposta</span>
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a
                                                                    onClick="apriModal('Cancellazione','Elimina Domanda','panieri/delete','<?php echo $v['id']; ?>', '', '1')">
                                                                    <em class="icon ni ni-shield-star"></em>
                                                                    <span>Cancella Domanda</span>
                                                                </a>
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
                </div>
            </div><!-- .card-preview -->
        </div>

    </div>
</div>
<!-- Form unico nella pagina -->
<form id="mainForm" action="<?php echo $serp; ?>" method="POST">
    <!-- Altri campi visibili opzionali -->
</form>