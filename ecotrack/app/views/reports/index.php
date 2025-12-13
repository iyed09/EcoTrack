<div class="dashboard-header">
    <div class="container">
        <h2><i class="bi-flag me-2"></i>Report Improper Trash Disposal</h2>
        <p>Help keep our community clean by reporting improper trash disposal</p>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <i class="bi-flag"></i>
                    <h3><?php echo count($reports); ?></h3>
                    <p>Total Reports</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <i class="bi-hourglass-split"></i>
                    <h3><?php echo $pendingCount; ?></h3>
                    <p>Pending</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <i class="bi-check-circle"></i>
                    <h3><?php echo $resolvedCount; ?></h3>
                    <p>Resolved</p>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>Your Reports</h4>
            <a href="<?php echo URL_ROOT; ?>/reports/add" class="custom-btn"><i class="bi-plus-lg me-2"></i>New Report</a>
        </div>

        <?php if (count($reports) > 0): ?>
        <div class="row">
            <?php foreach ($reports as $report): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="module-card">
                    <?php if ($report['photo_path']): ?>
                    <img src="<?php echo URL_ROOT; ?>/uploads/reports/<?php echo htmlspecialchars($report['photo_path']); ?>" class="img-fluid" style="height: 200px; width: 100%; object-fit: cover;" alt="Report photo">
                    <?php else: ?>
                    <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
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
                        <h6><i class="bi-geo-alt me-1"></i><?php echo htmlspecialchars(substr($report['location_description'], 0, 50)); ?>...</h6>
                        <p class="text-muted small mb-0"><?php echo htmlspecialchars(substr($report['description'], 0, 100)); ?>...</p>
                        
                        <?php if ($report['admin_response']): ?>
                        <div class="mt-3 pt-2 border-top">
                            <small class="text-success fw-bold"><i class="bi-reply-fill me-1"></i>Admin Response:</small>
                            <p class="small text-muted mb-0 mt-1 fst-italic">"<?php echo htmlspecialchars($report['admin_response']); ?>"</p>
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
            <p class="text-muted mt-3">No reports yet. Help keep your community clean!</p>
            <a href="<?php echo URL_ROOT; ?>/reports/add" class="custom-btn">Submit Your First Report</a>
        </div>
        <?php endif; ?>
    </div>
</section>
