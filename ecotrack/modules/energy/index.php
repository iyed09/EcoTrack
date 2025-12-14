<?php
require_once '../../includes/config.php';

if (!isLoggedIn()) {
    redirect('../../modules/auth/login.php');
}

$userId = $_SESSION['user_id'];

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM energy_consumption WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);
    redirect('index.php');
}

$stmt = $pdo->prepare("SELECT ec.*, es.name as source_name, es.unit, es.emission_factor FROM energy_consumption ec JOIN energy_sources es ON ec.source_id = es.id WHERE ec.user_id = ? ORDER BY ec.date DESC");
$stmt->execute([$userId]);
$entries = $stmt->fetchAll();

$totalEmissions = 0;
foreach ($entries as $entry) {
    $totalEmissions += $entry['amount'] * $entry['emission_factor'];
}

$pageTitle = 'Energy Tracking';
include '../../includes/header.php';
?>

<div class="dashboard-header">
    <div class="container">
        <h2><i class="bi-lightning-charge me-2"></i>Energy Tracking</h2>
        <p>Monitor your energy consumption and reduce your carbon footprint</p>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="stats-card">
                    <i class="bi-lightning-charge"></i>
                    <h3><?php echo number_format($totalEmissions, 2); ?> kg</h3>
                    <p>Total CO2 Emissions from Energy</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stats-card">
                    <i class="bi-list-check"></i>
                    <h3><?php echo count($entries); ?></h3>
                    <p>Total Entries</p>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>Your Energy Entries</h4>
            <a href="add.php" class="custom-btn"><i class="bi-plus-lg me-2"></i>Add Entry</a>
        </div>

        <?php if (count($entries) > 0): ?>
        <div class="module-card">
            <div class="module-card-body">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Source</th>
                                <th>Amount</th>
                                <th>CO2 (kg)</th>
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($entries as $entry): ?>
                            <tr>
                                <td><?php echo formatDate($entry['date']); ?></td>
                                <td><?php echo htmlspecialchars($entry['source_name']); ?></td>
                                <td><?php echo $entry['amount']; ?> <?php echo $entry['unit']; ?></td>
                                <td><?php echo number_format($entry['amount'] * $entry['emission_factor'], 2); ?></td>
                                <td><?php echo htmlspecialchars($entry['notes'] ?? '-'); ?></td>
                                <td>
                                    <a href="edit.php?id=<?php echo $entry['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi-pencil"></i></a>
                                    <a href="index.php?delete=<?php echo $entry['id']; ?>" class="btn btn-sm btn-outline-danger delete-btn"><i class="bi-trash"></i></a>
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
            <i class="bi-lightning-charge" style="font-size: 60px; color: #ccc;"></i>
            <p class="text-muted mt-3">No energy entries yet. Start tracking your consumption!</p>
            <a href="add.php" class="custom-btn">Add Your First Entry</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../../includes/footer.php'; ?>
