<div class="nk-header nk-header-fixed is-light">
    <div class="container-fluid">
        <div class="nk-header-wrap">
            <div class="nk-menu-trigger d-xl-none ms-n1">
                <a href="#" class="nk-nav-toggle nk-quick-nav-icon" data-target="sidebarMenu"><em class="icon ni ni-menu"></em></a>
            </div>
            <div class="nk-header-app-name">
                <div class="nk-header-app-logo">                    <em class="icon ni ni-dashlite bg-purple-dim"></em>
                </div>
                <div class="nk-header-app-info">
                    <span class="lead-text"><?php echo $titolo; ?></span>
                    <span class="sub-text"><?php echo $title; ?></span>
                </div>
            </div>
            <div class="nk-header-menu is-light">
                <div class="nk-header-menu-inner">
                    <!-- Menu -->
                </div>
            </div>
            <div class="nk-header-tools">
                <ul class="nk-quick-nav">
                    <!--navigazione veloce --> 
                    <li class="dropdown notification-dropdown">
                        <a href="#" class="dropdown-toggle nk-quick-nav-icon" data-bs-toggle="dropdown">
                            <div class="icon-status icon-status-info"><em class="icon ni ni-bell"></em></div>
                        </a>
                        <!-- menu a discesa notifiche-->
                        <div class="dropdown-menu dropdown-menu-xl dropdown-menu-end">
                            <div class="dropdown-head">
                                <span class="sub-title nk-dropdown-title">Notifiche </span>
                                <!--<a href="#">Mark All as Read</a>-->
                            </div>
                            <div class="dropdown-body">
                                <div class="nk-notification">
                                    <?php if(!ISSET($prob)) $prob=array(); ?>
                                    <?php foreach ($prob as $key=>$value):  ?>
                                    <div class="nk-notification-item dropdown-inner">
                                        <div class="nk-notification-icon">
                                            <em class="icon icon-circle bg-primary-dim ni ni-ticket-alt-fill"></em>
                                        </div>
                                        <div class="nk-notification-content">
                                            <div class="nk-notification-text"><?php echo $value['Oid']." - ".substr($value['problema'], 0, 50);?></div>
                                            <div class="nk-notification-time"><?php echo $value['data']; ?></div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div><!-- .nk-notification -->
                            </div><!-- .nk-dropdown-body -->
                            <div class="dropdown-foot center">
                                <a href="customercare">View All</a>
                            </div>
                        </div>
                        <!-- menu a discesa notifiche-->
                    </li>
                    <li class="dropdown list-apps-dropdown d-lg-none">
                        <a href="#" class="dropdown-toggle nk-quick-nav-icon" data-bs-toggle="dropdown">
                            <div class="icon-status icon-status-na"><em class="icon ni ni-menu-circled"></em></div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                            <div class="dropdown-body">
                                <ul class="list-apps">
                                    <?php if(!ISSET($apps)) $apps=array(); ?>
                                    <?php foreach ($apps as $key=>$value):  ?> 
                                    <li>
                                        <a href="<?php echo $value[0]; ?>">
                                            <span class="list-apps-media"><em class="icon ni ni-<?php echo $value[1]; ?> bg-<?php echo $value[2]; ?> text-white"></em></span>
                                            <span class="list-apps-title"><?php echo $key; ?></span>
                                        </a>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div><!-- .nk-dropdown-body -->
                        </div>
                    </li>
                    <li class="dropdown user-dropdown">
                        <a href="#" class="dropdown-toggle me-n1" data-bs-toggle="dropdown">
                            <div class="user-toggle">
                                <div class="user-avatar sm">
                                    <em class="icon ni ni-user-alt"></em>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-md dropdown-menu-end">
                            <div class="dropdown-inner user-card-wrap bg-lighter d-none d-md-block">
                                <div class="user-card">
                                    <div class="user-avatar">
                                        <span><?php echo strtoupper(substr($_SESSION['utente']['nome'],0,1).substr($_SESSION['utente']['cognome'],0,1)); ?></span>
                                    </div>
                                    <div class="user-info">
                                        <span class="lead-text"><?php echo $_SESSION['utente']['nome']." ".$_SESSION['utente']['cognome']; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="dropdown-inner">
                                <ul class="link-list">
                                    <li><a href="#"><em class="icon ni ni-user-alt"></em><span>View Profile</span></a></li>
                                    <li><a href="#"><em class="icon ni ni-setting-alt"></em><span>Account Setting</span></a></li>
                                    <li><a href="#"><em class="icon ni ni-activity-alt"></em><span>Login Activity</span></a></li>
                                    <li><a class="dark-switch" href="#"><em class="icon ni ni-moon"></em><span>Dark Mode</span></a></li>
                                </ul>
                            </div>
                            <div class="dropdown-inner">
                                <ul class="link-list">
                                    <li><a href="/?logout=1"><em class="icon ni ni-signout"></em><span>Esci</span></a></li>
                                </ul>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>