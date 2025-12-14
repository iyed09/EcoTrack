<?php
// evenementoffice.php - Plateforme Professionnelle ÉcoTrack
session_start();

// CONNEXION À LA BASE DE DONNÉES
$host = 'localhost';
$dbname = 'smartinnovators';
$username = 'root';
$password = '';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion: " . $e->getMessage());
}

// RÉCUPÉRER LES ÉVÉNEMENTS
$query = "SELECT idEvenement, titre, description, statut 
          FROM evenement 
          ORDER BY idEvenement DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$evenements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// RÉCUPÉRER LES PARTICIPATIONS
$sql = "SELECT p.*, e.titre as evenement_titre, e.statut as evenement_statut 
        FROM participation p
        LEFT JOIN evenement e ON p.idEvenement = e.idEvenement
        ORDER BY p.dateParticipation DESC";
$query_participations = $db->prepare($sql);
$query_participations->execute();
$participations = $query_participations->fetchAll(PDO::FETCH_ASSOC);

// Gestion des actions POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        // 1. CRÉATION D'ÉVÉNEMENT
        if ($action === 'add_event') {
            $titre = trim($_POST['titre'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $statut = $_POST['statut'] ?? 'ouverte';
            
            if (!empty($titre) && !empty($description)) {
                try {
                    $query = "INSERT INTO evenement (titre, description, statut) 
                              VALUES (:titre, :description, :statut)";
                    $stmt = $db->prepare($query);
                    $stmt->execute([
                        ':titre' => $titre,
                        ':description' => $description,
                        ':statut' => $statut
                    ]);
                    
                    $_SESSION['message'] = [
                        'type' => 'success',
                        'text' => '✅ Événement créé avec succès !'
                    ];
                    
                    // Rafraîchir la liste
                    $query = "SELECT idEvenement, titre, description, statut 
                              FROM evenement 
                              ORDER BY idEvenement DESC";
                    $stmt = $db->prepare($query);
                    $stmt->execute();
                    $evenements = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    header('Location: ' . $_SERVER['PHP_SELF'] . '#evenements');
                    exit;
                    
                } catch(PDOException $e) {
                    $_SESSION['message'] = [
                        'type' => 'error',
                        'text' => '❌ Erreur lors de la création: ' . $e->getMessage()
                    ];
                }
            } else {
                $_SESSION['message'] = [
                    'type' => 'error',
                    'text' => '⚠️ Veuillez remplir tous les champs obligatoires'
                ];
            }
        }
        
        // 2. MODIFICATION D'ÉVÉNEMENT
        if ($action === 'update_event' && isset($_POST['idEvenement'])) {
            $id = intval($_POST['idEvenement']);
            $titre = trim($_POST['titre'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $statut = $_POST['statut'] ?? 'ouverte';
            
            if (!empty($titre) && !empty($description) && $id > 0) {
                try {
                    $query = "UPDATE evenement 
                              SET titre = :titre, 
                                  description = :description, 
                                  statut = :statut
                              WHERE idEvenement = :id";
                    $stmt = $db->prepare($query);
                    $stmt->execute([
                        ':titre' => $titre,
                        ':description' => $description,
                        ':statut' => $statut,
                        ':id' => $id
                    ]);
                    
                    $_SESSION['message'] = [
                        'type' => 'success',
                        'text' => '✅ Événement modifié avec succès !'
                    ];
                    
                    // Rafraîchir la liste
                    $query = "SELECT idEvenement, titre, description, statut 
                              FROM evenement 
                              ORDER BY idEvenement DESC";
                    $stmt = $db->prepare($query);
                    $stmt->execute();
                    $evenements = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    header('Location: ' . $_SERVER['PHP_SELF'] . '#evenements');
                    exit;
                    
                } catch(PDOException $e) {
                    $_SESSION['message'] = [
                        'type' => 'error',
                        'text' => '❌ Erreur lors de la modification: ' . $e->getMessage()
                    ];
                }
            }
        }
        
        // 3. SUPPRESSION D'ÉVÉNEMENT
        if ($action === 'delete_event' && isset($_POST['idEvenement'])) {
            $id = intval($_POST['idEvenement']);
            if ($id > 0) {
                try {
                    // Supprimer d'abord les participations associées
                    $query = "DELETE FROM participation WHERE idEvenement = :id";
                    $stmt = $db->prepare($query);
                    $stmt->execute([':id' => $id]);
                    
                    // Puis supprimer l'événement
                    $query = "DELETE FROM evenement WHERE idEvenement = :id";
                    $stmt = $db->prepare($query);
                    $stmt->execute([':id' => $id]);
                    
                    $_SESSION['message'] = [
                        'type' => 'success',
                        'text' => '✅ Événement supprimé avec succès !'
                    ];
                    
                    // Rafraîchir la liste
                    $query = "SELECT idEvenement, titre, description, statut 
                              FROM evenement 
                              ORDER BY idEvenement DESC";
                    $stmt = $db->prepare($query);
                    $stmt->execute();
                    $evenements = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    header('Location: ' . $_SERVER['PHP_SELF'] . '#evenements');
                    exit;
                    
                } catch(PDOException $e) {
                    $_SESSION['message'] = [
                        'type' => 'error',
                        'text' => '❌ Erreur lors de la suppression: ' . $e->getMessage()
                    ];
                }
            }
        }
        
        // 4. PARTICIPATION À UN ÉVÉNEMENT
        if ($action === 'participer') {
            $idEvenement = intval($_POST['idEvenement'] ?? 0);
            $contenu = trim($_POST['contenu'] ?? '');
            $dateParticipation = $_POST['dateParticipation'] ?? date('Y-m-d H:i:s');
            
            if (!isset($_SESSION['user'])) {
                $_SESSION['user'] = [
                    'id' => session_id(),
                    'nom' => 'Participant',
                    'email' => 'guest@ecotrack.com'
                ];
            }
            
            if ($idEvenement > 0 && !empty($contenu)) {
                try {
                    $query = "INSERT INTO participation (idEvenement, contenu, dateParticipation) 
                              VALUES (:idEvenement, :contenu, :dateParticipation)";
                    $stmt = $db->prepare($query);
                    $stmt->execute([
                        ':idEvenement' => $idEvenement,
                        ':contenu' => $contenu,
                        ':dateParticipation' => $dateParticipation
                    ]);
                    
                    $_SESSION['message'] = [
                        'type' => 'success',
                        'text' => '✅ Participation enregistrée avec succès !'
                    ];
                    
                    header('Location: ' . $_SERVER['PHP_SELF'] . '#participation');
                    exit;
                    
                } catch(PDOException $e) {
                    $_SESSION['message'] = [
                        'type' => 'error',
                        'text' => '❌ Erreur lors de l\'enregistrement: ' . $e->getMessage()
                    ];
                }
            } else {
                $_SESSION['message'] = [
                    'type' => 'error',
                    'text' => '⚠️ Veuillez remplir tous les champs obligatoires'
                ];
            }
        }
        
        // 5. PAIEMENT SIMULÉ
        if ($action === 'paiement') {
            $montant = floatval($_POST['montant'] ?? 0);
            $methode = $_POST['methode'] ?? 'carte';
            
            if ($montant > 0) {
                $transaction_id = 'TRX' . date('YmdHis') . rand(1000, 9999);
                
                $_SESSION['paiements'][] = [
                    'transaction_id' => $transaction_id,
                    'montant' => $montant,
                    'methode' => $methode,
                    'date' => date('Y-m-d H:i:s'),
                    'statut' => 'complet'
                ];
                
                $_SESSION['message'] = [
                    'type' => 'success',
                    'text' => '✅ Paiement de ' . number_format($montant, 2) . ' TND effectué avec succès ! Transaction ID: ' . $transaction_id
                ];
                
                header('Location: ' . $_SERVER['PHP_SELF'] . '#paiement');
                exit;
            }
        }
        
        // 6. ANALYSE AI DES DÉCHETS
        if ($action === 'analyze_waste') {
            $type_dechet = $_POST['type_dechet'] ?? '';
            $quantite = floatval($_POST['quantite'] ?? 0);
            
            if (!empty($type_dechet) && $quantite > 0) {
                $impact = calculateEcoImpact($type_dechet, $quantite);
                $suggestions = getAISuggestions($type_dechet);
                
                $_SESSION['waste_analysis'] = [
                    'type' => $type_dechet,
                    'quantite' => $quantite,
                    'impact' => $impact,
                    'suggestions' => $suggestions,
                    'date' => date('Y-m-d H:i:s')
                ];
                
                $_SESSION['message'] = [
                    'type' => 'info',
                    'text' => '🤖 Analyse AI complétée ! Voir les résultats ci-dessous.'
                ];
                
                header('Location: ' . $_SERVER['PHP_SELF'] . '#ai-analyzer');
                exit;
            }
        }
    }
}

// Fonctions AI Avancées
function calculateEcoImpact($type, $quantity) {
    $impact_factors = [
        'Plastique' => ['co2' => 3.5, 'water' => 100, 'energy' => 80],
        'Verre' => ['co2' => 1.2, 'water' => 40, 'energy' => 20],
        'Papier' => ['co2' => 1.8, 'water' => 60, 'energy' => 30],
        'Métal' => ['co2' => 2.5, 'water' => 80, 'energy' => 60],
        'Organique' => ['co2' => 0.5, 'water' => 20, 'energy' => 10],
        'Électronique' => ['co2' => 5.0, 'water' => 150, 'energy' => 120]
    ];
    
    $factor = $impact_factors[$type] ?? $impact_factors['Plastique'];
    
    return [
        'co2_saved' => round($factor['co2'] * $quantity, 2),
        'water_saved' => round($factor['water'] * $quantity, 2),
        'energy_saved' => round($factor['energy'] * $quantity, 2),
        'score' => round((100 - ($factor['co2'] * 10)) * $quantity, 1),
        'equivalent_trees' => round(($factor['co2'] * $quantity) / 21.77, 2)
    ];
}

function getAISuggestions($type) {
    $suggestions_db = [
        'Plastique' => [
            'Réutiliser les bouteilles en contenants',
            'Privilégier les produits en vrac',
            'Utiliser des sacs réutilisables',
            'Recycler en produits d\'artisanat'
        ],
        'Verre' => [
            'Transformer en verre recyclé',
            'Réutiliser comme contenant alimentaire',
            'Créer des décorations',
            'Projets artistiques avec du verre brisé'
        ],
        'Papier' => [
            'Composter les déchets organiques',
            'Réutiliser pour l\'emballage',
            'Recycler en papier toilette',
            'Créer du papier mâché'
        ],
        'Métal' => [
            'Recycler pour réduire l\'extraction minière',
            'Réutiliser comme matériau de bricolage',
            'Vendre à des centres de recyclage',
            'Créer des œuvres d\'art'
        ],
        'Organique' => [
            'Compostage pour engrais naturel',
            'Lombricompostage',
            'Bokashi pour fermentation',
            'Jardinage avec compost'
        ],
        'Électronique' => [
            'Donner à des associations',
            'Recycler dans des centres spécialisés',
            'Vendre des pièces détachées',
            'Upcycler en objets décoratifs'
        ]
    ];
    
    return $suggestions_db[$type] ?? ['Recycler correctement', 'Consulter les centres de tri locaux'];
}

// Données utilisateur (simulées)
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = [
        'id' => session_id(),
        'nom' => 'Invité',
        'email' => 'guest@ecotrack.com',
        'ville' => 'Tunis',
        'points' => 150,
        'niveau' => 'Débutant',
        'joined' => date('Y-m-d')
    ];
}

// Statistiques
$stats = [
    'total_evenements' => count($evenements),
    'evenements_ouverts' => count(array_filter($evenements, fn($e) => $e['statut'] === 'ouverte')),
    'evenements_encours' => count(array_filter($evenements, fn($e) => $e['statut'] === 'en_cours')),
    'evenements_termines' => count(array_filter($evenements, fn($e) => $e['statut'] === 'resolue')),
    'total_participations' => count($participations),
    'users' => isset($_SESSION['users_count']) ? $_SESSION['users_count'] : 42
];

// Paiements (simulés)
$paiements = $_SESSION['paiements'] ?? [];

// Analyse AI (session)
$waste_analysis = $_SESSION['waste_analysis'] ?? null;

// Si édition d'événement
$edit_event = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $query = "SELECT * FROM evenement WHERE idEvenement = :id";
    $stmt = $db->prepare($query);
    $stmt->execute([':id' => $edit_id]);
    $edit_event = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoTrack - Plateforme Écologique Intelligente</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.css">
    <style>
        /* DESIGN PROFESSIONNEL AVANCÉ */
        :root {
            --primary: #10b981;
            --primary-dark: #059669;
            --primary-light: rgba(16, 185, 129, 0.1);
            --secondary: #3b82f6;
            --secondary-dark: #2563eb;
            --accent: #8b5cf6;
            --accent-dark: #7c3aed;
            --danger: #ef4444;
            --warning: #f59e0b;
            --success: #10b981;
            --info: #06b6d4;
            
            --dark: #111827;
            --dark-light: #1f2937;
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
            --gray-900: #111827;
            
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-md: 0 6px 8px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            --shadow-2xl: 0 25px 50px -12px rgb(0 0 0 / 0.25);
            
            --radius-sm: 0.375rem;
            --radius: 0.5rem;
            --radius-md: 0.75rem;
            --radius-lg: 1rem;
            --radius-xl: 1.5rem;
            --radius-2xl: 2rem;
            --radius-full: 9999px;
            
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Segoe UI Emoji', 'Segoe UI Symbol', system-ui, sans-serif;
        }

        body {
            background: linear-gradient(135deg, var(--gray-50) 0%, #f0f7ff 100%);
            color: var(--gray-800);
            line-height: 1.6;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* GRADIENTS ET EFFETS */
        .gradient-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
        }
        
        .gradient-accent {
            background: linear-gradient(135deg, var(--accent), var(--secondary));
        }
        
        .gradient-success {
            background: linear-gradient(135deg, var(--success), #0da271);
        }
        
        .gradient-warning {
            background: linear-gradient(135deg, var(--warning), #d97706);
        }

        /* HEADER PROFESSIONNEL */
        .header {
            background: var(--dark);
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: var(--shadow-lg);
            backdrop-filter: blur(10px);
            background: rgba(17, 24, 39, 0.95);
        }

        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }

        /* LOGO PROFESSIONNEL */
        .logo {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            text-decoration: none;
            transition: var(--transition);
        }

        .logo:hover {
            transform: translateY(-2px);
        }

        .logo-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            box-shadow: var(--shadow-lg);
            transition: var(--transition);
        }

        .logo:hover .logo-icon {
            transform: rotate(15deg);
            box-shadow: var(--shadow-xl);
        }

        .logo-text h1 {
            font-size: 1.75rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.025em;
        }

        .logo-text span {
            font-size: 0.75rem;
            color: var(--gray-400);
            font-weight: 500;
            display: block;
            margin-top: 0.125rem;
        }

        /* NAVIGATION AVANCÉE */
        .nav-menu {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .nav-link {
            color: var(--gray-300);
            text-decoration: none;
            padding: 0.75rem 1.25rem;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 0.875rem;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.625rem;
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.6s;
        }

        .nav-link:hover::before {
            left: 100%;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.05);
            color: white;
            transform: translateY(-1px);
        }

        .nav-link.active {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(59, 130, 246, 0.2));
            color: var(--primary);
            border: 1px solid rgba(16, 185, 129, 0.3);
            box-shadow: var(--shadow-sm);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: var(--radius);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .user-info {
            line-height: 1.4;
        }

        .user-name {
            font-weight: 600;
            color: white;
            font-size: 0.875rem;
        }

        .user-level {
            font-size: 0.75rem;
            color: var(--gray-400);
        }

        /* HERO SECTION PROFESSIONNELLE */
        .hero {
            padding: 6rem 0;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, 
                rgba(16, 185, 129, 0.1) 0%, 
                rgba(59, 130, 246, 0.1) 50%, 
                rgba(139, 92, 246, 0.05) 100%);
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 50%, rgba(16, 185, 129, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(59, 130, 246, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 40% 80%, rgba(139, 92, 246, 0.1) 0%, transparent 50%);
            animation: gradientShift 20s ease infinite;
        }

        .hero-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            position: relative;
            z-index: 1;
        }

        .hero-content {
            text-align: center;
            max-width: 800px;
            margin: 0 auto;
        }

        .hero-badge {
            display: inline-block;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 0.5rem 1.25rem;
            border-radius: var(--radius-full);
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            animation: pulse 2s infinite;
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, var(--gray-900) 0%, var(--gray-700) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1.2;
            animation: fadeInUp 1s ease-out;
        }

        .hero p {
            font-size: 1.25rem;
            color: var(--gray-600);
            margin-bottom: 3rem;
            line-height: 1.6;
            animation: fadeInUp 1s ease-out 0.2s both;
        }

        .hero-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-top: 4rem;
            animation: fadeInUp 1s ease-out 0.4s both;
        }

        .stat-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 2rem;
            text-align: center;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--gray-200);
            transition: var(--transition);
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
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--gray-600);
            font-weight: 600;
            font-size: 0.875rem;
        }

        /* SECTIONS PROFESSIONNELLES */
        .section {
            padding: 5rem 0;
        }

        .section-alt {
            background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-badge {
            display: inline-block;
            background: linear-gradient(135deg, var(--primary-light), rgba(59, 130, 246, 0.1));
            color: var(--primary);
            padding: 0.5rem 1.5rem;
            border-radius: var(--radius-full);
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 1rem;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--gray-900);
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .section-subtitle {
            color: var(--gray-600);
            font-size: 1.125rem;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* GRIDS ET LAYOUTS */
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .grid-4 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        /* CARDS PROFESSIONNELLES */
        .card {
            background: white;
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--gray-200);
            transition: var(--transition);
            animation: fadeIn 0.6s ease-out;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
        }

        .card-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            background: linear-gradient(135deg, var(--gray-50), var(--gray-100));
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .card-footer {
            padding: 1.5rem;
            border-top: 1px solid var(--gray-200);
            background: var(--gray-50);
        }

        /* FORMULAIRES PROFESSIONNELS */
        .form-card {
            background: white;
            border-radius: var(--radius-xl);
            padding: 2.5rem;
            box-shadow: var(--shadow-2xl);
            border: 1px solid var(--gray-200);
            position: relative;
            overflow: hidden;
        }

        .form-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, var(--primary), var(--secondary), var(--accent));
        }

        .form-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .form-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }

        .form-subtitle {
            color: var(--gray-600);
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
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-label .required {
            color: var(--danger);
            font-size: 1.2em;
            line-height: 0;
        }

        .form-control {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius);
            font-size: 1rem;
            transition: var(--transition);
            background: white;
            color: var(--gray-800);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
            background: var(--gray-50);
        }

        textarea.form-control {
            min-height: 150px;
            resize: vertical;
            line-height: 1.6;
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2310b981' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1.25rem center;
            background-size: 16px 12px;
            padding-right: 3rem;
        }

        .form-text {
            font-size: 0.75rem;
            color: var(--gray-500);
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .invalid-feedback {
            color: var(--danger);
            font-size: 0.75rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        /* BOUTONS PROFESSIONNELS */
        .btn {
            padding: 1rem 1.75rem;
            border: none;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.6s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: var(--shadow);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-secondary {
            background: linear-gradient(135deg, var(--secondary), var(--secondary-dark));
            color: white;
        }

        .btn-accent {
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            color: white;
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success), #0da271);
            color: white;
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
        }

        .btn-sm {
            padding: 0.625rem 1.25rem;
            font-size: 0.875rem;
        }

        .btn-lg {
            padding: 1.25rem 2.5rem;
            font-size: 1.125rem;
        }

        /* BADGES PROFESSIONNELS */
        .badge {
            padding: 0.5rem 1rem;
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            white-space: nowrap;
        }

        .badge-primary {
            background: linear-gradient(135deg, var(--primary-light), rgba(16, 185, 129, 0.2));
            color: var(--primary);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .badge-secondary {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(59, 130, 246, 0.2));
            color: var(--secondary);
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        .badge-success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.2));
            color: var(--success);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .badge-warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(245, 158, 11, 0.2));
            color: var(--warning);
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .badge-danger {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(239, 68, 68, 0.2));
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        /* ALERTS ET NOTIFICATIONS */
        .alert {
            padding: 1.25rem 1.5rem;
            border-radius: var(--radius);
            margin-bottom: 2rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            animation: slideIn 0.3s ease-out;
            border: 1px solid transparent;
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.05));
            color: var(--success);
            border-color: rgba(16, 185, 129, 0.2);
        }

        .alert-error {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(239, 68, 68, 0.05));
            color: var(--danger);
            border-color: rgba(239, 68, 68, 0.2);
        }

        .alert-warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(245, 158, 11, 0.05));
            color: var(--warning);
            border-color: rgba(245, 158, 11, 0.2);
        }

        .alert-info {
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.1), rgba(6, 182, 212, 0.05));
            color: var(--info);
            border-color: rgba(6, 182, 212, 0.2);
        }

        .alert-icon {
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        /* MODAL D'ÉDITION */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            backdrop-filter: blur(4px);
            animation: fadeIn 0.3s ease-out;
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: var(--radius-xl);
            padding: 2.5rem;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: var(--shadow-2xl);
            animation: slideUp 0.3s ease-out;
        }

        /* FOOTER PROFESSIONNEL */
        .footer {
            background: var(--dark);
            color: white;
            padding: 5rem 0 2rem;
            margin-top: 5rem;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            margin-bottom: 4rem;
        }

        .footer-section h3 {
            color: var(--primary);
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.875rem;
        }

        .footer-links a {
            color: var(--gray-400);
            text-decoration: none;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .footer-links a:hover {
            color: var(--primary);
            transform: translateX(5px);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--gray-400);
            font-size: 0.875rem;
        }

        /* ANIMATIONS */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.05);
                opacity: 0.8;
            }
        }

        @keyframes gradientShift {
            0%, 100% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        /* UTILITY CLASSES */
        .text-center { text-align: center; }
        .text-primary { color: var(--primary); }
        .text-success { color: var(--success); }
        .text-danger { color: var(--danger); }
        .text-warning { color: var(--warning); }
        
        .mb-1 { margin-bottom: 0.5rem; }
        .mb-2 { margin-bottom: 1rem; }
        .mb-3 { margin-bottom: 1.5rem; }
        .mb-4 { margin-bottom: 2rem; }
        .mb-5 { margin-bottom: 3rem; }
        
        .mt-1 { margin-top: 0.5rem; }
        .mt-2 { margin-top: 1rem; }
        .mt-3 { margin-top: 1.5rem; }
        .mt-4 { margin-top: 2rem; }
        .mt-5 { margin-top: 3rem; }
        
        .p-3 { padding: 1.5rem; }
        .p-4 { padding: 2rem; }
        .p-5 { padding: 3rem; }
        
        .shadow-lg { box-shadow: var(--shadow-lg); }
        .shadow-xl { box-shadow: var(--shadow-xl); }
        
        .rounded { border-radius: var(--radius); }
        .rounded-lg { border-radius: var(--radius-lg); }
        .rounded-xl { border-radius: var(--radius-xl); }
        
        .hidden { display: none; }
        .block { display: block; }
        .flex { display: flex; }
        .grid { display: grid; }
        
        .items-center { align-items: center; }
        .justify-center { justify-content: center; }
        .justify-between { justify-content: space-between; }
        
        .gap-2 { gap: 1rem; }
        .gap-3 { gap: 1.5rem; }
        .gap-4 { gap: 2rem; }
        
        .w-full { width: 100%; }
        .h-full { height: 100%; }
        
        .animate-fade-in { animation: fadeIn 0.6s ease-out; }
        .animate-fade-in-up { animation: fadeInUp 0.6s ease-out; }
        .animate-pulse { animation: pulse 2s infinite; }
        .animate-float { animation: float 6s ease-in-out infinite; }

        /* RESPONSIVE DESIGN */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }
            
            .nav-menu {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .hero p {
                font-size: 1.125rem;
            }
            
            .section {
                padding: 3rem 0;
            }
            
            .form-card {
                padding: 1.5rem;
            }
            
            .container {
                padding: 0 1rem;
            }
            
            .footer-grid {
                grid-template-columns: 1fr;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .hero h1 {
                font-size: 2rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .grid-3, .grid-4 {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <header class="header">
        <div class="header-container">
            <div class="header-content">
                <a href="#" class="logo">
                    <div class="logo-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <div class="logo-text">
                        <h1>EcoTrack</h1>
                        <span>Écologie Intelligente</span>
                    </div>
                </a>
                
                <nav class="nav-menu">
                    <a href="#accueil" class="nav-link active">
                        <i class="fas fa-home"></i> Accueil
                    </a>
                    <a href="#evenements" class="nav-link">
                        <i class="fas fa-calendar-alt"></i> Événements
                    </a>
                    <a href="#ai-analyzer" class="nav-link">
                        <i class="fas fa-robot"></i> AI Analyzer
                    </a>
                    <a href="#paiement" class="nav-link">
                        <i class="fas fa-credit-card"></i> Paiement
                    </a>
                    
                    <div class="user-profile">
                        <div class="user-avatar">
                            <?= strtoupper(substr($_SESSION['user']['nom'], 0, 1)) ?>
                        </div>
                        <div class="user-info">
                            <div class="user-name"><?= htmlspecialchars($_SESSION['user']['nom']) ?></div>
                            <div class="user-level">Niveau: <?= $_SESSION['user']['niveau'] ?></div>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </header>

    <!-- HERO -->
    <section class="hero" id="accueil">
        <div class="hero-container">
            <div class="hero-content">
                <span class="hero-badge animate-pulse">
                    <i class="fas fa-star"></i> Plateforme Premium
                </span>
                <h1>Écologie 4.0 avec Intelligence Artificielle</h1>
                <p>Transformez votre engagement écologique avec notre plateforme intelligente. 
                   Gérez des événements, analysez votre impact, et contribuez à un avenir durable.</p>
                
                <div class="hero-stats">
                    <div class="stat-card">
                        <div class="stat-number"><?= $stats['total_evenements'] ?></div>
                        <div class="stat-label">Événements</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?= $stats['total_participations'] ?></div>
                        <div class="stat-label">Participations</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?= $stats['users'] ?></div>
                        <div class="stat-label">Membres</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="co2Saved">0</div>
                        <div class="stat-label">Kg CO₂ Économisés</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- MESSAGES -->
    <div class="container">
        <?php if(isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message']['type'] ?> animate-fade-in">
            <i class="fas fa-<?= 
                $_SESSION['message']['type'] === 'success' ? 'check-circle' : 
                ($_SESSION['message']['type'] === 'error' ? 'exclamation-circle' :
                ($_SESSION['message']['type'] === 'warning' ? 'exclamation-triangle' : 'info-circle'))
            ?> alert-icon"></i>
            <div>
                <?= $_SESSION['message']['text'] ?>
            </div>
        </div>
        <?php unset($_SESSION['message']); endif; ?>
    </div>

    <!-- ÉVÉNEMENTS -->
    <section class="section" id="evenements">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">
                    <i class="fas fa-calendar-star"></i> Gestion Avancée
                </span>
                <h2 class="section-title">Événements Écologiques</h2>
                <p class="section-subtitle">
                    Créez, gérez et participez à des événements écologiques avec notre système avancé
                </p>
            </div>

            <!-- FORMULAIRE CRÉATION/MODIFICATION ÉVÉNEMENT -->
            <div class="form-card mb-5">
                <div class="form-header">
                    <h3 class="form-title">
                        <i class="fas fa-<?= $edit_event ? 'edit' : 'plus-circle' ?> text-primary"></i> 
                        <?= $edit_event ? 'Modifier l\'Événement' : 'Créer un Événement' ?>
                    </h3>
                    <p class="form-subtitle">Tous les champs marqués d'un * sont obligatoires</p>
                </div>
                
                <form method="POST" id="eventForm" class="needs-validation" novalidate>
                    <input type="hidden" name="action" value="<?= $edit_event ? 'update_event' : 'add_event' ?>">
                    <?php if($edit_event): ?>
                    <input type="hidden" name="idEvenement" value="<?= $edit_event['idEvenement'] ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-file-alt"></i> Titre de l'Événement
                            <span class="required">*</span>
                        </label>
                        <input type="text" 
                               name="titre" 
                               class="form-control" 
                               placeholder="Ex: Nettoyage de la plage de Sousse - Édition 2024"
                               value="<?= $edit_event ? htmlspecialchars($edit_event['titre']) : '' ?>"
                               required
                               minlength="5"
                               maxlength="100">
                        <div class="form-text">
                            <i class="fas fa-info-circle"></i> 5 à 100 caractères
                        </div>
                        <div class="invalid-feedback">
                            <i class="fas fa-exclamation-circle"></i> Le titre est requis (5-100 caractères)
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-align-left"></i> Description
                            <span class="required">*</span>
                        </label>
                        <textarea name="description" 
                                  class="form-control" 
                                  rows="5"
                                  placeholder="Décrivez en détail votre événement écologique : objectifs, localisation, équipement nécessaire, etc."
                                  required
                                  minlength="20"><?= $edit_event ? htmlspecialchars($edit_event['description']) : '' ?></textarea>
                        <div class="form-text">
                            <i class="fas fa-info-circle"></i> Minimum 20 caractères
                        </div>
                        <div class="invalid-feedback">
                            <i class="fas fa-exclamation-circle"></i> La description est requise (minimum 20 caractères)
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-chart-line"></i> Statut
                            <span class="required">*</span>
                        </label>
                        <select name="statut" class="form-control" required>
                            <option value="">Sélectionner un statut</option>
                            <option value="ouverte" <?= ($edit_event && $edit_event['statut'] == 'ouverte') ? 'selected' : '' ?>>Ouvert aux inscriptions</option>
                            <option value="en_cours" <?= ($edit_event && $edit_event['statut'] == 'en_cours') ? 'selected' : '' ?>>En cours</option>
                            <option value="resolue" <?= ($edit_event && $edit_event['statut'] == 'resolue') ? 'selected' : '' ?>>Terminé</option>
                        </select>
                        <div class="invalid-feedback">
                            <i class="fas fa-exclamation-circle"></i> Veuillez sélectionner un statut
                        </div>
                    </div>
                    
                    <div class="flex gap-3">
                        <button type="submit" class="btn btn-primary btn-lg" id="submitEventBtn">
                            <i class="fas fa-<?= $edit_event ? 'save' : 'paper-plane' ?>"></i> 
                            <?= $edit_event ? 'Modifier l\'Événement' : 'Publier l\'Événement' ?>
                        </button>
                        
                        <?php if($edit_event): ?>
                        <a href="evenementoffice.php#evenements" class="btn btn-outline">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="text-center mt-3">
                        <p class="form-text">
                            <i class="fas fa-shield-alt"></i> Vos données sont sécurisées et protégées
                        </p>
                    </div>
                </form>
            </div>

            <!-- LISTE DES ÉVÉNEMENTS -->
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fas fa-list-ul"></i> Tous les Événements
                    <span class="badge badge-primary"><?= count($evenements) ?></span>
                </h3>
            </div>
            
            <?php if(empty($evenements)): ?>
            <div class="card text-center p-5">
                <div class="card-body">
                    <i class="fas fa-calendar-times text-warning" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                    <h4 style="color: var(--gray-700); margin-bottom: 1rem;">Aucun événement pour le moment</h4>
                    <p style="color: var(--gray-600); margin-bottom: 2rem;">
                        Soyez le premier à créer un événement écologique !
                    </p>
                </div>
            </div>
            <?php else: ?>
            <div class="grid-3">
                <?php foreach($evenements as $event): ?>
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">
                            <i class="fas fa-leaf text-primary"></i>
                            <?= htmlspecialchars($event['titre']) ?>
                        </h4>
                        <span class="badge <?= 
                            $event['statut'] === 'ouverte' ? 'badge-success' : 
                            ($event['statut'] === 'en_cours' ? 'badge-warning' : 'badge-secondary')
                        ?>">
                            <i class="fas fa-circle"></i>
                            <?= ucfirst($event['statut']) ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <p style="color: var(--gray-600); margin-bottom: 1.5rem;">
                            <?= htmlspecialchars(substr($event['description'], 0, 150)) ?>...
                        </p>
                        <div style="color: var(--gray-500); font-size: 0.875rem;">
                            <div class="flex items-center gap-2 mb-1">
                                <i class="fas fa-hashtag"></i>
                                <span>ID: <?= $event['idEvenement'] ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="flex justify-between items-center">
                            <button class="btn btn-primary btn-sm participer-modal-btn"
                                    data-event-id="<?= $event['idEvenement'] ?>"
                                    data-event-title="<?= htmlspecialchars($event['titre']) ?>">
                                <i class="fas fa-check"></i> Participer
                            </button>
                            <div class="flex gap-2">
                                <a href="evenementoffice.php?edit_id=<?= $event['idEvenement'] ?>#evenements" 
                                   class="btn btn-outline btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" style="display: inline;" 
                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet événement ?')">
                                    <input type="hidden" name="action" value="delete_event">
                                    <input type="hidden" name="idEvenement" value="<?= $event['idEvenement'] ?>">
                                    <button type="submit" class="btn btn-outline btn-sm"
                                            style="color: var(--danger); border-color: var(--danger);">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- MODAL PARTICIPATION -->
    <div class="modal" id="participationModal">
        <div class="modal-content">
            <div class="form-header mb-4">
                <h3 class="form-title">
                    <i class="fas fa-hands-helping text-primary"></i> Participer à l'Événement
                </h3>
                <p class="form-subtitle" id="modalEventTitle">Sélectionnez un événement</p>
            </div>
            
            <form method="POST" id="participationForm">
                <input type="hidden" name="action" value="participer">
                <input type="hidden" name="idEvenement" id="modalEventId">
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-comment-dots"></i> Votre Message
                        <span class="required">*</span>
                    </label>
                    <textarea name="contenu" 
                              class="form-control" 
                              rows="6"
                              placeholder="Partagez vos motivations pour participer à cet événement..."
                              required
                              minlength="50"></textarea>
                    <div class="form-text">
                        <i class="fas fa-info-circle"></i> Minimum 50 caractères
                        <span id="charCount" style="float: right;">0/50</span>
                    </div>
                    <div class="invalid-feedback">
                        <i class="fas fa-exclamation-circle"></i> Votre message est requis (minimum 50 caractères)
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-calendar-alt"></i> Date de Participation
                    </label>
                    <input type="datetime-local" 
                           name="dateParticipation" 
                           class="form-control"
                           value="<?= date('Y-m-d\TH:i') ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <div class="alert alert-info">
                        <i class="fas fa-user-check alert-icon"></i>
                        <div>
                            <strong>Participant :</strong> <?= htmlspecialchars($_SESSION['user']['nom']) ?>
                            <br>
                            <small>Email: <?= htmlspecialchars($_SESSION['user']['email']) ?></small>
                        </div>
                    </div>
                </div>
                
                <div class="flex gap-3">
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fas fa-paper-plane"></i> Envoyer ma Participation
                    </button>
                    <button type="button" class="btn btn-outline" id="closeModal">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- AI ANALYZER -->
    <section class="section" id="ai-analyzer">
        <div class="container">
            <div class="section-header">
                <span class="section-badge gradient-accent" style="color: white;">
                    <i class="fas fa-robot"></i> Intelligence Artificielle
                </span>
                <h2 class="section-title">Analyseur Écologique AI</h2>
                <p class="section-subtitle">
                    Analysez votre impact écologique avec notre intelligence artificielle avancée
                </p>
            </div>
            
            <div class="form-card">
                <h3 style="color: var(--gray-900); margin-bottom: 1.5rem; font-size: 1.75rem;">
                    <i class="fas fa-brain"></i> Analyse d'Impact
                </h3>
                <p style="color: var(--gray-600); margin-bottom: 2rem;">
                    Notre AI analyse vos données pour vous donner des insights personnalisés
                </p>
                
                <form method="POST" id="aiAnalyzerForm">
                    <input type="hidden" name="action" value="analyze_waste">
                    
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-trash"></i> Type de Déchet
                            </label>
                            <select name="type_dechet" class="form-control" required>
                                <option value="">Sélectionner un type</option>
                                <option value="Plastique">Plastique</option>
                                <option value="Verre">Verre</option>
                                <option value="Papier">Papier/Carton</option>
                                <option value="Métal">Métal</option>
                                <option value="Organique">Organique</option>
                                <option value="Électronique">Électronique</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-weight"></i> Quantité (kg)
                            </label>
                            <input type="number" 
                                   name="quantite" 
                                   class="form-control" 
                                   step="0.1" 
                                   min="0.1" 
                                   placeholder="0.5" 
                                   required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-accent btn-lg btn-block mt-4">
                        <i class="fas fa-magic"></i> Analyser avec AI
                    </button>
                </form>
                
                <?php if($waste_analysis): ?>
                <div class="mt-5 p-4" style="background: var(--primary-light); border-radius: var(--radius); border: 1px solid var(--primary);">
                    <h4 style="color: var(--primary); margin-bottom: 1.5rem;">
                        <i class="fas fa-chart-bar"></i> Résultats de l'Analyse
                    </h4>
                    
                    <div class="grid-2">
                        <div>
                            <h5 style="color: var(--gray-700); margin-bottom: 1rem;">Impact Écologique</h5>
                            <div style="color: var(--gray-700);">
                                <div class="flex justify-between mb-2">
                                    <span>CO₂ Économisé:</span>
                                    <strong><?= $waste_analysis['impact']['co2_saved'] ?> kg</strong>
                                </div>
                                <div class="flex justify-between mb-2">
                                    <span>Eau Économisée:</span>
                                    <strong><?= $waste_analysis['impact']['water_saved'] ?> L</strong>
                                </div>
                                <div class="flex justify-between mb-2">
                                    <span>Énergie Économisée:</span>
                                    <strong><?= $waste_analysis['impact']['energy_saved'] ?> kWh</strong>
                                </div>
                                <div class="flex justify-between mb-2">
                                    <span>Score Écologique:</span>
                                    <strong><?= $waste_analysis['impact']['score'] ?>/100</strong>
                                </div>
                                <div class="flex justify-between">
                                    <span>Arbres Équivalents:</span>
                                    <strong><?= $waste_analysis['impact']['equivalent_trees'] ?> arbres</strong>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h5 style="color: var(--gray-700); margin-bottom: 1rem;">Suggestions AI</h5>
                            <ul style="color: var(--gray-700); list-style: none; padding: 0;">
                                <?php foreach($waste_analysis['suggestions'] as $suggestion): ?>
                                <li class="mb-2">
                                    <i class="fas fa-lightbulb text-warning" style="margin-right: 0.5rem;"></i>
                                    <?= htmlspecialchars($suggestion) ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- PAIEMENT -->
    <section class="section section-alt" id="paiement">
        <div class="container">
            <div class="section-header">
                <span class="section-badge" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white;">
                    <i class="fas fa-credit-card"></i> Paiement Sécurisé
                </span>
                <h2 class="section-title">Paiement en Ligne</h2>
                <p class="section-subtitle">
                    Soutenez nos initiatives écologiques avec un paiement sécurisé
                </p>
            </div>
            
            <div class="form-card">
                <div class="form-header mb-4">
                    <h3 class="form-title">
                        <i class="fas fa-donate text-primary"></i> Faire un Don
                    </h3>
                    <p class="form-subtitle">Votre contribution soutient nos projets écologiques</p>
                </div>
                
                <form method="POST" id="paymentForm">
                    <input type="hidden" name="action" value="paiement">
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-money-bill-wave"></i> Montant (TND)
                            <span class="required">*</span>
                        </label>
                        <div class="flex gap-2 mb-3">
                            <button type="button" class="btn btn-outline amount-btn" data-amount="10">10 TND</button>
                            <button type="button" class="btn btn-outline amount-btn" data-amount="25">25 TND</button>
                            <button type="button" class="btn btn-outline amount-btn" data-amount="50">50 TND</button>
                            <button type="button" class="btn btn-outline amount-btn" data-amount="100">100 TND</button>
                        </div>
                        <input type="number" 
                               name="montant" 
                               id="paymentAmount"
                               class="form-control" 
                               min="1" 
                               step="0.01"
                               placeholder="Entrez un montant"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-credit-card"></i> Méthode de Paiement
                            <span class="required">*</span>
                        </label>
                        <div class="grid-3 mb-3">
                            <div class="payment-method" data-method="carte">
                                <i class="fas fa-credit-card fa-2x mb-2" style="color: var(--primary);"></i>
                                <div>Carte</div>
                            </div>
                            <div class="payment-method" data-method="paypal">
                                <i class="fab fa-cc-paypal fa-2x mb-2" style="color: #003087;"></i>
                                <div>PayPal</div>
                            </div>
                            <div class="payment-method" data-method="virement">
                                <i class="fas fa-university fa-2x mb-2" style="color: var(--secondary);"></i>
                                <div>Virement</div>
                            </div>
                        </div>
                        <input type="hidden" name="methode" id="paymentMethod" value="carte" required>
                    </div>
                    
                    <div class="form-group">
                        <div class="alert alert-info">
                            <i class="fas fa-lock alert-icon"></i>
                            <div>
                                <strong>Paiement 100% sécurisé</strong>
                                <br>
                                <small>Toutes vos informations sont cryptées et protégées</small>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-success btn-lg btn-block">
                        <i class="fas fa-lock"></i> Payer Maintenant
                    </button>
                </form>
                
                <?php if(!empty($paiements)): ?>
                <div class="mt-5">
                    <h4 style="color: var(--gray-900); margin-bottom: 1.5rem;">
                        <i class="fas fa-history"></i> Historique des Paiements
                    </h4>
                    <div style="background: var(--gray-50); border-radius: var(--radius); padding: 1.5rem;">
                        <?php foreach(array_slice($paiements, 0, 3) as $paiement): ?>
                        <div style="padding: 1rem; border-bottom: 1px solid var(--gray-200);">
                            <div class="flex justify-between items-center">
                                <div>
                                    <div style="font-weight: 600; color: var(--gray-700);">
                                        <?= number_format($paiement['montant'], 2) ?> TND
                                    </div>
                                    <div style="font-size: 0.875rem; color: var(--gray-500);">
                                        <?= date('d/m/Y H:i', strtotime($paiement['date'])) ?>
                                    </div>
                                </div>
                                <div>
                                    <span class="badge badge-success">
                                        <?= ucfirst($paiement['methode']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-grid">
                <div class="footer-section">
                    <h3>
                        <i class="fas fa-leaf"></i> EcoTrack
                    </h3>
                    <p style="color: var(--gray-400); margin-top: 1rem; line-height: 1.6;">
                        Plateforme innovante pour la gestion écologique intelligente. 
                        Nous combinons technologie et écologie pour un avenir durable.
                    </p>
                </div>
                
                <div class="footer-section">
                    <h3>
                        <i class="fas fa-link"></i> Liens Rapides
                    </h3>
                    <ul class="footer-links">
                        <li><a href="#accueil"><i class="fas fa-chevron-right"></i> Accueil</a></li>
                        <li><a href="#evenements"><i class="fas fa-chevron-right"></i> Événements</a></li>
                        <li><a href="#ai-analyzer"><i class="fas fa-chevron-right"></i> AI Analyzer</a></li>
                        <li><a href="#paiement"><i class="fas fa-chevron-right"></i> Paiement</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>
                        <i class="fas fa-envelope"></i> Contact
                    </h3>
                    <div style="color: var(--gray-400); line-height: 1.6;">
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                            <i class="fas fa-envelope"></i>
                            <span>contact@ecotrack.com</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <i class="fas fa-phone"></i>
                            <span>+216 12 345 678</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2024 EcoTrack. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <!-- SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        // INITIALISATION
        document.addEventListener('DOMContentLoaded', function() {
            // 1. ANIMATION CO2
            animateCO2Counter();
            
            // 2. SMOOTH SCROLL
            initSmoothScroll();
            
            // 3. MODALS
            initModals();
            
            // 4. PAIEMENT
            initPayment();
            
            // 5. FORM VALIDATION
            initForms();
            
            // 6. NOTIFICATIONS
            initNotifications();
            
            // 7. ACTIVE NAV ON SCROLL
            initActiveNav();
        });
        
        // 1. ANIMATION CO2
        function animateCO2Counter() {
            const counter = document.getElementById('co2Saved');
            if (!counter) return;
            
            let current = 0;
            const target = 1250;
            const increment = target / 100;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    counter.textContent = target;
                    clearInterval(timer);
                } else {
                    counter.textContent = Math.floor(current);
                }
            }, 20);
        }
        
        // 2. SMOOTH SCROLL
        function initSmoothScroll() {
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href');
                    if (targetId !== '#') {
                        const targetElement = document.querySelector(targetId);
                        if (targetElement) {
                            window.scrollTo({
                                top: targetElement.offsetTop - 80,
                                behavior: 'smooth'
                            });
                        }
                    }
                });
            });
        }
        
        // 3. MODALS
        function initModals() {
            // Modal participation
            const modal = document.getElementById('participationModal');
            const closeBtn = document.getElementById('closeModal');
            
            document.querySelectorAll('.participer-modal-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const eventId = this.dataset.eventId;
                    const eventTitle = this.dataset.eventTitle;
                    openParticipationModal(eventId, eventTitle);
                });
            });
            
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    modal.classList.remove('show');
                });
            }
            
            // Close modal on outside click
            window.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.remove('show');
                }
            });
            
            // Char counter for participation
            const textarea = document.querySelector('#participationForm textarea');
            if (textarea) {
                textarea.addEventListener('input', function() {
                    const charCount = this.value.length;
                    const counter = document.getElementById('charCount');
                    if (counter) {
                        counter.textContent = `${charCount}/50`;
                        if (charCount < 50) {
                            counter.style.color = 'var(--danger)';
                        } else {
                            counter.style.color = 'var(--success)';
                        }
                    }
                });
            }
        }
        
        function openParticipationModal(eventId, eventTitle) {
            const modal = document.getElementById('participationModal');
            const modalTitle = document.getElementById('modalEventTitle');
            const modalEventId = document.getElementById('modalEventId');
            
            modalEventId.value = eventId;
            modalTitle.textContent = eventTitle;
            modal.classList.add('show');
            
            // Reset form
            const form = document.getElementById('participationForm');
            if (form) {
                form.reset();
                form.classList.remove('was-validated');
                
                // Reset char counter
                const counter = document.getElementById('charCount');
                if (counter) {
                    counter.textContent = '0/50';
                    counter.style.color = 'var(--gray-500)';
                }
            }
        }
        
        // 4. PAIEMENT
        function initPayment() {
            // Boutons montant
            document.querySelectorAll('.amount-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const amount = this.dataset.amount;
                    document.getElementById('paymentAmount').value = amount;
                    
                    // Reset tous les boutons
                    document.querySelectorAll('.amount-btn').forEach(b => {
                        b.classList.remove('active');
                        b.style.background = '';
                        b.style.color = '';
                    });
                    
                    // Activer le bouton cliqué
                    this.classList.add('active');
                    this.style.background = 'var(--primary)';
                    this.style.color = 'white';
                });
            });
            
            // Méthodes de paiement
            document.querySelectorAll('.payment-method').forEach(method => {
                method.addEventListener('click', function() {
                    const methodValue = this.dataset.method;
                    document.getElementById('paymentMethod').value = methodValue;
                    
                    // Reset toutes les méthodes
                    document.querySelectorAll('.payment-method').forEach(m => {
                        m.classList.remove('active');
                        m.style.borderColor = '';
                        m.style.background = '';
                    });
                    
                    // Activer la méthode sélectionnée
                    this.classList.add('active');
                    this.style.borderColor = 'var(--primary)';
                    this.style.background = 'var(--primary-light)';
                });
            });
        }
        
        // 5. FORM VALIDATION
        function initForms() {
            // Validation événement
            const eventForm = document.getElementById('eventForm');
            if (eventForm) {
                eventForm.addEventListener('submit', function(e) {
                    if (!this.checkValidity()) {
                        e.preventDefault();
                        e.stopPropagation();
                        this.classList.add('was-validated');
                        return false;
                    }
                    
                    // Animation de soumission
                    const submitBtn = document.getElementById('submitEventBtn');
                    if (submitBtn) {
                        const originalHTML = submitBtn.innerHTML;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Publication en cours...';
                        submitBtn.disabled = true;
                        
                        // Simuler délai
                        setTimeout(() => {
                            submitBtn.innerHTML = originalHTML;
                            submitBtn.disabled = false;
                        }, 2000);
                    }
                    
                    return true;
                });
            }
        }
        
        // 6. NOTIFICATIONS
        function initNotifications() {
            // Auto-hide alerts
            setTimeout(() => {
                document.querySelectorAll('.alert').forEach(alert => {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                });
            }, 5000);
        }
        
        // 7. ACTIVE NAV ON SCROLL
        function initActiveNav() {
            window.addEventListener('scroll', function() {
                const sections = document.querySelectorAll('section[id]');
                const scrollPos = window.scrollY + 100;
                
                sections.forEach(section => {
                    const sectionTop = section.offsetTop;
                    const sectionHeight = section.clientHeight;
                    const sectionId = section.getAttribute('id');
                    
                    if (scrollPos >= sectionTop && scrollPos < sectionTop + sectionHeight) {
                        document.querySelectorAll('.nav-link').forEach(link => {
                            link.classList.remove('active');
                            if (link.getAttribute('href') === `#${sectionId}`) {
                                link.classList.add('active');
                            }
                        });
                    }
                });
            });
        }
        
        // 8. SHOW SUCCESS MESSAGE
        function showSuccessMessage(message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success animate-fade-in';
            alertDiv.innerHTML = `
                <i class="fas fa-check-circle alert-icon"></i>
                <div>${message}</div>
            `;
            alertDiv.style.position = 'fixed';
            alertDiv.style.top = '20px';
            alertDiv.style.right = '20px';
            alertDiv.style.zIndex = '99999';
            alertDiv.style.maxWidth = '400px';
            alertDiv.style.boxShadow = 'var(--shadow-xl)';
            
            document.body.appendChild(alertDiv);
            
            setTimeout(() => {
                alertDiv.style.opacity = '0';
                setTimeout(() => alertDiv.remove(), 300);
            }, 3000);
        }
    </script>
</body>
</html>