<?php
// template.php
function render($templateName, $content = []) {
    // Path al file del template
    $templatePath = __DIR__ . '/templates/' . $templateName . '.php';
    //return $templatePath;
    // Verifica se il file del template esiste
    if (file_exists($templatePath)) {
        // Estrai i dati dai metatag e dal content in variabili separate
        extract($content);
        // Avvia il buffering dell'output
        ob_start();
        // Includi il file del template
        include $templatePath;
        $pageContent = ob_get_clean();
        // Verifica se il file layout.php esiste
        $layoutPath = __DIR__ . '/templates/layout.php';
        //include il file di layout
        include $layoutPath;
        // Recupera il contenuto del buffer e lo assegna a $output
        $output = ob_get_clean();
        // Restituisci l'output del template
        return $output;
    } else {
        // Se il template non esiste, puoi gestire l'errore in qualche modo
        return 'Template not found';
    }
}
function renderMobi($templateName, $content = []) {
    // Path al file del template
    // Path al file del template
    $templatePath = __DIR__ . '/templates/' . $templateName . '.php';

    // Verifica se il file del template esiste
    if (!file_exists($templatePath)) {
        return 'Template not found';
    }

    // Estrai i dati dal contenuto passato alla funzione
    extract($content);

    // Avvia il buffering dell'output per il template
    ob_start();
    include $templatePath;
    $pageContent = ob_get_clean(); // Contenuto del template

    // Path al layout
    $layoutPath = __DIR__ . '/templates/app.php';

    // Verifica se il layout esiste
    if (!file_exists($layoutPath)) {
        return 'Layout not found';
    }

    // Avvia il buffering dell'output per il layout
    ob_start();
    include $layoutPath;
    $output = ob_get_clean(); // Contenuto del layout con $pageContent incluso
    echo $output;

    // Restituisci l'output finale
    return $output;
}

?>