<?php
class Achievement extends Model {
    protected $table = 'achievements';

    public function getActive() {
        return $this->fetchAll("SELECT * FROM achievements WHERE is_active = 1 ORDER BY points_required");
    }

    public function getUserAchievements($userId) {
        $sql = "SELECT a.*, ua.earned_at 
                FROM achievements a 
                LEFT JOIN user_achievements ua ON a.id = ua.achievement_id AND ua.user_id = ? 
                WHERE a.is_active = 1 
                ORDER BY a.points_required";
        return $this->fetchAll($sql, [$userId]);
    }

    public function getEarnedByUser($userId) {
        $sql = "SELECT a.*, ua.earned_at 
                FROM achievements a 
                JOIN user_achievements ua ON a.id = ua.achievement_id 
                WHERE ua.user_id = ? 
                ORDER BY ua.earned_at DESC";
        return $this->fetchAll($sql, [$userId]);
    }

    public function countEarnedByUser($userId) {
        $sql = "SELECT COUNT(*) FROM user_achievements WHERE user_id = ?";
        return $this->fetchColumn($sql, [$userId]);
    }

    public function awardToUser($userId, $achievementId) {
        $sql = "INSERT IGNORE INTO user_achievements (user_id, achievement_id) VALUES (?, ?)";
        return $this->insert($sql, [$userId, $achievementId]);
    }

    public function hasUserEarned($userId, $achievementId) {
        $sql = "SELECT 1 FROM user_achievements WHERE user_id = ? AND achievement_id = ?";
        return $this->fetch($sql, [$userId, $achievementId]) !== false;
    }
}
