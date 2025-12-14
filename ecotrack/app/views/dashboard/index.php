<div class="dashboard-header">
    <div class="container">
        <h2>Welcome back, <?php echo htmlspecialchars($userName); ?>!</h2>
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
                        <li><a href="<?php echo URL_ROOT; ?>/energy/add"><i class="bi-lightning-charge"></i> Add Energy Entry</a></li>
                        <li><a href="<?php echo URL_ROOT; ?>/transport/add"><i class="bi-car-front"></i> Add Transport Entry</a></li>
                        <li><a href="<?php echo URL_ROOT; ?>/waste/add"><i class="bi-trash"></i> Add Waste Entry</a></li>
                        <li><a href="<?php echo URL_ROOT; ?>/reports/add"><i class="bi-flag"></i> Report Trash</a></li>
                        <li><a href="<?php echo URL_ROOT; ?>/profile"><i class="bi-person"></i> Edit Profile</a></li>
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
                                        <tr><th>Source</th><th>Amount</th><th>Date</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentEnergyData as $entry): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($entry['source_name']); ?></td>
                                            <td><?php echo $entry['amount']; ?> <?php echo $entry['unit']; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($entry['date'])); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <a href="<?php echo URL_ROOT; ?>/energy" class="custom-btn mt-3 d-inline-block">View All</a>
                                <?php else: ?>
                                <p class="text-muted">No energy entries yet.</p>
                                <a href="<?php echo URL_ROOT; ?>/energy/add" class="custom-btn">Add First Entry</a>
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
                                        <tr><th>Type</th><th>Distance</th><th>Date</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentTransportData as $entry): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($entry['transport_name']); ?></td>
                                            <td><?php echo $entry['distance_km']; ?> km</td>
                                            <td><?php echo date('M d, Y', strtotime($entry['date'])); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <a href="<?php echo URL_ROOT; ?>/transport" class="custom-btn mt-3 d-inline-block">View All</a>
                                <?php else: ?>
                                <p class="text-muted">No transport entries yet.</p>
                                <a href="<?php echo URL_ROOT; ?>/transport/add" class="custom-btn">Add First Entry</a>
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
