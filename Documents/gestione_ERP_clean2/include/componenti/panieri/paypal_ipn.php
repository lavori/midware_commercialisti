<?php
// c:\Users\admin\Documents\gestione-universitygtc\paypal_ipn.php

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Assicurati che questo script sia chiamato solo da PayPal tramite POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    exit();
}

// 1. Leggi i dati POST grezzi inviati da PayPal
$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);
$myPost = array();
foreach ($raw_post_array as $keyval) {
    $keyval = explode('=', $keyval);
    if (count($keyval) == 2) {
        $myPost[$keyval[0]] = urldecode($keyval[1]);
    }
}

// 2. Costruisci la richiesta di validazione IPN
$req = 'cmd=_notify-validate';
foreach ($myPost as $key => $value) {
    // Stripslashes per rimuovere eventuali slash aggiunti automaticamente
    // urlencode per codificare i valori per la richiesta HTTP
    $value = urlencode(stripslashes($value));
    $req .= "&$key=$value";
}

// 3. Invia i dati IPN di nuovo a PayPal per la validazione
// Per l'ambiente di produzione, usa https://www.paypal.com/cgi-bin/webscr
// Per l'ambiente di sandbox, usa https://www.sandbox.paypal.com/cgi-bin/webscr
$paypal_url = "https://www.paypal.com/cgi-bin/webscr"; 
$ch = curl_init($paypal_url);
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt(curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1));
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1); // Verifica il certificato SSL del server PayPal
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // Verifica che il nome host corrisponda al certificato
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

// In a real application, you should use a proper error handling mechanism
// for cURL. For simplicity, we're omitting it here.
$res = curl_exec($ch);
if (curl_errno($ch)) {
    // Logga l'errore cURL
    error_log("IPN cURL Error: " . curl_error($ch));
    curl_close($ch);
    exit();
}
curl_close($ch);

// 4. Elabora la risposta di validazione
if (strcmp($res, "VERIFIED") == 0) {
    // IPN verificato, procedi con l'elaborazione
    $payment_status = $_POST['payment_status'];
    $txn_id = $_POST['txn_id'];
    $item_name = $_POST['item_name']; // "Accesso Paniere ID: X"
    $mc_gross = $_POST['mc_gross'];
    $payer_email = $_POST['payer_email'];
    $custom_data = $_POST['custom']; // Recupera i dati custom (es. user_id)

    // Estrai l'ID del paniere da item_name (formato "Accesso Paniere ID: X")
    preg_match('/ID:\s*(\d+)/', $item_name, $matches);
    $paniere_id = isset($matches[1]) ? (int)$matches[1] : null;

    
    // Estrai l'ID utente dal campo 'custom' (es. "user_id=123")
    $user_id = null;
    if (preg_match('/user_id=(\d+)/', $custom_data, $user_matches)) {
        $user_id = (int)$user_matches[1];
    }

    // Logga i dettagli IPN per il debug
    error_log("IPN VERIFIED: Status=$payment_status, TxnID=$txn_id, Item=$item_name, Amount=$mc_gross, Payer=$payer_email, PaniereID=$paniere_id, UserID=$user_id");

    // Connettiti al tuo database
    include('config.php'); // Assumendo che config.php contenga $host, $username, $password, $db
    require_once('db.php'); // Assumendo che db.php contenga la tua classe Database

    $database = new Database($host, $username, $password, $db);
    $database->connect();

    // Controlla se questo ID di transazione è già stato elaborato per prevenire duplicati
    $existing_transaction = $database->select('panieri_acq', 'id', "txn_id = '" . $database->escapeString($txn_id) . "'");

    if (empty($existing_transaction)) {
        if ($payment_status == 'Completed') {
            // Recupera la durata del paniere da panieri_listino
            $listino_paniere_info = $database->select('panieri_listino', 'gg_attivo', "pid = " . $paniere_id);
            $gg_attivo = 0;
            if (!empty($listino_paniere_info)) {
                $gg_attivo = (int)$listino_paniere_info[0]['gg_attivo'];
            }

            // Calcola la data di scadenza
            $data_aq = date('Y-m-d');
            $data_sc = date('Y-m-d', strtotime("+$gg_attivo days", strtotime($data_aq)));

            // Inserisci nella tabella panieri_acq
            $data_to_insert = [
                'uid' => $user_id, 
                'pid' => $paniere_id,
                'data_aq' => $data_aq,
                'data_sc' => $data_sc,
            ];

            try {
                $database->insert('panieri_acq', $data_to_insert);
                error_log("Paniere purchase recorded for user $user_id, paniere $paniere_id, txn $txn_id");
            } catch (Exception $e) {
                error_log("Errore di inserimento DB per IPN: " . $e->getMessage());
            }
        } else {
            // Handle other payment statuses (e.g., 'Pending', 'Refunded', 'Failed')
            // Gestisci altri stati di pagamento (es. 'Pending', 'Refunded', 'Failed')
            error_log("IPN received with status: $payment_status for TxnID: $txn_id");
        }
    } else {
        error_log("Duplicate IPN received for TxnID: $txn_id. Skipping processing.");
    }

    $database->disconnect();

} else if (strcmp($res, "INVALID") == 0) {
    // IPN non valido, logga per investigazione
    error_log("IPN INVALID: " . $raw_post_data);
}

// Rispondi sempre con un 200 OK a PayPal
http_response_code(200);
?>
