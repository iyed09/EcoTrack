<?php
class config {
    private static $pdo = null;
    public static function getConnexion() {
        if (!isset(self::$pdo)) {
            // Use 127.0.0.1 and explicit port to avoid named-pipe/localhost resolution issues on Windows
            $servername = "127.0.0.1";
            $port = 3306; // change to 3307 if XAMPP/MariaDB uses that
            $dbname = "ecotrack";
            $username = "root";
            $password = "";
            $charset = 'utf8mb4';
            $portsToTry = [$port, 3307];
            $lastEx = null;
            foreach ($portsToTry as $p) {
                $dsn = "mysql:host={$servername};port={$p};dbname={$dbname};charset={$charset}";
                try {
                    self::$pdo = new PDO($dsn,
                        $username,
                        $password,
                        [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                        ]
                    );
                    // if we reached here, connection succeeded — store the port used in a debug log
                    error_log(date('[Y-m-d H:i:s] ') . "DB Connexion OK: host={$servername};port={$p};dbname={$dbname}" . "\n", 3, __DIR__ . '/../log/error.log');
                    break;
                } catch (Exception $e) {
                    $lastEx = $e;
                    // try the next port
                    error_log(date('[Y-m-d H:i:s] ') . "DB Connexion failed on port {$p}: " . $e->getMessage() . "\n", 3, __DIR__ . '/../log/error.log');
                    self::$pdo = null;
                }
            }
            if (!isset(self::$pdo) || self::$pdo === null) {
                // log details to help debugging (do not expose to users in production)
                error_log(date('[Y-m-d H:i:s] ') . "DB Connexion Erreur: " . ($lastEx ? $lastEx->getMessage() : 'Unknown') . "\n", 3, __DIR__ . '/../log/error.log');
                throw new Exception("DB Connexion Erreur: " . ($lastEx ? $lastEx->getMessage() : 'Unknown'));
            }
        }
        return self::$pdo;
    }
}
