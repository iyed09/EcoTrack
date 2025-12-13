<div class="dashboard-header rewards-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2><i class="bi-star-fill me-2"></i>Your Eco-Points Dashboard</h2>
                <p>Track your points, climb the leaderboard, and earn badges!</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="points-display-large">
                    <span class="points-number"><?php echo number_format($totalPoints); ?></span>
                    <span class="points-label">Eco-Points</span>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card points-stat">
                    <i class="bi-trophy"></i>
                    <h3>#<?php echo $userRank; ?></h3>
                    <p>Your Rank</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card points-stat">
                    <i class="bi-star-fill"></i>
                    <h3><?php echo number_format($totalPoints); ?></h3>
                    <p>Total Points</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card points-stat">
                    <i class="bi-calendar-week"></i>
                    <h3>+<?php echo number_format($pointsThisWeek); ?></h3>
                    <p>This Week</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card points-stat">
                    <i class="bi-award"></i>
                    <h3><?php echo count($userAchievements); ?></h3>
                    <p>Badges Earned</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="module-card h-100">
                    <div class="module-card-header" style="background: linear-gradient(135deg, #ff9800, #ffc107);">
                        <h5 class="mb-0"><i class="bi-info-circle me-2"></i>How to Earn Points</h5>
                    </div>
                    <div class="module-card-body">
                        <ul class="points-guide">
                            <li><i class="bi-flag text-danger"></i> <strong>+25 pts</strong> Report trash</li>
                            <li><i class="bi-recycle text-success"></i> <strong>+15 pts</strong> Recycle waste</li>
                            <li><i class="bi-trash text-purple"></i> <strong>+10 pts</strong> Log waste disposal</li>
                            <li><i class="bi-bicycle text-info"></i> <strong>+15 pts</strong> Eco-friendly transport</li>
                            <li><i class="bi-car-front text-secondary"></i> <strong>+5 pts</strong> Log transport</li>
                            <li><i class="bi-sun text-warning"></i> <strong>+20 pts</strong> Use renewable energy</li>
                            <li><i class="bi-lightning-charge text-warning"></i> <strong>+5 pts</strong> Log energy usage</li>
                        </ul>
                        <div class="mt-3">
                            <a href="<?php echo URL_ROOT; ?>/rewards/achievements" class="custom-btn w-100">View All Badges</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 mb-4">
                <div class="module-card h-100">
                    <div class="module-card-header" style="background: linear-gradient(135deg, #4caf50, #81c784);">
                        <h5 class="mb-0"><i class="bi-clock-history me-2"></i>Recent Activity</h5>
                    </div>
                    <div class="module-card-body">
                        <?php if (count($pointsHistory) > 0): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Activity</th>
                                    <th>Points</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pointsHistory as $entry): ?>
                                <tr>
                                    <td>
                                        <span class="action-icon action-<?php echo $entry['action_type']; ?>">
                                            <?php echo getActionIcon($entry['action_type']); ?>
                                        </span>
                                        <?php echo htmlspecialchars($entry['action_description']); ?>
                                    </td>
                                    <td><span class="points-earned">+<?php echo $entry['points']; ?></span></td>
                                    <td><?php echo date('M d, Y', strtotime($entry['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi-inbox display-4 text-muted"></i>
                            <p class="text-muted mt-2">No points earned yet. Start tracking your eco-activities!</p>
                            <a href="<?php echo URL_ROOT; ?>/reports/add" class="custom-btn">Report Trash</a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if (count($userAchievements) > 0): ?>
        <div class="row">
            <div class="col-12">
                <div class="module-card">
                    <div class="module-card-header" style="background: linear-gradient(135deg, #9c27b0, #e91e63);">
                        <h5 class="mb-0"><i class="bi-award me-2"></i>Your Badges</h5>
                    </div>
                    <div class="module-card-body">
                        <div class="badges-grid">
                            <?php foreach ($userAchievements as $achievement): ?>
                            <div class="badge-item earned">
                                <div class="badge-icon bg-<?php echo $achievement['badge_color']; ?>">
                                    <i class="<?php echo $achievement['badge_icon']; ?>"></i>
                                </div>
                                <div class="badge-info">
                                    <h6><?php echo htmlspecialchars($achievement['name']); ?></h6>
                                    <small><?php echo htmlspecialchars($achievement['description']); ?></small>
                                    <div class="badge-date">Earned <?php echo date('M d, Y', strtotime($achievement['earned_at'])); ?></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="text-center mt-3">
                            <a href="<?php echo URL_ROOT; ?>/rewards/achievements" class="btn btn-outline-primary">View All Achievements</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="row mt-4">
            <div class="col-12 text-center">
                <a href="<?php echo URL_ROOT; ?>/rewards/leaderboard" class="custom-btn custom-btn-lg">
                    <i class="bi-bar-chart-fill me-2"></i>View Full Leaderboard
                </a>
            </div>
        </div>
    </div>
</section>

<?php
function getActionIcon($actionType) {
    $icons = [
        'trash_report' => '<i class="bi-flag"></i>',
        'waste_entry' => '<i class="bi-trash"></i>',
        'recycle' => '<i class="bi-recycle"></i>',
        'energy_entry' => '<i class="bi-lightning-charge"></i>',
        'transport_entry' => '<i class="bi-car-front"></i>',
        'eco_transport' => '<i class="bi-bicycle"></i>',
        'register' => '<i class="bi-person-check"></i>'
    ];
    return $icons[$actionType] ?? '<i class="bi-star"></i>';
}
?>
