<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

if (isset($_GET['status']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $status = sanitize($_GET['status']);

    if (in_array($status, ['pending', 'resolved', 'rejected'])) {
        $stmt = $pdo->prepare("UPDATE trash_reports SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$status, $id]);
    }
    redirect('reports.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['response_text']) && isset($_POST['report_id'])) {
    $response = sanitize($_POST['response_text']);
    $reportId = (int)$_POST['report_id'];

    if (!empty($response)) {
        $stmt = $pdo->prepare("UPDATE trash_reports SET admin_response = ?, response_at = CURRENT_TIMESTAMP, status = 'resolved', updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$response, $reportId]);
    }
    redirect('reports.php');
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT photo_path FROM trash_reports WHERE id = ?");
    $stmt->execute([$id]);
    $report = $stmt->fetch();

    if ($report && $report['photo_path']) {
        @unlink('../uploads/reports/' . $report['photo_path']);
    }

    $stmt = $pdo->prepare("DELETE FROM trash_reports WHERE id = ?");
    $stmt->execute([$id]);
    redirect('reports.php');
}

$reports = $pdo->query("SELECT tr.*, u.name as reporter_name FROM trash_reports tr LEFT JOIN users u ON tr.reporter_id = u.id ORDER BY tr.created_at DESC")->fetchAll();

// Count stats
$pendingCount = count(array_filter($reports, fn($r) => $r['status'] === 'pending'));
$resolvedCount = count(array_filter($reports, fn($r) => $r['status'] === 'resolved'));
$rejectedCount = count(array_filter($reports, fn($r) => $r['status'] === 'rejected'));

$pageTitle = 'Manage Reports';
include 'includes/admin_header.php';
?>

<!-- Page Header -->
<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4><i class="bx bx-flag me-2"></i>Manage Reports</h4>
            <p class="mb-0">Review and manage trash disposal reports</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-label-warning"><?php echo $pendingCount; ?> Pending</span>
            <span class="badge bg-label-success"><?php echo $resolvedCount; ?> Resolved</span>
            <span class="badge bg-label-danger"><?php echo $rejectedCount; ?> Rejected</span>
        </div>
    </div>
</div>

<!-- Stats Row -->
<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stats-card">
            <div class="stats-icon primary">
                <i class="bx bx-flag"></i>
            </div>
            <div class="stats-content">
                <h3><?php echo count($reports); ?></h3>
                <p>Total Reports</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stats-card">
            <div class="stats-icon warning">
                <i class="bx bx-time-five"></i>
            </div>
            <div class="stats-content">
                <h3><?php echo $pendingCount; ?></h3>
                <p>Pending</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stats-card">
            <div class="stats-icon success">
                <i class="bx bx-check-circle"></i>
            </div>
            <div class="stats-content">
                <h3><?php echo $resolvedCount; ?></h3>
                <p>Resolved</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stats-card">
            <div class="stats-icon danger">
                <i class="bx bx-x-circle"></i>
            </div>
            <div class="stats-content">
                <h3><?php echo $rejectedCount; ?></h3>
                <p>Rejected</p>
            </div>
        </div>
    </div>
</div>

<!-- Reports Grid -->
<?php if (count($reports) > 0): ?>
<div class="row">
    <?php foreach ($reports as $report): ?>
    <div class="col-lg-6 col-xl-4 mb-4">
        <div class="card h-100">
            <?php if ($report['photo_path']): ?>
            <img src="../uploads/reports/<?php echo htmlspecialchars($report['photo_path']); ?>" 
                 class="card-img-top" 
                 style="height: 180px; object-fit: cover;" 
                 alt="Report photo">
            <?php else: ?>
            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                <i class="bx bx-image text-muted" style="font-size: 3rem;"></i>
            </div>
            <?php endif; ?>
            
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <small class="text-muted d-block">
                            <i class="bx bx-user me-1"></i><?php echo htmlspecialchars($report['reporter_name'] ?? 'Unknown'); ?>
                        </small>
                        <small class="text-muted">
                            <i class="bx bx-calendar me-1"></i><?php echo formatDate($report['created_at']); ?>
                        </small>
                    </div>
                    <?php
                    $statusClass = 'bg-label-secondary';
                    if ($report['status'] === 'pending') $statusClass = 'bg-label-warning';
                    if ($report['status'] === 'resolved') $statusClass = 'bg-label-success';
                    if ($report['status'] === 'rejected') $statusClass = 'bg-label-danger';
                    ?>
                    <span class="badge <?php echo $statusClass; ?>">
                        <?php echo ucfirst($report['status']); ?>
                    </span>
                </div>

                <h6 class="card-title">
                    <i class="bx bx-map me-1 text-primary"></i>
                    <?php echo htmlspecialchars($report['location_description']); ?>
                </h6>
                <p class="card-text text-muted small">
                    <?php echo htmlspecialchars(substr($report['description'], 0, 100)) . (strlen($report['description']) > 100 ? '...' : ''); ?>
                </p>

                <!-- Action Buttons -->
                <div class="d-flex gap-1 flex-wrap mb-3">
                    <?php if ($report['status'] !== 'resolved'): ?>
                    <a href="reports.php?id=<?php echo $report['id']; ?>&status=resolved" class="btn btn-sm btn-label-success">
                        <i class="bx bx-check me-1"></i>Resolve
                    </a>
                    <?php endif; ?>
                    <?php if ($report['status'] !== 'rejected'): ?>
                    <a href="reports.php?id=<?php echo $report['id']; ?>&status=rejected" class="btn btn-sm btn-label-warning">
                        <i class="bx bx-x me-1"></i>Reject
                    </a>
                    <?php endif; ?>
                    <?php if ($report['status'] !== 'pending'): ?>
                    <a href="reports.php?id=<?php echo $report['id']; ?>&status=pending" class="btn btn-sm btn-outline-secondary">
                        <i class="bx bx-reset me-1"></i>Reset
                    </a>
                    <?php endif; ?>
                    <a href="reports.php?delete=<?php echo $report['id']; ?>" 
                       class="btn btn-sm btn-label-danger delete-btn"
                       data-confirm="Are you sure you want to delete this report?">
                        <i class="bx bx-trash"></i>
                    </a>
                </div>

                <?php if ($report['admin_response']): ?>
                <!-- Admin Response Display -->
                <div class="alert alert-success mb-0 py-2 px-3">
                    <small class="fw-semibold d-block mb-1">
                        <i class="bx bx-check-circle me-1"></i>Admin Response
                    </small>
                    <small><?php echo htmlspecialchars($report['admin_response']); ?></small>
                </div>
                <?php else: ?>
                <!-- Response Form -->
                <div class="collapse" id="responseForm<?php echo $report['id']; ?>">
                    <form method="POST" action="reports.php" class="mt-2">
                        <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
                        <div class="mb-2">
                            <textarea name="response_text" 
                                      class="form-control form-control-sm" 
                                      rows="2" 
                                      placeholder="Write your response..." 
                                      required></textarea>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary w-100">
                            <i class="bx bx-send me-1"></i>Send & Mark Resolved
                        </button>
                    </form>
                </div>
                <button class="btn btn-sm btn-outline-primary w-100 mt-2" 
                        type="button" 
                        data-bs-toggle="collapse" 
                        data-bs-target="#responseForm<?php echo $report['id']; ?>">
                    <i class="bx bx-reply me-1"></i>Send Response
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bx bx-flag text-muted" style="font-size: 4rem;"></i>
        <h5 class="mt-3 text-muted">No Reports Yet</h5>
        <p class="text-muted">When users submit trash reports, they will appear here.</p>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/admin_footer.php'; ?>
