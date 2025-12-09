<?php
require_once __DIR__ . '/config.php';

class Report {
    private ?int $id;
    private string $content_type; // 'post' or 'comment'
    private int $content_id;
    private string $reported_by;
    private string $reason;
    private string $created_at;
    private string $status; // 'pending' or 'dismissed'

    public function __construct($id = null, $content_type = '', $content_id = 0, $reported_by = 'Anonyme', $reason = '', $created_at = '', $status = 'pending') {
        $this->id = $id;
        $this->content_type = $content_type;
        $this->content_id = $content_id;
        $this->reported_by = $reported_by;
        $this->reason = $reason;
        $this->created_at = $created_at;
        $this->status = $status;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getContentType() { return $this->content_type; }
    public function getContentId() { return $this->content_id; }
    public function getReportedBy() { return $this->reported_by; }
    public function getReason() { return $this->reason; }
    public function getCreatedAt() { return $this->created_at; }
    public function getStatus() { return $this->status; }

    // Setters
    public function setContentType($content_type) { $this->content_type = $content_type; }
    public function setContentId($content_id) { $this->content_id = $content_id; }
    public function setReportedBy($reported_by) { $this->reported_by = $reported_by; }
    public function setReason($reason) { $this->reason = $reason; }
    public function setStatus($status) { $this->status = $status; }
}

class ReportCRUD {
    /**
     * Create reports table if it doesn't exist
     */
    public static function createTable() {
        $db = config::getConnexion();
        $sql = "CREATE TABLE IF NOT EXISTS reports (
            id INT AUTO_INCREMENT PRIMARY KEY,
            content_type VARCHAR(20) NOT NULL,
            content_id INT NOT NULL,
            reported_by VARCHAR(100) DEFAULT 'Anonyme',
            reason VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status VARCHAR(20) DEFAULT 'pending',
            INDEX(content_type, content_id),
            INDEX(status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $db->exec($sql);
    }

    /**
     * Create a new report
     */
    public static function create($content_type, $content_id, $reported_by, $reason) {
        $db = config::getConnexion();
        $sql = "INSERT INTO reports (content_type, content_id, reported_by, reason) 
                VALUES (:content_type, :content_id, :reported_by, :reason)";
        $query = $db->prepare($sql);
        
        try {
            $query->execute([
                ':content_type' => $content_type,
                ':content_id' => $content_id,
                ':reported_by' => $reported_by,
                ':reason' => $reason
            ]);
            return (int)$db->lastInsertId();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get all pending reports with associated content details
     */
    public static function getAll() {
        $db = config::getConnexion();
        
        // Get reports joined with posts and comments
        $sql = "SELECT 
                    r.id,
                    r.content_type,
                    r.content_id,
                    r.reported_by,
                    r.reason,
                    r.created_at,
                    r.status,
                    CASE 
                        WHEN r.content_type = 'post' THEN p.contenu
                        WHEN r.content_type = 'comment' THEN cm.contenu
                    END as content,
                    CASE 
                        WHEN r.content_type = 'post' THEN p.send_by
                        WHEN r.content_type = 'comment' THEN cm.send_by
                    END as content_author,
                    CASE 
                        WHEN r.content_type = 'post' THEN p.time
                        WHEN r.content_type = 'comment' THEN cm.time
                    END as content_time
                FROM reports r
                LEFT JOIN post p ON r.content_type = 'post' AND r.content_id = p.ID
                LEFT JOIN comments cm ON r.content_type = 'comment' AND r.content_id = cm.id
                WHERE r.status = 'pending'
                ORDER BY r.created_at DESC";
        
        $query = $db->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Dismiss a report (mark as reviewed)
     */
    public static function dismiss($id) {
        $db = config::getConnexion();
        $sql = "UPDATE reports SET status = 'dismissed' WHERE id = :id";
        $query = $db->prepare($sql);
        
        try {
            $query->execute([':id' => $id]);
            return $query->rowCount() > 0;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete a report
     */
    public static function delete($id) {
        $db = config::getConnexion();
        $sql = "DELETE FROM reports WHERE id = :id";
        $query = $db->prepare($sql);
        
        try {
            $query->execute([':id' => $id]);
            return $query->rowCount() > 0;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Count reports for a specific content item
     */
    public static function countReportsByContent($content_type, $content_id) {
        $db = config::getConnexion();
        $sql = "SELECT COUNT(*) as count FROM reports 
                WHERE content_type = :content_type AND content_id = :content_id";
        $query = $db->prepare($sql);
        
        try {
            $query->execute([
                ':content_type' => $content_type,
                ':content_id' => $content_id
            ]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return (int)$result['count'];
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete all reports for a specific content item
     */
    public static function deleteByContent($content_type, $content_id) {
        $db = config::getConnexion();
        $sql = "DELETE FROM reports WHERE content_type = :content_type AND content_id = :content_id";
        $query = $db->prepare($sql);
        
        try {
            $query->execute([
                ':content_type' => $content_type,
                ':content_id' => $content_id
            ]);
            return $query->rowCount();
        } catch (Exception $e) {
            throw $e;
        }
    }
}

// Ensure table exists when model is included
try {
    ReportCRUD::createTable();
} catch (Exception $e) {
    // Let controller handle errors; do not emit output here
}
