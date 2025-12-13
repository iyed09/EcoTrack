<?php
session_start();

$database_url = getenv('DATABASE_URL');

if ($database_url && strpos($database_url, 'mysql') !== false) {
    $db_parts = parse_url($database_url);
    $host = $db_parts['host'];
    $port = $db_parts['port'] ?? 3306;
    $dbname = ltrim($db_parts['path'], '/');
    $user = $db_parts['user'];
    $password = $db_parts['pass'] ?? '';
} else {
    $host = getenv('DB_HOST') ?: 'localhost';
    $port = getenv('DB_PORT') ?: 3306;
    $dbname = getenv('DB_NAME') ?: 'ecotrack';
    $user = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: '';
}

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

define('SITE_NAME', 'EcoTrack');
define('SITE_SLOGAN', 'Save the planet, one click at a time.');
define('MISTRAL_API_KEY', 'lsm6xhmBJfc9JBHbxjT1IdjIZfk8QIjd');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

$request_uri = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : '';
if (stripos($request_uri, '/ecotrack') === 0) {
    define('ROOT_PATH', '/ecotrack');
} else {
    define('ROOT_PATH', '');
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function formatDate($date) {
    return date('M d, Y', strtotime($date));
}
?>
