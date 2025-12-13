<?php
require_once '../../includes/config.php';
require_once '../../includes/PointsManager.php';

if (!isLoggedIn()) {
    redirect('../../modules/auth/login.php');
}

$userId = $_SESSION['user_id'];
$pointsManager = new PointsManager($pdo);

$allAchievements = $pointsManager->getAllAchievements();
$userAchievements = $pointsManager->getUserAchievements($userId);
$earnedIds = array_column($userAchievements, 'id');
$totalPoints = $pointsManager->getUserPoints($userId);

$pageTitle = 'Achievements';
include '../../includes/header.php';
?>

<div class="dashboard-header" style="background: linear-gradient(135deg, #9c27b0 0%, #e91e63 100%);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2><i class="bi-award me-2"></i>Achievements & Badges</h2>
                <p>Unlock badges by completing eco-friendly activities!</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="achievement-progress-summary">
                    <span class="earned-count"><?php echo count($userAchievements); ?></span>
                    <span class="total-count">/ <?php echo count($allAchievements); ?></span>
                    <span class="label">Badges Earned</span>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <div class="progress-bar-container">
                    <div class="progress-info">
                        <span>Overall Progress</span>
                        <span><?php echo round((count($userAchievements) / count($allAchievements)) * 100); ?>%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-success" style="width: <?php echo (count($userAchievements) / count($allAchievements)) * 100; ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="achievements-grid">
            <?php foreach ($allAchievements as $achievement): ?>
            <?php 
                $isEarned = in_array($achievement['id'], $earnedIds);
                $progress = $pointsManager->getAchievementProgress($userId, $achievement);
            ?>
            <div class="achievement-card <?php echo $isEarned ? 'earned' : 'locked'; ?>">
                <div class="achievement-icon bg-<?php echo $achievement['badge_color']; ?>">
                    <i class="<?php echo $achievement['badge_icon']; ?>"></i>
                    <?php if ($isEarned): ?>
                    <div class="earned-check"><i class="bi-check-circle-fill"></i></div>
                    <?php endif; ?>
                </div>
                <div class="achievement-content">
                    <h5><?php echo htmlspecialchars($achievement['name']); ?></h5>
                    <p><?php echo htmlspecialchars($achievement['description']); ?></p>
                    
                    <?php if (!$isEarned): ?>
                    <div class="achievement-progress">
                        <div class="progress">
                            <div class="progress-bar" style="width: <?php echo $progress; ?>%"></div>
                        </div>
                        <span class="progress-text"><?php echo round($progress); ?>%</span>
                    </div>
                    
                    <?php if ($achievement['points_required'] > 0): ?>
                    <small class="requirement">Requires <?php echo number_format($achievement['points_required']); ?> points</small>
                    <?php elseif ($achievement['action_count'] > 0): ?>
                    <small class="requirement"><?php echo $pointsManager->getActionCount($userId, $achievement['action_type']); ?>/<?php echo $achievement['action_count']; ?> completed</small>
                    <?php endif; ?>
                    <?php else: ?>
                    <div class="earned-badge">
                        <i class="bi-patch-check-fill"></i> Earned!
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="row mt-5">
            <div class="col-12 text-center">
                <a href="index.php" class="btn btn-outline-primary me-2">
                    <i class="bi-arrow-left me-1"></i>Back to Dashboard
                </a>
                <a href="leaderboard.php" class="custom-btn">
                    <i class="bi-bar-chart me-1"></i>View Leaderboard
                </a>
            </div>
        </div>
    </div>
</section>

<?php include '../../includes/footer.php'; ?>
