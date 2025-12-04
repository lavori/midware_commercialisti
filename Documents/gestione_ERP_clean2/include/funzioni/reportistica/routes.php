<?php
/**
 * ROUTES - Reportistica
 *
 * - GET /reportistica                    → pagina con tabella
 * - GET /reportistica/api/search_discenti→ JSON Select2
 * - GET /reportistica/api/piani          → JSON dataset (facoltativo)
 * - GET /reportistica/api/rate           → JSON dettagli rate per MODALE
 */

include_once __DIR__ . '/service.php';

/* Pagina principale */
$router->addRoute('/reportistica', function () use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
    $did = isset($_GET['did']) ? (int) $_GET['did'] : 0;
    $discente = $did > 0 ? reportistica_get_discente($database, $did) : null;
    $righe    = $discente ? reportistica_get_dataset_unificato($database, $did) : [];

    $content = [
        'dominio'      => $dominio,
        'titolo'       => $titolo,
        'title'        => 'Reportistica',
        'serp'         => '/reportistica',
        'menu'         => $menu['reportistica'] ?? [],
        'apps'         => $apps,
        'h1'           => 'Reportistica',
        'h2'           => 'Ricerca discente e piani economici',
        'revisione'    => $revisione,
        'message'      => '',
        'selected_did' => $did,
        'discente'     => $discente,
        'righe'        => $righe,
    ];
    return render('reportistica/reportistica', $content);
});

/* API: ricerca Select2 */
$router->addRoute('/reportistica/api/search_discenti', function () use ($database) {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    $term    = (string)($_GET['term'] ?? '');
    $results = reportistica_search_discenti($database, $term, 20);
    echo json_encode(['results' => $results], JSON_UNESCAPED_UNICODE);
    return;
});

/* API: dataset (eventuale uso AJAX) */
$router->addRoute('/reportistica/api/piani', function () use ($database) {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    $did      = (int)($_GET['discente_id'] ?? $_GET['did'] ?? 0);
    $discente = $did ? reportistica_get_discente($database, $did) : null;
    $righe    = $discente ? reportistica_get_dataset_unificato($database, $did) : [];
    echo json_encode(['discente' => $discente, 'righe' => $righe], JSON_UNESCAPED_UNICODE);
    return;
});

/* API: DETTAGLIO RATE per modale (nuova query) */
$router->addRoute('/reportistica/api/rate', function () use ($database) {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

    $did = (int)($_GET['did'] ?? 0);
    $pid = (int)($_GET['piano_id'] ?? 0);

    $payload = reportistica_get_rate_dettaglio($database, $did, $pid);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    return;
});
