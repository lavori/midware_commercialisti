<div class="nk-apps-sidebar is-dark">
   <div class="nk-apps-brand">
       <a href="/" class="logo-link">
           <img class="logo-light logo-img" src="./images/logo-small.png" srcset="./images/logo-small2x.png 2x" alt="logo">
           <img class="logo-dark logo-img" src="./images/logo-dark-small.png" srcset="./images/logo-dark-small2x.png 2x" alt="logo-dark">
       </a>
   </div>
   <div class="nk-sidebar-element">
       <div class="nk-sidebar-body">
               <div class="nk-sidebar-content" data-simplebar>
                   <div class="nk-sidebar-menu">
                       <!-- Menu -->
                       <ul class="nk-menu apps-menu">
                       <?php if(!ISSET($apps)) $apps=array(); ?>
                       <?php 
                        foreach ($apps as $key=>$value):  
                            if (($value[0] === "/" && $value[0] == $serp) || ($value[0] !== "/" && strpos($serp, $value[0]) === 0)) {
                                $style="active current-page"; 
                            }else{$style=""; }
                       ?> 
                           <li class="nk-menu-item <?php echo $style; ?>">
                               <a href="<?php echo $value[0]; ?>" class="nk-menu-link" title="<?php echo $key; ?>">
                                   <span class="nk-menu-icon"><em class="icon ni ni-<?php echo $value[1]; ?>"></em></span>
                               </a>
                           </li>
                       <?php endforeach; ?>
                       </ul>
                   </div> 
               </div>
               <div class="nk-sidebar-footer">
                   <ul class="nk-menu nk-menu-md">
                       <li class="nk-menu-item">
                           <a href="#" class="nk-menu-link" title="Settings">
                               <span class="nk-menu-icon"><em class="icon ni ni-setting"></em></span>
                           </a>
                       </li>
                   </ul>
               </div>
               <div class="nk-sidebar-profile nk-sidebar-profile-fixed dropdown">
                   <a href="#" class="toggle" data-target="profileDD">
                       <div class="user-avatar">
                           <span>
                               <?php 
                                   $iniziali=strtoupper(substr($_SESSION['utente']['nome'], 0, 1).substr($_SESSION['utente']['cognome'], 0, 1));
                                   echo $iniziali;
                               ?>
                           </span>
                       </div>
                   </a>
                   <div class="dropdown-menu dropdown-menu-md m-1 nk-sidebar-profile-dropdown" data-content="profileDD">
                       <div class="dropdown-inner user-card-wrap d-none d-md-block">
                           <div class="user-card">
                               <div class="user-avatar">
                                   <span><?php echo $iniziali; ?></span>
                               </div>
                               <div class="user-info">
                                   <span class="lead-text"><?php echo $_SESSION['utente']['nome']." ".$_SESSION['utente']['cognome']; ?></span>
                                   <span class="sub-text text-soft"><?php echo $_SESSION['utente']['email']; ?></span>
                               </div>
                               
                           </div>
                       </div>
                       <div class="dropdown-inner">
                           <ul class="link-list">
                               <li><a href="/dettagli/<?php echo $_SESSION['utente']['id']; ?>"><em class="icon ni ni-user-alt"></em><span>Profilo</span></a></li>
                               <li><a onclick=lastlogin(<?php echo $_SESSION['utente']['last_login']; ?>)><em class="icon ni ni-activity-alt"></em><span>Ultimo accesso</span></a></li>
                           </ul>
                       </div>
                       <div class="dropdown-inner">
                           <ul class="link-list">
                               <li><a href="./index1.php?logout=1"><em class="icon ni ni-signout"></em><span>Esci</span></a></li>
                           </ul>
                       </div>
                   </div>
               </div>
       </div>
   </div>
</div> 
<script>
<<<<<<< HEAD
    function lasstlogin(a){
=======
    function lastlogin(a){ //Corretto da lasstlogin a lastlogin
>>>>>>> f79c4ff8185eb035c1453524a5f456bfcb729d26
    function lasstlogin(a){
        alert('Ultimo accesso al CRM aziendale: '+a);
    }
</script>