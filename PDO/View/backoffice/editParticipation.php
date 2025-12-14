<?php
// editParticipation.php - Formulaire de modification de participation
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

// RÉCUPÉRER LES ÉVÉNEMENTS POUR LE SELECT
$evenements = [];
try {
    $query = "SELECT idEvenement, titre, description, statut 
              FROM evenement 
              WHERE statut IN ('ouverte', 'en_cours') 
              ORDER BY titre ASC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $evenements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $evenements = [];
}

// Variables
$message = '';
$participation = null;
$idParticipation = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : 0;

// RÉCUPÉRER LA PARTICIPATION À MODIFIER SI ID EST PRÉSENT
if ($idParticipation > 0) {
    try {
        $query = "SELECT p.*, e.titre as event_titre 
                  FROM participation p
                  LEFT JOIN evenement e ON p.idEvenement = e.idEvenement
                  WHERE p.idParticipation = :id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':id' => $idParticipation]);
        $participation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$participation) {
            $message = 'Participation non trouvée';
        }
    } catch(PDOException $e) {
        $message = 'Erreur lors du chargement de la participation';
    }
}

// Gestion du formulaire
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $idParticipation = $_POST['idParticipation'] ?? 0;
    $idEvenement = $_POST['idEvenement'] ?? '';
    $contenu = trim($_POST['contenu'] ?? '');
    $dateParticipation = $_POST['dateParticipation'] ?? '';
    
    if(!empty($idEvenement) && !empty($contenu) && !empty($dateParticipation)){
        try {
            // METTRE À JOUR LA PARTICIPATION
            $query = "UPDATE participation 
                      SET idEvenement = :idEvenement, 
                          contenu = :contenu, 
                          dateParticipation = :dateParticipation
                      WHERE idParticipation = :idParticipation";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':idEvenement' => $idEvenement,
                ':contenu' => $contenu,
                ':dateParticipation' => $dateParticipation,
                ':idParticipation' => $idParticipation
            ]);
            
            $message = 'modifier avec success';
            
            // Recharger les données après modification
            $query = "SELECT p.*, e.titre as event_titre 
                      FROM participation p
                      LEFT JOIN evenement e ON p.idEvenement = e.idEvenement
                      WHERE p.idParticipation = :id";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':id' => $idParticipation]);
            $participation = $stmt->fetch(PDO::FETCH_ASSOC);
            
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
    <title><?= $participation ? 'Modifier' : 'Ajouter' ?> Participation - EcoTrack</title>
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

        input[type="datetime-local"].form-control {
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
                <span><?= $participation ? 'Modifier Participation' : 'Nouvelle Participation' ?></span>
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
            <h2><?= $participation ? 'Modifier la Participation' : 'Ajouter une Participation' ?></h2>
            <p><?= $participation ? 'Modifiez les informations de la participation' : 'Remplissez tous les champs requis' ?></p>
        </div>

        <form method="POST" id="participationForm">
            <!-- CHAMP CACHÉ POUR L'ID DE PARTICIPATION -->
            <?php if ($participation): ?>
            <input type="hidden" name="idParticipation" value="<?= $participation['idParticipation'] ?>">
            <?php endif; ?>

            <!-- ATTRIBUT 1: idEvenement -->
            <div class="form-group">
                <label class="form-label">
                    Événement *
                </label>
                <select name="idEvenement" id="idEvenement" class="form-control" required>
                    <option value="">Sélectionner un événement</option>
                    <?php foreach ($evenements as $event): ?>
                        <option value="<?= htmlspecialchars($event['idEvenement']) ?>"
                            <?= ($participation && $participation['idEvenement'] == $event['idEvenement']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($event['titre']) ?> 
                            (<?= htmlspecialchars($event['statut'] === 'ouverte' ? 'Ouvert' : 'En cours') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- ATTRIBUT 2: contenu -->
            <div class="form-group">
                <label class="form-label">
                    Message de Participation *
                </label>
                <textarea name="contenu" id="contenu" class="form-control"
                          placeholder="Exprimez votre motivation pour participer à cet événement..."
                          rows="6" required><?= $participation ? htmlspecialchars($participation['contenu']) : '' ?></textarea>
                <div class="char-counter" id="contenuCounter">0 caractères (minimum 10)</div>
            </div>

            <!-- ATTRIBUT 3: dateParticipation -->
            <div class="form-group">
                <label class="form-label">
                    Date de Participation *
                </label>
                <?php 
                // Formater la date pour l'input datetime-local
                $dateValue = '';
                if ($participation && !empty($participation['dateParticipation'])) {
                    $dateValue = date('Y-m-d\TH:i', strtotime($participation['dateParticipation']));
                } else {
                    $dateValue = date('Y-m-d\TH:i');
                }
                ?>
                <input type="datetime-local" 
                       name="dateParticipation" 
                       id="dateParticipation" 
                       class="form-control" 
                       value="<?= $dateValue ?>"
                       required>
            </div>

            <!-- ACTIONS DU FORMULAIRE -->
            <div class="form-actions">
                <a href="listParticipation.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i>
                    Retour à la liste
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-<?= $participation ? 'save' : 'paper-plane' ?>"></i>
                    <?= $participation ? 'Modifier' : 'Envoyer' ?>
                </button>
            </div>
        </form>
    </div>

    <!-- JAVASCRIPT -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('participationForm');
            const contenuTextarea = document.getElementById('contenu');
            const contenuCounter = document.getElementById('contenuCounter');

            // Compteur de caractères
            function updateContenuCounter() {
                const length = contenuTextarea.value.length;
                contenuCounter.textContent = `${length} caractères (minimum 10)`;
                
                if (length < 10) {
                    contenuCounter.classList.add('danger');
                    contenuCounter.classList.remove('warning');
                } else if (length < 50) {
                    contenuCounter.classList.add('warning');
                    contenuCounter.classList.remove('danger');
                } else {
                    contenuCounter.classList.remove('warning', 'danger');
                }
            }

            contenuTextarea.addEventListener('input', updateContenuCounter);
            updateContenuCounter();

            // Validation
            form.addEventListener('submit', function(e) {
                const idEvenement = document.getElementById('idEvenement').value;
                const contenu = contenuTextarea.value.trim();
                const dateParticipation = document.getElementById('dateParticipation').value;
                
                if(!idEvenement || !contenu || !dateParticipation) {
                    e.preventDefault();
                    alert('Veuillez remplir tous les champs');
                    return false;
                }
                
                if(contenu.length < 10) {
                    e.preventDefault();
                    alert('Le message doit contenir au moins 10 caractères');
                    return false;
                }
            });
        });
    </script>
</body>
</html>