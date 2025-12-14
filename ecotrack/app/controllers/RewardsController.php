<?php
class RewardsController extends Controller {
    public function index() {
        $this->requireLogin();
        $userId = $_SESSION['user_id'];

        $userModel = new User();
        $pointsHistory = new PointsHistory();

        $user = $userModel->findById($userId);
        $history = $pointsHistory->getByUser($userId, 20);
        $rank = $userModel->getUserRank($userId);

        $data = [
            'pageTitle' => 'My Points',
            'user' => $user,
            'history' => $history,
            'rank' => $rank,
            'isLoggedIn' => true,
            'isAdmin' => $this->isAdmin()
        ];

        $this->render('rewards/index', $data);
    }

    public function leaderboard() {
        $this->requireLogin();
        $userId = $_SESSION['user_id'];

        $userModel = new User();
        $leaderboard = $userModel->getLeaderboard(50);
        $userRank = $userModel->getUserRank($userId);
        $user = $userModel->findById($userId);

        $data = [
            'pageTitle' => 'Leaderboard',
            'leaderboard' => $leaderboard,
            'userRank' => $userRank,
            'user' => $user,
            'isLoggedIn' => true,
            'isAdmin' => $this->isAdmin()
        ];

        $this->render('rewards/leaderboard', $data);
    }

    public function achievements() {
        $this->requireLogin();
        $userId = $_SESSION['user_id'];

        $achievementModel = new Achievement();
        $achievements = $achievementModel->getUserAchievements($userId);
        $earnedCount = $achievementModel->countEarnedByUser($userId);
        $totalCount = count($achievements);

        $data = [
            'pageTitle' => 'Achievements',
            'achievements' => $achievements,
            'earnedCount' => $earnedCount,
            'totalCount' => $totalCount,
            'isLoggedIn' => true,
            'isAdmin' => $this->isAdmin()
        ];

        $this->render('rewards/achievements', $data);
    }
}
