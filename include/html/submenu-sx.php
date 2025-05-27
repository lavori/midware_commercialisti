<?php 
    // Esempio con $_SERVER['REQUEST_URI']
    $requestUri = $_SERVER['REQUEST_URI'];

    // Rimuovi eventuali parametri dalla URL
    $requestUriParts = explode('?', $requestUri);
    $path = $requestUriParts[0];

    // Divide il percorso in parti
    $pathParts = explode('/', trim($path, '/'));
    $sub_c=$pathParts[0];
    $current_serp='/'.$pathParts[0];
    if(isset($pathParts[1]) && $pathParts[1]!='') $current_serp.='/'.$pathParts[1];
    
?> 
<div class="nk-sidebar" data-content="sidebarMenu">
    <div class="nk-sidebar-inner" data-simplebar>
    <?php 
        $struttura=getMenuStructureType($menu);
        if($struttura=='multi-level'):
            foreach ($menu as $key => $submenu): 
    ?>
        <ul class="nk-menu nk-menu-md" id="menu-user">
            <li class="nk-menu-heading">
                <h6 class="overline-title text-primary-alt"><?php echo $key;?> </h6>
            </li><!-- .nk-menu-heading -->
            <?php 
                foreach ($submenu as $key1 => $value): 
                    $style_add='';
                    if($current_serp==$value[0]) $style_add="active current-page";
            ?>
                <li class="nk-menu-item <?php echo $style_add; ?>">
                    <a href="<?php echo $value[0]; ?>" class="nk-menu-link">
                        <span class="nk-menu-icon"><em class="icon ni ni-<?php echo $value[1]; ?>"></em></span>
                        <span class="nk-menu-text"><?php echo $key1; ?> </span>
                    </a>
                </li><!-- .nk-menu-item -->
            <?php 
                endforeach; 
            ?>
        </ul><!-- .nk-menu -->  
        <hr>
    <?php 
            endforeach; 
        elseif($struttura=='single-level' || $struttura=='unknown/empty'):
    ?>
        <ul class="nk-menu nk-menu-md" id="menu-user">
            <li class="nk-menu-heading">
                <h6 class="overline-title text-primary-alt"><?php echo $title;?> </h6>
            </li><!-- .nk-menu-heading -->
            <?php 
                foreach ($menu as $key1 => $value): 
                    $style_add='';
                    if($current_serp==$value[0]) $style_add="active current-page";
            ?>
                <li class="nk-menu-item <?php echo $style_add; ?>">
                    <a href="<?php echo $value[0]; ?>" class="nk-menu-link">
                        <span class="nk-menu-icon"><em class="icon ni ni-<?php echo $value[1]; ?>"></em></span>
                        <span class="nk-menu-text"><?php echo $key1; ?> </span>
                    </a>
                </li><!-- .nk-menu-item -->
            <?php 
                endforeach; 
            ?>
        </ul><!-- .nk-menu -->  
        <hr>
    <?php
        endif;
    ?>         
    </div>
</div>

<?php 
    function getMenuStructureType(array $menuSection): string {
        if (empty($menuSection)) {
            return 'unknown/empty';
        }

        reset($menuSection); 
        $firstValue = current($menuSection);

        if (!is_array($firstValue) || empty($firstValue)) {
            return 'unknown/empty'; 
        }
        
        reset($firstValue); 
        $innerElement = current($firstValue);
        $innerKey = key($firstValue); 

        if (is_array($innerElement) && is_string($innerKey)) {
            
            return 'multi-level'; 
        } elseif (is_string($innerElement) && is_int($innerKey) && $innerKey === 0) {
            
            return 'single-level'; 
        }

        return 'unknown/empty'; 
    }
?>
