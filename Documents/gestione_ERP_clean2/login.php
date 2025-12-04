<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// Mostra tutti gli errori
error_reporting(E_ALL);
// Attiva la visualizzazione degli errori
ini_set('display_errors', 1);
//inizializzazione variabili
require './mailing/vendor/autoload.php';
require_once('./include/config.php');


//inizializzazione variabili
SESSION_START();
$message = "";
//echo "<pre>"; print_r($_POST); echo"</pre>"; 
if (isset($_GET['logout']) && $_GET['logout'] == "1") {
    session_destroy();
    header('Location:login.php');
    exit();
}
if (isset($_GET['message']))
    $message = $_GET['message'];
if (isset($_POST['action']) && $_POST['action'] == "login") {
    require_once('./include/db.php');

    $database = new Database($host, $username, $password, $db);
    $database->connect();
    // Query login
    $utenti = $database->select("users", "*", "user like '" . $_POST['user'] . "'");
    //echo "QUI<br>";exit();
    if (isset($utenti) && count($utenti) > 0 && password_verify($_POST['password'], $utenti[0]['pwd'])) {
        $data = [
            "last_login" => "NOW()"
        ];
        $where = "id = " . $utenti[0]['id'];
        $database->update("users", $data, $where);

        // Calcola il timestamp di 14 giorni fa
        $differenzagiorni = strtotime('-14 days');

        // Controlla se l'utente è 'adminjr' E l'ultimo login è avvenuto negli ultimi 14 giorni
        if ((($utenti[0]['user'] == 'adminjr' || $utenti[0]['user']=='l.donofrio@networkgtc.it' 
            || $utenti[0]['user']=='a.bianchino@networkgtc.it' || $utenti[0]['user']=='a.bertocco@networkgtc.it') 
            && strtotime($utenti[0]['last_login']) >= $differenzagiorni) || $utenti[0]['user'] == 'admin') {
            $_SESSION['autorizzato'] = 'ok';
            $_SESSION['utente'] = $utenti[0];

            $ruolo = $database->select("rules", "*", "id = '" . $utenti[0]['ruolo'] . "'");
            $_SESSION['utente']['id_ruolo'] = $utenti[0]['ruolo'];
            $_SESSION['utente']['ruolo'] = $ruolo[0]['ruolo'];


            header('location: ./');
            exit();
            // Altrimenti, procedi con l'autenticazione a due fattori
        }

        $_SESSION['utente'] = $utenti[0];
        $ruolo = $database->select("rules", "*", "id = '" . $utenti[0]['ruolo'] . "'");
        $_SESSION['utente']['id_ruolo'] = $utenti[0]['ruolo'];
        $_SESSION['utente']['ruolo'] = $ruolo[0]['ruolo'];

        if($_SESSION['utente']['id_ruolo']== 7){
            $idDiscente=$database->select("an_discenti","id","idUser= ".$_SESSION['utente']['id']);
            $_SESSION['utente']['id_discente']=$idDiscente[0]['id'];
        }
        $database->disconnect();
        $_SESSION['OTP'] = rand(100000, 999999);

        if ($_SESSION['utente']['status'] == 'attivo' && isset($_SESSION['utente']['email']) && $_SESSION['utente']['email'] != "") {
            $_SESSION['autorizzato'] = "OTP";

            $destinatario = $_SESSION['utente']['email'];
            //Invia otp tramite email
            $mail = new PHPMailer(true);
            $mail->SMTPDebug = 0;

            try {
                // Configurazione del server SMTP
                $mail->isSMTP();
                $mail->Host = 'smtps.aruba.it'; // Specifica il server SMTP
                $mail->SMTPAuth = true;
                $mail->Username = 'no-reply@networkgtc.it'; // Usa variabili d'ambiente per la sicurezza
                $mail->Password = 'N0-R3ply@@.'; // Usa variabili d'ambiente per la sicurezza
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;
                $mail->CharSet = 'UTF-8';
                $mail->setFrom('no-reply@networkgtc.it', 'NoReply');

                // Destinatario
                $mail->addAddress($destinatario);

                // Contenuto dell'email
                $mail->isHTML(true);
                $mail->Subject = "Codice OTP";
                $mail->Body = "<div style='
                                        background-color: #fff;
                                        padding: 20px;
                                        border-radius: 8px;
                                        box-shadow: 5px 5px 20px 5px rgba(0, 0, 0, 0.25); /* Ombra uniforme su tutti i lati */
                                        width: 80%;
                                        max-width: 400px;
                                        margin: auto;
                                        box-sizing: border-box;
                                        text-align: center;
                                        font-family: Arial, sans-serif;
                                        color: #333;
                                    '>
                                        <img src='{$dominio}/images/universitygtc.png' alt='' style='display: block; margin: 0 auto; width:108; height:100;'><br><br>
                                        <h2 style='margin: 20px 0 10px;'>Il tuo codice OTP</h2>
                                        <p style='font-size: 1.5em; font-weight: bold; color: #6495ed;'>" . $_SESSION['OTP'] . "</p>
                                        <p style='margin-top: 20px; color: #555;'>Utilizza questo codice per completare la tua autenticazione.</p>
                                    </div>
                                ";

                // Invia email
                $mail->send();
            } catch (Exception $e) {
                echo "Errore nell'invio dell'email a $destinatario. Errore: {$mail->ErrorInfo}<br>";
            }

            //print_r($_SESSION);
            header('location: ./2fa.php');
            exit();
        }
        header("Location: ./");
    } else {
        $message = "<h6 style='color:red;'>USER o PASSWORD errate si prega di riprovare</h6>";
    }
} else if ($_POST && isset($_POST['action']) && $_POST['action'] == "reset") {
    require_once('./include/db.php');
    $database = new Database($host, $username, $password, $db);
    $database->connect();
    $utenti = $database->select("users", "id,user,email", "email like '" . $_POST['email'] . "'");
    // Creazione della stringa da criptare in base64 
    $userData = $utenti[0]['id'] . ':' . $utenti[0]['user'] . ':' . $utenti[0]['email'] . ':' . time();
    $base64Encoded = base64_encode($userData);

    // Costruzione del link 
    $resetLink = 'https://' . $dominio . '/login.php?action=' . urlencode($base64Encoded);

    $oggetto = "Reset Password";
    $destinatario = $utenti[0]['email'];
    //Invia otp tramite email
    $mail = new PHPMailer(true);
    $mail->SMTPDebug = 0;

    try {
        // Configurazione del server SMTP
        $mail->isSMTP();
        $mail->Host = 'smtps.aruba.it'; // Specifica il server SMTP
        $mail->SMTPAuth = true;
        $mail->Username = 'no-reply@networkgtc.it'; // Usa variabili d'ambiente per la sicurezza
        $mail->Password = 'N0-R3ply@@.'; // Usa variabili d'ambiente per la sicurezza
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom('no-reply@networkgtc.it', 'NoReply');

        // Destinatario
        $mail->addAddress($destinatario);

        // Contenuto dell'email
        $mail->isHTML(true);
        $mail->Subject = "Link Reset Password";

        $mail->Body = "<div style='
                    background-color: #fff;
                    padding: 20px;
                    border-radius: 8px;
                    box-shadow: 5px 5px 20px 5px rgba(0, 0, 0, 0.25); /* Ombra uniforme su tutti i lati */
                    width: 80%;
                    max-width: 400px;
                    margin: auto;
                    box-sizing: border-box;
                    text-align: center;
                    font-family: Arial, sans-serif;
                    color: #333;
                '>
                    <img src='{$dominio}/images/universitygtc.png' alt='' style='display: block; margin: 0 auto; width:108; height:100;'><br><br>
                    <h2 style='margin: 20px 0 10px;'>Link per il reset password</h2>
                    <p style='font-size: 1.5em; font-weight: bold; color: #6495ed;'>" . $resetLink . "</p>
                    <p style='margin-top: 20px; color: #555;'>Apri il link per resettare la password.</p>
                </div>";

        // Invia email
        $mail->send();
        $message = 'Email inviata con successo.';

    } catch (Exception $e) {
        echo "Errore nell'invio dell'email a $destinatario. Errore: {$mail->ErrorInfo}<br>";
        $message = 'Si è verificato un errore durante l\'invio dell\'email.';

    }

} else if (isset($_POST['action']) && $_POST['action'] == "reset_pwd" && $_POST['cript'] != "") {
    require_once('./include/db.php');
    $database = new Database($host, $username, $password, $db);
    $database->connect();
    // Decodifica della stringa
    $base64Decoded = base64_decode($_POST['cript']);
    $userData = explode(':', $base64Decoded);
    if (isset($_POST['conf-pwd']) && isset($_POST['pwd']) && isset($userData[3]) && $_POST['conf-pwd'] == $_POST['pwd'] && time() - $userData[3] < 3600) {
        $data = [
            "pwd" => password_hash($_POST['pwd'], PASSWORD_DEFAULT)
        ];
        $where = "id = " . $userData[0] . " and user = '" . $userData[1] . "' and email = '" . $userData[2] . "'";
        $database->update("users", $data, $where);
        $database->disconnect();
        $message = "<span class='text-success'>Password cambiata con successo</span>";
        header("Location: ./login.php?message=" . $message);
    } elseif ($_POST['conf-pwd'] != $_POST['pwd']) {
        $message = "<span class='text-danger'>Le password non coincidono</span>";
        $action = "reset_pwd";
        $criptata = $base64Decoded;
    } elseif (isset($userData[3]) && time() - $userData[3] > 3600) {
        $message = "<span class='text-danger'>Il link &egrave; scaduto</span>";
        $action = "reset_pwd";
        $criptata = $base64Decoded;
    }
} else if (!$_POST && isset($_GET['action']) && $_GET['action'] != "") {
    // Decodifica della stringa
    $base64Decoded = base64_decode($_GET['action']);
    $userData = explode(':', $base64Decoded);
    if (time() - $userData[3] < 3600) {
        $action = "reset_pwd";
        $criptata = $base64Decoded;
    }
}

?>


<!DOCTYPE html>
<html lang="ita" class="js">

<head>
    <base href="./">
    <meta charset="utf-8">
    <meta name="author" content="Roberto Fortunato">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="<?php echo $titolo; ?>">
    <!-- Fav Icon  -->
    <link rel="shortcut icon" href="./images/favicon.png">
    <!-- Page Title  -->
    <title>Accesso Area Riservata | <?php echo $titolo; ?> </title>
    <!-- StyleSheets  -->
    <link rel="stylesheet" href="./assets/css/dashlite.css?ver=3.1.3">
    <link id="skin-default" rel="stylesheet" href="./assets/css/theme.css?ver=3.1.3">
</head>

<body class="nk-body npc-default pg-auth">
    <div class="nk-app-root">
        <!-- main @s -->
        <div class="nk-main ">
            <!-- wrap @s -->
            <div class="nk-wrap nk-wrap-nosidebar">
                <!-- content @s -->
                <div class="nk-content ">
                    <div class="nk-split nk-split-page nk-split-lg">
                        <div class="nk-split-content nk-block-area nk-block-area-column nk-auth-container bg-white">
                            <div class="absolute-top-right d-lg-none p-3 p-sm-5">
                                <a href="#" class="toggle btn-white btn btn-icon btn-light" data-target="athPromo"><em
                                        class="icon ni ni-info"></em></a>
                            </div>
                            <div class="nk-block nk-block-middle nk-auth-body">
                                <div class="brand-logo pb-5">
                                    <a href="./index.php" class="logo-link">
                                        <img class="logo-light logo-img logo-img-lg" src="./images/universitygtc.png"
                                            srcset="./images/universitygtc.png" alt="logo">
                                        <img class="logo-dark logo-img logo-img-lg" src="./images/universitygtc.png"
                                            srcset="./images/universitygtc.png" alt="logo-dark">
                                    </a>
                                </div>
                                <?php if ((isset($action) && $action == "reset_pwd") || (isset($_POST['action']) && $_POST['action'] == "reset_pwd")) { ?>
                                    <div id="reset_pwd">
                                        <div class="nk-block-head">
                                            <div class="nk-block-head-content">
                                                <h5 class="nk-block-title">Reset Password</h5>
                                                <div class="nk-block-des">
                                                    <p>Resetta La tua password.</p>
                                                    <?php
                                                    if ($message != "") {
                                                        echo '<p class="was-validated">' . $message . '</p>';
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div><!-- .nk-block-head -->
                                        <form action="./login.php" class="form-validate is-alter" autocomplete="off"
                                            method="POST">
                                            <input type="hidden" name="action" value="reset_pwd">
                                            <input type="hidden" name="cript" value="<?php if (isset($_POST['cript'])) {
                                                echo $_POST['cript'];
                                            } else {
                                                echo $_GET['action'];
                                            } ?>">
                                            <div class="form-group">
                                                <div class="form-label-group">
                                                    <label class="form-label" for="pwd">Password</label>
                                                </div>
                                                <div class="form-control-wrap">
                                                    <a tabindex="-1" href="#"
                                                        class="form-icon form-icon-right passcode-switch lg"
                                                        data-target="pwd">
                                                        <em class="passcode-icon icon-show icon ni ni-eye"></em>
                                                        <em class="passcode-icon icon-hide icon ni ni-eye-off"></em>
                                                    </a>
                                                    <input name="pwd" autocomplete="off" type="password"
                                                        class="form-control form-control-lg" required id="pwd">
                                                </div>
                                            </div><!-- .form-group -->
                                            <div class="form-group">
                                                <div class="form-label-group">
                                                    <label class="form-label" for="conf-pwd">Conferma Password</label>
                                                    <!--<a class="link link-primary link-sm" tabindex="-1" href="./auths/auth-reset.html">Dimenticata la password?</a>-->
                                                </div>
                                                <div class="form-control-wrap">
                                                    <a tabindex="-1" class="form-icon form-icon-right passcode-switch1 lg"
                                                        data-target="conf_pwd">
                                                        <em class="passcode-icon icon-show icon ni ni-eye"></em>
                                                        <em class="passcode-icon icon-hide icon ni ni-eye-off"></em>
                                                    </a>
                                                    <input name="conf-pwd" autocomplete="off" type="password"
                                                        class="form-control form-control-lg" required id="conf-pwd">
                                                </div>
                                            </div><!-- .form-group -->
                                            <div class="form-group">
                                                <button class="btn btn-lg btn-primary btn-block">Resetta la Pasword</button>
                                            </div>
                                        </form>
                                        <!-- form -->
                                    </div>
                                <?php } else { ?>
                                    <div id="ath">
                                        <div class="nk-block-head">
                                            <div class="nk-block-head-content">
                                                <h5 class="nk-block-title">Accesso</h5>
                                                <div class="nk-block-des">
                                                    <p>Per accedere alla Dashboard del sistema usa la tua user e password.
                                                    </p>
                                                    <?php
                                                    if ($message != "") {
                                                        echo '<p class="was-validated">' . $message . '</p>';
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div><!-- .nk-block-head -->
                                        <form action="./login.php" class="form-validate is-alter" autocomplete="off"
                                            method="POST">
                                            <input type="hidden" name="action" value="login">
                                            <div class="form-group">
                                                <div class="form-label-group">
                                                    <label class="form-label" for="user">Username</label>
                                                </div>
                                                <div class="form-control-wrap">
                                                    <input name="user" autocomplete="off" type="text"
                                                        class="form-control form-control-lg" required id="user"
                                                        placeholder="Inserisci la tua username">
                                                </div>
                                            </div><!-- .form-group -->
                                            <div class="form-group">
                                                <div class="form-label-group">
                                                    <label class="form-label" for="password">Password</label>
                                                    <!--<a class="link link-primary link-sm" tabindex="-1" href="./auths/auth-reset.html">Dimenticata la password?</a>-->
                                                </div>
                                                <div class="form-control-wrap">
                                                    <a tabindex="-1" href="#"
                                                        class="form-icon form-icon-right passcode-switch lg"
                                                        data-target="password">
                                                        <em class="passcode-icon icon-show icon ni ni-eye"></em>
                                                        <em class="passcode-icon icon-hide icon ni ni-eye-off"></em>
                                                    </a>
                                                    <input name="password" autocomplete="off" type="password"
                                                        class="form-control form-control-lg" required id="password"
                                                        placeholder="Inserisci la tua password">
                                                </div>
                                            </div><!-- .form-group -->
                                            <div class="form-group">
                                                <input type="submit" class="btn btn-lg btn-primary btn-block"
                                                    value="Accedi">
                                            </div>
                                        </form>
                                        <!-- form -->
                                        <div class="form-note-s2 pt-5">
                                            <p>Password Dimenticata? <a
                                                    OnClick="document.getElementById('pwd').style.display='block';document.getElementById('ath').style.display='none';"
                                                    style="cursor: pointer;"><strong>Clicca qui</strong></a> per cambiare la
                                                password.</p>
                                        </div>
                                        <?php //echo"<pre>"; print_r($_SESSION); echo"</pre>"; ?>
                                    </div>
                                    <div id="pwd" style="display: none;">
                                        <div class="nk-block-head">
                                            <div class="nk-block-head-content">
                                                <h5 class="nk-block-title">Reset password</h5>
                                                <div class="nk-block-des">
                                                    <p>Se hai dimenticato la password, inserisci la tua email ti invieremo
                                                        la mail di reset.</p>
                                                </div>
                                            </div>
                                        </div><!-- .nk-block-head -->
                                        <form class="form-validate is-alter" autocomplete="off" method="POST">
                                            <input type="hidden" name="action" value="reset">
                                            <div class="form-group">
                                                <div class="form-label-group">
                                                    <label class="form-label" for="default-01">Email</label>
                                                </div>
                                                <div class="form-control-wrap">
                                                    <input type="text" class="form-control form-control-lg" id="email"
                                                        name="email" placeholder="Enter your email address">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <button class="btn btn-lg btn-primary btn-block">Send Reset Link</button>
                                            </div>
                                        </form><!-- form -->
                                        <div class="form-note-s2 pt-5">
                                            <a OnClick="document.getElementById('ath').style.display='block';document.getElementById('pwd').style.display='none';"
                                                style="cursor: pointer;"><em class="icon ni ni-arrow-left"></em> <strong>
                                                    Ritorna al Login</strong></a>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div><!-- .nk-split-content -->
                        <div class="nk-split-content nk-split-stretch bg-lighter d-flex toggle-break-lg toggle-slide toggle-slide-right"
                            data-toggle-body="true" data-content="athPromo" data-toggle-screen="lg"
                            data-toggle-overlay="true">
                            <div class="slider-wrap w-100 w-max-550px p-3 p-sm-5 m-auto">
                                <div class="slider-init" data-slick='{"dots":true, "arrows":false}'>
                                    <!-- .slider-item 1-->
                                    <div class="slider-item">
                                        <div class="nk-feature nk-feature-center">
                                            <div class="nk-feature-img">
                                                <img class="round" src="./images/slides/promo-a.png"
                                                    srcset="./images/slides/promo-a2x.png 2x" alt="">
                                            </div>
                                            <div class="nk-feature-content py-4 p-sm-5">
                                                <h4><?php echo $titolo; ?></h4>
                                                <p><!-- Descrizione --></p>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- .slider-item 1-->
                                    <!-- .slider-item 2-->
                                    <div class="slider-item">
                                        <div class="nk-feature nk-feature-center">
                                            <div class="nk-feature-img">
                                                <img class="round" src="./images/slides/promo-b.png"
                                                    srcset="./images/slides/promo-b2x.png 2x" alt="">
                                            </div>
                                            <div class="nk-feature-content py-4 p-sm-5">
                                                <h4><?php echo $titolo; ?></h4>
                                                <p><!-- Descrizione --></p>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- .slider-item 2-->
                                    <!-- .slider-item 3-->
                                    <div class="slider-item">
                                        <div class="nk-feature nk-feature-center">
                                            <div class="nk-feature-img">
                                                <img class="round" src="./images/slides/promo-c.png"
                                                    srcset="./images/slides/promo-c2x.png 2x" alt="">
                                            </div>
                                            <div class="nk-feature-content py-4 p-sm-5">
                                                <h4><?php echo $titolo; ?></h4>
                                                <p><!-- Descrizione --></p>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- .slider-item 3-->
                                </div><!-- .slider-init -->
                                <div class="slider-dots"></div>
                                <div class="slider-arrows"></div>
                            </div><!-- .slider-wrap -->
                        </div><!-- .nk-split-content -->
                    </div><!-- .nk-split -->
                </div>
                <!-- wrap @e -->
            </div>
            <!-- content @e -->
        </div>
        <!-- main @e -->
    </div>
    <!-- app-root @e -->
    <!-- JavaScript -->
    <script src="./assets/js/bundle.js?ver=3.1.3"></script>
    <script src="./assets/js/scripts.js?ver=3.1.3"></script>
    <script>
        $(document).ready(function () {
            $('.passcode-switch1').click(function () {
                var inputField = $('#conf_pwd');
                if (inputField.attr('type') === 'password') {
                    inputField.attr('type', 'text');
                } else {
                    inputField.attr('type', 'password');
                }
            });
        });
    </script>

    <!-- select region modal -->



</html>