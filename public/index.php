<?php
require_once "C:/xampppp/htdocs/ecotrack/Database.php";
require_once "C:/xampppp/htdocs/ecotrack/Controller/ProduitController.php";
require_once "C:/xampppp/htdocs/ecotrack/Controller/DechetController.php";

$conn = Database::getConnexion();

// Initialiser les variables
$produits = [];
$dechets = [];
$produits_result = [];
$dechets_result = [];
$message = '';
$message_type = '';

// Récupérer les données
$produits = ProduitController::listProduit($conn);
$dechets = DechetController::listDechet($conn);

// Calcul des statistiques produits
$empreinteTotale = 0;
$highImpact = 0;
$produitsCount = count($produits);

foreach ($produits as $produit) {
    if (isset($produit['empreinteCarbone'])) {
        $empreinteTotale += $produit['empreinteCarbone'];
        if ($produit['empreinteCarbone'] > 10) $highImpact++;
    }
}

// Calcul des statistiques déchets
$totalPoids = 0;
$recyclableCount = 0;
$dechetsCount = count($dechets);

foreach ($dechets as $dechet) {
    $totalPoids += $dechet['poids'];
    if ($dechet['recyclable']) {
        $recyclableCount++;
    }
}

// Produits récents (5 derniers)
$produitsRecents = array_slice($produits, -5, 5, true);
$produitsRecents = array_reverse($produitsRecents);

// Déchets récents (5 derniers)
$dechetsRecents = array_slice($dechets, -5, 5, true);
$dechetsRecents = array_reverse($dechetsRecents);

// Calculer le ratio recyclabilité
$ratioRecyclable = $dechetsCount > 0 ? ($recyclableCount / $dechetsCount) * 100 : 0;

// Gestion des actions selon le paramètre GET
$action = $_GET['action'] ?? 'dashboard';
$id = $_GET['id'] ?? null;
$idDechet = $_GET['idDechet'] ?? null;

// Traitement des actions POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ajout de produit
    if (isset($_POST['add_produit'])) {
        $nom = trim($_POST['nom'] ?? '');
        $categorie = trim($_POST['categorie'] ?? '');
        $empreinteCarbone = $_POST['empreinteCarbone'] ?? '';
        
        $errors = [];
        
        if (empty($nom)) $errors[] = "Le nom du produit est obligatoire";
        if (empty($categorie)) $errors[] = "La catégorie est obligatoire";
        if (empty($empreinteCarbone) || !is_numeric($empreinteCarbone) || $empreinteCarbone < 0) {
            $errors[] = "L'empreinte carbone doit être un nombre positif";
        }
        
        if (empty($errors)) {
            $data = [
                'nom' => $nom,
                'categorie' => $categorie,
                'empreinteCarbone' => floatval($empreinteCarbone)
            ];
            
            $result = ProduitController::addProduit($conn, $data);
            
            if ($result) {
                header("Location: index.php?action=listProduits&success=1");
                exit;
            } else {
                $message = "Erreur lors de l'ajout du produit";
                $message_type = 'error';
                $action = 'addProduit';
            }
        } else {
            $message = implode('<br>', $errors);
            $message_type = 'error';
            $action = 'addProduit';
        }
    }
    
    // Modification de produit
    elseif (isset($_POST['edit_produit'])) {
        $id = $_POST['produit_id'] ?? '';
        $nom = trim($_POST['nom'] ?? '');
        $categorie = trim($_POST['categorie'] ?? '');
        $empreinteCarbone = $_POST['empreinteCarbone'] ?? '';
        
        $errors = [];
        
        if (empty($nom)) $errors[] = "Le nom du produit est obligatoire";
        if (empty($categorie)) $errors[] = "La catégorie est obligatoire";
        if (empty($empreinteCarbone) || !is_numeric($empreinteCarbone) || $empreinteCarbone < 0) {
            $errors[] = "L'empreinte carbone doit être un nombre positif";
        }
        
        if (empty($errors)) {
            $data = [
                'nom' => $nom,
                'categorie' => $categorie,
                'empreinteCarbone' => floatval($empreinteCarbone)
            ];
            
            $result = ProduitController::editProduit($conn, $id, $data);
            
            if ($result) {
                header("Location: index.php?action=listProduits&updated=1");
                exit;
            } else {
                $message = "Erreur lors de la modification du produit";
                $message_type = 'error';
                $action = 'editProduit';
            }
        } else {
            $message = implode('<br>', $errors);
            $message_type = 'error';
            $action = 'editProduit';
        }
    }
    
    // Ajout de déchet
    elseif (isset($_POST['add_dechet'])) {
        $type = trim($_POST['type'] ?? '');
        $poids = $_POST['poids'] ?? '';
        $recyclable = isset($_POST['recyclable']) ? 1 : 0;
        $id = $_POST['id'] ?? '';

        $errors = [];

        if (empty($type)) $errors[] = "Le type de déchet est obligatoire";
        if (empty($poids) || !is_numeric($poids) || $poids <= 0) {
            $errors[] = "Le poids doit être un nombre positif";
        }
        if (empty($id) || !is_numeric($id)) $errors[] = "Veuillez sélectionner un produit";

        if (empty($errors)) {
            $data = [
                'type' => $type,
                'poids' => floatval($poids),
                'recyclable' => $recyclable,
                'id' => intval($id)
            ];

            $result = DechetController::addDechet($conn, $data);

            if ($result) {
                header("Location: index.php?action=listDechets&success=1");
                exit;
            } else {
                $message = "Erreur lors de l'ajout du déchet";
                $message_type = 'error';
                $action = 'addDechet';
            }
        } else {
            $message = implode('<br>', $errors);
            $message_type = 'error';
            $action = 'addDechet';
        }
    }
    
    // Modification de déchet
    elseif (isset($_POST['edit_dechet'])) {
        $idDechet = $_POST['dechet_id'] ?? '';
        $type = trim($_POST['type'] ?? '');
        $poids = $_POST['poids'] ?? '';
        $recyclable = isset($_POST['recyclable']) ? 1 : 0;
        $id = $_POST['id'] ?? '';

        $errors = [];

        if (empty($type)) $errors[] = "Le type de déchet est obligatoire";
        if (empty($poids) || !is_numeric($poids) || $poids <= 0) {
            $errors[] = "Le poids doit être un nombre positif";
        }
        if (empty($id) || !is_numeric($id)) $errors[] = "Veuillez sélectionner un produit";

        if (empty($errors)) {
            $data = [
                'type' => $type,
                'poids' => floatval($poids),
                'recyclable' => $recyclable,
                'id' => intval($id)
            ];

            $result = DechetController::editDechet($conn, $idDechet, $data);

            if ($result) {
                header("Location: index.php?action=listDechets&updated=1");
                exit;
            } else {
                $message = "Erreur lors de la modification du déchet";
                $message_type = 'error';
                $action = 'editDechet';
            }
        } else {
            $message = implode('<br>', $errors);
            $message_type = 'error';
            $action = 'editDechet';
        }
    }
    
    // Suppression de produit
    elseif (isset($_POST['delete_produit'])) {
        $id = $_POST['produit_id'] ?? '';
        
        $result = ProduitController::removeProduit($conn, $id);
        
        if ($result === true) {
            header("Location: index.php?action=listProduits&deleted=1");
            exit;
        } elseif ($result === "FK_CONSTRAINT") {
            header("Location: index.php?action=listProduits&error=foreign");
            exit;
        } else {
            header("Location: index.php?action=listProduits&error=unknown");
            exit;
        }
    }
    
    // Suppression de déchet
    elseif (isset($_POST['delete_dechet'])) {
        $idDechet = $_POST['dechet_id'] ?? '';
        
        $result = DechetController::removeDechet($conn, $idDechet);
        
        if ($result === true) {
            header("Location: index.php?action=listDechets&deleted=1");
            exit;
        } elseif ($result === "FK_CONSTRAINT") {
            header("Location: index.php?action=listDechets&error=foreign");
            exit;
        } else {
            header("Location: index.php?action=listDechets&error=unknown");
            exit;
        }
    }
}

// Charger les données pour l'édition si nécessaire
$produit_to_edit = null;
$dechet_to_edit = null;

if ($action == 'editProduit' && $id) {
    $produit_to_edit = ProduitController::getProduitById($conn, $id);
    if (!$produit_to_edit) {
        header("Location: index.php?action=listProduits&error=notfound");
        exit;
    }
}

if ($action == 'editDechet' && $idDechet) {
    $dechet_to_edit = DechetController::getDechetById($conn, $idDechet);
    if (!$dechet_to_edit) {
        header("Location: index.php?action=listDechets&error=notfound");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoTrack</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        /* Style pour le header */
        .header {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .header h1 {
            color: #2c3e50;
            font-size: 2.8rem;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .header p {
            color: #7f8c8d;
            font-size: 1.2rem;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Navigation */
        .nav {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 20px;
            flex-wrap: wrap;
        }

        .nav-btn {
            padding: 12px 24px;
            background: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .nav-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .nav-btn.active {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }

        /* Contenu principal */
        .main-content {
            background: white;
            border-radius: 15px;
            margin: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            min-height: 500px;
        }

        /* Statistiques principales */
        .main-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .main-stat-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .main-stat-card:hover {
            transform: translateY(-5px);
        }

        .main-stat-card.produit {
            border-top: 5px solid #3498db;
        }

        .main-stat-card.dechet {
            border-top: 5px solid #2ecc71;
        }

        .main-stat-card.impact {
            border-top: 5px solid #e74c3c;
        }

        .main-stat-card.ratio {
            border-top: 5px solid #f39c12;
        }

        .main-stat-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        .main-stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .main-stat-label {
            color: #7f8c8d;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }

        @media (max-width: 1100px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        .dashboard-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .dashboard-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f1f1;
        }

        .dashboard-card-title {
            font-size: 1.5rem;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Liste des éléments récents */
        .recent-list {
            list-style: none;
        }

        .recent-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            margin-bottom: 10px;
            background: #f9f9f9;
            border-radius: 10px;
            transition: background 0.3s ease;
        }

        .recent-item:hover {
            background: #f1f1f1;
        }

        .recent-info {
            flex: 1;
        }

        .recent-name {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .recent-details {
            font-size: 0.9rem;
            color: #7f8c8d;
        }

        .recent-actions {
            display: flex;
            gap: 10px;
        }

        /* Actions rapides */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .quick-action-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            cursor: pointer;
        }

        .quick-action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .quick-action-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .quick-action-title {
            font-size: 1.2rem;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .quick-action-desc {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        /* Boutons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }

        .btn-sm {
            padding: 8px 16px;
            font-size: 0.8rem;
        }

        .btn-success {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
        }

        .btn-warning {
            background: linear-gradient(135deg, #f39c12, #e67e22);
        }

        .btn-danger {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
        }

        /* Badges */
        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        /* État vide */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #7f8c8d;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.5s ease;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 30px;
            margin-top: 40px;
            color: white;
            font-size: 0.9rem;
            opacity: 0.8;
        }

        /* Styles pour les formulaires */
        .form-container {
            max-width: 600px;
            margin: 0 auto;
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-header h2 {
            color: #2c3e50;
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        /* Styles pour les tableaux */
        .table-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 15px 20px;
            text-align: left;
            font-weight: 600;
        }

        td {
            padding: 15px 20px;
            border-bottom: 1px solid #ecf0f1;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .impact-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .impact-low {
            background: #d4edda;
            color: #155724;
        }

        .impact-medium {
            background: #fff3cd;
            color: #856404;
        }

        .impact-high {
            background: #f8d7da;
            color: #721c24;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        /* Messages */
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Statistiques */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #3498db;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        /* Confirmation de suppression */
        .confirm-box {
            text-align: center;
            padding: 40px;
            background: #f8f9fa;
            border-radius: 10px;
            margin: 20px 0;
        }

        .confirm-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }

        .confirm-title {
            color: #e74c3c;
            margin-bottom: 10px;
        }

        .confirm-actions {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }

        /* ID Box */
        .id-box {
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }

        .id-box span {
            font-weight: 600;
            color: #3498db;
        }
    </style>
</head>
<body>
    <!-- Header principal -->
    <div class="header fade-in">
        <h1>🌍 EcoTrack</h1>
        <p>Surveillez et gérez l'impact environnemental de vos produits et déchets en un seul endroit</p>
    </div>

    <!-- Navigation -->
    <div class="nav">
        <button class="nav-btn <?= $action == 'dashboard' ? 'active' : '' ?>" onclick="changeAction('dashboard')">🏠 Tableau de bord</button>
        <button class="nav-btn <?= $action == 'listProduits' ? 'active' : '' ?>" onclick="changeAction('listProduits')">📦 Produits</button>
        <button class="nav-btn <?= $action == 'listDechets' ? 'active' : '' ?>" onclick="changeAction('listDechets')">🗑️ Déchets</button>
        <button class="nav-btn <?= $action == 'addProduit' ? 'active' : '' ?>" onclick="changeAction('addProduit')">➕ Ajouter Produit</button>
        <button class="nav-btn <?= $action == 'addDechet' ? 'active' : '' ?>" onclick="changeAction('addDechet')">➕ Ajouter Déchet</button>
    </div>

    <!-- Contenu principal -->
    <div class="main-content">
        <?php if ($action == 'dashboard'): ?>
            <!-- Dashboard -->
            <!-- Statistiques principales -->
            <div class="main-stats">
                <div class="main-stat-card produit fade-in" style="animation-delay: 0.1s">
                    <div class="main-stat-icon">📦</div>
                    <div class="main-stat-value"><?= $produitsCount ?></div>
                    <div class="main-stat-label">Produits</div>
                </div>

                <div class="main-stat-card dechet fade-in" style="animation-delay: 0.2s">
                    <div class="main-stat-icon">🗑️</div>
                    <div class="main-stat-value"><?= $dechetsCount ?></div>
                    <div class="main-stat-label">Déchets</div>
                </div>

                <div class="main-stat-card impact fade-in" style="animation-delay: 0.3s">
                    <div class="main-stat-icon">⚠️</div>
                    <div class="main-stat-value"><?= number_format($empreinteTotale, 1) ?> kg</div>
                    <div class="main-stat-label">CO₂ Total</div>
                </div>

                <div class="main-stat-card ratio fade-in" style="animation-delay: 0.4s">
                    <div class="main-stat-icon">♻️</div>
                    <div class="main-stat-value"><?= number_format($ratioRecyclable, 0) ?>%</div>
                    <div class="main-stat-label">Taux de recyclage</div>
                </div>
            </div>

            <!-- Grille du tableau de bord -->
            <div class="dashboard-grid">
                <!-- Produits récents -->
                <div class="dashboard-card fade-in" style="animation-delay: 0.2s">
                    <div class="dashboard-card-header">
                        <h2 class="dashboard-card-title">
                            <span>📦</span> Produits récents
                        </h2>
                        <button class="btn btn-sm" onclick="changeAction('listProduits')">Voir tous</button>
                    </div>

                    <?php if (empty($produitsRecents)): ?>
                        <div class="empty-state">
                            <div>📦</div>
                            <h3>Aucun produit</h3>
                            <p>Ajoutez votre premier produit</p>
                        </div>
                    <?php else: ?>
                        <ul class="recent-list">
                            <?php foreach ($produitsRecents as $produit): ?>
                            <?php
                            $empreinteCarbone = isset($produit['empreinteCarbone']) ? floatval($produit['empreinteCarbone']) : 0;
                            $impactClass = $empreinteCarbone > 10 ? 'badge-danger' : ($empreinteCarbone > 5 ? 'badge-warning' : 'badge-success');
                            $impactText = $empreinteCarbone > 10 ? 'Élevé' : ($empreinteCarbone > 5 ? 'Moyen' : 'Faible');
                            ?>
                            <li class="recent-item">
                                <div class="recent-info">
                                    <div class="recent-name"><?= htmlspecialchars($produit['nom'] ?? 'Nom inconnu') ?></div>
                                    <div class="recent-details">
                                        <?= htmlspecialchars($produit['categorie'] ?? 'Non catégorisé') ?> 
                                        • <?= number_format($empreinteCarbone, 2) ?> kg CO₂
                                    </div>
                                </div>
                                <div class="recent-actions">
                                    <span class="badge <?= $impactClass ?>"><?= $impactText ?></span>
                                    <button class="btn btn-sm btn-warning" onclick="changeAction('editProduit&id=<?= $produit['id'] ?>')">✏️</button>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <div style="text-align: center; margin-top: 20px;">
                        <button class="btn btn-success" onclick="changeAction('addProduit')">+ Ajouter un produit</button>
                    </div>
                </div>

                <!-- Déchets récents -->
                <div class="dashboard-card fade-in" style="animation-delay: 0.3s">
                    <div class="dashboard-card-header">
                        <h2 class="dashboard-card-title">
                            <span>🗑️</span> Déchets récents
                        </h2>
                        <button class="btn btn-sm" onclick="changeAction('listDechets')">Voir tous</button>
                    </div>

                    <?php if (empty($dechetsRecents)): ?>
                        <div class="empty-state">
                            <div>🗑️</div>
                            <h3>Aucun déchet</h3>
                            <p>Ajoutez votre premier déchet</p>
                        </div>
                    <?php else: ?>
                        <ul class="recent-list">
                            <?php foreach ($dechetsRecents as $dechet): ?>
                            <li class="recent-item">
                                <div class="recent-info">
                                    <div class="recent-name"><?= htmlspecialchars($dechet['type'] ?? 'Type inconnu') ?></div>
                                    <div class="recent-details">
                                        <?= number_format($dechet['poids'], 2) ?> kg
                                        <?php if ($dechet['produit_nom']): ?>
                                            • Produit: <?= htmlspecialchars($dechet['produit_nom']) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="recent-actions">
                                    <span class="badge <?= $dechet['recyclable'] ? 'badge-success' : 'badge-danger' ?>">
                                        <?= $dechet['recyclable'] ? 'Recyclable' : 'Non recyclable' ?>
                                    </span>
                                    <button class="btn btn-sm btn-warning" onclick="changeAction('editDechet&idDechet=<?= $dechet['idDechet'] ?>')">✏️</button>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <div style="text-align: center; margin-top: 20px;">
                        <button class="btn btn-success" onclick="changeAction('addDechet')">+ Ajouter un déchet</button>
                    </div>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="quick-actions fade-in" style="animation-delay: 0.4s">
                <div class="quick-action-card" onclick="changeAction('addProduit')">
                    <div class="quick-action-icon">📦</div>
                    <div class="quick-action-title">Ajouter un produit</div>
                    <div class="quick-action-desc">Enregistrez un nouveau produit avec son empreinte carbone</div>
                </div>

                <div class="quick-action-card" onclick="changeAction('addDechet')">
                    <div class="quick-action-icon">🗑️</div>
                    <div class="quick-action-title">Ajouter un déchet</div>
                    <div class="quick-action-desc">Enregistrez un déchet associé à un produit</div>
                </div>

                <div class="quick-action-card" onclick="changeAction('listProduits')">
                    <div class="quick-action-icon">📋</div>
                    <div class="quick-action-title">Liste des produits</div>
                    <div class="quick-action-desc">Consultez et gérez tous vos produits</div>
                </div>

                <div class="quick-action-card" onclick="changeAction('listDechets')">
                    <div class="quick-action-icon">📊</div>
                    <div class="quick-action-title">Liste des déchets</div>
                    <div class="quick-action-desc">Visualisez et gérez tous vos déchets</div>
                </div>
            </div>

        <?php elseif ($action == 'listProduits'): ?>
            <!-- Liste des produits -->
            <?php
            $produits_result = ProduitController::listProduit($conn);
            if (is_array($produits_result)) {
                $produits = $produits_result;
            } else {
                $produits = [];
                $error_message = "Erreur lors du chargement des produits depuis la base de données.";
            }

            $empreinteTotale = 0;
            $highImpact = 0;
            $produitsCount = count($produits);
            foreach ($produits as $produit) {
                if (isset($produit['empreinteCarbone'])) {
                    $empreinteTotale += $produit['empreinteCarbone'];
                    if ($produit['empreinteCarbone'] > 10) $highImpact++;
                }
            }
            ?>

            <div class="form-header">
                <h2>📦 Liste des Produits</h2>
                <p>Gérez l'ensemble des produits et leur impact environnemental</p>
            </div>

            <!-- Messages de succès/erreur -->
            <?php if (isset($_GET['success'])): ?>
                <div class="message success">
                    ✅ Produit ajouté avec succès !
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['updated'])): ?>
                <div class="message success">
                    ✅ Produit modifié avec succès !
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['deleted'])): ?>
                <div class="message success">
                    ✅ Produit supprimé avec succès !
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <?php if ($_GET['error'] == 'foreign'): ?>
                    <div class="message error">
                        ❌ Impossible de supprimer ce produit car il est associé à des déchets.
                    </div>
                <?php else: ?>
                    <div class="message error">
                        ❌ Erreur lors de l'opération.
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="message error">
                    ❌ <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <!-- Statistiques -->
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-value"><?= $produitsCount ?></div>
                    <div class="stat-label">Produits Totaux</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">
                        <?= number_format($empreinteTotale, 2) ?> kg
                    </div>
                    <div class="stat-label">Empreinte Totale CO₂</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">
                        <?= $highImpact ?>
                    </div>
                    <div class="stat-label">Produits Haut Impact</div>
                </div>
            </div>

            <!-- Actions -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="color: #2c3e50;">Gestion des Produits</h3>
                <button class="btn btn-success" onclick="changeAction('addProduit')">
                    + Ajouter un Produit
                </button>
            </div>

            <div class="table-container">
                <?php if (empty($produits)): ?>
                    <div class="empty-state">
                        <i>📦</i>
                        <h3>Aucun produit enregistré</h3>
                        <p>Commencez par ajouter votre premier produit</p>
                        <button class="btn btn-success" onclick="changeAction('addProduit')" style="margin-top: 20px;">
                            Ajouter un Produit
                        </button>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Catégorie</th>
                                <th>Empreinte Carbone</th>
                                <th>Impact</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produits as $produit): ?>
                            <?php
                            $id = isset($produit['id']) ? $produit['id'] : 'N/A';
                            $nom = isset($produit['nom']) ? htmlspecialchars($produit['nom']) : 'Nom non disponible';
                            $categorie = isset($produit['categorie']) ? htmlspecialchars($produit['categorie']) : 'Non catégorisé';
                            $empreinteCarbone = isset($produit['empreinteCarbone']) ? floatval($produit['empreinteCarbone']) : 0;
                            
                            $impactClass = '';
                            $impactText = '';
                            if ($empreinteCarbone > 10) {
                                $impactClass = 'impact-high';
                                $impactText = 'Élevé';
                            } elseif ($empreinteCarbone > 5) {
                                $impactClass = 'impact-medium';
                                $impactText = 'Moyen';
                            } else {
                                $impactClass = 'impact-low';
                                $impactText = 'Faible';
                            }
                            ?>
                            <tr>
                                <td>
                                    <strong><?= $nom ?></strong>
                                    <br>
                                    <small style="color: #7f8c8d;">ID: #<?= $id ?></small>
                                </td>
                                <td>
                                    <span style="background: #e3f2fd; color: #1976d2; padding: 4px 8px; border-radius: 12px; font-size: 0.8rem;">
                                        <?= $categorie ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?= number_format($empreinteCarbone, 2) ?> kg CO₂</strong>
                                </td>
                                <td>
                                    <span class="impact-badge <?= $impactClass ?>">
                                        <?= $impactText ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-warning btn-sm" onclick="changeAction('editProduit&id=<?= $id ?>')">
                                            ✏️ Modifier
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="changeAction('deleteProduit&id=<?= $id ?>')">
                                            🗑️ Supprimer
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

        <?php elseif ($action == 'listDechets'): ?>
            <!-- Liste des déchets -->
            <?php
            $dechets = DechetController::listDechet($conn);
            $totalPoids = 0;
            $recyclableCount = 0;
            foreach ($dechets as $dechet) {
                $totalPoids += $dechet['poids'];
                if ($dechet['recyclable']) {
                    $recyclableCount++;
                }
            }
            ?>

            <div class="form-header">
                <h2>🗑️ Liste des Déchets</h2>
                <p>Gérez l'ensemble des déchets et leur impact environnemental</p>
            </div>

            <!-- Messages de succès/erreur -->
            <?php if (isset($_GET['success'])): ?>
                <div class="message success">
                    ✅ Déchet ajouté avec succès !
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['updated'])): ?>
                <div class="message success">
                    ✅ Déchet modifié avec succès !
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['deleted'])): ?>
                <div class="message success">
                    ✅ Déchet supprimé avec succès !
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <?php if ($_GET['error'] == 'foreign'): ?>
                    <div class="message error">
                        ❌ Impossible de supprimer ce déchet.
                    </div>
                <?php else: ?>
                    <div class="message error">
                        ❌ Erreur lors de l'opération.
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Statistiques -->
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-value"><?= count($dechets) ?></div>
                    <div class="stat-label">Déchets Totaux</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">
                        <?= number_format($totalPoids, 2) ?> kg
                    </div>
                    <div class="stat-label">Poids Total</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">
                        <?= $recyclableCount ?>
                    </div>
                    <div class="stat-label">Déchets Recyclables</div>
                </div>
            </div>

            <!-- Actions -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3>Gestion des Déchets</h3>
                <button class="btn btn-success" onclick="changeAction('addDechet')">
                    + Ajouter un Déchet
                </button>
            </div>

            <div class="table-container">
                <?php if (empty($dechets)): ?>
                    <div class="empty-state">
                        <i>🗑️</i>
                        <h3>Aucun déchet enregistré</h3>
                        <p>Commencez par ajouter votre premier déchet</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Poids (kg)</th>
                                <th>Recyclable</th>
                                <th>Produit associé</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dechets as $dechet): ?>
                            <tr>
                                <td><?= htmlspecialchars($dechet['type']) ?></td>
                                <td><?= number_format($dechet['poids'], 2) ?></td>
                                <td>
                                    <?php if ($dechet['recyclable']): ?>
                                        <span style="color: green;">✅ Oui</span>
                                    <?php else: ?>
                                        <span style="color: red;">❌ Non</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($dechet['produit_nom'] ?? 'Non associé') ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-warning btn-sm" onclick="changeAction('editDechet&idDechet=<?= $dechet['idDechet'] ?>')">
                                            ✏️ Modifier
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="changeAction('deleteDechet&idDechet=<?= $dechet['idDechet'] ?>')">
                                            🗑️ Supprimer
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

        <?php elseif ($action == 'addProduit'): ?>
            <!-- Ajouter un produit -->
            <div class="form-container">
                <div class="form-header">
                    <h2>📦 Ajouter un Produit</h2>
                    <p>Enregistrez un nouveau produit dans le système EcoTrack</p>
                </div>

                <?php if ($message): ?>
                    <div class="message <?= $message_type ?>">
                        <?= $message ?>
                    </div>
                <?php endif; ?>

                <form method="POST" id="produitForm" novalidate>
                    <input type="hidden" name="add_produit" value="1">
                    
                    <div class="form-group">
                        <label for="nom">Nom du produit *</label>
                        <input type="text" 
                               name="nom" 
                               id="nom" 
                               required 
                               placeholder="Ex: Téléphone portable, Bouteille en plastique..."
                               value="<?= isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : '' ?>"
                               maxlength="255"
                               class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="categorie">Catégorie *</label>
                        <select name="categorie" id="categorie" required class="form-control">
                            <option value="">Sélectionnez une catégorie</option>
                            <option value="Électronique" <?= (isset($_POST['categorie']) && $_POST['categorie'] == 'Électronique') ? 'selected' : '' ?>>📱 Électronique</option>
                            <option value="Textile" <?= (isset($_POST['categorie']) && $_POST['categorie'] == 'Textile') ? 'selected' : '' ?>>👕 Textile</option>
                            <option value="Alimentation" <?= (isset($_POST['categorie']) && $_POST['categorie'] == 'Alimentation') ? 'selected' : '' ?>>🍎 Alimentation</option>
                            <option value="Emballage" <?= (isset($_POST['categorie']) && $_POST['categorie'] == 'Emballage') ? 'selected' : '' ?>>📦 Emballage</option>
                            <option value="Mobilier" <?= (isset($_POST['categorie']) && $_POST['categorie'] == 'Mobilier') ? 'selected' : '' ?>>🛋️ Mobilier</option>
                            <option value="Transport" <?= (isset($_POST['categorie']) && $_POST['categorie'] == 'Transport') ? 'selected' : '' ?>>🚗 Transport</option>
                            <option value="Énergie" <?= (isset($_POST['categorie']) && $_POST['categorie'] == 'Énergie') ? 'selected' : '' ?>>⚡ Énergie</option>
                            <option value="Construction" <?= (isset($_POST['categorie']) && $_POST['categorie'] == 'Construction') ? 'selected' : '' ?>>🏗️ Construction</option>
                            <option value="Autre" <?= (isset($_POST['categorie']) && $_POST['categorie'] == 'Autre') ? 'selected' : '' ?>>📦 Autre</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="empreinteCarbone">Empreinte Carbone (kg CO₂) *</label>
                        <input type="number" 
                               name="empreinteCarbone" 
                               id="empreinteCarbone" 
                               step="0.01" 
                               min="0" 
                               required 
                               placeholder="Ex: 2.5"
                               value="<?= isset($_POST['empreinteCarbone']) ? htmlspecialchars($_POST['empreinteCarbone']) : '' ?>"
                               class="form-control">
                        <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                            🌍 Entrez l'empreinte carbone en kilogrammes de CO₂ équivalent
                        </small>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-warning" onclick="changeAction('dashboard')">
                            Annuler
                        </button>
                        <button type="submit" class="btn btn-success">
                            💾 Enregistrer le Produit
                        </button>
                    </div>
                </form>
            </div>

        <?php elseif ($action == 'editProduit' && $produit_to_edit): ?>
            <!-- Modifier un produit -->
            <div class="form-container">
                <div class="form-header">
                    <h2>✏️ Modifier un Produit</h2>
                    <p>Modifiez les informations du produit dans le système EcoTrack</p>
                </div>

                <?php if ($message): ?>
                    <div class="message <?= $message_type ?>">
                        <?= $message ?>
                    </div>
                <?php endif; ?>

                <div class="id-box">
                    📋 <strong>Produit ID :</strong> <span>#<?= htmlspecialchars($produit_to_edit['id']) ?></span>
                </div>

                <form method="POST" id="produitForm">
                    <input type="hidden" name="edit_produit" value="1">
                    <input type="hidden" name="produit_id" value="<?= $produit_to_edit['id'] ?>">
                    
                    <div class="form-group">
                        <label for="nom">Nom du produit *</label>
                        <input type="text" 
                               name="nom" 
                               id="nom" 
                               required 
                               placeholder="Ex: Téléphone portable, Bouteille en plastique..."
                               value="<?= htmlspecialchars($produit_to_edit['nom']) ?>"
                               maxlength="255"
                               class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="categorie">Catégorie *</label>
                        <select name="categorie" id="categorie" required class="form-control">
                            <option value="">Sélectionnez une catégorie</option>
                            <option value="Électronique" <?= $produit_to_edit['categorie'] == 'Électronique' ? 'selected' : '' ?>>📱 Électronique</option>
                            <option value="Textile" <?= $produit_to_edit['categorie'] == 'Textile' ? 'selected' : '' ?>>👕 Textile</option>
                            <option value="Alimentation" <?= $produit_to_edit['categorie'] == 'Alimentation' ? 'selected' : '' ?>>🍎 Alimentation</option>
                            <option value="Emballage" <?= $produit_to_edit['categorie'] == 'Emballage' ? 'selected' : '' ?>>📦 Emballage</option>
                            <option value="Mobilier" <?= $produit_to_edit['categorie'] == 'Mobilier' ? 'selected' : '' ?>>🛋️ Mobilier</option>
                            <option value="Transport" <?= $produit_to_edit['categorie'] == 'Transport' ? 'selected' : '' ?>>🚗 Transport</option>
                            <option value="Énergie" <?= $produit_to_edit['categorie'] == 'Énergie' ? 'selected' : '' ?>>⚡ Énergie</option>
                            <option value="Construction" <?= $produit_to_edit['categorie'] == 'Construction' ? 'selected' : '' ?>>🏗️ Construction</option>
                            <option value="Autre" <?= $produit_to_edit['categorie'] == 'Autre' ? 'selected' : '' ?>>📦 Autre</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="empreinteCarbone">Empreinte Carbone (kg CO₂) *</label>
                        <input type="number" 
                               name="empreinteCarbone" 
                               id="empreinteCarbone" 
                               step="0.01" 
                               min="0" 
                               required 
                               placeholder="Ex: 2.5"
                               value="<?= htmlspecialchars($produit_to_edit['empreinteCarbone']) ?>"
                               class="form-control">
                        <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                            🌍 Entrez l'empreinte carbone en kilogrammes de CO₂ équivalent
                        </small>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-warning" onclick="changeAction('listProduits')">
                            Annuler
                        </button>
                        <button type="submit" class="btn btn-success">
                            💾 Mettre à jour le Produit
                        </button>
                    </div>
                </form>
            </div>

        <?php elseif ($action == 'deleteProduit' && $id): ?>
            <!-- Supprimer un produit -->
            <?php
            $produit_to_delete = ProduitController::getProduitById($conn, $id);
            if (!$produit_to_delete) {
                echo "<script>changeAction('listProduits&error=notfound');</script>";
                exit;
            }
            ?>

            <div class="form-container">
                <div class="form-header">
                    <h2>🗑️ Supprimer un Produit</h2>
                    <p>Confirmez la suppression de ce produit</p>
                </div>

                <div class="confirm-box">
                    <div class="confirm-icon">⚠️</div>
                    <h2 class="confirm-title">Êtes-vous sûr de vouloir supprimer ce produit ?</h2>
                    <p>Cette action est irréversible et supprimera toutes les données associées à ce produit.</p>
                    
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: left;">
                        <h4>Informations du produit :</h4>
                        <p><strong>Nom :</strong> <?= htmlspecialchars($produit_to_delete['nom']) ?></p>
                        <p><strong>Catégorie :</strong> <?= htmlspecialchars($produit_to_delete['categorie']) ?></p>
                        <p><strong>Empreinte carbone :</strong> <?= number_format($produit_to_delete['empreinteCarbone'], 2) ?> kg CO₂</p>
                    </div>

                    <div class="confirm-actions">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="delete_produit" value="1">
                            <input type="hidden" name="produit_id" value="<?= $id ?>">
                            <button type="submit" class="btn btn-danger">
                                🗑️ Oui, supprimer
                            </button>
                        </form>
                        <button type="button" class="btn btn-warning" onclick="changeAction('listProduits')">
                            ❌ Non, annuler
                        </button>
                    </div>
                </div>
            </div>

        <?php elseif ($action == 'addDechet'): ?>
            <!-- Ajouter un déchet -->
            <?php
            $produits_list = ProduitController::listProduit($conn);
            ?>

            <div class="form-container">
                <div class="form-header">
                    <h2>🗑️ Ajouter un Déchet</h2>
                    <p>Enregistrez un nouveau déchet associé à un produit</p>
                </div>

                <?php if ($message): ?>
                    <div class="message <?= $message_type ?>">
                        <?= $message ?>
                    </div>
                <?php endif; ?>

                <form method="POST" id="dechetForm">
                    <input type="hidden" name="add_dechet" value="1">
                    
                    <div class="form-group">
                        <label for="type" class="required">Type de déchet *</label>
                        <select name="type" id="type" required class="form-control">
                            <option value="">Sélectionnez un type</option>
                            <option value="Plastique" <?= isset($_POST['type']) && $_POST['type'] == 'Plastique' ? 'selected' : '' ?>>Plastique</option>
                            <option value="Verre" <?= isset($_POST['type']) && $_POST['type'] == 'Verre' ? 'selected' : '' ?>>Verre</option>
                            <option value="Métal" <?= isset($_POST['type']) && $_POST['type'] == 'Métal' ? 'selected' : '' ?>>Métal</option>
                            <option value="Papier" <?= isset($_POST['type']) && $_POST['type'] == 'Papier' ? 'selected' : '' ?>>Papier</option>
                            <option value="Organique" <?= isset($_POST['type']) && $_POST['type'] == 'Organique' ? 'selected' : '' ?>>Organique</option>
                            <option value="Électronique" <?= isset($_POST['type']) && $_POST['type'] == 'Électronique' ? 'selected' : '' ?>>Électronique</option>
                            <option value="Textile" <?= isset($_POST['type']) && $_POST['type'] == 'Textile' ? 'selected' : '' ?>>Textile</option>
                            <option value="Autre" <?= isset($_POST['type']) && $_POST['type'] == 'Autre' ? 'selected' : '' ?>>Autre</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="poids">Poids (kg) *</label>
                        <input type="number" 
                               name="poids" 
                               id="poids" 
                               step="0.01" 
                               min="0.01" 
                               required 
                               placeholder="Ex: 2.5"
                               value="<?= isset($_POST['poids']) ? htmlspecialchars($_POST['poids']) : '' ?>"
                               class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="id">Produit associé *</label>
                        <select name="id" id="id" required class="form-control">
                            <option value="">Sélectionnez un produit</option>
                            <?php foreach ($produits_list as $produit): ?>
                                <option value="<?= $produit['id'] ?>" <?= isset($_POST['id']) && $_POST['id'] == $produit['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($produit['nom']) ?> (<?= $produit['categorie'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" 
                                   name="recyclable" 
                                   value="1" 
                                   <?= isset($_POST['recyclable']) ? 'checked' : '' ?>>
                            Recyclable
                        </label>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-warning" onclick="changeAction('dashboard')">
                            Annuler
                        </button>
                        <button type="submit" class="btn btn-success">
                            💾 Enregistrer le Déchet
                        </button>
                    </div>
                </form>
            </div>

        <?php elseif ($action == 'editDechet' && $dechet_to_edit): ?>
            <!-- Modifier un déchet -->
            <?php
            $produits_list = ProduitController::listProduit($conn);
            ?>

            <div class="form-container">
                <div class="form-header">
                    <h2>✏️ Modifier un Déchet</h2>
                    <p>Modifiez les informations du déchet dans le système EcoTrack</p>
                </div>

                <?php if ($message): ?>
                    <div class="message <?= $message_type ?>">
                        <?= $message ?>
                    </div>
                <?php endif; ?>

                <div class="id-box">
                    🗑️ <strong>Déchet ID :</strong> <span>#<?= htmlspecialchars($dechet_to_edit['idDechet']) ?></span>
                </div>

                <form method="POST" id="dechetForm">
                    <input type="hidden" name="edit_dechet" value="1">
                    <input type="hidden" name="dechet_id" value="<?= $dechet_to_edit['idDechet'] ?>">
                    
                    <div class="form-group">
                        <label for="type" class="required">Type de déchet *</label>
                        <select name="type" id="type" required class="form-control">
                            <option value="">Sélectionnez un type</option>
                            <?php
                            $types = ['Plastique', 'Verre', 'Métal', 'Papier', 'Organique', 'Électronique', 'Textile', 'Autre'];
                            foreach ($types as $type):
                            ?>
                                <option value="<?= $type ?>" <?= ($dechet_to_edit['type'] ?? '') === $type ? 'selected' : '' ?>>
                                    <?= $type ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="poids" class="required">Poids (kg) *</label>
                        <input type="number" 
                               name="poids" 
                               id="poids" 
                               step="0.01" 
                               min="0.01" 
                               required 
                               value="<?= htmlspecialchars($dechet_to_edit['poids'] ?? '') ?>"
                               class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="id" class="required">Produit associé *</label>
                        <select name="id" id="id" required class="form-control">
                            <option value="">Sélectionnez un produit</option>
                            <?php foreach ($produits_list as $produit): ?>
                                <option value="<?= htmlspecialchars($produit['id']) ?>" 
                                    <?= ($dechet_to_edit['id'] ?? '') == $produit['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($produit['nom']) ?> (<?= htmlspecialchars($produit['categorie']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="checkbox" 
                                   name="recyclable" 
                                   id="recyclable" 
                                   value="1" 
                                   <?= ($dechet_to_edit['recyclable'] ?? 0) ? 'checked' : '' ?>>
                            <label for="recyclable">Ce déchet est recyclable</label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-warning" onclick="changeAction('listDechets')">
                            Annuler
                        </button>
                        <button type="submit" class="btn btn-success">
                            💾 Mettre à jour
                        </button>
                    </div>
                </form>
            </div>

        <?php elseif ($action == 'deleteDechet' && $idDechet): ?>
            <!-- Supprimer un déchet -->
            <?php
            $dechet_to_delete = DechetController::getDechetById($conn, $idDechet);
            if (!$dechet_to_delete) {
                echo "<script>changeAction('listDechets&error=notfound');</script>";
                exit;
            }
            ?>

            <div class="form-container">
                <div class="form-header">
                    <h2>🗑️ Supprimer un Déchet</h2>
                    <p>Confirmez la suppression de ce déchet</p>
                </div>

                <div class="confirm-box">
                    <div class="confirm-icon">⚠️</div>
                    <h2 class="confirm-title">Êtes-vous sûr de vouloir supprimer ce déchet ?</h2>
                    <p>Cette action est irréversible.</p>
                    
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: left;">
                        <h4>Informations du déchet :</h4>
                        <p><strong>Type :</strong> <?= htmlspecialchars($dechet_to_delete['type']) ?></p>
                        <p><strong>Poids :</strong> <?= number_format($dechet_to_delete['poids'], 2) ?> kg</p>
                        <p><strong>Recyclable :</strong> <?= $dechet_to_delete['recyclable'] ? 'Oui' : 'Non' ?></p>
                        <p><strong>Produit associé :</strong> <?= htmlspecialchars($dechet_to_delete['produit_nom'] ?? 'Non associé') ?></p>
                    </div>

                    <div class="confirm-actions">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="delete_dechet" value="1">
                            <input type="hidden" name="dechet_id" value="<?= $idDechet ?>">
                            <button type="submit" class="btn btn-danger">
                                🗑️ Oui, supprimer
                            </button>
                        </form>
                        <button type="button" class="btn btn-warning" onclick="changeAction('listDechets')">
                            ❌ Non, annuler
                        </button>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- Page non trouvée -->
            <div class="empty-state">
                <div>❓</div>
                <h3>Page non trouvée</h3>
                <p>La page que vous recherchez n'existe pas ou a été déplacée.</p>
                <button class="btn btn-success" onclick="changeAction('dashboard')" style="margin-top: 20px;">
                    Retour au tableau de bord
                </button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <div class="footer fade-in" style="animation-delay: 0.5s">
        <p>© <?= date('Y') ?> EcoTrack - Suivi environnemental des produits et déchets</p>
        <p>Total produits: <?= $produitsCount ?> | Total déchets: <?= $dechetsCount ?> | Empreinte carbone totale: <?= number_format($empreinteTotale, 2) ?> kg CO₂</p>
    </div>

    <script>
        // Fonction pour changer l'action
        function changeAction(action) {
            window.location.href = 'index.php?action=' + action;
        }

        // Animation des statistiques
        document.addEventListener('DOMContentLoaded', function() {
            const statValues = document.querySelectorAll('.main-stat-value');
            statValues.forEach(stat => {
                const originalText = stat.textContent;
                stat.textContent = '0';
                
                setTimeout(() => {
                    let current = 0;
                    const target = parseFloat(originalText.replace(/[^0-9.]/g, ''));
                    const suffix = originalText.replace(/[0-9.]/g, '');
                    const increment = target / 50;
                    const timer = setInterval(() => {
                        current += increment;
                        if (current >= target) {
                            current = target;
                            clearInterval(timer);
                        }
                        stat.textContent = current.toFixed(suffix.includes('%') ? 0 : 1) + suffix;
                    }, 30);
                }, 500);
            });
        });

        // Validation des formulaires
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const requiredFields = form.querySelectorAll('[required]');
                    let isValid = true;
                    let errorMessage = '';
                    
                    requiredFields.forEach(field => {
                        if (!field.value.trim()) {
                            isValid = false;
                            field.style.borderColor = '#e74c3c';
                            errorMessage = 'Veuillez remplir tous les champs obligatoires (*)';
                        } else {
                            field.style.borderColor = '#ddd';
                        }
                        
                        // Validation spécifique pour les nombres
                        if (field.type === 'number' && field.value) {
                            const value = parseFloat(field.value);
                            const min = parseFloat(field.min);
                            const step = parseFloat(field.step) || 0.01;
                            
                            if (field.id === 'empreinteCarbone' && value < 0) {
                                isValid = false;
                                field.style.borderColor = '#e74c3c';
                                errorMessage = 'L\'empreinte carbone doit être un nombre positif';
                            }
                            if (field.id === 'poids' && value <= 0) {
                                isValid = false;
                                field.style.borderColor = '#e74c3c';
                                errorMessage = 'Le poids doit être un nombre positif';
                            }
                        }
                    });
                    
                    if (!isValid) {
                        e.preventDefault();
                        alert(errorMessage);
                    }
                });
            });
        });
    </script>
</body>
</html>