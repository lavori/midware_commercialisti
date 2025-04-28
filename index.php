<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
    SESSION_START();
    if(isset($_GET['logout']) && $_GET['logout']=="1"){
        $_SESSION=array();
        //$_SESSION['autorizzato']="ko";
        session_destroy();
    }
    if(!isset($_SESSION) || $_SESSION['autorizzato']!="ok"):
        header('Location:login.php'); exit();
    endif;

    require_once ('./include/routing.php'); // Includi il file con la definizione della classe Router
    $router = new Router(); // Crea un'istanza del router
    // Includi le route da "routes.php" passando il router come argomento
    $routes = require_once ('./include/routes.php');
    $routes($router);
    // Ottieni il percorso richiesto dall'URL (ad esempio, "/users/1")
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    // Esegui la route e cattura il risultato
    $result = $router->handleRequest($path);
?>


