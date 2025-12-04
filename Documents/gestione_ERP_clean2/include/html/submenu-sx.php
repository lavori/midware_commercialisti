<?php
// Esempio con $_SERVER['REQUEST_URI']
$requestUri = $_SERVER['REQUEST_URI'];

// Rimuovi eventuali parametri dalla URL
$requestUriParts = explode('?', $requestUri);
$path = $requestUriParts[0];

// Divide il percorso in parti
$pathParts = explode('/', trim($path, '/'));
$sub_c = $pathParts[0];
$current_serp = '/' . $pathParts[0];
if (isset($pathParts[1]) && $pathParts[1] != '')
    $current_serp .= '/' . $pathParts[1];

?>
<div class="nk-sidebar" data-content="sidebarMenu">
    <div class="nk-sidebar-inner" data-simplebar>
        <?php
        $struttura = getMenuStructureType($menu);
        if ($struttura == 'multi-level'):
            foreach ($menu as $key => $submenu):
                ?>
                <ul class="nk-menu nk-menu-md" id="menu-user">
                    <li class="nk-menu-heading">
                        <h6 class="overline-title text-primary-alt"><?php echo $key; ?> </h6>
                    </li><!-- .nk-menu-heading -->
                    <?php
                    foreach ($submenu as $key1 => $value):
                        $style_add = '';
                        if ($current_serp == $value[0])
                            $style_add = "active current-page";
                        ?>
                        <?php
                        $ruoli = explode(',', $value[3]);
                        $last_ruolo = $ruoli[array_key_last($ruoli)];
                        if (in_array($_SESSION['utente']['id_ruolo'], $ruoli)):
                            ?>
                            <li class="nk-menu-item <?php echo $style_add; ?>">
                                <a href="<?php echo $value[0]; ?>" class="nk-menu-link">
                                    <span class="nk-menu-icon"><em class="icon ni ni-<?php echo $value[1]; ?>"></em></span>
                                    <span class="nk-menu-text"><?php echo $key1; ?> </span>
                                </a>
                            </li><!-- .nk-menu-item -->
                            <?php
                        endif;
                        ?>

                        <?php
                    endforeach;
                    ?>
                </ul><!-- .nk-menu -->
                <hr>
                <?php
            endforeach;
        elseif ($struttura == 'single-level' || $struttura == 'unknown/empty'):
            ?>
            <ul class="nk-menu nk-menu-md" id="menu-user">
                <li class="nk-menu-heading">
                    <h6 class="overline-title text-primary-alt"><?php echo $title; ?> </h6>
                </li><!-- .nk-menu-heading -->
                <?php
                foreach ($menu as $key1 => $value):
                    $style_add = '';
                    if ($current_serp == $value[0])
                        $style_add = "active current-page";
                    if (substr($value[0], 0, 1) == '.' && $current_serp != $serp . substr($value[0], 1, strlen($value[0]))) {
                        $value[0] = $serp . substr($value[0], 1, strlen($value[0])); // Assicurati che il percorso inizi con '/'
                    }
                    ?>
                    <?php
                    if (isset($value[3]) && !empty($value[3])) {
                        $ruoli = explode(',', $value[3]);
                        if (in_array($_SESSION['utente']['id_ruolo'], $ruoli)):
                            ?>
                            <li class="nk-menu-item <?php echo $style_add; ?>">
                                <a href="<?php echo $value[0]; ?>" class="nk-menu-link">
                                    <span class="nk-menu-icon"><em class="icon ni ni-<?php echo $value[1]; ?>"></em></span>
                                    <span class="nk-menu-text"><?php echo $key1; ?> </span>
                                </a>
                            </li>
                            <?php
                        endif;
                    }
                    ?>
                    <?php
                endforeach;
                ?>
            </ul><!-- .nk-menu -->
            <hr>
            <?php
        endif;
        ?>

        <?php if (isset($menu_discenti) && $menu_discenti == 1): ?>
            <h5><?= $nome . ' '. $cognome ?></h5>
            <ul class="link-list-opt no-bdr">
                <li><a href="/discenti/<?= $id ?>"><em class="icon ni ni-eye"></em><span>Visualizza Discente</span></a></li>
                <li><a href="/discenti/gestione/<?= $id ?>/update"><em class="icon ni ni-edit-alt"></em><span>Modifica
                            Discente</span></a></li>
                <li><a href="/discenti/gestione/<?= $id ?>/modifica_percorso"><em
                            class="icon ni ni-edit-alt"></em><span>Modifica Esami</span></a></li>
                <li><a href="/discenti/gestione/<?= $id ?>/nuovo_prodotto"><em class="icon ni ni-plus"></em><span>Aggiungi
                            Prodotto</span></a></li>
                <li><a href="/discenti/gestione/<?= $id ?>/assegnazione_prodotti"><em class="icon ni ni-calendar"></em><span>Assegna Data
                            Esame</span></a></li>
                <li><a href="/discenti/gestione/<?= $id ?>/collegamento"><em class="icon ni ni-link"></em><span>Collegamento
                            E-Campus</span></a></li>
                <li><a href="/discenti/gestione/<?= $id ?>/accordo"><em class="icon ni ni-coin-alt"></em><span>Accordo
                            Economico</span></a></li>
                <ul class="link-list-opt no-bdr" style="margin-left: 12%;">
                    <li><a href="/discenti/gestione/<?= $id ?>/accordi_sottoscritti"><em
                                class="icon ni ni-coin-alt"></em><span>Accordi Sottoscritti</span></a></li>
                </ul>
                <li><a href="/discenti/gestione/<?= $id ?>/carriera"><em class="icon ni ni-book"></em><span>Visualizza
                            Carriera Esami</span></a></li>
            </ul>
        <?php endif; ?>
    </div>
</div>
<?php


function getMenuStructureType(array $menuSection): string
{
    if (empty($menuSection)) {
        return 'unknown/empty';
    }

    foreach ($menuSection as $key => $value) {
        if (is_array($value)) {
            // Controlla se il valore è un sotto-menu
            foreach ($value as $subKey => $subValue) {
                if (is_array($subValue) && isset($subValue[0]) && is_string($subValue[0])) {
                    // È un menu multilivello
                    return 'multi-level';
                }
            }
        }
    }

    // Se non ci sono sotto-menu, è single-level
    return 'single-level';
}
?>