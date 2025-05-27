<?php

// routing.php

class Router {
    private $routes = [];

    public function addRoute($path, $handler) {
       // $this->routes[$path] = $handler;
       
       // Sostituisci le variabili dinamiche con espressioni regolari
       $path = preg_replace('/{(\w+)}/', '(?P<$1>[^/]+)', $path);

       // Aggiungi la route con la nuova espressione regolare
       $this->routes["^$path$"] = $handler;
    }

    public function handleRequest($path) {
        foreach ($this->routes as $route => $handler) {
            if (preg_match("#$route#", $path, $matches)) {
                // Chiamare la funzione di gestione con i parametri corrispondenti
                return call_user_func($handler, $matches);
            }
        }

        // Gestisci l'errore se la route non è definita
        return 'Route not found';
    }
}
/*
    public function handleRequest($path) {
        $handler = $this->routes[$path] ?? null;

        if ($handler !== null) {
            return call_user_func($handler);
        } else {
            // Gestisci l'errore se la route non è definita
            return 'Route not found';
        }
    }
}

*/



?>
