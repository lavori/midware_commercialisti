<?php 
   //inizializzazione variabili
   SESSION_START();
   $message="";
   require_once('./include/config.php');
echo "<pre>"; print_r($_POST); echo"</pre>"; 
   if(isset($_GET['logout']) && $_GET['logout']=="1"){ 
       session_destroy();
       header('Location:login.php'); exit();
   }
   if(isset($_SESSION['utente']) && isset($_SESSION['autorizzato'])){
       if($_SESSION['utente']!=array() && $_SESSION['autorizzato']=='ok'){
        if($_SESSION['ruolo']=="Operatore"){
            header("Location: /app"); exit(); 
        } else {
            header("Location: /admin"); exit(); 
        }
       }
   }
   if(isset($_GET['message'])) $message=$_GET['message'];

   if(isset($_POST['action']) && $_POST['action']=="login"){  
        require_once('./include/db.php');
        echo "QUI<br>"; 
        $database = new Database($host, $username, $password, $db); 
        $database->connect(); 
        // Query login
        $utenti = $database->select("users", "*", "user like '".$_POST['user']."'");
        if (isset($utenti) && count($utenti)>0 && MD5($_POST['password'])==$utenti[0]['pwd']){
            echo $utenti[0]['pwd']; 
           $data = [
               "last_login" => "NOW()"
           ];
           $where = "id = ".$utenti[0]['id'];
           //$database->update("users", $data, $where);
           $_SESSION['utente'] = $utenti[0];
           $ruolo = $database->select("rules", "*", "id = '".$utenti[0]['ruolo']."'");
           $_SESSION['ruolo'] = $ruolo[0]['ruolo'];
           $_SESSION['permessi'] = $ruolo[0]['permessi'];
           $database->disconnect();
           $_SESSION['OTP']=rand(100000,999999);
           echo "<pre>"; print_r($_SESSION); echo "</pre>"; //exit();
           if($_SESSION['utente']['status']=='attivo' && isset($_SESSION['utente']['email']) && $_SESSION['utente']['email']!=""){
                $_SESSION['autorizzato']="OTP"; 
                inviaOTP($_SESSION['utente']['email'], $_SESSION['OTP']); 
                header("Location: ./2fa.php?otp=".$_SESSION['OTP']); exit();
            } elseif($_SESSION['utente']['abilitazione']==1 && (!isset($_SESSION['users']['email']) || $_SESSION['users']['email']=="")){  
                $_SESSION['autorizzato']="ok"; 
                if($_SESSION['ruolo']=="Operatore"){
                    header("Location: /app"); exit(); 
                } else {
                    header("Location: /admin"); exit(); 
                }
            }
           header("Location: ./"); 
        } else{ 
           $message="USER o PASSWORD errate si prega di riprovare"; 
        }
    } else if($_POST && isset($_POST['action']) && $_POST['action']=="reset"){ 
       // Query login 
       $utenti = $database->select("utenti", "id,user,email", "email like '".$_POST['email']."'"); 

       // Creazione della stringa da criptare in base64 
       $userData = $utenti[0]['id'] . ':' . $utenti[0]['user'] . ':' . $utenti[0]['email'] . ':' . time(); 
       $base64Encoded = base64_encode($userData); 

       // Costruzione del link 
       $resetLink = 'https://'.$dominio.'/login.php?action=' . urlencode($base64Encoded); 

       // Contenuto dell'email 
       $to = $utenti[0]['email']; 
       $subject = 'Reset Password'; 
       $message = 'Clicca sul seguente link per resettare la tua password: ' . $resetLink; 
       $headers = 'From: no_reply@' .$dominio. "\r\n" . 
                  'Reply-To: info@' .$dominio. "\r\n" . 
                  'X-Mailer: PHP/' . phpversion(); 

       // Invio dell'email 
       $mailSent = mail($to, $subject, $message, $headers); 

       if ($mailSent) { 
           $message =  'Email inviata con successo.'; 
       } else { 
           $message =  'Si è verificato un errore durante l\'invio dell\'email.'; 
       }

   } else if(isset($_POST['action']) && $_POST['action']=="reset_pwd" && $_POST['cript']!=""){
       // Decodifica della stringa
       $base64Decoded = base64_decode($_POST['cript']);
       $userData = explode(':', $base64Decoded);
       if(isset($_POST['conf-pwd']) && isset($_POST['pwd']) && isset($userData[3]) && $_POST['conf-pwd']==$_POST['pwd'] && time()-$userData[3]<3600){
           $data = [
               "pwd" => MD5($_POST['pwd'])
           ];
           $where = "id = ".$userData[0]." and user = '".$userData[1]."' and email = '".$userData[2]."'";
           $database->update("users", $data, $where);
           $database->disconnect();
           $message="<span class='text-success'>Password cambiata con successo</span>";
           header("Location: ./login.php?message=".$message);
       } elseif($_POST['conf-pwd']!=$_POST['pwd']){
           $message="<span class='text-danger'>Le password non coincidono</span>";
           $action="reset_pwd";
           $criptata=$base64Decoded;
       } elseif(isset($userData[3]) && time()-$userData[3]>3600){
           $message="<span class='text-danger'>Il link &egrave; scaduto</span>";
           $action="reset_pwd";
           $criptata=$base64Decoded;
       }
   } else if(!$_POST && isset($_GET['action']) && $_GET['action']!=""){
       // Decodifica della stringa
       $base64Decoded = base64_decode($_GET['action']);
       $userData = explode(':', $base64Decoded);
       if(time()-$userData[3]<3600){
           $action="reset_pwd";
           $criptata=$base64Decoded;
       }
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
                               <a href="#" class="toggle btn-white btn btn-icon btn-light" data-target="athPromo"><em class="icon ni ni-info"></em></a>
                           </div>
                           <div class="nk-block nk-block-middle nk-auth-body">
                               <div class="brand-logo pb-5">
                                   <a href="./index.php" class="logo-link">
                                       <img class="logo-light logo-img logo-img-lg" src="./images/corpo-capelli/logo.png" srcset="./images/corpo-capelli/logo2x.png 2x" alt="logo">
                                       <img class="logo-dark logo-img logo-img-lg" src="./images/corpo-capelli/logo-dark.png" srcset="./images/corpo-capelli/logo-dark2x.png 2x" alt="logo-dark">
                                   </a>
                               </div>
                           <?php if((isset($action) && $action=="reset_pwd") || (isset($_POST['action']) && $_POST['action']=="reset_pwd")){ ?>
                               <div id="reset_pwd">
                                   <div class="nk-block-head">
                                       <div class="nk-block-head-content">
                                           <h5 class="nk-block-title">Reset Password</h5>
                                           <div class="nk-block-des">
                                               <p>Resetta La tua password.</p>
                                               <?php 
                                                   if($message!=""){
                                                       echo '<p class="was-validated">'.$message.'</p>';
                                                   } 
                                               ?>
                                           </div>
                                       </div>
                                   </div><!-- .nk-block-head -->
                                   <form action="./login.php" class="form-validate is-alter" autocomplete="off" method="POST">
                                       <input type="hidden" name="action" value="reset_pwd">
                                       <input type="hidden" name="cript" value="<?php if(isset($_POST['cript'])){ echo $_POST['cript']; } else {echo $_GET['action']; }?>">
                                       <div class="form-group">
                                           <div class="form-label-group">
                                               <label class="form-label" for="pwd">Password</label>
                                           </div>
                                           <div class="form-control-wrap">
                                               <a tabindex="-1" href="#" class="form-icon form-icon-right passcode-switch lg" data-target="pwd">
                                                   <em class="passcode-icon icon-show icon ni ni-eye"></em>
                                                   <em class="passcode-icon icon-hide icon ni ni-eye-off"></em>
                                               </a>
                                               <input name="pwd" autocomplete="off" type="password" class="form-control form-control-lg" required id="pwd">
                                           </div>
                                       </div><!-- .form-group -->
                                       <div class="form-group">
                                           <div class="form-label-group">
                                               <label class="form-label" for="conf-pwd">Conferma Password</label>
                                               <!--<a class="link link-primary link-sm" tabindex="-1" href="./auths/auth-reset.html">Dimenticata la password?</a>-->
                                           </div>
                                           <div class="form-control-wrap">
                                               <a tabindex="-1"  class="form-icon form-icon-right passcode-switch1 lg" data-target="conf_pwd">
                                                   <em class="passcode-icon icon-show icon ni ni-eye"></em>
                                                   <em class="passcode-icon icon-hide icon ni ni-eye-off"></em>
                                               </a>
                                               <input name="conf-pwd" autocomplete="off" type="password" class="form-control form-control-lg" required id="conf-pwd">
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
                                               <p>Per accedere alla Dashboard del sistema usa la tua user e password.</p>
                                               <?php 
                                                   if($message!=""){
                                                       echo '<p class="was-validated">'.$message.'</p>';
                                                   } 
                                               ?>
                                           </div>
                                       </div>
                                   </div><!-- .nk-block-head -->
                                   <form action="./login.php" class="form-validate is-alter" autocomplete="off" method="POST">
                                       <input type="hidden" name="action" value="login">
                                       <div class="form-group">
                                           <div class="form-label-group">
                                               <label class="form-label" for="user">Username</label>
                                           </div>
                                           <div class="form-control-wrap">
                                               <input name="user" autocomplete="off" type="text" class="form-control form-control-lg" required id="user" placeholder="Inserisci la tua username">
                                           </div>
                                       </div><!-- .form-group -->
                                       <div class="form-group">
                                           <div class="form-label-group">
                                               <label class="form-label" for="password">Password</label>
                                               <!--<a class="link link-primary link-sm" tabindex="-1" href="./auths/auth-reset.html">Dimenticata la password?</a>-->
                                           </div>
                                           <div class="form-control-wrap">
                                               <a tabindex="-1" href="#" class="form-icon form-icon-right passcode-switch lg" data-target="password">
                                                   <em class="passcode-icon icon-show icon ni ni-eye"></em>
                                                   <em class="passcode-icon icon-hide icon ni ni-eye-off"></em>
                                               </a>
                                               <input name="password" autocomplete="off" type="password" class="form-control form-control-lg" required id="password" placeholder="Inserisci la tua password">
                                           </div>
                                       </div><!-- .form-group -->
                                       <div class="form-group">
                                           <input type="submit" class="btn btn-lg btn-primary btn-block" value="Accedi">
                                       </div>
                                   </form>
                                   <!-- form -->
                                   <div class="form-note-s2 pt-5">
                                       <p>Password Dimenticata? <a OnClick="document.getElementById('pwd').style.display='block';document.getElementById('ath').style.display='none';" style="cursor: pointer;"><strong>Clicca qui</strong></a> per cambiare la password.</p>
                                   </div>
                                   <?php //echo"<pre>"; print_r($_SESSION); echo"</pre>"; ?>
                               </div>
                               <div id="pwd" style="display: none;">                       
                                   <div class="nk-block-head">
                                       <div class="nk-block-head-content">
                                           <h5 class="nk-block-title">Reset password</h5>
                                           <div class="nk-block-des">
                                               <p>Se hai dimenticato la password, inserisci la tua email ti invieremo la mail di reset.</p>
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
                                               <input type="text" class="form-control form-control-lg" id="email" name="email" placeholder="Enter your email address">
                                           </div>
                                       </div>
                                       <div class="form-group">
                                           <button class="btn btn-lg btn-primary btn-block">Send Reset Link</button>
                                       </div>
                                   </form><!-- form -->
                                   <div class="form-note-s2 pt-5">
                                       <a OnClick="document.getElementById('ath').style.display='block';document.getElementById('pwd').style.display='none';"  style="cursor: pointer;"><em class="icon ni ni-arrow-left"></em> <strong> Ritorna al Login</strong></a>
                                   </div>
                               </div>
                           <?php } ?>
                           </div>
                       </div><!-- .nk-split-content -->
                       <div class="nk-split-content nk-split-stretch bg-lighter d-flex toggle-break-lg toggle-slide toggle-slide-right" data-toggle-body="true" data-content="athPromo" data-toggle-screen="lg" data-toggle-overlay="true">
                           <div class="slider-wrap w-100 w-max-550px p-3 p-sm-5 m-auto">
                               <div class="slider-init" data-slick='{"dots":true, "arrows":false}'>
                                   <!-- .slider-item 1-->
                                    <div class="slider-item">
                                        <div class="nk-feature nk-feature-center">
                                            <div class="nk-feature-img">
                                                <img class="round" src="./images/slides/promo-a.png" srcset="./images/slides/promo-a2x.png 2x" alt="">
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
                                                <img class="round" src="./images/slides/promo-b.png" srcset="./images/slides/promo-b2x.png 2x" alt="">
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
                                                <img class="round" src="./images/slides/promo-c.png" srcset="./images/slides/promo-c2x.png 2x" alt="">
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
       $(document).ready(function() {
           $('.passcode-switch1').click(function() {
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




