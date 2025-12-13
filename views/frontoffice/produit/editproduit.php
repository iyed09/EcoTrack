<?php
require_once "C:/xampppp/htdocs/ecotrack/Database.php";
require_once "C:/xampppp/htdocs/ecotrack/Controller/ProduitController.php";

$conn = Database::getConnexion();
$message = '';
$message_type = '';

// Vérifier si un id est passé
if (!isset($_GET['id'])) {
    header("Location: listproduit.php");
    exit;
}

$id = (int) $_GET['id'];

// Charger le produit existant
try {
    $produit = ProduitController::getProduitById($conn, $id);
} catch (PDOException $e) {
    echo "Erreur: " . $e->getMessage();
    exit;
}

// Vérifier si le produit existe
if (!$produit || !isset($produit['id'])) {
    echo "Produit introuvable.";
    exit;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation des données
    $nom = trim($_POST['nom'] ?? '');
    $categorie = trim($_POST['categorie'] ?? '');
    $empreinteCarbone = $_POST['empreinteCarbone'] ?? '';
    
    $errors = [];
    
    // Validation
    if (empty($nom)) {
        $errors[] = "Le nom du produit est obligatoire";
    }
    
    if (empty($categorie)) {
        $errors[] = "La catégorie est obligatoire";
    }
    
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
        
        // Vérifier que $result est un tableau avant d'y accéder
        if (is_array($result) && isset($result['ok'])) {
            if ($result['ok']) {
                header("Location: listproduit.php?updated=1");
                exit;
            } else {
                $message = $result['msg'] ?? "Erreur lors de la modification du produit";
                $message_type = 'error';
            }
        } else {
            // Si $result n'est pas un tableau (retourne un booléen par exemple)
            if ($result) {
                header("Location: listproduit.php?updated=1");
                exit;
            } else {
                $message = "Erreur lors de la modification du produit";
                $message_type = 'error';
            }
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
    <title>Modifier un Produit - EcoTrack</title>
    <style>
        /* [Conserver le même CSS que addproduit.php] */
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
            background: linear-gradient(135deg, #3498db, #2980b9);
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
            border-color: #3498db;
            background: white;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #3498db, #2980b9);
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
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }

        .message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }

        .message.error {
            background: #ffe6e6;
            color: #d63031;
            border: 1px solid #ff7675;
        }

        .form-footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }

        .form-footer a {
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
        }

        .required::after {
            content: " *";
            color: #e74c3c;
        }

        .impact-info {
            background: #fff9e6;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
        }

        .impact-info h4 {
            color: #e67e22;
            margin-bottom: 10px;
        }

        .impact-info ul {
            list-style: none;
            padding-left: 0;
        }

        .impact-info li {
            margin-bottom: 5px;
            font-size: 13px;
            color: #666;
        }

        .product-id {
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }

        .product-id span {
            font-weight: 600;
            color: #3498db;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-container {
            animation: fadeIn 0.5s ease;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <h2>✏️ Modifier un Produit</h2>
            <p>Modifiez les informations du produit dans le système EcoTrack</p>
        </div>

        <div class="form-body">
            <?php if ($message): ?>
                <div class="message <?= $message_type ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <div class="product-id">
                📋 <strong>Produit ID :</strong> <span>#<?= htmlspecialchars($produit['id']) ?></span>
            </div>

            <form method="POST" id="produitForm">
                <!-- Nom du produit -->
                <div class="form-group">
                    <label for="nom" class="required">Nom du produit</label>
                    <input type="text" 
                           name="nom" 
                           id="nom" 
                           required 
                           placeholder="Ex: Téléphone portable, Bouteille en plastique..."
                           value="<?= htmlspecialchars($produit['nom']) ?>"
                           maxlength="255">
                </div>

                <!-- Catégorie -->
                <div class="form-group">
                    <label for="categorie" class="required">Catégorie</label>
                    <select name="categorie" id="categorie" required>
                        <option value="">Sélectionnez une catégorie</option>
                        <option value="Électronique" <?= $produit['categorie'] == 'Électronique' ? 'selected' : '' ?>>📱 Électronique</option>
                        <option value="Textile" <?= $produit['categorie'] == 'Textile' ? 'selected' : '' ?>>👕 Textile</option>
                        <option value="Alimentation" <?= $produit['categorie'] == 'Alimentation' ? 'selected' : '' ?>>🍎 Alimentation</option>
                        <option value="Emballage" <?= $produit['categorie'] == 'Emballage' ? 'selected' : '' ?>>📦 Emballage</option>
                        <option value="Mobilier" <?= $produit['categorie'] == 'Mobilier' ? 'selected' : '' ?>>🛋️ Mobilier</option>
                        <option value="Transport" <?= $produit['categorie'] == 'Transport' ? 'selected' : '' ?>>🚗 Transport</option>
                        <option value="Énergie" <?= $produit['categorie'] == 'Énergie' ? 'selected' : '' ?>>⚡ Énergie</option>
                        <option value="Construction" <?= $produit['categorie'] == 'Construction' ? 'selected' : '' ?>>🏗️ Construction</option>
                        <option value="Autre" <?= $produit['categorie'] == 'Autre' ? 'selected' : '' ?>>📦 Autre</option>
                    </select>
                </div>

                <!-- Empreinte Carbone -->
                <div class="form-group">
                    <label for="empreinteCarbone" class="required">Empreinte Carbone (kg CO₂)</label>
                    <input type="number" 
                           name="empreinteCarbone" 
                           id="empreinteCarbone" 
                           step="0.01" 
                           min="0" 
                           required 
                           placeholder="Ex: 2.5"
                           value="<?= htmlspecialchars($produit['empreinteCarbone']) ?>">
                    <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                        🌍 Entrez l'empreinte carbone en kilogrammes de CO₂ équivalent
                    </small>
                </div>

                <button type="submit" class="btn">
                    💾 Mettre à jour le Produit
                </button>
            </form>

            <!-- Guide d'empreinte carbone -->
            <div class="impact-info">
                <h4>📊 Guide d'empreinte carbone</h4>
                <ul>
                    <li>🟢 <strong>Faible (0-5 kg CO₂)</strong> : Produits locaux, réutilisables, faibles impacts</li>
                    <li>🟡 <strong>Moyen (5-15 kg CO₂)</strong> : Produits standards, transformation modérée</li>
                    <li>🔴 <strong>Élevé (+15 kg CO₂)</strong> : Produits importés, électroniques, haute énergie</li>
                </ul>
            </div>

            <div class="form-footer">
                <a href="listproduit.php">← Retour à la liste des produits</a>
            </div>
        </div>
    </div>

    <script>
        // Validation côté client
        document.getElementById('produitForm').addEventListener('submit', function(e) {
            const nom = document.getElementById('nom').value;
            const categorie = document.getElementById('categorie').value;
            const empreinte = document.getElementById('empreinteCarbone').value;

            let errors = [];

            if (!nom.trim()) {
                errors.push("Le nom du produit est obligatoire");
            } else if (nom.trim().length < 2) {
                errors.push("Le nom doit contenir au moins 2 caractères");
            }

            if (!categorie) {
                errors.push("Veuillez sélectionner une catégorie");
            }

            if (!empreinte || parseFloat(empreinte) < 0) {
                errors.push("L'empreinte carbone doit être un nombre positif");
            }

            if (errors.length > 0) {
                e.preventDefault();
                alert("Veuillez corriger les erreurs suivantes :\n\n" + errors.join('\n'));
            }
        });

        // Focus sur le premier champ
        document.getElementById('nom').focus();
    </script>
</body>
</html>