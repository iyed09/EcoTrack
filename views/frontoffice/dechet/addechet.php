<?php
require_once "C:/xampppp/htdocs/ecotrack/Database.php";
require_once "C:/xampppp/htdocs/ecotrack/Controller/DechetController.php";
require_once "C:/xampppp/htdocs/ecotrack/Controller/ProduitController.php";

$conn = Database::getConnexion();
$message = '';
$message_type = '';

// Récupérer la liste des produits pour le dropdown
$produits = ProduitController::listProduit($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation
    $type = trim($_POST['type'] ?? '');
    $poids = $_POST['poids'] ?? '';
    $recyclable = isset($_POST['recyclable']) ? 1 : 0;
    $id = $_POST['id'] ?? '';

    $errors = [];

    if (empty($type)) {
        $errors[] = "Le type de déchet est obligatoire";
    }

    if (empty($poids) || !is_numeric($poids) || $poids <= 0) {
        $errors[] = "Le poids doit être un nombre positif";
    }

    if (empty($id) || !is_numeric($id)) {
        $errors[] = "Veuillez sélectionner un produit";
    }

    if (empty($errors)) {
        $data = [
            'type' => $type,
            'poids' => floatval($poids),
            'recyclable' => $recyclable,
            'id' => intval($id)
        ];

        $result = DechetController::addDechet($conn, $data);

        if ($result) {
            header("Location: listdechet.php?success=1");
            exit;
        } else {
            $message = "Erreur lors de l'ajout du déchet";
            $message_type = 'error';
        }
    } else {
        $message = implode('<br>', $errors);
        $message_type = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Déchet - EcoTrack</title>
    <style>
        /* Styles similaires à addproduit.php */
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <h2>🗑️ Ajouter un Déchet</h2>
            <p>Enregistrez un nouveau déchet associé à un produit</p>
        </div>

        <div class="form-body">
            <?php if ($message): ?>
                <div class="message <?= $message_type ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="dechetForm">
                <!-- Type de déchet -->
                <div class="form-group">
                    <label for="type" class="required">Type de déchet</label>
                    <select name="type" id="type" required>
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

                <!-- Poids -->
                <div class="form-group">
                    <label for="poids" class="required">Poids (kg)</label>
                    <input type="number" 
                           name="poids" 
                           id="poids" 
                           step="0.01" 
                           min="0.01" 
                           required 
                           placeholder="Ex: 2.5"
                           value="<?= isset($_POST['poids']) ? htmlspecialchars($_POST['poids']) : '' ?>">
                </div>

                <!-- Produit associé -->
                <div class="form-group">
                    <label for="id" class="required">Produit associé</label>
                    <select name="id" id="id" required>
                        <option value="">Sélectionnez un produit</option>
                        <?php foreach ($produits as $produit): ?>
                            <option value="<?= $produit['id'] ?>" <?= isset($_POST['id']) && $_POST['id'] == $produit['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($produit['nom']) ?> (<?= $produit['categorie'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Recyclable -->
                <div class="form-group">
                    <label>
                        <input type="checkbox" 
                               name="recyclable" 
                               value="1" 
                               <?= isset($_POST['recyclable']) ? 'checked' : '' ?>>
                        Recyclable
                    </label>
                </div>

                <button type="submit" class="btn">
                    💾 Enregistrer le Déchet
                </button>
            </form>

            <div class="form-footer">
                <a href="listdechet.php">← Retour à la liste des déchets</a>
            </div>
        </div>
    </div>

    <script>
        // Validation côté client
        document.getElementById('dechetForm').addEventListener('submit', function(e) {
            const type = document.getElementById('type').value;
            const poids = document.getElementById('poids').value;
            const produit = document.getElementById('id').value;

            let errors = [];

            if (!type) {
                errors.push("Veuillez sélectionner un type de déchet");
            }

            if (!poids || parseFloat(poids) <= 0) {
                errors.push("Le poids doit être un nombre positif");
            }

            if (!produit) {
                errors.push("Veuillez sélectionner un produit");
            }

            if (errors.length > 0) {
                e.preventDefault();
                alert("Veuillez corriger les erreurs suivantes :\n\n" + errors.join('\n'));
            }
        });
    </script>
</body>
</html>