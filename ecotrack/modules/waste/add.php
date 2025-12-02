<?php
require_once '../../includes/config.php';
require_once '../../includes/PointsManager.php';

if (!isLoggedIn()) {
    redirect('../../modules/auth/login.php');
}

$userId = $_SESSION['user_id'];
$error = '';
$pointsManager = new PointsManager($pdo);

$wasteTypes = $pdo->query("SELECT * FROM waste_types ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $wasteTypeId = (int)($_POST['waste_type_id'] ?? 0);
    $weight = (float)($_POST['weight_kg'] ?? 0);
    $properlyDisposed = isset($_POST['properly_disposed']) ? 1 : 0;
    $date = $_POST['date'] ?? '';
    $notes = sanitize($_POST['notes'] ?? '');

    if ($wasteTypeId <= 0 || $weight <= 0 || empty($date)) {
        $error = 'Please fill in all required fields.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, properly_disposed, date, notes) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$userId, $wasteTypeId, $weight, $properlyDisposed, $date, $notes])) {
            $entryId = $pdo->lastInsertId();
            $wasteType = $pdo->query("SELECT recyclable FROM waste_types WHERE id = $wasteTypeId")->fetch();
            $isRecyclable = $wasteType && $wasteType['recyclable'] && $properlyDisposed;
            $actionType = $isRecyclable ? 'recycle' : 'waste_entry';
            $points = PointsManager::getPointsForAction('waste_entry', $isRecyclable);
            $description = $isRecyclable ? 'Properly recycled waste' : 'Logged waste disposal';
            $pointsManager->awardPoints($userId, $points, $actionType, $description, $entryId, 'waste_entry');
            $_SESSION['points_earned'] = $points;
            $_SESSION['points_message'] = $description;
            redirect('index.php');
        } else {
            $error = 'Failed to add entry. Please try again.';
        }
    }
}

$pageTitle = 'Add Waste Entry';
include '../../includes/header.php';
?>

<div class="dashboard-header">
    <div class="container">
        <h2><i class="bi-trash me-2"></i>Add Waste Entry</h2>
        <p>Log your waste disposal</p>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="module-card">
                    <div class="module-card-header waste">
                        <h5 class="mb-0">New Waste Entry</h5>
                    </div>
                    <div class="module-card-body">
                        <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" class="custom-form">
                            <div class="mb-3">
                                <label class="form-label">Waste Type *</label>
                                <select name="waste_type_id" class="form-control" required>
                                    <option value="">Select waste type</option>
                                    <?php foreach ($wasteTypes as $type): ?>
                                    <option value="<?php echo $type['id']; ?>"><?php echo htmlspecialchars($type['name']); ?> <?php echo $type['recyclable'] ? '(Recyclable)' : ''; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Weight (kg) *</label>
                                <input type="number" name="weight_kg" class="form-control" step="0.1" min="0" required placeholder="Enter weight">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Date *</label>
                                <input type="date" name="date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" name="properly_disposed" class="form-check-input" id="properlyDisposed" checked>
                                <label class="form-check-label" for="properlyDisposed">Properly disposed/recycled</label>
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
