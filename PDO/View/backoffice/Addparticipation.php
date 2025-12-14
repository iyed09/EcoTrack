<?php
// AddParticipation.php - Formulaire d'ajout de participation
session_start();

// Variables
$message = '';
$evenements = [];

// CONNEXION À LA BASE DE DONNÉES
$host = 'localhost';
$dbname = 'smartinnovators'; // Nom de votre base de données
$username = 'root'; // Changez selon votre configuration
$password = ''; // Changez selon votre configuration

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<!-- Connexion à la base de données $dbname réussie -->";
} catch(PDOException $e) {
    echo "<!-- Erreur de connexion: " . $e->getMessage() . " -->";
    $message = 'Erreur de connexion à la base de données: ' . $e->getMessage();
}

// RÉCUPÉRER LES ÉVÉNEMENTS DE LA BASE DE DONNÉES
if(isset($pdo) && empty($message)) {
    try {
        // Vérifier d'abord si la table evenement existe
        $checkTable = $pdo->query("SHOW TABLES LIKE 'evenement'");
        $tableExists = $checkTable->rowCount() > 0;
        
        if ($tableExists) {
            $query = "SELECT idEvenement, titre, description, statut 
                      FROM evenement 
                      WHERE statut IN ('ouverte', 'en_cours') 
                      ORDER BY titre ASC";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $evenements = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<!-- Nombre d'événements trouvés: " . count($evenements) . " -->";
            
            // Debug: Afficher les événements trouvés
            if(count($evenements) > 0) {
                echo "<!-- Événements trouvés: -->";
                foreach($evenements as $event) {
                    echo "<!-- ID: " . $event['idEvenement'] . " - Titre: " . $event['titre'] . " - Statut: " . $event['statut'] . " -->";
                }
            } else {
                echo "<!-- Aucun événement avec statut 'ouverte' ou 'en_cours' trouvé -->";
            }
        } else {
            echo "<!-- La table 'evenement' n'existe pas dans la base de données -->";
            $message = "La table 'evenement' n'existe pas dans la base de données.";
        }
        
    } catch(PDOException $e) {
        echo "<!-- Erreur lors de la récupération des événements: " . $e->getMessage() . " -->";
        $message = 'Erreur lors de la récupération des événements: ' . $e->getMessage();
        $evenements = [];
    }
} else {
    echo "<!-- PDO n'est pas défini ou il y a une erreur de connexion -->";
}

// Gestion du formulaire
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($pdo) && empty($message)){
    $idEvenement = $_POST['idEvenement'] ?? '';
    $contenu = trim($_POST['contenu'] ?? '');
    $dateParticipation = $_POST['dateParticipation'] ?? '';
    
    if(!empty($idEvenement) && !empty($contenu) && !empty($dateParticipation)){
        // Vérifier si la table participation existe
        try {
            $checkParticipationTable = $pdo->query("SHOW TABLES LIKE 'participation'");
            $participationTableExists = $checkParticipationTable->rowCount() > 0;
            
            if (!$participationTableExists) {
                // Créer la table participation si elle n'existe pas
                $pdo->exec("CREATE TABLE IF NOT EXISTS participation (
                    idParticipation INT PRIMARY KEY AUTO_INCREMENT,
                    idEvenement INT NOT NULL,
                    contenu TEXT NOT NULL,
                    dateParticipation DATETIME NOT NULL,
                    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )");
                echo "<!-- Table 'participation' créée -->";
            }
            
            // INSÉRER DANS LA BASE DE DONNÉES
            $query = "INSERT INTO participation (idEvenement, contenu, dateParticipation) 
                      VALUES (:idEvenement, :contenu, :dateParticipation)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':idEvenement' => $idEvenement,
                ':contenu' => $contenu,
                ':dateParticipation' => $dateParticipation
            ]);
            
            $message = 'envoyer avec success';
            
            // Réinitialiser les champs après succès
            $_POST = [];
            
        } catch(PDOException $e) {
            echo "<!-- Erreur d'insertion: " . $e->getMessage() . " -->";
            $message = 'Erreur lors de l\'enregistrement: ' . $e->getMessage();
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
    <title>Ajouter Participation - EcoTrack</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS SIMPLIFIÉ POUR PARTICIPATION */
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

        /* LOGO IDENTIQUE À EVENEMENT */
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
            max-width: 800px;
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
            background: linear-gradient(90deg, var(--secondary), var(--accent));
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

        .required::after {
            content: " *";
            color: var(--danger);
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
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
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
            background: linear-gradient(135deg, var(--secondary), #2980b9);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
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

        .notification.info {
            background: rgba(52, 152, 219, 0.1);
            color: var(--secondary);
            border: 1px solid rgba(52, 152, 219, 0.2);
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

        /* ERROR STYLES */
        .field-error {
            border-color: var(--danger) !important;
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1) !important;
        }

        .error-message {
            color: var(--danger);
            font-size: 0.75rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        /* ANIMATIONS */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

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
                <span>Nouvelle Participation</span>
            </div>
        </div>

        <!-- MESSAGE DE SUCCÈS OU ERREUR -->
        <?php if(!empty($message)): ?>
        <div class="notification <?= strpos($message, 'success') !== false ? 'success' : (strpos($message, 'Erreur') !== false ? 'error' : 'info') ?>">
            <i class="fas fa-<?= strpos($message, 'success') !== false ? 'check-circle' : (strpos($message, 'Erreur') !== false ? 'exclamation-circle' : 'info-circle') ?>"></i>
            <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>

        <!-- INFO SI AUCUN ÉVÉNEMENT -->
        <?php if(isset($pdo) && empty($message) && empty($evenements)): ?>
        <div class="notification info">
            <i class="fas fa-info-circle"></i>
            Aucun événement actif trouvé dans la base de données. Créez d'abord des événements.
        </div>
        <?php endif; ?>

        <!-- FORMULAIRE -->
        <div class="form-title">
            <h2>Ajouter une Participation</h2>
            <p>Participez à un événement écologique et partagez vos idées</p>
        </div>

        <form method="POST" id="participationForm">
            <!-- ATTRIBUT 1: Événement -->
            <div class="form-group">
                <label class="form-label required">
                    Événement
                </label>
                <select name="idEvenement" id="eventSelect" class="form-control" required <?= (empty($evenements) || !empty($message)) ? 'disabled' : '' ?>>
                    <option value="">-- Choisir un événement --</option>
                    <?php if (!empty($evenements)): ?>
                        <?php foreach ($evenements as $evenement): ?>
                            <option value="<?= htmlspecialchars($evenement['idEvenement']) ?>">
                                <?= htmlspecialchars($evenement['titre']) ?> 
                                (<?= htmlspecialchars($evenement['statut'] === 'ouverte' ? 'Ouvert' : 'En cours') ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>Aucun événement disponible</option>
                    <?php endif; ?>
                </select>
            </div>
            
            <!-- ATTRIBUT 2: Contenu -->
            <div class="form-group">
                <label class="form-label required">
                    Contenu
                </label>
                <textarea name="contenu" id="contenu" class="form-control"
                          placeholder="Partagez vos idées, suggestions ou questions..."
                          rows="6" required <?= (!empty($message) && strpos($message, 'Erreur') !== false) ? 'disabled' : '' ?>>
                    <?= isset($_POST['contenu']) ? htmlspecialchars($_POST['contenu']) : '' ?>
                </textarea>
                <div class="char-counter" id="contenuCounter">0 caractères (minimum 20)</div>
            </div>
            
            <!-- ATTRIBUT 3: Date de participation -->
            <div class="form-group">
                <label class="form-label required">
                    Date de participation
                </label>
                <input type="datetime-local" name="dateParticipation" id="dateParticipation" 
                       class="form-control" 
                       value="<?= isset($_POST['dateParticipation']) ? htmlspecialchars($_POST['dateParticipation']) : '' ?>"
                       required <?= (!empty($message) && strpos($message, 'Erreur') !== false) ? 'disabled' : '' ?>>
            </div>

            <!-- BOUTON ENVOYER -->
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block" <?= (!empty($message) && strpos($message, 'Erreur') !== false) ? 'disabled' : '' ?>>
                    <i class="fas fa-paper-plane"></i>
                    Envoyer
                </button>
            </div>
        </form>
    </div>

    <!-- JAVASCRIPT -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('participationForm');
            const eventSelect = document.getElementById('eventSelect');
            const contenuInput = document.getElementById('contenu');
            const dateInput = document.getElementById('dateParticipation');
            const contenuCounter = document.getElementById('contenuCounter');

            // Compteur de caractères
            function updateCounter() {
                const length = contenuInput.value.length;
                contenuCounter.textContent = `${length} caractères (minimum 20)`;
                
                if (length < 20) {
                    contenuCounter.classList.add('danger');
                    contenuCounter.classList.remove('warning');
                } else if (length < 100) {
                    contenuCounter.classList.add('warning');
                    contenuCounter.classList.remove('danger');
                } else {
                    contenuCounter.classList.remove('warning', 'danger');
                }
            }

            contenuInput.addEventListener('input', updateCounter);
            updateCounter();

            // Date par défaut si vide
            if (!dateInput.value) {
                const now = new Date();
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const day = String(now.getDate()).padStart(2, '0');
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                
                dateInput.value = `${year}-${month}-${day}T${hours}:${minutes}`;
            }

            // Validation du formulaire
            function validateForm() {
                let isValid = true;

                // Validation événement
                if (!eventSelect.value) {
                    isValid = false;
                    highlightError(eventSelect, true);
                } else {
                    highlightError(eventSelect, false);
                }

                // Validation contenu
                if (!contenuInput.value.trim()) {
                    isValid = false;
                    highlightError(contenuInput, true);
                } else if (contenuInput.value.trim().length < 20) {
                    isValid = false;
                    highlightError(contenuInput, true);
                } else {
                    highlightError(contenuInput, false);
                }

                // Validation date
                if (!dateInput.value) {
                    isValid = false;
                    highlightError(dateInput, true);
                } else {
                    highlightError(dateInput, false);
                }

                return isValid;
            }

            function highlightError(element, isError) {
                if (isError) {
                    element.classList.add('field-error');
                    
                    element.animate([
                        { transform: 'translateX(0)' },
                        { transform: 'translateX(-5px)' },
                        { transform: 'translateX(5px)' },
                        { transform: 'translateX(0)' }
                    ], {
                        duration: 300,
                        iterations: 1
                    });
                } else {
                    element.classList.remove('field-error');
                }
            }

            // Soumission du formulaire
            form.addEventListener('submit', function(e) {
                if (!validateForm()) {
                    e.preventDefault();
                    return false;
                }
            });

            // Validation en temps réel
            eventSelect.addEventListener('blur', () => validateForm());
            contenuInput.addEventListener('blur', () => validateForm());
            dateInput.addEventListener('change', () => validateForm());
        });
    </script>
</body>
</html>