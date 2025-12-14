<?php
require_once '../../includes/config.php';

if (!isLoggedIn()) {
    redirect('../../modules/auth/login.php');
}

$userId = $_SESSION['user_id'];

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM waste_entries WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);
    redirect('index.php');
}

$stmt = $pdo->prepare("SELECT we.*, wt.name as waste_name, wt.recyclable, wt.impact_score FROM waste_entries we JOIN waste_types wt ON we.waste_type_id = wt.id WHERE we.user_id = ? ORDER BY we.date DESC");
$stmt->execute([$userId]);
$entries = $stmt->fetchAll();

$totalWeight = 0;
$totalImpact = 0;
foreach ($entries as $entry) {
    $totalWeight += $entry['weight_kg'];
    $totalImpact += $entry['weight_kg'] * $entry['impact_score'];
}

$pageTitle = 'Waste Management';
include '../../includes/header.php';
?>

<div class="dashboard-header">
    <div class="container">
        <h2><i class="bi-trash me-2"></i>Waste Management</h2>
        <p>Track your waste and improve recycling habits</p>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <i class="bi-box-seam"></i>
                    <h3><?php echo number_format($totalWeight, 1); ?> kg</h3>
                    <p>Total Waste</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <i class="bi-bar-chart-line"></i>
                    <h3><?php echo number_format($totalImpact, 1); ?></h3>
                    <p>Environmental Impact Score</p>
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
            <h4>Your Waste Entries</h4>
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
                                <th>Type</th>
                                <th>Weight</th>
                                <th>Recyclable</th>
                                <th>Disposed</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($entries as $entry): ?>
                            <tr>
                                <td><?php echo formatDate($entry['date']); ?></td>
                                <td><?php echo htmlspecialchars($entry['waste_name']); ?></td>
                                <td><?php echo $entry['weight_kg']; ?> kg</td>
                                <td>
                                    <?php if ($entry['recyclable']): ?>
                                    <span class="status-badge status-resolved">Yes</span>
                                    <?php else: ?>
                                    <span class="status-badge status-rejected">No</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($entry['properly_disposed']): ?>
                                    <span class="status-badge status-resolved">Properly</span>
                                    <?php else: ?>
                                    <span class="status-badge status-pending">Improperly</span>
                                    <?php endif; ?>
                                </td>
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
            <i class="bi-trash" style="font-size: 60px; color: #ccc;"></i>
            <p class="text-muted mt-3">No waste entries yet. Start tracking!</p>
            <a href="add.php" class="custom-btn">Add Your First Entry</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../../includes/footer.php'; ?>
