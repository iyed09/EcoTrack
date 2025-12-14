<?php
class TrashReport extends Model {
    protected $table = 'trash_reports';

    public function getByUser($userId) {
        $sql = "SELECT * FROM trash_reports WHERE reporter_id = ? ORDER BY created_at DESC";
        return $this->fetchAll($sql, [$userId]);
    }

    public function countByUser($userId) {
        $sql = "SELECT COUNT(*) FROM trash_reports WHERE reporter_id = ?";
        return $this->fetchColumn($sql, [$userId]);
    }

    public function create($reporterId, $locationDescription, $latitude, $longitude, $description, $photoPath = null) {
        $sql = "INSERT INTO trash_reports (reporter_id, location_description, latitude, longitude, description, photo_path) VALUES (?, ?, ?, ?, ?, ?)";
        return $this->insert($sql, [$reporterId, $locationDescription, $latitude, $longitude, $description, $photoPath]);
    }

    public function getAllWithReporter() {
        $sql = "SELECT tr.*, u.name as reporter_name 
                FROM trash_reports tr 
                LEFT JOIN users u ON tr.reporter_id = u.id 
                ORDER BY tr.created_at DESC";
        return $this->fetchAll($sql);
    }

    public function getRecentWithReporter($limit = 5) {
        $sql = "SELECT tr.*, u.name as reporter_name 
                FROM trash_reports tr 
                LEFT JOIN users u ON tr.reporter_id = u.id 
                ORDER BY tr.created_at DESC 
                LIMIT ?";
        return $this->fetchAll($sql, [$limit]);
    }

    public function getPending() {
        $sql = "SELECT tr.*, u.name as reporter_name 
                FROM trash_reports tr 
                LEFT JOIN users u ON tr.reporter_id = u.id 
                WHERE tr.status = 'pending' 
                ORDER BY tr.created_at DESC";
        return $this->fetchAll($sql);
    }

    public function countPending() {
        return $this->count("status = 'pending'");
    }

    public function updateStatus($id, $status, $adminResponse = null) {
        $sql = "UPDATE trash_reports SET status = ?, admin_response = ?, response_at = CURRENT_TIMESTAMP WHERE id = ?";
        return $this->update($sql, [$status, $adminResponse, $id]);
    }

    public function getForRadar() {
        $sql = "SELECT id, latitude, longitude, location_description, description, status, created_at, photo_path 
                FROM trash_reports 
                WHERE latitude IS NOT NULL AND longitude IS NOT NULL 
                ORDER BY created_at DESC";
        return $this->fetchAll($sql);
    }
}
