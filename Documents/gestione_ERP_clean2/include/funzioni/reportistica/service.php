<?php
/**
 * SERVICE - Reportistica
 *
 * - Tutta la LOGICA DATI (query + trasformazioni)
 * - NESSUN HTML e NESSUNA ROUTE qui.
 */

/* =========================================================
   Helper di escape (per LIKE)
   ========================================================= */
function reportistica_escape($database, $value) {
    if (is_object($database) && method_exists($database, 'escape')) {
        return $database->escape($value);
    }
    return addslashes($value);
}

/* =========================================================
   Ricerca discenti (Select2)
   ========================================================= */
function reportistica_search_discenti($database, $term, $limit = 20) {
    $term = trim((string)$term);
    if (mb_strlen($term) < 2) return [];

    $q = reportistica_escape($database, $term);

    $sql = "
        SELECT id, nome, cognome, cf, email
        FROM an_discenti
        WHERE cognome LIKE '%{$q}%'
           OR nome    LIKE '%{$q}%'
           OR CONCAT(cognome, ' ', nome) LIKE '%{$q}%'
           OR cf      LIKE '%{$q}%'
           OR email   LIKE '%{$q}%'
        ORDER BY cognome, nome
        LIMIT {$limit}
    ";
    $rows = $database->query($sql) ?: [];

    $out = [];
    foreach ($rows as $r) {
        $label = trim(($r['cognome'] ?? '').' '.($r['nome'] ?? ''));
        if (!empty($r['cf']))    $label .= ' · '.$r['cf'];
        if (!empty($r['email'])) $label .= ' · '.$r['email'];
        $out[] = ['id' => (int)$r['id'], 'text' => $label];
    }
    return $out;
}

/* =========================================================
   Anagrafica discente
   ========================================================= */
function reportistica_get_discente($database, $did) {
    $did = (int)$did;
    if ($did <= 0) return null;

    $sql  = "SELECT id, nome, cognome, cf FROM an_discenti WHERE id={$did} LIMIT 1";
    $rows = $database->query($sql);
    return $rows ? $rows[0] : null;
}

/* =========================================================
   Carriera (iscrizioni ufficiali)
   ========================================================= */
function reportistica_get_carriera($database, $did) {
    $did = (int)$did;
    if ($did <= 0) return [];

    $sql = "
      SELECT
        c.id,
        c.id_discente,
        c.tabella,
        c.id_percorso,
        c.id_master,
        c.id_esame,
        c.id_certificazione,
        c.data,
        CASE
          WHEN c.tabella = 'an_percorsi' THEN (
            SELECT rd.riferimentoNomeProd
            FROM rateDiscente rd
            WHERE rd.id_discente = c.id_discente
              AND rd.riferimentoTabellaProd LIKE '%an_percorsi%'
            ORDER BY rd.id DESC LIMIT 1
          )
          WHEN c.tabella = 'an_master' THEN (
            SELECT rd.riferimentoNomeProd
            FROM rateDiscente rd
            WHERE rd.id_discente = c.id_discente
              AND rd.riferimentoTabellaProd LIKE '%an_master%'
            ORDER BY rd.id DESC LIMIT 1
          )
          WHEN c.tabella = 'an_esami' THEN (
            SELECT de.riferimentoNomeEsame
            FROM discente_esameSingolo de
            WHERE de.id = c.id_esame
            LIMIT 1
          )
          WHEN c.tabella = 'an_certificazioni' THEN (
            SELECT dc.riferimentoNomeCert
            FROM discente_certificazioni dc
            WHERE dc.id = c.id_certificazione
            LIMIT 1
          )
          ELSE NULL
        END AS nome,
        CASE
          WHEN c.tabella='an_percorsi'       THEN 'Percorso di Laurea'
          WHEN c.tabella='an_master'         THEN 'Master'
          WHEN c.tabella='an_esami'          THEN 'Esame Singolo'
          WHEN c.tabella='an_certificazioni' THEN 'Certificazione'
          ELSE 'Altro'
        END AS tipo
      FROM carriera c
      WHERE c.id_discente = {$did}
      ORDER BY c.data DESC, c.id DESC
    ";
    $rows = $database->query($sql) ?: [];

    foreach ($rows as &$r) {
        if (empty($r['nome'])) {
            switch ($r['tabella']) {
                case 'an_percorsi':       $r['nome'] = 'Percorso #'.(int)($r['id_percorso'] ?? 0); break;
                case 'an_master':         $r['nome'] = 'Master #'.(int)($r['id_master'] ?? 0); break;
                case 'an_esami':          $r['nome'] = 'Esame #'.(int)($r['id_esame'] ?? 0); break;
                case 'an_certificazioni': $r['nome'] = 'Certificazione #'.(int)($r['id_certificazione'] ?? 0); break;
                default:                  $r['nome'] = '—';
            }
        }
    }
    unset($r);
    return $rows;
}

/* =========================================================
   Piani economici (pagamenti)
   ========================================================= */
function reportistica_get_piani_discente($database, $did) {
    $did = (int)$did;
    if ($did <= 0) return [];

    $sql = "
      SELECT * FROM (
        /* A) Piani rateDiscente + scadenzeRate */
        SELECT
          0 AS src_order,
          rd.id AS piano_id,
          rd.riferimentoNomeProd AS prodotto,
          rd.riferimentoTabellaProd,
          CASE
            WHEN rd.riferimentoTabellaProd LIKE '%tutoraggio%' OR rd.tipoPagamento='tutoraggio' THEN 'Tutoraggio'
            WHEN rd.riferimentoTabellaProd LIKE '%an_percorsi%'       THEN 'Percorso di Laurea'
            WHEN rd.riferimentoTabellaProd LIKE '%an_master%'         THEN 'Master'
            WHEN rd.riferimentoTabellaProd LIKE '%an_certificazioni%' THEN 'Certificazione'
            WHEN rd.riferimentoTabellaProd LIKE '%an_esami%'          THEN 'Esame Singolo'
            ELSE 'Altro'
          END AS tipo,
          COALESCE(rd.importoTotale,0) AS importo_totale,
          COALESCE(rd.acconto,0)       AS acconto,
          COALESCE(rd.residuo,0)       AS residuo,
          (SELECT COUNT(*) FROM scadenzeRate s0
            WHERE s0.id_rateScadenze = rd.id AND s0.id_discente = rd.id_discente
          ) AS rate_totali,
          (SELECT COUNT(*) FROM scadenzeRate s1
            WHERE s1.id_rateScadenze = rd.id AND s1.id_discente = rd.id_discente AND s1.pagato = 'ok'
          ) AS rate_pagate,
          (SELECT MAX(s2.dataPagamentoRata) FROM scadenzeRate s2
            WHERE s2.id_rateScadenze = rd.id AND s2.id_discente = rd.id_discente AND s2.pagato = 'ok'
          ) AS ultima_data,
          (SELECT SUM(s3.importoRata) FROM scadenzeRate s3
            WHERE s3.id_rateScadenze = rd.id AND s3.id_discente = rd.id_discente AND s3.pagato = 'ok'
              AND s3.dataPagamentoRata = (
                SELECT MAX(s4.dataPagamentoRata) FROM scadenzeRate s4
                WHERE s4.id_rateScadenze = rd.id AND s4.id_discente = rd.id_discente AND s4.pagato = 'ok'
              )
          ) AS ultima_importo,
          (SELECT MIN(s5.data_scadenza) FROM scadenzeRate s5
            WHERE s5.id_rateScadenze = rd.id AND s5.id_discente = rd.id_discente
              AND (s5.pagato IS NULL OR s5.pagato <> 'ok')
          ) AS prossima_data,
          (SELECT s6.importoRata FROM scadenzeRate s6
            WHERE s6.id_rateScadenze = rd.id AND s6.id_discente = rd.id_discente
              AND s6.data_scadenza = (
                SELECT MIN(s7.data_scadenza) FROM scadenzeRate s7
                WHERE s7.id_rateScadenze = rd.id AND s7.id_discente = rd.id_discente
                  AND (s7.pagato IS NULL OR s7.pagato <> 'ok')
              )
            LIMIT 1
          ) AS prossima_importo
        FROM rateDiscente rd
        WHERE rd.id_discente = {$did}

        UNION ALL

        /* B1) Esame Singolo (riga prodotto) */
        SELECT
          1 AS src_order,
          de.id AS piano_id,
          de.riferimentoNomeEsame AS prodotto,
          'discente_esameSingolo' AS riferimentoTabellaProd,
          'Esame Singolo' AS tipo,
          COALESCE(NULLIF(de.prezzo,0),
                   GREATEST(COALESCE(de.prezzoTotale,0) - COALESCE(de.prezzoTutoraggio,0), 0)
          ) AS importo_totale,
          0 AS acconto,
          COALESCE(NULLIF(de.prezzo,0),
                   GREATEST(COALESCE(de.prezzoTotale,0) - COALESCE(de.prezzoTutoraggio,0), 0)
          ) AS residuo,
          1 AS rate_totali,
          0 AS rate_pagate,
          NULL AS ultima_data,
          NULL AS ultima_importo,
          de.`data` AS prossima_data,
          COALESCE(NULLIF(de.prezzo,0),
                   GREATEST(COALESCE(de.prezzoTotale,0) - COALESCE(de.prezzoTutoraggio,0), 0)
          ) AS prossima_importo
        FROM discente_esameSingolo de
        WHERE de.did = {$did}

        UNION ALL

        /* B2) Esame Singolo (riga tutoraggio se presente) */
        SELECT
          1 AS src_order,
          de.id AS piano_id,
          CONCAT('Tutoraggio - ', de.riferimentoNomeEsame) AS prodotto,
          'discente_esameSingolo' AS riferimentoTabellaProd,
          'Tutoraggio' AS tipo,
          COALESCE(de.prezzoTutoraggio,0) AS importo_totale,
          0 AS acconto,
          COALESCE(de.prezzoTutoraggio,0) AS residuo,
          1 AS rate_totali,
          0 AS rate_pagate,
          NULL AS ultima_data,
          NULL AS ultima_importo,
          de.`data` AS prossima_data,
          COALESCE(de.prezzoTutoraggio,0) AS prossima_importo
        FROM discente_esameSingolo de
        WHERE de.did = {$did}
          AND COALESCE(de.prezzoTutoraggio,0) > 0

        UNION ALL

        /* C) Certificazioni (tutoraggio intrinseco) */
        SELECT
          2 AS src_order,
          dc.id AS piano_id,
          dc.riferimentoNomeCert AS prodotto,
          'discente_certificazioni' AS riferimentoTabellaProd,
          'Certificazione' AS tipo,
          COALESCE(dc.prezzoTotale, dc.prezzo + COALESCE(dc.prezzoTutoraggio,0)) AS importo_totale,
          0 AS acconto,
          COALESCE(dc.prezzoTotale, dc.prezzo + COALESCE(dc.prezzoTutoraggio,0)) AS residuo,
          1 AS rate_totali,
          0 AS rate_pagate,
          NULL AS ultima_data,
          NULL AS ultima_importo,
          dc.`data` AS prossima_data,
          COALESCE(dc.prezzoTotale, dc.prezzo + COALESCE(dc.prezzoTutoraggio,0)) AS prossima_importo
        FROM discente_certificazioni dc
        WHERE dc.did = {$did}
      ) AS X
      ORDER BY X.src_order ASC, X.prodotto ASC, X.piano_id DESC
    ";

    $rows = $database->query($sql) ?: [];
    foreach ($rows as &$r) {
        $r['importo_totale']   = (float)($r['importo_totale'] ?? 0);
        $r['acconto']          = (float)($r['acconto'] ?? 0);
        $r['residuo']          = (float)($r['residuo'] ?? 0);
        $r['ultima_importo']   = isset($r['ultima_importo'])   ? (float)$r['ultima_importo']   : null;
        $r['prossima_importo'] = isset($r['prossima_importo']) ? (float)$r['prossima_importo'] : null;
        $r['rate_totali']      = (int)($r['rate_totali'] ?? 0);
        $r['rate_pagate']      = (int)($r['rate_pagate'] ?? 0);
    }
    unset($r);
    return $rows;
}

/* =========================================================
   Merge pagamenti + carriera (dataset unico)
   ========================================================= */
function reportistica_make_key(string $tipo, ?string $prodotto): string {
    $t = mb_strtolower(trim($tipo));
    $p = preg_replace('/\s+/', ' ', mb_strtolower(trim((string)$prodotto)));
    return $t.'|'.$p;
}
function reportistica_has_economic_info(array $r): bool {
    return (
        ($r['importo_totale']   ?? 0) > 0 ||
        ($r['acconto']          ?? 0) > 0 ||
        ($r['residuo']          ?? 0) > 0 ||
        ($r['rate_totali']      ?? 0) > 0 ||
        ($r['rate_pagate']      ?? 0) > 0 ||
        ($r['ultima_importo']   ?? null) !== null ||
        ($r['prossima_importo'] ?? null) !== null
    );
}
function reportistica_get_dataset_unificato($database, int $did): array {
    $piani    = reportistica_get_piani_discente($database, $did);
    $carriera = reportistica_get_carriera($database, $did);

    $byKey = [];

    foreach ($piani as $p) {
        $row = [
            'fonte'            => 'pagamenti',
            'piano_id'         => (int)($p['piano_id'] ?? 0) ?: null,
            'carriera_id'      => null,
            'tipo'             => (string)($p['tipo'] ?? ''),
            'prodotto'         => (string)($p['prodotto'] ?? ''),
            'data_iscrizione'  => null,
            'importo_totale'   => (float)($p['importo_totale'] ?? 0),
            'acconto'          => (float)($p['acconto'] ?? 0),
            'residuo'          => (float)($p['residuo'] ?? 0),
            'rate_totali'      => (int)($p['rate_totali'] ?? 0),
            'rate_pagate'      => (int)($p['rate_pagate'] ?? 0),
            'ultima_data'      => $p['ultima_data'] ?? null,
            'ultima_importo'   => $p['ultima_importo'] ?? null,
            'prossima_data'    => $p['prossima_data'] ?? null,
            'prossima_importo' => $p['prossima_importo'] ?? null,
        ];
        $key = reportistica_make_key($row['tipo'], $row['prodotto']);
        if (!isset($byKey[$key]) || reportistica_has_economic_info($row)) {
            $byKey[$key] = $row;
        }
    }

    foreach ($carriera as $c) {
        $row = [
            'fonte'            => 'carriera',
            'piano_id'         => null,
            'carriera_id'      => (int)($c['id'] ?? 0) ?: null,
            'tipo'             => (string)($c['tipo'] ?? ''),
            'prodotto'         => (string)($c['nome'] ?? ''),
            'data_iscrizione'  => $c['data'] ?? null,
            'importo_totale'   => 0.0,
            'acconto'          => 0.0,
            'residuo'          => 0.0,
            'rate_totali'      => 0,
            'rate_pagate'      => 0,
            'ultima_data'      => null,
            'ultima_importo'   => null,
            'prossima_data'    => null,
            'prossima_importo' => null,
        ];
        $key = reportistica_make_key($row['tipo'], $row['prodotto']);
        if (!isset($byKey[$key])) {
            $byKey[$key] = $row;
        } else {
            if (empty($byKey[$key]['data_iscrizione']) && !empty($row['data_iscrizione'])) {
                $byKey[$key]['data_iscrizione'] = $row['data_iscrizione'];
            }
        }
    }

    $out = array_values($byKey);
    usort($out, function($a, $b){
        $ta = mb_strtolower($a['tipo'] ?? '');
        $tb = mb_strtolower($b['tipo'] ?? '');
        if ($ta === $tb) {
            return strcmp(mb_strtolower($a['prodotto'] ?? ''), mb_strtolower($b['prodotto'] ?? ''));
        }
        return strcmp($ta, $tb);
    });

    return $out;
}

/* =========================================================
   NUOVA API: Dettaglio rate per piano (per MODALE)
   ========================================================= */
function reportistica_get_rate($database, int $did, int $pianoId): array {
    $did = (int)$did; $pianoId = (int)$pianoId;
    if ($did <= 0 || $pianoId <= 0) return [];

    $sql = "
        SELECT
            s.id,
            s.id_discente,
            s.id_rateScadenze,
            s.importoRata,
            s.data_scadenza,
            s.dataPagamentoRata,
            s.pagato
        FROM scadenzeRate s
        WHERE s.id_discente = {$did}
          AND s.id_rateScadenze = {$pianoId}
        ORDER BY s.data_scadenza ASC, s.id ASC
    ";
    $rows = $database->query($sql) ?: [];
    foreach ($rows as &$r) {
        if (isset($r['importoRata'])) $r['importoRata'] = (float)$r['importoRata'];
    }
    unset($r);
    return $rows;
}

/**
 * Versione “ricca” per la modale: info piano + rate + stats
 */
function reportistica_get_rate_dettaglio($database, int $did, int $pianoId): array {
    $did = (int)$did; $pianoId = (int)$pianoId;
    if ($did <= 0 || $pianoId <= 0) {
        return ['ok'=>false, 'error'=>'Parametri non validi'];
    }

    // 1) Info piano (prodotto/tipo/importi) da rateDiscente
    $sqlPiano = "
        SELECT
          rd.id AS piano_id,
          rd.id_discente,
          rd.riferimentoNomeProd AS prodotto,
          rd.riferimentoTabellaProd,
          CASE
            WHEN rd.riferimentoTabellaProd LIKE '%tutoraggio%' OR rd.tipoPagamento='tutoraggio' THEN 'Tutoraggio'
            WHEN rd.riferimentoTabellaProd LIKE '%an_percorsi%'       THEN 'Percorso di Laurea'
            WHEN rd.riferimentoTabellaProd LIKE '%an_master%'         THEN 'Master'
            WHEN rd.riferimentoTabellaProd LIKE '%an_certificazioni%' THEN 'Certificazione'
            WHEN rd.riferimentoTabellaProd LIKE '%an_esami%'          THEN 'Esame Singolo'
            ELSE 'Altro'
          END AS tipo,
          COALESCE(rd.importoTotale,0) AS importo_totale,
          COALESCE(rd.acconto,0)       AS acconto,
          COALESCE(rd.residuo,0)       AS residuo
        FROM rateDiscente rd
        WHERE rd.id = {$pianoId}
          AND rd.id_discente = {$did}
        LIMIT 1
    ";
    $piano = $database->query($sqlPiano);
    if (!$piano) {
        return ['ok'=>false, 'error'=>'Piano non trovato'];
    }
    $piano = $piano[0];

    // 2) Elenco rate
    $rows = reportistica_get_rate($database, $did, $pianoId);

    // 3) Statistiche semplici
    $totali = count($rows);
    $pagate = 0;
    $sommaPagata = 0.0;
    $sommaResidua = 0.0;
    foreach ($rows as $rr) {
        $isPaid = (strtolower((string)$rr['pagato']) === 'ok') || $rr['pagato'] === 1 || $rr['pagato'] === true;
        if ($isPaid) {
            $pagate++;
            $sommaPagata += (float)($rr['importoRata'] ?? 0);
        } else {
            $sommaResidua += (float)($rr['importoRata'] ?? 0);
        }
    }

    return [
        'ok'    => true,
        'piano' => $piano,
        'rows'  => $rows,
        'stats' => [
            'rate_totali'   => $totali,
            'rate_pagate'   => $pagate,
            'somma_pagata'  => $sommaPagata,
            'somma_residua' => $sommaResidua,
        ],
    ];
}
