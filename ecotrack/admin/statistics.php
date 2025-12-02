<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$totalAdmins = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();

$totalEnergy = $pdo->query("SELECT COALESCE(SUM(ec.amount * es.emission_factor), 0) FROM energy_consumption ec JOIN energy_sources es ON ec.source_id = es.id")->fetchColumn();
$totalTransport = $pdo->query("SELECT COALESCE(SUM(te.distance_km * tt.emission_per_km), 0) FROM transport_entries te JOIN transport_types tt ON te.transport_id = tt.id")->fetchColumn();
$totalWaste = $pdo->query("SELECT COALESCE(SUM(weight_kg), 0) FROM waste_entries")->fetchColumn();

$totalReports = $pdo->query("SELECT COUNT(*) FROM trash_reports")->fetchColumn();
$pendingReports = $pdo->query("SELECT COUNT(*) FROM trash_reports WHERE status = 'pending'")->fetchColumn();
$resolvedReports = $pdo->query("SELECT COUNT(*) FROM trash_reports WHERE status = 'resolved'")->fetchColumn();
$rejectedReports = $pdo->query("SELECT COUNT(*) FROM trash_reports WHERE status = 'rejected'")->fetchColumn();

$topEnergySources = $pdo->query("SELECT es.name, SUM(ec.amount) as total FROM energy_consumption ec JOIN energy_sources es ON ec.source_id = es.id GROUP BY es.id ORDER BY total DESC LIMIT 5")->fetchAll();
$topTransportTypes = $pdo->query("SELECT tt.name, SUM(te.distance_km) as total FROM transport_entries te JOIN transport_types tt ON te.transport_id = tt.id GROUP BY tt.id ORDER BY total DESC LIMIT 5")->fetchAll();

$pageTitle = 'Statistics';
include '../includes/header.php';
?>

<div class="dashboard-header">
    <div class="container">
        <h2><i class="bi-bar-chart me-2"></i>Global Statistics</h2>
        <p>Overview of platform-wide environmental data</p>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-12 mb-4">
                <div class="sidebar">
                    <h5 class="mb-4">Admin Menu</h5>
                    <ul class="sidebar-menu">
                        <li><a href="index.php"><i class="bi-speedometer2"></i> Dashboard</a></li>
                        <li><a href="users.php"><i class="bi-people"></i> Manage Users</a></li>
                        <li><a href="reports.php"><i class="bi-flag"></i> Manage Reports</a></li>
                        <li><a href="statistics.php" class="active"><i class="bi-bar-chart"></i> Statistics</a></li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-9 col-12">
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="stats-card">
                            <i class="bi-people"></i>
                            <h3><?php echo $totalUsers; ?></h3>
                            <p>Users</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stats-card">
                            <i class="bi-person-badge"></i>
                            <h3><?php echo $totalAdmins; ?></h3>
                            <p>Admins</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stats-card">
                            <i class="bi-cloud"></i>
                            <h3><?php echo number_format($totalEnergy + $totalTransport, 0); ?></h3>
                            <p>Total kg CO2</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stats-card">
                            <i class="bi-trash"></i>
                            <h3><?php echo number_format($totalWaste, 1); ?> kg</h3>
                            <p>Total Waste</p>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <div class="module-card">
                            <div class="module-card-header report">
                                <h5 class="mb-0"><i class="bi-flag me-2"></i>Reports Statistics</h5>
                            </div>
                            <div class="module-card-body">
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <h3><?php echo $totalReports; ?></h3>
                                        <p class="text-muted">Total Reports</p>
                                    </div>
                                    <div class="col-md-3">
                                        <h3 class="text-warning"><?php echo $pendingReports; ?></h3>
                                        <p class="text-muted">Pending</p>
                                    </div>
                                    <div class="col-md-3">
                                        <h3 class="text-success"><?php echo $resolvedReports; ?></h3>
                                        <p class="text-muted">Resolved</p>
                                    </div>
                                    <div class="col-md-3">
                                        <h3 class="text-danger"><?php echo $rejectedReports; ?></h3>
                                        <p class="text-muted">Rejected</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="module-card">
                            <div class="module-card-header energy">
                                <h5 class="mb-0"><i class="bi-lightning-charge me-2"></i>Top Energy Sources</h5>
                            </div>
                            <div class="module-card-body">
                                <?php if (count($topEnergySources) > 0): ?>
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Source</th>
                                            <th>Total Usage</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($topEnergySources as $source): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($source['name']); ?></td>
                                            <td><?php echo number_format($source['total'], 1); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <?php else: ?>
                                <p class="text-muted">No data available.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <div class="module-card">
                            <div class="module-card-header transport">
                                <h5 class="mb-0"><i class="bi-car-front me-2"></i>Top Transport Types</h5>
                            </div>
                            <div class="module-card-body">
                                <?php if (count($topTransportTypes) > 0): ?>
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Total km</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($topTransportTypes as $type): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($type['name']); ?></td>
                                            <td><?php echo number_format($type['total'], 1); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <?php else: ?>
                                <p class="text-muted">No data available.</p>
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
