<?php
class PointsHistory extends Model {
    protected $table = 'points_history';

    public function getByUser($userId, $limit = 20) {
        $sql = "SELECT * FROM points_history WHERE user_id = ? ORDER BY created_at DESC LIMIT ?";
        return $this->fetchAll($sql, [$userId, $limit]);
    }

    public function addPoints($userId, $points, $actionType, $description, $referenceId = null, $referenceType = null) {
        $sql = "INSERT INTO points_history (user_id, points, action_type, action_description, reference_id, reference_type) VALUES (?, ?, ?, ?, ?, ?)";
        return $this->insert($sql, [$userId, $points, $actionType, $description, $referenceId, $referenceType]);
    }

    public function getTotalPoints($userId) {
        $sql = "SELECT COALESCE(SUM(points), 0) FROM points_history WHERE user_id = ?";
        return $this->fetchColumn($sql, [$userId]);
    }

    public function countByActionType($userId, $actionType) {
        $sql = "SELECT COUNT(*) FROM points_history WHERE user_id = ? AND action_type = ?";
        return $this->fetchColumn($sql, [$userId, $actionType]);
    }
}
