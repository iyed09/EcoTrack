<div class="row">
    <div class="col-12 mb-4">
        <div class="welcome-card p-4 bg-primary text-white rounded">
            <h4>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h4>
            <p class="mb-0">Monitor environmental data and manage your EcoTrack platform from this dashboard.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stats-card">
            <i class="bi-people"></i>
            <h3><?php echo $totalUsers; ?></h3>
            <p>Total Users</p>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stats-card">
            <i class="bi-flag"></i>
            <h3><?php echo $totalReports; ?></h3>
            <p>Total Reports</p>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stats-card">
            <i class="bi-hourglass-split"></i>
            <h3><?php echo $pendingReports; ?></h3>
            <p>Pending Reports</p>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stats-card">
            <i class="bi-cloud"></i>
            <h3><?php echo number_format($totalEmissions, 0); ?></h3>
            <p>Total kg CO2</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="module-card h-100">
            <div class="module-card-header">
                <h5 class="mb-0"><i class="bi-people me-2"></i>Recent Users</h5>
            </div>
            <div class="module-card-body">
                <?php if (count($recentUsers) > 0): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentUsers as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td>
                                    <span class="badge <?php echo $user['role'] === 'admin' ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-end mt-3">
                    <a href="<?php echo URL_ROOT; ?>/admin/users" class="btn btn-outline-primary btn-sm">View All Users</a>
                </div>
                <?php else: ?>
                <p class="text-muted text-center">No users found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="module-card h-100">
            <div class="module-card-header">
                <h5 class="mb-0"><i class="bi-flag me-2"></i>Recent Reports</h5>
            </div>
            <div class="module-card-body">
                <?php if (count($recentReports) > 0): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Location</th>
                                <th>Reporter</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentReports as $report): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(substr($report['location_description'], 0, 30)); ?>...</td>
                                <td><?php echo htmlspecialchars($report['reporter_name'] ?? 'Unknown'); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $report['status']; ?>">
                                        <?php echo ucfirst($report['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-end mt-3">
                    <a href="<?php echo URL_ROOT; ?>/admin/reports" class="btn btn-outline-primary btn-sm">View All Reports</a>
                </div>
                <?php else: ?>
                <p class="text-muted text-center">No reports found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="d-flex gap-3 flex-wrap">
            <a href="<?php echo URL_ROOT; ?>/admin/users" class="custom-btn"><i class="bi-people me-2"></i>Manage Users</a>
            <a href="<?php echo URL_ROOT; ?>/admin/reports" class="custom-btn"><i class="bi-flag me-2"></i>Manage Reports</a>
            <a href="<?php echo URL_ROOT; ?>/admin/statistics" class="custom-btn"><i class="bi-graph-up me-2"></i>View Statistics</a>
        </div>
    </div>
</div>
