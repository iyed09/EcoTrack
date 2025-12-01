<?php
// Chemin CORRIGÉ vers EvenementController.php
require_once __DIR__ . '/../../Controller/EvenementController.php';

$controller = new EvenementController();

// Traitement des actions
$action = $_GET['action'] ?? 'accueil';
$message = '';

// TRAITEMENT AJOUT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_evenement') {
    if (!empty($_POST['titre']) && !empty($_POST['description'])) {
        $evenementData = [
            'titre' => $_POST['titre'],
            'description' => $_POST['description'],
            'statut' => 'ouverte'
        ];
        
        $result = $controller->addEvenement($evenementData);
        
        if ($result) {
            $message = '<div class="message success"><i class="fas fa-check-circle"></i>Événement innovant créé avec succès!</div>';
            $action = 'liste';
        } else {
            $message = '<div class="message error"><i class="fas fa-exclamation-circle"></i>Erreur lors de la création</div>';
        }
    } else {
        $message = '<div class="message warning"><i class="fas fa-exclamation-triangle"></i>Veuillez remplir tous les champs</div>';
    }
}

// TRAITEMENT SUPPRESSION
if (isset($_GET['delete_id'])) {
    if ($controller->deleteEvenement($_GET['delete_id'])) {
        $message = '<div class="message success"><i class="fas fa-check-circle"></i>Événement supprimé!</div>';
    }
}

// Récupération des données
$evenements = $controller->getAllEvenements();
$stats = [
    'total' => count($evenements),
    'ouvertes' => count(array_filter($evenements, fn($e) => $e['statut'] === 'ouverte')),
    'en_cours' => count(array_filter($evenements, fn($e) => $e['statut'] === 'en_cours')),
    'resolues' => count(array_filter($evenements, fn($e) => $e['statut'] === 'resolue'))
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoTrack - Plateforme d'Événements Écologiques Innovants</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-cyan: #00f5d4;
            --primary-blue: #00bbf9;
            --primary-purple: #9b5de5;
            --accent-pink: #f15bb5;
            --accent-yellow: #fee440;
            --dark-charcoal: #1a1a2e;
            --dark-slate: #16213e;
            --light-glass: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --glass-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.36);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        body {
            background: linear-gradient(135deg, 
                var(--dark-slate) 0%, 
                var(--dark-charcoal) 50%, 
                #0f3460 100%);
            color: white;
            min-height: 100vh;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(0, 245, 212, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(155, 93, 229, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(241, 91, 181, 0.05) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
        }

        /* Header Futuriste */
        .tech-header {
            background: rgba(26, 26, 46, 0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
            padding: 1.2rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }

        .logo-tech {
            font-size: 2.4rem;
            font-weight: 800;
            background: linear-gradient(45deg, var(--primary-cyan), var(--primary-blue));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: flex;
            align-items: center;
            gap: 12px;
            text-shadow: 0 0 30px rgba(0, 245, 212, 0.5);
        }

        .nav-tech {
            display: flex;
            gap: 0.5rem;
        }

        .nav-tech a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            padding: 0.8rem 1.8rem;
            border-radius: 50px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid transparent;
            display: flex;
            align-items: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
        }

        .nav-tech a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.6s;
        }

        .nav-tech a:hover::before {
            left: 100%;
        }

        .nav-tech a:hover,
        .nav-tech a.active {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--primary-cyan);
            box-shadow: 0 0 20px rgba(0, 245, 212, 0.3);
            transform: translateY(-2px);
        }

        /* Hero Section */
        .hero-innovation {
            padding: 120px 2rem;
            text-align: center;
            position: relative;
        }

        .hero-content {
            max-width: 900px;
            margin: 0 auto;
            position: relative;
        }

        .hero-title {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            font-weight: 800;
            background: linear-gradient(45deg, var(--primary-cyan), var(--accent-yellow), var(--primary-blue));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 50px rgba(0, 245, 212, 0.3);
            line-height: 1.1;
        }

        .hero-subtitle {
            font-size: 1.4rem;
            margin-bottom: 3rem;
            opacity: 0.9;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.8);
        }

        /* Boutons Animés */
        .btn-tech {
            padding: 18px 35px;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            position: relative;
            overflow: hidden;
        }

        .btn-tech::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.6s;
        }

        .btn-tech:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--primary-cyan), var(--primary-blue));
            color: var(--dark-charcoal);
            box-shadow: 0 8px 30px rgba(0, 245, 212, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 15px 40px rgba(0, 245, 212, 0.6);
        }

        .btn-secondary {
            background: transparent;
            color: white;
            border: 2px solid var(--primary-cyan);
            box-shadow: 0 0 20px rgba(0, 245, 212, 0.3);
        }

        .btn-secondary:hover {
            background: var(--primary-cyan);
            color: var(--dark-charcoal);
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 245, 212, 0.5);
        }

        /* Contenu Principal */
        .main-container {
            max-width: 1400px;
            margin: 4rem auto;
            padding: 0 2rem;
        }

        /* Cartes de Statistiques */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .stat-card {
            background: var(--light-glass);
            backdrop-filter: blur(20px);
            padding: 3rem 2rem;
            border-radius: 25px;
            border: 1px solid var(--glass-border);
            text-align: center;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(45deg, var(--primary-cyan), var(--primary-purple));
        }

        .stat-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: var(--glass-shadow), 0 0 50px rgba(0, 245, 212, 0.2);
            border-color: var(--primary-cyan);
        }

        .stat-number {
            font-size: 3.5rem;
            font-weight: 800;
            background: linear-gradient(45deg, var(--primary-cyan), var(--accent-yellow));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
            text-shadow: 0 0 30px rgba(0, 245, 212, 0.3);
        }

        .stat-label {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 600;
            font-size: 1.2rem;
        }

        /* Formulaire Futuriste */
        .form-innovation {
            background: var(--light-glass);
            backdrop-filter: blur(20px);
            padding: 4rem;
            border-radius: 30px;
            border: 1px solid var(--glass-border);
            box-shadow: var(--glass-shadow);
            position: relative;
            overflow: hidden;
        }

        .form-innovation::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(45deg, var(--primary-cyan), var(--accent-pink), var(--primary-purple));
        }

        .form-group {
            margin-bottom: 2.5rem;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 1rem;
            font-weight: 700;
            color: var(--primary-cyan);
            font-size: 1.2rem;
        }

        .form-input {
            width: 100%;
            padding: 1.2rem 1.8rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            border-radius: 15px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            color: white;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-cyan);
            box-shadow: 0 0 30px rgba(0, 245, 212, 0.2);
            background: rgba(255, 255, 255, 0.08);
        }

        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .form-textarea {
            min-height: 180px;
            resize: vertical;
        }

        /* Tableau Futuriste */
        .table-tech {
            width: 100%;
            background: var(--light-glass);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            border: 1px solid var(--glass-border);
            overflow: hidden;
            box-shadow: var(--glass-shadow);
        }

        .table-tech th {
            background: linear-gradient(45deg, var(--primary-cyan), var(--primary-blue));
            color: var(--dark-charcoal);
            padding: 1.8rem;
            text-align: left;
            font-weight: 800;
            font-size: 1.1rem;
        }

        .table-tech td {
            padding: 1.8rem;
            border-bottom: 1px solid var(--glass-border);
            transition: all 0.3s ease;
        }

        .table-tech tr:hover td {
            background: rgba(255, 255, 255, 0.05);
            transform: scale(1.01);
        }

        /* Badges de Statut */
        .badge {
            padding: 0.7rem 1.5rem;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-ouverte {
            background: linear-gradient(45deg, var(--primary-cyan), var(--primary-blue));
            color: var(--dark-charcoal);
        }

        .badge-en_cours {
            background: linear-gradient(45deg, var(--accent-yellow), #ff9e00);
            color: var(--dark-charcoal);
        }

        .badge-resolue {
            background: linear-gradient(45deg, var(--accent-pink), var(--primary-purple));
            color: white;
        }

        /* Messages */
        .message {
            padding: 1.8rem;
            border-radius: 20px;
            margin-bottom: 2.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 1.2rem;
            border: 1px solid;
            backdrop-filter: blur(20px);
        }

        .message.success {
            background: rgba(0, 245, 212, 0.1);
            color: var(--primary-cyan);
            border-color: var(--primary-cyan);
        }

        .message.error {
            background: rgba(241, 91, 181, 0.1);
            color: var(--accent-pink);
            border-color: var(--accent-pink);
        }

        /* Grille des Fonctionnalités */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2.5rem;
            margin-top: 5rem;
        }

        .feature-card {
            background: var(--light-glass);
            backdrop-filter: blur(20px);
            padding: 3rem 2.5rem;
            border-radius: 25px;
            border: 1px solid var(--glass-border);
            text-align: center;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(45deg, var(--primary-cyan), var(--primary-purple));
        }

        .feature-card:hover {
            transform: translateY(-12px) scale(1.03);
            box-shadow: var(--glass-shadow), 0 0 60px rgba(0, 245, 212, 0.3);
            border-color: var(--primary-cyan);
        }

        .feature-icon {
            font-size: 3.5rem;
            margin-bottom: 2rem;
            background: linear-gradient(45deg, var(--primary-cyan), var(--accent-yellow));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .feature-title {
            font-size: 1.6rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            color: white;
        }

        .feature-card p {
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.6;
            font-size: 1.1rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1.5rem;
            }

            .nav-tech {
                flex-wrap: wrap;
                justify-content: center;
            }

            .hero-title {
                font-size: 2.8rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .form-innovation {
                padding: 2.5rem;
            }

            .feature-card {
                padding: 2.5rem 2rem;
            }
        }

        /* Animations */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .floating {
            animation: float 6s ease-in-out infinite;
        }
    </style>
</head>
<body>
    <!-- Header Futuriste -->
    <header class="tech-header">
        <div class="header-content">
            <div class="logo-tech floating">
                <i class="fas fa-seedling"></i>
                Eco<span>Track</span>
            </div>
            <nav class="nav-tech">
                <a href="?action=accueil" class="<?= $action === 'accueil' ? 'active' : '' ?>">
                    <i class="fas fa-home"></i> Accueil
                </a>
                <a href="?action=ajouter" class="<?= $action === 'ajouter' ? 'active' : '' ?>">
                    <i class="fas fa-plus"></i> Nouvel Événement
                </a>
                <a href="?action=liste" class="<?= $action === 'liste' ? 'active' : '' ?>">
                    <i class="fas fa-calendar-alt"></i> Mes Événements
                </a>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <?php if ($action === 'accueil'): ?>
    <section class="hero-innovation">
        <div class="hero-content">
            <h1 class="hero-title floating">Révolution Écologique par la Technologie</h1>
            <p class="hero-subtitle">Plateforme d'événements innovants alliant intelligence artificielle et développement durable pour un avenir plus vert</p>
            <div style="display: flex; gap: 1.5rem; justify-content: center; flex-wrap: wrap; margin-top: 4rem;">
                <a href="?action=ajouter" class="btn-tech btn-primary floating">
                    <i class="fas fa-rocket"></i>
                    Lancer un Événement
                </a>
                <a href="?action=liste" class="btn-tech btn-secondary floating">
                    <i class="fas fa-chart-network"></i>
                    Explorer les Activités
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Messages -->
    <?php if ($message): ?>
        <div class="main-container"><?= $message ?></div>
    <?php endif; ?>

    <!-- Contenu Principal -->
    <main class="main-container">
        <?php if ($action === 'accueil'): ?>
            <!-- Statistiques -->
            <div class="stats-grid">
                <div class="stat-card floating">
                    <div class="stat-number"><?= $stats['total'] ?></div>
                    <div class="stat-label">Événements Actifs</div>
                </div>
                <div class="stat-card floating">
                    <div class="stat-number"><?= $stats['ouvertes'] ?></div>
                    <div class="stat-label">Inscriptions Ouvertes</div>
                </div>
                <div class="stat-card floating">
                    <div class="stat-number"><?= $stats['en_cours'] ?></div>
                    <div class="stat-label">En Cours</div>
                </div>
                <div class="stat-card floating">
                    <div class="stat-number"><?= $stats['resolues'] ?></div>
                    <div class="stat-label">Accomplis</div>
                </div>
            </div>

            <!-- Fonctionnalités -->
            <div class="features-grid">
                <div class="feature-card floating">
                    <div class="feature-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <h3 class="feature-title">IA Écologique</h3>
                    <p>Optimisation intelligente des événements environnementaux grâce à l'apprentissage automatique</p>
                </div>
                <div class="feature-card floating">
                    <div class="feature-icon">
                        <i class="fas fa-satellite-dish"></i>
                    </div>
                    <h3 class="feature-title">IoT Connecté</h3>
                    <p>Réseau de capteurs intelligents pour le monitoring environnemental en temps réel</p>
                </div>
                <div class="feature-card floating">
                    <div class="feature-icon">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <h3 class="feature-title">Analytics 3D</h3>
                    <p>Visualisations immersives et analyses prédictives des impacts écologiques</p>
                </div>
            </div>

        <?php elseif ($action === 'ajouter'): ?>
            <!-- Formulaire d'Ajout -->
            <div class="form-innovation floating">
                <h2 style="margin-bottom: 2.5rem; color: var(--primary-cyan); font-size: 2.5rem; text-align: center;">
                    <i class="fas fa-magic"></i> Créer un Événement Innovant
                </h2>
                <form method="POST">
                    <input type="hidden" name="action" value="add_evenement">
                    <div class="form-group">
                        <label class="form-label">Titre de l'événement</label>
                        <input type="text" name="titre" class="form-input" required 
                               placeholder="Ex: Hackathon IA Écologique, Nettoyage 4.0, Conférence Tech Verte...">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description innovante</label>
                        <textarea name="description" class="form-input form-textarea" required 
                                  placeholder="Décrivez la mission écologique, les technologies utilisées, l'impact environnemental..."></textarea>
                    </div>
                    <button type="submit" class="btn-tech btn-primary" style="margin-top: 2rem; width: 100%;">
                        <i class="fas fa-paper-plane"></i> Lancez l'Événement
                    </button>
                </form>
            </div>

        <?php elseif ($action === 'liste'): ?>
            <!-- Liste des Événements -->
            <h2 style="margin-bottom: 3rem; color: var(--primary-cyan); font-size: 2.5rem; text-align: center;">
                <i class="fas fa-stars"></i> Mes Événements Écologiques
            </h2>
            
            <?php if (empty($evenements)): ?>
                <div style="text-align: center; padding: 6rem 2rem; color: white;">
                    <i class="fas fa-rocket" style="font-size: 5rem; margin-bottom: 2rem; opacity: 0.7;"></i>
                    <h3 style="margin-bottom: 1.5rem; font-size: 2rem;">Aucun événement spatial</h3>
                    <p style="margin-bottom: 3rem; font-size: 1.2rem; opacity: 0.8;">Propulsez votre premier événement écologique innovant</p>
                    <a href="?action=ajouter" class="btn-tech btn-primary">
                        <i class="fas fa-plus"></i> Décoller Maintenant
                    </a>
                </div>
            <?php else: ?>
                <div class="table-tech floating">
                    <table style="width: 100%;">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Titre</th>
                                <th>Description</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($evenements as $evenement): ?>
                            <tr>
                                <td><strong style="color: var(--primary-cyan);">#<?= $evenement['idEvenement'] ?></strong></td>
                                <td style="font-weight: 600;"><?= htmlspecialchars($evenement['titre']) ?></td>
                                <td>
                                    <?= strlen($evenement['description']) > 100 ? 
                                        htmlspecialchars(substr($evenement['description'], 0, 100)) . '...' : 
                                        htmlspecialchars($evenement['description']) ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $evenement['statut'] ?>">
                                        <?= ucfirst($evenement['statut']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="?action=liste&delete_id=<?= $evenement['idEvenement'] ?>" 
                                       class="btn-tech" 
                                       style="background: linear-gradient(45deg, #ff6b6b, #ee5a52); color: white; padding: 0.8rem 1.5rem; font-size: 0.9rem;"
                                       onclick="return confirm('Supprimer cet événement ?');">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <script>
        // Animations avancées
        document.addEventListener('DOMContentLoaded', function() {
            // Animation séquentielle des cartes
            const cards = document.querySelectorAll('.stat-card, .feature-card, .form-innovation, .table-tech');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px) scale(0.9)';
                card.style.transition = 'all 0.8s cubic-bezier(0.4, 0, 0.2, 1)';
                
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0) scale(1)';
                }, index * 200 + 500);
            });

            // Effets de particules sur les boutons
            const buttons = document.querySelectorAll('.btn-tech');
            buttons.forEach(btn => {
                btn.addEventListener('mouseenter', function(e) {
                    const rect = this.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    
                    const ripple = document.createElement('span');
                    ripple.style.cssText = `
                        position: absolute;
                        top: ${y}px;
                        left: ${x}px;
                        width: 0;
                        height: 0;
                        border-radius: 50%;
                        background: rgba(255,255,255,0.3);
                        transform: translate(-50%, -50%);
                        animation: ripple 0.6s ease-out;
                    `;
                    
                    this.appendChild(ripple);
                    
                    setTimeout(() => ripple.remove(), 600);
                });
            });

            // Auto-hide messages avec style
            setTimeout(() => {
                const messages = document.querySelectorAll('.message');
                messages.forEach(message => {
                    message.style.transition = 'all 0.5s ease';
                    message.style.opacity = '0';
                    message.style.transform = 'translateX(100px)';
                    setTimeout(() => message.remove(), 500);
                });
            }, 5000);
        });

        // Style pour l'effet ripple
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    width: 200px;
                    height: 200px;
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>