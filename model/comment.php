<?php
require_once __DIR__ . '/config.php';

class Comment {
    private ?int $id;
    private string $send_by;
    private ?int $comment_id; // parent post id
    private string $contenu;
    private string $time;

    public function __construct($id = null, $send_by = '', $contenu = '', $time = '', $comment_id = null) {
        $this->id = $id;
        $this->send_by = $send_by;
        $this->contenu = $contenu;
        $this->time = $time;
        $this->comment_id = $comment_id;
    }
    public function getId() { return $this->id; }
    public function getSendBy() { return $this->send_by; }
    public function getContenu() { return $this->contenu; }
    public function getTime() { return $this->time; }
    public function getCommentId() { return $this->comment_id; }
    public function setSendBy($send_by) { $this->send_by = $send_by; }
    public function setContenu($contenu) { $this->contenu = $contenu; }
    public function setTime($time) { $this->time = $time; }
    public function setCommentId($comment_id) { $this->comment_id = $comment_id; }
}

class CommentCRUD {
    public static function createTable() {
        $db = config::getConnexion();
        $db->exec("CREATE TABLE IF NOT EXISTS comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            send_by VARCHAR(100) DEFAULT 'Anonyme',
            contenu TEXT NOT NULL,
            time DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        // Ensure send_by column exists for older databases
        // Ensure send_by, time and `comment id` columns exist for older databases
        try {
            $col = $db->query("SHOW COLUMNS FROM comments LIKE 'send_by'")->fetch();
            if (!$col) {
                $db->exec("ALTER TABLE comments ADD COLUMN send_by VARCHAR(100) DEFAULT 'Anonyme' AFTER id");
            }
            $col = $db->query("SHOW COLUMNS FROM comments LIKE 'time'")->fetch();
            if (!$col) {
                $db->exec("ALTER TABLE comments ADD COLUMN time DATETIME NULL AFTER contenu");
                // Set current time for existing rows
                $db->exec("UPDATE comments SET time = NOW() WHERE time IS NULL");
            }
            $col = $db->query("SHOW COLUMNS FROM comments LIKE 'comment id'")->fetch();
            if (!$col) {
                $db->exec("ALTER TABLE comments ADD COLUMN `comment id` INT NULL AFTER time");
            }
        } catch (Exception $_) {
            // ignore migration failures
        }
    }
    public static function addComment($comment) {
        $db = config::getConnexion();
        $sql = "INSERT INTO comments (send_by, contenu, time, `comment id`) VALUES (:send_by, :contenu, :time, :comment_id)";
        $query = $db->prepare($sql);
        try {
            $query->bindValue(':send_by', $comment->getSendBy());
            $query->bindValue(':contenu', $comment->getContenu());
            $query->bindValue(':time', $comment->getTime());
            $query->bindValue(':comment_id', $comment->getCommentId());
            $query->execute();
            return (int)$db->lastInsertId();
        } catch (Exception $e) {
            // rethrow so controller can handle/log
            throw $e;
        }
    }
    public static function getAllComments() {
        $db = config::getConnexion();
        // Use correct column names: id, contenu, `comment id`
        $sql = "SELECT id, send_by, contenu, time, `comment id` FROM comments ORDER BY id DESC";
        $query = $db->prepare($sql);
        $query->execute();
        return $query->fetchAll();
    }
    public static function updateComment($id, $contenu, $send_by = null) {
        $db = config::getConnexion();
        if ($send_by !== null) {
            $sql = "UPDATE comments SET contenu = :contenu, send_by = :send_by WHERE id = :id";
            $query = $db->prepare($sql);
            try {
                $query->execute([
                    ':id' => $id,
                    ':contenu' => $contenu,
                    ':send_by' => $send_by
                ]);
                return $query->rowCount() > 0;
            } catch (Exception $e) {
                throw $e;
            }
        }
        $sql = "UPDATE comments SET contenu = :contenu WHERE id = :id";
        $query = $db->prepare($sql);
        try {
            $query->execute([
                ':id' => $id,
                ':contenu' => $contenu
            ]);
            return $query->rowCount() > 0;
        } catch (Exception $e) {
            throw $e;
        }
    }
    public static function deleteComment($id) {
        $db = config::getConnexion();
        $sql = "DELETE FROM comments WHERE id = :id";
        $query = $db->prepare($sql);
        try {
            $query->execute([':id' => $id]);
            return $query->rowCount() > 0;
        } catch (Exception $e) {
            throw $e;
        }
    }
}

// Ensure table exists when model is included
try {
    CommentCRUD::createTable();
} catch (Exception $e) {
    // let controller handle errors; do not emit output here
}
