<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4><i class="bi-flag me-2"></i>Manage Reports</h4>
        <p class="mb-0 text-muted">Review and respond to trash reports</p>
    </div>
    <span class="badge bg-primary fs-6"><?php echo count($reports); ?> Reports</span>
</div>

<?php if (isset($error) && $error): ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if (isset($success) && $success): ?>
<div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="stats-card">
            <i class="bi-hourglass-split text-warning"></i>
            <h3><?php echo $pendingCount; ?></h3>
            <p>Pending</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stats-card">
            <i class="bi-check-circle text-success"></i>
            <h3><?php echo $resolvedCount; ?></h3>
            <p>Resolved</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stats-card">
            <i class="bi-x-circle text-danger"></i>
            <h3><?php echo $rejectedCount; ?></h3>
            <p>Rejected</p>
        </div>
    </div>
</div>

<?php if (count($reports) > 0): ?>
<div class="row">
    <?php foreach ($reports as $report): ?>
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="module-card h-100">
            <?php if ($report['photo_path']): ?>
            <img src="<?php echo URL_ROOT; ?>/uploads/reports/<?php echo htmlspecialchars($report['photo_path']); ?>" class="img-fluid" style="height: 180px; width: 100%; object-fit: cover;" alt="Report photo">
            <?php else: ?>
            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                <i class="bi-image text-muted" style="font-size: 48px;"></i>
            </div>
            <?php endif; ?>
            <div class="module-card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <small class="text-muted"><?php echo date('M d, Y', strtotime($report['created_at'])); ?></small>
                    <span class="status-badge status-<?php echo $report['status']; ?>">
                        <?php echo ucfirst($report['status']); ?>
                    </span>
                </div>
                <h6><i class="bi-geo-alt me-1"></i><?php echo htmlspecialchars(substr($report['location_description'], 0, 40)); ?>...</h6>
                <p class="text-muted small"><?php echo htmlspecialchars(substr($report['description'], 0, 80)); ?>...</p>
                <p class="small"><strong>Reporter:</strong> <?php echo htmlspecialchars($report['reporter_name'] ?? 'Unknown'); ?></p>
                
                <?php if ($report['status'] === 'pending'): ?>
                <div class="d-flex gap-2 mt-3">
                    <a href="<?php echo URL_ROOT; ?>/admin/reports?resolve=<?php echo $report['id']; ?>" class="btn btn-sm btn-success flex-fill">
                        <i class="bi-check"></i> Resolve
                    </a>
                    <a href="<?php echo URL_ROOT; ?>/admin/reports?reject=<?php echo $report['id']; ?>" class="btn btn-sm btn-danger flex-fill">
                        <i class="bi-x"></i> Reject
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="text-center py-5">
    <i class="bi-flag" style="font-size: 60px; color: #ccc;"></i>
    <p class="text-muted mt-3">No reports found.</p>
</div>
<?php endif; ?>

<div class="mt-4">
    <a href="<?php echo URL_ROOT; ?>/admin" class="btn btn-outline-secondary"><i class="bi-arrow-left me-2"></i>Back to Dashboard</a>
</div>
