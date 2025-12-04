<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Abilita la segnalazione degli errori
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Includi l'autoloader di Composer
require 'vendor/autoload.php';

// Crea una nuova istanza di PHPMailer
$mail = new PHPMailer(true);
$mail->SMTPDebug = 2;  
try {
    // Configurazione del server SMTP
    $mail->isSMTP();
    $mail->Host       = 'smtps.aruba.it'; // Specifica il server SMTP principale e di backup
    $mail->SMTPAuth   = true;
    $mail->Username   = 'r.lamanna@networkgtc.it'; // SMTP username
    $mail->Password   = 'Lmna1234.'; // SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Abilita la crittografia TLS; `PHPMailer::ENCRYPTION_SMTPS` anche accettato
    $mail->Port       = 465; // Porta TCP a cui connettersi

    // Destinatari
    $mail->setFrom('r.lamanna@networkGTC.it', 'Network GTC');
    //$mail->addAddress('r.fortunato@networkgtc.it'); // Aggiungi un destinatario
    $mail->addAddress('lavori.rl@gmail.com'); // Aggiungi un destinatario

    // Allegati
    $mail->addEmbeddedImage('../Post_ferie.jpg', 'image1', '../Post_ferie.jpg'); // Aggiungi un allegato

    // Contenuto
    $mail->isHTML(true); // Imposta il formato email a HTML
    $mail->Subject = 'Buone vacanze da Network GTC';
    $mail->Body    = '
    <!DOCTYPE html>
    <html>
    <body>
        <h2>Network ITC ti augura buone vacanze</h2>
        <p>Siamo chiusi per ferie dal 10 agosto al 25 agosto</p>
        <img src="cid:image1" alt="Buone vacanze">
    </body>
    </html>
    ';

    $mail->send();
    echo 'Email inviata con successo!';
} catch (Exception $e) {
    echo "Invio email fallito. Errore Mailer: {$mail->ErrorInfo}";
}
?>
