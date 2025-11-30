<?php
/**
 * Base Controller Class
 * All controllers extend this class
 */
class Controller {
    protected $params = [];
    protected $viewPath = '';

    public function __construct($params = []) {
        $this->params = $params;
    }

    /**
     * Render a view
     */
    protected function render($view, $data = []) {
        extract($data);
        
        $viewFile = 'app/views/' . $view . '.php';
        
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            throw new Exception("View file not found: $view");
        }
    }

    /**
     * Render JSON response
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Redirect to URL
     */
    protected function redirect($url) {
        // If URL doesn't start with http:// or https://, treat as internal route
        if (!preg_match('/^https?:\/\//', $url)) {
            $url = $this->url($url);
        }
        header("Location: $url");
        exit;
    }

    /**
     * Generate URL for a route
     */
    protected function url($route = '') {
        $baseUrl = rtrim(APP_URL, '/');
        $route = ltrim($route, '/');
        return $baseUrl . ($route ? '/' . $route : '');
    }

    /**
     * Generate URL for public assets
     */
    protected function asset($path) {
        $baseUrl = rtrim(APP_URL, '/');
        $path = ltrim($path, '/');
        return $baseUrl . '/public/' . $path;
    }

    /**
     * Check if user is authenticated
     */
    protected function requireAuth() {
        if (!isset($_SESSION['user'])) {
            $this->redirect('login');
        }
    }

    /**
     * Get current user
     */
    protected function getCurrentUser() {
        return $_SESSION['user'] ?? null;
    }

    /**
     * Check if request is AJAX
     */
    protected function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
}

