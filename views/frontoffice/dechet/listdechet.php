<?php
require_once "C:/xampppp/htdocs/ecotrack/Database.php";
require_once "C:/xampppp/htdocs/ecotrack/Controller/DechetController.php";

$conn = Database::getConnexion();
$dechets = DechetController::listDechet($conn);

// Calcul des statistiques si nécessaire
$totalPoids = 0;
$recyclableCount = 0;
foreach ($dechets as $dechet) {
    $totalPoids += $dechet['poids'];
    if ($dechet['recyclable']) {
        $recyclableCount++;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Déchets - EcoTrack</title>
    <style>
        /* Styles similaires à listproduit.php */
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🗑️ Liste des Déchets</h1>
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
        <div class="actions">
            <h2>Gestion des Déchets</h2>
            <a href="addechet.php" class="btn btn-success">
                + Ajouter un Déchet
            </a>
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
                                    <a href="editdechet.php?idDechet=<?= $dechet['idDechet'] ?>" class="btn btn-warning btn-sm">
                                        ✏️ Modifier
                                    </a>
                                    <a href="suppdechet.php?idDechet=<?= $dechet['idDechet'] ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce déchet ?')">
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
</body>
</html>