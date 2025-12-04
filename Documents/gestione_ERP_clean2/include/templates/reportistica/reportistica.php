<?php
/**
 * VIEW - reportistica/reportistica.php
 * Tabella compatta + modale “Dettagli rate” con lo stesso modello della tabella di reportistica.
 */

$selected_did = (int)($selected_did ?? 0);
$discente     = $discente ?? null;
$righe        = $righe ?? [];
$serp         = $serp ?? '/reportistica';
?>
<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h1 class="page-title"><?= htmlspecialchars($h1 ?? 'Reportistica') ?></h1>
            <div class="nk-block-des text-soft"><h2><?= htmlspecialchars($h2 ?? '') ?></h2></div>
        </div>
    </div>
</div>

<div class="nk-block nk-block-lg" id="reportistica-root"
     data-api-search="/reportistica/api/search_discenti"
     data-api-plans="/reportistica/api/piani"
     data-serp="<?= htmlspecialchars($serp) ?>">
    <div class="card card-bordered card-preview">
        <div class="card-inner">

            <!-- Ricerca -->
            <form method="get" action="<?= htmlspecialchars($serp) ?>" class="row g-2 align-items-end mb-2">
                <div class="col-md-6">
                    <label class="form-label">Cerca discente</label>
                    <select id="discente-search" name="did" class="form-select" style="width:100%;">
                        <?php if ($selected_did && $discente): ?>
                            <option value="<?= (int)$discente['id'] ?>" selected>
                                <?= htmlspecialchars(($discente['cognome'] ?? '').' '.($discente['nome'] ?? '')) ?>
                            </option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-auto"><button class="btn btn-primary">Cerca</button></div>
            </form>

            <script>
                (function init(){
                    if (window.jQuery && jQuery.fn.select2) {
                        jQuery('#discente-search').select2({
                            placeholder: 'Cerca per nome, cognome, CF, email…',
                            allowClear: true,
                            ajax: {
                                url: '/reportistica/api/search_discenti',
                                dataType: 'json',
                                delay: 250,
                                data: function (p) { return { term: p.term || '' }; },
                                processResults: function (d) { return d; },
                                cache: true
                            },
                            minimumInputLength: 2,
                            width: 'resolve'
                        });
                    } else { setTimeout(init, 50); }
                })();
            </script>

            <?php if ($selected_did && $discente): ?>
                <div class="alert alert-info mt-3">
                    <strong>Discente:</strong>
                    <?= htmlspecialchars(($discente['cognome'] ?? '').' '.($discente['nome'] ?? '')) ?>
                    <?php if (!empty($discente['cf'])): ?>
                        <span class="ms-2">(<?= htmlspecialchars($discente['cf']) ?>)</span>
                    <?php endif; ?>
                </div>

                <div class="mt-3"></div>
                <table class="table datatable-init-export" id="dataset-unico"
                       data-export-title="Reportistica - Dataset Unificato">
                    <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Prodotto</th>
                        <th>Iscrizione</th>
                        <th>Totale prezzo</th>
                        <th>Acconto</th>
                        <th>Ultimo pagamento</th>
                        <th>Prossima rata</th>
                        <th>Rate</th>
                        <th>Residuo</th>
                        <th style="width:1%;"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($righe)): ?>
                        <tr><td colspan="10">Nessun dato da mostrare</td></tr>
                    <?php else:
                        $badge = [
                            'tutoraggio'          => 'badge badge-dim bg-primary',
                            'percorso di laurea'  => 'badge badge-dim bg-info',
                            'master'              => 'badge badge-dim bg-indigo',
                            'certificazione'      => 'badge badge-dim bg-success',
                            'esame singolo'       => 'badge badge-dim bg-teal',
                            'altro'               => 'badge badge-dim bg-secondary',
                        ];
                        $did = (int)$selected_did;

                        foreach ($righe as $r):
                            $tipo   = trim((string)($r['tipo'] ?? ''));
                            $tipoLc = mb_strtolower($tipo);
                            $cls    = $badge[$tipoLc] ?? 'badge badge-dim bg-info';

                            $iscrRaw = $r['data_iscrizione'] ?? null;
                            $iscrTs  = $iscrRaw ? strtotime($iscrRaw) : 0;
                            $iscrD   = $iscrTs  ? date('d/m/Y', $iscrTs) : '—';

                            $ultImp = $r['ultima_importo'] ?? null;
                            $ultRaw = $r['ultima_data']    ?? null;
                            $ultTs  = $ultRaw ? strtotime($ultRaw) : 0;
                            $ultD   = $ultTs  ? date('d/m/Y', $ultTs) : null;

                            $proxImp = $r['prossima_importo'] ?? null;
                            $proxRaw = $r['prossima_data']    ?? null;
                            $proxTs  = $proxRaw ? strtotime($proxRaw) : 0;
                            $proxD   = $proxTs  ? date('d/m/Y', $proxTs) : null;

                            $ratePag = (int)($r['rate_pagate'] ?? 0);
                            $rateTot = (int)($r['rate_totali'] ?? 0);

                            $pid = !empty($r['piano_id']) ? (int)$r['piano_id'] : null;
                            ?>
                            <tr>
                                <td><span class="<?= $cls ?>"><?= htmlspecialchars($tipo ?: '-') ?></span></td>
                                <td><?= nl2br(htmlspecialchars($r['prodotto'] ?? '-')) ?></td>
                                <td data-order="<?= $iscrTs ?>"><?= $iscrD ?></td>
                                <td><?= number_format((float)($r['importo_totale'] ?? 0), 2, ',', '.') ?> €</td>
                                <td><?= number_format((float)($r['acconto'] ?? 0), 2, ',', '.') ?> €</td>
                                <td data-order="<?= $ultTs ?>">
                                    <?php if ($ultImp !== null): ?>
                                        <?= number_format((float)$ultImp, 2, ',', '.') ?> €
                                        <?php if ($ultD): ?><div class="text-soft small"><?= $ultD ?></div><?php endif; ?>
                                    <?php else: ?>—<?php endif; ?>
                                </td>
                                <td data-order="<?= $proxTs ?>">
                                    <?php if ($proxImp !== null): ?>
                                        <?= number_format((float)$proxImp, 2, ',', '.') ?> €
                                        <?php if ($proxD): ?><div class="text-soft small"><?= $proxD ?></div><?php endif; ?>
                                    <?php else: ?>—<?php endif; ?>
                                </td>
                                <td><?= $ratePag ?> / <?= $rateTot ?></td>
                                <td><?= number_format((float)($r['residuo'] ?? 0), 2, ',', '.') ?> €</td>

                                <td class="text-end">
                                    <div class="dropdown">
                                        <a href="#" class="dropdown-toggle btn btn-icon btn-trigger" data-bs-toggle="dropdown" aria-expanded="false">
                                            <em class="icon ni ni-more-h"></em>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <ul class="link-list-opt no-bdr">
                                                <li class="<?= $pid ? '' : 'disabled' ?>">
                                                    <a href="javascript:void(0)"
                                                       role="button"
                                                       data-action="show-rates"
                                                       data-did="<?= (int)$did ?>"
                                                       data-pid="<?= (int)$pid ?>"
                                                        <?= $pid ? '' : 'tabindex="-1" aria-disabled="true"' ?>>
                                                        <em class="icon ni ni-calendar-check"></em>
                                                        <span>Dettagli rate</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- MODALE con tabella in stile reportistica (export + show) -->
<div class="modal fade" id="rateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Dettaglio rate</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body">

                <!-- contatori + TOOLBAR (export + show) -->
                <div class="d-flex justify-content-between flex-wrap gap-3 mb-2">
                    <div class="text-muted">Totale rate: <strong id="rate-total-count">0</strong></div>
                    <div class="text-muted">Pagate: <strong id="rate-paid-count">0</strong></div>
                    <div class="text-muted">Da pagare: <strong id="rate-due-count">0</strong></div>

                    <!-- qui appendo pulsanti export e select show -->
                    <div class="ms-auto d-flex align-items-center gap-3" id="rate-dt-toolbar">
                        <div class="dt-export-buttons"></div>
                        <div class="dt-length"></div>
                    </div>
                </div>

                <div class="rate-loader text-center py-4">
                    <div class="spinner-border" role="status" aria-hidden="true"></div>
                    <div class="mt-2 text-muted">Caricamento…</div>
                </div>
                <div class="rate-error alert alert-danger d-none"></div>

                <div class="table-responsive rate-table-wrapper d-none">
                    <!-- STESSE CLASSI della tabella principale -->
                    <table class="table datatable-init-export" id="rate-modal-table"
                           data-export-title="Dettaglio rate">
                        <thead>
                        <tr>
                            <th style="width:56px;">#</th>
                            <th>Importo</th>
                            <th>Scadenza</th>
                            <th>Pagato il</th>
                            <th>Stato</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const modalEl = document.getElementById('rateModal');
        if (!modalEl) return;

        const hasBs  = !!(window.bootstrap && bootstrap.Modal);
        const bsModal = hasBs ? new bootstrap.Modal(modalEl) : null;

        const q = sel => modalEl.querySelector(sel);
        const loader   = () => q('.rate-loader');
        const tableBox = () => q('.rate-table-wrapper');
        const tbodyEl  = () => q('#rate-modal-table tbody');

        const fMoney = n => new Intl.NumberFormat('it-IT', {style:'currency', currency:'EUR'}).format(+n || 0);
        const fDate  = s => {
            if (!s) return '—';
            const m = String(s).match(/^(\d{4})-(\d{2})-(\d{2})/);
            return m ? `${m[3]}/${m[2]}/${m[1]}` : s;
        };

        function ensureErrorBox(){
            let box = q('.rate-error');
            if (!box) {
                box = document.createElement('div');
                box.className = 'rate-error alert alert-danger d-none';
                q('.modal-body').prepend(box);
            }
            return box;
        }

        function resetModal(title){
            q('.modal-title').textContent = title || 'Dettaglio rate';
            loader().classList.remove('d-none');
            tableBox().classList.add('d-none');
            tbodyEl().innerHTML = '';
            q('#rate-total-count').textContent = '0';
            q('#rate-paid-count').textContent  = '0';
            q('#rate-due-count').textContent   = '0';

            // distruggo eventuale DataTable già attivo
            if (window.jQuery && jQuery.fn.DataTable) {
                const $t = jQuery('#rate-modal-table');
                if ($t.hasClass('dataTable')) $t.DataTable().clear().destroy();
            }
            // pulisco toolbar export/length
            const tb = document.getElementById('rate-dt-toolbar');
            tb.querySelector('.dt-export-buttons').innerHTML = '';
            tb.querySelector('.dt-length').innerHTML = '';
            const err = ensureErrorBox(); err.classList.add('d-none'); err.textContent = '';
        }

        // Inizializza DataTables con **lo stesso modello della reportistica** (Buttons + length)
        function initDtLikeReportistica(){
            if (!(window.jQuery && jQuery.fn.DataTable)) return;

            const dt = jQuery('#rate-modal-table').DataTable({
                order: [[0,'asc']],
                pageLength: 10,
                lengthMenu: [[10,25,50,100,-1],[10,25,50,100,'All']],
                dom: 'lBfrtip',                       // length + Buttons + filter + table + info + paging
                buttons: ['copy','csv','excel','pdf'],// export come in reportistica
                language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/it-IT.json' }
            });

            // sposto i container “length” e “buttons” nella toolbar dedicata
            const $wrap    = jQuery('#rate-modal-table').closest('.dataTables_wrapper');
            const $length  = $wrap.find('div.dataTables_length');
            const $buttons = $wrap.find('div.dt-buttons');
            jQuery('#rate-dt-toolbar .dt-length').append($length);
            jQuery('#rate-dt-toolbar .dt-export-buttons').append($buttons);
        }

        function renderRates(payload){
            const rows = payload.rates || payload.righe || payload.rows || [];
            let pagate = 0;

            rows.forEach((r, idx) => {
                const statoOK = String(r.pagato || r.stato || '').toLowerCase() === 'ok';
                if (statoOK) pagate++;
                const tr = document.createElement('tr');
                tr.innerHTML = `
                <td class="text-muted">${('n' in r ? r.n : (idx + 1))}</td>
                <td>${fMoney(r.importo ?? r.importoRata ?? r.importo_rata)}</td>
                <td>${fDate(r.scadenza ?? r.data_scadenza)}</td>
                <td>${fDate(r.pagato_il ?? r.dataPagamentoRata)}</td>
                <td>${statoOK ? '<span class="badge badge-dim bg-success">Pagata</span>'
                    : '<span class="badge badge-dim bg-warning text-dark">Da pagare</span>'}</td>`;
                tbodyEl().appendChild(tr);
            });

            q('#rate-total-count').textContent = String(rows.length);
            q('#rate-paid-count').textContent  = String(pagate);
            q('#rate-due-count').textContent   = String(Math.max(0, rows.length - pagate));

            loader().classList.add('d-none');
            tableBox().classList.remove('d-none');

            // inizializzo DataTables con Buttons + Show (uguale alla tabella principale)
            initDtLikeReportistica();
        }

        // Apertura modale con fetch
        document.addEventListener('click', async function(e){
            const a = e.target.closest('a[data-action="show-rates"]');
            if (!a) return;

            e.preventDefault(); e.stopPropagation();

            const did = a.getAttribute('data-did');
            const pid = a.getAttribute('data-pid');
            if (!did || !pid || pid === '0') return;

            resetModal('Dettaglio rate');
            if (bsModal) bsModal.show();
            else { modalEl.style.display='block'; modalEl.classList.add('show'); }

            try {
                const res = await fetch(`/reportistica/api/rate?did=${encodeURIComponent(did)}&piano_id=${encodeURIComponent(pid)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin'
                });
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                const data = await res.json();
                if (data && (data.rates || data.righe || data.rows)) renderRates(data);
                else throw new Error('Risposta non valida');
            } catch (err) {
                loader().classList.add('d-none');
                const box = ensureErrorBox();
                box.textContent = `Impossibile caricare le rate. ${err.message || err}`;
                box.classList.remove('d-none');
            }
        }, true);
    })();
</script>
