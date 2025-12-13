<?php
require_once __DIR__ . '/config.php';

class Post {
    private ?int $id;
    private string $author;
    private string $content;
    private string $created_at;

    public function __construct($id, $author, $content, $created_at = null) {
        $this->id = $id;
        $this->author = $author;
        $this->content = $content;
        $this->created_at = $created_at ?? date('Y-m-d H:i:s');
    }
    public function getId() { return $this->id; }
    public function getAuthor() { return $this->author; }
    public function getContent() { return $this->content; }
    public function getCreatedAt() { return $this->created_at; }
    public function setAuthor($a) { $this->author = $a; }
    public function setContent($c) { $this->content = $c; }
}

class PostCRUD {
    public static function createTable() {
        $db = config::getConnexion();
        $db->exec("CREATE TABLE IF NOT EXISTS post (
            ID INT AUTO_INCREMENT PRIMARY KEY,
            send_by VARCHAR(100) DEFAULT 'Anonyme',
            time DATETIME NOT NULL,
            contenu TEXT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        // ensure contenu exists for pre-existing tables
        try {
            $col = $db->query("SHOW COLUMNS FROM post LIKE 'contenu'")->fetch();
            if (!$col) {
                $db->exec("ALTER TABLE post ADD COLUMN contenu TEXT NULL AFTER time");
            }
            // ensure send_by exists; if older column 'author' exists, copy it
            $colSend = $db->query("SHOW COLUMNS FROM post LIKE 'send_by'")->fetch();
            if (!$colSend) {
                $db->exec("ALTER TABLE post ADD COLUMN send_by VARCHAR(100) DEFAULT 'Anonyme' AFTER ID");
                // if 'author' column exists, copy values and remove it
                $colAuthor = $db->query("SHOW COLUMNS FROM post LIKE 'author'")->fetch();
                if ($colAuthor) {
                    try { $db->exec("UPDATE post SET send_by = author WHERE send_by IS NULL OR send_by = ''"); } catch (Exception $_) {}
                    try { $db->exec("ALTER TABLE post DROP COLUMN author"); } catch (Exception $_) {}
                }
            }
                // Ensure 'time' column exists; if older 'created_at' exists, keep it and copy values
                $colTime = $db->query("SHOW COLUMNS FROM post LIKE 'time'")->fetch();
                if (!$colTime) {
                    $colCreatedAt = $db->query("SHOW COLUMNS FROM post LIKE 'created_at'")->fetch();
                    if ($colCreatedAt) {
                        try { $db->exec("ALTER TABLE post ADD COLUMN time DATETIME NULL AFTER send_by"); } catch (Exception $_) {}
                        try { $db->exec("UPDATE post SET time = created_at WHERE time IS NULL OR time = ''"); } catch (Exception $_) {}
                        try { $db->exec("ALTER TABLE post DROP COLUMN created_at"); } catch (Exception $_) {}
                    } else {
                        try { $db->exec("ALTER TABLE post ADD COLUMN time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER send_by"); } catch (Exception $_) {}
                    }
                }
        } catch (Exception $_) {
            // ignore migration issues
        }
    }
    public static function addPost($send_by, $contenu) {
        $db = config::getConnexion();
        $sql = "INSERT INTO post (send_by, time, contenu) VALUES (:send_by, :time, :contenu)";
        $stmt = $db->prepare($sql);
        $now = date('Y-m-d H:i:s');
        $stmt->execute([
            ':send_by' => $send_by,
            ':time' => $now,
            ':contenu' => $contenu
        ]);
        return $db->lastInsertId();
    }
}

// Ensure table exists when model is included
try { PostCRUD::createTable(); } catch (Exception $e) { /* ignore */ }
