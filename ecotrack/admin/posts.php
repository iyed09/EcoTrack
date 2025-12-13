<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$pageTitle = 'Posts';
include 'includes/admin_header.php';
?>

<div class="page-header mb-4">
    <h4><i class="bx bx-news me-2"></i>Posts Management</h4>
    <p class="mb-0">Manage blog posts and announcements</p>
</div>

<div class="card">
    <div class="card-body text-center py-5">
        <i class="bx bx-news text-muted" style="font-size: 5rem;"></i>
        <h4 class="mt-3 text-muted">Coming Soon</h4>
        <p class="text-muted">Posts management functionality will be available in a future update.</p>
        <a href="index.php" class="btn btn-primary mt-3">
            <i class="bx bx-arrow-back me-1"></i> Back to Dashboard
        </a>
    </div>
</div>

<?php include 'includes/admin_footer.php'; ?>
