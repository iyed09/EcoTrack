<?php
class PointsManager {
    private $db;
    private $pointsHistory;
    private $userModel;
    private $achievementModel;

    private static $pointsConfig = [
        'register' => 50,
        'energy_entry' => 10,
        'energy_entry_renewable' => 20,
        'transport_entry' => 10,
        'transport_entry_eco' => 25,
        'waste_entry' => 10,
        'waste_entry_recycle' => 15,
        'trash_report' => 30
    ];

    public function __construct() {
        $this->db = Database::getInstance();
        $this->pointsHistory = new PointsHistory();
        $this->userModel = new User();
        $this->achievementModel = new Achievement();
    }

    public static function getPointsForAction($action, $bonus = false) {
        $key = $bonus ? $action . '_renewable' : $action;
        if ($bonus && $action === 'transport_entry') {
            $key = 'transport_entry_eco';
        }
        if ($bonus && $action === 'waste_entry') {
            $key = 'waste_entry_recycle';
        }
        return self::$pointsConfig[$key] ?? self::$pointsConfig[$action] ?? 0;
    }

    public function awardPoints($userId, $points, $actionType, $description, $referenceId = null, $referenceType = null) {
        $this->pointsHistory->addPoints($userId, $points, $actionType, $description, $referenceId, $referenceType);
        $this->userModel->updatePoints($userId, $points);
        $this->checkAchievements($userId);
        return $points;
    }

    public function checkAchievements($userId) {
        $user = $this->userModel->findById($userId);
        if (!$user) return;

        $achievements = $this->achievementModel->getActive();
        
        foreach ($achievements as $achievement) {
            if ($this->achievementModel->hasUserEarned($userId, $achievement['id'])) {
                continue;
            }

            $earned = false;

            if ($achievement['points_required'] > 0 && $user['total_points'] >= $achievement['points_required']) {
                $earned = true;
            }

            if ($achievement['action_type'] && $achievement['action_count'] > 0) {
                $count = $this->pointsHistory->countByActionType($userId, $achievement['action_type']);
                if ($count >= $achievement['action_count']) {
                    $earned = true;
                }
            }

            if ($earned) {
                $this->achievementModel->awardToUser($userId, $achievement['id']);
            }
        }
    }
}
