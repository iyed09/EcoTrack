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

$userGrowthData = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
    FROM users 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
    ORDER BY month ASC
")->fetchAll();

$monthlyEmissions = $pdo->query("
    SELECT 
        DATE_FORMAT(ec.date, '%Y-%m') as month,
        COALESCE(SUM(ec.amount * es.emission_factor), 0) as energy_co2
    FROM energy_consumption ec 
    JOIN energy_sources es ON ec.source_id = es.id 
    WHERE ec.date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(ec.date, '%Y-%m')
    ORDER BY month ASC
")->fetchAll();

$monthlyTransport = $pdo->query("
    SELECT 
        DATE_FORMAT(te.date, '%Y-%m') as month,
        COALESCE(SUM(te.distance_km * tt.emission_per_km), 0) as transport_co2
    FROM transport_entries te 
    JOIN transport_types tt ON te.transport_id = tt.id 
    WHERE te.date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(te.date, '%Y-%m')
    ORDER BY month ASC
")->fetchAll();

$wasteByType = $pdo->query("
    SELECT wt.name, SUM(we.weight_kg) as total 
    FROM waste_entries we 
    JOIN waste_types wt ON we.waste_type_id = wt.id 
    GROUP BY wt.id 
    ORDER BY total DESC
")->fetchAll();

$userGrowthLabels = json_encode(array_column($userGrowthData, 'month'));
$userGrowthCounts = json_encode(array_map('intval', array_column($userGrowthData, 'count')));

$emissionLabels = array_unique(array_merge(
    array_column($monthlyEmissions, 'month'),
    array_column($monthlyTransport, 'month')
));
sort($emissionLabels);
$emissionLabels = array_values($emissionLabels);

$energyEmissions = [];
$transportEmissions = [];
foreach ($emissionLabels as $month) {
    $energyFound = false;
    $transportFound = false;
    foreach ($monthlyEmissions as $e) {
        if ($e['month'] === $month) {
            $energyEmissions[] = floatval($e['energy_co2']);
            $energyFound = true;
            break;
        }
    }
    if (!$energyFound) $energyEmissions[] = 0;
    
    foreach ($monthlyTransport as $t) {
        if ($t['month'] === $month) {
            $transportEmissions[] = floatval($t['transport_co2']);
            $transportFound = true;
            break;
        }
    }
    if (!$transportFound) $transportEmissions[] = 0;
}

$wasteLabels = json_encode(array_column($wasteByType, 'name'));
$wasteTotals = json_encode(array_map('floatval', array_column($wasteByType, 'total')));

$pageTitle = 'Statistics';
include 'includes/admin_header.php';
?>

<div class="page-header mb-4">
    <h4><i class="bx bx-bar-chart-alt-2 me-2"></i>Global Statistics</h4>
    <p class="mb-0">Interactive overview of platform-wide environmental data</p>
</div>

<div class="row mb-4">
    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
        <div class="stats-card">
            <div class="stats-icon primary">
                <i class="bx bx-user"></i>
            </div>
            <div class="stats-content">
                <h3><?php echo $totalUsers; ?></h3>
                <p>Regular Users</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
        <div class="stats-card">
            <div class="stats-icon success">
                <i class="bx bx-shield"></i>
            </div>
            <div class="stats-content">
                <h3><?php echo $totalAdmins; ?></h3>
                <p>Administrators</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
        <div class="stats-card">
            <div class="stats-icon warning">
                <i class="bx bx-cloud"></i>
            </div>
            <div class="stats-content">
                <h3><?php echo number_format($totalEnergy + $totalTransport, 0); ?></h3>
                <p>Total kg CO2</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
        <div class="stats-card">
            <div class="stats-icon danger">
                <i class="bx bx-trash"></i>
            </div>
            <div class="stats-content">
                <h3><?php echo number_format($totalWaste, 1); ?> kg</h3>
                <p>Total Waste</p>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">
                    <i class="bx bx-line-chart me-2 text-primary"></i>User Growth (Last 12 Months)
                </h5>
            </div>
            <div class="card-body">
                <div style="position: relative; height: 300px; width: 100%;">
                    <canvas id="userGrowthChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bx bx-pie-chart-alt-2 me-2 text-warning"></i>Reports Status
                </h5>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <div style="position: relative; height: 250px; width: 100%;">
                    <canvas id="reportsChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">
                    <i class="bx bx-cloud me-2 text-info"></i>CO2 Emissions Over Time (kg)
                </h5>
            </div>
            <div class="card-body">
                <div style="position: relative; height: 300px; width: 100%;">
                    <canvas id="emissionsChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bx bx-doughnut-chart me-2 text-success"></i>Waste by Type (kg)
                </h5>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <div style="position: relative; height: 300px; width: 100%;">
                    <canvas id="wasteChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bx bx-leaf me-2 text-success"></i>Environmental Impact Summary
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-6 mb-3">
                        <div class="p-3 bg-light rounded">
                            <i class="bx bx-bolt text-warning mb-2" style="font-size: 2.5rem;"></i>
                            <h4 class="mb-1"><?php echo number_format($totalEnergy, 1); ?> kg</h4>
                            <p class="text-muted mb-0">CO2 from Energy</p>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="p-3 bg-light rounded">
                            <i class="bx bx-car text-info mb-2" style="font-size: 2.5rem;"></i>
                            <h4 class="mb-1"><?php echo number_format($totalTransport, 1); ?> kg</h4>
                            <p class="text-muted mb-0">CO2 from Transport</p>
                        </div>
                    </div>
                </div>
                <div class="row text-center mt-3">
                    <div class="col-12">
                        <div class="p-4 bg-primary rounded text-white">
                            <i class="bx bx-globe mb-2" style="font-size: 3rem;"></i>
                            <h3 class="mb-1"><?php echo number_format($totalEnergy + $totalTransport, 1); ?> kg</h3>
                            <p class="mb-0">Total CO2 Emissions</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bx bx-bolt me-2 text-warning"></i>Top Energy Sources
                </h5>
            </div>
            <div class="card-body">
                <?php if (count($topEnergySources) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Source</th>
                                <th>Total Usage</th>
                                <th>Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $maxEnergy = max(array_column($topEnergySources, 'total')) ?: 1;
                            foreach ($topEnergySources as $index => $source): 
                            $percentage = ($source['total'] / $maxEnergy) * 100;
                            ?>
                            <tr>
                                <td>
                                    <span class="badge bg-label-primary rounded-pill"><?php echo $index + 1; ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($source['name']); ?></td>
                                <td>
                                    <strong><?php echo number_format($source['total'], 1); ?></strong>
                                </td>
                                <td style="width: 30%;">
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-warning" style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="bx bx-bolt text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">No energy data available.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bx bx-car me-2 text-info"></i>Top Transport Types
                </h5>
            </div>
            <div class="card-body">
                <?php if (count($topTransportTypes) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Type</th>
                                <th>Total km</th>
                                <th>Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $maxTransport = max(array_column($topTransportTypes, 'total')) ?: 1;
                            foreach ($topTransportTypes as $index => $type): 
                            $percentage = ($type['total'] / $maxTransport) * 100;
                            ?>
                            <tr>
                                <td>
                                    <span class="badge bg-label-info rounded-pill"><?php echo $index + 1; ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($type['name']); ?></td>
                                <td>
                                    <strong><?php echo number_format($type['total'], 1); ?> km</strong>
                                </td>
                                <td style="width: 30%;">
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-info" style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="bx bx-car text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">No transport data available.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartColors = {
        primary: 'rgba(105, 108, 255, 1)',
        primaryLight: 'rgba(105, 108, 255, 0.2)',
        success: 'rgba(113, 221, 55, 1)',
        successLight: 'rgba(113, 221, 55, 0.2)',
        warning: 'rgba(255, 171, 0, 1)',
        warningLight: 'rgba(255, 171, 0, 0.2)',
        danger: 'rgba(255, 62, 29, 1)',
        dangerLight: 'rgba(255, 62, 29, 0.2)',
        info: 'rgba(3, 195, 236, 1)',
        infoLight: 'rgba(3, 195, 236, 0.2)',
        secondary: 'rgba(133, 146, 163, 1)'
    };

    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
            duration: 800,
            easing: 'easeOutQuart'
        }
    };

    const userGrowthLabels = <?php echo $userGrowthLabels ?: '[]'; ?>;
    const userGrowthData = <?php echo $userGrowthCounts ?: '[]'; ?>;
    
    if (document.getElementById('userGrowthChart')) {
        new Chart(document.getElementById('userGrowthChart'), {
            type: 'line',
            data: {
                labels: userGrowthLabels,
                datasets: [{
                    label: 'New Users',
                    data: userGrowthData,
                    borderColor: chartColors.primary,
                    backgroundColor: chartColors.primaryLight,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: chartColors.primary,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5
                }]
            },
            options: {
                ...defaultOptions,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    }

    if (document.getElementById('reportsChart')) {
        new Chart(document.getElementById('reportsChart'), {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Resolved', 'Rejected'],
                datasets: [{
                    data: [<?php echo $pendingReports; ?>, <?php echo $resolvedReports; ?>, <?php echo $rejectedReports; ?>],
                    backgroundColor: [chartColors.warning, chartColors.success, chartColors.danger],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                ...defaultOptions,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 20 }
                    }
                }
            }
        });
    }

    const emissionLabels = <?php echo json_encode($emissionLabels); ?>;
    const energyEmissions = <?php echo json_encode($energyEmissions); ?>;
    const transportEmissions = <?php echo json_encode($transportEmissions); ?>;
    
    if (document.getElementById('emissionsChart')) {
        new Chart(document.getElementById('emissionsChart'), {
            type: 'bar',
            data: {
                labels: emissionLabels,
                datasets: [
                    {
                        label: 'Energy CO2',
                        data: energyEmissions,
                        backgroundColor: chartColors.warning,
                        borderRadius: 4
                    },
                    {
                        label: 'Transport CO2',
                        data: transportEmissions,
                        backgroundColor: chartColors.info,
                        borderRadius: 4
                    }
                ]
            },
            options: {
                ...defaultOptions,
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    x: { stacked: true },
                    y: { 
                        stacked: true,
                        beginAtZero: true
                    }
                }
            }
        });
    }

    const wasteLabels = <?php echo $wasteLabels ?: '[]'; ?>;
    const wasteData = <?php echo $wasteTotals ?: '[]'; ?>;
    
    if (document.getElementById('wasteChart')) {
        const wasteColors = [
            chartColors.primary,
            chartColors.success,
            chartColors.warning,
            chartColors.danger,
            chartColors.info,
            chartColors.secondary,
            'rgba(153, 102, 255, 1)'
        ];
        
        new Chart(document.getElementById('wasteChart'), {
            type: 'polarArea',
            data: {
                labels: wasteLabels,
                datasets: [{
                    data: wasteData,
                    backgroundColor: wasteColors.map(c => c.replace('1)', '0.7)')),
                    borderColor: wasteColors,
                    borderWidth: 2
                }]
            },
            options: {
                ...defaultOptions,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { padding: 15 }
                    }
                }
            }
        });
    }
});
</script>

<?php include 'includes/admin_footer.php'; ?>
