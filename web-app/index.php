<?php
/**
 * SkillOffice - Main Entry Point
 * Front Controller Pattern
 */

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Autoloader
require_once 'app/core/Autoloader.php';
Autoloader::register();

// Load configuration (must be before session_start for session ini settings)
require_once 'app/config/config.php';

// Start session (after session ini settings are configured)
session_start();

// Initialize Router
$router = new Router();

// Load routes
require_once 'app/config/routes.php';

// Dispatch request
$router->dispatch();

