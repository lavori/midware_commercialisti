<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('../../config.php');
require_once('../../db.php');

// Inizializzazione del database una sola volta
$database = new Database($host, $username, $password, $db);
$database->connect();


$id = $_POST['id'];
//fake data debug
$id=24;
$query = "SELECT * FROM `panieri_listino` WHERE pid= " . $id;
$listino = $database->query($query);
if($listino == '' || $listino == array()){
    $errore="Nessun Paniere a Listino";
} else {
    $listino_paniere = $listino[0];
}

// Assume $paypal_business_email and $dominio are defined in config.php
// If not, you should define them in your config.php file or here for testing:
 $paypal_business_email = 'lavori.rl@gmail.com'; // Replace with your actual PayPal business email
 $dominio = 'https://gestione.universitygtc.it'; // Replace with your actual domain

$item_name = "Accesso Paniere ID: " . $listino_paniere['pid'];
$amount = $listino_paniere['prezzo'];
$currency_code = 'EUR'; // Set your currency code (e.g., 'USD', 'EUR', 'GBP')

// Construct the PayPal payment URL
$paypal_url = "https://www.paypal.com/cgi-bin/webscr?" .
              "cmd=_xclick&" .
              "business=" . urlencode($paypal_business_email) . "&" .
              "item_name=" . urlencode($item_name) . "&" .
              "amount=" . urlencode(number_format($amount, 2, '.', '')) . "&" . // Format to 2 decimal places
              "currency_code=" . urlencode($currency_code) . "&" .
              "no_shipping=1&" . // 1 = no shipping address required (for digital goods)
              "no_note=1&" .     // 1 = no note field from buyer
              "return=" . urlencode($dominio . '/payment_success.php') . "&" . // URL for successful payment
              "cancel_return=" . urlencode($dominio . '/payment_cancel.php') . "&" . // URL for canceled payment
              "notify_url=" . urlencode($dominio . '/paypal_ipn.php'); // URL for IPN (Instant Payment Notification)

$database->disconnect();

?>
<div class="nk-block nk-block-lg">
    <div class="card card-bordered card-preview">
        <div class="card-inner">
            <p>Caricamento del modulo di pagamento PayPal...</p>
            <iframe src="<?php echo $paypal_url; ?>" 
                    width="100%" 
                    height="500px" 
                    frameborder="0" 
                    scrolling="auto" 
                    style="border: none; overflow: hidden;">
                Il tuo browser non supporta gli iframe. Clicca qui per procedere con il pagamento: 
                <a href="<?php echo $paypal_url; ?>" target="_blank">Paga con PayPal</a>
            </iframe>
        </div>
    </div>
</div>