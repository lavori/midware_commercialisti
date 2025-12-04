<!-- content @s -->
<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h1 class="page-title"><?php echo htmlspecialchars($h1 ?? ''); ?></h1>
            <div class="nk-block-des text-soft"></div>
        </div>
    </div>
</div>

<div class="nk-block nk-block-lg">
    <div class="row g-gs my-3">
        <div class="col-md-12">
            <div class="card card-bordered card-preview">
                <div class="card-inner">
                    <?php
                    // normalizza variabili (fallback se non hai passato $hasAccordo dal controller)
                    $accordo = $accordo ?? ($discente['accordo'] ?? null);
                    $hasAccordo = isset($hasAccordo) ? $hasAccordo : !empty($accordo);
                    ?>

                    <?php if (!$hasAccordo): ?>
                        <div class="alert alert-warning">
                            Nessun accordo di pagamento associato a questa rata.
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <div class="mb-3">
                            <h4 class="fw-bold mb-1">Discente: <?= $discente['nome'] . ' ' . $discente['cognome'] ?>
                        </div>
                        <div class="mb-3"><br>
                            <h6 class="fw-bold mb-1">Prodotto/i:</h6>
                            <?php if (!empty($discente['accordo']['riferimentoNomeProd'])): ?>
                                <ul class="list-unstyled mb-0 ps-3">
                                    <?php foreach ($discente['accordo']['riferimentoNomeProd'] as $val): ?>
                                        <?= htmlspecialchars($val) ?><br>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <h6 class="fw-bold mb-1">Tipologia:</h6>
                            <?php if (!empty($discente['accordo']['riferimentoTabellaProd'])): ?>
                                <ul class="list-unstyled mb-0 ps-3">
                                    <?php foreach ($discente['accordo']['riferimentoTabellaProd'] as $val): ?>
                                        <?= htmlspecialchars($val) ?><br>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </div>

                        <div class="mb-2">
                            <h6 class="fw-bold mb-1">Email:</h6>
                            <span><?= htmlspecialchars($discente['email'] ?? '—') ?></span>
                        </div>

                        <div class="mb-2">
                            <h6 class="fw-bold mb-1">Telefono:</h6>
                            <span><?= htmlspecialchars($discente['telefono'] ?? '—') ?></span>
                        </div>

                        <div class="mb-2">
                            <h6 class="fw-bold mb-1">Cellulare:</h6>
                            <span><?= htmlspecialchars($discente['cellulare'] ?? '—') ?></span>
                        </div>

                        <div class="mb-2">
                            <h6 class="fw-bold mb-1">Numero Matricola:</h6>
                            <span><?= htmlspecialchars($discente['numMatricola'] ?? '—') ?></span>
                        </div>

                        <hr><br>

                        <div class="gy-3">

                            <div class="row g-3 align-center">
                                <div class="col-lg-5">
                                    <div class="form-group">
                                        <label class="form-label" for="importoTotale">Importo Totale corso</label>
                                        <span class="form-note">Importo totale del corso del discente.</span>
                                    </div>
                                </div>
                                <div class="col-lg-7">
                                    <div class="form-group">
                                        <div class="form-control-wrap">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" id="basic-addon-euro-1">€</span>
                                                </div>
                                                <input type="number" class="form-control" name="importoTotale"
                                                    id="importoTotale" step="0.01" readonly value="<?php
                                                    echo isset($discente['accordo']['importoTotale'])
                                                        ? htmlspecialchars($discente['accordo']['importoTotale'])
                                                        : '';
                                                    ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 align-center">
                                <div class="col-lg-5">
                                    <div class="form-group">
                                        <label class="form-label" for="accontoCorso">Acconto corso</label>
                                        <span class="form-note">Acconto del corso del discente.</span>
                                    </div>
                                </div>
                                <div class="col-lg-7">
                                    <div class="form-group">
                                        <div class="form-control-wrap">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" id="basic-addon-euro-2">€</span>
                                                </div>
                                                <input type="number" class="form-control" name="accontoCorso"
                                                    id="accontoCorso" step="0.01" readonly value="<?php
                                                    echo isset($discente['accordo']['acconto'])
                                                        ? htmlspecialchars($discente['accordo']['acconto'])
                                                        : '';
                                                    ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php $rata = $discente['rata'] ?? null; ?>

                            <div class="row g-3 align-center">
                                <div class="col-lg-5">
                                    <div class="form-group">
                                        <label class="form-label" for="costoRata">Costo Rata</label>
                                        <span class="form-note">Costo della rata.</span>
                                    </div>
                                </div>
                                <div class="col-lg-7">
                                    <div class="form-group">
                                        <div class="form-control-wrap">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" id="basic-addon-euro-3">€</span>
                                                </div>
                                                <input type="number" class="form-control" name="costoRata"
                                                    id="costoRata" step="0.01" readonly
                                                    value="<?php echo htmlspecialchars($rata['importoRata'] ?? ''); ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 align-center">
                                <div class="col-lg-5">
                                    <div class="form-group">
                                        <label class="form-label" for="dataScadenzaRata">Data Scadenza</label>
                                        <span class="form-note">Data scadenza della rata.</span>
                                    </div>
                                </div>
                                <div class="col-lg-7">
                                    <div class="form-group">
                                        <div class="form-control-wrap">
                                            <div class="input-group">
                                                <input type="date" class="form-control" name="dataScadenzaRata"
                                                    id="dataScadenzaRata" readonly
                                                    value="<?php echo htmlspecialchars($rata['data_scadenza'] ?? ''); ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr><br>

                            <form method="POST" enctype="multipart/form-data">
                                <div class="row g-3 align-center">
                                    <div class="col-lg-5">
                                        <div class="form-group">
                                            <label class="form-label" for="filePagamento">Carica File</label>
                                            <span class="form-note">Inserisci il file di caricamento della
                                                rata.</span>
                                        </div>
                                    </div>
                                    <div class="col-lg-7">
                                        <div class="form-group">
                                            <div class="form-control-wrap">
                                                <div class="form-file">
                                                    <input type="file" class="form-file-input" id="filePagamento"
                                                        name="filePagamento" accept=".pdf" required>
                                                    <label class="form-file-label" for="filePagamento">Scegli
                                                        File</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <br>

                                <div class="center">
                                    <div class="custom-control custom-control-sm custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="pagato" name="pagato"
                                            value="ok" required>
                                        <label class="custom-control-label form-note center" for="pagato">
                                            Conferma che il pagamento è avvenuto con successo
                                        </label>
                                    </div>
                                </div>

                                <br>

                                <div class="row g-3">
                                    <div class="col-lg-7 offset-lg-5">
                                        <div class="form-group mt-2">
                                            <input type="hidden" name="idRata"
                                                value="<?php echo htmlspecialchars($rata['id'] ?? ''); ?>">
                                            <button type="submit" class="btn btn-primary">Inserisci
                                                Pagamento</button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                        </div> <!-- /.gy-3 -->
                    </div> <!-- /.form-group -->

                </div>
            </div>
        </div>
    </div>
</div>