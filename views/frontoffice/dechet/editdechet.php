<?php
$produits = $produits ?? [];
$dechet = $dechet ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un Déchet - EcoTrack</title>
    <style>
        /* Même style que addechet.php */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .form-container {
            width: 100%;
            max-width: 500px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .form-header {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }

        .form-header h2 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .form-header p {
            opacity: 0.9;
            font-size: 14px;
        }

        .form-body {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 14px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #f39c12;
            background: white;
            box-shadow: 0 0 0 3px rgba(243, 156, 18, 0.1);
        }

        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(243, 156, 18, 0.4);
        }

        .form-footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }

        .form-footer a {
            color: #f39c12;
            text-decoration: none;
            font-weight: 500;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }

        .required::after {
            content: " *";
            color: #e74c3c;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
        }

        .dechet-id {
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }

        .dechet-id span {
            font-weight: 600;
            color: #f39c12;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <h2>✏️ Modifier un Déchet</h2>
            <p>Modifiez les informations du déchet dans le système EcoTrack</p>
        </div>

        <div class="form-body">
            <div class="dechet-id">
                🗑️ <strong>Déchet ID :</strong> <span>#<?= htmlspecialchars($dechet['idDechet']) ?></span>
            </div>

            <form method="POST" id="dechetForm">
                <!-- Type de déchet -->
                <div class="form-group">
                    <label for="type" class="required">Type de déchet</label>
                    <select name="type" id="type" required>
                        <option value="">Sélectionnez un type</option>
                        <?php
                        $types = ['Plastique', 'Verre', 'Métal', 'Papier', 'Organique', 'Électronique', 'Textile', 'Autre'];
                        foreach ($types as $type):
                        ?>
                            <option value="<?= $type ?>" <?= ($dechet['type'] ?? '') === $type ? 'selected' : '' ?>>
                                <?= $type ?>
                            </option>
                        <?php endforeach; ?>
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
                           value="<?= htmlspecialchars($dechet['poids'] ?? '') ?>">
                </div>

                <!-- Produit associé -->
                <div class="form-group">
                    <label for="id" class="required">Produit associé</label>
                    <select name="id" id="id" required>
                        <option value="">Sélectionnez un produit</option>
                        <?php foreach ($produits as $produit): ?>
                            <option value="<?= htmlspecialchars($produit['id']) ?>" 
                                <?= ($dechet['id'] ?? '') == $produit['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($produit['nom']) ?> (<?= htmlspecialchars($produit['categorie']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Recyclable -->
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" 
                               name="recyclable" 
                               id="recyclable" 
                               value="1" 
                               <?= ($dechet['recyclable'] ?? 0) ? 'checked' : '' ?>>
                        <label for="recyclable">Ce déchet est recyclable</label>
                    </div>
                </div>

                <button type="submit" class="btn">
                    💾 Mettre à jour
                </button>
            </form>

            <div class="form-footer">
                <a href="index.php?controller=dechet&action=list">← Retour à la liste des déchets</a>
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
                errors.push("Veuillez sélectionner un produit associé");
            }

            if (errors.length > 0) {
                e.preventDefault();
                alert("Veuillez corriger les erreurs suivantes :\n\n" + errors.join('\n'));
            }
        });
    </script>
</body>
</html>