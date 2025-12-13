<?php
require_once "C:/xampppp/htdocs/ecotrack/Database.php";
require_once "C:/xampppp/htdocs/ecotrack/Controller/ProduitController.php";

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = Database::getConnexion();
    
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
        
        $result = ProduitController::addProduit($conn, $data);
        
        // Vérifier que $result est un tableau avant d'y accéder
        if (is_array($result) && isset($result['ok'])) {
            if ($result['ok']) {
                header("Location: listproduit.php?success=1");
                exit;
            } else {
                $message = $result['msg'] ?? "Erreur lors de l'ajout du produit";
                $message_type = 'error';
            }
        } else {
            // Si $result n'est pas un tableau (retourne un booléen par exemple)
            if ($result) {
                header("Location: listproduit.php?success=1");
                exit;
            } else {
                $message = "Erreur lors de l'ajout du produit";
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
    <title>Ajouter un Produit - EcoTrack</title>
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

        .form-group input.error,
        .form-group select.error {
            border-color: #e74c3c;
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
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

        .btn:disabled {
            background: #bdc3c7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
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

        .message.success {
            background: #e6f7e6;
            color: #27ae60;
            border: 1px solid #2ecc71;
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

        .form-footer a:hover {
            text-decoration: underline;
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

        .error-text {
            color: #e74c3c;
            font-size: 12px;
            margin-top: 5px;
            display: block;
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
            <h2>📦 Ajouter un Produit</h2>
            <p>Enregistrez un nouveau produit dans le système EcoTrack</p>
        </div>

        <div class="form-body">
            <?php if ($message): ?>
                <div class="message <?= $message_type ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="produitForm" novalidate>
                <!-- Nom du produit -->
                <div class="form-group">
                    <label for="nom" class="required">Nom du produit</label>
                    <input type="text" 
                           name="nom" 
                           id="nom" 
                           required 
                           placeholder="Ex: Téléphone portable, Bouteille en plastique..."
                           value="<?= isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : '' ?>"
                           maxlength="255"
                           class="<?= (isset($errors) && in_array('Le nom du produit est obligatoire', $errors)) ? 'error' : '' ?>">
                    <?php if (isset($errors) && in_array('Le nom du produit est obligatoire', $errors)): ?>
                        <span class="error-text">Le nom du produit est obligatoire</span>
                    <?php endif; ?>
                </div>

                <!-- Catégorie -->
                <div class="form-group">
                    <label for="categorie" class="required">Catégorie</label>
                    <select name="categorie" id="categorie" required
                            class="<?= (isset($errors) && in_array('La catégorie est obligatoire', $errors)) ? 'error' : '' ?>">
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
                    <?php if (isset($errors) && in_array('La catégorie est obligatoire', $errors)): ?>
                        <span class="error-text">La catégorie est obligatoire</span>
                    <?php endif; ?>
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
                           value="<?= isset($_POST['empreinteCarbone']) ? htmlspecialchars($_POST['empreinteCarbone']) : '' ?>"
                           class="<?= (isset($errors) && in_array('L\'empreinte carbone doit être un nombre positif', $errors)) ? 'error' : '' ?>">
                    <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                        🌍 Entrez l'empreinte carbone en kilogrammes de CO₂ équivalent
                    </small>
                    <?php if (isset($errors) && in_array('L\'empreinte carbone doit être un nombre positif', $errors)): ?>
                        <span class="error-text">L'empreinte carbone doit être un nombre positif</span>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn" id="submitBtn">
                    💾 Enregistrer le Produit
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
                <p style="margin-top: 10px; font-size: 12px; color: #666;">
                    <strong>Exemples :</strong> Téléphone (45 kg) • T-shirt coton (8 kg) • Bouteille plastique (2 kg)
                </p>
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
            const submitBtn = document.getElementById('submitBtn');

            let errors = [];
            let isValid = true;

            // Reset des erreurs visuelles
            document.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
            document.querySelectorAll('.error-text').forEach(el => el.remove());

            if (!nom.trim()) {
                errors.push("Le nom du produit est obligatoire");
                document.getElementById('nom').classList.add('error');
                isValid = false;
            } else if (nom.trim().length < 2) {
                errors.push("Le nom doit contenir au moins 2 caractères");
                document.getElementById('nom').classList.add('error');
                isValid = false;
            }

            if (!categorie) {
                errors.push("Veuillez sélectionner une catégorie");
                document.getElementById('categorie').classList.add('error');
                isValid = false;
            }

            if (!empreinte || isNaN(empreinte) || parseFloat(empreinte) < 0) {
                errors.push("L'empreinte carbone doit être un nombre positif");
                document.getElementById('empreinteCarbone').classList.add('error');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
                
                // Afficher les erreurs
                const errorContainer = document.createElement('div');
                errorContainer.className = 'message error';
                errorContainer.innerHTML = '<strong>Veuillez corriger les erreurs suivantes :</strong><br>' + errors.join('<br>');
                
                const existingMessage = document.querySelector('.message');
                if (existingMessage) {
                    existingMessage.remove();
                }
                
                document.querySelector('.form-body').insertBefore(errorContainer, document.getElementById('produitForm'));
                
                // Scroll to top
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                // Désactiver le bouton pendant l'envoi
                submitBtn.disabled = true;
                submitBtn.innerHTML = '⏳ Enregistrement en cours...';
            }
        });

        // Calcul automatique de suggestions basées sur la catégorie
        document.getElementById('categorie').addEventListener('change', function() {
            const categorie = this.value;
            const empreinteInput = document.getElementById('empreinteCarbone');
            const suggestions = {
                'Électronique': '45.50',
                'Textile': '8.75',
                'Alimentation': '3.20',
                'Emballage': '2.30',
                'Mobilier': '25.00',
                'Transport': '120.00',
                'Énergie': '50.00',
                'Construction': '85.00'
            };

            if (suggestions[categorie] && !empreinteInput.value) {
                empreinteInput.placeholder = `Suggestion: ${suggestions[categorie]} kg CO₂`;
            }
        });

        // Validation en temps réel
        document.querySelectorAll('#produitForm input, #produitForm select').forEach(element => {
            element.addEventListener('input', function() {
                this.classList.remove('error');
                const errorText = this.parentNode.querySelector('.error-text');
                if (errorText) {
                    errorText.remove();
                }
            });
        });

        // Focus sur le premier champ
        document.getElementById('nom').focus();
    </script>
</body>
</html>