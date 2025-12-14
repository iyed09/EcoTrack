<?php
// AddEvenement.php - Formulaire d'ajout d'événement
session_start();

// Variables
$is_edit = false;
$message = '';

// Gestion du formulaire
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $statut = $_POST['statut'] ?? 'ouverte';
    
    if(!empty($titre) && !empty($description) && !empty($statut)){
        // SIMPLE MESSAGE DE SUCCÈS
        $message = 'envoyer avec success';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter Événement - EcoTrack</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS SIMPLIFIÉ */
        :root {
            --primary: #2ecc71;
            --primary-dark: #27ae60;
            --secondary: #3498db;
            --danger: #e74c3c;
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
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            --radius: 0.5rem;
            --radius-xl: 1rem;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: linear-gradient(135deg, var(--gray-50) 0%, #f0f7ff 100%);
            color: var(--gray-800);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }

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

        .form-box {
            background: white;
            border-radius: var(--radius-xl);
            padding: 3rem;
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--gray-200);
            width: 100%;
            max-width: 600px;
            position: relative;
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

        .btn-block {
            width: 100%;
            justify-content: center;
        }

        .success-message {
            background: rgba(46, 204, 113, 0.1);
            color: #27ae60;
            padding: 1rem 1.5rem;
            border-radius: var(--radius);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border: 1px solid rgba(46, 204, 113, 0.2);
            animation: fadeIn 0.5s ease;
        }

        .char-counter {
            text-align: right;
            font-size: 0.75rem;
            color: var(--gray-500);
            margin-top: 0.5rem;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

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
                <span>Nouvel Événement</span>
            </div>
        </div>

        <!-- MESSAGE DE SUCCÈS -->
        <?php if(!empty($message)): ?>
        <div class="success-message">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>

        <!-- FORMULAIRE -->
        <div class="form-title">
            <h2>Créer un Nouvel Événement</h2>
            <p>Remplissez tous les champs requis</p>
        </div>

        <form method="POST" id="eventForm">
            <!-- Titre -->
            <div class="form-group">
                <label class="form-label">
                    Titre *
                </label>
                <input type="text" 
                       name="titre" 
                       id="titre" 
                       class="form-control" 
                       placeholder="Titre de l'événement"
                       value=""
                       required
                       maxlength="100">
                <div class="char-counter" id="titreCounter">0/100 caractères</div>
            </div>

            <!-- Description -->
            <div class="form-group">
                <label class="form-label">
                    Description *
                </label>
                <textarea name="description" 
                          id="description" 
                          class="form-control" 
                          placeholder="Description..."
                          required></textarea>
                <div class="char-counter" id="descriptionCounter">0 caractères</div>
            </div>

            <!-- Statut -->
            <div class="form-group">
                <label class="form-label">
                    Statut *
                </label>
                <select name="statut" id="statut" class="form-control" required>
                    <option value="">Sélectionner</option>
                    <option value="ouverte">Ouvert</option>
                    <option value="en_cours">En cours</option>
                    <option value="resolue">Terminé</option>
                </select>
            </div>

            <!-- Bouton Envoyer -->
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-paper-plane"></i>
                    Envoyer
                </button>
            </div>
        </form>
    </div>

    <!-- JAVASCRIPT SIMPLE -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const titreInput = document.getElementById('titre');
            const descriptionInput = document.getElementById('description');
            const titreCounter = document.getElementById('titreCounter');
            const descriptionCounter = document.getElementById('descriptionCounter');

            // Compteurs de caractères
            function updateCounters() {
                const titreLength = titreInput.value.length;
                const descriptionLength = descriptionInput.value.length;
                
                titreCounter.textContent = `${titreLength}/100 caractères`;
                descriptionCounter.textContent = `${descriptionLength} caractères`;
            }

            titreInput.addEventListener('input', updateCounters);
            descriptionInput.addEventListener('input', updateCounters);
            updateCounters();

            // Validation simple
            const form = document.getElementById('eventForm');
            form.addEventListener('submit', function(e) {
                let isValid = true;

                if (!titreInput.value.trim()) {
                    isValid = false;
                    highlightError(titreInput, true);
                } else {
                    highlightError(titreInput, false);
                }

                if (!descriptionInput.value.trim()) {
                    isValid = false;
                    highlightError(descriptionInput, true);
                } else {
                    highlightError(descriptionInput, false);
                }

                if (!document.getElementById('statut').value) {
                    isValid = false;
                    highlightError(document.getElementById('statut'), true);
                } else {
                    highlightError(document.getElementById('statut'), false);
                }

                if (!isValid) {
                    e.preventDefault();
                }
            });

            function highlightError(element, isError) {
                if (isError) {
                    element.style.borderColor = 'var(--danger)';
                    element.style.boxShadow = '0 0 0 3px rgba(231, 76, 60, 0.1)';
                } else {
                    element.style.borderColor = 'var(--gray-300)';
                    element.style.boxShadow = 'none';
                }
            }
        });
    </script>
</body>
</html>