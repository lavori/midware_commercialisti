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

<div class="nk-block nk-block-lg">
    <div class="card card-bordered card-preview">
        <div class="card-inner">
            <table class="datatable-init nk-tb-list nk-tb-ulist" data-auto-responsive="false">
                <thead>
                    <tr class="nk-tb-item nk-tb-head">
                        <th class="nk-tb-col">
                            <span class="sub-text">Nome</span>
                        </th>
                        <th class="nk-tb-col tb-col-md">
                            <span class="sub-text">C.F.</span>
                        </th>
                        <th class="nk-tb-col tb-col-lg">
                            <span class="sub-text">Associato</span>
                        </th>
                        <th class="nk-tb-col nk-tb-col-tools text-end">
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tassisti as $t) { ?>
                        <tr class="nk-tb-item">
                            <td class="nk-tb-col">
                                <div class="user-card">
                                    <div class="user-avatar bg-dim-primary d-none d-sm-flex">
                                        <span><?php echo substr($t['nome'], 0, 1) . substr($t['cognome'], 0, 1) ?></span>
                                    </div>
                                    <div class="user-info">
                                        <span class="tb-lead">
                                            <?php echo $t['nome'] . ' ' . $t['cognome'] ?> <span
                                                class="dot dot-success d-md-none ms-1"></span>
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="nk-tb-col tb-col-md">
                                <span><?php echo $t['cf'] ?></span>
                            </td>
                            <td class="nk-tb-col tb-col-lg">
                                <span class="tb-status text-<?php if ($t['associato'] == 'si') {
                                    echo 'success';
                                } else {
                                    echo 'danger';
                                } ?>">
                                    <?php echo $t['associato'] ?>
                                </span>
                            </td>
                            <td class="nk-tb-col nk-tb-col-tools">
                                <ul class="nk-tb-actions gx-1">
                                    <li>
                                        <a onClick="apriModal('Aggiorna Tassista','Gestione Tassisti','user/form_update_tassisti','<?php echo $t['id'] ?>')"
                                            class="btn btn-primary">Modifica</a>
                                    </li>
                                    <li>
                                        <form method="POST" action="/admin/tassisti">
                                            <input type="hidden" name="delete" value="<?php echo $t['id'] ?>">
                                            <button type="submit" class="btn btn-danger">Elimina</button>
                                        </form>
                                    </li>
                                </ul>
                            </td>
                        </tr>
                        <!-- .nk-tb-item  -->
                    <?php } ?>
                </tbody>
            </table>
            <div class="center card-footer" style="background-color: #fff; border-top:none; margin-top:1.5rem;">
                <a onClick="apriModal('Nuovo Tassista','Gestione Tassisti','user/form_insert_tassisti','new')"
                    class="btn btn-dim btn-warning">Aggiungi <?php echo $h2; ?></a>
            </div>
        </div>
    </div>
</div>


<!-- Form unico nella pagina -->
<form id="mainForm" action="<?php echo $serp; ?>" method="POST">
    <!-- Altri campi visibili opzionali -->
</form>



<script>
    document.addEventListener('DOMContentLoaded', function () {
        const deleteForms = document.querySelectorAll('form[action="/admin/tassisti"]');

        deleteForms.forEach(function (form) {
            form.addEventListener('submit', function (event) {
                const confirmDelete = confirm('Sei sicuro di voler eliminare questo tassista?');
                if (!confirmDelete) {
                    event.preventDefault(); // Annulla l'invio del form
                }
            });
        });
    });
</script>