<?php
declare(strict_types=1);

/**
 * Environment Configuration Loader
 * Loads environment variables from .env file
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Helper function to get environment variables with default values
function env(string $key, $default = null) {
    return $_ENV[$key] ?? $default;
}

// Validate required environment variables
$required = [
    'DB_HOST',
    'DB_USERNAME', 
    'DB_PASSWORD',
    'AUTH_DB_NAME',
    'INVENTORY_DB_NAME',
    'REPORTS_DB_NAME',
    'JWT_SECRET'
];

foreach ($required as $var) {
    if (empty(env($var))) {
        throw new Exception("Required environment variable {$var} is not set");
    }
}

// Set timezone
date_default_timezone_set(env('DEFAULT_TIMEZONE', 'UTC'));

// Set error reporting based on environment
if (env('APP_DEBUG', false)) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
    ini_set('display_errors', '0');
}

// Set session configuration (only if session not started)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', '0'); // Set to 1 for HTTPS
    ini_set('session.use_strict_mode', '1');
    ini_set('session.gc_maxlifetime', env('SESSION_LIFETIME', '3600'));
}

