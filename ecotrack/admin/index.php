<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalReports = $pdo->query("SELECT COUNT(*) FROM trash_reports")->fetchColumn();
$pendingReports = $pdo->query("SELECT COUNT(*) FROM trash_reports WHERE status = 'pending'")->fetchColumn();

$totalEnergy = $pdo->query("SELECT COALESCE(SUM(ec.amount * es.emission_factor), 0) FROM energy_consumption ec JOIN energy_sources es ON ec.source_id = es.id")->fetchColumn();
$totalTransport = $pdo->query("SELECT COALESCE(SUM(te.distance_km * tt.emission_per_km), 0) FROM transport_entries te JOIN transport_types tt ON te.transport_id = tt.id")->fetchColumn();

$recentUsers = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recentReports = $pdo->query("SELECT tr.*, u.name as reporter_name FROM trash_reports tr LEFT JOIN users u ON tr.reporter_id = u.id ORDER BY tr.created_at DESC LIMIT 5")->fetchAll();

$pageTitle = 'Admin Dashboard';
include 'includes/admin_header.php';
?>

<!-- Welcome Card -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="welcome-card">
            <div class="row align-items-center">
                <div class="col-sm-7">
                    <h4>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! 🎉</h4>
                    <p>Monitor environmental data and manage your EcoTrack platform from this dashboard.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Row -->
<div class="row">
    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
        <div class="stats-card">
            <div class="stats-icon primary">
                <i class="bx bx-user"></i>
            </div>
            <div class="stats-content">
                <h3><?php echo $totalUsers; ?></h3>
                <p>Total Users</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
        <div class="stats-card">
            <div class="stats-icon success">
                <i class="bx bx-flag"></i>
            </div>
            <div class="stats-content">
                <h3><?php echo $totalReports; ?></h3>
                <p>Total Reports</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
        <div class="stats-card">
            <div class="stats-icon warning">
                <i class="bx bx-time-five"></i>
            </div>
            <div class="stats-content">
                <h3><?php echo $pendingReports; ?></h3>
                <p>Pending Reports</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
        <div class="stats-card">
            <div class="stats-icon info">
                <i class="bx bx-cloud"></i>
            </div>
            <div class="stats-content">
                <h3><?php echo number_format($totalEnergy + $totalTransport, 0); ?></h3>
                <p>Total kg CO2</p>
            </div>
        </div>
    </div>
</div>

<!-- Data Tables Row -->
<div class="row">
    <!-- Recent Users -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">
                    <i class="bx bx-user me-2 text-primary"></i>Recent Users
                </h5>
                <a href="users.php" class="btn btn-sm btn-label-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (count($recentUsers) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
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
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded-circle bg-label-primary">
                                                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                            </span>
                                        </div>
                                        <span><?php echo htmlspecialchars($user['name']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?php echo $user['role'] === 'admin' ? 'bg-label-success' : 'bg-label-secondary'; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo formatDate($user['created_at']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted text-center py-4">No users yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Reports -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">
                    <i class="bx bx-flag me-2 text-warning"></i>Recent Reports
                </h5>
                <a href="reports.php" class="btn btn-sm btn-label-warning">View All</a>
            </div>
            <div class="card-body">
                <?php if (count($recentReports) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Reporter</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentReports as $report): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($report['reporter_name'] ?? 'Unknown'); ?></td>
                                <td>
                                    <?php
                                    $statusClass = 'bg-label-secondary';
                                    if ($report['status'] === 'pending') $statusClass = 'bg-label-warning';
                                    if ($report['status'] === 'resolved') $statusClass = 'bg-label-success';
                                    if ($report['status'] === 'rejected') $statusClass = 'bg-label-danger';
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?>">
                                        <?php echo ucfirst($report['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo formatDate($report['created_at']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted text-center py-4">No reports yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bx bx-rocket me-2 text-info"></i>Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="users.php" class="btn btn-label-primary w-100 py-3">
                            <i class="bx bx-user-plus fs-4 d-block mb-1"></i>
                            Manage Users
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="reports.php" class="btn btn-label-warning w-100 py-3">
                            <i class="bx bx-flag fs-4 d-block mb-1"></i>
                            Review Reports
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="statistics.php" class="btn btn-label-success w-100 py-3">
                            <i class="bx bx-bar-chart-alt-2 fs-4 d-block mb-1"></i>
                            View Statistics
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="<?php echo ROOT_PATH; ?>/dashboard.php" class="btn btn-label-info w-100 py-3">
                            <i class="bx bx-tachometer fs-4 d-block mb-1"></i>
                            User Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/admin_footer.php'; ?>
