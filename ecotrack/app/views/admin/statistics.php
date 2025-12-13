<div class="mb-4">
    <h4><i class="bi-graph-up me-2"></i>Platform Statistics</h4>
    <p class="mb-0 text-muted">Environmental impact data and platform metrics</p>
</div>

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
            <i class="bi-lightning-charge"></i>
            <h3><?php echo number_format($totalEnergy, 0); ?> kg</h3>
            <p>Energy Emissions</p>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stats-card">
            <i class="bi-car-front"></i>
            <h3><?php echo number_format($totalTransport, 0); ?> kg</h3>
            <p>Transport Emissions</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="module-card h-100">
            <div class="module-card-header">
                <h5 class="mb-0"><i class="bi-lightning-charge me-2"></i>Energy Sources</h5>
            </div>
            <div class="module-card-body">
                <?php if (count($energySources) > 0): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Source</th>
                                <th>Total Amount</th>
                                <th>CO2 (kg)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($energySources as $source): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($source['name']); ?></td>
                                <td><?php echo number_format($source['total_amount'], 1); ?> <?php echo $source['unit']; ?></td>
                                <td><?php echo number_format($source['total_emissions'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted text-center">No energy data available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="module-card h-100">
            <div class="module-card-header">
                <h5 class="mb-0"><i class="bi-car-front me-2"></i>Transport Types</h5>
            </div>
            <div class="module-card-body">
                <?php if (count($transportTypes) > 0): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Total Distance</th>
                                <th>CO2 (kg)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transportTypes as $type): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($type['name']); ?></td>
                                <td><?php echo number_format($type['total_distance'], 1); ?> km</td>
                                <td><?php echo number_format($type['total_emissions'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted text-center">No transport data available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 mb-4">
        <div class="module-card">
            <div class="module-card-header">
                <h5 class="mb-0"><i class="bi-trash me-2"></i>Waste Statistics</h5>
            </div>
            <div class="module-card-body">
                <?php if (count($wasteTypes) > 0): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Waste Type</th>
                                <th>Total Weight</th>
                                <th>Recyclable</th>
                                <th>Entries</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($wasteTypes as $type): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($type['name']); ?></td>
                                <td><?php echo number_format($type['total_weight'], 1); ?> kg</td>
                                <td>
                                    <?php if ($type['recyclable']): ?>
                                    <span class="status-badge status-resolved">Yes</span>
                                    <?php else: ?>
                                    <span class="status-badge status-rejected">No</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $type['entry_count']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted text-center">No waste data available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="mt-4">
    <a href="<?php echo URL_ROOT; ?>/admin" class="btn btn-outline-secondary"><i class="bi-arrow-left me-2"></i>Back to Dashboard</a>
</div>
