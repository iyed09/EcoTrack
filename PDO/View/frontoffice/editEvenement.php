<?php
// editEvenement.php - Formulaire de modification d'événement
session_start();

// CONNEXION À LA BASE DE DONNÉES
$host = 'localhost';
$dbname = 'smartinnovators';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch(PDOException $e) {
    die('Erreur de connexion: ' . $e->getMessage());
}

// Variables
$message = '';
$evenement = null;
$idEvenement = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : 0;

// RÉCUPÉRER L'ÉVÉNEMENT À MODIFIER SI ID EST PRÉSENT
if ($idEvenement > 0) {
    try {
        $query = "SELECT idEvenement, titre, description, statut 
                  FROM evenement 
                  WHERE idEvenement = :id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':id' => $idEvenement]);
        $evenement = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$evenement) {
            $message = 'Événement non trouvé';
        }
    } catch(PDOException $e) {
        $message = 'Erreur lors du chargement de l\'événement';
    }
}

// Gestion du formulaire
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $idEvenement = $_POST['idEvenement'] ?? 0;
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $statut = $_POST['statut'] ?? 'ouverte';
    
    if(!empty($titre) && !empty($description) && !empty($statut)){
        try {
            // METTRE À JOUR L'ÉVÉNEMENT
            $query = "UPDATE evenement 
                      SET titre = :titre, 
                          description = :description, 
                          statut = :statut
                      WHERE idEvenement = :idEvenement";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':titre' => $titre,
                ':description' => $description,
                ':statut' => $statut,
                ':idEvenement' => $idEvenement
            ]);
            
            $message = 'modifier avec success';
            
            // Recharger les données après modification
            $query = "SELECT idEvenement, titre, description, statut 
                      FROM evenement 
                      WHERE idEvenement = :id";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':id' => $idEvenement]);
            $evenement = $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch(PDOException $e) {
            $message = 'Erreur lors de la modification';
        }
    } else {
        $message = 'Veuillez remplir tous les champs';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $evenement ? 'Modifier' : 'Ajouter' ?> Événement - EcoTrack</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2ecc71;
            --primary-light: rgba(46, 204, 113, 0.1);
            --primary-dark: #27ae60;
            --secondary: #3498db;
            --accent: #9b59b6;
            --success: #2ecc71;
            --warning: #f39c12;
            --danger: #e74c3c;
            --dark: #1a252f;
            --light: #ffffff;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            --radius: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        body {
            background: linear-gradient(135deg, var(--gray-50) 0%, #f0f7ff 100%);
            color: var(--gray-800);
            line-height: 1.5;
            font-size: 0.875rem;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }

        /* LOGO */
        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            margin-bottom: 2rem;
            justify-content: center;
        }

        .logo-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            box-shadow: var(--shadow);
        }

        .logo-text h1 {
            font-size: 1.75rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.25rem;
        }

        .logo-text span {
            font-size: 0.875rem;
            color: var(--gray-500);
            font-weight: 500;
        }

        /* FORM BOX */
        .form-box {
            background: white;
            border-radius: var(--radius-xl);
            padding: 3rem;
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--gray-200);
            width: 100%;
            max-width: 700px;
            position: relative;
            overflow: hidden;
        }

        .form-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .form-title {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .form-title h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }

        .form-title p {
            color: var(--gray-500);
            font-size: 0.875rem;
        }

        /* FORM GROUPS */
        .form-group {
            margin-bottom: 1.75rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.75rem;
            font-weight: 600;
            color: var(--gray-700);
            font-size: 0.875rem;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius);
            font-size: 0.875rem;
            transition: var(--transition);
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.1);
        }

        textarea.form-control {
            min-height: 150px;
            resize: vertical;
            line-height: 1.5;
        }

        select.form-control {
            cursor: pointer;
        }

        /* BOUTONS */
        .btn {
            padding: 0.875rem 1.75rem;
            border: none;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }

        .btn-block {
            width: 100%;
            justify-content: center;
        }

        /* MESSAGES */
        .notification {
            padding: 1rem 1.5rem;
            border-radius: var(--radius);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideIn 0.3s ease-out;
        }

        .notification.success {
            background: rgba(46, 204, 113, 0.1);
            color: var(--success);
            border: 1px solid rgba(46, 204, 113, 0.2);
        }

        .notification.error {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger);
            border: 1px solid rgba(231, 76, 60, 0.2);
        }

        /* CHAR COUNTER */
        .char-counter {
            text-align: right;
            font-size: 0.75rem;
            color: var(--gray-500);
            margin-top: 0.5rem;
        }

        .char-counter.warning {
            color: var(--warning);
        }

        .char-counter.danger {
            color: var(--danger);
        }

        /* FORM ACTIONS */
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        /* ANIMATIONS */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            
            .form-box {
                padding: 2rem;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="form-box">
        <!-- LOGO -->
        <div class="logo">
            <div class="logo-icon">
                <i class="fas fa-leaf"></i>
            </div>
            <div class="logo-text">
                <h1>EcoTrack</h1>
                <span><?= $evenement ? 'Modifier Événement' : 'Nouvel Événement' ?></span>
            </div>
        </div>

        <!-- MESSAGE -->
        <?php if(!empty($message)): ?>
        <div class="notification <?= strpos($message, 'succes') !== false ? 'success' : 'error' ?>">
            <i class="fas fa-<?= strpos($message, 'succes') !== false ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>

        <!-- FORMULAIRE -->
        <div class="form-title">
            <h2><?= $evenement ? 'Modifier l\'Événement' : 'Ajouter un Événement' ?></h2>
            <p><?= $evenement ? 'Modifiez les informations de l\'événement' : 'Remplissez tous les champs requis' ?></p>
        </div>

        <form method="POST" id="evenementForm">
            <!-- CHAMP CACHÉ POUR L'ID D'ÉVÉNEMENT -->
            <?php if ($evenement): ?>
            <input type="hidden" name="idEvenement" value="<?= $evenement['idEvenement'] ?>">
            <?php endif; ?>

            <!-- ATTRIBUT 1: titre -->
            <div class="form-group">
                <label class="form-label">
                    Titre *
                </label>
                <input type="text" 
                       name="titre" 
                       id="titre" 
                       class="form-control" 
                       placeholder="Titre de l'événement"
                       value="<?= $evenement ? htmlspecialchars($evenement['titre']) : '' ?>"
                       required
                       maxlength="100">
                <div class="char-counter" id="titreCounter">0/100 caractères</div>
            </div>

            <!-- ATTRIBUT 2: description -->
            <div class="form-group">
                <label class="form-label">
                    Description *
                </label>
                <textarea name="description" 
                          id="description" 
                          class="form-control" 
                          placeholder="Description de l'événement..."
                          rows="6" required><?= $evenement ? htmlspecialchars($evenement['description']) : '' ?></textarea>
                <div class="char-counter" id="descriptionCounter">0 caractères (minimum 20)</div>
            </div>

            <!-- ATTRIBUT 3: statut -->
            <div class="form-group">
                <label class="form-label">
                    Statut *
                </label>
                <select name="statut" id="statut" class="form-control" required>
                    <option value="">Sélectionner un statut</option>
                    <option value="ouverte" <?= ($evenement && $evenement['statut'] == 'ouverte') ? 'selected' : '' ?>>Ouvert aux inscriptions</option>
                    <option value="en_cours" <?= ($evenement && $evenement['statut'] == 'en_cours') ? 'selected' : '' ?>>En cours</option>
                    <option value="resolue" <?= ($evenement && $evenement['statut'] == 'resolue') ? 'selected' : '' ?>>Terminé</option>
                </select>
            </div>

            <!-- ACTIONS DU FORMULAIRE -->
            <div class="form-actions">
                <a href="listEvenement.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i>
                    Retour à la liste
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-<?= $evenement ? 'save' : 'paper-plane' ?>"></i>
                    <?= $evenement ? 'Modifier' : 'Envoyer' ?>
                </button>
            </div>
        </form>
    </div>

    <!-- JAVASCRIPT -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('evenementForm');
            const titreInput = document.getElementById('titre');
            const descriptionInput = document.getElementById('description');
            const titreCounter = document.getElementById('titreCounter');
            const descriptionCounter = document.getElementById('descriptionCounter');

            // Compteurs de caractères
            function updateCounters() {
                const titreLength = titreInput.value.length;
                const descriptionLength = descriptionInput.value.length;
                
                // Titre counter
                titreCounter.textContent = `${titreLength}/100 caractères`;
                if (titreLength > 90) {
                    titreCounter.classList.add('warning');
                    titreCounter.classList.remove('danger');
                } else if (titreLength > 95) {
                    titreCounter.classList.add('danger');
                    titreCounter.classList.remove('warning');
                } else {
                    titreCounter.classList.remove('warning', 'danger');
                }
                
                // Description counter
                descriptionCounter.textContent = `${descriptionLength} caractères (minimum 20)`;
                if (descriptionLength < 20) {
                    descriptionCounter.classList.add('danger');
                    descriptionCounter.classList.remove('warning');
                } else if (descriptionLength < 50) {
                    descriptionCounter.classList.add('warning');
                    descriptionCounter.classList.remove('danger');
                } else {
                    descriptionCounter.classList.remove('warning', 'danger');
                }
            }

            titreInput.addEventListener('input', updateCounters);
            descriptionInput.addEventListener('input', updateCounters);
            updateCounters();

            // Validation
            form.addEventListener('submit', function(e) {
                const titre = titreInput.value.trim();
                const description = descriptionInput.value.trim();
                const statut = document.getElementById('statut').value;
                
                if(!titre || !description || !statut) {
                    e.preventDefault();
                    alert('Veuillez remplir tous les champs');
                    return false;
                }
                
                if(titre.length < 3) {
                    e.preventDefault();
                    alert('Le titre doit contenir au moins 3 caractères');
                    return false;
                }
                
                if(description.length < 20) {
                    e.preventDefault();
                    alert('La description doit contenir au moins 20 caractères');
                    return false;
                }
            });
        });
    </script>
</body>
</html>