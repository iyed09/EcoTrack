<?php
session_start();

define('APP_ROOT', dirname(dirname(__FILE__)));
define('URL_ROOT', '');
define('SITE_NAME', 'EcoTrack');
define('SITE_SLOGAN', 'Save the planet, one click at a time.');
define('MISTRAL_API_KEY', 'lsm6xhmBJfc9JBHbxjT1IdjIZfk8QIjd');
define('UPLOAD_PATH', APP_ROOT . '/public_html/uploads/');

$database_url = getenv('DATABASE_URL');

if ($database_url && strpos($database_url, 'mysql') !== false) {
    $db_parts = parse_url($database_url);
    define('DB_HOST', $db_parts['host']);
    define('DB_PORT', $db_parts['port'] ?? 3306);
    define('DB_NAME', ltrim($db_parts['path'], '/'));
    define('DB_USER', $db_parts['user']);
    define('DB_PASS', $db_parts['pass'] ?? '');
} else {
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_PORT', getenv('DB_PORT') ?: 3306);
    define('DB_NAME', getenv('DB_NAME') ?: 'ecotrack');
    define('DB_USER', getenv('DB_USER') ?: 'root');
    define('DB_PASS', getenv('DB_PASSWORD') ?: '');
}
