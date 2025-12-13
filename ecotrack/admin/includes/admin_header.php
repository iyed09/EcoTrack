<?php
// Admin Header with Sneat Bootstrap Template
// This file should be included at the top of all admin pages
?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="../assets/" data-template="vertical-menu-template-free">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>EcoTrack Admin</title>
    <meta name="description" content="EcoTrack Admin Dashboard" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo ROOT_PATH; ?>/favicon.svg" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" />
    
    <!-- Sneat Theme CSS -->
    <link rel="stylesheet" href="<?php echo ROOT_PATH; ?>/admin/assets/css/sneat-theme.css" />
    
    <!-- Page CSS -->
    <style>
        :root {
            --bs-primary: #696cff;
            --bs-primary-rgb: 105, 108, 255;
        }
    </style>
</head>
<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            
            <!-- Menu -->
            <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
                <div class="app-brand demo">
                    <a href="<?php echo ROOT_PATH; ?>/admin/index.php" class="app-brand-link">
                        <span class="app-brand-logo demo">
                            <i class="bi-globe-americas text-primary" style="font-size: 28px;"></i>
                        </span>
                        <span class="app-brand-text demo menu-text fw-bolder ms-2">EcoTrack</span>
                    </a>
                    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
                        <i class="bx bx-chevron-left bx-sm align-middle"></i>
                    </a>
                </div>

                <div class="menu-inner-shadow"></div>

                <ul class="menu-inner py-1">
                    <!-- Dashboard -->
                    <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                        <a href="<?php echo ROOT_PATH; ?>/admin/index.php" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-home-circle"></i>
                            <div data-i18n="Dashboard">Dashboard</div>
                        </a>
                    </li>

                    <!-- Admin Section Header -->
                    <li class="menu-header small text-uppercase">
                        <span class="menu-header-text">Management</span>
                    </li>

                    <!-- Users -->
                    <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                        <a href="<?php echo ROOT_PATH; ?>/admin/users.php" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-user"></i>
                            <div data-i18n="Users">Users</div>
                        </a>
                    </li>

                    <!-- Reports -->
                    <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                        <a href="<?php echo ROOT_PATH; ?>/admin/reports.php" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-flag"></i>
                            <div data-i18n="Reports">Reports</div>
                        </a>
                    </li>

                    <!-- Statistics -->
                    <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'statistics.php' ? 'active' : ''; ?>">
                        <a href="<?php echo ROOT_PATH; ?>/admin/statistics.php" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-bar-chart-alt-2"></i>
                            <div data-i18n="Statistics">Statistics</div>
                        </a>
                    </li>

                    <!-- Posts -->
                    <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'posts.php' ? 'active' : ''; ?>">
                        <a href="<?php echo ROOT_PATH; ?>/admin/posts.php" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-news"></i>
                            <div data-i18n="Posts">Posts</div>
                        </a>
                    </li>

                    <!-- Score -->
                    <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'score.php' ? 'active' : ''; ?>">
                        <a href="<?php echo ROOT_PATH; ?>/admin/score.php" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-trophy"></i>
                            <div data-i18n="Score">Score</div>
                        </a>
                    </li>

                    <!-- Gestion Produits & Déchets -->
                    <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'products-waste.php' ? 'active' : ''; ?>">
                        <a href="<?php echo ROOT_PATH; ?>/admin/products-waste.php" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-package"></i>
                            <div data-i18n="Gestion Produits">Gestion Produits & Déchets</div>
                        </a>
                    </li>

                    <!-- Misc Section -->
                    <li class="menu-header small text-uppercase"><span class="menu-header-text">Navigation</span></li>

                    <!-- Back to Site -->
                    <li class="menu-item">
                        <a href="<?php echo ROOT_PATH; ?>/index.php" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-globe"></i>
                            <div data-i18n="Back to Site">Back to Site</div>
                        </a>
                    </li>

                    <!-- User Dashboard -->
                    <li class="menu-item">
                        <a href="<?php echo ROOT_PATH; ?>/dashboard.php" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-tachometer"></i>
                            <div data-i18n="User Dashboard">User Dashboard</div>
                        </a>
                    </li>
                </ul>
            </aside>
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                
                <!-- Navbar -->
                <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
                    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
                        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                            <i class="bx bx-menu bx-sm"></i>
                        </a>
                    </div>

                    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
                        <!-- Search -->
                        <div class="navbar-nav align-items-center">
                            <div class="nav-item d-flex align-items-center position-relative">
                                <i class="bx bx-search fs-4 lh-0"></i>
                                <input type="text" class="form-control border-0 shadow-none" id="globalSearch" placeholder="Search users, reports, pages..." aria-label="Search..." autocomplete="off" />
                                <div id="searchResults" class="position-absolute bg-white rounded shadow-lg" style="display: none; top: 100%; left: 0; right: 0; min-width: 350px; max-height: 400px; overflow-y: auto; z-index: 1050;">
                                </div>
                            </div>
                        </div>
                        <!-- /Search -->

                        <ul class="navbar-nav flex-row align-items-center ms-auto">
                            <!-- User -->
                            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                                    <div class="avatar avatar-online">
                                        <span class="avatar-initial rounded-circle bg-label-primary">
                                            <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1)); ?>
                                        </span>
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="#">
                                            <div class="d-flex">
                                                <div class="flex-shrink-0 me-3">
                                                    <div class="avatar avatar-online">
                                                        <span class="avatar-initial rounded-circle bg-label-primary">
                                                            <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1)); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <span class="fw-semibold d-block"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></span>
                                                    <small class="text-muted">Administrator</small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider"></div>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?php echo ROOT_PATH; ?>/profile.php">
                                            <i class="bx bx-user me-2"></i>
                                            <span class="align-middle">My Profile</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?php echo ROOT_PATH; ?>/admin/index.php">
                                            <i class="bx bx-cog me-2"></i>
                                            <span class="align-middle">Settings</span>
                                        </a>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider"></div>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?php echo ROOT_PATH; ?>/modules/auth/logout.php">
                                            <i class="bx bx-power-off me-2"></i>
                                            <span class="align-middle">Log Out</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <!--/ User -->
                        </ul>
                    </div>
                </nav>
                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">
