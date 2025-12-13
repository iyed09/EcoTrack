<div class="dashboard-header" style="background: linear-gradient(135deg, #ff9800 0%, #ffc107 100%);">
    <div class="container">
        <h2><i class="bi-bar-chart-fill me-2"></i>Community Leaderboard</h2>
        <p>See how you rank against other eco-warriors!</p>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <?php if (isset($isLoggedIn) && $isLoggedIn): ?>
        <div class="row mb-4">
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="your-rank-card">
                    <div class="rank-badge">#<?php echo $userRank; ?></div>
                    <div class="rank-info">
                        <h5>Your Position</h5>
                        <p><?php echo number_format($userPoints); ?> points</p>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-success">
                    <i class="bi-star me-2"></i><strong>Join the competition!</strong> <a href="<?php echo URL_ROOT; ?>/auth/register">Sign up now</a> to start earning eco-points and climb the leaderboard!
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (count($leaderboard) >= 3): ?>
        <div class="row mb-5">
            <div class="col-12">
                <div class="top-3-podium">
                    <div class="podium-item second">
                        <div class="podium-avatar">
                            <?php if ($leaderboard[1]['avatar']): ?>
                            <img src="<?php echo URL_ROOT; ?>/uploads/avatars/<?php echo $leaderboard[1]['avatar']; ?>" alt="">
                            <?php else: ?>
                            <i class="bi-person-circle"></i>
                            <?php endif; ?>
                        </div>
                        <div class="podium-rank">2</div>
                        <h6><?php echo htmlspecialchars($leaderboard[1]['name']); ?></h6>
                        <span class="podium-points"><?php echo number_format($leaderboard[1]['total_points']); ?> pts</span>
                        <div class="podium-base silver"></div>
                    </div>
                    <div class="podium-item first">
                        <div class="crown"><i class="bi-crown-fill"></i></div>
                        <div class="podium-avatar">
                            <?php if ($leaderboard[0]['avatar']): ?>
                            <img src="<?php echo URL_ROOT; ?>/uploads/avatars/<?php echo $leaderboard[0]['avatar']; ?>" alt="">
                            <?php else: ?>
                            <i class="bi-person-circle"></i>
                            <?php endif; ?>
                        </div>
                        <div class="podium-rank">1</div>
                        <h6><?php echo htmlspecialchars($leaderboard[0]['name']); ?></h6>
                        <span class="podium-points"><?php echo number_format($leaderboard[0]['total_points']); ?> pts</span>
                        <div class="podium-base gold"></div>
                    </div>
                    <div class="podium-item third">
                        <div class="podium-avatar">
                            <?php if ($leaderboard[2]['avatar']): ?>
                            <img src="<?php echo URL_ROOT; ?>/uploads/avatars/<?php echo $leaderboard[2]['avatar']; ?>" alt="">
                            <?php else: ?>
                            <i class="bi-person-circle"></i>
                            <?php endif; ?>
                        </div>
                        <div class="podium-rank">3</div>
                        <h6><?php echo htmlspecialchars($leaderboard[2]['name']); ?></h6>
                        <span class="podium-points"><?php echo number_format($leaderboard[2]['total_points']); ?> pts</span>
                        <div class="podium-base bronze"></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="module-card">
                    <div class="module-card-header" style="background: linear-gradient(135deg, #4caf50, #81c784);">
                        <h5 class="mb-0"><i class="bi-list-ol me-2"></i>Full Rankings</h5>
                    </div>
                    <div class="module-card-body">
                        <table class="data-table leaderboard-table">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>User</th>
                                    <th>Badges</th>
                                    <th>Points</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $rank = 1; foreach ($leaderboard as $user): ?>
                                <tr class="<?php echo isset($userId) && $user['id'] == $userId ? 'current-user' : ''; ?>">
                                    <td>
                                        <span class="rank-number rank-<?php echo $rank <= 3 ? $rank : 'other'; ?>">
                                            <?php if ($rank == 1): ?>
                                            <i class="bi-trophy-fill text-warning"></i>
                                            <?php elseif ($rank == 2): ?>
                                            <i class="bi-trophy-fill text-secondary"></i>
                                            <?php elseif ($rank == 3): ?>
                                            <i class="bi-trophy-fill text-bronze"></i>
                                            <?php else: ?>
                                            #<?php echo $rank; ?>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="user-cell">
                                            <div class="user-avatar-small">
                                                <?php if ($user['avatar']): ?>
                                                <img src="<?php echo URL_ROOT; ?>/uploads/avatars/<?php echo $user['avatar']; ?>" alt="">
                                                <?php else: ?>
                                                <i class="bi-person-circle"></i>
                                                <?php endif; ?>
                                            </div>
                                            <span><?php echo htmlspecialchars($user['name']); ?></span>
                                            <?php if (isset($userId) && $user['id'] == $userId): ?>
                                            <span class="badge bg-primary ms-2">You</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-count">
                                            <i class="bi-award text-warning"></i>
                                            <?php echo $user['badge_count']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="points-display"><?php echo number_format($user['total_points']); ?></span>
                                    </td>
                                </tr>
                                <?php $rank++; endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12 text-center">
                <a href="<?php echo URL_ROOT; ?>/rewards" class="btn btn-outline-primary me-2">
                    <i class="bi-arrow-left me-1"></i>Back to Dashboard
                </a>
                <a href="<?php echo URL_ROOT; ?>/rewards/achievements" class="custom-btn">
                    <i class="bi-award me-1"></i>View Achievements
                </a>
            </div>
        </div>
    </div>
</section>
