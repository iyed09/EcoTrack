<?php
require_once '../../includes/config.php';
require_once '../../includes/PointsManager.php';

if (!isLoggedIn()) {
    redirect('../../modules/auth/login.php');
}

$userId = $_SESSION['user_id'];
$error = '';
$pointsManager = new PointsManager($pdo);

$transports = $pdo->query("SELECT * FROM transport_types ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transportId = (int)($_POST['transport_id'] ?? 0);
    $distance = (float)($_POST['distance_km'] ?? 0);
    $date = $_POST['date'] ?? '';
    $notes = sanitize($_POST['notes'] ?? '');

    if ($transportId <= 0 || $distance <= 0 || empty($date)) {
        $error = 'Please fill in all required fields.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO transport_entries (user_id, transport_id, distance_km, date, notes) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$userId, $transportId, $distance, $date, $notes])) {
            $entryId = $pdo->lastInsertId();
            $transport = $pdo->query("SELECT name, is_eco_friendly FROM transport_types WHERE id = $transportId")->fetch();
            $isEco = $transport && $transport['is_eco_friendly'];
            $actionType = $isEco ? 'eco_transport' : 'transport_entry';
            $points = PointsManager::getPointsForAction('transport_entry', $isEco);
            $description = $isEco ? 'Used eco-friendly transport' : 'Logged transport activity';
            $pointsManager->awardPoints($userId, $points, $actionType, $description, $entryId, 'transport_entry');
            $_SESSION['points_earned'] = $points;
            $_SESSION['points_message'] = $description;
            redirect('index.php');
        } else {
            $error = 'Failed to add entry. Please try again.';
        }
    }
}

$pageTitle = 'Add Transport Entry';
include '../../includes/header.php';
?>

<div class="dashboard-header">
    <div class="container">
        <h2><i class="bi-car-front me-2"></i>Add Transport Entry</h2>
        <p>Log your travel distance</p>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="module-card">
                    <div class="module-card-header transport">
                        <h5 class="mb-0">New Transport Entry</h5>
                    </div>
                    <div class="module-card-body">
                        <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" class="custom-form">
                            <div class="mb-3">
                                <label class="form-label">Transport Type *</label>
                                <select name="transport_id" class="form-control" required>
                                    <option value="">Select transport</option>
                                    <?php foreach ($transports as $transport): ?>
                                    <option value="<?php echo $transport['id']; ?>"><?php echo htmlspecialchars($transport['name']); ?> (<?php echo $transport['emission_per_km']; ?> kg CO2/km)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Distance (km) *</label>
                                <input type="number" name="distance_km" class="form-control" step="0.1" min="0" required placeholder="Enter distance">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Date *</label>
                                <input type="date" name="date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Notes (optional)</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Trip description..."></textarea>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="custom-btn">Add Entry</button>
                                <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../../includes/footer.php'; ?>
