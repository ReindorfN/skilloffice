<?php
/**
 * Autoloader Class
 * Simple autoloader for MVC structure
 */
class Autoloader {
    public static function register() {
        spl_autoload_register([__CLASS__, 'load']);
    }

    public static function load($class) {
        // Try different paths
        $paths = [
            'app/models/' . $class . '.php',
            'app/controllers/' . $class . '.php',
            'app/services/' . $class . '.php',
            'app/core/' . $class . '.php',
        ];
        
        foreach ($paths as $path) {
            if (file_exists($path)) {
                require $path;
                return true;
            }
        }
        
        return false;
    }
}

