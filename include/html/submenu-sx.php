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
            <?php foreach ($submenu as $key1 => $value): ?>
                <li class="nk-menu-item active current-page">
                    <a href="<?php echo $value[0]; ?>" class="nk-menu-link">
                        <span class="nk-menu-icon"><em class="icon ni ni-<?php echo $value[1]; ?>"></em></span>
                        <span class="nk-menu-text"><?php echo $key1; ?> </span>
                    </a>
                </li><!-- .nk-menu-item -->
            <?php endforeach; ?>
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
            <?php foreach ($menu as $key1 => $value): ?>
                <li class="nk-menu-item active current-page">
                    <a href="<?php echo $value[0]; ?>" class="nk-menu-link">
                        <span class="nk-menu-icon"><em class="icon ni ni-<?php echo $value[1]; ?>"></em></span>
                        <span class="nk-menu-text"><?php echo $key1; ?> </span>
                    </a>
                </li><!-- .nk-menu-item -->
            <?php endforeach; ?>
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

        // Get the first value (which should be an array in both valid cases)
        reset($menuSection); // Ensure pointer is at the start
        $firstValue = current($menuSection);

        if (!is_array($firstValue) || empty($firstValue)) {
            return 'unknown/empty'; // First value isn't an array or is empty
        }

        // Get the first element *within* the $firstValue array
        reset($firstValue); // Ensure pointer is at the start of the inner array
        $innerElement = current($firstValue);
        $innerKey = key($firstValue); // Get the key of the inner element

        // Check the type of the inner element and its key
        if (is_array($innerElement) && is_string($innerKey)) {
            // Example: $firstValue = ['Gestione corrispettivi' => [url, icon, color], ...]
            // $innerElement is the array [url, icon, color]
            // $innerKey is the string 'Gestione corrispettivi'
            return 'multi-level'; // Like 'corrispettivi'
        } elseif (is_string($innerElement) && is_int($innerKey) && $innerKey === 0) {
            // Example: $firstValue = ['/users', 'users', 'azure']
            // $innerElement is the string '/users'
            // $innerKey is the integer 0
            return 'single-level'; // Like 'users'
        }

        return 'unknown/empty'; // Structure doesn't match expected patterns
    }
?>
