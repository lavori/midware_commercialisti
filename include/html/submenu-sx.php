<?php 
    // Esempio con $_SERVER['REQUEST_URI']
    $requestUri = $_SERVER['REQUEST_URI'];

    // Rimuovi eventuali parametri dalla URL
    $requestUriParts = explode('?', $requestUri);
    $path = $requestUriParts[0];

    // Divide il percorso in parti
    $pathParts = explode('/', trim($path, '/'));
    $sub_c=$pathParts[0];
    
?>
<?php //if($sub_c!=''){?>
<div class="nk-sidebar" data-content="sidebarMenu">
    <div class="nk-sidebar-inner" data-simplebar>
        <ul class="nk-menu nk-menu-md" id="menu-user">
            <li class="nk-menu-heading">
                <h6 class="overline-title text-primary-alt"><?php echo $title;?> </h6>
            </li><!-- .nk-menu-heading -->
        <?php foreach ($menu as $key => $value): ?>
            <li class="nk-menu-item active current-page">
                <a href="<?php echo $value[0]; ?>" class="nk-menu-link">
                    <span class="nk-menu-icon"><em class="icon ni ni-<?php echo $value[1]; ?>"></em></span>
                    <span class="nk-menu-text"><?php echo $key; ?> </span>
                </a>
            </li><!-- .nk-menu-item -->
        <?php endforeach; ?>
        </ul><!-- .nk-menu -->

        <?php /*if($_SESSION['autorizzato']=="ok"):?>
            <?php if($sub_c!='upload-video'):?>
            <div id="ath">
                <div class="nk-block-head">
                    <div class="nk-block-head-content">
                        <h5 class="nk-block-title">Carica un Video</h5>
                        <div class="nk-block-des">
                            <a href="/upload-video" class="btn btn-lg btn-primary btn-block">Carica un Video</a>
                        </div>
                    </div>
                </div><!-- .nk-block-head -->
            </div>
            <?php endif;?>
        <?php else: ?>
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
        <?php endif;*/ ?>
            
    </div>
</div>
<?php //} ?>