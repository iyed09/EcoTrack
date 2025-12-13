<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    redirect('modules/auth/login.php');
}

$userId = $_SESSION['user_id'];

$energyTotal = $pdo->prepare("SELECT COALESCE(SUM(ec.amount * es.emission_factor), 0) as total FROM energy_consumption ec JOIN energy_sources es ON ec.source_id = es.id WHERE ec.user_id = ?");
$energyTotal->execute([$userId]);
$energyEmissions = $energyTotal->fetch()['total'];

$transportTotal = $pdo->prepare("SELECT COALESCE(SUM(te.distance_km * tt.emission_per_km), 0) as total FROM transport_entries te JOIN transport_types tt ON te.transport_id = tt.id WHERE te.user_id = ?");
$transportTotal->execute([$userId]);
$transportEmissions = $transportTotal->fetch()['total'];

$wasteTotal = $pdo->prepare("SELECT COALESCE(SUM(we.weight_kg * wt.impact_score), 0) as total FROM waste_entries we JOIN waste_types wt ON we.waste_type_id = wt.id WHERE we.user_id = ?");
$wasteTotal->execute([$userId]);
$wasteImpact = $wasteTotal->fetch()['total'];

$reportsCount = $pdo->prepare("SELECT COUNT(*) FROM trash_reports WHERE reporter_id = ?");
$reportsCount->execute([$userId]);
$totalReports = $reportsCount->fetchColumn();

$recentEnergy = $pdo->prepare("SELECT ec.*, es.name as source_name, es.unit FROM energy_consumption ec JOIN energy_sources es ON ec.source_id = es.id WHERE ec.user_id = ? ORDER BY ec.date DESC LIMIT 5");
$recentEnergy->execute([$userId]);
$recentEnergyData = $recentEnergy->fetchAll();

$recentTransport = $pdo->prepare("SELECT te.*, tt.name as transport_name FROM transport_entries te JOIN transport_types tt ON te.transport_id = tt.id WHERE te.user_id = ? ORDER BY te.date DESC LIMIT 5");
$recentTransport->execute([$userId]);
$recentTransportData = $recentTransport->fetchAll();

$pageTitle = 'Dashboard';
include 'includes/header.php';
?>

<div class="dashboard-header">
    <div class="container">
        <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h2>
        <p>Track your ecological impact and make a difference</p>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card">
                    <i class="bi-lightning-charge"></i>
                    <h3><?php echo number_format($energyEmissions, 1); ?></h3>
                    <p>kg CO2 from Energy</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card">
                    <i class="bi-car-front"></i>
                    <h3><?php echo number_format($transportEmissions, 1); ?></h3>
                    <p>kg CO2 from Transport</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card">
                    <i class="bi-trash"></i>
                    <h3><?php echo number_format($wasteImpact, 1); ?></h3>
                    <p>Waste Impact Score</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card">
                    <i class="bi-flag"></i>
                    <h3><?php echo $totalReports; ?></h3>
                    <p>Trash Reports Filed</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3 col-12 mb-4">
                <div class="sidebar">
                    <h5 class="mb-4">Quick Actions</h5>
                    <ul class="sidebar-menu">
                        <li><a href="<?php echo ROOT_PATH; ?>/modules/energy/add.php"><i class="bi-lightning-charge"></i> Add Energy Entry</a></li>
                        <li><a href="<?php echo ROOT_PATH; ?>/modules/transport/add.php"><i class="bi-car-front"></i> Add Transport Entry</a></li>
                        <li><a href="<?php echo ROOT_PATH; ?>/modules/waste/add.php"><i class="bi-trash"></i> Add Waste Entry</a></li>
                        <li><a href="<?php echo ROOT_PATH; ?>/modules/reports/add.php"><i class="bi-flag"></i> Report Trash</a></li>
                        <li><a href="<?php echo ROOT_PATH; ?>/profile.php"><i class="bi-person"></i> Edit Profile</a></li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-9 col-12">
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="module-card">
                            <div class="module-card-header energy">
                                <h5 class="mb-0"><i class="bi-lightning-charge me-2"></i>Recent Energy Entries</h5>
                            </div>
                            <div class="module-card-body">
                                <?php if (count($recentEnergyData) > 0): ?>
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Source</th>
                                            <th>Amount</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentEnergyData as $entry): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($entry['source_name']); ?></td>
                                            <td><?php echo $entry['amount']; ?> <?php echo $entry['unit']; ?></td>
                                            <td><?php echo formatDate($entry['date']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <a href="<?php echo ROOT_PATH; ?>/modules/energy/index.php" class="custom-btn mt-3 d-inline-block">View All</a>
                                <?php else: ?>
                                <p class="text-muted">No energy entries yet.</p>
                                <a href="<?php echo ROOT_PATH; ?>/modules/energy/add.php" class="custom-btn">Add First Entry</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <div class="module-card">
                            <div class="module-card-header transport">
                                <h5 class="mb-0"><i class="bi-car-front me-2"></i>Recent Transport Entries</h5>
                            </div>
                            <div class="module-card-body">
                                <?php if (count($recentTransportData) > 0): ?>
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Distance</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentTransportData as $entry): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($entry['transport_name']); ?></td>
                                            <td><?php echo $entry['distance_km']; ?> km</td>
                                            <td><?php echo formatDate($entry['date']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <a href="<?php echo ROOT_PATH; ?>/modules/transport/index.php" class="custom-btn mt-3 d-inline-block">View All</a>
                                <?php else: ?>
                                <p class="text-muted">No transport entries yet.</p>
                                <a href="<?php echo ROOT_PATH; ?>/modules/transport/add.php" class="custom-btn">Add First Entry</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="module-card">
                            <div class="module-card-header" style="background: linear-gradient(135deg, #4caf50, #81c784);">
                                <h5 class="mb-0"><i class="bi-graph-up me-2"></i>Your Eco Impact Summary</h5>
                            </div>
                            <div class="module-card-body">
                                <div class="row text-center">
                                    <div class="col-md-4 mb-3">
                                        <h4 class="text-success"><?php echo number_format($energyEmissions + $transportEmissions, 1); ?></h4>
                                        <p class="text-muted mb-0">Total kg CO2 Emissions</p>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <h4 class="text-warning"><?php echo number_format($wasteImpact, 1); ?></h4>
                                        <p class="text-muted mb-0">Waste Impact Score</p>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <h4 class="text-info"><?php echo $totalReports; ?></h4>
                                        <p class="text-muted mb-0">Community Reports</p>
                                    </div>
                                </div>
                                <hr>
                                <p class="text-center mb-0">Keep tracking your activities to improve your ecological footprint!</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
