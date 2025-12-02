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

$pageTitle = 'Manage Reports';
include '../includes/header.php';
?>

<div class="dashboard-header">
    <div class="container">
        <h2><i class="bi-flag me-2"></i>Manage Reports</h2>
        <p>Review and manage trash disposal reports</p>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-12 mb-4">
                <div class="sidebar">
                    <h5 class="mb-4">Admin Menu</h5>
                    <ul class="sidebar-menu">
                        <li><a href="index.php"><i class="bi-speedometer2"></i> Dashboard</a></li>
                        <li><a href="users.php"><i class="bi-people"></i> Manage Users</a></li>
                        <li><a href="reports.php" class="active"><i class="bi-flag"></i> Manage Reports</a></li>
                        <li><a href="statistics.php"><i class="bi-bar-chart"></i> Statistics</a></li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-9 col-12">
                <?php if (count($reports) > 0): ?>
                <div class="row">
                    <?php foreach ($reports as $report): ?>
                    <div class="col-lg-6 mb-4">
                        <div class="module-card">
                            <?php if ($report['photo_path']): ?>
                            <img src="../uploads/reports/<?php echo htmlspecialchars($report['photo_path']); ?>" class="img-fluid" style="height: 200px; width: 100%; object-fit: cover;" alt="Report photo">
                            <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
                                <i class="bi-image text-muted" style="font-size: 48px;"></i>
                            </div>
                            <?php endif; ?>
                            <div class="module-card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <small class="text-muted">Reported by: <?php echo htmlspecialchars($report['reporter_name'] ?? 'Unknown'); ?></small>
                                        <br>
                                        <small class="text-muted"><?php echo formatDate($report['created_at']); ?></small>
                                    </div>
                                    <span class="status-badge status-<?php echo $report['status']; ?>">
                                        <?php echo ucfirst($report['status']); ?>
                                    </span>
                                </div>
                                
                                <h6><i class="bi-geo-alt me-1"></i><?php echo htmlspecialchars($report['location_description']); ?></h6>
                                <p class="text-muted small"><?php echo htmlspecialchars($report['description']); ?></p>
                                
                                <div class="d-flex gap-2 flex-wrap">
                                    <?php if ($report['status'] !== 'resolved'): ?>
                                    <a href="reports.php?id=<?php echo $report['id']; ?>&status=resolved" class="btn btn-sm btn-success">
                                        <i class="bi-check-lg me-1"></i>Resolve
                                    </a>
                                    <?php endif; ?>
                                    <?php if ($report['status'] !== 'rejected'): ?>
                                    <a href="reports.php?id=<?php echo $report['id']; ?>&status=rejected" class="btn btn-sm btn-warning">
                                        <i class="bi-x-lg me-1"></i>Reject
                                    </a>
                                    <?php endif; ?>
                                    <?php if ($report['status'] !== 'pending'): ?>
                                    <a href="reports.php?id=<?php echo $report['id']; ?>&status=pending" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi-arrow-counterclockwise me-1"></i>Reset
                                    </a>
                                    <?php endif; ?>
                                    <a href="reports.php?delete=<?php echo $report['id']; ?>" class="btn btn-sm btn-outline-danger delete-btn">
                                        <i class="bi-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi-flag" style="font-size: 60px; color: #ccc;"></i>
                    <p class="text-muted mt-3">No reports yet.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
