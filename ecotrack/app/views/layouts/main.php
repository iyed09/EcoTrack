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
    <link href="<?php echo URL_ROOT; ?>/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg fixed-top" id="mainNav">
        <div class="container">
            <a class="navbar-brand" href="<?php echo URL_ROOT; ?>/">
                <i class="bi-globe-americas"></i>
                <span>EcoTrack</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo URL_ROOT; ?>/">Home</a>
                    </li>
                    <?php if (isset($isLoggedIn) && $isLoggedIn): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo URL_ROOT; ?>/dashboard">Dashboard</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Modules</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo URL_ROOT; ?>/energy"><i class="bi-lightning-charge me-2"></i>Energy</a></li>
                            <li><a class="dropdown-item" href="<?php echo URL_ROOT; ?>/transport"><i class="bi-car-front me-2"></i>Transport</a></li>
                            <li><a class="dropdown-item" href="<?php echo URL_ROOT; ?>/waste"><i class="bi-trash me-2"></i>Waste</a></li>
                            <li><a class="dropdown-item" href="<?php echo URL_ROOT; ?>/reports"><i class="bi-flag me-2"></i>Report Trash</a></li>
                            <li><a class="dropdown-item" href="<?php echo URL_ROOT; ?>/reports/radar"><i class="bi-broadcast me-2"></i>Eco-Radar</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle rewards-nav" href="#" data-bs-toggle="dropdown">
                            <i class="bi-star-fill me-1"></i>Rewards
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo URL_ROOT; ?>/rewards"><i class="bi-speedometer2 me-2"></i>My Points</a></li>
                            <li><a class="dropdown-item" href="<?php echo URL_ROOT; ?>/rewards/leaderboard"><i class="bi-bar-chart-fill me-2"></i>Leaderboard</a></li>
                            <li><a class="dropdown-item" href="<?php echo URL_ROOT; ?>/rewards/achievements"><i class="bi-award me-2"></i>Achievements</a></li>
                        </ul>
                    </li>
                    <?php if (isset($isAdmin) && $isAdmin): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo URL_ROOT; ?>/admin">Admin</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi-person-circle me-1"></i><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo URL_ROOT; ?>/profile"><i class="bi-person me-2"></i>Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo URL_ROOT; ?>/auth/logout"><i class="bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo URL_ROOT; ?>/auth/login">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link custom-btn custom-btn-outline ms-2" href="<?php echo URL_ROOT; ?>/auth/register">Sign Up</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <?php echo $content; ?>

    <footer class="site-footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-12 mb-4">
                    <a class="navbar-brand" href="<?php echo URL_ROOT; ?>/">
                        <i class="bi-globe-americas"></i>
                        <span>EcoTrack</span>
                    </a>
                    <p class="text-white mt-3"><?php echo SITE_SLOGAN; ?></p>
                </div>
                <div class="col-lg-2 col-6 mb-4">
                    <h6 class="site-footer-title">Quick Links</h6>
                    <ul class="site-footer-links">
                        <li><a href="<?php echo URL_ROOT; ?>/" class="site-footer-link">Home</a></li>
                        <li><a href="<?php echo URL_ROOT; ?>/dashboard" class="site-footer-link">Dashboard</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-6 mb-4">
                    <h6 class="site-footer-title">Modules</h6>
                    <ul class="site-footer-links">
                        <li><a href="<?php echo URL_ROOT; ?>/energy" class="site-footer-link">Energy</a></li>
                        <li><a href="<?php echo URL_ROOT; ?>/transport" class="site-footer-link">Transport</a></li>
                        <li><a href="<?php echo URL_ROOT; ?>/waste" class="site-footer-link">Waste</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 col-12">
                    <h6 class="site-footer-title">Contact</h6>
                    <p class="text-white-50">contact@ecotrack.com</p>
                    <p class="text-white-50">Tunis, Tunisia</p>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <p class="copyright-text">&copy; <?php echo date('Y'); ?> EcoTrack. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo URL_ROOT; ?>/js/main.js"></script>
</body>
</html>
