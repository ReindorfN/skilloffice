<?php
/**
 * Application Configuration
 */

// Firebase Configuration
define('FIREBASE_API_KEY', 'AIzaSyDSXQYzK4zO6URVvs3cnn_Vs_OIuDKewTU');
define('FIREBASE_PROJECT_ID', 'skill-offices');
define('FIREBASE_AUTH_DOMAIN', 'skill-offices.firebaseapp.com');
define('FIREBASE_STORAGE_BUCKET', 'skill-offices.firebasestorage.app');
define('FIREBASE_MESSAGING_SENDER_ID', '812751536814');
define('FIREBASE_APP_ID', '1:812751536814:web:9f3c4b056f3a1142dde33f');

// Application Settings
define('APP_NAME', 'SkillOffice');
define('APP_URL', 'http://169.239.251.102:442/~reindorf.narh/skilloffice/web-app');
define('BASE_URL', 'http://169.239.251.102:442/~reindorf.narh/skilloffice/web-app'); // Alias for APP_URL
define('TIMEZONE', 'Africa/Accra');

// Paystack Configuration
// TODO: Replace with your actual Paystack keys
define('PAYSTACK_PUBLIC_KEY', 'pk_test_54fbf060481d331dd17f20bf33187428b03d67aa');
define('PAYSTACK_SECRET_KEY', 'sk_test_da2809bf894241352ad7163982eaf4dbbd7d27bd');
define('PAYSTACK_CALLBACK_URL', APP_URL . '/api/payment/callback');

// Set timezone
date_default_timezone_set(TIMEZONE);

// Session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS

/**
 * Global helper functions for views
 */

/**
 * Generate URL for a route
 */
if (!function_exists('url')) {
    function url($route = '') {
        $baseUrl = rtrim(APP_URL, '/');
        $route = ltrim($route, '/');
        return $baseUrl . ($route ? '/' . $route : '');
    }
}

/**
 * Generate URL for public assets
 */
if (!function_exists('asset')) {
    function asset($path) {
        $baseUrl = rtrim(APP_URL, '/');
        $path = ltrim($path, '/');
        return $baseUrl . '/public/' . $path;
    }
}

