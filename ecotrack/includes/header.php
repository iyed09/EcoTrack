<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700&family=Open+Sans&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo ROOT_PATH; ?>/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg fixed-top" id="mainNav">
        <div class="container">
            <a class="navbar-brand" href="<?php echo ROOT_PATH; ?>/index.php">
                <i class="bi-globe-americas"></i>
                <span>EcoTrack</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo ROOT_PATH; ?>/index.php">Home</a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo ROOT_PATH; ?>/dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Modules</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo ROOT_PATH; ?>/modules/energy/index.php"><i class="bi-lightning-charge me-2"></i>Energy</a></li>
                            <li><a class="dropdown-item" href="<?php echo ROOT_PATH; ?>/modules/transport/index.php"><i class="bi-car-front me-2"></i>Transport</a></li>
                            <li><a class="dropdown-item" href="<?php echo ROOT_PATH; ?>/modules/waste/index.php"><i class="bi-trash me-2"></i>Waste</a></li>
                            <li><a class="dropdown-item" href="<?php echo ROOT_PATH; ?>/modules/reports/index.php"><i class="bi-flag me-2"></i>Report Trash</a></li>
                            <li><a class="dropdown-item" href="<?php echo ROOT_PATH; ?>/modules/reports/radar.php"><i class="bi-broadcast me-2"></i>Eco-Radar</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle rewards-nav" href="#" data-bs-toggle="dropdown">
                            <i class="bi-star-fill me-1"></i>Rewards
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo ROOT_PATH; ?>/modules/rewards/index.php"><i class="bi-speedometer2 me-2"></i>My Points</a></li>
                            <li><a class="dropdown-item" href="<?php echo ROOT_PATH; ?>/modules/rewards/leaderboard.php"><i class="bi-bar-chart-fill me-2"></i>Leaderboard</a></li>
                            <li><a class="dropdown-item" href="<?php echo ROOT_PATH; ?>/modules/rewards/achievements.php"><i class="bi-award me-2"></i>Achievements</a></li>
                        </ul>
                    </li>
                    <?php if (isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo ROOT_PATH; ?>/admin/index.php">Admin</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi-person-circle me-1"></i><?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo ROOT_PATH; ?>/profile.php"><i class="bi-person me-2"></i>Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo ROOT_PATH; ?>/modules/auth/logout.php"><i class="bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo ROOT_PATH; ?>/modules/auth/login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link custom-btn custom-btn-outline ms-2" href="<?php echo ROOT_PATH; ?>/modules/auth/register.php">Sign Up</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
