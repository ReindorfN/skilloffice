<?php
/**
 * Router Class
 * Handles routing and dispatches requests to appropriate controllers
 */
class Router {
    private $routes = [];
    private $params = [];

    /**
     * Add a route
     */
    public function add($route, $params = []) {
        // Convert route to regex pattern
        $route = preg_replace('/\//', '\\/', $route);
        // Allow any characters except slashes for parameters (to support Firebase IDs with dots, colons, etc.)
        $route = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[^\/]+)', $route);
        $route = preg_replace('/\{([a-z]+):([^\}]+)\}/', '(?P<\1>\2)', $route);
        $route = '/^' . $route . '$/i';
        
        $this->routes[$route] = $params;
    }

    /**
     * Get routes
     */
    public function getRoutes() {
        return $this->routes;
    }

    /**
     * Match route against URL
     */
    public function match($url) {
        foreach ($this->routes as $route => $params) {
            if (preg_match($route, $url, $matches)) {
                foreach ($matches as $key => $match) {
                    if (is_string($key)) {
                        $params[$key] = $match;
                    }
                }
                $this->params = $params;
                return true;
            }
        }
        return false;
    }

    /**
     * Dispatch request
     */
    public function dispatch() {
        $url = $this->getUrl();
        
        if ($this->match($url)) {
            $controller = $this->params['controller'];
            $controller = $this->convertToStudlyCaps($controller);
            $controller = $controller . 'Controller';
            
            if (class_exists($controller)) {
                $controllerObject = new $controller($this->params);
                
                $action = $this->params['action'] ?? 'index';
                $action = $this->convertToCamelCase($action);
                
                if (is_callable([$controllerObject, $action])) {
                    $controllerObject->$action();
                } else {
                    throw new Exception("Method $action not found in controller $controller");
                }
            } else {
                throw new Exception("Controller class $controller not found");
            }
        } else {
            throw new Exception('No route matched.', 404);
        }
    }

    /**
     * Get URL from query string
     */
    private function getUrl() {
        $url = $_GET['route'] ?? '';
        $url = rtrim($url, '/');
        $url = filter_var($url, FILTER_SANITIZE_URL);
        // Return empty string if no route (will match the empty route pattern)
        return $url;
    }

    /**
     * Convert string to StudlyCaps
     */
    private function convertToStudlyCaps($string) {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
    }

    /**
     * Convert string to camelCase
     */
    private function convertToCamelCase($string) {
        return lcfirst($this->convertToStudlyCaps($string));
    }
}

