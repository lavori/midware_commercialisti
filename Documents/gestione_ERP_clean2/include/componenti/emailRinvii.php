<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('../config.php');
require_once('../db.php');

// Inizializzazione del database una sola volta
$database = new Database($host, $username, $password, $db);
$database->connect();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../../mailing/vendor/autoload.php';


$queryRinvii = "SELECT 
                    COUNT(se.id) AS numero_rinvii,
                    se.id_esame,
                    se.id_discente,
                    ae.nome AS nome_esame,
                    ad.nome AS nome_discente,
                    ad.cognome AS cognome_discente
                FROM scadenzaEsami se
                JOIN an_esami ae ON se.id_esame = ae.id
                JOIN an_discenti ad ON se.id_discente = ad.id
                WHERE se.motivazione = 'rinviato' AND se.data_invio_esame IS NULL
                GROUP BY se.id_discente, se.id_esame, ae.nome, ad.nome, ad.cognome";
$rinvii = $database->query($queryRinvii);

if ($rinvii >= 3) {
    $destinatario = "r.fortunato@networkgtc.it";
    $oggetto = "Avviso Rinvii Esame";
    $messaggio_personalizzato = "<table width='100%' cellpadding='0' cellspacing='0' bgcolor='#f5f6fa' style='font-family: Arial, sans-serif;'>
                                <tr>
                                    <td align='center'>
                                    <table width='600' cellpadding='0' cellspacing='0' bgcolor='#ffffff' style='margin: 30px auto; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.05);'>
                                        
                                        <!-- HEADER -->
                                        <tr>
                                        <td align='center' style='padding: 30px 0;'>
                                            <img src='https://gestione.universitygtc.it/universitygtc.png' width='120' style='display:block;'>
                                            <h2 style='color: #364a63; font-size: 18px; margin: 10px 0 0;'>
                                                Avviso Rinvii Esame
                                            </h2>
                                        </td>
                                        </tr>

                                        <!-- CONTENUTO -->
                                        <tr>
                                        <td style='padding: 0 40px 20px;'>
                                            <h3 style='color: #1f2d3d; font-size: 16px;'>Ciao,</h3>

                                            <p style='color: #526484; font-size: 14px; line-height: 1.6;'>
                                                L'esame <b>" . $rinvii[0]['nome_esame'] . "</b> per il discente <b>" . $rinvii[0]['nome_discente'] . " " . $rinvii[0]['cognome_discente'] . "</b>
                                                è stato rinviato troppe volte.
                                            </p>
                                            <p style='color: #1f2d3d; font-size: 14px;'>
                                            <b>WebMaster</b><br>
                                            </p>
                                        </td>
                                        </tr>

                                        <!-- FOOTER -->
                                        <tr>
                                        <td style='padding: 20px 40px; border-top: 1px solid #e5e9f2; text-align: center;'>
                                            <p style='font-size: 12px; color: #8392a5;'>
                                            Copyright © 2025<br>
                                            <a href='https://gestione-universitygtc.it' style'color: #6a82fb;'>https://gestione-universitygtc.it</a>.
                                            </p>
                                        </td>
                                        </tr>

                                    </table>
                                    </td>
                                </tr>
                                </table>";
    $filesNames = "";
    $invii = "";
    InviaEmail($oggetto, $destinatario, $messaggio_personalizzato, $filesNames, $invii);

    $dataToUpdate=[
        'data_invio_esame' => date('Y-m-d')
    ];

    $where="id_esame= " . $rinvii[0]['id_esame']. " AND motivazione= 'rinviato'";

    $updateData=$database->update("scadenzaEsami",$dataToUpdate,$where);

}


function InviaEmail($oggetto, $destinatario, $messaggio_personalizzato, $fileNames, $invii)
{

    // Crea una nuova istanza di PHPMailer
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    $mail->SMTPDebug = 0;
    try {
        // Configurazione del server SMTP
        $mail->isSMTP();
        $mail->Host = 'smtps.aruba.it'; // Specifica il server SMTP
        $mail->SMTPAuth = true;
        $mail->Username = 'comunicazione@networkgtc.it'; // SMTP username
        $mail->Password = 'Ctnt378@'; // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Abilita la crittografia TLS
        $mail->Port = 465; // Porta SMTP

        // Destinatari
        $mail->setFrom('comunicazione@networkgtc.it', 'NoReply'); // Nome visibile mascherato            //$mail->addReplyTo(); // Mittente per risposte
        $mail->addAddress($destinatario); // Aggiungi il destinatario

        // Contenuto dell'email
        $mail->isHTML(true); // Imposta il formato email a HTML
        $mail->Subject = mb_convert_encoding($oggetto, 'UTF-8', 'auto');
        $mail->Body = mb_convert_encoding($messaggio_personalizzato, 'UTF-8', 'auto'); // Corpo del messaggio

        // Aggiungi gli allegati
        if (is_array($fileNames)) {
            foreach ($fileNames as $filePath) {
                if (file_exists($filePath)) {
                    $mail->addAttachment($filePath);
                }
            }
        }

        // Invia email
        $mail->send();

    } catch (Exception $e) {
        $invii .= "Errore nell'invio dell'email a $destinatario. Errore: {$mail->ErrorInfo}<br>";
    }
}

?>