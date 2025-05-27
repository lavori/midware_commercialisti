<?php 
    //inizializzazione variabili
    SESSION_START();
    $message="";
    //echo "<pre>"; print_r($_SESSION); echo"</pre>";// exit();
    if ($_SESSION['autorizzato']=="OTP"){
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $otpInserito = $_POST['otp'];   
            if ($otpInserito == $_SESSION['OTP']) {
                // OTP corretto, autentica l'utente
                $_SESSION['autorizzato'] = 'ok';
                unset($_SESSION['OTP']); // Rimuove l'OTP per sicurezza
        
                // Reindirizza alla pagina riservata
                if($_SESSION['ruolo']=="Operatore"){
                    header("Location: /app"); exit(); 
                } else {
                    header("Location: /admin"); exit(); 
                }
            } elseif($_GET['newcode']==1){
                unset($_SESSION['OTP']); // Rimuove l'OTP per sicurezza
                $otp = rand(100000, 999999); // Genera un codice OTP casuale a 6 cifre
                $_SESSION['OTP'] = $otp;
                inviaOTP($_SESSION['users']['email'], $otp);
            } else {
                $message = "Codice OTP non corretto. Riprova.";
            } 
        }
    } elseif ($_SESSION['autorizzato']=="ok"){
        header('Location:/admin'); exit();
    } else{
        header('Location:login.php'); exit();
    }


    function inviaOTP($email, $otp) {
        $subject = "Il tuo codice di verifica";
        $message = "Il tuo codice di accesso è: $otp";
        mail($email, $subject, $message); // Usa una libreria o un servizio per invii più sicuri
    }
?>
<!DOCTYPE html>
<html lang="ita" class="js">
<head>
    <base href="./">
    <meta charset="utf-8">
    <meta name="author" content="Roberto Lamanna">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Gestione produttiva e roportistica vendite.">
    <!-- Fav Icon  -->
    <link rel="shortcut icon" href="./images/favicon.png">
    <!-- Page Title  -->
    <title>Autenticazione a due Fattori | Controllo Picking</title>
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
                                <a href="#" class="toggle btn-white btn btn-icon btn-light" data-target="athPromo"><em class="icon ni ni-info"></em></a>
                            </div>
                            <div class="nk-block nk-block-middle nk-auth-body">
                                <div class="brand-logo pb-5">
                                    <a href="./index.php" class="logo-link">
                                        <img class="logo-light logo-img logo-img-lg" src="./images/napolifacility/napolifacility.jpg" 
                                            srcset="./images/napolifacility/napolifacility.jpg 2x" style="scale: 5; margin-left: 12em;" alt="logo">
                                        <img class="logo-dark logo-img logo-img-lg" src="./images/napolifacility/napolifacility.jpg" 
                                            srcset="./images/napolifacility/napolifacility.jpg 2x" style="scale: 5; margin-left: 12em;" alt="logo-dark">
                                    </a>
                                </div>
                                <div id="ath">
                                    <div class="nk-block-head">
                                        <div class="nk-block-head-content">
                                            <h5 class="nk-block-title">Riconoscimento a due Fattori</h5>
                                            <div class="nk-block-des">
                                                <p>Per accedere alla Dashboard del sistema inserisci l'OTP che hai ricevuto via mail.</p>
                                                <?php 
                                                    if($message!=""){
                                                        echo '<p class="was-validated">'.$message.'</p>';
                                                    } 
                                                ?>
                                            </div>
                                        </div>
                                    </div><!-- .nk-block-head -->
                                    <form action="./2fa.php" class="form-validate is-alter" autocomplete="off" method="POST">
                                        <input type="hidden" name="action" value="login">
                                        <div class="form-group">
                                            <div class="form-label-group">
                                                <label class="form-label" for="otp">Codice OTP</label>
                                            </div>
                                            <div class="form-control-wrap">
                                                <input name="otp" autocomplete="off" type="text" class="form-control form-control-lg" required id="user" placeholder="Inserisci il codice OTP ricevuto via mail">
                                            </div>
                                        </div><!-- .form-group -->
                                        <div class="form-group">
                                            
                                        </div><!-- .form-group -->
                                        <div class="form-group">
                                            <button class="btn btn-lg btn-primary btn-block">Verifica</button>
                                        </div>
                                    </form>
                                    <!-- form -->
                                    <div class="form-note-s2 pt-5">
                                        <p>Se non hai ricevuto il codice OTP, controlla nello spam o richiedilo di nuovo <a href="2fa.php?newcode=1" style="cursor: pointer;"><strong>Richiedi Ora</strong></a></p>
                                    </div>
                                </div>
                            </div>
                        </div><!-- .nk-split-content -->
                        <div class="nk-split-content nk-split-stretch bg-lighter d-flex toggle-break-lg toggle-slide toggle-slide-right" data-toggle-body="true" data-content="athPromo" data-toggle-screen="lg" data-toggle-overlay="true">
                            <div class="slider-wrap w-100 w-max-550px p-3 p-sm-5 m-auto">
                                <div class="slider-init" data-slick='{"dots":true, "arrows":false}'>
                                    <div class="slider-item">
                                        <div class="nk-feature nk-feature-center">
                                            <div class="nk-feature-img">
                                                <img class="round" src="./images/slides/promo-a.png" srcset="./images/slides/promo-a2x.png 2x" alt="">
                                            </div>
                                            <div class="nk-feature-content py-4 p-sm-5">
                                                <h4>Gestione Produzione</h4>
                                                <p><!-- Descrizione --></p>
                                            </div>
                                        </div>
                                    </div><!-- .slider-item -->
                                    <div class="slider-item">
                                        <div class="nk-feature nk-feature-center">
                                            <div class="nk-feature-img">
                                                <img class="round" src="./images/slides/promo-b.png" srcset="./images/slides/promo-b2x.png 2x" alt="">
                                            </div>
                                            <div class="nk-feature-content py-4 p-sm-5">
                                                <h4>Gestione Produzione</h4>
                                                <p><!-- Descrizione --></p>
                                            </div>
                                        </div>
                                    </div><!-- .slider-item -->
                                    <div class="slider-item">
                                        <div class="nk-feature nk-feature-center">
                                            <div class="nk-feature-img">
                                                <img class="round" src="./images/slides/promo-c.png" srcset="./images/slides/promo-c2x.png 2x" alt="">
                                            </div>
                                            <div class="nk-feature-content py-4 p-sm-5">
                                                <h4>Gestione Produzione</h4>
                                                <p><!-- Descrizione --></p>
                                            </div>
                                        </div>
                                    </div><!-- .slider-item -->
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
   
    <!-- select region modal -->
</html>