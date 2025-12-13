<?php
require_once '../../includes/config.php';
require_once '../../includes/PointsManager.php';

if (!isLoggedIn()) {
    redirect('../../modules/auth/login.php');
}

$userId = $_SESSION['user_id'];
$error = '';
$success = '';
$pointsManager = new PointsManager($pdo);

$sources = $pdo->query("SELECT * FROM energy_sources ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sourceId = (int)($_POST['source_id'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);
    $date = $_POST['date'] ?? '';
    $notes = sanitize($_POST['notes'] ?? '');

    if ($sourceId <= 0 || $amount <= 0 || empty($date)) {
        $error = 'Please fill in all required fields.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO energy_consumption (user_id, source_id, amount, date, notes) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$userId, $sourceId, $amount, $date, $notes])) {
            $entryId = $pdo->lastInsertId();
            $source = $pdo->query("SELECT name FROM energy_sources WHERE id = $sourceId")->fetch();
            $isRenewable = $source && (stripos($source['name'], 'Solar') !== false || stripos($source['name'], 'Wind') !== false);
            $points = PointsManager::getPointsForAction('energy_entry', $isRenewable);
            $description = $isRenewable ? 'Used renewable energy' : 'Logged energy consumption';
            $pointsManager->awardPoints($userId, $points, 'energy_entry', $description, $entryId, 'energy_consumption');
            $_SESSION['points_earned'] = $points;
            $_SESSION['points_message'] = $description;
            redirect('index.php');
        } else {
            $error = 'Failed to add entry. Please try again.';
        }
    }
}

$pageTitle = 'Add Energy Entry';
include '../../includes/header.php';
?>

<div class="dashboard-header">
    <div class="container">
        <h2><i class="bi-lightning-charge me-2"></i>Add Energy Entry</h2>
        <p>Log your energy consumption</p>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="module-card">
                    <div class="module-card-header energy">
                        <h5 class="mb-0">New Energy Entry</h5>
                    </div>
                    <div class="module-card-body">
                        <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" class="custom-form">
                            <div class="mb-3">
                                <label class="form-label">Energy Source *</label>
                                <select name="source_id" class="form-control" required>
                                    <option value="">Select source</option>
                                    <?php foreach ($sources as $source): ?>
                                    <option value="<?php echo $source['id']; ?>"><?php echo htmlspecialchars($source['name']); ?> (<?php echo $source['unit']; ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Amount *</label>
                                <input type="number" name="amount" class="form-control" step="0.01" min="0" required placeholder="Enter amount">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Date *</label>
                                <input type="date" name="date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Notes (optional)</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Any additional notes..."></textarea>
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
