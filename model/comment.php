<?php
require_once __DIR__ . '/config.php';

class Comment {
    private ?int $id;
    private string $send_by;
    private string $contenu;
    private string $time;

    public function __construct($id = null, $send_by = '', $contenu = '', $time = '') {
        $this->id = $id;
        $this->send_by = $send_by;
        $this->contenu = $contenu;
        $this->time = $time;
    }
    public function getId() { return $this->id; }
    public function getSendBy() { return $this->send_by; }
    public function getContenu() { return $this->contenu; }
    public function getTime() { return $this->time; }
    public function setSendBy($send_by) { $this->send_by = $send_by; }
    public function setContenu($contenu) { $this->contenu = $contenu; }
    public function setTime($time) { $this->time = $time; }
}

class CommentCRUD {
    public static function createTable() {
        $db = config::getConnexion();
        $db->exec("CREATE TABLE IF NOT EXISTS comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            send_by VARCHAR(100) NOT NULL,
            contenu TEXT NOT NULL,
            time DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
    public static function addComment($comment) {
        $db = config::getConnexion();
        $sql = "INSERT INTO comments (send_by, contenu, time) VALUES (:send_by, :contenu, :time)";
        $query = $db->prepare($sql);
        try {
            $query->bindValue(':send_by', $comment->getSendBy());
            $query->bindValue(':contenu', $comment->getContenu());
            $query->bindValue(':time', $comment->getTime());
            $query->execute();
            return (int)$db->lastInsertId();
        } catch (Exception $e) {
            // rethrow so controller can handle/log
            throw $e;
        }
    }
    public static function getAllComments() {
        $db = config::getConnexion();
        $sql = "SELECT * FROM comments ORDER BY time DESC";
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
        } else {
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
