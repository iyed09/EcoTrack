<?php
require_once '../../includes/config.php';

if (!isLoggedIn()) {
    redirect('../../modules/auth/login.php');
}

$userId = $_SESSION['user_id'];
$entryId = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM waste_entries WHERE id = ? AND user_id = ?");
$stmt->execute([$entryId, $userId]);
$entry = $stmt->fetch();

if (!$entry) {
    redirect('index.php');
}

$wasteTypes = $pdo->query("SELECT * FROM waste_types ORDER BY name")->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $wasteTypeId = (int)($_POST['waste_type_id'] ?? 0);
    $weight = (float)($_POST['weight_kg'] ?? 0);
    $properlyDisposed = isset($_POST['properly_disposed']) ? 1 : 0;
    $date = $_POST['date'] ?? '';
    $notes = sanitize($_POST['notes'] ?? '');

    if ($wasteTypeId <= 0 || $weight <= 0 || empty($date)) {
        $error = 'Please fill in all required fields.';
    } else {
        $stmt = $pdo->prepare("UPDATE waste_entries SET waste_type_id = ?, weight_kg = ?, properly_disposed = ?, date = ?, notes = ? WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$wasteTypeId, $weight, $properlyDisposed, $date, $notes, $entryId, $userId])) {
            redirect('index.php');
        } else {
            $error = 'Failed to update entry.';
        }
    }
}

$pageTitle = 'Edit Waste Entry';
include '../../includes/header.php';
?>

<div class="dashboard-header">
    <div class="container">
        <h2><i class="bi-trash me-2"></i>Edit Waste Entry</h2>
        <p>Update your waste record</p>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="module-card">
                    <div class="module-card-header waste">
                        <h5 class="mb-0">Edit Entry</h5>
                    </div>
                    <div class="module-card-body">
                        <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" class="custom-form">
                            <div class="mb-3">
                                <label class="form-label">Waste Type *</label>
                                <select name="waste_type_id" class="form-control" required>
                                    <?php foreach ($wasteTypes as $type): ?>
                                    <option value="<?php echo $type['id']; ?>" <?php echo $type['id'] == $entry['waste_type_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($type['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Weight (kg) *</label>
                                <input type="number" name="weight_kg" class="form-control" step="0.1" min="0" required value="<?php echo $entry['weight_kg']; ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Date *</label>
                                <input type="date" name="date" class="form-control" required value="<?php echo $entry['date']; ?>">
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" name="properly_disposed" class="form-check-input" id="properlyDisposed" <?php echo $entry['properly_disposed'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="properlyDisposed">Properly disposed/recycled</label>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Notes (optional)</label>
                                <textarea name="notes" class="form-control" rows="3"><?php echo htmlspecialchars($entry['notes'] ?? ''); ?></textarea>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="custom-btn">Update Entry</button>
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
