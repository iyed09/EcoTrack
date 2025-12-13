<?php
require_once "C:/xampppp/htdocs/ecotrack/Database.php";
require_once "C:/xampppp/htdocs/ecotrack/Controller/ProduitController.php";

$conn = Database::getConnexion();
$produits_result = ProduitController::listProduit($conn);

// Vérification robuste du résultat
if (is_array($produits_result)) {
    $produits = $produits_result;
} else {
    $produits = [];
    $error_message = "Erreur lors du chargement des produits depuis la base de données.";
}

// Calcul des statistiques
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

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Produits - EcoTrack</title>
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
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .header h1 {
            color: #2c3e50;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .header p {
            color: #7f8c8d;
            font-size: 1.1rem;
        }

        .actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
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

        .btn-sm {
            padding: 8px 16px;
            font-size: 0.8rem;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

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

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .table-container {
            animation: fadeIn 0.5s ease;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>📦 Liste des Produits</h1>
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

        <!-- Actions et Table -->
        <div class="actions">
            <h2 style="color: #2c3e50;">Gestion des Produits</h2>
            <a href="addproduit.php" class="btn btn-success">
                <span>+</span> Ajouter un Produit
            </a>
        </div>

        <div class="table-container">
            <?php if (isset($error_message)): ?>
                <!-- Ne rien afficher car l'erreur est déjà affichée plus haut -->
            <?php elseif (empty($produits)): ?>
                <div class="empty-state">
                    <i>📦</i>
                    <h3>Aucun produit enregistré</h3>
                    <p>Commencez par ajouter votre premier produit</p>
                    <a href="addproduit.php" class="btn btn-success" style="margin-top: 20px;">
                        Ajouter un Produit
                    </a>
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
                        // Validation robuste des données du produit
                        $id = isset($produit['id']) ? $produit['id'] : 'N/A';
                        $nom = isset($produit['nom']) ? htmlspecialchars($produit['nom']) : 'Nom non disponible';
                        $categorie = isset($produit['categorie']) ? htmlspecialchars($produit['categorie']) : 'Non catégorisé';
                        $empreinteCarbone = isset($produit['empreinteCarbone']) ? floatval($produit['empreinteCarbone']) : 0;
                        
                        // Détermination de l'impact
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
                                    <a href="editproduit.php?id=<?= $id ?>" class="btn btn-warning btn-sm">
                                        ✏️ Modifier
                                    </a>
                                    <a href="supproduit.php?id=<?= $id ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirmDelete()">
                                        🗑️ Supprimer
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Confirmation de suppression
        function confirmDelete() {
            return confirm('Êtes-vous sûr de vouloir supprimer ce produit ? Cette action est irréversible.');
        }

        // Animation pour les lignes du tableau
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach((row, index) => {
                row.style.animationDelay = `${index * 0.1}s`;
                row.style.opacity = '0';
                row.style.transform = 'translateX(-20px)';
                row.style.animation = 'fadeIn 0.5s ease forwards';
            });
        });
    </script>
</body>
</html>