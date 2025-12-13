<?php
// Database Configuration for EcoTrack
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ecotrack');

// Create connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        // In production, log this instead of showing it
        die("Erreur de connexion à la base EcoTrack: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}
?>
