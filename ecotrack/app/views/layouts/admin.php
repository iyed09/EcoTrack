<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Admin - <?php echo SITE_NAME; ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700&family=Open+Sans&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="<?php echo URL_ROOT; ?>/css/style.css" rel="stylesheet">
    <link href="<?php echo URL_ROOT; ?>/css/admin.css" rel="stylesheet">
</head>
<body>
    <div class="admin-wrapper">
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <a href="<?php echo URL_ROOT; ?>/admin" class="sidebar-brand">
                    <i class="bi-globe-americas"></i>
                    <span>EcoTrack</span>
                </a>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="<?php echo URL_ROOT; ?>/admin" class="nav-link"><i class="bx bx-home-circle"></i> Dashboard</a></li>
                    <li><a href="<?php echo URL_ROOT; ?>/admin/users" class="nav-link"><i class="bx bx-user"></i> Users</a></li>
                    <li><a href="<?php echo URL_ROOT; ?>/admin/reports" class="nav-link"><i class="bx bx-flag"></i> Reports</a></li>
                    <li><a href="<?php echo URL_ROOT; ?>/admin/statistics" class="nav-link"><i class="bx bx-bar-chart-alt-2"></i> Statistics</a></li>
                    <li><a href="<?php echo URL_ROOT; ?>/dashboard" class="nav-link"><i class="bx bx-arrow-back"></i> Back to Site</a></li>
                </ul>
            </nav>
        </aside>
        <main class="admin-main">
            <header class="admin-header">
                <h4><?php echo $pageTitle ?? 'Admin'; ?></h4>
                <div class="header-actions">
                    <span class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></span>
                    <a href="<?php echo URL_ROOT; ?>/auth/logout" class="btn btn-sm btn-outline-danger ms-3">Logout</a>
                </div>
            </header>
            <div class="admin-content">
                <?php echo $content; ?>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
