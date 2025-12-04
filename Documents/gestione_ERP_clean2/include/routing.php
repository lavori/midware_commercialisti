<?php
// routing.php
class Router {
    private $routes = [];
    public function addRoute($path, $handler) {
        $this->routes[$path] = $handler;
    }
    public function handleRequest($path) {
        /*$handler = $this->routes[$path] ?? null;
        if ($handler !== null) {
            return call_user_func($handler);
        } else {
            // Gestisci l'errore se la route non è definita
            return 'Route not found';
        }*/
        foreach ($this->routes as $routeDefinition => $handler) {
            // Converte la definizione del percorso (es. /elimina_audio/{id}) in una regex
            // Esempio: /elimina_audio/{id} diventa #^/elimina_audio/(?P<id>[^/]+)$#
            // Il pattern (?P<nome_parametro>[^/]+) cattura qualsiasi carattere eccetto '/'
            // e lo assegna a un gruppo nominato (es. 'id').
            $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_-]*)\}/', '(?P<$1>[^/]+)', $routeDefinition);
            $regex = '#^' . $pattern . '$#';

            if (preg_match($regex, $path, $matches)) {
                // Filtra $matches per mantenere solo le catture nominate (i parametri)
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                return call_user_func($handler, $params); // Passa i parametri estratti all'handler
            }
        }
        // Gestisci l'errore se la route non è definita
        return 'Route not found'; // O la tua gestione personalizzata del 404
    }
}
?>
