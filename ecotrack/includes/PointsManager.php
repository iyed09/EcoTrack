<?php
class PointsManager {
    private $pdo;
    
    const POINTS_TRASH_REPORT = 25;
    const POINTS_WASTE_ENTRY = 10;
    const POINTS_WASTE_RECYCLE = 15;
    const POINTS_ENERGY_ENTRY = 5;
    const POINTS_ENERGY_RENEWABLE = 20;
    const POINTS_TRANSPORT_ENTRY = 5;
    const POINTS_TRANSPORT_ECO = 15;
    const POINTS_REGISTER = 50;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function awardPoints($userId, $points, $actionType, $description, $referenceId = null, $referenceType = null) {
        $stmt = $this->pdo->prepare("
            INSERT INTO points_history (user_id, points, action_type, action_description, reference_id, reference_type)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $points, $actionType, $description, $referenceId, $referenceType]);
        
        $stmt = $this->pdo->prepare("UPDATE users SET total_points = total_points + ? WHERE id = ?");
        $stmt->execute([$points, $userId]);
        
        $this->checkAndAwardAchievements($userId);
        
        return $points;
    }
    
    public function getUserPoints($userId) {
        $stmt = $this->pdo->prepare("SELECT total_points FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn() ?: 0;
    }
    
    public function getPointsHistory($userId, $limit = 10) {
        $limit = (int)$limit;
        $stmt = $this->pdo->prepare("
            SELECT * FROM points_history 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT $limit
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public function getLeaderboard($limit = 10) {
        $limit = (int)$limit;
        $stmt = $this->pdo->prepare("
            SELECT id, name, avatar, total_points,
                   (SELECT COUNT(*) FROM user_achievements WHERE user_id = users.id) as badge_count
            FROM users 
            WHERE role != 'admin'
            ORDER BY total_points DESC 
            LIMIT $limit
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getUserRank($userId) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) + 1 as rank 
            FROM users 
            WHERE total_points > (SELECT total_points FROM users WHERE id = ?)
            AND role != 'admin'
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }
    
    public function getActionCount($userId, $actionType) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM points_history 
            WHERE user_id = ? AND action_type = ?
        ");
        $stmt->execute([$userId, $actionType]);
        return $stmt->fetchColumn();
    }
    
    public function checkAndAwardAchievements($userId) {
        $userPoints = $this->getUserPoints($userId);
        $newAchievements = [];
        
        $achievements = $this->pdo->query("SELECT * FROM achievements WHERE is_active = TRUE")->fetchAll();
        
        foreach ($achievements as $achievement) {
            if ($this->hasAchievement($userId, $achievement['id'])) {
                continue;
            }
            
            $earned = false;
            
            if ($achievement['points_required'] > 0 && $userPoints >= $achievement['points_required']) {
                $earned = true;
            }
            
            if ($achievement['action_type'] && $achievement['action_count'] > 0) {
                $count = $this->getActionCount($userId, $achievement['action_type']);
                if ($count >= $achievement['action_count']) {
                    $earned = true;
                }
            }
            
            if ($earned) {
                $this->awardAchievement($userId, $achievement['id']);
                $newAchievements[] = $achievement;
            }
        }
        
        return $newAchievements;
    }
    
    public function hasAchievement($userId, $achievementId) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM user_achievements 
            WHERE user_id = ? AND achievement_id = ?
        ");
        $stmt->execute([$userId, $achievementId]);
        return $stmt->fetchColumn() > 0;
    }
    
    public function awardAchievement($userId, $achievementId) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT IGNORE INTO user_achievements (user_id, achievement_id) 
                VALUES (?, ?)
            ");
            $stmt->execute([$userId, $achievementId]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function getUserAchievements($userId) {
        $stmt = $this->pdo->prepare("
            SELECT a.*, ua.earned_at 
            FROM achievements a
            INNER JOIN user_achievements ua ON a.id = ua.achievement_id
            WHERE ua.user_id = ?
            ORDER BY ua.earned_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public function getAllAchievements() {
        return $this->pdo->query("SELECT * FROM achievements WHERE is_active = TRUE ORDER BY id")->fetchAll();
    }
    
    public function getAchievementProgress($userId, $achievement) {
        if ($achievement['points_required'] > 0) {
            $userPoints = $this->getUserPoints($userId);
            return min(100, ($userPoints / $achievement['points_required']) * 100);
        }
        
        if ($achievement['action_type'] && $achievement['action_count'] > 0) {
            $count = $this->getActionCount($userId, $achievement['action_type']);
            return min(100, ($count / $achievement['action_count']) * 100);
        }
        
        return 0;
    }
    
    public function getPointsThisWeek($userId) {
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(points), 0) FROM points_history 
            WHERE user_id = ? AND created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }
    
    public function getPointsThisMonth($userId) {
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(points), 0) FROM points_history 
            WHERE user_id = ? AND created_at >= DATE_FORMAT(CURRENT_DATE, '%Y-%m-01')
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }
    
    public static function getPointsForAction($actionType, $isEcoFriendly = false) {
        switch ($actionType) {
            case 'trash_report':
                return self::POINTS_TRASH_REPORT;
            case 'waste_entry':
                return $isEcoFriendly ? self::POINTS_WASTE_RECYCLE : self::POINTS_WASTE_ENTRY;
            case 'energy_entry':
                return $isEcoFriendly ? self::POINTS_ENERGY_RENEWABLE : self::POINTS_ENERGY_ENTRY;
            case 'transport_entry':
                return $isEcoFriendly ? self::POINTS_TRANSPORT_ECO : self::POINTS_TRANSPORT_ENTRY;
            case 'register':
                return self::POINTS_REGISTER;
            default:
                return 0;
        }
    }
}
?>
