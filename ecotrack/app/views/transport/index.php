<div class="dashboard-header">
    <div class="container">
        <h2><i class="bi-car-front me-2"></i>Transport Tracking</h2>
        <p>Track your travel emissions and discover greener alternatives</p>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <i class="bi-speedometer2"></i>
                    <h3><?php echo number_format($totalDistance, 1); ?> km</h3>
                    <p>Total Distance Traveled</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <i class="bi-cloud"></i>
                    <h3><?php echo number_format($totalEmissions, 2); ?> kg</h3>
                    <p>Total CO2 Emissions</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <i class="bi-list-check"></i>
                    <h3><?php echo count($entries); ?></h3>
                    <p>Total Entries</p>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>Your Transport Entries</h4>
            <a href="<?php echo URL_ROOT; ?>/transport/add" class="custom-btn"><i class="bi-plus-lg me-2"></i>Add Entry</a>
        </div>

        <?php if (count($entries) > 0): ?>
        <div class="module-card">
            <div class="module-card-body">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Transport</th>
                                <th>Distance</th>
                                <th>CO2 (kg)</th>
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($entries as $entry): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($entry['date'])); ?></td>
                                <td><?php echo htmlspecialchars($entry['transport_name']); ?></td>
                                <td><?php echo $entry['distance_km']; ?> km</td>
                                <td><?php echo number_format($entry['distance_km'] * $entry['emission_per_km'], 2); ?></td>
                                <td><?php echo htmlspecialchars($entry['notes'] ?? '-'); ?></td>
                                <td>
                                    <a href="<?php echo URL_ROOT; ?>/transport/edit/<?php echo $entry['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi-pencil"></i></a>
                                    <a href="<?php echo URL_ROOT; ?>/transport/delete/<?php echo $entry['id']; ?>" class="btn btn-sm btn-outline-danger delete-btn"><i class="bi-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <i class="bi-car-front" style="font-size: 60px; color: #ccc;"></i>
            <p class="text-muted mt-3">No transport entries yet. Start tracking your travels!</p>
            <a href="<?php echo URL_ROOT; ?>/transport/add" class="custom-btn">Add Your First Entry</a>
        </div>
        <?php endif; ?>
    </div>
</section>
