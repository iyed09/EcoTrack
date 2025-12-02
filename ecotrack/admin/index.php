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
include '../includes/header.php';
?>

<div class="dashboard-header">
    <div class="container">
        <h2><i class="bi-speedometer2 me-2"></i>Admin Dashboard</h2>
        <p>Manage users, content, and view statistics</p>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <div class="row mb-4">
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
                    <h3><?php echo number_format($totalEnergy + $totalTransport, 0); ?></h3>
                    <p>Total kg CO2</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3 col-12 mb-4">
                <div class="sidebar">
                    <h5 class="mb-4">Admin Menu</h5>
                    <ul class="sidebar-menu">
                        <li><a href="<?php echo ROOT_PATH; ?>/admin/index.php" class="active"><i class="bi-speedometer2"></i> Dashboard</a></li>
                        <li><a href="<?php echo ROOT_PATH; ?>/admin/users.php"><i class="bi-people"></i> Manage Users</a></li>
                        <li><a href="<?php echo ROOT_PATH; ?>/admin/reports.php"><i class="bi-flag"></i> Manage Reports</a></li>
                        <li><a href="<?php echo ROOT_PATH; ?>/admin/statistics.php"><i class="bi-bar-chart"></i> Statistics</a></li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-9 col-12">
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="module-card">
                            <div class="module-card-header" style="background: linear-gradient(135deg, #4caf50, #81c784);">
                                <h5 class="mb-0"><i class="bi-people me-2"></i>Recent Users</h5>
                            </div>
                            <div class="module-card-body">
                                <?php if (count($recentUsers) > 0): ?>
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
                                                <span class="status-badge <?php echo $user['role'] === 'admin' ? 'status-resolved' : 'status-pending'; ?>">
                                                    <?php echo ucfirst($user['role']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo formatDate($user['created_at']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <a href="users.php" class="custom-btn mt-3 d-inline-block">View All Users</a>
                                <?php else: ?>
                                <p class="text-muted">No users yet.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <div class="module-card">
                            <div class="module-card-header report">
                                <h5 class="mb-0"><i class="bi-flag me-2"></i>Recent Reports</h5>
                            </div>
                            <div class="module-card-body">
                                <?php if (count($recentReports) > 0): ?>
                                <table class="data-table">
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
                                                <span class="status-badge status-<?php echo $report['status']; ?>">
                                                    <?php echo ucfirst($report['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo formatDate($report['created_at']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <a href="reports.php" class="custom-btn mt-3 d-inline-block">View All Reports</a>
                                <?php else: ?>
                                <p class="text-muted">No reports yet.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
