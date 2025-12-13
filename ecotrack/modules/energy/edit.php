<?php
require_once '../../includes/config.php';

if (!isLoggedIn()) {
    redirect('../../modules/auth/login.php');
}

$userId = $_SESSION['user_id'];
$entryId = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM energy_consumption WHERE id = ? AND user_id = ?");
$stmt->execute([$entryId, $userId]);
$entry = $stmt->fetch();

if (!$entry) {
    redirect('index.php');
}

$sources = $pdo->query("SELECT * FROM energy_sources ORDER BY name")->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sourceId = (int)($_POST['source_id'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);
    $date = $_POST['date'] ?? '';
    $notes = sanitize($_POST['notes'] ?? '');

    if ($sourceId <= 0 || $amount <= 0 || empty($date)) {
        $error = 'Please fill in all required fields.';
    } else {
        $stmt = $pdo->prepare("UPDATE energy_consumption SET source_id = ?, amount = ?, date = ?, notes = ? WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$sourceId, $amount, $date, $notes, $entryId, $userId])) {
            redirect('index.php');
        } else {
            $error = 'Failed to update entry. Please try again.';
        }
    }
}

$pageTitle = 'Edit Energy Entry';
include '../../includes/header.php';
?>

<div class="dashboard-header">
    <div class="container">
        <h2><i class="bi-lightning-charge me-2"></i>Edit Energy Entry</h2>
        <p>Update your energy consumption record</p>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="module-card">
                    <div class="module-card-header energy">
                        <h5 class="mb-0">Edit Entry</h5>
                    </div>
                    <div class="module-card-body">
                        <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" class="custom-form">
                            <div class="mb-3">
                                <label class="form-label">Energy Source *</label>
                                <select name="source_id" class="form-control" required>
                                    <?php foreach ($sources as $source): ?>
                                    <option value="<?php echo $source['id']; ?>" <?php echo $source['id'] == $entry['source_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($source['name']); ?> (<?php echo $source['unit']; ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Amount *</label>
                                <input type="number" name="amount" class="form-control" step="0.01" min="0" required value="<?php echo $entry['amount']; ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Date *</label>
                                <input type="date" name="date" class="form-control" required value="<?php echo $entry['date']; ?>">
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
