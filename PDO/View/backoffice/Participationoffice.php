<?php
// DASHBOARD PROFESSIONNEL MODERNE CORRIGÉ - Gestion des Participations

// Vérifier si session déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CONNEXION À LA BASE DE DONNÉES
$host = 'localhost';
$dbname = 'smartinnovators';
$username = 'root';
$password = '';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch(PDOException $e) {
    die('Erreur de connexion à la base de données: ' . $e->getMessage());
}

// ========== TRAITEMENT DES ACTIONS ==========

// Messages
$message = '';
$success_message = '';
$error_message = '';

// Suppression d'une participation
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    
    try {
        // Vérifier s'il y a des paiements liés
        $sqlCheck = "SELECT COUNT(*) as count FROM paiement_participation WHERE idParticipation = :id";
        $queryCheck = $db->prepare($sqlCheck);
        $queryCheck->execute([':id' => $id]);
        $hasPayments = $queryCheck->fetch()['count'] > 0;
        
        if ($hasPayments) {
            $error_message = 'Cette participation a des paiements associés. Supprimez d\'abord les paiements.';
        } else {
            $sql = "DELETE FROM participation WHERE idParticipation = :id";
            $query = $db->prepare($sql);
            $query->execute([':id' => $id]);
            
            $success_message = 'Participation supprimée avec succès';
        }
        
    } catch(Exception $e) {
        $error_message = 'Erreur lors de la suppression: ' . $e->getMessage();
    }
}

// Recherche
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

// ========== STATISTIQUES RÉELLES ==========
$stats = [];

try {
    // Total participations
    $sql = "SELECT COUNT(*) as total FROM participation";
    $query = $db->prepare($sql);
    $query->execute();
    $stats['total'] = $query->fetch()['total'];
    
    // Participations aujourd'hui
    $sql = "SELECT COUNT(*) as count FROM participation WHERE DATE(dateParticipation) = CURDATE()";
    $query = $db->prepare($sql);
    $query->execute();
    $stats['today'] = $query->fetch()['count'];
    
    // Participations cette semaine
    $sql = "SELECT COUNT(*) as count FROM participation WHERE YEARWEEK(dateParticipation, 1) = YEARWEEK(CURDATE(), 1)";
    $query = $db->prepare($sql);
    $query->execute();
    $stats['this_week'] = $query->fetch()['count'];
    
    // Participations ce mois
    $sql = "SELECT COUNT(*) as count FROM participation WHERE MONTH(dateParticipation) = MONTH(NOW()) AND YEAR(dateParticipation) = YEAR(NOW())";
    $query = $db->prepare($sql);
    $query->execute();
    $stats['this_month'] = $query->fetch()['count'];
    
    // Récupérer tous les événements
    $sql = "SELECT idEvenement, titre FROM evenement WHERE statut IN ('ouverte', 'en_cours') ORDER BY titre";
    $query = $db->prepare($sql);
    $query->execute();
    $evenements = $query->fetchAll(PDO::FETCH_ASSOC);
    
    // Top 5 événements
    $sql = "SELECT e.titre, COUNT(p.idParticipation) as count 
            FROM evenement e 
            LEFT JOIN participation p ON e.idEvenement = p.idEvenement 
            GROUP BY e.idEvenement 
            ORDER BY count DESC 
            LIMIT 5";
    $query = $db->prepare($sql);
    $query->execute();
    $stats['top_events'] = $query->fetchAll(PDO::FETCH_ASSOC);
    
    // Tendance hebdomadaire
    $sql = "SELECT 
                DATE_FORMAT(dateParticipation, '%Y-%m-%d') as date,
                COUNT(*) as count
            FROM participation 
            WHERE dateParticipation >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY DATE(dateParticipation)
            ORDER BY date";
    $query = $db->prepare($sql);
    $query->execute();
    $stats['weekly_trend'] = $query->fetchAll(PDO::FETCH_ASSOC);
    
    // ========== LISTE DES PARTICIPATIONS ==========
    if ($searchTerm) {
        $sql = "SELECT p.*, e.titre as evenement_titre, e.statut as evenement_statut 
                FROM participation p
                LEFT JOIN evenement e ON p.idEvenement = e.idEvenement
                WHERE p.contenu LIKE :search 
                OR e.titre LIKE :search
                ORDER BY p.dateParticipation DESC";
        $query = $db->prepare($sql);
        $query->execute(['search' => "%$searchTerm%"]);
    } else {
        $sql = "SELECT p.*, e.titre as evenement_titre, e.statut as evenement_statut 
                FROM participation p
                LEFT JOIN evenement e ON p.idEvenement = e.idEvenement
                ORDER BY p.dateParticipation DESC";
        $query = $db->prepare($sql);
        $query->execute();
    }
    $participations = $query->fetchAll(PDO::FETCH_ASSOC);
    
    // ========== PAIEMENTS RÉELS ==========
    // Vérifier si la table paiement_participation existe
    $tableCheck = $db->query("SHOW TABLES LIKE 'paiement_participation'")->fetch();
    
    if ($tableCheck) {
        $sql = "SELECT 
                    pp.*,
                    p.idEvenement,
                    e.titre as evenement_titre
                FROM paiement_participation pp
                LEFT JOIN participation p ON pp.idParticipation = p.idParticipation
                LEFT JOIN evenement e ON p.idEvenement = e.idEvenement
                ORDER BY pp.date_paiement DESC
                LIMIT 10";
        $query = $db->prepare($sql);
        $query->execute();
        $payments = $query->fetchAll(PDO::FETCH_ASSOC);
        
        // Statistiques paiements
        $sql = "SELECT 
                    COUNT(*) as total_paiements,
                    COALESCE(SUM(montant), 0) as total_montant,
                    COALESCE(AVG(montant), 0) as moyenne_montant,
                    SUM(CASE WHEN statut_paiement = 'complet' THEN montant ELSE 0 END) as montant_paye,
                    SUM(CASE WHEN statut_paiement = 'en_attente' THEN montant ELSE 0 END) as montant_attente
                FROM paiement_participation";
        $query = $db->prepare($sql);
        $query->execute();
        $payment_stats = $query->fetch(PDO::FETCH_ASSOC);
    } else {
        // Créer la table si elle n'existe pas
        $db->exec("CREATE TABLE IF NOT EXISTS paiement_participation (
            idPaiement INT PRIMARY KEY AUTO_INCREMENT,
            idParticipation INT NOT NULL,
            montant DECIMAL(10,2) NOT NULL,
            statut_paiement ENUM('en_attente', 'complet', 'annule') DEFAULT 'en_attente',
            mode_paiement VARCHAR(50),
            date_paiement DATETIME DEFAULT CURRENT_TIMESTAMP,
            transaction_id VARCHAR(100),
            FOREIGN KEY (idParticipation) REFERENCES participation(idParticipation) ON DELETE CASCADE
        )");
        
        $payments = [];
        $payment_stats = [
            'total_paiements' => 0,
            'total_montant' => 0,
            'moyenne_montant' => 0,
            'montant_paye' => 0,
            'montant_attente' => 0
        ];
    }
    
    // ========== CALENDRIER RÉEL ==========
    $sql = "SELECT 
                p.idParticipation,
                p.dateParticipation as start,
                CONCAT('Participation #', p.idParticipation) as title,
                e.titre as description,
                '#2ecc71' as color
            FROM participation p
            LEFT JOIN evenement e ON p.idEvenement = e.idEvenement
            WHERE p.dateParticipation >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ORDER BY p.dateParticipation DESC";
    $query = $db->prepare($sql);
    $query->execute();
    $calendarEvents = $query->fetchAll(PDO::FETCH_ASSOC);
    
    // Convertir pour FullCalendar
    $calendarJson = json_encode(array_map(function($event) {
        return [
            'id' => $event['idParticipation'],
            'title' => $event['title'],
            'start' => $event['start'],
            'description' => $event['description'],
            'color' => $event['color']
        ];
    }, $calendarEvents));
    
    // ========== ANALYSE DE SENTIMENT ==========
    $sql = "SELECT 
                p.idParticipation,
                p.contenu,
                CASE 
                    WHEN p.contenu LIKE '%super%' OR p.contenu LIKE '%excellent%' OR p.contenu LIKE '%génial%' OR p.contenu LIKE '%merci%' OR p.contenu LIKE '%parfait%' THEN 'positif'
                    WHEN p.contenu LIKE '%problème%' OR p.contenu LIKE '%difficile%' OR p.contenu LIKE '%compliqué%' OR p.contenu LIKE '%mauvais%' OR p.contenu LIKE '%déçu%' THEN 'negatif'
                    ELSE 'neutre'
                END as sentiment
            FROM participation p
            ORDER BY p.dateParticipation DESC
            LIMIT 50";
    $query = $db->prepare($sql);
    $query->execute();
    $sentimentAnalysis = $query->fetchAll(PDO::FETCH_ASSOC);
    
    // Statistiques sentiments
    $sentimentStats = [
        'positif' => 0,
        'neutre' => 0,
        'negatif' => 0
    ];
    
    foreach ($sentimentAnalysis as $analysis) {
        if (isset($analysis['sentiment']) && isset($sentimentStats[$analysis['sentiment']])) {
            $sentimentStats[$analysis['sentiment']]++;
        }
    }
    
    $totalSentiments = array_sum($sentimentStats);
    
} catch(PDOException $e) {
    $error_message = 'Erreur de base de données: ' . $e->getMessage();
    $participations = [];
    $evenements = [];
    $sentimentAnalysis = [];
    $sentimentStats = ['positif' => 0, 'neutre' => 0, 'negatif' => 0];
    $totalSentiments = 0;
}

// ========== GESTION DES ACTIONS POST ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Ajouter une participation
    if (isset($_POST['action']) && $_POST['action'] === 'add_participation') {
        $idEvenement = $_POST['idEvenement'] ?? '';
        $contenu = trim($_POST['contenu'] ?? '');
        $dateParticipation = $_POST['dateParticipation'] ?? '';
        
        if (!empty($idEvenement) && !empty($contenu) && !empty($dateParticipation)) {
            try {
                $sql = "INSERT INTO participation (idEvenement, contenu, dateParticipation) 
                        VALUES (:idEvenement, :contenu, :dateParticipation)";
                $query = $db->prepare($sql);
                $query->execute([
                    ':idEvenement' => $idEvenement,
                    ':contenu' => $contenu,
                    ':dateParticipation' => $dateParticipation
                ]);
                
                $success_message = 'Participation ajoutée avec succès';
                // Recharger la page
                echo "<script>window.location.href = window.location.href.split('?')[0] + '?success=" . urlencode($success_message) . "';</script>";
                exit;
            } catch(Exception $e) {
                $error_message = 'Erreur: ' . $e->getMessage();
            }
        } else {
            $error_message = 'Veuillez remplir tous les champs';
        }
    }
    
    // Ajouter un paiement
    elseif (isset($_POST['action']) && $_POST['action'] === 'add_payment') {
        $idParticipation = $_POST['payment_participation_id'] ?? '';
        $montant = $_POST['payment_amount'] ?? '';
        $statut = $_POST['payment_status'] ?? 'en_attente';
        $mode = $_POST['payment_method'] ?? '';
        $date = $_POST['payment_date'] ?? date('Y-m-d H:i:s');
        
        if (!empty($idParticipation) && !empty($montant) && $montant > 0) {
            try {
                // Vérifier si la participation existe
                $sqlCheck = "SELECT idParticipation FROM participation WHERE idParticipation = :id";
                $queryCheck = $db->prepare($sqlCheck);
                $queryCheck->execute([':id' => $idParticipation]);
                
                if ($queryCheck->fetch()) {
                    $sql = "INSERT INTO paiement_participation (idParticipation, montant, statut_paiement, mode_paiement, date_paiement) 
                            VALUES (:idParticipation, :montant, :statut, :mode, :date)";
                    $query = $db->prepare($sql);
                    $query->execute([
                        ':idParticipation' => $idParticipation,
                        ':montant' => $montant,
                        ':statut' => $statut,
                        ':mode' => $mode,
                        ':date' => $date
                    ]);
                    
                    $success_message = 'Paiement ajouté avec succès';
                    echo "<script>window.location.href = window.location.href.split('?')[0] + '?success=" . urlencode($success_message) . "';</script>";
                    exit;
                } else {
                    $error_message = 'Participation non trouvée';
                }
            } catch(Exception $e) {
                $error_message = 'Erreur: ' . $e->getMessage();
            }
        } else {
            $error_message = 'Veuillez remplir tous les champs correctement';
        }
    }
}

// Messages de succès/erreur depuis URL
if (isset($_GET['success'])) {
    $success_message = htmlspecialchars($_GET['success']);
}
if (isset($_GET['error'])) {
    $error_message = htmlspecialchars($_GET['error']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Gestion des Participations - EcoTrack</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            --sidebar-width: 280px;
            --ai-color: #9b59b6;
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
        }

        /* Dashboard Layout */
        .dashboard-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--dark);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .brand-logo {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
        }

        .brand-text h2 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.125rem;
        }

        .brand-text span {
            font-size: 0.75rem;
            color: var(--gray-400);
            opacity: 0.8;
        }

        .sidebar-nav {
            padding: 1.5rem;
        }

        .nav-group {
            margin-bottom: 2rem;
        }

        .nav-title {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--gray-400);
            margin-bottom: 0.75rem;
            font-weight: 600;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: var(--gray-300);
            text-decoration: none;
            border-radius: var(--radius);
            transition: var(--transition);
            margin-bottom: 0.5rem;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .nav-item.active {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
        }

        .nav-icon {
            width: 20px;
            text-align: center;
        }

        .nav-badge {
            margin-left: auto;
            background: var(--primary);
            color: white;
            font-size: 0.75rem;
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 2rem;
        }

        /* Top Navigation */
        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            background: white;
            padding: 1.5rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-200);
        }

        .page-title h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .page-title p {
            color: var(--gray-500);
            font-size: 0.875rem;
        }

        .top-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .search-container {
            position: relative;
        }

        .search-input {
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius);
            font-size: 0.875rem;
            width: 300px;
            transition: var(--transition);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
        }

        /* Buttons */
        .btn {
            padding: 0.75rem 1.5rem;
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

        .btn-secondary {
            background: var(--gray-200);
            color: var(--gray-700);
        }

        .btn-secondary:hover {
            background: var(--gray-300);
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

        .btn-ai {
            background: linear-gradient(135deg, var(--accent), #8e44ad);
            color: white;
        }

        .btn-ai:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-200);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .stat-title {
            font-size: 0.875rem;
            color: var(--gray-500);
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--gray-900);
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stat-trend {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            color: var(--gray-500);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--secondary), #2980b9);
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
        }

        /* Notifications */
        .notification {
            padding: 1rem 1.5rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
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

        .notification.ai {
            background: rgba(155, 89, 182, 0.1);
            color: var(--accent);
            border: 1px solid rgba(155, 89, 182, 0.2);
        }

        /* Dashboard Content Layout */
        .dashboard-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
        }

        @media (max-width: 1200px) {
            .dashboard-content {
                grid-template-columns: 1fr;
            }
        }

        /* Table Section */
        .table-section {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--gray-200);
            overflow: hidden;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 1.5rem 0;
            margin-bottom: 1rem;
        }

        .table-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .table-badge {
            background: var(--primary-light);
            color: var(--primary);
            font-size: 0.75rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-weight: 600;
        }

        .table-container {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table thead {
            background: var(--gray-50);
        }

        .data-table th {
            padding: 1rem 1.5rem;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
            border-bottom: 1px solid var(--gray-200);
        }

        .data-table td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            color: var(--gray-700);
            font-size: 0.875rem;
            vertical-align: middle;
        }

        .data-table tbody tr {
            transition: var(--transition);
        }

        .data-table tbody tr:hover {
            background: var(--primary-light);
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Badges */
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
        }

        .badge-event {
            background: rgba(155, 89, 182, 0.1);
            color: var(--accent);
        }

        .badge-date {
            background: rgba(52, 152, 219, 0.1);
            color: var(--secondary);
        }

        /* Content Preview */
        .content-preview {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-action {
            width: 32px;
            height: 32px;
            border: none;
            border-radius: var(--radius);
            background: var(--gray-100);
            color: var(--gray-600);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .btn-action:hover {
            background: var(--gray-200);
            transform: translateY(-1px);
        }

        /* Calendar Section */
        .calendar-section {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--gray-200);
            overflow: hidden;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 1.5rem 0;
            margin-bottom: 1rem;
        }

        .calendar-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .calendar-container {
            padding: 1.5rem;
            height: 400px;
        }

        /* Predictive Card */
        .predictive-card {
            background: linear-gradient(135deg, var(--gray-50), var(--gray-100));
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            border: 1px solid var(--gray-200);
        }

        .prediction-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 1rem;
        }

        /* Loading */
        .loading {
            text-align: center;
            padding: 3rem;
            color: var(--gray-500);
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid var(--gray-200);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }

        /* Advanced Sections */
        .advanced-section {
            margin-top: 1.5rem;
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--gray-700);
            font-size: 0.875rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
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
            min-height: 100px;
            resize: vertical;
        }

        select.form-control {
            cursor: pointer;
        }

        /* Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .modal {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
            max-width: 500px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .modal-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            padding: 1.5rem;
            border-top: 1px solid var(--gray-200);
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        /* Payment Status */
        .payment-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0.75rem;
            border-radius: var(--radius);
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-complet {
            background: rgba(46, 204, 113, 0.1);
            color: var(--success);
            border: 1px solid rgba(46, 204, 113, 0.2);
        }

        .status-en_attente {
            background: rgba(243, 156, 18, 0.1);
            color: var(--warning);
            border: 1px solid rgba(243, 156, 18, 0.2);
        }

        .status-annule {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger);
            border: 1px solid rgba(231, 76, 60, 0.2);
        }

        /* AI Response */
        .ai-response {
            background: rgba(155, 89, 182, 0.05);
            border-left: 3px solid var(--ai-color);
            padding: 1rem;
            border-radius: var(--radius);
            white-space: pre-line;
            font-family: monospace;
            font-size: 0.875rem;
        }

        /* Export Buttons */
        .export-buttons {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
            padding: 0 1.5rem 1.5rem;
        }

        /* Animations */
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

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                width: 70px;
            }
            
            .sidebar-header {
                padding: 1rem;
                justify-content: center;
            }
            
            .brand-text,
            .nav-title,
            .nav-item span,
            .nav-badge {
                display: none;
            }
            
            .nav-item {
                justify-content: center;
                padding: 1rem;
            }
            
            .main-content {
                margin-left: 70px;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }
            
            .top-nav {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }
            
            .search-input {
                width: 100%;
            }
            
            .top-actions {
                flex-wrap: wrap;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .modal {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="brand">
                    <div class="brand-logo">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <div class="brand-text">
                        <h2>EcoTrack</h2>
                        <span>Dashboard Pro</span>
                    </div>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-group">
                    <div class="nav-title">Navigation</div>
                    <a href="#" class="nav-item">
                        <i class="fas fa-tachometer-alt nav-icon"></i>
                        <span>Tableau de bord</span>
                    </a>
                    <a href="Participation.php" class="nav-item active">
                        <i class="fas fa-comments nav-icon"></i>
                        <span>Participations</span>
                        <span class="nav-badge"><?= $stats['total'] ?? 0 ?></span>
                    </a>
                    <a href="listEvenement.php" class="nav-item">
                        <i class="fas fa-calendar-alt nav-icon"></i>
                        <span>Événements</span>
                    </a>
                </div>

                <div class="nav-group">
                    <div class="nav-title">Fonctions Avancées</div>
                    <a href="#aiChat" class="nav-item" onclick="showAIChatbox()">
                        <i class="fas fa-robot nav-icon"></i>
                        <span>Assistant AI</span>
                    </a>
                    <a href="#reports" class="nav-item" onclick="showReportSection()">
                        <i class="fas fa-file-export nav-icon"></i>
                        <span>Générer Rapports</span>
                    </a>
                    <a href="#calendar" class="nav-item" onclick="showCalendar()">
                        <i class="fas fa-calendar-alt nav-icon"></i>
                        <span>Calendrier</span>
                    </a>
                    <a href="#payments" class="nav-item" onclick="showPayments()">
                        <i class="fas fa-credit-card nav-icon"></i>
                        <span>Paiements</span>
                        <?php if(($payment_stats['total_paiements'] ?? 0) > 0): ?>
                        <span class="nav-badge"><?= $payment_stats['total_paiements'] ?? 0 ?></span>
                        <?php endif; ?>
                    </a>
                </div>

                <div class="nav-group">
                    <div class="nav-title">Actions Rapides</div>
                    <a href="AddParticipation.php" class="nav-item">
                        <i class="fas fa-plus-circle nav-icon"></i>
                        <span>Nouvelle Participation</span>
                    </a>
                    <a href="#" class="nav-item" onclick="openAddModal()">
                        <i class="fas fa-edit nav-icon"></i>
                        <span>Modifier rapide</span>
                    </a>
                    <a href="#" class="nav-item" onclick="exportAllData()">
                        <i class="fas fa-download nav-icon"></i>
                        <span>Exporter tout</span>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navigation -->
            <header class="top-nav">
                <div class="page-title">
                    <h1><i class="fas fa-comments"></i> Gestion des Participations</h1>
                    <p>Dashboard Backoffice - Analytics & Intelligence</p>
                </div>

                <div class="top-actions">
                    <div class="search-container">
                        <i class="fas fa-search search-icon"></i>
                        <form method="GET" action="">
                            <input type="text" 
                                   name="search" 
                                   class="search-input" 
                                   placeholder="Rechercher..." 
                                   value="<?= htmlspecialchars($searchTerm) ?>">
                            <button type="submit" style="display:none"></button>
                        </form>
                    </div>

                    <button class="btn btn-primary" onclick="window.location.href='AddParticipation.php'">
                        <i class="fas fa-plus-circle"></i>
                        Nouvelle Participation
                    </button>

                    <button class="btn btn-secondary" onclick="refreshData()">
                        <i class="fas fa-sync-alt"></i>
                        Actualiser
                    </button>
                </div>
            </header>

            <!-- Notifications -->
            <?php if ($success_message): ?>
                <div class="notification success">
                    <i class="fas fa-check-circle"></i>
                    <?= $success_message ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="notification error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= $error_message ?>
                </div>
            <?php endif; ?>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-title">Total Participations</div>
                            <div class="stat-value"><?= $stats['total'] ?? 0 ?></div>
                            <div class="stat-trend">
                                <i class="fas fa-chart-line"></i>
                                <span>Statistiques globales</span>
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-title">Aujourd'hui</div>
                            <div class="stat-value"><?= $stats['today'] ?? 0 ?></div>
                            <div class="stat-trend">
                                <i class="fas fa-bolt"></i>
                                <span>Activité du jour</span>
                            </div>
                        </div>
                        <div class="stat-icon" style="background: linear-gradient(135deg, #f39c12, #d35400);">
                            <i class="fas fa-sun"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-title">Ce Mois</div>
                            <div class="stat-value"><?= $stats['this_month'] ?? 0 ?></div>
                            <div class="stat-trend">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Mois en cours</span>
                            </div>
                        </div>
                        <div class="stat-icon" style="background: linear-gradient(135deg, #9b59b6, #8e44ad);">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-title">Revenu Total</div>
                            <div class="stat-value"><?= number_format($payment_stats['total_montant'] ?? 0, 2) ?> €</div>
                            <div class="stat-trend">
                                <i class="fas fa-euro-sign"></i>
                                <span>Paiements reçus</span>
                            </div>
                        </div>
                        <div class="stat-icon" style="background: linear-gradient(135deg, #2ecc71, #27ae60);">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="dashboard-content">
                <!-- Liste des Participations -->
                <div class="table-section">
                    <div class="table-header">
                        <div>
                            <h3 class="table-title">
                                <i class="fas fa-list"></i>
                                Liste des Participations
                                <span class="table-badge"><?= count($participations) ?></span>
                            </h3>
                        </div>
                        <div class="table-actions">
                            <button class="btn btn-outline" onclick="exportToCSV()">
                                <i class="fas fa-file-csv"></i>
                                CSV
                            </button>
                            <button class="btn btn-outline" onclick="exportToPDF()">
                                <i class="fas fa-file-pdf"></i>
                                PDF
                            </button>
                            <button class="btn btn-ai" onclick="showAIChatbox()">
                                <i class="fas fa-robot"></i>
                                Analyser
                            </button>
                        </div>
                    </div>

                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Événement</th>
                                    <th>Contenu</th>
                                    <th>Date</th>
                                    <th>Sentiment</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($participations)): ?>
                                    <tr>
                                        <td colspan="6" class="loading">
                                            <div class="loading-spinner"></div>
                                            <div>Aucune participation trouvée</div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($participations as $participation): 
                                        $contenu_lower = strtolower($participation['contenu']);
                                        $sentiment = 'neutre';
                                        if (strpos($contenu_lower, 'super') !== false || strpos($contenu_lower, 'excellent') !== false || 
                                            strpos($contenu_lower, 'génial') !== false || strpos($contenu_lower, 'merci') !== false) {
                                            $sentiment = 'positif';
                                        } elseif (strpos($contenu_lower, 'probleme') !== false || strpos($contenu_lower, 'difficile') !== false || 
                                                   strpos($contenu_lower, 'compliqué') !== false || strpos($contenu_lower, 'mauvais') !== false) {
                                            $sentiment = 'negatif';
                                        }
                                    ?>
                                    <tr>
                                        <td>#<?= htmlspecialchars($participation['idParticipation']) ?></td>
                                        <td>
                                            <span class="badge badge-event">
                                                <?= htmlspecialchars($participation['evenement_titre'] ?? 'N/A') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="content-preview" title="<?= htmlspecialchars($participation['contenu']) ?>">
                                                <?= htmlspecialchars(substr($participation['contenu'], 0, 100)) ?>
                                                <?= strlen($participation['contenu']) > 100 ? '...' : '' ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-date">
                                                <?= date('d/m/Y H:i', strtotime($participation['dateParticipation'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge" style="background: <?= $sentiment == 'positif' ? 'rgba(46, 204, 113, 0.1)' : ($sentiment == 'negatif' ? 'rgba(231, 76, 60, 0.1)' : 'rgba(243, 156, 18, 0.1)') ?>;">
                                                <?= $sentiment ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-action" onclick="viewParticipation(<?= $participation['idParticipation'] ?>)" title="Voir">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn-action" onclick="editParticipation(<?= $participation['idParticipation'] ?>)" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn-action" onclick="addPayment(<?= $participation['idParticipation'] ?>)" title="Paiement">
                                                    <i class="fas fa-euro-sign"></i>
                                                </button>
                                                <button class="btn-action" onclick="deleteParticipation(<?= $participation['idParticipation'] ?>)" title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Boutons d'export -->
                    <div class="export-buttons">
                        <button class="btn btn-outline" onclick="exportToExcel()">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                        <button class="btn btn-outline" onclick="printTable()">
                            <i class="fas fa-print"></i> Imprimer
                        </button>
                    </div>
                </div>

                <!-- Section Droite : Calendrier et Stats -->
                <div>
                    <!-- Calendrier -->
                    <div class="calendar-section">
                        <div class="calendar-header">
                            <h3 class="calendar-title">
                                <i class="fas fa-calendar-alt"></i>
                                Calendrier des Participations
                            </h3>
                            <button class="btn btn-outline btn-sm" onclick="refreshCalendar()">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                        <div class="calendar-container" id="calendar"></div>
                    </div>

                    <!-- Analyse de Sentiment -->
                    <div class="predictive-card" style="margin-top: 1.5rem;">
                        <div class="prediction-indicator">
                            <i class="fas fa-smile" style="color: var(--ai-color);"></i>
                            <span>Analyse de Sentiment</span>
                        </div>
                        <div style="font-size: 0.875rem; color: var(--gray-700);">
                            <p>😊 <strong>Positif:</strong> <?= $sentimentStats['positif'] ?> (<?= $totalSentiments > 0 ? round(($sentimentStats['positif']/$totalSentiments)*100) : 0 ?>%)</p>
                            <p>😐 <strong>Neutre:</strong> <?= $sentimentStats['neutre'] ?> (<?= $totalSentiments > 0 ? round(($sentimentStats['neutre']/$totalSentiments)*100) : 0 ?>%)</p>
                            <p>😞 <strong>Négatif:</strong> <?= $sentimentStats['negatif'] ?> (<?= $totalSentiments > 0 ? round(($sentimentStats['negatif']/$totalSentiments)*100) : 0 ?>%)</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========== SECTIONS AVANCÉES ========== -->

            <!-- Section Paiements -->
            <div id="paymentsSection" class="table-section advanced-section" style="display: none; margin-top: 1.5rem;">
                <div class="table-header">
                    <h3 class="table-title">
                        <i class="fas fa-credit-card"></i>
                        Gestion des Paiements
                    </h3>
                    <button class="btn btn-primary" onclick="openPaymentModal()">
                        <i class="fas fa-plus"></i>
                        Nouveau Paiement
                    </button>
                </div>
                
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID Paiement</th>
                                <th>Participation</th>
                                <th>Montant</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th>Mode</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($payments)): ?>
                                <tr>
                                    <td colspan="7" class="loading">
                                        <div>Aucun paiement enregistré</div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($payments as $payment): ?>
                                <tr>
                                    <td>#<?= htmlspecialchars($payment['idPaiement']) ?></td>
                                    <td>Participation #<?= htmlspecialchars($payment['idParticipation']) ?></td>
                                    <td><?= number_format($payment['montant'], 2) ?> €</td>
                                    <td>
                                        <span class="payment-status status-<?= htmlspecialchars($payment['statut_paiement']) ?>">
                                            <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                            <?= htmlspecialchars($payment['statut_paiement']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($payment['date_paiement'])) ?></td>
                                    <td><?= htmlspecialchars($payment['mode_paiement']) ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-action" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn-action" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Section AI Chat -->
            <div id="aiChatSection" class="table-section advanced-section" style="display: none; margin-top: 1.5rem;">
                <div class="table-header">
                    <h3 class="table-title">
                        <i class="fas fa-robot"></i>
                        Assistant AI
                    </h3>
                </div>
                
                <div class="table-container">
                    <div style="padding: 1.5rem;">
                        <form method="POST" id="aiForm">
                            <input type="hidden" name="action" value="ask_ai">
                            
                            <div class="form-group">
                                <label class="form-label">Posez une question à l'AI</label>
                                <textarea name="ai_question" class="form-control" rows="3" 
                                          placeholder="Exemple: Quelle est la tendance des participations ce mois ?" required></textarea>
                            </div>
                            
                            <div>
                                <button type="submit" class="btn btn-ai">
                                    <i class="fas fa-paper-plane"></i>
                                    Analyser
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- ========== MODALS ========== -->

    <!-- Modal Paiement -->
    <div id="paymentModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fas fa-credit-card"></i>
                    Ajouter un Paiement
                </h3>
                <button type="button" class="btn-action" onclick="closePaymentModal()" style="background: transparent;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" id="paymentForm" action="">
                <input type="hidden" name="action" value="add_payment">
                <input type="hidden" name="payment_participation_id" id="paymentParticipationId">
                
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Participation ID</label>
                        <input type="text" id="paymentParticipationDisplay" class="form-control" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Montant (€) *</label>
                        <input type="number" name="payment_amount" class="form-control" 
                               step="0.01" min="0.01" required placeholder="0.00">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Statut du paiement *</label>
                        <select name="payment_status" class="form-control" required>
                            <option value="en_attente">En attente</option>
                            <option value="complet">Complet</option>
                            <option value="annule">Annulé</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Mode de paiement *</label>
                        <select name="payment_method" class="form-control" required>
                            <option value="Carte bancaire">Carte bancaire</option>
                            <option value="PayPal">PayPal</option>
                            <option value="Virement">Virement</option>
                            <option value="Espèces">Espèces</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Date du paiement *</label>
                        <input type="datetime-local" name="payment_date" class="form-control" 
                               value="<?= date('Y-m-d\TH:i') ?>" required>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closePaymentModal()">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Vue Détails -->
    <div id="viewModal" class="modal-overlay">
        <div class="modal" style="max-width: 600px;">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fas fa-eye"></i>
                    Détails de la Participation
                </h3>
                <button type="button" class="btn-action" onclick="closeViewModal()" style="background: transparent;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="viewContent">
                <!-- Chargé dynamiquement -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeViewModal()">Fermer</button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/fr.js"></script>
    
    <script>
        // ========== FONCTIONS PRINCIPALES ==========
        
        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            initializeCalendar();
            setupEventListeners();
        });
        
        // Initialiser le calendrier FullCalendar
        function initializeCalendar() {
            const calendarEl = document.getElementById('calendar');
            if (calendarEl) {
                try {
                    const calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'dayGridMonth',
                        locale: 'fr',
                        height: 'auto',
                        events: <?= $calendarJson ?>,
                        eventClick: function(info) {
                            if (info.event.id) {
                                viewParticipation(info.event.id);
                            }
                        },
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek'
                        }
                    });
                    calendar.render();
                    window.calendar = calendar;
                } catch (e) {
                    console.error('Erreur calendrier:', e);
                }
            }
        }
        
        // Configurer les écouteurs d'événements
        function setupEventListeners() {
            // Auto-hide notifications
            setTimeout(() => {
                document.querySelectorAll('.notification').forEach(notification => {
                    notification.style.opacity = '0';
                    setTimeout(() => notification.remove(), 300);
                });
            }, 5000);
            
            // Validation formulaires
            setupFormValidation();
            
            // Recherche à la frappe
            const searchInput = document.querySelector('.search-input');
            if (searchInput) {
                let searchTimeout;
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        this.form.submit();
                    }, 500);
                });
            }
        }
        
        // ========== FONCTIONS DE NAVIGATION ==========
        
        function showPayments() {
            hideAllSections();
            document.getElementById('paymentsSection').style.display = 'block';
            scrollToSection('paymentsSection');
        }
        
        function showAIChatbox() {
            hideAllSections();
            document.getElementById('aiChatSection').style.display = 'block';
            scrollToSection('aiChatSection');
        }
        
        function hideAllSections() {
            document.querySelectorAll('.advanced-section').forEach(section => {
                section.style.display = 'none';
            });
        }
        
        function scrollToSection(sectionId) {
            const section = document.getElementById(sectionId);
            if (section) {
                window.scrollTo({
                    top: section.offsetTop - 20,
                    behavior: 'smooth'
                });
            }
        }
        
        // ========== FONCTIONS DES PARTICIPATIONS ==========
        
        function viewParticipation(id) {
            fetch('get_participation_details.php?id=' + id)
                .then(response => {
                    if (!response.ok) throw new Error('Network error');
                    return response.text();
                })
                .then(html => {
                    document.getElementById('viewContent').innerHTML = html || 'Aucun détail disponible';
                    document.getElementById('viewModal').style.display = 'flex';
                })
                .catch(error => {
                    document.getElementById('viewContent').innerHTML = `
                        <div style="text-align: center; padding: 2rem;">
                            <h4>Détails de la Participation #${id}</h4>
                            <p>Impossible de charger les détails. Voici un aperçu rapide:</p>
                            <p><strong>ID:</strong> ${id}</p>
                            <p><em>Fonctionnalité avancée en développement...</em></p>
                        </div>
                    `;
                    document.getElementById('viewModal').style.display = 'flex';
                });
        }
        
        function editParticipation(id) {
            window.location.href = 'editParticipation.php?edit_id=' + id;
        }
        
        function deleteParticipation(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette participation ?')) {
                window.location.href = 'Participation.php?delete_id=' + id;
            }
        }
        
        function addPayment(participationId) {
            document.getElementById('paymentParticipationId').value = participationId;
            document.getElementById('paymentParticipationDisplay').value = 'Participation #' + participationId;
            document.getElementById('paymentModal').style.display = 'flex';
        }
        
        function openPaymentModal() {
            document.getElementById('paymentParticipationId').value = '';
            document.getElementById('paymentParticipationDisplay').value = '';
            document.getElementById('paymentModal').style.display = 'flex';
        }
        
        function closePaymentModal() {
            document.getElementById('paymentModal').style.display = 'none';
            document.getElementById('paymentForm').reset();
        }
        
        function closeViewModal() {
            document.getElementById('viewModal').style.display = 'none';
        }
        
        // ========== FONCTIONS D'EXPORT ==========
        
        function exportToCSV() {
            const table = document.querySelector('.data-table');
            if (!table) return;
            
            let csv = [];
            
            // Headers
            const headers = [];
            table.querySelectorAll('thead th').forEach(th => {
                headers.push(th.innerText.trim());
            });
            csv.push(headers.join(','));
            
            // Rows
            table.querySelectorAll('tbody tr').forEach(row => {
                const rowData = [];
                row.querySelectorAll('td').forEach(cell => {
                    let text = cell.innerText.trim();
                    // Nettoyer le texte pour CSV
                    text = text.replace(/"/g, '""').replace(/\n/g, ' ');
                    rowData.push(`"${text}"`);
                });
                csv.push(rowData.join(','));
            });
            
            // Télécharger
            const blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `participations_${new Date().toISOString().slice(0,10)}.csv`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }
        
        function exportToPDF() {
            alert('Fonction PDF à implémenter avec une bibliothèque dédiée comme jsPDF');
        }
        
        function exportToExcel() {
            exportToCSV(); // CSV est lisible par Excel
        }
        
        function printTable() {
            const printContent = document.querySelector('.table-section').innerHTML;
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Liste des Participations - EcoTrack</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f2f2f2; }
                        @media print {
                            body { margin: 0; }
                            @page { margin: 1cm; }
                        }
                    </style>
                </head>
                <body>
                    <h1>Liste des Participations - EcoTrack</h1>
                    <p>Généré le ${new Date().toLocaleDateString('fr-FR')}</p>
                    <p>Total: ${document.querySelector('.table-badge')?.innerText || '0'} participations</p>
                    ${printContent.replace(/<button[^>]*>.*?<\/button>/g, '')}
                </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.focus();
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 250);
        }
        
        function exportAllData() {
            if (confirm('Cette fonctionnalité générera un fichier CSV avec toutes les données. Continuer ?')) {
                window.location.href = 'export_all_data.php';
            }
        }
        
        // ========== FONCTIONS UTILITAIRES ==========
        
        function refreshData() {
            window.location.reload();
        }
        
        function refreshCalendar() {
            if (window.calendar) {
                window.calendar.refetchEvents();
            }
        }
        
        // Fermer modals en cliquant à l'extérieur
        window.onclick = function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                event.target.style.display = 'none';
            }
        }
        
        // Validation des formulaires
        function setupFormValidation() {
            const paymentForm = document.getElementById('paymentForm');
            if (paymentForm) {
                paymentForm.addEventListener('submit', function(e) {
                    const amount = this.querySelector('input[name="payment_amount"]');
                    if (!amount.value || parseFloat(amount.value) <= 0) {
                        e.preventDefault();
                        alert('Le montant doit être supérieur à 0.');
                        amount.focus();
                    }
                });
            }
            
            const aiForm = document.getElementById('aiForm');
            if (aiForm) {
                aiForm.addEventListener('submit', function(e) {
                    const question = this.querySelector('textarea[name="ai_question"]');
                    if (!question.value.trim()) {
                        e.preventDefault();
                        alert('Veuillez poser une question à l\'AI.');
                        question.focus();
                    }
                });
            }
        }
        
        // ========== FONCTIONS AI ==========
        
        function analyzeWithAI() {
            const question = prompt('Que voulez-vous analyser ?');
            if (question) {
                document.querySelector('textarea[name="ai_question"]').value = question;
                document.getElementById('aiForm').submit();
            }
        }
        
        // ========== AUTRES FONCTIONS ==========
        
        function openAddModal() {
            const randomId = Math.floor(Math.random() * <?= $stats['total'] ?? 1 ?>);
            editParticipation(randomId + 1);
        }
        
        // Créer un fichier get_participation_details.php temporaire
        function createDetailsFile() {
            // Cette fonction peut être utilisée pour créer dynamiquement le fichier nécessaire
            console.log('Fonction pour créer get_participation_details.php');
        }
        
        // Initialiser la création du fichier si nécessaire
        createDetailsFile();
    </script>
</body>
</html>