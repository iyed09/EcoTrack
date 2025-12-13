<?php
// Inclure les contrôleurs nécessaires
include __DIR__ . "/../../Controller/DechetController.php";
require_once __DIR__ . "/../../Database.php";  

try {
    $conn = Database::getConnexion();   
} catch (Exception $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

// Récupération des paramètres
$action = $_GET['action'] ?? 'dashboard-dechets';
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$message = null;
$alertClass = "success";

// Traitement du formulaire d'ajout/modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'type' => trim($_POST['type'] ?? ''),
        'poids' => floatval($_POST['poids'] ?? 0),
        'recyclable' => isset($_POST['recyclable']) ? 1 : 0,
        'idUser' => isset($_POST['idUser']) && $_POST['idUser'] !== '' ? (int)$_POST['idUser'] : null,
        'idProduit' => isset($_POST['idProduit']) && $_POST['idProduit'] !== '' ? (int)$_POST['idProduit'] : null,
    ];
    
    if ($action === 'add-dechet') {
        if (method_exists('DechetController', 'addDechet')) {
            $res = DechetController::addDechet($conn, $data);
            
            if ($res['ok']) {
                header("Location: ../../../public/index.php?action=dashboard-dechets&msg=" . urlencode($res['msg']) . "&alertClass=success");
                exit;
            } else {
                $message = $res['msg'];
                $alertClass = "error";
            }
        } else {
            $message = "Méthode addDechet non disponible dans le contrôleur";
            $alertClass = "error";
        }
    }

    if ($action === 'edit-dechet' && $id) {
        if (method_exists('DechetController', 'editDechet')) {
            $res = DechetController::editDechet($conn, $id, $data);
            
            if ($res['ok']) {
                header("Location: ../../../public/index.php?action=dashboard-dechets&msg=" . urlencode($res['msg']) . "&alertClass=success");
                exit;
            } else {
                $message = $res['msg'];
                $alertClass = "error";
            }
        } else {
            $message = "Méthode editDechet non disponible dans le contrôleur";
            $alertClass = "error";
        }
    }
}

// Suppression
if ($action === 'delete-dechet' && $id) {
    if (method_exists('DechetController', 'removeDechet')) {
        $res = DechetController::removeDechet($conn, $id);
        $message = $res['msg'];
        $alertClass = $res['ok'] ? 'success' : 'error';
        header("Location: ../../../public/index.php?action=dashboard-dechets&msg=" . urlencode($message) . "&alertClass=" . $alertClass);
        exit;
    } else {
        $message = "Méthode removeDechet non disponible dans le contrôleur";
        $alertClass = "error";
    }
}

// Récupération des données pour l'affichage
try {
    // Récupérer la liste des déchets
    if (method_exists('DechetController', 'listDechets')) {
        $dechets = DechetController::listDechets($conn);
    } else {
        $dechets = [];
        $message = "Méthode listDechets non disponible";
        $alertClass = "error";
    }
    
    // Calculer les statistiques manuellement
    $stats = [
        'total_dechets' => 0,
        'total_poids' => 0,
        'recyclables' => 0,
        'non_recyclables' => 0
    ];
    
    foreach ($dechets as $dechet) {
        $stats['total_dechets']++;
        $stats['total_poids'] += floatval($dechet['poids'] ?? 0);
        
        if (isset($dechet['recyclable']) && ($dechet['recyclable'] == 1 || $dechet['recyclable'] === true || $dechet['recyclable'] == '1')) {
            $stats['recyclables']++;
        } else {
            $stats['non_recyclables']++;
        }
    }
    
} catch (Exception $e) {
    $dechets = [];
    $stats = [
        'total_dechets' => 0,
        'total_poids' => 0,
        'recyclables' => 0,
        'non_recyclables' => 0
    ];
    $message = "Erreur lors de la récupération des données: " . $e->getMessage();
    $alertClass = "error";
}

// Calcul du taux de recyclage
$tauxRecyclage = 0;
if (isset($stats['total_dechets']) && $stats['total_dechets'] > 0) {
    $tauxRecyclage = ($stats['recyclables'] / $stats['total_dechets']) * 100;
}

// Récupération du message depuis l'URL
if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
    $alertClass = $_GET['alertClass'] ?? 'success';
}

// Préparer les données pour les graphiques
$typesDechets = [];
$poidsParType = [];
$recyclableParType = [];

foreach ($dechets as $dechet) {
    $type = $dechet['type'] ?? 'Non spécifié';
    
    if (!isset($typesDechets[$type])) {
        $typesDechets[$type] = 0;
        $poidsParType[$type] = 0;
        $recyclableParType[$type] = 0;
    }
    
    $typesDechets[$type]++;
    $poidsParType[$type] += floatval($dechet['poids'] ?? 0);
    
    if (isset($dechet['recyclable']) && ($dechet['recyclable'] == 1 || $dechet['recyclable'] === true || $dechet['recyclable'] == '1')) {
        $recyclableParType[$type]++;
    }
}

// Si aucun type, ajouter un type par défaut
if (empty($typesDechets)) {
    $typesDechets = ['Aucun déchet' => 0];
    $poidsParType = ['Aucun déchet' => 0];
    $recyclableParType = ['Aucun déchet' => 0];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Déchets - EcoTrack</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/style.css">
    <style>
        :root {
            --primary-color: #10b981;
            --primary-dark: #059669;
            --secondary-color: #3b82f6;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --success-color: #22c55e;
            --dark-color: #1f2937;
            --light-color: #f9fafb;
            --gray-light: #e5e7eb;
            --gray-medium: #9ca3af;
            --gray-dark: #4b5563;
            --sidebar-width: 260px;
            --border-radius: 12px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.12);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f6f7f9 0%, #eef2f7 100%);
            color: var(--dark-color);
            line-height: 1.6;
            min-height: 100vh;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--dark-color) 0%, #111827 100%);
            color: white;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            z-index: 1000;
            box-shadow: var(--shadow-lg);
            transition: var(--transition);
        }

        .sidebar-header {
            padding: 24px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 24px;
            font-weight: 700;
            color: white;
        }

        .logo i {
            color: var(--primary-color);
            font-size: 28px;
        }

        .sidebar-subtitle {
            font-size: 12px;
            color: var(--gray-medium);
            margin-top: 4px;
            letter-spacing: 1px;
        }

        .sidebar-nav {
            flex: 1;
            padding: 24px 0;
        }

        .sidebar-nav ul {
            list-style: none;
        }

        .nav-item {
            margin: 4px 16px;
            border-radius: var(--border-radius);
            overflow: hidden;
            transition: var(--transition);
        }

        .nav-item.active {
            background: rgba(16, 185, 129, 0.15);
            border-left: 4px solid var(--primary-color);
        }

        .nav-item:hover:not(.active) {
            background: rgba(255,255,255,0.05);
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 20px;
            color: white;
            text-decoration: none;
            transition: var(--transition);
        }

        .nav-link i {
            width: 20px;
            text-align: center;
            font-size: 18px;
        }

        .nav-item.active .nav-link {
            color: var(--primary-color);
        }

        .sidebar-footer {
            padding: 24px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: rgba(16, 185, 129, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--primary-color);
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
        }

        .user-role {
            font-size: 12px;
            color: var(--gray-medium);
        }

        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 24px;
            min-height: 100vh;
        }

        .content-header {
            background: white;
            border-radius: var(--border-radius);
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: var(--shadow-md);
            animation: slideDown 0.5s ease-out;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-left h1 {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 8px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header-left p {
            color: var(--gray-dark);
            font-size: 14px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: var(--border-radius);
            margin-bottom: 16px;
            border-left: 4px solid;
            animation: fadeIn 0.5s ease-out;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background-color: rgba(34, 197, 94, 0.1);
            border-color: var(--success-color);
            color: #166534;
        }

        .alert-error {
            background-color: rgba(239, 68, 68, 0.1);
            border-color: var(--danger-color);
            color: #991b1b;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark-color);
        }

        .stat-label {
            color: var(--gray-dark);
            font-size: 14px;
            margin-top: 8px;
        }

        .ecoTabs {
            background: white;
            border-radius: var(--border-radius);
            margin-bottom: 24px;
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }

        .tab-nav {
            display: flex;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-bottom: 1px solid var(--gray-light);
        }

        .tab-btn {
            padding: 16px 24px;
            background: none;
            border: none;
            font-weight: 500;
            color: var(--gray-dark);
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tab-btn i {
            font-size: 14px;
        }

        .tab-btn.active {
            color: var(--primary-color);
            background: white;
            border-bottom: 3px solid var(--primary-color);
        }

        .tab-btn:hover:not(.active) {
            background: rgba(16, 185, 129, 0.05);
        }

        .tab-content {
            display: none;
            padding: 24px;
            animation: fadeIn 0.3s ease-out;
        }

        .tab-content.active {
            display: block;
        }

        .impact-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: var(--shadow-sm);
            border-left: 4px solid var(--primary-color);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .card-title {
            font-weight: 600;
            color: var(--dark-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .impact-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .high-impact {
            background: #fee2e2;
            color: #dc2626;
        }

        .medium-impact {
            background: #fef3c7;
            color: #d97706;
        }

        .low-impact {
            background: #d1fae5;
            color: #059669;
        }

        .chart-container {
            background: white;
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--shadow-sm);
        }

        .modern-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        .modern-table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: var(--gray-dark);
            border-bottom: 2px solid var(--gray-light);
        }

        .modern-table td {
            padding: 12px;
            border-bottom: 1px solid var(--gray-light);
        }

        .modern-table tr:hover {
            background: #f9fafb;
        }

        .btn-action {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 4px;
            text-decoration: none;
        }

        .btn-warning {
            background: #fbbf24;
            color: #78350f;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-warning:hover {
            background: #f59e0b;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #047857 100%);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 1024px) {
            .sidebar {
                width: 80px;
            }
            
            .sidebar .logo span,
            .sidebar-subtitle,
            .nav-link span,
            .user-details {
                display: none;
            }
            
            .main-content {
                margin-left: 80px;
            }
        }

        @media (max-width: 768px) {
            .tab-nav {
                flex-wrap: wrap;
            }
            
            .tab-btn {
                flex: 1;
                min-width: 120px;
                justify-content: center;
            }
            
            .content-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }

        .recyclable-yes {
            color: #059669;
            font-weight: 600;
        }

        .recyclable-no {
            color: #dc2626;
            font-weight: 600;
        }
        
        .text-center {
            text-align: center;
        }
        
        .py-4 {
            padding-top: 16px;
            padding-bottom: 16px;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: var(--gray-medium);
            font-style: italic;
        }
        
        .btn-group {
            display: flex;
            gap: 5px;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-leaf"></i>
                    <span>EcoTrack</span>
                </div>
                <div class="sidebar-subtitle">Gestion Déchets</div>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li class="nav-item">
                        <a href="../../../public/index.php?action=dashboard-produits" class="nav-link">
                            <i class="fas fa-industry"></i>
                            <span>Dashboard Produits</span>
                        </a>
                    </li>
                    <li class="nav-item active">
                        <a href="../../../public/index.php?action=dashboard-dechets" class="nav-link">
                            <i class="fas fa-trash"></i>
                            <span>Dashboard Déchets</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../../../public/index.php?action=list-dechets" class="nav-link">
                            <i class="fas fa-eye"></i>
                            <span>Front Office</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../../../public/index.php?action=add-dechet" class="nav-link">
                            <i class="fas fa-plus-circle"></i>
                            <span>Ajouter Déchet</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-details">
                        <span class="user-name">Admin</span>
                        <span class="user-role">Gestion Déchets</span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="content-header">
                <div class="header-left">
                    <h1><i class="fas fa-trash"></i> Dashboard Gestion des Déchets</h1>
                    <p>Analyse et gestion complète des déchets - Suivi écologique et optimisation</p>
                </div>
                <div class="header-actions">
                    <a href="../../../public/index.php?action=add-dechet" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Ajouter un Déchet
                    </a>
                </div>
            </header>

            <?php if ($message): ?>
            <div class="alert alert-<?= $alertClass === 'error' ? 'error' : 'success' ?>">
                <i class="fas <?= $alertClass === 'error' ? 'fa-exclamation-triangle' : 'fa-check-circle' ?>"></i>
                <?= htmlspecialchars($message) ?>
            </div>
            <?php endif; ?>

            <!-- Statistiques -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?= $stats['total_dechets'] ?? 0 ?></div>
                    <div class="stat-label">Total Déchets</div>
                    <div class="progress-bar-container">
                        <div class="progress-bar" style="width: 100%; background: var(--primary-color);"></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value"><?= number_format($stats['total_poids'] ?? 0, 2) ?> <small>kg</small></div>
                    <div class="stat-label">Poids Total</div>
                    <div class="progress-bar-container">
                        <div class="progress-bar" style="width: <?= min(($stats['total_poids'] ?? 0) / 10, 100) ?>%; background: var(--warning-color);"></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value"><?= $stats['recyclables'] ?? 0 ?></div>
                    <div class="stat-label">Déchets Recyclables</div>
                    <div class="progress-bar-container">
                        <div class="progress-bar" style="width: <?= ($stats['total_dechets'] ?? 0) > 0 ? ($stats['recyclables'] / $stats['total_dechets']) * 100 : 0 ?>%; background: var(--success-color);"></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value"><?= number_format($tauxRecyclage, 1) ?>%</div>
                    <div class="stat-label">Taux de Recyclage</div>
                    <div class="progress-bar-container">
                        <div class="progress-bar" style="width: <?= $tauxRecyclage ?>%; background: linear-gradient(90deg, var(--success-color) 0%, #059669 100%);"></div>
                    </div>
                </div>
            </div>

            <!-- Tabs d'Analyse -->
            <div class="ecoTabs">
                <div class="tab-nav">
                    <button class="tab-btn active" data-tab="gestion">
                        <i class="fas fa-list"></i>
                        Gestion Déchets
                    </button>
                    <button class="tab-btn" data-tab="analytics">
                        <i class="fas fa-chart-bar"></i>
                        Analytics
                    </button>
                    <button class="tab-btn" data-tab="recyclage">
                        <i class="fas fa-recycle"></i>
                        Analyse Recyclage
                    </button>
                </div>

                <!-- Onglet 1: Gestion Déchets -->
                <div class="tab-content active" id="gestion-tab">
                    <div class="impact-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-trash"></i>
                                Liste des Déchets
                            </h3>
                            <span class="impact-badge <?= count($dechets) > 10 ? 'high-impact' : (count($dechets) > 0 ? 'medium-impact' : 'low-impact') ?>">
                                <?= count($dechets) ?> Déchet<?= count($dechets) > 1 ? 's' : '' ?>
                            </span>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th>ID Dechet</th>
                                        <th>Type</th>
                                        <th>Poids (kg)</th>
                                        <th>Recyclable</th>
                                        <th>ID User</th>
                                        <th>ID Produit</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($dechets)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <div class="no-data">
                                                    <i class="fas fa-database fa-2x mb-3"></i><br>
                                                    Aucun déchet trouvé dans la base de données
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($dechets as $dechet): 
                                            // Utiliser les bonnes conventions de nommage
                                            $idDechet = $dechet['idDechet'] ?? $dechet['iddechet'] ?? 'N/A';
                                            $type = $dechet['type'] ?? 'Non spécifié';
                                            $poids = $dechet['poids'] ?? 0;
                                            $recyclable = isset($dechet['recyclable']) && 
                                                         ($dechet['recyclable'] == 1 || 
                                                          $dechet['recyclable'] === true || 
                                                          $dechet['recyclable'] == '1');
                                            $idUser = $dechet['idUser'] ?? $dechet['iduser'] ?? 'N/A';
                                            $idProduit = $dechet['idProduit'] ?? $dechet['idproduit'] ?? 'Non associé';
                                        ?>
                                        <tr>
                                            <td><strong>#<?= htmlspecialchars($idDechet) ?></strong></td>
                                            <td><?= htmlspecialchars($type) ?></td>
                                            <td><?= number_format($poids, 2) ?></td>
                                            <td>
                                                <span class="<?= $recyclable ? 'recyclable-yes' : 'recyclable-no' ?>">
                                                    <i class="fas fa-<?= $recyclable ? 'check' : 'times' ?>"></i>
                                                    <?= $recyclable ? 'Oui' : 'Non' ?>
                                                </span>
                                            </td>
                                            <td>#<?= htmlspecialchars($idUser) ?></td>
                                            <td>
                                                <?php if ($idProduit != 'Non associé'): ?>
                                                    <span class="badge bg-info">#<?= htmlspecialchars($idProduit) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted"><?= htmlspecialchars($idProduit) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="../../../public/index.php?action=edit-dechet&id=<?= $idDechet ?>" 
                                                       class="btn-action btn-warning" title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="../../../public/index.php?action=delete-dechet&id=<?= $idDechet ?>" 
                                                       class="btn-action btn-danger" 
                                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce déchet ?')" title="Supprimer">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Onglet 2: Analytics -->
                <div class="tab-content" id="analytics-tab">
                    <div class="chart-container">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line"></i>
                            Évolution des Déchets
                        </h3>
                        <canvas id="evolutionChart" height="200"></canvas>
                    </div>

                    <div class="impact-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-balance-scale"></i>
                                Répartition par Type
                            </h3>
                            <span class="impact-badge medium-impact"><?= number_format($stats['total_poids'] ?? 0, 2) ?> kg total</span>
                        </div>
                        <canvas id="typesChart" height="150"></canvas>
                    </div>
                </div>

                <!-- Onglet 3: Analyse Recyclage -->
                <div class="tab-content" id="recyclage-tab">
                    <div class="chart-container">
                        <h3 class="card-title">
                            <i class="fas fa-recycle"></i>
                            Taux de Recyclage
                        </h3>
                        <div class="text-center py-4">
                            <div class="taux-recyclage-display" style="font-size: 48px; font-weight: 700; color: <?= $tauxRecyclage > 50 ? 'var(--success-color)' : 'var(--danger-color)' ?>;">
                                <?= number_format($tauxRecyclage, 1) ?>%
                            </div>
                            <p class="text-muted mt-2">des déchets sont recyclables</p>
                        </div>
                    </div>

                    <div class="impact-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-exclamation-triangle"></i>
                                Points d'Amélioration
                            </h3>
                            <span class="impact-badge <?= ($stats['non_recyclables'] ?? 0) > 5 ? 'high-impact' : 'low-impact' ?>">
                                <?= $stats['non_recyclables'] ?? 0 ?> non recyclable<?= ($stats['non_recyclables'] ?? 0) > 1 ? 's' : '' ?>
                            </span>
                        </div>
                        
                        <div class="stats-summary">
                            <div class="row-stats">
                                <div class="stat-item">
                                    <div class="stat-number" style="color: var(--success-color);"><?= $stats['recyclables'] ?? 0 ?></div>
                                    <div class="stat-label-small">Recyclables</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number" style="color: var(--danger-color);"><?= $stats['non_recyclables'] ?? 0 ?></div>
                                    <div class="stat-label-small">Non recyclables</div>
                                </div>
                            </div>
                            
                            <div class="recommendations mt-4">
                                <h4><i class="fas fa-lightbulb text-warning"></i> Recommandations :</h4>
                                <ul class="recommendation-list">
                                    <li><i class="fas fa-check-circle text-success"></i> Augmenter la collecte sélective</li>
                                    <li><i class="fas fa-check-circle text-success"></i> Sensibiliser au tri des déchets</li>
                                    <li><i class="fas fa-check-circle text-success"></i> Promouvoir les emballages recyclables</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alertes et Notifications -->
            <div class="impact-card mt-4">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bell" style="color: var(--warning-color);"></i>
                        Alertes Déchets
                    </h3>
                    <span class="impact-badge <?= ($stats['non_recyclables'] ?? 0) > 5 ? 'high-impact' : 'low-impact' ?>">
                        <?= ($stats['non_recyclables'] ?? 0) > 0 ? ($stats['non_recyclables'] ?? 0) . ' alerte' . (($stats['non_recyclables'] ?? 0) > 1 ? 's' : '') : 'Aucune alerte' ?>
                    </span>
                </div>
                
                <div class="alerts-container">
                    <?php if (($stats['non_recyclables'] ?? 0) > 5): ?>
                    <div class="alert-item alert-danger">
                        <div class="alert-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="alert-content">
                            <div class="alert-title">Trop de déchets non recyclables</div>
                            <div class="alert-description">
                                <?= $stats['non_recyclables'] ?? 0 ?> déchets non recyclables détectés (objectif : < 5)
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (($stats['total_poids'] ?? 0) > 100): ?>
                    <div class="alert-item alert-warning">
                        <div class="alert-icon">
                            <i class="fas fa-weight"></i>
                        </div>
                        <div class="alert-content">
                            <div class="alert-title">Poids total élevé</div>
                            <div class="alert-description">
                                <?= number_format($stats['total_poids'] ?? 0, 2) ?> kg de déchets (objectif : < 100 kg)
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($tauxRecyclage > 70): ?>
                    <div class="alert-item alert-success">
                        <div class="alert-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="alert-content">
                            <div class="alert-title">Excellent : Taux de recyclage élevé</div>
                            <div class="alert-description">
                                Taux de recyclage : <?= number_format($tauxRecyclage, 1) ?>% (objectif : > 60%)
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Initialisation des graphiques
        document.addEventListener('DOMContentLoaded', function() {
            // Données pour les graphiques (basées sur PHP)
            const types = <?= json_encode(array_keys($typesDechets)) ?>;
            const typeCounts = <?= json_encode(array_values($typesDechets)) ?>;
            const poidsData = <?= json_encode(array_values($poidsParType)) ?>;
            
            // Graphique d'évolution
            const evolutionCtx = document.getElementById('evolutionChart');
            if (evolutionCtx) {
                new Chart(evolutionCtx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun'],
                        datasets: [{
                            label: 'Déchets (kg)',
                            data: [45, 52, 48, 65, 58, 62],
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Poids (kg)'
                                }
                            }
                        }
                    }
                });
            }

            // Graphique des types de déchets
            const typesCtx = document.getElementById('typesChart');
            if (typesCtx && types.length > 0) {
                new Chart(typesCtx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: types,
                        datasets: [{
                            data: typeCounts,
                            backgroundColor: [
                                '#10b981', '#3b82f6', '#f59e0b', '#ef4444', 
                                '#8b5cf6', '#6b7280', '#ec4899', '#14b8a6'
                            ],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'right',
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = Math.round((value / total) * 100);
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Gestionnaire d'onglets
            const tabBtns = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const tabId = this.dataset.tab;
                    
                    // Retirer la classe active de tous les boutons et contenus
                    tabBtns.forEach(b => b.classList.remove('active'));
                    tabContents.forEach(c => c.classList.remove('active'));
                    
                    // Activer l'onglet cliqué
                    this.classList.add('active');
                    document.getElementById(`${tabId}-tab`).classList.add('active');
                });
            });

            // Confirmation de suppression
            document.querySelectorAll('.btn-danger').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    if (!confirm('Êtes-vous sûr de vouloir supprimer ce déchet ? Cette action est irréversible.')) {
                        e.preventDefault();
                    }
                });
            });

            // Styles pour les barres de progression
            const progressBars = document.querySelectorAll('.progress-bar-container');
            progressBars.forEach(container => {
                const bar = container.querySelector('.progress-bar');
                if (bar) {
                    const width = bar.style.width;
                    container.innerHTML = `
                        <div class="progress-bar-background" style="height: 4px; background: var(--gray-light); border-radius: 2px; margin-top: 8px;">
                            <div class="progress-bar-fill" style="width: ${width}; height: 100%; border-radius: 2px;"></div>
                        </div>
                    `;
                    container.querySelector('.progress-bar-fill').style.background = bar.style.background;
                }
            });
        });
    </script>
</body>
</html>